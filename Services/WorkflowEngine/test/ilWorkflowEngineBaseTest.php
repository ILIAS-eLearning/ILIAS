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
 * Class ilWorkflowEngineBaseTest
 */
abstract class ilWorkflowEngineBaseTest extends TestCase
{
    private ?Container $dic = null;

    /**
     * @param string $name
     * @param mixed $value
     * @return void
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

    protected function setUp(): void
    {
        parent::setUp();

        global $DIC;

        $this->dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();

        $this->setGlobalVariable('ilDB', $this->getMockBuilder(ilDBInterface::class)->getMock());

        $this->setGlobalVariable(
            'ilAppEventHandler',
            $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->onlyMethods(['raise'])->getMock()
        );

        $this->setGlobalVariable(
            'ilSetting',
            $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods(['delete', 'get', 'set'])->getMock()
        );
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }
}
