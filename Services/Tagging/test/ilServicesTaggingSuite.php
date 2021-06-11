<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

require_once 'libs/composer/vendor/autoload.php';

/**
 * Exercise test suite
 * @author Alexander Killing <killing@leifos.de>
 */
class ilServicesTaggingSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        require_once("./Services/Tagging/test/Assignment/TaggingTest.php");
        $suite->addTestSuite("TaggingTest");

        return $suite;
    }
}
