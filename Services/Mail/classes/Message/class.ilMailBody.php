<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailBody
{
	private $bodyContent;

	public function __construct(string $content, ilMailBodyPurifier $purifier)
	{
		$this->bodyContent = $purifier->purify($content);
	}

	public function getContent()
	{
		return $this->bodyContent;
	}
}
