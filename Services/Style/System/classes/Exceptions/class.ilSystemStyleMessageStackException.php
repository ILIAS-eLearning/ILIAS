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
class ilSystemStyleMessageStackException extends ilSystemStyleExceptionBase
{
    const MESSAGE_STACK_TYPE_ID_DOES_NOT_EXIST = 1001;


    protected function assignMessageToCode()
    {
        switch ($this->code) {
            case self::MESSAGE_STACK_TYPE_ID_DOES_NOT_EXIST:
                $this->message = "Type id does not exist in message stack";
                break;
            default:
                $this->message = "Unknown Exception " . $this->add_info;
                break;
        }
    }
}
