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

class HandlerTest extends TestCase
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
        self::assertFalse($repository_key_empty->isObjectIdKey());
        self::assertFalse($repository_key_empty->isResourceIdKey());
        self::assertFalse($repository_key_empty->isCompleteKey());
        self::assertEquals(-1, $repository_key_empty->getObjectId()->toInt());
        self::assertEquals("", $repository_key_empty->getResourceIdSerialized());
        self::assertFalse($repository_key_with_resource_id->isObjectIdKey());
        self::assertTrue($repository_key_with_resource_id->isResourceIdKey());
        self::assertFalse($repository_key_with_resource_id->isCompleteKey());
        self::assertEquals(-1, $repository_key_with_resource_id->getObjectId()->toInt());
        self::assertEquals($resource_identification, $repository_key_with_resource_id->getResourceIdSerialized());
        self::assertTrue($repository_key_with_object_id->isObjectIdKey());
        self::assertFalse($repository_key_with_object_id->isResourceIdKey());
        self::assertFalse($repository_key_with_object_id->isCompleteKey());
        self::assertEquals($object_id_mock->toInt(), $repository_key_with_object_id->getObjectId()->toInt());
        self::assertEquals("", $repository_key_with_object_id->getResourceIdSerialized());
        self::assertFalse($repository_key_complete->isObjectIdKey());
        self::assertFalse($repository_key_complete->isResourceIdKey());
        self::assertTrue($repository_key_complete->isCompleteKey());
        self::assertEquals($object_id_mock->toInt(), $repository_key_complete->getObjectId()->toInt());
        self::assertEquals($resource_identification, $repository_key_complete->getResourceIdSerialized());
        self::assertTrue($repository_key_empty->equals($repository_key_empty));
        self::assertTrue($repository_key_with_resource_id->equals($repository_key_with_resource_id));
        self::assertTrue($repository_key_with_object_id->equals($repository_key_with_object_id));
        self::assertTrue($repository_key_complete->equals($repository_key_complete));
        self::assertFalse($repository_key_empty->equals($repository_key_with_resource_id));
        self::assertFalse($repository_key_empty->equals($repository_key_with_object_id));
        self::assertFalse($repository_key_empty->equals($repository_key_complete));
        self::assertFalse($repository_key_with_object_id->equals($repository_key_with_resource_id));
        self::assertFalse($repository_key_with_object_id->equals($repository_key_complete));
        self::assertFalse($repository_key_with_resource_id->equals($repository_key_complete));
    }
}
