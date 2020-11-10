<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\HTTP\Response\ResponseHeader;

/**
 * Class DownloadConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
class DownloadConsumer extends BaseConsumer implements DeliveryConsumer
{

    public function run() : void
    {
        global $DIC;

        $revision = $this->getRevision();

        $file_name = $revision->getInformation()->getTitle();
        $mime_type = $revision->getInformation()->getMimeType();

        $response = $DIC->http()->response();
        $response = $response->withHeader(ResponseHeader::CONTENT_TYPE, $mime_type);
        $response = $response->withHeader(ResponseHeader::CONNECTION, 'close');
        $response = $response->withHeader(ResponseHeader::ACCEPT_RANGES, 'bytes');
        $response = $response->withHeader(
            ResponseHeader::CONTENT_DISPOSITION,
            'attachment'
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
