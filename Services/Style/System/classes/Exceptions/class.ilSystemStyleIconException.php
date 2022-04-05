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
class ilSystemStyleIconException extends ilSystemStyleExceptionBase
{
    public const IMAGES_FOLDER_DOES_NOT_EXIST = 1001;
    public const ICON_DOES_NOT_EXIST = 1002;

    protected function assignMessageToCode() : void
    {
        switch ($this->code) {
            case self::IMAGES_FOLDER_DOES_NOT_EXIST:
                $this->message = 'Images folder set for this style does not exist or can not be read: ' . $this->add_info;
                break;
            case self::ICON_DOES_NOT_EXIST:
                $this->message = 'The selected Icon does not exit: ' . $this->add_info;
                break;
            default:
                $this->message = 'Unknown Exception ' . $this->add_info;
                break;
        }
    }
}
