<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceResponseFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceResponseFactoryTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var bool
	 */
	protected $backupGlobals = false;

	/**
	 *
	 */
	public function setUp()
	{
		require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';
		ilUnitUtil::performInitialisation();
	}

	/**
	 *
	 */
	public function testInstanceCanBeCreated()
	{
		$factory = new ilTermsOfServiceResponseFactory();
		$this->assertInstanceOf('ilTermsOfServiceResponseFactory', $factory);
		$this->assertInstanceOf('ilTermsOfServiceFactory', $factory);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionIsRaisedWhenUnknowResponseIsRequested()
	{
		$factory = new ilTermsOfServiceResponseFactory();
		$factory->getByName('PHP Unit');
	}
}
