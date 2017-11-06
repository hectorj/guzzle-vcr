<?php
declare(strict_types=1);

namespace Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Response;
use GuzzleVCR\Dump\PHPRecordDumper;
use GuzzleVCR\RecorderMiddleware;
use GuzzleVCR\RequestNormalizers\StandardNormalizers;
use GuzzleVCR\RequestNormalizers\UserAgentStripper;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class RecorderMiddlewareTest extends TestCase
{
	public function test_basic_dump()
	{
		$mock = new MockHandler([
			new Response(200, ['X-Foo' => 'Bar']),
		]);
		$stack = new HandlerStack($mock);
		$recordDumper = new InMemoryRecordDumper();
		$stack->push(new RecorderMiddleware($recordDumper));

		$client = new GuzzleClient([
			'handler' => $stack
		]);
		$expectedResponse = $client->get('http://example.com/');

		$records = $recordDumper->getRecords();
		self::assertCount(1, $records);
		self::assertEquals($expectedResponse, $records[0]->getPromise()->wait(true));
	}

	public function test_record_dump1()
	{
		$promise1 = new Promise();
		$promise2 = new Promise();
		$mock = new MockHandler([
			$promise1,
			$promise2,
			new Response(200, ['X-Foo' => 'Bar1']),
			new \Exception('test 404 exception #1', 42),
		]);
		$stack = new HandlerStack($mock);
		$tmpFilePath = stream_get_meta_data(tmpfile())['uri'];

		$phpRecordDumper = new PHPRecordDumper($tmpFilePath);
		$recordDumper = (new StandardNormalizers())->wrapDumper($phpRecordDumper);
		$stack->push(new RecorderMiddleware($recordDumper));

		$client = new GuzzleClient([
			'handler' => $stack
		]);

		$returnedPromise1 = $client->getAsync('http://example.com/');

		$returnedPromise2 = $client->getAsync('http://example.com/404');

		$client->get('http://example.com/');

		$client->getAsync('http://example.com/404')->wait(false);

		$promise2->reject(new \Exception('test 404 exception #2', 7));
		$promise1->resolve(new Response(200, ['X-Foo' => 'Bar2']));

		$returnedPromise1->wait(false);
		$returnedPromise2->wait(false);

		$phpRecordDumper->flush();


		$dumpContent = file_get_contents($tmpFilePath);

		$expectedDumpFilePath = __DIR__.'/dumps/dump1.expected.php';
		if (!is_file($expectedDumpFilePath)) {
			file_put_contents($expectedDumpFilePath, $dumpContent);
			self::markTestIncomplete('The expected dump did not exist. We created it. Now re-run this test!');
		}
		$expectedDumpContent = file_get_contents($expectedDumpFilePath);

		try {
			self::assertSame($expectedDumpContent, $dumpContent);
		} catch (ExpectationFailedException $e) {
			file_put_contents(__DIR__.'/dumps/dump1.actual.php', $dumpContent);
			throw $e;
		}
	}
}
