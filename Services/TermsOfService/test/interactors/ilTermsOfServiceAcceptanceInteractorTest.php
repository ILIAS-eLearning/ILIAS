<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceInteractor.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceEntity.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceRequest.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceInteractorFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceEntityFactory.php';
require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceSignableDocument.php';
/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAcceptanceInteractorTest extends PHPUnit_Framework_TestCase
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
		$interactor = new ilTermsOfServiceAcceptanceInteractor();
		$this->assertInstanceOf('ilTermsOfServiceAcceptanceInteractor', $interactor);
		$this->assertInstanceOf('ilTermsOfServiceInteractor', $interactor);
	}

	/**
	 *
	 */
	public function testInvoke()
	{
		$interactor = new ilTermsOfServiceAcceptanceInteractor();

		$entity   = $this->getMock('ilTermsOfServiceAcceptanceEntity');
		$request  = $this->getMock('ilTermsOfServiceAcceptanceRequest');
		$factory  = $this->getMock('ilTermsOfServiceEntityFactory');
		$document = $this->getMock('ilTermsOfServiceSignableDocument');

		$factory->expects($this->once())->method('getByName')->with('ilTermsOfServiceAcceptanceEntity')->will($this->returnValue($entity));

		$request->expects($this->once())->method('getEntityFactory')->will($this->returnValue($factory));
		$request->expects($this->any())->method('getDocument')->will($this->returnValue($document));

		$entity->expects($this->once())->method('setUserId');
		$entity->expects($this->once())->method('setSource');
		$entity->expects($this->once())->method('setSourceType');
		$entity->expects($this->once())->method('setSignedText');
		$entity->expects($this->once())->method('setIso2LanguageCode');
		$entity->expects($this->once())->method('setTimestamp');
		$entity->expects($this->once())->method('setHash');
		$entity->expects($this->once())->method('save');

		$interactor->invoke($request);
	}
}
