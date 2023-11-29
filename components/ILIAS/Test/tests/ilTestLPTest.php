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

class ilTestLPTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestLPTest = new ilTestLP(0);
        $this->assertInstanceOf(ilTestLP::class, $ilTestLPTest);
    }

    /**
     * @dataProvider getDefaultModesDataProvider
     */
    public function testGetDefaultModes(bool $input, array $output): void
    {
        $this->assertEquals($output, ilTestLP::getDefaultModes($input));
    }

    public function getDefaultModesDataProvider(): array
    {
        return [
            [true, [ilLPObjSettings::LP_MODE_DEACTIVATED, ilLPObjSettings::LP_MODE_TEST_FINISHED, ilLPObjSettings::LP_MODE_TEST_PASSED]],
            [false, [ilLPObjSettings::LP_MODE_DEACTIVATED, ilLPObjSettings::LP_MODE_TEST_FINISHED, ilLPObjSettings::LP_MODE_TEST_PASSED]]
        ];
    }

    public function testGetDefaultMode(): void
    {
        $ilTestLP = new ilTestLP(0);
        $this->assertEquals(ilLPObjSettings::LP_MODE_TEST_PASSED, $ilTestLP->getDefaultMode());
    }

    public function testGetValidModes(): void
    {
        $ilTestLP = new ilTestLP(0);
        $this->assertEquals([ilLPObjSettings::LP_MODE_DEACTIVATED, ilLPObjSettings::LP_MODE_TEST_FINISHED, ilLPObjSettings::LP_MODE_TEST_PASSED], $ilTestLP->getValidModes());
    }
}