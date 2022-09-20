<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilTermsOfServiceAcceptanceEntityTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceEntityTest extends ilTermsOfServiceBaseTest
{
    public function testInstanceCanBeCreated(): void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertInstanceOf(ilTermsOfServiceAcceptanceEntity::class, $entity);
    }

    public function testIdIsInitiallyEmpty(): void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getId());
    }

    public function testUserIdIsInitiallyEmpty(): void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getUserId());
    }

    public function testTextIsInitiallyEmpty(): void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getText());
    }

    public function testTitleIsInitiallyEmpty(): void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getTitle());
    }

    public function testDocumentIdIsInitiallyEmpty(): void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getDocumentId());
    }

    public function testTimestampOfSignatureIsInitiallyEmpty(): void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getTimestamp());
    }

    public function testHashIsInitiallyEmpty(): void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getHash());
    }

    public function testCriteriaAreInitiallyEmpty(): void
    {
        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertEmpty($entity->getSerializedCriteria());
    }

    public function testEntityShouldReturnIdWhenIdIsSet(): void
    {
        $expected = 4711;

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertSame($expected, $entity->withId($expected)->getId());
    }

    public function testEntityShouldReturnUserIdWhenUserIdIsSet(): void
    {
        $expected = 1337;

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertSame($expected, $entity->withUserId($expected)->getUserId());
    }

    public function testEntityShouldReturnTextWhenTextIsSet(): void
    {
        $expected = 'Lorem Ipsum';

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertSame($expected, $entity->withText($expected)->getText());
    }

    public function testEntityShouldReturnDocumentIdWhenDocumentIdIsSet(): void
    {
        $expected = 4711;

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertSame($expected, $entity->withDocumentId($expected)->getDocumentId());
    }

    public function testEntityShouldReturnSourceTypeWhenSourceTypeIsSet(): void
    {
        $expected = 'Document PHP Unit';

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertSame($expected, $entity->withTitle($expected)->getTitle());
    }

    public function testEntityShouldReturnTimestampWhenTimestampIsSet(): void
    {
        $expected = time();

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertSame($expected, $entity->withTimestamp($expected)->getTimestamp());
    }

    public function testEntityShouldReturnHashWhenHashIsSet(): void
    {
        $expected = 'hash';

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertSame($expected, $entity->withHash($expected)->getHash());
    }

    public function testEntityShouldReturnCriteriaWhenCriteriaAreSet(): void
    {
        $expected = 'criteria';

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $this->assertSame($expected, $entity->withSerializedCriteria($expected)->getSerializedCriteria());
    }
}
