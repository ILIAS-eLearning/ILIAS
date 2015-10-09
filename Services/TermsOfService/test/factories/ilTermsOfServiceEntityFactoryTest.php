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
		if(!defined('MDB2_AUTOQUERY_INSERT'))
		{
			define('MDB2_AUTOQUERY_INSERT', 1);
		}

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
