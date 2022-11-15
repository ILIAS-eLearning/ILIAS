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

use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Resource\StorableResource;

/**
 * Class BaseHTTPResponseBasedConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
abstract class BaseHTTPResponseBasedConsumer extends BaseConsumer implements DeliveryConsumer
{
    // This should be 'application/octet-stream', but Firefox determines the content type from the file content, then.
    private const NON_VALID_EXTENSION_MIME = \ILIAS\FileUpload\MimeType::TEXT__PLAIN;
    private \ILIAS\HTTP\Services $http;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        StorableResource $resource,
        StreamAccess $stream_access,
        FileNamePolicy $file_name_policy
    ) {
        $this->http = $http;
        parent::__construct($resource, $stream_access, $file_name_policy);
    }

    abstract protected function getDisposition(): string;

    public function run(): void
    {
        $revision = $this->getRevision();
        $filename_with_extension = $this->file_name ?? $revision->getInformation()->getTitle();
        $extension = pathinfo($filename_with_extension, PATHINFO_EXTENSION);
        $file_name_for_consumer = $this->file_name_policy->prepareFileNameForConsumer($filename_with_extension);

        // Build Response
        $response = $this->http->response();
        if ($this->file_name_policy->isValidExtension($extension)) {
            $response = $response->withHeader(ResponseHeader::CONTENT_TYPE, $revision->getInformation()->getMimeType());
        } else {
            $response = $response->withHeader(ResponseHeader::X_CONTENT_TYPE_OPTIONS, 'nosniff');
            $response = $response->withHeader(ResponseHeader::CONTENT_TYPE, self::NON_VALID_EXTENSION_MIME);
        }

        $response = $response->withHeader(ResponseHeader::CONNECTION, 'close');
        $response = $response->withHeader(ResponseHeader::ACCEPT_RANGES, 'none');
        $response = $response->withHeader(
            ResponseHeader::CONTENT_DISPOSITION,
            $this->getDisposition()
            . '; filename="'
            . $file_name_for_consumer
            . '"'
        );
        $revision = $this->stream_access->populateRevision($revision);

        $response = $response->withBody($revision->maybeGetToken()->resolveStream());

        $this->http->saveResponse($response);
        $this->http->sendResponse();
        $this->http->close();
    }
}
