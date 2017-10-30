<?php
declare(strict_types=1);

namespace GuzzleVCR;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

interface GuzzleHandler
{
    /**
     * @param RequestInterface $request
     * @param array $options {@link http://docs.guzzlephp.org/en/stable/request-options.html}
     * @return PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $options): PromiseInterface;
}
