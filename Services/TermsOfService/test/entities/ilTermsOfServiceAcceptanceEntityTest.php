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
    public function testTimestampOfSignatureIsInitiallyEmpty()
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
        $this->assertEmpty($entity->getSerializedCriteria());
    }

    /**
     *
     */
    public function testEntityShouldReturnIdWhenIdIsSet()
    {
        $expected = 4711;

        $entity = new \ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withId($expected)->getId());
    }

    /**
     *
     */
    public function testEntityShouldReturnUserIdWhenUserIdIsSet()
    {
        $expected = 1337;

        $entity = new \ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withUserId($expected)->getUserId());
    }

    /**
     *
     */
    public function testEntityShouldReturnTextWhenTextIsSet()
    {
        $expected = 'Lorem Ipsum';

        $entity = new \ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withText($expected)->getText());
    }

    /**
     *
     */
    public function testEntityShouldReturnDocumentIdWhenDocumentIdIsSet()
    {
        $expected = 4711;

        $entity = new \ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withDocumentId($expected)->getDocumentId());
    }

    /**
     *
     */
    public function testEntityShouldReturnSourceTypeWhenSourceTypeIsSet()
    {
        $expected = 'Document PHP Unit';

        $entity = new \ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withTitle($expected)->getTitle());
    }

    /**
     *
     */
    public function testEntityShouldReturnTimestampWhenTimestampIsSet()
    {
        $expected = time();

        $entity = new \ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withTimestamp($expected)->getTimestamp());
    }

    /**
     *
     */
    public function testEntityShouldReturnHashWhenHashIsSet()
    {
        $expected = 'hash';

        $entity = new \ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withHash($expected)->getHash());
    }

    /**
     *
     */
    public function testEntityShouldReturnCriteriaWhenCriteriaAreSet()
    {
        $expected = 'criteria';

        $entity = new \ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withSerializedCriteria($expected)->getSerializedCriteria());
    }
}
