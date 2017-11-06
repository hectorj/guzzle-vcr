<?php

$fn = function () : \Generator {
	$yields = [];
	$promises = [];

	$promises[0] = new \GuzzleHttp\Promise\Promise();
	$yields[0] = new \GuzzleVCR\Record(
		new \GuzzleVCR\GuzzleRequest(
			new \GuzzleHttp\Psr7\Request(
				'GET',
				'http://example.com/',
				array (
					'Host' =>
					array (
						0 => 'example.com',
					),
				),
				'',
				'1.1'
			),
			array (
				'allow_redirects' =>
				array (
					'max' => 5,
					'protocols' =>
					array (
						0 => 'http',
						1 => 'https',
					),
					'strict' => false,
					'referer' => false,
					'track_redirects' => false,
				),
				'http_errors' => true,
				'decode_content' => true,
				'verify' => true,
				'cookies' => false,
			)
		),
		$promises[0]
	);
	yield $yields[0];

	$promises[1] = new \GuzzleHttp\Promise\Promise();
	$yields[1] = new \GuzzleVCR\Record(
		new \GuzzleVCR\GuzzleRequest(
			new \GuzzleHttp\Psr7\Request(
				'GET',
				'http://example.com/404',
				array (
					'Host' =>
					array (
						0 => 'example.com',
					),
				),
				'',
				'1.1'
			),
			array (
				'allow_redirects' =>
				array (
					'max' => 5,
					'protocols' =>
					array (
						0 => 'http',
						1 => 'https',
					),
					'strict' => false,
					'referer' => false,
					'track_redirects' => false,
				),
				'http_errors' => true,
				'decode_content' => true,
				'verify' => true,
				'cookies' => false,
			)
		),
		$promises[1]
	);
	yield $yields[1];

	$promises[2] = new \GuzzleHttp\Promise\Promise();
	$yields[2] = new \GuzzleVCR\Record(
		new \GuzzleVCR\GuzzleRequest(
			new \GuzzleHttp\Psr7\Request(
				'GET',
				'http://example.com/',
				array (
					'Host' =>
					array (
						0 => 'example.com',
					),
				),
				'',
				'1.1'
			),
			array (
				'synchronous' => true,
				'allow_redirects' =>
				array (
					'max' => 5,
					'protocols' =>
					array (
						0 => 'http',
						1 => 'https',
					),
					'strict' => false,
					'referer' => false,
					'track_redirects' => false,
				),
				'http_errors' => true,
				'decode_content' => true,
				'verify' => true,
				'cookies' => false,
			)
		),
		$promises[2]
	);

	$promises[2]->resolve(
		new \GuzzleHttp\Psr7\Response(
			200,
			array (
				'X-Foo' =>
				array (
					0 => 'Bar1',
				),
			),
			'',
			'1.1',
			'OK'
		)
	);
	yield $yields[2];

	$promises[3] = new \GuzzleHttp\Promise\Promise();
	$yields[3] = new \GuzzleVCR\Record(
		new \GuzzleVCR\GuzzleRequest(
			new \GuzzleHttp\Psr7\Request(
				'GET',
				'http://example.com/404',
				array (
					'Host' =>
					array (
						0 => 'example.com',
					),
				),
				'',
				'1.1'
			),
			array (
				'allow_redirects' =>
				array (
					'max' => 5,
					'protocols' =>
					array (
						0 => 'http',
						1 => 'https',
					),
					'strict' => false,
					'referer' => false,
					'track_redirects' => false,
				),
				'http_errors' => true,
				'decode_content' => true,
				'verify' => true,
				'cookies' => false,
			)
		),
		$promises[3]
	);

	$promises[3]->reject(
		new \Exception(
			'test 404 exception #1',
			'42'
		)
	);

	$promises[1]->reject(
		new \Exception(
			'test 404 exception #2',
			'7'
		)
	);

	$promises[0]->resolve(
		new \GuzzleHttp\Psr7\Response(
			200,
			array (
				'X-Foo' =>
				array (
					0 => 'Bar2',
				),
			),
			'',
			'1.1',
			'OK'
		)
	);
	yield $yields[3];
};

return $fn();
