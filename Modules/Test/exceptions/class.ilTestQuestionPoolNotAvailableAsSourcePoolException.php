<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Modules/Test/exceptions/class.ilTestException.php';

/**
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id: class.ilTestMissingQuestionPoolIdParameterException.php 44690 2013-09-10 13:38:03Z bheyser $
 *
 * @ingroup ModulesTest
 */
class ilTestQuestionPoolNotAvailableAsSourcePoolException extends ilTestException
{
	public function __construct()
	{
		parent::__construct('', 0);
	}
}

