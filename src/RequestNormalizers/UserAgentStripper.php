<?php
declare(strict_types=1);

namespace GuzzleVCR\RequestNormalizers;

class UserAgentStripper extends HeaderStripper
{
	public function __construct()
	{
		parent::__construct('User-Agent');
	}
}
