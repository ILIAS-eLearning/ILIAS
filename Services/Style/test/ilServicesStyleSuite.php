<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */

class ilServicesStyleSuite extends TestSuite
{
	public static function suite()
	{
		$suite = new ilServicesStyleSuite();

		// add each test class of the component
		include_once("./Services/Style/System/test/ilServicesStyleSystemSuite.php");
		$suite->addTestSuite("ilServicesStyleSystemSuite");
		return $suite;
    }
}