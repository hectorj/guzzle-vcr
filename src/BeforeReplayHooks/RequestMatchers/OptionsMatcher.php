<?php
declare(strict_types=1);

namespace GuzzleVCR\BeforeReplayHooks\RequestMatchers;

use GuzzleVCR\BeforeReplayHook;
use GuzzleVCR\GuzzleRequest;
use GuzzleVCR\Record;
use PHPUnit\Framework\Assert;

class OptionsMatcher implements BeforeReplayHook
{
	/**
	 * @param Record $record
	 * @param GuzzleRequest $actualRequest
	 */
	public function __invoke(Record $record, GuzzleRequest $actualRequest): void
	{
		$expectedOptions = $record->getRequest()->getOptions();
		$actualOptions = $actualRequest->getOptions();

		Assert::assertSame($expectedOptions, $actualOptions, "request's options should match records, or records should be regenerated");
	}
}
