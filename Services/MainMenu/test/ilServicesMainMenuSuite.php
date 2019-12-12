<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\MainMenu\Tests\ResourceBuilderTest;

require_once 'libs/composer/vendor/autoload.php';

/**
 * Class ilServicesMainMenuSuite
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilServicesMainMenuSuite extends \PHPUnit\Framework\TestSuite
{

    /**
     * @return self
     */
    public static function suite()
    {
        $suite = new self();

        require_once 'Services/MainMenu/test/Storage/ResourceBuilderTest.php';
        $suite->addTestSuite(ResourceBuilderTest::class);

        return $suite;
    }
}
