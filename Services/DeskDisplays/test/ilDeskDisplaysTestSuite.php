<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Database/classes/class.ilDB.php';
require_once 'Services/Language/classes/class.ilLanguage.php';
require_once 'Services/Logging/classes/class.ilLog.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilDeskDisplaysTestSuite extends PHPUnit_Framework_TestSuite
{
	/**
	 * @return ilDeskDisplaysTestSuite
	 */
	public static function suite()
	{
		$suite = new self();

		require_once 'Services/DeskDisplays/test/service/ilDeskDisplayTest.php';
		$suite->addTestSuite('ilDeskDisplayTest');

		return $suite;
	}
}
