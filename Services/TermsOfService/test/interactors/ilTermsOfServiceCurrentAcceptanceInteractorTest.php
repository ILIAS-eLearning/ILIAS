<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceCurrentAcceptanceInteractor.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceEntity.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceCurrentAcceptanceRequest.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceInteractorFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceEntityFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceCurrentAcceptanceInteractorTest extends PHPUnit_Framework_TestCase
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
		$interactor = new ilTermsOfServiceCurrentAcceptanceInteractor();
		$this->assertInstanceOf('ilTermsOfServiceCurrentAcceptanceInteractor', $interactor);
		$this->assertInstanceOf('ilTermsOfServiceInteractor', $interactor);
	}

	/**
	 *
	 */
	public function testInvoke()
	{
		$interactor = new ilTermsOfServiceCurrentAcceptanceInteractor();

		$entity  = $this->getMock('ilTermsOfServiceAcceptanceEntity');
		$request = $this->getMock('ilTermsOfServiceCurrentAcceptanceRequest');
		$entity_factory = $this->getMock('ilTermsOfServiceEntityFactory');

		$entity_factory->expects($this->once())->method('getByName')->with('ilTermsOfServiceAcceptanceEntity')->will($this->returnValue($entity));
		$request->expects($this->once())->method('getEntityFactory')->will($this->returnValue($entity_factory));

		$entity->expects($this->once())->method('loadCurrentOfUser');

		$interactor->invoke($request);
	}
}