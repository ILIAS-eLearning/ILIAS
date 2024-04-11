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

namespace ILIAS\Services\Badge;

use ilBadge;
use ilException;
use Throwable;

class BadgeException extends ilException
{
    public const EXCEPTION_FILE_NOT_FOUND = 1;
    public const EXCEPTION_MOVE_UPLOADED_IMAGE_FAILED = 2;

    private ilBadge $badge;

    public function __construct(int $code, ilBadge $badge, ?ilException $previous_exception = null)
    {
        parent::__construct('', $code, $previous_exception);
        $this->badge = $badge;

    }

    public static function uploadedBadgeImageFileNotFound(ilBadge $badge, ?ilException $previous_exception = null): self
    {
        return new self(self::EXCEPTION_FILE_NOT_FOUND, $badge, $previous_exception);
    }

    public static function moveUploadedBadgeImageFailed(ilBadge $badge, ?ilException $previous_exception = null): self
    {
        return new self(self::EXCEPTION_MOVE_UPLOADED_IMAGE_FAILED, $badge, $previous_exception);
    }
}
