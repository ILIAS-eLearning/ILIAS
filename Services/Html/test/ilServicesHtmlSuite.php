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
    /**
     * @return self
     * @throws ReflectionException
     */
    public static function suite() : self
    {
        $suite = new self();

        require_once 'ilHtmlPurifierCompositeTest.php';
        $suite->addTestSuite(ilHtmlPurifierCompositeTest::class);

        return $suite;
    }
}
