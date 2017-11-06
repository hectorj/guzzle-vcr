<?php
declare(strict_types=1);

namespace GuzzleVCR;

use GuzzleHttp\Promise\PromiseInterface;

final class Record
{
	/** @var GuzzleRequest */
	private $request;

	/** @var PromiseInterface */
	private $promise;

	/**
	 * @param GuzzleRequest $request
	 * @param PromiseInterface $promise
	 */
	public function __construct(GuzzleRequest $request, PromiseInterface $promise)
	{
		$this->request = $request;
		$this->promise = $promise;
	}

	public function getRequest(): GuzzleRequest
	{
		return $this->request;
	}

	public function getPromise(): PromiseInterface
	{
		return $this->promise;
	}
}
