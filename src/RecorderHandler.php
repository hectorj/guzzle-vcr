<?php
declare(strict_types=1);

namespace GuzzleVCR;

use GuzzleVCR\Dump\RecordDumper;

final class RecorderHandler implements GuzzleHandler
{
	/** @var callable|GuzzleHandler */
	private $nextHandler;

	/** @var RecordDumper */
	private $recordDumper;

	/**
	 * @param callable|GuzzleHandler $nextHandler
	 * @param RecordDumper $recordDumper
	 */
	public function __construct(callable $nextHandler, RecordDumper $recordDumper)
	{
		$this->nextHandler = $nextHandler;
		$this->recordDumper = $recordDumper;
	}

	/**
	 * @param \Psr\Http\Message\RequestInterface $request
	 * @param array $options
	 * @return \GuzzleHttp\Promise\PromiseInterface
	 */
	public function __invoke(\Psr\Http\Message\RequestInterface $request, array $options): \GuzzleHttp\Promise\PromiseInterface
	{
		$originalRequest = new GuzzleRequest($request, $options);

		$handler = $this->nextHandler;
		$promise = $handler($request, $options);

		$dumper = $this->recordDumper;
		$promise = $dumper(new Record($originalRequest, $promise));

		return $promise;
	}
}
