<?php

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

declare(strict_types=1);

namespace ILIAS\Export\Test\ExportHandler\Repository\Key;

use Exception;
use ILIAS\Data\ObjectId;
use PHPUnit\Framework\TestCase;
use ILIAS\Export\ExportHandler\Repository\Key\Handler as ilExportHandlerRepositoryKey;

class ilHandlerTest extends TestCase
{
    public function testExportHandlerRepositoryKey(): void
    {
        $resource_identification = "abc";
        $object_id_mock = $this->createMock(ObjectId::class);
        $object_id_mock->method('toInt')->willReturn(123);
        $object_id_mock->method("toReferenceIds")->willThrowException(new Exception("toReferenceIds should not be called"));
        $repository_key_empty = new ilExportHandlerRepositoryKey();
        $repository_key_with_resource_id = $repository_key_empty->withResourceIdSerialized($resource_identification);
        $repository_key_with_object_id = $repository_key_empty->withObjectId($object_id_mock);
        $repository_key_complete = $repository_key_empty->withResourceIdSerialized($resource_identification)->withObjectId($object_id_mock);
        $this->assertFalse($repository_key_empty->isObjectIdKey());
        $this->assertFalse($repository_key_empty->isResourceIdKey());
        $this->assertFalse($repository_key_empty->isCompleteKey());
        $this->assertEquals(-1, $repository_key_empty->getObjectId()->toInt());
        $this->assertEquals("", $repository_key_empty->getResourceIdSerialized());
        $this->assertFalse($repository_key_with_resource_id->isObjectIdKey());
        $this->assertTrue($repository_key_with_resource_id->isResourceIdKey());
        $this->assertFalse($repository_key_with_resource_id->isCompleteKey());
        $this->assertEquals(-1, $repository_key_with_resource_id->getObjectId()->toInt());
        $this->assertEquals($resource_identification, $repository_key_with_resource_id->getResourceIdSerialized());
        $this->assertTrue($repository_key_with_object_id->isObjectIdKey());
        $this->assertFalse($repository_key_with_object_id->isResourceIdKey());
        $this->assertFalse($repository_key_with_object_id->isCompleteKey());
        $this->assertEquals($object_id_mock->toInt(), $repository_key_with_object_id->getObjectId()->toInt());
        $this->assertEquals("", $repository_key_with_object_id->getResourceIdSerialized());
        $this->assertFalse($repository_key_complete->isObjectIdKey());
        $this->assertFalse($repository_key_complete->isResourceIdKey());
        $this->assertTrue($repository_key_complete->isCompleteKey());
        $this->assertEquals($object_id_mock->toInt(), $repository_key_complete->getObjectId()->toInt());
        $this->assertEquals($resource_identification, $repository_key_complete->getResourceIdSerialized());
    }
}
