<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Extended Service context (factory) class
 *
 * ONLY FOR TESTS!!!!
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilContextExtended extends ilContext
{
    public static function getClassName() : string
    {
        return self::$class_name;
    }
}
