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

namespace ILIAS\Export\ExportHandler\PublicAccess;

use ILIAS\Data\ObjectId;
use ILIAS\Data\ReferenceId;
use ILIAS\Export\ExportHandler\I\PublicAccess\HandlerInterface as ilExportHandlerPublicAccessInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Link\FactoryInterface as ilExportHandlerPublicAccessLinkFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\FactoryInterface as ilExportHandlerPublicAccessRepositoryElementFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\HandlerInterface as ilExportHandlerPublicAccessRepositoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\FactoryInterface as ilExportHandlerPublicAccessRepositoryKeyFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Values\FactoryInterface as ilExportHandlerPublicAccessRepositoryValuesFactoryInterface;

class Handler implements ilExportHandlerPublicAccessInterface
{
    protected ilExportHandlerPublicAccessRepositoryInterface $public_access_repository;
    protected ilExportHandlerPublicAccessRepositoryElementFactoryInterface $public_access_repository_element_factory;
    protected ilExportHandlerPublicAccessRepositoryKeyFactoryInterface $public_access_repository_key_factory;
    protected ilExportHandlerPublicAccessRepositoryValuesFactoryInterface $public_access_repository_values_factory;
    protected ilExportHandlerPublicAccessLinkFactoryInterface $public_access_link_factory;

    public function __construct(
        ilExportHandlerPublicAccessRepositoryInterface $public_access_repository,
        ilExportHandlerPublicAccessRepositoryElementFactoryInterface $public_access_repository_element_factory,
        ilExportHandlerPublicAccessRepositoryKeyFactoryInterface $public_access_repository_key_factory,
        ilExportHandlerPublicAccessLinkFactoryInterface $public_access_link_factory,
        ilExportHandlerPublicAccessRepositoryValuesFactoryInterface $public_access_repository_values_factory
    ) {
        $this->public_access_repository = $public_access_repository;
        $this->public_access_repository_element_factory = $public_access_repository_element_factory;
        $this->public_access_repository_key_factory = $public_access_repository_key_factory;
        $this->public_access_link_factory = $public_access_link_factory;
        $this->public_access_repository_values_factory = $public_access_repository_values_factory;
    }

    public function setPublicAccessFile(
        ObjectId $object_id,
        string $export_option_id,
        string $file_identifier
    ): void {
        $key = $this->public_access_repository_key_factory->handler()
            ->withObjectId($object_id);
        $values = $this->public_access_repository_values_factory->handler()
            ->withIdentification($file_identifier)
            ->withExportOptionId($export_option_id);
        $element = $this->public_access_repository_element_factory->handler()
            ->withKey($key)
            ->withValues($values);
        $this->public_access_repository->storeElement($element);
    }

    public function hasPublicAccessFile(
        ObjectId $object_id
    ): bool {
        $key = $this->public_access_repository_key_factory->handler()
            ->withObjectId($object_id);
        return $this->public_access_repository->hasElement($key);
    }

    public function getPublicAccessFileIdentifier(
        ObjectId $object_id
    ): string {
        $key = $this->public_access_repository_key_factory->handler()
            ->withObjectId($object_id);
        $element = $this->public_access_repository->getElement($key);
        return is_null($element) ? "" : $element->getValues()->getIdentification();
    }

    public function getPublicAccessFileExportOptionId(
        ObjectId $object_id
    ): string {
        $key = $this->public_access_repository_key_factory->handler()
            ->withObjectId($object_id);
        $element = $this->public_access_repository->getElement($key);
        return is_null($element) ? "" : $element->getValues()->getExportOptionId();
    }

    public function downloadLinkOfPublicAccessFile(
        ReferenceId $reference_id
    ): string {
        return (string) $this->public_access_link_factory->handler()->withReferenceId($reference_id)->getLink();
    }

    public function removePublicAccessFile(
        ObjectId $object_id
    ): void {
        $key = $this->public_access_repository_key_factory->handler()
            ->withObjectId($object_id);
        $key_collection = $this->public_access_repository_key_factory->collection()
            ->withElement($key);
        $this->public_access_repository->deleteElements($key_collection);
    }
}
