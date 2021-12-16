<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * Class ilMailBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilMailBaseTest extends TestCase
{
    protected function brutallyTrimHTML(string $html) : string
    {
        $html = str_replace(["\n", "\r", "\t"], "", $html);
        $html = preg_replace('# {2,}#', " ", $html);
        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);
        $html = preg_replace("/>(\s+)</", "><", $html);
        $html = str_replace([" >", " <"], [">", "<"], $html);

        return trim($html);
    }

    protected function setUp() : void
    {
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }

        $GLOBALS['DIC'] = new Container();

        parent::setUp();
    }

    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (Container $c) use ($name) {
            return $GLOBALS[$name];
        };
    }
}
