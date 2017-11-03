<?php
declare(strict_types=1);

namespace GuzzleVCR;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleVCR\BeforeReplayHooks\RequestMatchers\FullRequestMatcher;
use GuzzleVCR\Dump\PHPRecordDumper;
use GuzzleVCR\Dump\RecordDumper;
use GuzzleVCR\RequestNormalizers\RequestNormalizer;
use GuzzleVCR\RequestNormalizers\StandardNormalizers;
use PHPUnit\Framework\TestCase;

class GuzzleVCRTestCase extends TestCase
{
	/** @var PHPRecordDumper[] */
	protected $PHPRecordDumpers = [];

	protected function tearDown()
	{
		if ($this->getTestResultObject()->wasSuccessful()) {
			foreach ($this->PHPRecordDumpers as $dumper) {
				$dumper->flush();
			}
			$this->PHPRecordDumpers = [];
		}
		parent::tearDown();
	}

	/**
	 * @param string $recordFilepath
	 * @param array $config
	 * @return Client
	 * @api
	 */
	protected function newGuzzleVCRClient(string $recordFilepath, array $config = []): GuzzleClient
	{
		$config['handler'] = self::newHandlerStack($recordFilepath, $config['handler'] ?? null);

		return new Client($config);
	}

	protected function getGuzzleVcrDumper(string $recordFilepath): RecordDumper
	{
		$dumper = new PHPRecordDumper($recordFilepath);
		$this->PHPRecordDumpers[] = $dumper;

		return $dumper;
	}

	protected function getGuzzleVcrBeforeReplayHook(): BeforeReplayHook
	{
		return new FullRequestMatcher();
	}

	protected function getGuzzleVcrNormalizer(): RequestNormalizer
	{
		return new StandardNormalizers();
	}

	private function newHandlerStack(string $recordFilepath, ?callable $handler = null): HandlerStack
	{
		if (file_exists($recordFilepath)) {
			$replayer = self::newReplayer($recordFilepath);
			return HandlerStack::create($replayer);
		}

		$stack = HandlerStack::create($handler);
		$recorder = self::newRecorder($recordFilepath);
		$stack->push($recorder);

		return $stack;
	}

	private function newReplayer(string $recordFilepath): ReplayerHandler
	{
		try {
			$records = PHPRecordDumper::loadDump($recordFilepath);
		} catch (\Throwable $e) {
			throw new GuzzleVcrError("Failed to load records from '$recordFilepath'. If you don't understand why, try to delete the file.", $e->getCode(), $e);
		}

		$hook = $this->getGuzzleVcrBeforeReplayHook();
		$normalizers = $this->getGuzzleVcrNormalizer();
		$hook = $normalizers->wrapHook($hook);

		return new ReplayerHandler($records, $hook);
	}

	private function newRecorder(string $recordFilepath): RecorderMiddleware
	{
		$dumper = $this->getGuzzleVcrDumper($recordFilepath);

		$normalizers = $this->getGuzzleVcrNormalizer();
		$dumper = $normalizers->wrapDumper($dumper);

		return new RecorderMiddleware($dumper);
	}
}
