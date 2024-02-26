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

use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Resource\StorableResource;

/**
 * Class BaseHTTPResponseBasedConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
abstract class BaseHTTPResponseBasedConsumer extends BaseConsumer implements DeliveryConsumer
{
    // Firefox determines the content type from the file content anyway for some content.
    private const NON_VALID_EXTENSION_MIME = \ILIAS\FileUpload\MimeType::APPLICATION__OCTET_STREAM;
    private \ILIAS\HTTP\Services $http;
    private \ILIAS\FileDelivery\Delivery\StreamDelivery $delivery;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        StorableResource $resource,
        StreamAccess $stream_access,
        FileNamePolicy $file_name_policy
    ) {
        global $DIC;
        $this->delivery = $DIC->fileDelivery()->delivery();
        parent::__construct($resource, $stream_access, $file_name_policy);
    }

    abstract protected function getDisposition(): string;

    public function run(): void
    {
        $revision = $this->getRevision();
        $filename_with_extension = $this->file_name ?? $revision->getInformation()->getTitle();
        $extension = pathinfo($filename_with_extension, PATHINFO_EXTENSION);
        $file_name_for_consumer = $this->file_name_policy->prepareFileNameForConsumer($filename_with_extension);

        $mime_type = $this->file_name_policy->isValidExtension($extension)
            ? $revision->getInformation()->getMimeType()
            : self::NON_VALID_EXTENSION_MIME;

        // Build Response
        $revision = $this->stream_access->populateRevision($revision);

        switch ($this->getDisposition()) {
            case 'attachment':
                $this->delivery->attached(
                    $revision->maybeStreamResolver()?->getStream(),
                    $file_name_for_consumer,
                    $mime_type
                );
                break;
            case 'inline':
                $this->delivery->inline(
                    $revision->maybeStreamResolver()?->getStream(),
                    $file_name_for_consumer,
                    $mime_type
                );
                break;
            default:
                throw new \InvalidArgumentException('Invalid disposition');
        }
    }
}
