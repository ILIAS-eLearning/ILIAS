<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceCurrentAcceptanceInteractor.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceEntity.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceCurrentAcceptanceRequest.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceCurrentAcceptanceResponse.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceInteractorFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceEntityFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceResponseFactory.php';

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
		require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';
		ilUnitUtil::performInitialisation();
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
		$response = $this->getMock('ilTermsOfServiceCurrentAcceptanceResponse');
		$entity_factory = $this->getMock('ilTermsOfServiceEntityFactory');
		$response_factory = $this->getMock('ilTermsOfServiceResponseFactory');

		$entity_factory->expects($this->once())->method('getByName')->with('ilTermsOfServiceAcceptanceEntity')->will($this->returnValue($entity));
		$response_factory->expects($this->once())->method('getByName')->with('ilTermsOfServiceCurrentAcceptanceResponse')->will($this->returnValue($response));
		$request->expects($this->once())->method('getEntityFactory')->will($this->returnValue($entity_factory));
		$request->expects($this->once())->method('getResponseFactory')->will($this->returnValue($response_factory));

		$entity->expects($this->once())->method('loadCurrentOfUser');

		$interactor->invoke($request);
	}
}