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

class ilDBStepReaderTestObject extends ilDBStepReader
{
    public function setStepNumbers(array $arr): void
    {
        $this->step_numbers = $arr;
    }
}

class Test_ilDBStepReader implements ilDatabaseUpdateSteps
{
    public $called = [];

    protected ?ilDBInterface $db = null;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }


    public function step_1()
    {
    }

    // 4 comes before 2 to check if the class gets the sorting right
    public function step_4()
    {
    }

    public function step_2()
    {
    }
}

class ilDBStepReaderTest extends TestCase
{
    public function testObjectCreation(): void
    {
        $obj = new ilDBStepReader();
        $this->assertInstanceOf(ilDBStepReader::class, $obj);
    }

    public function test_getLatestStepNumber(): void
    {
        $obj = new ilDBStepReaderTestObject();
        $this->assertEquals(4, $obj->getLatestStepNumber(Test_ilDBStepReader::class, "step_"));
    }

    public function test_readSteps(): void
    {
        $obj = new ilDBStepReaderTestObject();
        $result = $obj->readStepNumbers(Test_ilDBStepReader::class, "step_");

        $this->assertIsArray($result);
        $this->assertEquals(1, $result[0]);
        $this->assertEquals(2, $result[1]);
        $this->assertEquals(4, $result[2]);
    }
}
