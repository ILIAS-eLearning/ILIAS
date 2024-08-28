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

namespace ExportHandler\Repository\Element;

use DateTimeImmutable;
use ILIAS\Data\ObjectId;
use PHPUnit\Framework\TestCase;

use ILIAS\Export\ExportHandler\Repository\Element\Handler as ilExportHandlerRepositoryElement;
use ILIAS\Export\ExportHandler\Repository\Element\Wrapper\IRSSInfo\Factory as ilExportHandlerRepositoryElementIRSSInfoWrapperFactory;
use ILIAS\Export\ExportHandler\Repository\Element\Wrapper\IRSSInfo\Handler as ilExportHandlerRepositoryElementIRSSInfoWrapper;
use ILIAS\Export\ExportHandler\Repository\Element\Wrapper\IRSS\Factory as ilExportHandlerRepositoryElementIRSSWrapperFactory;
use ILIAS\Export\ExportHandler\Repository\Element\Wrapper\IRSS\Handler as ilExportHandlerRepositoryElementIRSSWrapper;
use ILIAS\Export\ExportHandler\Repository\Key\Handler as ilExportHandlerRepositoryKey;
use ILIAS\Export\ExportHandler\Repository\Values\Handler as ilExportHandlerRepositoryValues;

class HandlerTest extends TestCase
{
    public function testExportHandlerRepositoryElement(): void
    {
        $resouce_id_serialized = "keykeykey";
        $object_id_mock = $this->createMock(ObjectId::class);
        $key_mock = $this->createMock(ilExportHandlerRepositoryKey::class);
        $key_mock->method("isCompleteKey")->willReturn(true);
        $key_mock->method("isObjectIdKey")->willReturn(false);
        $key_mock->method("isResourceIdKey")->willReturn(false);
        $key_mock->method("getResourceIdSerialized")->willReturn($resouce_id_serialized);
        $key_mock->method("getObjectId")->willReturn($object_id_mock);
        $date_time_mock = $this->createMock(DateTimeImmutable::class);
        $value_mock = $this->createMock(ilExportHandlerRepositoryValues::class);
        $value_mock->method("isValid")->willReturn(true);
        $value_mock->method("getOwnerId")->willReturn(1);
        $value_mock->method("getCreationDate")->willReturn($date_time_mock);
        $irss_wrapper_mock = $this->createMock(ilExportHandlerRepositoryElementIRSSWrapper::class);
        $irss_wrapper_mock->method("withResourceIdSerialized")->with($resouce_id_serialized)->willReturn($irss_wrapper_mock);
        $irss_wrapper_factory_mock = $this->createMock(ilExportHandlerRepositoryElementIRSSWrapperFactory::class);
        $irss_wrapper_factory_mock->method("handler")->willReturn($irss_wrapper_mock);
        $irss_info_wrapper_mock = $this->createMock(ilExportHandlerRepositoryElementIRSSInfoWrapper::class);
        $irss_info_wrapper_mock->method("withResourceIdSerialized")->with($resouce_id_serialized)->willReturn($irss_info_wrapper_mock);
        $irss_info_wrapper_factory_mock = $this->createMock(ilExportHandlerRepositoryElementIRSSInfoWrapperFactory::class);
        $irss_info_wrapper_factory_mock->method("handler")->willReturn($irss_info_wrapper_mock);

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

        $this->assertFalse($element_not_storable_0->isStorable());
        $this->assertFalse($element_not_storable_1->isStorable());
        $this->assertFalse($element_not_storable_2->isStorable());
        $this->assertEquals($irss_wrapper_factory_mock->handler(), $element->getIRSS());
        $this->assertEquals($irss_info_wrapper_factory_mock->handler(), $element->getIRSSInfo());
        $this->assertEquals($value_mock, $element->getValues());
        $this->assertEquals($key_mock, $element->getKey());
        $this->assertTrue($element->isStorable());
        $this->assertEquals("xml", $element->getFileType());
    }
}
