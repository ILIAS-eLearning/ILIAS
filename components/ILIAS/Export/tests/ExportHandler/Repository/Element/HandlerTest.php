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

namespace ILIAS\Export\Test\ExportHandler\Repository\Element;

use DateTimeImmutable;
use Exception;
use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSS\FactoryInterface as ilExportHandlerRepositoryElementIRSSWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSS\HandlerInterface as ilExportHandlerRepositoryElementIRSSWrapperInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSSInfo\FactoryInterface as ilExportHandlerRepositoryElementIRSSInfoWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSSInfo\HandlerInterface as ilExportHandlerRepositoryElementIRSSInfoWrapperInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\HandlerInterface as ilExportHandlerRepositoryKeyInterface;
use ILIAS\Export\ExportHandler\I\Repository\Values\HandlerInterface as ilExportHandlerRepositoryValuesInterface;
use ILIAS\Export\ExportHandler\Repository\Element\Handler as ilExportHandlerRepositoryElement;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testExportHandlerRepositoryElement(): void
    {
        $resouce_id_serialized = "keykeykey";
        $object_id_mock = $this->createMock(ObjectId::class);
        $key_mock = $this->createMock(ilExportHandlerRepositoryKeyInterface::class);
        $key_mock->method("isCompleteKey")->willReturn(true);
        $key_mock->method("isObjectIdKey")->willReturn(false);
        $key_mock->method("isResourceIdKey")->willReturn(false);
        $key_mock->method("getResourceIdSerialized")->willReturn($resouce_id_serialized);
        $key_mock->method("getObjectId")->willReturn($object_id_mock);
        $date_time_mock = $this->createMock(DateTimeImmutable::class);
        $value_mock = $this->createMock(ilExportHandlerRepositoryValuesInterface::class);
        $value_mock->method("isValid")->willReturn(true);
        $value_mock->method("getOwnerId")->willReturn(1);
        $value_mock->method("getCreationDate")->willReturn($date_time_mock);
        $irss_wrapper_mock = $this->createMock(ilExportHandlerRepositoryElementIRSSWrapperInterface::class);
        $irss_wrapper_mock->method("withResourceIdSerialized")->with($resouce_id_serialized)->willReturn($irss_wrapper_mock);
        $irss_wrapper_factory_mock = $this->createMock(ilExportHandlerRepositoryElementIRSSWrapperFactoryInterface::class);
        $irss_wrapper_factory_mock->method("handler")->willReturn($irss_wrapper_mock);
        $irss_info_wrapper_mock = $this->createMock(ilExportHandlerRepositoryElementIRSSInfoWrapperInterface::class);
        $irss_info_wrapper_mock->method("withResourceIdSerialized")->with($resouce_id_serialized)->willReturn($irss_info_wrapper_mock);
        $irss_info_wrapper_factory_mock = $this->createMock(ilExportHandlerRepositoryElementIRSSInfoWrapperFactoryInterface::class);
        $irss_info_wrapper_factory_mock->method("handler")->willReturn($irss_info_wrapper_mock);
        try {
            $element = (new ilExportHandlerRepositoryElement(
                $irss_wrapper_factory_mock,
                $irss_info_wrapper_factory_mock
            ))
                ->withKey($key_mock)
                ->withValues($value_mock);
            $element_not_storable_0 = new ilExportHandlerRepositoryElement(
                $irss_wrapper_factory_mock,
                $irss_info_wrapper_factory_mock
            );
            $element_not_storable_1 = $element_not_storable_0->withValues($value_mock);
            $element_not_storable_2 = $element_not_storable_0->withKey($key_mock);
            self::assertFalse($element_not_storable_0->isStorable());
            self::assertFalse($element_not_storable_1->isStorable());
            self::assertFalse($element_not_storable_2->isStorable());
            self::assertEquals($irss_wrapper_mock, $element->getIRSS());
            self::assertEquals($irss_info_wrapper_mock, $element->getIRSSInfo());
            self::assertEquals($value_mock, $element->getValues());
            self::assertEquals($key_mock, $element->getKey());
            self::assertTrue($element->isStorable());
            self::assertEquals("xml", $element->getFileType());
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }
}
