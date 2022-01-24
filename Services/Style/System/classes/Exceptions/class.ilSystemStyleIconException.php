<?php

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
