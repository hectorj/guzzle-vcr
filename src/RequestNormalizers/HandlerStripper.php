<?php
declare(strict_types=1);

namespace GuzzleVCR\RequestNormalizers;

use GuzzleVCR\GuzzleRequest;

class HandlerStripper extends RequestNormalizer
{
	protected function modifyRequest(GuzzleRequest $request): GuzzleRequest
	{
		$options = $request->getOptions();
		unset($options['handler']);

		return new GuzzleRequest(
			$request->getRequest(),
			$options
		);
	}
}
