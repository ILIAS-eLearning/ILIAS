<?php declare(strict_types=1);

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilBuddySystemBaseTest extends TestCase
{
    /**
     * @param string $name
     * @param mixed $value
     */
    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        if (!$DIC) {
            $DIC = new Container();
        }

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }
}