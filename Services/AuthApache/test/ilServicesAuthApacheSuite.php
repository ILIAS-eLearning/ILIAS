<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 * Class ilServicesAuthApacheSuite
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilServicesAuthApacheSuite extends TestSuite
{
    /**
     * @return ilServicesAuthApacheSuite
     */
    public static function suite() : self
    {
        $suite = new self();

        require_once './Services/AuthApache/test/ilWhiteListUrlValidatorTest.php';
        $suite->addTestSuite('ilWhiteListUrlValidatorTest');

        return $suite;
    }
}