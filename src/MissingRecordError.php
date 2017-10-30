<?php
declare(strict_types=1);

namespace GuzzleVCR;

class MissingRecordError extends GuzzleVcrError
{
    public function __construct($message = "Missing record", \Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }

}
