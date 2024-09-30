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
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Input\Field\PhpUploadLimit;
use ILIAS\UI\Component\Input\Field\GlobalUploadLimit;

/**
 * This class will bee used by @see FileUpload to resolve upload-limits.
 *
 * The UI framework knows three kinds of values which might affect an upload-limit:
 *      - PHP: The upload-limit is defined by the php.ini options 'post_max_size' and 'upload_max_filesize'
 *      - Global: The upload-limit is defined by a higher order system like ILIAS, which is determined by various factors.
 *      - Local: The upload-limit is defined by the @see FileUpload itself for specific use-cases.
 *
 * Note:
 *      - local limits will always take precedence over other limits, because they are
 *        tailored for specific use-cases.
 *      - global or local limits exceeding the php-limit can only be applied if the
 *        corresponding @see UploadHandler supports chunked uploads.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class UploadLimitResolver
{
    public function __construct(
        protected PhpUploadLimit $php_upload_limit, 
        protected GlobalUploadLimit $global_upload_limit
    ) {
    }

    public function getBestPossibleUploadLimitInBytes(
        UploadHandler $upload_handler,
        int $local_limit_in_bytes = null
    ): int {
        if (null !== $local_limit_in_bytes && $this->canUploadLimitBeUsed($upload_handler, $local_limit_in_bytes)) {
            return $local_limit_in_bytes;
        }

        $global_upload_limit_in_bytes = $this->global_upload_limit->getGlobalUploadLimitInBytes();

        if (null !== $global_upload_limit_in_bytes &&
            $this->canUploadLimitBeUsed($upload_handler, $global_upload_limit_in_bytes)
        ) {
            return $global_upload_limit_in_bytes;
        }

        return $this->getPhpUploadLimitInBytes();
    }

    public function getPhpUploadLimitInBytes(): int
    {
        return $this->php_upload_limit->getPhpUploadLimitInBytes();
    }

    protected function canUploadLimitBeUsed(UploadHandler $upload_handler, ?int $limit_in_bytes): bool
    {
        if ($upload_handler->supportsChunkedUploads()) {
            return true;
        }

        return $limit_in_bytes <= $this->getPhpUploadLimitInBytes();
    }
}
