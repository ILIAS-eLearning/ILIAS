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
	}

	/**
	 *
	 */
	public function testInstanceCanBeCreated()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$this->assertInstanceOf('ilTermsOfServiceAcceptanceEntity', $entity);
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
	public function testTextIsInitiallyEmpty()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getText());
	}

	/**
	 *
	 */
	public function testSourceIsInitiallyEmpty()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getSource());
	}

	/**
	 *
	 */
	public function testSourceTypeIsInitiallyEmpty()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getSourceType());
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
	public function testHashIsInitiallyEmpty()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getHash());
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
	public function testEntityShouldReturnTextWhenTextIsSet()
	{
		$expected = 'Lorem Ipsum';

		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setText($expected);
		$this->assertEquals($expected, $entity->getText());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnSourceWhenSourceIsSet()
	{
		$expected = '/path/to/file';

		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setSource($expected);
		$this->assertEquals($expected, $entity->getSource());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnSourceTypeWhenSourceTypeIsSet()
	{
		$expected = 1;

		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setSourceType($expected);
		$this->assertEquals($expected, $entity->getSourceType());
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
	public function testEntityShouldReturnTimestampWhenTimestampIsSet()
	{
		$expected = time();

		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setTimestamp($expected);
		$this->assertEquals($expected, $entity->getTimestamp());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnHashWhenHashIsSet()
	{
		$expected = 'hash';

		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setHash($expected);
		$this->assertEquals($expected, $entity->getHash());
	}
}
