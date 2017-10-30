<?php
declare(strict_types=1);

namespace GuzzleVCR\BeforeReplayHooks\RequestMatchers;

use GuzzleVCR\BeforeReplayHook;
use GuzzleVCR\BeforeReplayHooks\HooksCollection;

class FullRequestMatcher extends HooksCollection implements BeforeReplayHook
{
	public function __construct()
	{
		parent::__construct(
			new URIMatcher(),
			new BodyMatcher(),
			new HeadersMatcher(),
			new MethodMatcher(),
			new OptionsMatcher(),
			new ProtocolVersionMatcher()
		);
	}
}
