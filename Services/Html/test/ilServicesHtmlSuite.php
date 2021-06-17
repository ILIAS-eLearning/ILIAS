<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

/**
 * Class ilServicesHtmlSuite
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilServicesHtmlSuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new self();

        require_once 'ilHtmlPurifierCompositeTest.php';
        $suite->addTestSuite(ilHtmlPurifierCompositeTest::class);

        require_once 'ilHtmlPurifierLibWrapperTest.php';
        $suite->addTestSuite(ilHtmlPurifierLibWrapperTest::class);

        require_once 'ilHtmlDomNodeIteratorTest.php';
        $suite->addTestSuite(ilHtmlDomNodeIteratorTest::class);

        return $suite;
    }
}
