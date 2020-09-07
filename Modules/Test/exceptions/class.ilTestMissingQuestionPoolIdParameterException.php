<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Modules/Test/exceptions/class.ilTestException.php';

/**
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @ingroup ModulesTest
 */
class ilTestMissingQuestionPoolIdParameterException extends ilTestException
{
    public function __construct()
    {
        parent::__construct('', 0);
    }
}
