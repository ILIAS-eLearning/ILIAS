<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceEntityTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceEntityTest extends \ilTermsOfServiceBaseTest
{
	/**
	 *
	 */
	public function testInstanceCanBeCreated()
	{
		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$this->assertInstanceOf(\ilTermsOfServiceAcceptanceEntity::class, $entity);
	}

	/**
	 *
	 */
	public function testIdIsInitiallyEmpty()
	{
		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getId());
	}

	/**
	 *
	 */
	public function testUserIdIsInitiallyEmpty()
	{
		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getUserId());
	}

	/**
	 *
	 */
	public function testTextIsInitiallyEmpty()
	{
		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getText());
	}

	/**
	 *
	 */
	public function testTitleIsInitiallyEmpty()
	{
		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getTitle());
	}

	/**
	 *
	 */
	public function testDocumentIdIsInitiallyEmpty()
	{
		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getDocumentId());
	}

	/**
	 *
	 */
	public function testTimestampOfSigningIsInitiallyEmpty()
	{
		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getTimestamp());
	}

	/**
	 *
	 */
	public function testHashIsInitiallyEmpty()
	{
		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getHash());
	}

	/**
	 *
	 */
	public function testCriteriaAreInitiallyEmpty()
	{
		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$this->assertEmpty($entity->getCriteria());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnIdWhenIdIsSet()
	{
		$expected = 4711;

		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$entity->setId($expected);
		$this->assertEquals($expected, $entity->getId());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnUserIdWhenUserIdIsSet()
	{
		$expected = 1337;

		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$entity->setUserId($expected);
		$this->assertEquals($expected, $entity->getUserId());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnTextWhenTextIsSet()
	{
		$expected = 'Lorem Ipsum';

		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$entity->setText($expected);
		$this->assertEquals($expected, $entity->getText());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnDocumentIdWhenDocumentIdIsSet()
	{
		$expected = 4711;

		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$entity->setDocumentId($expected);
		$this->assertEquals($expected, $entity->getDocumentId());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnSourceTypeWhenSourceTypeIsSet()
	{
		$expected = 'Document PHP Unit';

		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$entity->setTitle($expected);
		$this->assertEquals($expected, $entity->getTitle());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnTimestampWhenTimestampIsSet()
	{
		$expected = time();

		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$entity->setTimestamp($expected);
		$this->assertEquals($expected, $entity->getTimestamp());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnHashWhenHashIsSet()
	{
		$expected = 'hash';

		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$entity->setHash($expected);
		$this->assertEquals($expected, $entity->getHash());
	}

	/**
	 *
	 */
	public function testEntityShouldReturnCriteriaWhenCriteriaAreSet()
	{
		$expected = 'criteria';

		$entity = new \ilTermsOfServiceAcceptanceEntity();
		$entity->setCriteria($expected);
		$this->assertEquals($expected, $entity->getCriteria());
	}
}
