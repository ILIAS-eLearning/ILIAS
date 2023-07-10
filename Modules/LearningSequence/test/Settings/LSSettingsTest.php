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

/**
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class LSSettingsTest extends TestCase
{
    public const TO_OBJ_ID = 10;
    public const TO_ABSTRACT = "abstract";
    public const TO_EXTRO = "extro";
    public const TO_ABSTRACT_IMAGE = "abstract/image/path";
    public const TO_EXTRO_IMAGE = "extro/image/path";
    public const TO_ONLINE = true;
    public const TO_MEMBERS_GALLERY = true;

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
