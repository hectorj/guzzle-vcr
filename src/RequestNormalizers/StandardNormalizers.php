<?php
declare(strict_types=1);

namespace GuzzleVCR\RequestNormalizers;

class StandardNormalizers extends RequestNormalizersCollection
{
	public function __construct()
	{
		parent::__construct(
			new HandlerStripper(),
			new UserAgentStripper()
		);
	}
}
