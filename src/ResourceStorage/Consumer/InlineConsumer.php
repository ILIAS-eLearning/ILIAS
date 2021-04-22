<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\HTTP\Response\ResponseHeader;

/**
 * Class InlineConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
class InlineConsumer extends BaseConsumer implements DeliveryConsumer
{

    public function run() : void
    {
        global $DIC;

        $revision = $this->getRevision();

        $file_name = $this->file_name_policy->prepareFileNameForConsumer($revision->getInformation()->getTitle());
        $mime_type = $revision->getInformation()->getMimeType();

        $response = $DIC->http()->response();
        if ($this->file_name_policy->isValidExtension($revision->getInformation()->getSuffix())) {
            $response = $response->withHeader(ResponseHeader::CONTENT_TYPE, $mime_type);
        } else {
            $response = $response->withHeader(ResponseHeader::CONTENT_TYPE, 'application/octet-stream');
        }
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
