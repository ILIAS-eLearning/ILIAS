<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class LSSettingsTest extends TestCase
{
    const TO_OBJ_ID = 10;
    const TO_ABSTRACT = "abstract";
    const TO_EXTRO = "extro";
    const TO_ABSTRACT_IMAGE = "abstract/image/path";
    const TO_EXTRO_IMAGE = "extro/image/path";
    const TO_ONLINE = true;
    const TO_MEMBERS_GALLERY = true;

    public function testCreate()
    {
        $object = new ilLearningSequenceSettings(
            self::TO_OBJ_ID,
            self::TO_ABSTRACT,
            self::TO_EXTRO,
            self::TO_ABSTRACT_IMAGE,
            self::TO_EXTRO_IMAGE,
            self::TO_ONLINE,
            self::TO_MEMBERS_GALLERY
        );

        $this->assertEquals($object->getObjId(), self::TO_OBJ_ID);
        $this->assertEquals($object->getAbstract(), self::TO_ABSTRACT);
        $this->assertEquals($object->getExtro(), self::TO_EXTRO);
        $this->assertEquals($object->getAbstractImage(), self::TO_ABSTRACT_IMAGE);
        $this->assertEquals($object->getExtroImage(), self::TO_EXTRO_IMAGE);
        $this->assertEquals($object->getMembersGallery(), self::TO_MEMBERS_GALLERY);

        return $object;
    }

    /**
     * @depends testCreate
     */
    public function testWithAbstract(ilLearningSequenceSettings $object)
    {
        $new_object = $object->withAbstract("teststring");

        $this->assertEquals($object->getObjId(), self::TO_OBJ_ID);
        $this->assertEquals($object->getAbstract(), self::TO_ABSTRACT);
        $this->assertEquals($object->getExtro(), self::TO_EXTRO);
        $this->assertEquals($object->getAbstractImage(), self::TO_ABSTRACT_IMAGE);
        $this->assertEquals($object->getExtroImage(), self::TO_EXTRO_IMAGE);
        $this->assertEquals($object->getMembersGallery(), self::TO_MEMBERS_GALLERY);

        $this->assertEquals($new_object->getObjId(), self::TO_OBJ_ID);
        $this->assertEquals($new_object->getAbstract(), "teststring");
        $this->assertEquals($new_object->getExtro(), self::TO_EXTRO);
        $this->assertEquals($new_object->getAbstractImage(), self::TO_ABSTRACT_IMAGE);
        $this->assertEquals($new_object->getExtroImage(), self::TO_EXTRO_IMAGE);
        $this->assertEquals($new_object->getMembersGallery(), self::TO_MEMBERS_GALLERY);
    }

    /**
     * @depends testCreate
     */
    public function testWithExtro(ilLearningSequenceSettings $object)
    {
        $new_object = $object->withExtro("teststring");

        $this->assertEquals($object->getObjId(), self::TO_OBJ_ID);
        $this->assertEquals($object->getAbstract(), self::TO_ABSTRACT);
        $this->assertEquals($object->getExtro(), self::TO_EXTRO);
        $this->assertEquals($object->getAbstractImage(), self::TO_ABSTRACT_IMAGE);
        $this->assertEquals($object->getExtroImage(), self::TO_EXTRO_IMAGE);
        $this->assertEquals($object->getMembersGallery(), self::TO_MEMBERS_GALLERY);

        $this->assertEquals($new_object->getObjId(), self::TO_OBJ_ID);
        $this->assertEquals($new_object->getAbstract(), self::TO_ABSTRACT);
        $this->assertEquals($new_object->getExtro(), "teststring");
        $this->assertEquals($new_object->getAbstractImage(), self::TO_ABSTRACT_IMAGE);
        $this->assertEquals($new_object->getExtroImage(), self::TO_EXTRO_IMAGE);
        $this->assertEquals($new_object->getMembersGallery(), self::TO_MEMBERS_GALLERY);
    }

    /**
     * @depends testCreate
     */
    public function testWithAbstractImage(ilLearningSequenceSettings $object)
    {
        $new_object = $object->withAbstractImage("teststring");

        $this->assertEquals($object->getObjId(), self::TO_OBJ_ID);
        $this->assertEquals($object->getAbstract(), self::TO_ABSTRACT);
        $this->assertEquals($object->getExtro(), self::TO_EXTRO);
        $this->assertEquals($object->getAbstractImage(), self::TO_ABSTRACT_IMAGE);
        $this->assertEquals($object->getExtroImage(), self::TO_EXTRO_IMAGE);
        $this->assertEquals($object->getMembersGallery(), self::TO_MEMBERS_GALLERY);

        $this->assertEquals($new_object->getObjId(), self::TO_OBJ_ID);
        $this->assertEquals($new_object->getAbstract(), self::TO_ABSTRACT);
        $this->assertEquals($new_object->getExtro(), self::TO_EXTRO);
        $this->assertEquals($new_object->getAbstractImage(), "teststring");
        $this->assertEquals($new_object->getExtroImage(), self::TO_EXTRO_IMAGE);
        $this->assertEquals($new_object->getMembersGallery(), self::TO_MEMBERS_GALLERY);
    }

    /**
     * @depends testCreate
     */
    public function testWithExtroImage(ilLearningSequenceSettings $object)
    {
        $new_object = $object->withExtroImage("teststring");

        $this->assertEquals($object->getObjId(), self::TO_OBJ_ID);
        $this->assertEquals($object->getAbstract(), self::TO_ABSTRACT);
        $this->assertEquals($object->getExtro(), self::TO_EXTRO);
        $this->assertEquals($object->getAbstractImage(), self::TO_ABSTRACT_IMAGE);
        $this->assertEquals($object->getExtroImage(), self::TO_EXTRO_IMAGE);
        $this->assertEquals($object->getMembersGallery(), self::TO_MEMBERS_GALLERY);

        $this->assertEquals($new_object->getObjId(), self::TO_OBJ_ID);
        $this->assertEquals($new_object->getAbstract(), self::TO_ABSTRACT);
        $this->assertEquals($new_object->getExtro(), self::TO_EXTRO);
        $this->assertEquals($new_object->getAbstractImage(), self::TO_ABSTRACT_IMAGE);
        $this->assertEquals($new_object->getExtroImage(), "teststring");
        $this->assertEquals($new_object->getMembersGallery(), self::TO_MEMBERS_GALLERY);
    }

    /**
     * @depends testCreate
     */
    public function testWithMembersGallery(ilLearningSequenceSettings $object)
    {
        $new_object = $object->withMembersGallery(false);

        $this->assertEquals($object->getObjId(), self::TO_OBJ_ID);
        $this->assertEquals($object->getAbstract(), self::TO_ABSTRACT);
        $this->assertEquals($object->getExtro(), self::TO_EXTRO);
        $this->assertEquals($object->getAbstractImage(), self::TO_ABSTRACT_IMAGE);
        $this->assertEquals($object->getExtroImage(), self::TO_EXTRO_IMAGE);
        $this->assertEquals($object->getMembersGallery(), self::TO_MEMBERS_GALLERY);

        $this->assertEquals($new_object->getObjId(), self::TO_OBJ_ID);
        $this->assertEquals($new_object->getAbstract(), self::TO_ABSTRACT);
        $this->assertEquals($new_object->getExtro(), self::TO_EXTRO);
        $this->assertEquals($new_object->getAbstractImage(), self::TO_ABSTRACT_IMAGE);
        $this->assertEquals($new_object->getExtroImage(), self::TO_EXTRO_IMAGE);
        $this->assertEquals($new_object->getMembersGallery(), false);
    }
}
