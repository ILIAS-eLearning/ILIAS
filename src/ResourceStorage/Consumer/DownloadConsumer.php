<?php declare(strict_types=1);

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

        $file_name = $this->file_name_policy->prepareFileNameForConsumer($this->file_name ?? $revision->getInformation()->getTitle());
        $mime_type = $revision->getInformation()->getMimeType();
        /** @noRector */
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
