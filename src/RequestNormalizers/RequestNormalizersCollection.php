<?php
declare(strict_types=1);

namespace GuzzleVCR\RequestNormalizers;

use GuzzleVCR\GuzzleRequest;

class RequestNormalizersCollection extends RequestNormalizer
{
	/** @var RequestNormalizer[] */
	private $normalizers;

	public function __construct(RequestNormalizer ...$normalizers)
	{
		$this->normalizers = $normalizers;
	}

	protected function modifyRequest(GuzzleRequest $request): GuzzleRequest
	{
		foreach ($this->normalizers as $normalizer) {
			$request = $normalizer->modifyRequest($request);
		}

		return $request;
	}
}
