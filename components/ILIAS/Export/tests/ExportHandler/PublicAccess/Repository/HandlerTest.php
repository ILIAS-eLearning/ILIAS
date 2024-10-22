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

namespace ILIAS\Export\Test\ExportHandler\PublicAccess\Repository;

use _PHPStan_9815bbba4\Nette\Utils\AssertionException;
use Exception;
use ILIAS\Data\ObjectId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Handler as ilExportHandlerPublicAccessRepository;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Wrapper\DB\HandlerInterface as ilExportHandlerPublicAccessRepositoryDBWrapperInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\FactoryInterface as ilExportHandlerPublicAccessRepositoryKeyFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\HandlerInterface as ilExportHandlerPublicAccessRepositoryKeyInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\CollectionInterface as ilExportHandlerPublicAccessRepositoryKeyCollectionInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\HandlerInterface as ilExportHandlerPublicAccessRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\CollectionInterface as ilExportHandlerPublicAccessRepositoryElementCollectionInterface;
use UnexpectedValueException;

class HandlerTest extends TestCase
{
    /**
     * @var ilExportHandlerPublicAccessRepositoryElementInterface&MockObject[]
     */
    protected array $repository_elements;

    public function testExportHandlerPublicAccessRepository(): void
    {
        $this->repository_elements = [];

        $object_id_mock_01 = $this->createMock(ObjectId::class);
        $object_id_mock_01->method("toInt")->willReturn(1);
        $object_id_mock_01->method("toReferenceIds")->willThrowException(new UnexpectedValueException("unexpected method call"));

        $object_id_mock_02 = $this->createMock(ObjectId::class);
        $object_id_mock_02->method("toInt")->willReturn(2);
        $object_id_mock_02->method("toReferenceIds")->willThrowException(new UnexpectedValueException("unexpected method call"));

        $object_id_mock_03 = $this->createMock(ObjectId::class);
        $object_id_mock_03->method("toInt")->willReturn(3);
        $object_id_mock_03->method("toReferenceIds")->willThrowException(new UnexpectedValueException("unexpected method call"));

        $key_mock_01 = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyInterface::class);
        $key_mock_01->method("getObjectId")->willReturn($object_id_mock_01);
        $key_mock_01->method("withObjectId")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $key_mock_01->method("equals")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $key_mock_01->method("isValid")->willThrowException(new UnexpectedValueException("unexpected method call"));

