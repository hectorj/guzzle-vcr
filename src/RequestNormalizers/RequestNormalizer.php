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
		return new HookWrapper($innerHook, function (GuzzleRequest $request) : GuzzleRequest {
			return $this->modifyRequest($request);
		});
	}

	final public function wrapDumper(RecordDumper $innerDumper): RecordDumper
	{
		return new DumperWrapper($innerDumper, function (GuzzleRequest $request) : GuzzleRequest {
			return $this->modifyRequest($request);
		});
	}
}
