<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesLogging Services/Logging
 */

/**
 * ILIAS Log exception class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id:$
 * @ingroup ServicesLogging
 */
class ilLogException extends ilException
{
    /**
     * Constructor
     */
    public function __construct($a_message, $a_code = 0)
    {
        parent::__construct($a_message, $a_code);
    }
}
