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

namespace ILIAS\Export\Test\ExportHandler\PublicAccess\Repository\Element;

use Exception;
use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\HandlerInterface as ilExportHandlerPublicAccessRepositoryKeyInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Values\HandlerInterface as ilExportHandlerPublicAccessRepositoryValuesInteface;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Element\Handler as ilExportHandlerPublicAccessRepositoryElement;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testExportHandlerPublicAccessRepositoryElement(): void
    {
        $object_id_mock_01 = $this->createMock(ObjectId::class);
        $object_id_mock_01->method("toInt")->willReturn(20);
        $object_id_mock_01->method("toReferenceIds")->willThrowException(new Exception("unexpected reference id access"));
        $values_mock = $this->createMock(ilExportHandlerPublicAccessRepositoryValuesInteface::class);
        $values_mock->method("isValid")->willReturn(true);
        $values_mock->method("getIdentification")->willReturn("id");
        $values_mock->method("withIdentification")->willThrowException(new Exception("unexpected id overwrite"));
        $values_mock->method("getExportOptionId")->willReturn("exp_id");
        $values_mock->method("withExportOptionId")->willThrowException(new Exception("unexpected exp id overwrite"));
        $values_mock->method("getLastModified")->willThrowException(new Exception("unexpected last modified access"));
        $values_not_storable_mock = $this->createMock(ilExportHandlerPublicAccessRepositoryValuesInteface::class);
        $values_not_storable_mock->method("isValid")->willReturn(false);
        $values_not_storable_mock->method("getIdentification")->willThrowException(new Exception("unexpected id access"));
        $values_not_storable_mock->method("withIdentification")->willThrowException(new Exception("unexpected id overwrite"));
        $values_not_storable_mock->method("getExportOptionId")->willThrowException(new Exception("unexpected exp id access"));
        $values_not_storable_mock->method("withExportOptionId")->willThrowException(new Exception("unexpected exp id overwrite"));
        $values_not_storable_mock->method("getLastModified")->willThrowException(new Exception("unexpected last modified access"));
        $values_mock->method("equals")->willReturnMap([
            [$values_mock, true], [$values_not_storable_mock, false]
        ]);
        $values_not_storable_mock->method("equals")->willReturnMap([
            [$values_mock, false], [$values_not_storable_mock, true]
        ]);
        $key_mock = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyInterface::class);
        $key_mock->method("isValid")->willReturn(true);
        $key_mock->method("getObjectId")->willReturn($object_id_mock_01);
        $key_mock->method("withObjectId")->willThrowException(new Exception("unexpected object id overwrite"));
        $key_not_storable_mock = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyInterface::class);
        $key_not_storable_mock->method("isValid")->willReturn(false);
        $key_not_storable_mock->method("getObjectId")->willThrowException(new Exception("unexpected object id access"));
        $key_not_storable_mock->method("withObjectId")->willThrowException(new Exception("unexpected object id overwrite"));
        $key_mock->method("equals")->willReturnMap([
            [$key_mock, true], [$key_not_storable_mock, false]
        ]);
        $key_not_storable_mock->method("equals")->willReturnMap([
            [$key_mock, false], [$key_not_storable_mock, true]
        ]);
        try {
            $element = new ilExportHandlerPublicAccessRepositoryElement();
            $element_with_key = $element
                ->withKey($key_mock);
            $element_with_values = $element
                ->withValues($values_mock);
            $element_complete = $element
                ->withKey($key_mock)
                ->withValues($values_mock);
            $element_not_storable_01 = $element
                ->withKey($key_not_storable_mock)
                ->withValues($values_mock);
            $element_not_storable_02 = $element
                ->withKey($key_mock)
                ->withValues($values_not_storable_mock);
            $element_not_storable_03 = $element
                ->withKey($key_not_storable_mock)
                ->withValues($values_not_storable_mock);
            self::assertTrue($element_complete->isStorable());
            self::assertFalse($element_not_storable_01->isStorable());
            self::assertFalse($element_not_storable_02->isStorable());
            self::assertFalse($element_not_storable_03->isStorable());
            self::assertTrue($element->equals($element));
            self::assertTrue($element_with_key->equals($element_with_key));
            self::assertTrue($element_with_values->equals($element_with_values));
            self::assertTrue($element_complete->equals($element_complete));
            self::assertFalse($element_complete->equals($element));
            self::assertFalse($element_not_storable_01->equals($element));
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }
}
