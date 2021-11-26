<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ilUICoreSetupAgent;
use ILIAS\Setup\NullConfig;
use ILIAS\Setup\Objective;

/**
 * Class UICoreSetupAgentTest
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilUICoreSetupAgentTest extends TestCase
{
    private ilUICoreSetupAgent $testObj;

    protected function setUp() : void
    {
        parent::setUp();
        $this->testObj = new ilUICoreSetupAgent();
    }

    public function testGetNamedObjectives() : void
    {
        $this->assertArrayHasKey(
            "reloadCtrlStructure",
            $this->testObj->getNamedObjectives(new NullConfig())
        );
    }

    public function testExecuteClosure() : void
    {
        $objectiveConstructor = $this->testObj->getNamedObjectives(new NullConfig())["reloadCtrlStructure"];
        $closureResult = $objectiveConstructor->create();
        $this->assertInstanceOf(Objective::class, $closureResult);
    }
}
