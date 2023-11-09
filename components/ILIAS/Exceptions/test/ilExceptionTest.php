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


use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Class ilExceptionTest
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilExceptionTest extends TestCase
{
    protected Container $dic;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testConstruct(): void
    {
        $exception = new ilException('My fault');
        $this->assertInstanceOf(ilException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }
}
