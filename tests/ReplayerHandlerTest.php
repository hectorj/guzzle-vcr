<?php
declare(strict_types=1);

namespace Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleVCR\BeforeReplayHooks\RequestMatchers\FullRequestMatcher;
use GuzzleVCR\Dump\PHPRecordDumper;
use GuzzleVCR\GuzzleRequest;
use GuzzleVCR\MissingRecordError;
use GuzzleVCR\Record;
use GuzzleVCR\ReplayerHandler;
use GuzzleVCR\RequestNormalizers\HandlerStripper;
use GuzzleVCR\RequestNormalizers\StandardNormalizers;
use GuzzleVCR\RequestNormalizers\UserAgentStripper;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class ReplayerHandlerTest extends TestCase
{
	public function test_replay_without_records(): void
	{
		$records = [];
		$handler = new ReplayerHandler($records);
		$stack = new HandlerStack($handler);

		$client = new GuzzleClient([
			'handler' => $stack
		]);
		$this->expectException(MissingRecordError::class);
		$client->get('http://example.com/');
	}

	public function test_replay_nonmatching_record(): void
	{
		$records = [
			new Record(
				new GuzzleRequest(
					new Request('GET', 'http://example.com/'),
					[]
				),
				new FulfilledPromise(new Response(200, [], 'example body for testing purposes'))
			),
		];
		$handler = new ReplayerHandler($records, new FullRequestMatcher());
		$stack = new HandlerStack($handler);

		$client = new GuzzleClient([
			'handler' => $stack
		]);

		$this->expectException(ExpectationFailedException::class);
		$this->expectExceptionMessage("request's URI should match records, or records should be regenerated");
		$client->get('http://example.com/some-page');
	}

	public function test_replay_matching_record_with_response(): void
	{
		$expectedResponse = new Response(200, [], 'example body for testing purposes');
		$records = [
			new Record(
				new GuzzleRequest(
					new Request('GET', 'http://example.com/'),
					[]
				),
				new FulfilledPromise($expectedResponse)
			),
		];
		$handler = new ReplayerHandler($records);
		$stack = new HandlerStack($handler);

		$client = new GuzzleClient([
			'handler' => $stack
		]);

		$actualResponse = $client->get('http://example.com/');
		self::assertSame($expectedResponse, $actualResponse);
	}

	public function test_replay_matching_record_with_error(): void
	{
		$expectedException = new \Exception('example error for testing purposes', 42);
		$records = [
			new Record(
				new GuzzleRequest(
					new Request('GET', 'http://example.com/'),
					[]
				),
				new RejectedPromise($expectedException)
			),
		];
		$handler = new ReplayerHandler($records);
		$stack = new HandlerStack($handler);

		$client = new GuzzleClient([
			'handler' => $stack
		]);
		$this->expectException(get_class($expectedException));
		$this->expectExceptionMessage($expectedException->getMessage());
		$this->expectExceptionCode($expectedException->getCode());
		$client->get('http://example.com/');
	}

	public function test_replay_matching_record_with_delayed_response(): void
	{
		$expectedResponse = new Response(200, [], 'example body for testing purposes');
		$expectedPromise = new Promise();
		$records = [
			new Record(
				new GuzzleRequest(
					new Request('GET', 'http://example.com/'),
					[]
				),
				$expectedPromise
			),
		];
		$handler = new ReplayerHandler($records);
		$stack = new HandlerStack($handler);

		$client = new GuzzleClient([
			'handler' => $stack
		]);

		$actualPromise = $client->getAsync('http://example.com/');
		self::assertSame(PromiseInterface::PENDING, $actualPromise->getState());

		$expectedPromise->resolve($expectedResponse);
		self::assertSame(PromiseInterface::FULFILLED, $actualPromise->getState());

		$actualResponse = $actualPromise->wait(true);
		self::assertSame($expectedResponse, $actualResponse);
	}

	public function test_replay_matching_record_with_delayed_error(): void
	{
		$expectedException = new \Exception('example error for testing purposes', 42);
		$expectedPromise = new Promise();
		$records = [
			new Record(
				new GuzzleRequest(
					new Request('GET', 'http://example.com/'),
					[]
				),
				$expectedPromise
			),
		];
		$handler = new ReplayerHandler($records);
		$stack = new HandlerStack($handler);

		$client = new GuzzleClient([
			'handler' => $stack
		]);

		$actualPromise = $client->getAsync('http://example.com/');
		self::assertSame(PromiseInterface::PENDING, $actualPromise->getState());

		$expectedPromise->reject($expectedException);
		self::assertSame(PromiseInterface::REJECTED, $actualPromise->getState());

		$this->expectException(get_class($expectedException));
		$this->expectExceptionMessage($expectedException->getMessage());
		$this->expectExceptionCode($expectedException->getCode());
		$actualPromise->wait(true);
	}

	public function test_replay_dump1(): void
	{
		$records = PHPRecordDumper::loadDump(__DIR__.'/dumps/dump1.expected.php');
		$normalizers = new StandardNormalizers();
		$handler = new ReplayerHandler($records, $normalizers->wrapHook(new FullRequestMatcher()));
		$stack = new HandlerStack($handler);

		$client = new GuzzleClient([
			'handler' => $stack
		]);

		$returnedPromise1 = $client->getAsync('http://example.com/');

		$returnedPromise2 = $client->getAsync('http://example.com/404');

		$response = $client->get('http://example.com/');
		self::assertSame(200, $response->getStatusCode());
		self::assertSame(['X-Foo' => ['Bar1']], $response->getHeaders());

		$thrownException = null;
		try {
			$client->getAsync('http://example.com/404')->wait(true);
		} catch (\Exception $e) {
			$thrownException = $e;
		}
		self::assertNotNull($thrownException);
		self::assertSame(42, $thrownException->getCode());
		self::assertSame('test 404 exception #1', $thrownException->getMessage());

		$response = $returnedPromise1->wait(true);
		self::assertSame(200, $response->getStatusCode());
		self::assertSame(['X-Foo' => ['Bar2']], $response->getHeaders());

		$thrownException = null;
		try {
			$returnedPromise2->wait(true);
		} catch (\Exception $e) {
			$thrownException = $e;
		}
		self::assertNotNull($thrownException);
		self::assertSame(7, $thrownException->getCode());
		self::assertSame('test 404 exception #2', $thrownException->getMessage());
	}
}
