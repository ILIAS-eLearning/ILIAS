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
 * Unit tests for tree table
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesTree
 */
class ilRBACTest extends TestCase
{
    protected $backupGlobals = false;

    protected Container $dic;

    protected function setUp(): void
    {
        $this->initACDependencies();
        parent::setUp();
    }

    public function testConstruct(): void
    {
        $system = ilRbacSystem::getInstance();
        $this->assertTrue($system instanceof ilRbacSystem);

        $admin = new ilRbacAdmin();
        $this->assertTrue($admin instanceof ilRbacAdmin);

        $review = new ilRbacReview();
        $this->assertTrue($review instanceof ilRbacReview);
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

    protected function initACDependencies(): void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
        $this->setGlobalVariable('ilUser', $this->createMock(ilObjUser::class));
        $this->setGlobalVariable('rbacreview', $this->createMock(ilRbacReview::class));
        $this->setGlobalVariable('ilObjDataCache', $this->createMock(ilObjectDataCache::class));
        $this->setGlobalVariable('tree', $this->createMock(ilTree::class));
        $this->setGlobalVariable('http', $this->createMock(\ILIAS\HTTP\Services::class));
        $this->setGlobalVariable('refinery', $this->createMock(\ILIAS\Refinery\Factory::class));

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
