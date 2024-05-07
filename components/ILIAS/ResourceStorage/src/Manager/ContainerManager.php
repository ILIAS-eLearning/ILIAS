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

namespace ILIAS\ResourceStorage\Manager;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Resource\ResourceType;

/**
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
final class ContainerManager extends BaseManager
{
    protected function normalizePath(string $path_inside_container): string
    {
        $path_inside_container = '/' . ltrim($path_inside_container, './');
        $path_inside_container = rtrim($path_inside_container, '/');

        return $path_inside_container;
    }

    public function containerFromUpload(
        UploadResult $result,
        ResourceStakeholder $stakeholder,
        string $revision_title = null
    ): ResourceIdentification {
        // check if stream is a ZIP
        $this->checkZIP(mime_content_type($result->getMimeType()));

        return $this->upload($result, $stakeholder, $revision_title);
    }

    public function containerFromStream(
        FileStream $stream,
        ResourceStakeholder $stakeholder,
        string $revision_title = null
    ): ResourceIdentification {
        // check if stream is a ZIP
        $this->checkZIP(mime_content_type($stream->getMetadata()['uri']));

        return $this->newStreamBased(
            $stream,
            $stakeholder,
            ResourceType::CONTAINER,
            $revision_title
        );
    }

    public function createDirectoryInsideContainer(
        ResourceIdentification $container,
        string $path_inside_container
    ): bool {
        $path_inside_container = $this->normalizePath($path_inside_container);
        if (empty($path_inside_container)) {
            return false;
        }
        return $this->resource_builder->createDirectoryInsideContainer(
            $this->getResource($container),
            $path_inside_container
        );
    }

    public function removePathInsideContainer(
        ResourceIdentification $container,
        string $path_inside_container
    ): bool {
        if (empty($path_inside_container)) {
            return false;
        }
        return $this->resource_builder->removePathInsideContainer(
            $this->getResource($container),
            $path_inside_container
        );
    }

    public function addUploadToContainer(
        ResourceIdentification $container,
        UploadResult $result,
        string $parent_path_inside_container,
    ): bool {
        $parent_path_inside_container = $this->normalizePath($parent_path_inside_container);
        if (empty($parent_path_inside_container)) {
            $parent_path_inside_container = '/';
        }
        return $this->resource_builder->addUploadToContainer(
            $this->getResource($container),
            $result,
            $parent_path_inside_container
        );
    }

    public function addStreamToContainer(
        ResourceIdentification $container,
        FileStream $stream,
        string $path_inside_container,
    ): bool {
        $path_inside_container = $this->normalizePath($path_inside_container);
        if (empty($path_inside_container)) {
            return false;
        }
        return $this->resource_builder->addStreamToContainer(
            $this->getResource($container),
            $stream,
            $path_inside_container
        );
    }

}
