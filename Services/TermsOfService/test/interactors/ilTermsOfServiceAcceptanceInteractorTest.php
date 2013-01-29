<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceInteractor.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceEntity.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceRequest.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceInteractorFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceEntityFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceResponseFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceEntityFactory.php';

require_once 'vfsStream/vfsStream.php';

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

		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot(new vfsStreamDirectory('phpunit'));
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
		vfsStream::newFile('agreement_de.html', 644)->at(vfsStreamWrapper::getRoot());
		$expected_path = vfsStream::url('phpunit/agreement_de.html');
		$expected_content = 'phpunit';
		file_put_contents($expected_path, $expected_content);

		$interactor = new ilTermsOfServiceAcceptanceInteractor();

		$entity  = $this->getMock('ilTermsOfServiceAcceptanceEntity');
		$request = $this->getMock('ilTermsOfServiceAcceptanceRequest');
		$factory = $this->getMock('ilTermsOfServiceEntityFactory');

		$factory->expects($this->once())->method('getByName')->with('ilTermsOfServiceAcceptanceEntity')->will($this->returnValue($entity));
		$request->expects($this->once())->method('getEntityFactory')->will($this->returnValue($factory));
		$request->expects($this->any())->method('getPathToFile')->will($this->returnValue($expected_path));

		$entity->expects($this->once())->method('setUserId');
		$entity->expects($this->once())->method('setPathToFile');
		$entity->expects($this->once())->method('setSignedText');
		$entity->expects($this->once())->method('setLanguage');
		$entity->expects($this->once())->method('setTimestamp');
		$entity->expects($this->once())->method('setHash');
		$entity->expects($this->once())->method('save');

		$interactor->invoke($request);
	}
}
