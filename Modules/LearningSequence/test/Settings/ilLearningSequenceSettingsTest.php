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

use PHPUnit\Framework\TestCase;

class ilLearningSequenceSettingsTest extends TestCase
{
    public const TO_OBJ_ID = 10;
    public const TO_ABSTRACT = "abstract";
    public const TO_EXTRO = "extro";
    public const TO_ABSTRACT_IMAGE = "abstract/image/path";
    public const TO_EXTRO_IMAGE = "extro/image/path";
    public const TO_ONLINE = true;
    public const TO_MEMBERS_GALLERY = true;

    public function testCreate(): \ilLearningSequenceSettings
    {
        $object = new ilLearningSequenceSettings(
            self::TO_OBJ_ID,
            self::TO_ABSTRACT,
            self::TO_EXTRO,
            self::TO_ABSTRACT_IMAGE,
            self::TO_EXTRO_IMAGE,
            self::TO_MEMBERS_GALLERY
        );

        $this->assertEquals(self::TO_OBJ_ID, $object->getObjId());
        $this->assertEquals(self::TO_ABSTRACT, $object->getAbstract());
        $this->assertEquals(self::TO_EXTRO, $object->getExtro());
        $this->assertEquals(self::TO_ABSTRACT_IMAGE, $object->getAbstractImage());
        $this->assertEquals(self::TO_EXTRO_IMAGE, $object->getExtroImage());
        $this->assertEquals(self::TO_MEMBERS_GALLERY, $object->getMembersGallery());

        return $object;
    }

    /**
     * @depends testCreate
     */
    public function testWithAbstract(ilLearningSequenceSettings $object): void
    {
        $new_object = $object->withAbstract("teststring");

        $this->assertEquals(self::TO_OBJ_ID, $object->getObjId());
        $this->assertEquals(self::TO_ABSTRACT, $object->getAbstract());
        $this->assertEquals(self::TO_EXTRO, $object->getExtro());
        $this->assertEquals(self::TO_ABSTRACT_IMAGE, $object->getAbstractImage());
        $this->assertEquals(self::TO_EXTRO_IMAGE, $object->getExtroImage());
        $this->assertEquals(self::TO_MEMBERS_GALLERY, $object->getMembersGallery());

        $this->assertEquals(self::TO_OBJ_ID, $new_object->getObjId());
        $this->assertEquals("teststring", $new_object->getAbstract());
        $this->assertEquals(self::TO_EXTRO, $new_object->getExtro());
        $this->assertEquals(self::TO_ABSTRACT_IMAGE, $new_object->getAbstractImage());
        $this->assertEquals(self::TO_EXTRO_IMAGE, $new_object->getExtroImage());
        $this->assertEquals(self::TO_MEMBERS_GALLERY, $new_object->getMembersGallery());
    }

    /**
     * @depends testCreate
     */
    public function testWithExtro(ilLearningSequenceSettings $object): void
    {
        $new_object = $object->withExtro("teststring");

        $this->assertEquals(self::TO_OBJ_ID, $object->getObjId());
        $this->assertEquals(self::TO_ABSTRACT, $object->getAbstract());
        $this->assertEquals(self::TO_EXTRO, $object->getExtro());
        $this->assertEquals(self::TO_ABSTRACT_IMAGE, $object->getAbstractImage());
        $this->assertEquals(self::TO_EXTRO_IMAGE, $object->getExtroImage());
        $this->assertEquals(self::TO_MEMBERS_GALLERY, $object->getMembersGallery());

        $this->assertEquals(self::TO_OBJ_ID, $new_object->getObjId());
        $this->assertEquals(self::TO_ABSTRACT, $new_object->getAbstract());
        $this->assertEquals("teststring", $new_object->getExtro());
        $this->assertEquals(self::TO_ABSTRACT_IMAGE, $new_object->getAbstractImage());
        $this->assertEquals(self::TO_EXTRO_IMAGE, $new_object->getExtroImage());
        $this->assertEquals(self::TO_MEMBERS_GALLERY, $new_object->getMembersGallery());
    }

