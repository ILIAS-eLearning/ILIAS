<?php

declare(strict_types=1);

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
 *********************************************************************/

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\MimeType;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;

/**
 * Class DownloadMultipleConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
class DownloadMultipleConsumer implements DeliveryConsumer
{
    protected string $zip_file_name;
    protected ResourceCollection $collection;
    protected ?int $revision_number = null;
    protected FileNamePolicy $file_name_policy;
    protected StorageHandlerFactory $storage_handler_factory;
    /**
     * @var StorableResource[]
     */
    protected array $resources = [];
    protected bool $use_revision_titles = false;

    public function __construct(
        array $rescources,
        StorageHandlerFactory $storage_handler_factory,
        FileNamePolicy $file_name_policy,
        string $zip_file_name
    ) {
        $this->resources = $rescources;
        $this->storage_handler_factory = $storage_handler_factory;
        $this->file_name_policy = $file_name_policy;
        $this->zip_file_name = $zip_file_name;
    }


    public function run(): void
    {
        global $DIC;

        $directory = CLIENT_DATA_DIR . '/temp';
        $temp = tempnam($directory, 'zip');
        $zip = new \ZipArchive();
        $zip->open($temp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($this->resources as $resource) {
            $storage_handler = $this->storage_handler_factory->getHandlerForResource($resource);
            $revision = $resource->getCurrentRevision();
            if ($this->use_revision_titles) {
                $file_name = $this->file_name_policy->prepareFileNameForConsumer($revision->getTitle());
            } else {
                $file_name = $this->file_name_policy->prepareFileNameForConsumer(
                    $revision->getInformation()->getTitle()
                );
            }

            $zip->addFile($storage_handler->getStream($revision)->getMetadata('uri'), $file_name);
        }

        $zip->close();
        $response = $DIC->http()->response();
        $response = $response->withHeader(ResponseHeader::CONTENT_TYPE, MimeType::APPLICATION__ZIP);
        $response = $response->withHeader(ResponseHeader::CONNECTION, 'close');
        $response = $response->withHeader(ResponseHeader::ACCEPT_RANGES, 'bytes');
        $response = $response->withHeader(
            ResponseHeader::CONTENT_DISPOSITION,
            'attachment'
            . '; filename="'
            . $this->zip_file_name
            . '"'
        );
        $fopen = fopen($temp, 'rb');
        $response = $response->withBody(Streams::ofResource($fopen));
        $DIC->http()->saveResponse($response);
        $DIC->http()->sendResponse();
        fclose($fopen);
        unlink($temp);
        $DIC->http()->close();
    }


    public function useRevisionTitlesForFileNames(bool $use_revision_titles): self
    {
        $this->use_revision_titles = $use_revision_titles;
        return $this;
    }

    public function setRevisionNumber(int $revision_number): DeliveryConsumer
    {
        $this->revision_number = $revision_number;
        return $this;
    }

    public function overrideFileName(string $file_name): DeliveryConsumer
    {
        $this->zip_file_name = $file_name;
        return $this;
    }
}
