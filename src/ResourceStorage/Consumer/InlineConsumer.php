<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\ResourceStorage\StorableResource;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;

/**
 * Class InlineConsumer
 *
 * @package ILIAS\ResourceStorage\Consumer
 */
class InlineConsumer implements DeliveryConsumer
{

    /**
     * @var StorageHandler
     */
    private $storage_handler;
    /**
     * @var StorableResource
     */
    private $resource;


    /**
     * DownloadConsumer constructor.
     *
     * @param StorableResource $resource
     * @param StorageHandler   $storage_handler
     */
    public function __construct(StorableResource $resource, StorageHandler $storage_handler)
    {
        $this->resource = $resource;
        $this->storage_handler = $storage_handler;
    }


    public function run() : void
    {
        global $DIC;

        $revision = $this->resource->getCurrentRevision();
        $file_name = $revision->getInformation()->getTitle();
        $mime_type = $revision->getInformation()->getMimeType();

        $response = $DIC->http()->response();
        $response = $response->withHeader(ResponseHeader::CONTENT_TYPE, $mime_type);
        $response = $response->withHeader(ResponseHeader::CONNECTION, 'close');
        $response = $response->withHeader(ResponseHeader::ACCEPT_RANGES, 'bytes');
        $response = $response->withHeader(
            ResponseHeader::CONTENT_DISPOSITION,
            'inline'
            . '; filename="'
            . $file_name
            . '"'
        );
        $response = $response->withBody($this->storage_handler->getStream($revision));

        $DIC->http()->saveResponse($response);
        $DIC->http()->sendResponse();
        $DIC->http()->close();
    }
}