    /**
     * @depends testCreate
     */
    public function testWithAbstractImage(ilLearningSequenceSettings $object): void
    {
        $new_object = $object->withAbstractImage("teststring");

        $this->assertEquals(self::TO_OBJ_ID, $object->getObjId());
        $this->assertEquals(self::TO_ABSTRACT, $object->getAbstract());
        $this->assertEquals(self::TO_EXTRO, $object->getExtro());
        $this->assertEquals(self::TO_ABSTRACT_IMAGE, $object->getAbstractImage());
        $this->assertEquals(self::TO_EXTRO_IMAGE, $object->getExtroImage());
        $this->assertEquals(self::TO_MEMBERS_GALLERY, $object->getMembersGallery());

        $this->assertEquals(self::TO_OBJ_ID, $new_object->getObjId());
        $this->assertEquals(self::TO_ABSTRACT, $new_object->getAbstract());
        $this->assertEquals(self::TO_EXTRO, $new_object->getExtro());
        $this->assertEquals("teststring", $new_object->getAbstractImage());
        $this->assertEquals(self::TO_EXTRO_IMAGE, $new_object->getExtroImage());
        $this->assertEquals(self::TO_MEMBERS_GALLERY, $new_object->getMembersGallery());
    }

    /**
     * @depends testCreate
     */
    public function testWithExtroImage(ilLearningSequenceSettings $object): void
    {
        $new_object = $object->withExtroImage("teststring");

        $this->assertEquals(self::TO_OBJ_ID, $object->getObjId());
        $this->assertEquals(self::TO_ABSTRACT, $object->getAbstract());
        $this->assertEquals(self::TO_EXTRO, $object->getExtro());
        $this->assertEquals(self::TO_ABSTRACT_IMAGE, $object->getAbstractImage());
        $this->assertEquals(self::TO_EXTRO_IMAGE, $object->getExtroImage());
        $this->assertEquals(self::TO_MEMBERS_GALLERY, $object->getMembersGallery());

        $this->assertEquals(self::TO_OBJ_ID, $new_object->getObjId());
        $this->assertEquals(self::TO_ABSTRACT, $new_object->getAbstract());
        $this->assertEquals(self::TO_EXTRO, $new_object->getExtro());
        $this->assertEquals(self::TO_ABSTRACT_IMAGE, $new_object->getAbstractImage());
        $this->assertEquals("teststring", $new_object->getExtroImage());
        $this->assertEquals(self::TO_MEMBERS_GALLERY, $new_object->getMembersGallery());
    }

    /**
     * @depends testCreate
     */
    public function testWithMembersGallery(ilLearningSequenceSettings $object): void
    {
        $new_object = $object->withMembersGallery(false);

        $this->assertEquals(self::TO_OBJ_ID, $object->getObjId());
        $this->assertEquals(self::TO_ABSTRACT, $object->getAbstract());
        $this->assertEquals(self::TO_EXTRO, $object->getExtro());
        $this->assertEquals(self::TO_ABSTRACT_IMAGE, $object->getAbstractImage());
        $this->assertEquals(self::TO_EXTRO_IMAGE, $object->getExtroImage());
        $this->assertEquals(self::TO_MEMBERS_GALLERY, $object->getMembersGallery());

        $this->assertEquals(self::TO_OBJ_ID, $new_object->getObjId());
        $this->assertEquals(self::TO_ABSTRACT, $new_object->getAbstract());
        $this->assertEquals(self::TO_EXTRO, $new_object->getExtro());
        $this->assertEquals(self::TO_ABSTRACT_IMAGE, $new_object->getAbstractImage());
        $this->assertEquals(self::TO_EXTRO_IMAGE, $new_object->getExtroImage());
        $this->assertEquals(false, $new_object->getMembersGallery());
    }
}
