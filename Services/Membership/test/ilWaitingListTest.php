<?php declare(strict_types=1);
    
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
class ilWaitingListTest extends TestCase
{
    protected $backupGlobals = false;

    protected Container $dic;

    protected function setUp() : void
    {
        $this->initDependencies();
        parent::setUp();
    }

    public function testConstruction() : void
    {
        $obj_id = 0;
        $some_waiting_list = new class($obj_id) extends ilWaitingList {
        };
        $instance = new $some_waiting_list($obj_id);
        $this->assertInstanceOf(ilWaitingList::class, $instance);
    }

    protected function initDependencies() : void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
        $this->setGlobalVariable('ilAppEventHandler', $this->createMock(ilAppEventHandler::class));
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
}
