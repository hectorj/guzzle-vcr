<?php
declare(strict_types=1);

namespace GuzzleVCR\Dump;

use GuzzleHttp\Promise\Promise;
use GuzzleVCR\Record;
use Psr\Http\Message\ResponseInterface;

class PHPRecordDumper implements RecordDumper
{
	/** @var string */
	private $filepath;

	/** @var string[] */
	private $lines = [];

	/** @var int */
	private $indentationLevel = 0;

	/** @var int */
	private $recordIdAutoIncrement = 0;

	public function __construct(string $filepath)
	{
		$this->filepath = $filepath;

		$this->addLines([
			'<?php',
			'',
			'$fn = function () : \Generator {',
		], 1);
		$this->addLines([
			'$yields = [];',
			'$promises = [];',
		]);
	}


	public function __invoke(Record $record): \GuzzleHttp\Promise\PromiseInterface
	{
		$recordId = $this->recordIdAutoIncrement;
		$this->recordIdAutoIncrement++;
		$headersString = $this->varExport($record->getRequest()->getRequest()->getHeaders());

		$options = $record->getRequest()->getOptions();
		unset($options['handler']);
		$optionsString = $this->varExport($options);

		////
//        $promises = [];
//        function () use (&$promises, $recordId, $headersString, $optionsString, $record): \Generator {
//            $promises[$recordId] = new \GuzzleHttp\Promise\Promise();
//            yield new \GuzzleVCR\Record(
//                new \GuzzleVCR\GuzzleRequest(
//                    new \GuzzleHttp\Psr7\Request(
//                        $record->getRequest()->getRequest()->getMethod(),
//                        $record->getRequest()->getRequest()->getUri(),
//                        $headersString,
//                        $record->getRequest()->getRequest()->getBody()->getContents(),
//                        $record->getRequest()->getRequest()->getProtocolVersion()
//                    ),
//                    $optionsString
//                ),
//                $promises[$recordId]
//            );
//        }
		////

		if ($recordId > 0) {
			$this->addLines([
				'yield $yields['.($recordId-1).'];'
			]);
		}
		$this->addLines([
			'',
			'$promises['.$recordId.'] = new \GuzzleHttp\Promise\Promise();',
			'$yields['.$recordId.'] = new \GuzzleVCR\Record(',
		], 1);
		$this->addLines([
			"new \GuzzleVCR\GuzzleRequest(",
		], 1);
		$this->addLines([
			"new \GuzzleHttp\Psr7\Request(",
		], 1);
		$this->addLines([
			"'{$record->getRequest()->getRequest()->getMethod()}',",
			"'{$record->getRequest()->getRequest()->getUri()}',",
			"{$headersString},",
			"'{$this->escape($record->getRequest()->getRequest()->getBody()->getContents())}',",
			"'{$record->getRequest()->getRequest()->getProtocolVersion()}'",
		], -1);
		$this->addLines([
			"),",
			"{$optionsString}",
		], -1);
		$this->addLines([
			"),",
			"\$promises[${recordId}]",
		], -1);
		$this->addLines([
			');',
		]);

		$originalPromise = $record->getPromise();
		$originalPromise->then(function (ResponseInterface $response) use ($recordId) : void {
			$headersString = $this->varExport($response->getHeaders());

//            $reponse = new \GuzzleHttp\Psr7\Response(
//                $response->getStatusCode(),
//                $headersString,
//                $response->getBody()->getContents(),
//                $response->getProtocolVersion(),
//                $response->getReasonPhrase()
//            );

			$this->addLines([
				'',
				'$promises['.$recordId.']->resolve(',
			], 1);
			$this->addLines([
				"new \GuzzleHttp\Psr7\Response(",
			], 1);
			$this->addLines([
				"{$response->getStatusCode()},",
				"{$headersString},",
				"'{$this->escape($response->getBody()->getContents())}',",
				"'{$response->getProtocolVersion()}',",
				"'{$this->escape($response->getReasonPhrase())}'",
			], -1);
			$this->addLines([
				")",
			], -1);
			$this->addLines([
				');',
			]);
		}, function (\Throwable $e) use ($recordId) : void {
//            $exception = new \Exception(
//                $e->getMessage(),
//                $e->getCode()
//            );

			// @TODO : reproduce the exception's type, as all \Throwable aren't equals
			// @TODO (perhaps) : reproduce the exception's file, line, etc... ?
			// @TODO (perhaps) : reproduce the exception's previous ?
			$this->addLines([
				'',
				'$promises['.$recordId.']->reject(',
			], 1);
			$this->addLines([
				"new \Exception(",
			], 1);
			$this->addLines([
				"'{$e->getMessage()}',",
				"'{$e->getCode()}'",
			], -1);
			$this->addLines([
				")",
			], -1);
			$this->addLines([
				');',
			]);
		});

		$promise = new Promise();

		$promise->resolve($originalPromise);

		return $promise;
	}

	public function flush()
	{
		$this->addLines([
			'yield $yields['.($this->recordIdAutoIncrement-1).'];'
		], -1);
		$this->addLines([
			'};',
			'',
			'return $fn();',
			'',
		]);

		\assert($this->indentationLevel === 0);

		$dirname = dirname($this->filepath);
		if (!file_exists($dirname)) {
			mkdir($dirname, 0777, true);
		}
		file_put_contents($this->filepath, implode("\n", $this->lines));
	}

	/**
	 * @param string $filePath
	 * @return iterable|Record[]
	 */
	public static function loadDump(string $filePath): iterable
	{
		return require($filePath);
	}

	private function escape(string $str): string
	{
		return str_replace("'", "\\'", $str);
	}

	private function addLines(array $lines, int $indentationChange = 0): void
	{
		$this->lines = array_merge($this->lines, array_map([$this, 'indentString'], $lines));

		$this->indentationLevel += $indentationChange;
	}

	private function indentString(string $str): string
	{
		$indentationChars = str_repeat("\t", $this->indentationLevel);

		return $this->removeTrailingWhitespace($indentationChars . str_replace("\n", "\n".$indentationChars, $str));
	}

	private function removeTrailingWhitespace($code)
	{
		$lines = explode("\n", $code);
		$lines = array_map('rtrim', $lines);
		return implode("\n", $lines);
	}

	private function varExport(array $array): string
	{
		return str_replace('  ', "\t", var_export($array, true));
	}
}
