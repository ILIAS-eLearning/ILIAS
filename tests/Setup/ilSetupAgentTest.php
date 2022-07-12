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
 
namespace ILIAS\Tests\Setup;

use PHPUnit\Framework\TestCase;
use ILIAS\Setup\NullConfig;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Objective;
use ilSetupAgent;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;

/**
 * Class ilSetupAgentTest
 * @package ILIAS\Tests\Setup
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilSetupAgentTest extends TestCase
{
    private ilSetupAgent $testObj;

    protected function setUp() : void
    {
        $refinery = new Refinery(
            $this->createMock(DataFactory::class),
            $this->createMock(ilLanguage::class)
        );

        parent::setUp();
        $this->testObj = new ilSetupAgent($refinery, $this->createMock(DataFactory::class));
    }

    public function testGetNamedObjectives() : void
    {
        $this->assertArrayHasKey(
            "registerNICKey",
            $this->testObj->getNamedObjectives(new NullConfig())
        );
    }

    public function testExecuteClosure() : void
    {
        $objectiveConstructor = $this->testObj->getNamedObjectives(new NullConfig())["registerNICKey"];
        $closureResult = $objectiveConstructor->create();
        $this->assertInstanceOf(Objective::class, $closureResult);
    }
}
