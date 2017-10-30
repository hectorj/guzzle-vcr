<?php
declare(strict_types=1);

namespace GuzzleVCR\BeforeReplayHooks\RequestMatchers;

use GuzzleVCR\BeforeReplayHook;
use GuzzleVCR\GuzzleRequest;
use GuzzleVCR\Record;
use PHPUnit\Framework\Assert;

class URIMatcher implements BeforeReplayHook
{
	/**
	 * @param Record $record
	 * @param GuzzleRequest $actualRequest
	 */
	public function __invoke(Record $record, GuzzleRequest $actualRequest): void
	{
		$expectedURI = (string) $actualRequest->getRequest()->getUri();
		$actualURI = (string) $record->getRequest()->getRequest()->getUri();

		Assert::assertSame($expectedURI, $actualURI, "request's URI should match records, or records should be regenerated");
	}
}
