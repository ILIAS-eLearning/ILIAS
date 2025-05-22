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

namespace ILIAS\Poll\Image;

use ILIAS\Data\ObjectId;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Poll\Image\I\HandlerInterface as ilPollImageInterface;
use ILIAS\Poll\Image\I\Repository\FactoryInterface as ilPollImageRepositoryFactoryInterface;
use ILIAS\ResourceStorage\Services as ilResourceStorageServices;
use ILIAS\ResourceStorage\Revision\Revision;

class Handler implements ilPollImageInterface
{
    protected ilResourceStorageServices $irss;
    protected ilPollImageRepositoryFactoryInterface $repository;

    public function __construct(
        ilResourceStorageServices $irss,
        ilPollImageRepositoryFactoryInterface $repository
    ) {
        $this->irss = $irss;
        $this->repository = $repository;
    }

    public function uploadImage(
        ObjectId $object_id,
        string $file_path,
        string $file_name,
        int $user_id
    ): void {
        $this->deleteImage(
            $object_id,
            $user_id
        );
        $rid = $this->irss->manage()->stream(
            Streams::ofResource(fopen($file_path, 'r')),
            $this->repository->stakeholder()->handler()->withUserId($user_id),
            $file_name
        );
        $key = $this->repository->key()->handler()
            ->withObjectId($object_id);
        $values = $this->repository->values()->handler()
            ->withResourceIdSerialized($rid->serialize());
        $this->repository->handler()->store($key, $values);
    }

    public function cloneImage(
        ObjectId $original_object_id,
        ObjectId $clone_object_id,
        int $user_id
    ): void {
        $this->deleteImage(
            $clone_object_id,
            $user_id
        );
        $key_clone = $this->repository->key()->handler()
            ->withObjectId($clone_object_id);
        $key_original = $this->repository->key()->handler()
            ->withObjectId($original_object_id);
        $element_original = $this->repository->handler()->getElement($key_original);

        if ($element_original === null) {
            return;
        }

        $rid_original = $element_original->getIRSS()->getResourceIdentification();
        $rid_clone = $this->irss->manage()->clone($rid_original);
        $values_clone = $this->repository->values()->handler()
            ->withResourceIdSerialized($rid_clone->serialize());
        $this->repository->handler()->store($key_clone, $values_clone);
    }

    public function deleteImage(
        ObjectId $object_id,
        int $user_id
    ): void {
        $key = $this->repository->key()->handler()
            ->withObjectId($object_id);
        $existing_element = $this->repository->handler()->getElement($key);
        if (!is_null($existing_element)) {
            $existing_element->getIRSS()->delete($user_id);
            $this->repository->handler()->deleteElement($existing_element->getKey());
        }
    }

    public function getThumbnailImageURL(
        ObjectId $object_id
    ): null|string {
        $key = $this->repository->key()->handler()
            ->withObjectId($object_id);
        $element = $this->repository->handler()->getElement($key);
        if (is_null($element)) {
            return null;
        }
        return $element->getIRSS()->getThumbnailImageURL();
    }

    public function getProcessedImageURL(
        ObjectId $object_id
    ): null|string {
        $key = $this->repository->key()->handler()
            ->withObjectId($object_id);
        $element = $this->repository->handler()->getElement($key);
        if (is_null($element)) {
            return null;
        }
        return $element->getIRSS()->getProcessedImageURL();
    }

    public function getUnprocessedImageURL(
        ObjectId $object_id
    ): null|string {
        $key = $this->repository->key()->handler()
                                ->withObjectId($object_id);
        $element = $this->repository->handler()->getElement($key);
        if (is_null($element)) {
            return null;
        }
        return $element->getIRSS()->getUnprocessedImageURL();
    }

    public function getRessource(
        ObjectId $object_id
    ): null|Revision {
        $key = $this->repository->key()->handler()
                                ->withObjectId($object_id);
        $element = $this->repository->handler()->getElement($key);
        if (is_null($element)) {
            return null;
        }
        return $element->getIRSS()->getResource();
    }
}
