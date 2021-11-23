<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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
