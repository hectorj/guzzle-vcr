<?php
declare(strict_types=1);

namespace GuzzleVCR\BeforeReplayHooks\RequestMatchers;

use GuzzleVCR\BeforeReplayHook;
use GuzzleVCR\GuzzleRequest;
use GuzzleVCR\Record;
use PHPUnit\Framework\Assert;

class MethodMatcher implements BeforeReplayHook
{
	/**
	 * @param Record $record
	 * @param GuzzleRequest $actualRequest
	 */
	public function __invoke(Record $record, GuzzleRequest $actualRequest): void
	{
		$expectedMethod = $actualRequest->getRequest()->getMethod();
		$actualMethod = $record->getRequest()->getRequest()->getMethod();

		Assert::assertSame($expectedMethod, $actualMethod, "request's method should match records, or records should be regenerated");
	}
}
