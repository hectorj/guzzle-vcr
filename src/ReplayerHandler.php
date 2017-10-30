<?php
declare(strict_types=1);

namespace GuzzleVCR;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

final class ReplayerHandler implements GuzzleHandler
{
    /** @var \Iterator|Record[] */
    private $records;

    /** @var BeforeReplayHook[] */
    private $beforeReplayHooks = [];

	/**
	 * ReplayerHandler constructor.
	 * @param iterable|Record[] $records
	 * @param BeforeReplayHook|BeforeReplayHook[] ...$beforeReplayHooks
	 * @throws \Error
	 */
    public function __construct(iterable $records, BeforeReplayHook ...$beforeReplayHooks)
    {
    	if (!$records instanceof \Iterator) {
			if (is_array($records)) {
				$records = new \ArrayIterator($records);
			} elseif ($records instanceof \Traversable) {
				$records = new \IteratorIterator($records);
			} else {
				throw new \Error("We did not anticipate this iterable type (please report it): ".get_class($records) ?: gettype($records));
			}
		}

        $this->records = $records;
        $this->records->rewind();

        $this->beforeReplayHooks = $beforeReplayHooks;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(RequestInterface $request, array $options): PromiseInterface
    {
		if (!$this->records->valid()) {
			throw new MissingRecordError();
		}
		$record = $this->records->current();
		$this->records->next();

		$actualGuzzleRequest = new GuzzleRequest($request, $options);
		foreach($this->beforeReplayHooks as $hook) {
			$hook($record, $actualGuzzleRequest);
		}

        return $record->getPromise();
    }
}
