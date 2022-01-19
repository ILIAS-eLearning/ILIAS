<?php

declare(strict_types=1);

/**
 * Class for advanced editing exception handling in ILIAS.
 */
class ilSystemStyleMessageStackException extends ilSystemStyleExceptionBase
{
    public const MESSAGE_STACK_TYPE_ID_DOES_NOT_EXIST = 1001;


    protected function assignMessageToCode() : void
    {
        switch ($this->code) {
            case self::MESSAGE_STACK_TYPE_ID_DOES_NOT_EXIST:
                $this->message = 'Type id does not exist in message stack';
                break;
            default:
                $this->message = 'Unknown Exception ' . $this->add_info;
                break;
        }
    }
}
