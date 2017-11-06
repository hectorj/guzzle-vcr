<?php
declare(strict_types=1);

namespace Tests;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleVCR\Dump\RecordDumper;
use GuzzleVCR\Record;

class InMemoryRecordDumper implements RecordDumper
{
	/** @var Record[] */
	private $records = [];
	/**
	 * @inheritdoc
	 */
	public function __invoke(Record $record): PromiseInterface
	{
		$this->records[] = $record;

		return $record->getPromise();
	}

	/**
	 * @return Record[]
	 */
	public function getRecords(): array
	{
		return $this->records;
	}
}
