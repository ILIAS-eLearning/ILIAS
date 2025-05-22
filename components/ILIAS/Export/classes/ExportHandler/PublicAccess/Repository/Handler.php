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

namespace ILIAS\Export\ExportHandler\PublicAccess\Repository;

use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\CollectionInterface as ilExportHandlerPublicAccessRepositoryElementCollectionInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\HandlerInterface as ilExportHandlerPublicAccessRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\HandlerInterface as ilExportHandlerPublicAccessRepositoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\CollectionInterface as ilExportHandlerPublicAccessRepositoryKeyCollectionInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\FactoryInterface as ilExportHandlerPublicAccessRepositoryKeyFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\HandlerInterface as ilExportHandlerPublicAccessRepositoryKeyInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Wrapper\DB\HandlerInterface as ilExportHandlerPublicAccessRepositoryDBWrapperInterface;

class Handler implements ilExportHandlerPublicAccessRepositoryInterface
{
    protected ilExportHandlerPublicAccessRepositoryDBWrapperInterface $db_wrapper;
    protected ilExportHandlerPublicAccessRepositoryKeyFactoryInterface $key_factory;

    public function __construct(
        ilExportHandlerPublicAccessRepositoryDBWrapperInterface $db_wrapper,
        ilExportHandlerPublicAccessRepositoryKeyFactoryInterface $key_factory
    ) {
        $this->db_wrapper = $db_wrapper;
        $this->key_factory = $key_factory;
    }

    public function storeElement(ilExportHandlerPublicAccessRepositoryElementInterface $element): bool
    {
        if (!$element->isStorable()) {
            return false;
        }
        $this->db_wrapper->storeElement($element);
        return true;
    }

    public function getElements(
        ilExportHandlerPublicAccessRepositoryKeyCollectionInterface $keys
    ): ilExportHandlerPublicAccessRepositoryElementCollectionInterface {
        return $this->db_wrapper->getElements($keys);
    }

    public function getElement(ilExportHandlerPublicAccessRepositoryKeyInterface $key): ilExportHandlerPublicAccessRepositoryElementInterface|null
    {
        $elements = $this->getElements($this->key_factory->collection()->withElement($key));
        $elements->rewind();
        return $elements->valid() ? $elements->current() : null;
    }

    public function hasElements(
        ilExportHandlerPublicAccessRepositoryKeyCollectionInterface $keys
    ): bool {
        return $this->db_wrapper->getElements($keys)->count() > 0;
    }

    public function hasElement(ilExportHandlerPublicAccessRepositoryKeyInterface $key): bool
    {
        return $this->hasElements($this->key_factory->collection()->withElement($key));
    }

    public function deleteElements(
        ilExportHandlerPublicAccessRepositoryKeyCollectionInterface $keys
    ): void {
        $this->db_wrapper->deleteElements($keys);
    }

    public function deleteElement(ilExportHandlerPublicAccessRepositoryKeyInterface $key): void
    {
        $this->deleteElements($this->key_factory->collection()->withElement($key));
    }
}
