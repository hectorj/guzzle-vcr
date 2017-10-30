<?php
declare(strict_types=1);

namespace Tests\BeforeReplayHooks\RequestMatchers;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use GuzzleVCR\BeforeReplayHook;
use GuzzleVCR\BeforeReplayHooks\RequestMatchers\FullRequestMatcher;
use GuzzleVCR\GuzzleRequest;
use GuzzleVCR\Record;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class RequestMatchersTest extends TestCase
{
	/**
	 * @dataProvider nonMatchingProvider
	 */
	public function test_different_requests_make_test_fail(Record $record, GuzzleRequest $request, BeforeReplayHook $matcher, string $expectedExceptionMessage): void
	{
		$this->expectException(ExpectationFailedException::class);
		$this->expectExceptionMessage($expectedExceptionMessage);
		$matcher($record, $request);

	}

	public static function nonMatchingProvider(): array
	{
		$promise = new FulfilledPromise("doesn't matter");
		return [
			"same everything, except URI" => [
				new Record(
					new GuzzleRequest(
						new Request('GET', 'http://example.com/some/URI.html'),
						[]
					),
					clone $promise
				),
				new GuzzleRequest(
					new Request('GET', 'http://example.com/some/other/URI.html'),
					[]
				),
				new FullRequestMatcher(),
				"request's URI should match records, or records should be regenerated"
			],
			"same everything, except the body" => [
				new Record(
					new GuzzleRequest(
						new Request('GET', 'http://example.com/some/URI.html', [], "somebody"),
						[]
					),
					clone $promise
				),
				new GuzzleRequest(
					new Request('GET', 'http://example.com/some/URI.html', [], "anotherbody"),
					[]
				),
				new FullRequestMatcher(),
				"request's body should match records, or records should be regenerated"
			],
			"same everything, except one header" => [
				new Record(
					new GuzzleRequest(
						new Request('GET', 'http://example.com/some/URI.html', ['X-Foo' => 'Bar']),
						[]
					),
					clone $promise
				),
				new GuzzleRequest(
					new Request('GET', 'http://example.com/some/URI.html', ['X-Foo' => 'Bar2']),
					[]
				),
				new FullRequestMatcher(),
				"request's headers should match records, or records should be regenerated"
			],
			"same everything, except method" => [
				new Record(
					new GuzzleRequest(
						new Request('GET', 'http://example.com/some/URI.html'),
						[]
					),
					clone $promise
				),
				new GuzzleRequest(
					new Request('POST', 'http://example.com/some/URI.html'),
					[]
				),
				new FullRequestMatcher(),
				"request's method should match records, or records should be regenerated"
			],
			"same everything, except one option" => [
				new Record(
					new GuzzleRequest(
						new Request('GET', 'http://example.com/some/URI.html'),
						[
							RequestOptions::ALLOW_REDIRECTS => true,
						]
					),
					clone $promise
				),
				new GuzzleRequest(
					new Request('GET', 'http://example.com/some/URI.html'),
					[
						RequestOptions::ALLOW_REDIRECTS => false,
					]
				),
				new FullRequestMatcher(),
				"request's options should match records, or records should be regenerated"
			],
			"same everything, except protocol version" => [
				new Record(
					new GuzzleRequest(
						new Request('GET', 'http://example.com/some/URI.html', [], null, '1.1'),
						[]
					),
					clone $promise
				),
				new GuzzleRequest(
					new Request('GET', 'http://example.com/some/URI.html', [], null, '1.0'),
					[]
				),
				new FullRequestMatcher(),
				"request's protocol version should match records, or records should be regenerated"
			]
		];
	}
}
