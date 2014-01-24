<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Utilities-Tests Suite
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/Utilities
 */
class ilUtilitiesSuite extends PHPUnit_Framework_TestSuite
{
	/**
	 * @static
	 * @return ilUtilitiesSuite
	 */
	public static function suite()
	{
		$suite = new ilServicesWorkflowEngineSuite();

		require_once './Services/Utilities/test/class.ilBitmaskTest.php';
		$suite->addTestSuite('ilBitmaskTest');

		return $suite;
	}
}