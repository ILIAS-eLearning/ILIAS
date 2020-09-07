<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Exceptions/classes/class.ilException.php';

/**
 * Base Exception for all Exceptions relating to Modules/Test.
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @ingroup ModulesTest
 */
class ilTestException extends ilException
{
    public function __construct($msg = '', $code = 0)
    {
        if (!strlen($msg)) {
            $msg = get_class($this);
        }

        parent::__construct($msg, $code);
    }
}
