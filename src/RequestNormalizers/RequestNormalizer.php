<?php
declare(strict_types=1);

namespace GuzzleVCR\RequestNormalizers;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleVCR\BeforeReplayHook;
use GuzzleVCR\Dump\RecordDumper;
use GuzzleVCR\GuzzleRequest;
use GuzzleVCR\Record;

abstract class RequestNormalizer
{
	abstract protected function modifyRequest(GuzzleRequest $request): GuzzleRequest;

	final public function wrapHook(BeforeReplayHook $innerHook): BeforeReplayHook
	{
		return new class($innerHook, function (GuzzleRequest $request): GuzzleRequest {
			return $this->modifyRequest($request);
		}) implements BeforeReplayHook {
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
		};
	}

	final public function wrapDumper(RecordDumper $innerDumper): RecordDumper
	{
		return new class($innerDumper, function (GuzzleRequest $request): GuzzleRequest {
			return $this->modifyRequest($request);
		}) implements RecordDumper {
			/** @var RecordDumper */
			private $innerDumper;

			/** @var callable */
			private $normalizer;

			public function __construct(RecordDumper $innerDumper, callable $normalizer)
			{
				$this->innerDumper = $innerDumper;
				$this->normalizer = $normalizer;
			}

			public function __invoke(Record $originalRecord): PromiseInterface
			{
				$normalizer = $this->normalizer;
				$modifiedRequest = $normalizer($originalRecord->getRequest());

				$innerDumper = $this->innerDumper;
				return $innerDumper(new Record(
					$modifiedRequest,
					$originalRecord->getPromise()
				));
			}
		};
	}
}
