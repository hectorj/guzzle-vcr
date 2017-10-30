<?php
declare(strict_types=1);

namespace GuzzleVCR\BeforeReplayHooks\RequestMatchers;

use GuzzleVCR\BeforeReplayHook;
use GuzzleVCR\GuzzleRequest;
use GuzzleVCR\Record;
use PHPUnit\Framework\Assert;

class ProtocolVersionMatcher implements BeforeReplayHook
{
	/**
	 * @param Record $record
	 * @param GuzzleRequest $actualRequest
	 */
	public function __invoke(Record $record, GuzzleRequest $actualRequest): void
	{
		$expectedVersion = $actualRequest->getRequest()->getProtocolVersion();
		$actualVersion = $record->getRequest()->getRequest()->getProtocolVersion();

		Assert::assertSame($expectedVersion, $actualVersion, "request's protocol version should match records, or records should be regenerated");
	}
}
