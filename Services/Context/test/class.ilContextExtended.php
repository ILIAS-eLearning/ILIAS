<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Extended Service context (factory) class
 *
 * ONLY FOR TESTS!!!!
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version $Id$
 *
 * @ingroup ServicesContext
 */
require_once("Services/Context/classes/class.ilContext.php");

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
