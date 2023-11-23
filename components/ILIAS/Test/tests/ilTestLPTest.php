<?php

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
            [false, [ilLPObjSettings::LP_MODE_DEACTIVATED, ilLPObjSettings::LP_MODE_TEST_FINISHED, ilLPObjSettings::LP_MODE_TEST_PASSED]],
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

    public function testIsAnonymized(): void
    {
        $this->markTestSkipped();
    }

    public function testSetTestObject(): void
    {
        $this->markTestSkipped();
    }

    public function testResetCustomLPDataForUserIds(): void
    {
        $this->markTestSkipped();
    }

    public function testIsLPMember(): void
    {
        $this->markTestSkipped();
    }
}