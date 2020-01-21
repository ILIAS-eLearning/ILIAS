<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceEntityTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceEntityTest extends ilTermsOfServiceBaseTest
{
    /**
     *
     */
    public function testInstanceCanBeCreated() : void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertInstanceOf(ilTermsOfServiceAcceptanceEntity::class, $entity);
    }

    /**
     *
     */
    public function testIdIsInitiallyEmpty() : void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getId());
    }

    /**
     *
     */
    public function testUserIdIsInitiallyEmpty() : void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getUserId());
    }

    /**
     *
     */
    public function testTextIsInitiallyEmpty() : void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getText());
    }

    /**
     *
     */
    public function testTitleIsInitiallyEmpty() : void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getTitle());
    }

    /**
     *
     */
    public function testDocumentIdIsInitiallyEmpty() : void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getDocumentId());
    }

    /**
     *
     */
    public function testTimestampOfSignatureIsInitiallyEmpty() : void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getTimestamp());
    }

    /**
     *
     */
    public function testHashIsInitiallyEmpty() : void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getHash());
    }

    /**
     *
     */
    public function testCriteriaAreInitiallyEmpty() : void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getSerializedCriteria());
    }

    /**
     *
     */
    public function testEntityShouldReturnIdWhenIdIsSet() : void
    {
        $expected = 4711;

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withId($expected)->getId());
    }

    /**
     *
     */
    public function testEntityShouldReturnUserIdWhenUserIdIsSet() : void
    {
        $expected = 1337;

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withUserId($expected)->getUserId());
    }

    /**
     *
     */
    public function testEntityShouldReturnTextWhenTextIsSet() : void
    {
        $expected = 'Lorem Ipsum';

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withText($expected)->getText());
    }

    /**
     *
     */
    public function testEntityShouldReturnDocumentIdWhenDocumentIdIsSet() : void
    {
        $expected = 4711;

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withDocumentId($expected)->getDocumentId());
    }

    /**
     *
     */
    public function testEntityShouldReturnSourceTypeWhenSourceTypeIsSet() : void
    {
        $expected = 'Document PHP Unit';

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withTitle($expected)->getTitle());
    }

    /**
     *
     */
    public function testEntityShouldReturnTimestampWhenTimestampIsSet() : void
    {
        $expected = time();

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withTimestamp($expected)->getTimestamp());
    }

    /**
     *
     */
    public function testEntityShouldReturnHashWhenHashIsSet() : void
    {
        $expected = 'hash';

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withHash($expected)->getHash());
    }

    /**
     *
     */
    public function testEntityShouldReturnCriteriaWhenCriteriaAreSet() : void
    {
        $expected = 'criteria';

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEquals($expected, $entity->withSerializedCriteria($expected)->getSerializedCriteria());
    }
}
