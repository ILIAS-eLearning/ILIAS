<?php

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
 * @author  Stefan Meyer <meyer@leifos.de>
 * @ingroup ServicesTree
 */
class ilTrackingCollectionTest extends TestCase
{
    protected Container $dic;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initDependencies();
    }

    public function testCollectionInstance(): void
    {
        $objectives = ilLPCollection::getInstanceByMode(
            0,
            ilLPObjSettings::LP_MODE_OBJECTIVES
        );
        $this->assertInstanceOf(
            ilLPCollectionOfObjectives::class,
            $objectives
        );
    }

    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (Container $c) use ($value) {
            return $value;
        };
    }

    protected function initDependencies(): void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;
        $this->setGlobalVariable(
            'ilDB',
            $this->createMock(ilDBInterface::class)
        );
        $logger = $this->getMockBuilder(ilLogger::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $logger_factory = $this->getMockBuilder(ilLoggerFactory::class)
                               ->disableOriginalConstructor()
                               ->onlyMethods(['getComponentLogger'])
                               ->getMock();
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);
    }
}
