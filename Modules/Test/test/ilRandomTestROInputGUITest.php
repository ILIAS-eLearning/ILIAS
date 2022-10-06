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

/**
 * Class ilRandomTestROInputGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilRandomTestROInputGUITest extends ilTestBaseTestCase
{
    private ilRandomTestROInputGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_lng();

        $this->testObj = new ilRandomTestROInputGUI();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilRandomTestROInputGUI::class, $this->testObj);
    }

    public function testSetValues(): void
    {
        $expected = [
            "test" => "test2",
            "hello" => "world"
        ];
        $this->testObj->setValues($expected);
        $this->assertEquals($this->testObj->getValues(), $expected);
    }
}
