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
use ILIAS\Export\ExportHandler\I\Wrapper\DataFactory\HandlerInterface as ilExportHandlerDataFactoryWrapperInterface;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Key\Handler as ilExportHandlerPublicAccessRepositoryKey;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testExportHandlerPublicAccessRepositoryKey(): void
    {
        $object_id = 2;
        $object_id_mock = $this->createMock(ObjectId::class);
        $object_id_mock->method("toInt")->willReturn($object_id);
        $object_id_mock->method("toReferenceIds")->willThrowException(new Exception("unexpected access of reference ids"));
        $object_id_invalid_mock = $this->createMock(ObjectId::class);
        $object_id_invalid_mock->method('toInt')->willReturn(ilExportHandlerPublicAccessRepositoryKeyInterface::EMPTY_OBJECT_ID);
        $object_id_invalid_mock->method("toReferenceIds")->willThrowException(new Exception("toReferenceIds should not be called"));
        $df_factory_wrapper_mock = $this->createMock(ilExportHandlerDataFactoryWrapperInterface::class);
        $df_factory_wrapper_mock->method('objId')->willReturn($object_id_invalid_mock);
        try {
            $key = new ilExportHandlerPublicAccessRepositoryKey($df_factory_wrapper_mock);
            $key_with_object_id = $key
                ->withObjectId($object_id_mock);
            self::assertFalse($key->isValid());
            self::assertTrue($key_with_object_id->isValid());
            self::assertEquals($object_id, $key_with_object_id->getObjectId()->toInt());
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }
}
