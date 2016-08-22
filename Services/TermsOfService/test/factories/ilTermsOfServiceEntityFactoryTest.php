<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceEntityFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceDataGatewayFactory.php';
require_once 'Services/TermsOfService/test/ilTermsOfServiceBaseTest.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceEntityFactoryTest extends ilTermsOfServiceBaseTest
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
		parent::setUp();
	}

	/**
	 *
	 */
	public function testInstanceCanBeCreated()
	{
		$factory = new ilTermsOfServiceEntityFactory();
		$this->assertInstanceOf('ilTermsOfServiceEntityFactory', $factory);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionIsRaisedWhenUnknowEntityIsRequested()
	{
		$this->assertException(InvalidArgumentException::class);
		$factory = new ilTermsOfServiceEntityFactory();
		$factory->getByName('PHP Unit');
	}

	/**
	 *
	 */
	public function testAcceptanceEntityIsReturnedWhenRequestedByName()
	{
		$factory = new ilTermsOfServiceEntityFactory();
		$this->assertInstanceOf('ilTermsOfServiceAcceptanceEntity', $factory->getByName('ilTermsOfServiceAcceptanceEntity'));
	}
}
