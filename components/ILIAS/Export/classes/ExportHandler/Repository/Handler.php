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

namespace ILIAS\Export\ExportHandler\Repository;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Info\Export\HandlerInterface as ilExportHandlerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\CollectionInterface as ilExportHandlerRepositoryElementCollectionInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\FactoryInterface as ilExportHandlerRepositoryElementFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\Repository\HandlerInterface as ilExportHandlerRepositoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\CollectionInterface as ilExportHandlerRepositoryKeyCollectionInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\FactoryInterface as ilExportHandlerRepositoryKeyFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Stakeholder\HandlerInterface as ilExportHandlerRepositoryStakeholderInterface;
use ILIAS\Export\ExportHandler\I\Repository\Values\FactoryInterface as ilExportHandlerRepositoryValuesFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\DB\HandlerInterface as ilExportHandlerRepositoryDBWrapperInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\IRSS\HandlerInterface as ilExportHandlerRepositoryIRSSWrapperInterface;

class Handler implements ilExportHandlerRepositoryInterface
{
    protected ilExportHandlerRepositoryKeyFactoryInterface $key_factory;
    protected ilExportHandlerRepositoryValuesFactoryInterface $values_factory;
    protected ilExportHandlerRepositoryElementFactoryInterface $element_factory;
    protected ilExportHandlerRepositoryDBWrapperInterface $db_wrapper;
    protected ilExportHandlerRepositoryIRSSWrapperInterface $irss_wrapper;

    public function __construct(
        ilExportHandlerRepositoryKeyFactoryInterface $key_factory,
        ilExportHandlerRepositoryValuesFactoryInterface $values_factory,
        ilExportHandlerRepositoryElementFactoryInterface $element_factory,
        ilExportHandlerRepositoryDBWrapperInterface $db_wrapper,
        ilExportHandlerRepositoryIRSSWrapperInterface $irss_wrapper
    ) {
        $this->element_factory = $element_factory;
        $this->values_factory = $values_factory;
        $this->db_wrapper = $db_wrapper;
        $this->irss_wrapper = $irss_wrapper;
        $this->key_factory = $key_factory;
    }

    public function createElement(
        ObjectId $object_id,
        ilExportHandlerExportInfoInterface $info,
        ilExportHandlerRepositoryStakeholderInterface $stakeholder
    ): ilExportHandlerRepositoryElementInterface {
        $resource_id_serialized = $this->irss_wrapper->createEmptyContainer($info, $stakeholder);
        $key = $this->key_factory->handler()
            ->withObjectId($object_id)
            ->withResourceIdSerialized($resource_id_serialized);
        $values = $this->values_factory->handler()
            ->withOwnerId($stakeholder->getOwnerId())
            ->withCreationDate($this->irss_wrapper->getCreationDate($resource_id_serialized));
        $element = $this->element_factory->handler()
            ->withKey($key)
            ->withValues($values);
        $this->storeElement($element);
        return $element;
    }

    public function storeElement(ilExportHandlerRepositoryElementInterface $element): void
    {
        if ($element->isStorable()) {
            $this->db_wrapper->store($element);
        }
    }

    public function deleteElements(
        ilExportHandlerRepositoryKeyCollectionInterface $keys,
        ilExportHandlerRepositoryStakeholderInterface $stakeholder
    ): void {
        $removed_keys = $this->key_factory->collection();
        foreach ($keys as $key) {
            if ($this->irss_wrapper->removeContainer($key->getResourceIdSerialized(), $stakeholder)) {
                $removed_keys = $removed_keys->withElement($key);
            }
        }
        $this->db_wrapper->deleteElements($removed_keys);
    }

    public function getElements(
        ilExportHandlerRepositoryKeyCollectionInterface $keys
    ): ilExportHandlerRepositoryElementCollectionInterface {
        return $this->db_wrapper->getElements($keys);
    }
}
