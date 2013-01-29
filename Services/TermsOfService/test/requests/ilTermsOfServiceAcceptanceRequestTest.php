<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceRequest.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAcceptanceRequestTest extends PHPUnit_Framework_TestCase
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
		$request = new ilTermsOfServiceAcceptanceRequest();
		$this->assertInstanceOf('ilTermsOfServiceAcceptanceRequest', $request);
		$this->assertInstanceOf('ilTermsOfServiceBaseRequest', $request);
		$this->assertInstanceOf('ilTermsOfServiceRequest', $request);
	}

	/**
	 *
	 */
	public function testUserIdIsInitiallyEmpty()
	{
		$request = new ilTermsOfServiceAcceptanceRequest();
		$this->assertEmpty($request->getUserId());
	}

	/**
	 *
	 */
	public function testPathToFileIsInitiallyEmpty()
	{
		$request = new ilTermsOfServiceAcceptanceRequest();
		$this->assertEmpty($request->getPathToFile());
	}

	/**
	 *
	 */
	public function testTimestampOfSigningIsInitiallyEmpty()
	{
		$request = new ilTermsOfServiceAcceptanceRequest();
		$this->assertEmpty($request->getTimestamp());
	}

	/**
	 *
	 */
	public function testEntityFactoryIsInitiallyEmpty()
	{
		$request = new ilTermsOfServiceAcceptanceRequest();
		$this->assertEmpty($request->getEntityFactory());
	}

	/**
	 *
	 */
	public function testRequestShouldReturnUserIdWhenUserIdIsSet()
	{
		$exptected = 1337;

		$request = new ilTermsOfServiceAcceptanceRequest();
		$request->setUserId($exptected);
		$this->assertEquals($exptected, $request->getUserId());
	}

	/**
	 *
	 */
	public function testRequestShouldReturnPathToFileWhenSignedPathToFileIsSet()
	{
		$exptected = '/path/to/file';

		$request = new ilTermsOfServiceAcceptanceRequest();
		$request->setPathToFile($exptected);
		$this->assertEquals($exptected, $request->getPathToFile());
	}

	/**
	 *
	 */
	public function testRequestShouldReturnTimestampTextWhenTimestampIsSet()
	{
		$exptected = time();

		$request = new ilTermsOfServiceAcceptanceRequest();
		$request->setTimestamp($exptected);
		$this->assertEquals($exptected, $request->getTimestamp());
	}

	/**
	 *
	 */
	public function testRequestShouldReturnEntityFactoryWhenEntityFactoryIsSet()
	{
		$request = new ilTermsOfServiceAcceptanceRequest();
		$factory = $this->getMock('ilTermsOfServiceEntityFactory');
		$request->setEntityFactory($factory);
		$this->assertEquals($factory, $request->getEntityFactory());
	}
}
