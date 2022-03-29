<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilUserBaseTest extends TestCase
{
    protected function assertException(string $exception_class)
    {
        $this->expectException($exception_class);
    }
}
