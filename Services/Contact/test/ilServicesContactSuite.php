<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

require_once __DIR__ . '/bootstrap.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilServicesContactSuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new self();

        $suite->addTestSuite(ilBuddySystemTestSuite::class);

        return $suite;
    }
}
