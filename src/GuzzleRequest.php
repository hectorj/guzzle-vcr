<?php
declare(strict_types=1);

namespace GuzzleVCR;

use Psr\Http\Message\RequestInterface;

class GuzzleRequest
{
	/** @var RequestInterface */
	private $request;

	/** @var array */
	private $options;

	/**
	 * GuzzleRequest constructor.
	 * @param RequestInterface $request
	 * @param array $options
	 */
	public function __construct(RequestInterface $request, array $options)
	{
		$this->request = $request;
		$this->options = $options;
	}

	public function getRequest(): RequestInterface
	{
		return clone $this->request;
	}

	public function getOptions(): array
	{
		return $this->options;
	}
}
