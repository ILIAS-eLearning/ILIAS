<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'class.ilSystemStyleExceptionBase.php';

/**
 * Class for advanced editing exception handling in ILIAS.
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 *
 */
class ilSystemStyleIconException extends ilSystemStyleExceptionBase
{
    const IMAGES_FOLDER_DOES_NOT_EXIST = 1001;
    const ICON_DOES_NOT_EXIST = 1002;

    protected function assignMessageToCode()
    {
        switch ($this->code) {
            case self::IMAGES_FOLDER_DOES_NOT_EXIST:
                $this->message = "Images folder set for this style does not exist or can not be read: " . $this->add_info;
                break;
            case self::ICON_DOES_NOT_EXIST:
                $this->message = "The selected Icon does not exit: " . $this->add_info;
                break;
            default:
                $this->message = "Unknown Exception " . $this->add_info;
                break;
        }
    }
}
