<?php
declare(strict_types=1);

namespace GuzzleVCR;

interface GuzzleMiddleware
{
    /**
     * @param callable|GuzzleHandler $nextHandler
     * @return GuzzleHandler
     */
    public function __invoke(callable $nextHandler): GuzzleHandler;
}
