<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Class ilLanguageBaseTest
 * @author  Sílvia Mariné <silvia.marine@kroepelin-projekte.de>
 */
abstract class ilLanguageBaseTest extends TestCase
{
    protected function setUp() : void
    {
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
