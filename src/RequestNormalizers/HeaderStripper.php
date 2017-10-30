<?php
declare(strict_types=1);

namespace GuzzleVCR\RequestNormalizers;

use GuzzleVCR\GuzzleRequest;

class HeaderStripper extends RequestNormalizer
{
	/** @var string */
	private $headerKey;

	public function __construct(string $headerKey)
	{
		$this->headerKey = $headerKey;
	}


	protected function modifyRequest(GuzzleRequest $request): GuzzleRequest
	{
		if (!$request->getRequest()->hasHeader($this->headerKey)) {
			return $request;
		}

		return new GuzzleRequest(
			$request->getRequest()->withoutHeader($this->headerKey),
			$request->getOptions()
		);
	}
}
