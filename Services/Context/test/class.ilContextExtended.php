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
    /**
     * Get context className
     *
     * @return int
     */
    public static function getClassName()
    {
        return self::$class_name;
    }
}
