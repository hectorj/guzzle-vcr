<?php
declare(strict_types=1);

namespace GuzzleVCR\RequestNormalizers;

use GuzzleVCR\BeforeReplayHook;
use GuzzleVCR\GuzzleRequest;
use GuzzleVCR\Record;

class HookWrapper implements BeforeReplayHook
{
	/** @var BeforeReplayHook */
	private $innerHook;

	/** @var callable */
	private $normalizer;

	public function __construct(BeforeReplayHook $innerHook, callable $normalizer)
	{
		$this->innerHook = $innerHook;
		$this->normalizer = $normalizer;
	}

	/**
	 * @param Record $record
	 * @param GuzzleRequest $originalRequest
	 */
	public function __invoke(Record $record, GuzzleRequest $originalRequest): void
	{
		$normalizer = $this->normalizer;
		$modifiedRequest = $normalizer($originalRequest);

		$innerHook = $this->innerHook;
		$innerHook($record, $modifiedRequest);
	}
}
