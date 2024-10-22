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

namespace ILIAS\Export\Test\ExportHandler\Repository;

use DateTimeImmutable;
use Exception;
use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Info\Export\HandlerInterface as ilExportHandlerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\CollectionInterface as ilExportHandlerRepositoryElementCollectionInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\FactoryInterface as ilExportHandlerRepositoryElementFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\CollectionInterface as ilExportHandlerRepositoryKeyCollectionInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\FactoryInterface as ilExportHandlerRepositoryKeyFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\HandlerInterface as ilExportHandlerRepositoryKeyInterface;
use ILIAS\Export\ExportHandler\I\Repository\Stakeholder\HandlerInterface as ilExportHandlerRepositoryStakeholderInterface;
use ILIAS\Export\ExportHandler\I\Repository\Values\FactoryInterface as ilExportHandlerRepositoryValuesFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Values\HandlerInterface as ilExportHandlerRepositoryValuesInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\DB\HandlerInterface as ilExportHandlerRepositoryDBWrapperInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\IRSS\HandlerInterface as ilExportHandlerRepositoryIRSSWrapperInterface;
use ILIAS\Export\ExportHandler\Repository\Handler as ilExportHandlerRepository;
use PHPUnit\Framework\TestCase;

class Handler extends TestCase
{
    protected array $repository_elements;

