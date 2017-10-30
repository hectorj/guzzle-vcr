<?php
declare(strict_types=1);

namespace GuzzleVCR;

class NonMatchingRecordError extends GuzzleVcrError
{
	/** @var GuzzleRequest */
	private $expectedRequest;

	/** @var GuzzleRequest */
	private $actualRequest;

	public function __construct(GuzzleRequest $expectedRequest, GuzzleRequest $actualRequest)
    {
        parent::__construct("Non matching record", 400, null);
		$this->expectedRequest = $expectedRequest;
		$this->actualRequest = $actualRequest;
	}

}
