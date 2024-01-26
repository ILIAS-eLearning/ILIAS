<?php declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * Class ilMailBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilMailBaseTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp() : void
    {
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }

        $GLOBALS['DIC'] = new Container();

        parent::setUp();
    }

    /**
     * @param string $name
     * @param $value
     */
    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }
}
