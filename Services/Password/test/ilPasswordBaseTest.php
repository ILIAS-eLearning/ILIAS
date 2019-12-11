<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
abstract class ilPasswordBaseTest extends PHPUnit_Framework_TestCase
{
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
