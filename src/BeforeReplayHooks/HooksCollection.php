<?php
declare(strict_types=1);

namespace GuzzleVCR\BeforeReplayHooks;

use GuzzleVCR\BeforeReplayHook;
use GuzzleVCR\GuzzleRequest;
use GuzzleVCR\Record;

class HooksCollection implements BeforeReplayHook
{
	/** @var BeforeReplayHook[] */
	private $hooks = [];

	public function __construct(BeforeReplayHook ...$hooks)
	{
		$this->hooks = $hooks;
	}

	/**
	 * @param Record $record
	 * @param GuzzleRequest $actualRequest
	 */
	public function __invoke(Record $record, GuzzleRequest $actualRequest): void
	{
		foreach ($this->hooks as $hook) {
			$hook($record, $actualRequest);
		}
	}
}
