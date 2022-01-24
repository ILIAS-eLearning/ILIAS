<?php

declare(strict_types=1);

/**
 * Class for advanced editing exception handling in ILIAS.
 */
class ilSystemStyleColorException extends ilSystemStyleExceptionBase
{
    public const INVALID_COLOR_EXCEPTION = 1001;

    protected function assignMessageToCode() : void
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
