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
 *********************************************************************/

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemBaseTest extends TestCase
{
    private ?Container $dic = null;

    protected function setUp(): void
    {
        global $DIC;

        parent::setUp();

        $this->dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }

    /**
     * @param mixed $value
     */
    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }
}
