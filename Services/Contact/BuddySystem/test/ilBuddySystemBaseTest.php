<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilBuddySystemBaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $name
     * @param mixed $value
     */
    protected function setGlobalVariable($name, $value)
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }

    /**
     * @param string $exception_class
     */
    protected function assertException($exception_class)
    {
        if (version_compare(PHPUnit_Runner_Version::id(), '5.0', '>=')) {
            $this->setExpectedException($exception_class);
        }
    }
}
