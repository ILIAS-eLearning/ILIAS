<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceInteractorFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceInteractorFactoryTest extends PHPUnit_Framework_TestCase
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
		$factory = new ilTermsOfServiceInteractorFactory();
		$this->assertInstanceOf('ilTermsOfServiceInteractorFactory', $factory);
		$this->assertInstanceOf('ilTermsOfServiceFactory', $factory);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionIsRaisedWhenUnknowInteractorIsRequested()
	{
		$factory = new ilTermsOfServiceInteractorFactory();
		$factory->getByName('PHP Unit');
	}
}