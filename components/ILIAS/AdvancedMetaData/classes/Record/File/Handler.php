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

namespace ILIAS\AdvancedMetaData\Record\File;

use ILIAS\AdvancedMetaData\Record\File\I\HandlerInterface as FileInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\CollectionInterface as FileRepositoryElementCollectionInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\HandlerInterface as FileRepositoryElementInterface;
use ILIAS\Data\ObjectId;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\AdvancedMetaData\Record\File\I\FactoryInterface as FileFactoryInterface;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Services as IRSS;

class Handler implements FileInterface
{
    protected FileFactoryInterface $amd_record_file_factory;
    protected IRSS $irss;
    protected DataFactory $data_factory;

    public function __construct(
        FileFactoryInterface $amd_record_file_factory,
        IRSS $irss,
        DataFactory $data_factory
    ) {
        $this->amd_record_file_factory = $amd_record_file_factory;
        $this->irss = $irss;
        $this->data_factory = $data_factory;
    }

    public function getFilesByObjectId(
        ObjectId $object_id
    ): FileRepositoryElementCollectionInterface {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withObjectId($object_id);
        return $this->amd_record_file_factory->repository()->handler()->getElements($key);
    }

    public function getFileByObjectIdAndResourceId(
        ObjectId $object_id,
        string $resource_id_serialized
    ): FileRepositoryElementInterface|null {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withObjectId($object_id)
            ->withResourceIdSerialized($resource_id_serialized);
        $elements = $this->amd_record_file_factory->repository()->handler()->getElements($key);
        $elements->rewind();
        return $elements->count() === 0 ? null : $elements->current();
    }

    public function getGlobalFiles(): FileRepositoryElementCollectionInterface
    {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withIsGlobal(true);
        return $this->amd_record_file_factory->repository()->handler()->getElements($key);
    }

    public function addFile(
        ObjectId $object_id,
        int $user_id,
        string $file_name,
        FileStream $content
    ): void {
        $stakeholder = $this->amd_record_file_factory->repository()->stakeholder()->handler()
            ->withOwnerId($user_id);
        $rid = $this->irss->manage()->stream($content, $stakeholder);
        $this->irss->manage()->getResource($rid)->getCurrentRevision()->setTitle($file_name);
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withObjectId($object_id)
            ->withIsGlobal(false)
            ->withResourceIdSerialized($rid->serialize());
        $this->amd_record_file_factory->repository()->handler()->store($key);
    }

    public function addGlobalFile(
        int $user_id,
        string $file_name,
        FileStream $content
    ): void {
        $stakeholder = $this->amd_record_file_factory->repository()->stakeholder()->handler()
            ->withOwnerId($user_id);
        $rid = $this->irss->manage()->stream($content, $stakeholder);
        $this->irss->manage()->getResource($rid)->getCurrentRevision()->setTitle($file_name);
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withObjectId($this->data_factory->objId(0))
            ->withIsGlobal(true)
            ->withResourceIdSerialized($rid->serialize());
        $this->amd_record_file_factory->repository()->handler()->store($key);
    }

    public function download(
        ObjectId $object_id,
        string $resource_id_serialized,
        string|null $filename_overwrite
    ): void {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withIsGlobal(false)
            ->withObjectId($object_id)
            ->withResourceIdSerialized($resource_id_serialized);
        $elements = $this->amd_record_file_factory->repository()->handler()->getElements($key);
        if ($elements->count() === 0) {
            return;
        }
        $elements->rewind();
        $elements->current()->getIRSS()->download($filename_overwrite);
    }

    public function downloadGlobal(
        string $resource_id_serialized,
        string|null $filename_overwrite
    ): void {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withIsGlobal(true)
            ->withObjectId($this->data_factory->objId(0))
            ->withResourceIdSerialized($resource_id_serialized);
        $elements = $this->amd_record_file_factory->repository()->handler()->getElements($key);
        if ($elements->count() === 0) {
            return;
        }
        $elements->rewind();
        $elements->current()->getIRSS()->download($filename_overwrite);
    }

    public function delete(
        ObjectId $object_id,
        int $user_id,
        string $resource_id_serialized
    ): void {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withIsGlobal(false)
            ->withObjectId($object_id)
            ->withResourceIdSerialized($resource_id_serialized);
        $elements = $this->amd_record_file_factory->repository()->handler()->getElements($key);
        if ($elements->count() === 0) {
            return;
        }
        $elements->rewind();
        $element = $elements->current();
        $this->amd_record_file_factory->repository()->handler()->delete($key);
    }

    public function deleteGlobal(
        int $user_id,
        string $resource_id_serialized
    ): void {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withIsGlobal(true)
            ->withObjectId($this->data_factory->objId(0))
            ->withResourceIdSerialized($resource_id_serialized);
        $elements = $this->amd_record_file_factory->repository()->handler()->getElements($key);
        if ($elements->count() === 0) {
            return;
        }
        $elements->rewind();
        $element = $elements->current();

        $this->amd_record_file_factory->repository()->handler()->delete($key);
    }
}
