<?php
declare(strict_types=1);

namespace GuzzleVCR;

interface BeforeReplayHook
{
	/**
	 * @param Record $record
	 * @param GuzzleRequest $actualRequest
	 */
	public function __invoke(Record $record, GuzzleRequest $actualRequest): void;
}
