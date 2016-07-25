<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceEntityFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceDataGatewayFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceEntityFactoryTest extends PHPUnit_Framework_TestCase
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
	 *
	 */
	public function testExceptionIsRaisedWhenUnknowEntityIsRequested()
	{
		$this->expectException(InvalidArgumentException::class);
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
