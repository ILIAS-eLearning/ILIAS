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
class ilSystemStyleColorException extends ilSystemStyleExceptionBase
{
    const INVALID_COLOR_EXCEPTION = 1001;


    protected function assignMessageToCode()
    {
        switch ($this->code) {
            case self::INVALID_COLOR_EXCEPTION:
                $this->message = "Invalid Color value";
                break;
            default:
                $this->message = "Unknown Exception " . $this->add_info;
                break;
        }
    }
}
