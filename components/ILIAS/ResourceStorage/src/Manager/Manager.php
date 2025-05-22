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
use ILIAS\ResourceStorage\Resource\InfoResolver\UploadInfoResolver;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Resource\ResourceType;

/**
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
class Manager extends BaseManager
{
    /**
     * @description Creates a new resource from an upload, the status in this case is always PUBLISHED.
     */
    public function upload(
        UploadResult $result,
        ResourceStakeholder $stakeholder,
        ?string $revision_title = null
    ): ResourceIdentification {
        if ($result->isOK()) {
            $info_resolver = new UploadInfoResolver(
                $result,
                1,
                $stakeholder->getOwnerOfNewResources(),
                $revision_title ?? $result->getName()
            );

            $resource = $this->resource_builder->new(
                $result,
                $info_resolver
            );
            $resource->addStakeholder($stakeholder);
            $this->resource_builder->store($resource);

            return $resource->getIdentification();
        }
        throw new \LogicException("Can't handle UploadResult: " . $result->getStatus()->getMessage());
    }

    public function stream(
        FileStream $stream,
        ResourceStakeholder $stakeholder,
        ?string $revision_title = null
    ): ResourceIdentification {
        return $this->newStreamBased(
            $stream,
            $stakeholder,
            ResourceType::SINGLE_FILE,
            $revision_title
        );
    }

}
