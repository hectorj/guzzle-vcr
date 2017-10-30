<?php
declare(strict_types=1);

namespace GuzzleVCR\BeforeReplayHooks\RequestMatchers;

use GuzzleVCR\BeforeReplayHook;
use GuzzleVCR\GuzzleRequest;
use GuzzleVCR\Record;
use PHPUnit\Framework\Assert;

class BodyMatcher implements BeforeReplayHook
{
	/**
	 * @param Record $record
	 * @param GuzzleRequest $actualRequest
	 */
	public function __invoke(Record $record, GuzzleRequest $actualRequest): void
	{
		$expectedBody = (string) $actualRequest->getRequest()->getBody();
		$actualBody = (string) $record->getRequest()->getRequest()->getBody();

		Assert::assertSame($expectedBody, $actualBody, "request's body should match records, or records should be regenerated");
	}
}
