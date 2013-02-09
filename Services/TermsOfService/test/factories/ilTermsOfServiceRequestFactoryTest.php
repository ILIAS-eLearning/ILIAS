<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceRequestFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceRequestFactoryTest extends PHPUnit_Framework_TestCase
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
	}

	/**
	 *
	 */
	public function testInstanceCanBeCreated()
	{
		$factory = new ilTermsOfServiceRequestFactory();
		$this->assertInstanceOf('ilTermsOfServiceRequestFactory', $factory);
		$this->assertInstanceOf('ilTermsOfServiceFactory', $factory);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionIsRaisedWhenUnknowRequestIsRequested()
	{
		$factory = new ilTermsOfServiceRequestFactory();
		$factory->getByName('PHP Unit');
	}
}