        $key_mock_02 = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyInterface::class);
        $key_mock_02->method("getObjectId")->willReturn($object_id_mock_02);
        $key_mock_02->method("withObjectId")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $key_mock_02->method("equals")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $key_mock_02->method("isValid")->willThrowException(new UnexpectedValueException("unexpected method call"));

        $key_mock_03 = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyInterface::class);
        $key_mock_03->method("getObjectId")->willReturn($object_id_mock_03);
        $key_mock_03->method("withObjectId")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $key_mock_03->method("equals")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $key_mock_03->method("isValid")->willThrowException(new UnexpectedValueException("unexpected method call"));

        $element_mock_01 = $this->createMock(ilExportHandlerPublicAccessRepositoryElementInterface::class);
        $element_mock_01->method("getValues")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $element_mock_01->method("getKey")->willReturn($key_mock_01);
        $element_mock_01->method("withValues")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $element_mock_01->method("withKey")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $element_mock_01->method("isStorable")->willReturn(true);
        $element_mock_01->method("equals")->with($element_mock_01)->willReturn(true);

        $element_mock_02 = $this->createMock(ilExportHandlerPublicAccessRepositoryElementInterface::class);
        $element_mock_02->method("getValues")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $element_mock_02->method("getKey")->willReturn($key_mock_02);
        $element_mock_02->method("withValues")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $element_mock_02->method("withKey")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $element_mock_02->method("isStorable")->willReturn(true);
        $element_mock_02->method("equals")->with($element_mock_02)->willReturn(true);

        $element_not_storable_mock = $this->createMock(ilExportHandlerPublicAccessRepositoryElementInterface::class);
        $element_not_storable_mock->method("getValues")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $element_not_storable_mock->method("getKey")->willReturn($key_mock_03);
        $element_not_storable_mock->method("withValues")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $element_not_storable_mock->method("withKey")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $element_not_storable_mock->method("isStorable")->willReturn(false);
        $element_not_storable_mock->method("equals")->with($element_not_storable_mock)->willReturn(true);

        $key_collection_01_mock = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyCollectionInterface::class);
        # next, rewind are void
        $key_collection_01_mock->method("key")->willReturn(0, 1);
        $key_collection_01_mock->method("valid")->willReturn(true, false);
        $key_collection_01_mock->method("count")->willReturn(1);
        $key_collection_01_mock->method("current")->willReturn($key_mock_01);
        $key_collection_01_mock->method("withElement")->willThrowException(new UnexpectedValueException("unexpected method call"));

        $key_collection_02_mock = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyCollectionInterface::class);
        # next, rewind are void
        $key_collection_02_mock->method("key")->willReturn(0, 1);
        $key_collection_02_mock->method("valid")->willReturn(true, false);
        $key_collection_02_mock->method("count")->willReturn(1);
        $key_collection_02_mock->method("current")->willReturn($key_mock_02);
        $key_collection_02_mock->method("withElement")->willThrowException(new UnexpectedValueException("unexpected method call"));

        $key_collection_03_mock = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyCollectionInterface::class);
        # next, rewind are void
        $key_collection_03_mock->method("key")->willReturn(0, 1);
        $key_collection_03_mock->method("valid")->willReturn(true, false);
        $key_collection_03_mock->method("count")->willReturn(1);
        $key_collection_03_mock->method("current")->willReturn($key_mock_03);
        $key_collection_03_mock->method("withElement")->willThrowException(new UnexpectedValueException("unexpected method call"));

        $key_collection_all_mock = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyCollectionInterface::class);
        # next, rewind are void
        $key_collection_all_mock->method("key")->willReturn(0, 1, 2);
        $key_collection_all_mock->method("valid")->willReturn(true, true, false);
        $key_collection_all_mock->method("count")->willReturn(2);
        $key_collection_all_mock->method("current")->willReturn($key_mock_01, $key_mock_02);
        $key_collection_all_mock->method("withElement")->willThrowException(new UnexpectedValueException("unexpected method call"));

        $key_collection_empty_mock = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyCollectionInterface::class);
        # next, rewind are void
        $key_collection_empty_mock->method("key")->willReturn(0);
        $key_collection_empty_mock->method("valid")->willReturn(false);
        $key_collection_empty_mock->method("count")->willReturn(0);
        $key_collection_empty_mock->method("current")->willThrowException(new UnexpectedValueException("tried to access element on empty collection"));
        $key_collection_empty_mock->method("withElement")->willReturnMap([
            [$key_mock_01, $key_collection_01_mock],
            [$key_mock_02, $key_collection_02_mock],
            [$key_mock_03, $key_collection_03_mock],
        ]);

        $key_factory_mock = $this->createMock(ilExportHandlerPublicAccessRepositoryKeyFactoryInterface::class);
        $key_factory_mock->method("handler")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $key_factory_mock->method("collection")->willReturn($key_collection_empty_mock);

        $db_wrapper_mock = $this->createMock(ilExportHandlerPublicAccessRepositoryDBWrapperInterface::class);
        $db_wrapper_mock->method("storeElement")->willReturnCallback(function ($x) {
            $this->mockDBWrapperStore($x);
        });
        ;
        $db_wrapper_mock->method("getElements")->willReturnCallback(function ($x) {
            return $this->mockDBWrapperGetElementsByKeyCollection($x);
        });
        ;
        $db_wrapper_mock->method("deleteElements")->willReturnCallback(function ($x) {
            $this->mockDBWrapperRemoveByKeyCollection($x);
        });
        ;
        $db_wrapper_mock->method("buildDeleteQuery")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $db_wrapper_mock->method("buildSelectQuery")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $db_wrapper_mock->method("buildSelectAllQuery")->willThrowException(new UnexpectedValueException("unexpected method call"));

        try {
            $repository = new ilExportHandlerPublicAccessRepository(
                $db_wrapper_mock,
                $key_factory_mock
            );

            self::assertFalse($repository->hasElement($key_mock_01));
            self::assertFalse($repository->hasElement($key_mock_02));
            self::assertFalse($repository->hasElement($key_mock_03));

            $repository->storeElement($element_mock_01);
            $repository->storeElement($element_mock_02);
            $element_in_repository_01 = $repository->getElement($key_mock_01);
            $element_in_repository_02 = $repository->getElement($key_mock_02);

            self::assertObjectEquals($element_in_repository_01, $element_mock_01);
            self::assertObjectEquals($element_in_repository_02, $element_mock_02);
            self::assertTrue($repository->hasElement($key_mock_01));
            self::assertTrue($repository->hasElement($key_mock_02));
            self::assertFalse($repository->hasElement($key_mock_03));

            $repository->deleteElement($key_mock_01);
            $element_in_repository_01 = $repository->getElement($key_mock_01);
            $element_in_repository_02 = $repository->getElement($key_mock_02);

            self::assertNull($element_in_repository_01);
            self::assertObjectEquals($element_in_repository_02, $element_mock_02);
            self::assertFalse($repository->hasElement($key_mock_01));
            self::assertTrue($repository->hasElement($key_mock_02));
            self::assertFalse($repository->hasElement($key_mock_03));

            $repository->storeElement($element_mock_01);
            $element_in_repository_01 = $repository->getElement($key_mock_01);
            $element_in_repository_02 = $repository->getElement($key_mock_02);

            self::assertObjectEquals($element_in_repository_01, $element_mock_01);
            self::assertObjectEquals($element_in_repository_02, $element_mock_02);
            self::assertTrue($repository->hasElement($key_mock_01));
            self::assertTrue($repository->hasElement($key_mock_02));
            self::assertFalse($repository->hasElement($key_mock_03));

            $repository->deleteElements($key_collection_all_mock);
            $element_in_repository_01 = $repository->getElement($key_mock_01);
            $element_in_repository_02 = $repository->getElement($key_mock_02);

            self::assertNull($element_in_repository_01);
            self::assertNull($element_in_repository_02);
            self::assertFalse($repository->hasElement($key_mock_01));
            self::assertFalse($repository->hasElement($key_mock_02));
            self::assertFalse($repository->hasElement($key_mock_03));
        } catch (UnexpectedValueException $exception) {
            self::fail($exception->getMessage());
        }
    }

    protected function mockDBWrapperStore(
        ilExportHandlerPublicAccessRepositoryElementInterface&MockObject $element_mock
    ): void {
        $this->repository_elements[] = $element_mock;
    }

    protected function mockDBWrapperRemoveByKeyCollection(
        ilExportHandlerPublicAccessRepositoryKeyCollectionInterface&MockObject $key_collection_mock
    ): void {
        $ids = [];
        for ($i = 0; $i < count($key_collection_mock); $i++) {
            $ids[] = $key_collection_mock->current()->getObjectId()->toInt();
        }
        $new_repository_elements = [];
        foreach ($this->repository_elements as $element_mock) {
            if (!in_array($element_mock->getKey()->getObjectId()->toInt(), $ids)) {
                $new_repository_elements[] = $element_mock;
            }
        }
        $this->repository_elements = $new_repository_elements;
    }

    protected function mockDBWrapperGetElementsByKeyCollection(
        ilExportHandlerPublicAccessRepositoryKeyCollectionInterface&MockObject $key_collection_mock
    ): ilExportHandlerPublicAccessRepositoryElementCollectionInterface&MockObject {
        $elements = [];
        for ($i = 0; $i < $key_collection_mock->count(); $i++) {
            $current = $key_collection_mock->current();
            foreach ($this->repository_elements as $element_mock) {
                if ($current->getObjectId()->toInt() === $element_mock->getKey()->getObjectId()->toInt()) {
                    $elements[] = $element_mock;
                }
            }
        }
        $element_collection_mock = $this->createMock(ilExportHandlerPublicAccessRepositoryElementCollectionInterface::class);
        $element_collection_mock->method("withElement")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $element_collection_mock->method("key")->willThrowException(new UnexpectedValueException("unexpected method call"));
        $element_collection_mock->method("next")->willThrowException(new UnexpectedValueException("unexpected method call"));
        if (count($elements) === 0) {
            $element_collection_mock->method("count")->willReturn(0);
            $element_collection_mock->method("current")->willThrowException(new UnexpectedValueException("unexpected method call"));
            $element_collection_mock->method("valid")->willReturn(false);
        }
        if (count($elements) > 0) {
            $valid_return_values = array_fill(0, count($elements), true);
            $valid_return_values[] = false;
            $element_collection_mock->method("count")->willReturn(count($elements));
            $element_collection_mock->method("current")->willReturn(...$elements);
            $element_collection_mock->method("valid")->willReturn(...$valid_return_values);
        }
        return $element_collection_mock;
    }
}
