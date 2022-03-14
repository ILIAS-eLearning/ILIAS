<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Object not found exception
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectNotFoundException extends ilObjectException
{
    /**
     * A message is not optional as in build in class Exception
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
