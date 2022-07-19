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
 */

namespace ILIAS\UI\Implementation\Component\Input;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class UploadLimitResolver
{
    protected int $default_upload_size_limit;

    public function __construct(int $default_upload_size_limit)
    {
        $this->default_upload_size_limit = $default_upload_size_limit;
    }

    public function checkUploadLimit(int $size_in_bytes) : void
    {
        if ($size_in_bytes > $this->default_upload_size_limit) {
            throw new \InvalidArgumentException("File size exceeds $this->default_upload_size_limit bytest.");
        }
    }

    public function getUploadLimit() : int
    {
        return $this->default_upload_size_limit;
    }
}
