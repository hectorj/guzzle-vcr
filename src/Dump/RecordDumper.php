<?php
declare(strict_types=1);

namespace GuzzleVCR\Dump;

use GuzzleVCR\Record;

interface RecordDumper
{
	public function __invoke(Record $record): \GuzzleHttp\Promise\PromiseInterface;
}
