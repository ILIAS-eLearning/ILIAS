<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';

/**
 * Class ilServicesUtilitiesSuite
 */
class ilServicesUtilitiesSuite extends \PHPUnit_Framework_TestSuite
{
    /**
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new self();

        require 'Services/Utilities/test/ilMimeTypeTest.php';
        $suite->addTestSuite('ilMimeTypeTest');

        return $suite;
    }
}
