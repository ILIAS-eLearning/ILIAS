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

/**
 * Class for advanced editing exception handling in ILIAS.
 */
class ilSystemStyleColorException extends ilSystemStyleExceptionBase
{
    public const INVALID_COLOR_EXCEPTION = 1001;

    protected function assignMessageToCode(): void
    {
        switch ($this->code) {
            case self::INVALID_COLOR_EXCEPTION:
                $this->message = 'Invalid Color value';
                break;
            default:
                $this->message = 'Unknown Exception ' . $this->add_info;
                break;
        }
    }
}
