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

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\NoneFileNamePolicy;

/**
 * Class ConsumerFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ConsumerFactory
{
    private \ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory $storage_handler_factory;
    protected \ILIAS\ResourceStorage\Policy\FileNamePolicy $file_name_policy;
    private \ILIAS\HTTP\Services $http;

    /**
     * ConsumerFactory constructor.
     * @param FileNamePolicy|null   $file_name_policy
     */
    public function __construct(
        StorageHandlerFactory $storage_handler_factory,
        FileNamePolicy $file_name_policy = null
    ) {
        global $DIC;
        $this->storage_handler_factory = $storage_handler_factory;
        $this->file_name_policy = $file_name_policy ?? new NoneFileNamePolicy();
        $this->http = $DIC->http();
    }

    public function download(StorableResource $resource): DownloadConsumer
    {
        return new DownloadConsumer(
            $this->http,
            $resource,
            $this->storage_handler_factory->getHandlerForResource($resource),
            $this->file_name_policy
        );
    }

    public function inline(StorableResource $resource): InlineConsumer
    {
        return new InlineConsumer(
            $this->http,
            $resource,
            $this->storage_handler_factory->getHandlerForResource($resource),
            $this->file_name_policy
        );
    }

    public function fileStream(StorableResource $resource): FileStreamConsumer
    {
        return new FileStreamConsumer(
            $resource,
            $this->storage_handler_factory->getHandlerForResource($resource)
        );
    }

    /**
     * @deprecated
     */
    public function absolutePath(StorableResource $resource): AbsolutePathConsumer
    {
        return new AbsolutePathConsumer(
            $resource,
            $this->storage_handler_factory->getHandlerForResource($resource),
            $this->file_name_policy
        );
    }

    public function src(StorableResource $resource): SrcConsumer
    {
        return new SrcConsumer(
            $resource,
            $this->storage_handler_factory->getHandlerForResource($resource)
        );
    }

    public function downloadMultiple(
        array $resources,
        ?string $zip_filename = null
    ): DownloadMultipleConsumer {
        return new DownloadMultipleConsumer(
            $resources,
            $this->storage_handler_factory,
            $this->file_name_policy,
            $zip_filename ?? 'Download.zip'
        );
    }
}