    public function testExportHandlerRepository(): void
    {
        $resource_id_serialized = "rid";
        $owner_id = 6;
        $creation_date = new DateTimeImmutable();
        $element_collection_mock01 = $this->createMock(ilExportHandlerRepositoryElementCollectionInterface::class);
        $stakeholder_mock = $this->createMock(ilExportHandlerRepositoryStakeholderInterface::class);
        $stakeholder_mock->method("getOwnerId")->willReturn($owner_id);
        $stakeholder_mock->method("withOwnerId")->willThrowException(new Exception("owner id changed"));
        $this->repository_elements = [];
        $object_id_mock01 = $this->createMock(ObjectId::class);
        $object_id_mock01->method("toInt")->willReturn(1);
        $object_id_mock01->method("toReferenceIds")->willThrowException(new Exception("unexpected method call"));
        $key_complete_mock = $this->createMock(ilExportHandlerRepositoryKeyInterface::class);
        $key_complete_mock->method("withObjectId")->with($object_id_mock01)->willReturn($key_complete_mock);
        $key_complete_mock->method("withResourceIdSerialized")->with($resource_id_serialized)->willReturn($key_complete_mock);
        $key_complete_mock->method("getObjectId")->willReturn($object_id_mock01);
        $key_complete_mock->method("getResourceIdSerialized")->willReturn($resource_id_serialized);
        $key_obj_id_mock = $this->createMock(ilExportHandlerRepositoryKeyInterface::class);
        $key_obj_id_mock->method("withObjectId")->with($object_id_mock01)->willReturn($key_obj_id_mock);
        $key_obj_id_mock->method("withResourceIdSerialized")->with($resource_id_serialized)->willReturn($key_complete_mock);
        $key_obj_id_mock->method("getObjectId")->willReturn($object_id_mock01);
        $key_obj_id_mock->method("getResourceIdSerialized")->willThrowException(new Exception("resource id not set"));
        $key_res_id_mock = $this->createMock(ilExportHandlerRepositoryKeyInterface::class);
        $key_res_id_mock->method("withObjectId")->with($object_id_mock01)->willReturn($key_complete_mock);
        $key_res_id_mock->method("withResourceIdSerialized")->with($resource_id_serialized)->willReturn($key_res_id_mock);
        $key_res_id_mock->method("getObjectId")->willThrowException(new Exception("obj id not set"));
        $key_res_id_mock->method("getResourceIdSerialized")->willReturn($resource_id_serialized);
        $key_mock = $this->createMock(ilExportHandlerRepositoryKeyInterface::class);
        $key_mock->method("withObjectId")->with($object_id_mock01)->willReturn($key_obj_id_mock);
        $key_mock->method("withResourceIdSerialized")->with($resource_id_serialized)->willReturn($key_res_id_mock);
        $key_mock->method("getObjectId")->willThrowException(new Exception("obj id not set"));
        $key_mock->method("getResourceIdSerialized")->willThrowException(new Exception("resource id not set"));
        $key_collection_with_element_mock = $this->createMock(ilExportHandlerRepositoryKeyCollectionInterface::class);
        $key_collection_with_element_mock->method("withElement")->willThrowException(new Exception("to many keys added to collection"));
        $key_collection_with_element_mock->method("current")->willReturn($key_complete_mock);
        $key_collection_with_element_mock->method("key")->willReturn(0, 1);
        # next() does not return anything
        # rewind() does not return anything
        $key_collection_with_element_mock->method("valid")->willReturn(true, false);
        $key_collection_with_element_mock->method("count")->willReturn(1);
        $key_collection_mock01 = $this->createMock(ilExportHandlerRepositoryKeyCollectionInterface::class);
        $key_collection_mock01->method("withElement")->with($key_complete_mock)->willReturn($key_collection_with_element_mock);
        $key_collection_mock01->method("withElement")->with($key_mock)->willThrowException(new Exception("key incomplete"));
        $key_collection_mock01->method("withElement")->with($key_obj_id_mock)->willThrowException(new Exception("key incomplete"));
        $key_collection_mock01->method("withElement")->with($key_res_id_mock)->willThrowException(new Exception("key incomplete"));
        $key_collection_mock01->method("current")->willThrowException(new Exception("collection empty"));
        $key_collection_mock01->method("key")->willReturn(0);
        # next() does not return anything
        # rewind() does not return anything
        $key_collection_mock01->method("valid")->willReturn(false);
        $key_collection_mock01->method("count")->willReturn(0);
        $key_factory_mock = $this->createMock(ilExportHandlerRepositoryKeyFactoryInterface::class);
        $key_factory_mock->method("handler")->willReturn($key_mock);
        $key_factory_mock->method("collection")->willReturn($key_collection_mock01);
        $export_info_mock = $this->createMock(ilExportHandlerExportInfoInterface::class);
        $irss_wrapper_mock = $this->createMock(ilExportHandlerRepositoryIRSSWrapperInterface::class);
        $irss_wrapper_mock->method('createEmptyContainer')->with($export_info_mock, $stakeholder_mock)->willReturn($resource_id_serialized);
        $irss_wrapper_mock->method("getCreationDate")->with($resource_id_serialized)->willReturn($creation_date);
        $value_mock = $this->createMock(ilExportHandlerRepositoryValuesInterface::class);
        $value_mock->method("withOwnerId")->with($owner_id)->willReturn($value_mock);
        $value_mock->method("withCreationDate")->with($creation_date)->willReturn($value_mock);
        $value_mock->method("getOwnerId")->willReturn($owner_id);
        $value_mock->method("getCreationDate")->willReturn($creation_date);
        $values_factory_mock = $this->createMock(ilExportHandlerRepositoryValuesFactoryInterface::class);
        $values_factory_mock->method("handler")->willReturn($value_mock);
        $element_complete_mock = $this->createMock(ilExportHandlerRepositoryElementInterface::class);
        $element_complete_mock->method("isStorable")->willReturn(true);
        $element_complete_mock->method("withKey")->with($key_mock)->willReturn($element_complete_mock);
        $element_complete_mock->method("withValues")->with($value_mock)->willReturn($element_complete_mock);
        $element_w_key_mock = $this->createMock(ilExportHandlerRepositoryElementInterface::class);
        $element_w_key_mock->method("isStorable")->willReturn(false);
        $element_w_key_mock->method("withKey")->with($key_mock)->willReturn($element_w_key_mock);
        $element_w_key_mock->method("withValues")->with($value_mock)->willReturn($element_complete_mock);
        $element_w_values_mock = $this->createMock(ilExportHandlerRepositoryElementInterface::class);
        $element_w_values_mock->method("isStorable")->willReturn(false);
        $element_w_values_mock->method("withKey")->with($key_mock)->willReturn($element_complete_mock);
        $element_w_values_mock->method("withValues")->with($value_mock)->willReturn($element_w_values_mock);
        $element_emtpy_mock = $this->createMock(ilExportHandlerRepositoryElementInterface::class);
        $element_emtpy_mock->method("isStorable")->willReturn(false);
        $element_emtpy_mock->method("withKey")->with($key_mock)->willReturn($element_w_key_mock);
        $element_emtpy_mock->method("withValues")->with($value_mock)->willReturn($element_w_values_mock);
        $element_factory_mock = $this->createMock(ilExportHandlerRepositoryElementFactoryInterface::class);
        $element_factory_mock->method("handler")->willReturn($element_emtpy_mock);
        $db_wrapper_mock = $this->createMock(ilExportHandlerRepositoryDBWrapperInterface::class);
        $db_wrapper_mock->method("getElements")->with($key_collection_mock01)->willReturnCallback(function ($x) {
            return $this->mockDBWrapperGetElements($x);
        });
        $db_wrapper_mock->method("deleteElements")->with($key_collection_mock01)->willReturnCallback(function ($x) {
            $this->mockDBWrapperDeleteElements($x);
        });
        $db_wrapper_mock->method("store")->with($element_complete_mock)->willReturnCallback(function ($x) {
            $this->mockDBWrapperStore($x);
        });
        try {
            $export_repository = new ilExportHandlerRepository(
                $key_factory_mock,
                $values_factory_mock,
                $element_factory_mock,
                $db_wrapper_mock,
                $irss_wrapper_mock
            );
            $element = $export_repository->createElement(
                $object_id_mock01,
                $export_info_mock,
                $stakeholder_mock
            );
            self::assertCount(1, $this->repository_elements);
            $export_repository->storeElement($element_complete_mock);
            $export_repository->storeElement($element_emtpy_mock);
            self::assertCount(2, $this->repository_elements);
            $elements = $export_repository->getElements($key_collection_mock01);
            self::assertCount(2, $this->repository_elements);
            $export_repository->deleteElements($key_collection_mock01, $stakeholder_mock);
            self::assertCount(0, $this->repository_elements);
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }

    protected function mockDBWrapperStore(
        $x
    ): void {
        $this->repository_elements[] = $x;
    }

    protected function mockDBWrapperGetElements(
        $x
    ): ilExportHandlerRepositoryElementCollectionInterface {
        $key_collection_mock = func_get_args()[0];
        $element_collection_mock = $this->createMock(ilExportHandlerRepositoryElementCollectionInterface::class);
        if (empty($this->repository_elements)) {
            $element_collection_mock->method("newest")->willReturn(null);
            $element_collection_mock->method("current")->willThrowException(new Exception("empty collection"));
            $element_collection_mock->method("key")->willReturn(0);
            # next() does not return anything
            # rewind() does not return anything
            $element_collection_mock->method("valid")->willReturn(false);
            $element_collection_mock->method("count")->willReturn(0);
        }
        if (!empty($this->repository_elements)) {
            $element_collection_mock->method("newest")->willReturn($this->repository_elements[0]);
            $element_collection_mock->method("current")->willReturn(...$this->repository_elements);
            $element_collection_mock->method("key")->willReturn(...array_keys($this->repository_elements));
            # next() does not return anything
            # rewind() does not return anything
            $element_collection_mock->method("valid")->willReturn(...array_map(function ($element) { return !is_null($element); }, array_merge($this->repository_elements, [null])));
            $element_collection_mock->method("count")->willReturn(count($this->repository_elements));
        }
        return $element_collection_mock;
    }

    protected function mockDBWrapperDeleteElements(
        $x
    ): void {
        $this->repository_elements = [];
    }
}
