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

namespace ILIAS\Export\Test\ExportHandler\PublicAccess\Repository\Key;

use Exception;
use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\HandlerInterface as ilExportHandlerPublicAccessRepositoryKeyInterface;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Key\Collection as ilExportHandlerPublicAccessRepositoryKeyCollection;
use PHPUnit\Framework\TestCase;

class Collection extends TestCase
{
    public function testExportHandlerPublicAccessRepositoryKeyCollection(): void
    {
        $object_id_mock_01 = $this->createMock(ObjectId::class);
        $object_id_mock_01->method("toInt")->willReturn(1);
        $object_id_mock_01->method("toReferenceIds")->willThrowException(new Exception("unexpected access of reference ids"));
        $object_id_mock_02 = $this->createMock(ObjectId::class);
        $object_id_mock_02->method("toInt")->willReturn(2);
        $object_id_mock_02->method("toReferenceIds")->willThrowException(new Exception("unexpected access of reference ids"));
        $object_id_mock_03 = $this->createMock(ObjectId::class);
        $object_id_mock_03->method("toInt")->willReturn(3);
        $object_id_mock_03->method("toReferenceIds")->willThrowException(new Exception("unexpected access of reference ids"));
        $key_mock_01 = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyInterface::class);
        $key_mock_01->method("isValid")->willReturn(true);
        $key_mock_01->method("getObjectId")->willReturn($object_id_mock_01);
        $key_mock_01->method("withObjectId")->willThrowException(new Exception("unexpected overwrite of object id"));
        $key_mock_02 = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyInterface::class);
        $key_mock_02->method("isValid")->willReturn(true);
        $key_mock_02->method("getObjectId")->willReturn($object_id_mock_02);
        $key_mock_02->method("withObjectId")->willThrowException(new Exception("unexpected overwrite of object id"));
        $key_mock_03 = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyInterface::class);
        $key_mock_03->method("isValid")->willReturn(true);
        $key_mock_03->method("getObjectId")->willReturn($object_id_mock_03);
        $key_mock_03->method("withObjectId")->willThrowException(new Exception("unexpected overwrite of object id"));
        try {
            $empty_collection = new ilExportHandlerPublicAccessRepositoryKeyCollection();
            $full_collection = $empty_collection
                ->withElement($key_mock_01)
                ->withElement($key_mock_02)
                ->withElement($key_mock_03);
            self::assertCount(0, $empty_collection);
            self::assertFalse($empty_collection->valid());
            self::assertCount(3, $full_collection);
            $full_collection->rewind();
            for ($i = 0; $i < 3; $i++) {
                self::assertEquals($i, $full_collection->key());
                self::assertEquals($object_id_mock_01->toInt(), $full_collection->current()->getObjectId()->toInt());
                self::assertTrue($full_collection->valid());
                $full_collection->next();
            }
            self::assertFalse($full_collection->valid());
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }
}
