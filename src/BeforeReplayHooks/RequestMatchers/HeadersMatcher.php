<?php
declare(strict_types=1);

namespace GuzzleVCR\BeforeReplayHooks\RequestMatchers;

use GuzzleVCR\BeforeReplayHook;
use GuzzleVCR\GuzzleRequest;
use GuzzleVCR\Record;
use PHPUnit\Framework\Assert;

class HeadersMatcher implements BeforeReplayHook
{
	/**
	 * @param Record $record
	 * @param GuzzleRequest $actualRequest
	 */
	public function __invoke(Record $record, GuzzleRequest $actualRequest): void
	{
		$expectedHeaders = $record->getRequest()->getRequest()->getHeaders();
		$actualHeaders = $actualRequest->getRequest()->getHeaders();

		Assert::assertSame($expectedHeaders, $actualHeaders, "request's headers should match records, or records should be regenerated");
	}
}
