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

namespace ILIAS\Poll\Image\Repository\Element\Wrapper\IRSS;

use ILIAS\Poll\Image\I\Repository\Element\Wrapper\IRSS\HandlerInterface as ilPollImageRepositoryElementIRSSWrapperInterface;
use ILIAS\Poll\Image\I\Repository\FactoryInterface as ilPollImageRepositoryFactoryInterface;
use ILIAS\ResourceStorage\Flavour\Definition\CropToSquare;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Services as ILIASResourceStorageService;
use ILIAS\ResourceStorage\Revision\Revision;

class Handler implements ilPollImageRepositoryElementIRSSWrapperInterface
{
    protected string $resource_id_serialized;
    protected ILIASResourceStorageService $irss;
    protected ilPollImageRepositoryFactoryInterface $repository;
    protected int $thumbnail_size;
    protected int $processed_size;

    public function __construct(
        ILIASResourceStorageService $irss,
        ilPollImageRepositoryFactoryInterface $repository
    ) {
        $this->irss = $irss;
        $this->repository = $repository;
        $this->thumbnail_size = 100;
        $this->processed_size = 300;
    }

    public function withResourceIdSerialized(
        string $resource_id_serialized
    ): ilPollImageRepositoryElementIRSSWrapperInterface {
        $clone = clone $this;
        $clone->resource_id_serialized = $resource_id_serialized;
        return $clone;
    }

    public function delete(
        int $user_id
    ): void {
        $rid = $this->getResourceIdentification();
        if (is_null($rid)) {
            return;
        }
        $this->irss->manage()->remove(
            $this->getResourceIdentification(),
            $this->repository->stakeholder()->handler()->withUserId($user_id)
        );
    }

    public function getResourceIdSerialized(): string
    {
        return $this->resource_id_serialized;
    }

    public function getProcessedImageURL(): null|string
    {
        $rid = $this->getResourceIdentification();
        if (is_null($rid)) {
            return null;
        }
        $definition = new CropToSquare(true, $this->processed_size);
        $flavour = $this->irss->flavours()->get($rid, $definition);
        $urls_of_flavour_streams = $this->irss->consume()->flavourUrls($flavour);
        return $urls_of_flavour_streams->getURLsAsArray(true)[0];
    }

    public function getThumbnailImageURL(): null|string
    {
        $rid = $this->getResourceIdentification();
        if (is_null($rid)) {
            return null;
        }
        $definition = new CropToSquare(true, $this->thumbnail_size);
        $flavour = $this->irss->flavours()->get($rid, $definition);
        $urls_of_flavour_streams = $this->irss->consume()->flavourUrls($flavour);
        return $urls_of_flavour_streams->getURLsAsArray(true)[0];
    }

    public function getUnprocessedImageURL(): null|string
    {
        $rid = $this->getResourceIdentification();
        if (is_null($rid)) {
            return null;
        }
        return $this->irss->consume()->src($rid)->getSrc(true);
    }

    public function getResourceIdentification(): null|ResourceIdentification
    {
        return $this->irss->manage()->find($this->resource_id_serialized ?? "");
    }

    public function getResource(): null|Revision
    {
        $rid = $this->irss->manage()->find($this->resource_id_serialized ?? "");
        if ($rid === null) {
            return null;
        }
        return $this->irss->manage()->getResource($rid)->getCurrentRevision();
    }
}
