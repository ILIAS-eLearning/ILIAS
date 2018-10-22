<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilModulesForumSuite
 */
class ilModulesForumSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new self();

		require_once 'Modules/Forum/test/ilForumBbCodePurifierTest.php';
		$suite->addTestSuite('ilForumBbCodePurifierTest');

		return $suite;
	}
}
