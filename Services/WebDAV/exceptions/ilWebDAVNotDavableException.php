<?php declare(strict_types = 1);

class ilWebDAVNotDavableException extends ilException
{
    public const OBJECT_TYPE_NOT_DAVABLE = 'This object type is not davable!';
    public const FILE_EXTENSION_NOT_ALLOWED = 'This object has a forbidden file extension!';
    public const OBJECT_TITLE_NOT_DAVABLE = 'This object title is invalid or hidden!';
    
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
