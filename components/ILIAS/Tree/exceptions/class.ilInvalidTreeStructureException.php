<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Thrown if invalid tree strucutes are found
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesTree
 */
class ilInvalidTreeStructureException extends ilException
{
    public function __construct($a_message, $a_code = 0)
    {
        parent::__construct($a_message, $a_code);
    }
}
