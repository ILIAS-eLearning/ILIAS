<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesSearchSuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new ilServicesSearchSuite();

        include_once("./Services/Search/test/ilSearchLuceneQueryParserTest.php");
        $suite->addTestSuite(ilSearchLuceneQueryParserTest::class);
        return $suite;
    }
}
