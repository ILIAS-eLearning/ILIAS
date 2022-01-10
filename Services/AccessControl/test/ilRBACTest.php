<?php declare(strict_types=1);

/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

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

    protected function setUp() : void
    {
        $this->initACDependencies();
        parent::setUp();
    }

    public function testConstruct() : void
    {
        $system = ilRbacSystem::getInstance();
        $this->assertTrue($system instanceof ilRbacSystem);

        $admin = new ilRbacAdmin();
        $this->assertTrue($admin instanceof ilRbacAdmin);

        $review = new ilRbacReview();
        $this->assertTrue($review instanceof ilRbacReview);
    }

    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function initACDependencies() : void
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
