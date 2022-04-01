<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;

class ilLearningSequenceSettingsTest extends TestCase
{
    const TO_OBJ_ID = 10;
    const TO_ABSTRACT = "abstract";
    const TO_EXTRO = "extro";
    const TO_ABSTRACT_IMAGE = "abstract/image/path";
    const TO_EXTRO_IMAGE = "extro/image/path";
    const TO_ONLINE = true;
    const TO_MEMBERS_GALLERY = true;

    public function testCreate() : \ilLearningSequenceSettings
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
    public function testWithAbstract(ilLearningSequenceSettings $object) : void
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
    public function testWithExtro(ilLearningSequenceSettings $object) : void
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
    public function testWithAbstractImage(ilLearningSequenceSettings $object) : void
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
    public function testWithExtroImage(ilLearningSequenceSettings $object) : void
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
    public function testWithMembersGallery(ilLearningSequenceSettings $object) : void
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
