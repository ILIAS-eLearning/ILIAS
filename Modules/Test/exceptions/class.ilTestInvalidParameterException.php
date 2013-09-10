<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Modules/Test/exceptions/class.ilTestException.php';

/**
 * Exception for invalid parameters (e.g. an qpl id was not passed to a request showing a corresponding form)
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @ingroup ModulesTest
 */
class ilTestInvalidParameterException extends ilTestException
{
}

