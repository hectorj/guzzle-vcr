<?php
declare(strict_types=1);

namespace GuzzleVCR\RequestNormalizers;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleVCR\Dump\RecordDumper;
use GuzzleVCR\Record;

class DumperWrapper implements RecordDumper
{
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
}
