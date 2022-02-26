<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

require_once 'libs/composer/vendor/autoload.php';

/**
 * Class ilServicesUtilitiesSuite
 */
class ilServicesUtilitiesSuite extends TestSuite
{
  
    public static function suite()
    {
        $suite = new self();

        require 'Services/Utilities/test/ilMimeTypeTest.php';
        $suite->addTestSuite('ilMimeTypeTest');
        require 'Services/Utilities/test/ilUtilsPreventTest.php';
        $suite->addTestSuite('ilUtilsPreventTest');

        return $suite;
    }
}
