<?php
declare(strict_types=1);

namespace GuzzleVCR;

use GuzzleVCR\Dump\RecordDumper;

final class RecorderMiddleware implements GuzzleMiddleware
{
    /** @var RecordDumper */
    private $recordDumper;

    /**
     * RecorderMiddleware constructor.
     * @param RecordDumper $recordDumper
     */
    public function __construct(RecordDumper $recordDumper)
    {
        $this->recordDumper = $recordDumper;
    }


    /**
     * @inheritdoc
     */
    public function __invoke(callable $nextHandler): GuzzleHandler
    {
        return new RecorderHandler($nextHandler, $this->recordDumper);
    }
}
