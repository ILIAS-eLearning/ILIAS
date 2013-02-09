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
	public function testDocumentIsInitiallyEmpty()
	{
		$request = new ilTermsOfServiceAcceptanceRequest();
		$this->assertEmpty($request->getDocument());
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
		$expected = 1337;

		$request = new ilTermsOfServiceAcceptanceRequest();
		$request->setUserId($expected);
		$this->assertEquals($expected, $request->getUserId());
	}

	/**
	 *
	 */
	public function testRequestShouldReturnDocumentWhenDocumentIsSet()
	{
		$expected = $this->getMock('ilTermsOfServiceSignableDocument');

		$request = new ilTermsOfServiceAcceptanceRequest();
		$request->setDocument($expected);
		$this->assertEquals($expected, $request->getDocument());
	}

	/**
	 *
	 */
	public function testRequestShouldReturnTimestampTextWhenTimestampIsSet()
	{
		$expected = time();

		$request = new ilTermsOfServiceAcceptanceRequest();
		$request->setTimestamp($expected);
		$this->assertEquals($expected, $request->getTimestamp());
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
