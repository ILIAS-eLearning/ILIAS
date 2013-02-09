<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceEntity.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAcceptanceEntityTest extends PHPUnit_Framework_TestCase
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
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$this->assertInstanceOf('ilTermsOfServiceAcceptanceEntity', $entity);
		$this->assertInstanceOf('ilTermsOfServiceEntity', $entity);
	}

	/**
	 *
	 */
	public function testIdIsInitiallyEmpty()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getId());
	}

	/**
	 *
	 */
	public function testUserIdIsInitiallyEmpty()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getUserId());
	}

	/**
	 *
	 */
	public function testAcceptanceIsInitiallyEmpty()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getSignedText());
	}

	/**
	 *
	 */
	public function testPathToFileIsInitiallyEmpty()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getSource());
	}

	/**
	 *
	 */
	public function testLanguageOfAcceptanceIsInitiallyEmpty()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getIso2LanguageCode());
	}

	/**
	 *
	 */
	public function testTimestampOfSigningIsInitiallyEmpty()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getTimestamp());
	}

	/**
	 *
	 */
	public function testDataGatewayIsInitiallyEmpty()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getDataGateway());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnIdWhenIdIsSet()
	{
		$expected = 4711;

		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setId($expected);
		$this->assertEquals($expected, $entity->getId());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnUserIdWhenUserIdIsSet()
	{
		$expected = 1337;

		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setUserId($expected);
		$this->assertEquals($expected, $entity->getUserId());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnAcceptanceWhenAcceptanceIsSet()
	{
		$expected = 'Lorem Ipsum';

		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setSignedText($expected);
		$this->assertEquals($expected, $entity->getSignedText());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnPathToFileWhenSignedPathToFileIsSet()
	{
		$expected = '/path/to/file';

		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setSource($expected);
		$this->assertEquals($expected, $entity->getSource());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnLanguageWhenLanguageIsSet()
	{
		$expected = 'de';

		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setIso2LanguageCode($expected);
		$this->assertEquals($expected, $entity->getIso2LanguageCode());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnTimestampTextWhenTimestampIsSet()
	{
		$expected = time();

		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setTimestamp($expected);
		$this->assertEquals($expected, $entity->getTimestamp());
	}

	/**
	 * @expectedException ilTermsOfServiceMissingDataGatewayException
	 */
	public function testExceptionIsRaisedWhenEntityIsSavedWithAnIncompleteConfiguration()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->save();
	}

	/**
	 *
	 */
	public function testEntityIsSaved()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();

		$gateway = $this->getMock('ilTermsOfServiceAcceptanceDataGateway');
		$gateway->expects($this->once())->method('save')->with($entity);

		$entity->setDataGateway($gateway);
		$entity->save();
	}

	/**
	 *
	 */
	public function testEntityShouldReturnDataGatewayWhenDataGatewayIsSet()
	{
		$gateway = $this->getMock('ilTermsOfServiceAcceptanceDataGateway');

		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setDataGateway($gateway);
		$this->assertEquals($gateway, $entity->getDataGateway());
	}

	/**
	 *
	 */
	public function testCurrentEntityIsLoaded()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setId(4177);

		$gateway = $this->getMock('ilTermsOfServiceAcceptanceDataGateway');
		$gateway->expects($this->once())->method('loadCurrentOfUser')->with($entity);

		$entity->setDataGateway($gateway);
		$entity->loadCurrentOfUser();
	}
}
