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

declare(strict_types=1);

class ilObjTestSettingsTestBehaviourTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider getAndWithNumberOfTriesDataProvider
     */
    public function testGetAndWithNumberOfTries(int $IO): void
    {
        $ilObjTestSettingsTestBehaviour = new ilObjTestSettingsTestBehaviour(0);
        $ilObjTestSettingsTestBehaviour = $ilObjTestSettingsTestBehaviour->withNumberOfTries($IO);

        $this->assertInstanceOf(ilObjTestSettingsTestBehaviour::class, $ilObjTestSettingsTestBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsTestBehaviour->getNumberOfTries());
    }

    public function getAndWithNumberOfTriesDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider getAndWithBlockAfterPassedEnabledDataProvider
     */
    public function testGetAndWithBlockAfterPassedEnabled(): void
    {
        $ilObjTestSettingsTestBehaviour = new ilObjTestSettingsTestBehaviour(0);
        $ilObjTestSettingsTestBehaviour = $ilObjTestSettingsTestBehaviour->withBlockAfterPassedEnabled(true);

        $this->assertInstanceOf(ilObjTestSettingsTestBehaviour::class, $ilObjTestSettingsTestBehaviour);
        $this->assertTrue($ilObjTestSettingsTestBehaviour->getBlockAfterPassedEnabled());
    }

    public function getAndWithBlockAfterPassedEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithPassWaitingDataProvider
     */
    public function testGetAndWithPassWaiting(?string $IO): void
    {
        $ilObjTestSettingsTestBehaviour = new ilObjTestSettingsTestBehaviour(0);
        $ilObjTestSettingsTestBehaviour = $ilObjTestSettingsTestBehaviour->withPassWaiting($IO);

        $this->assertInstanceOf(ilObjTestSettingsTestBehaviour::class, $ilObjTestSettingsTestBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsTestBehaviour->getPassWaiting());
    }

    public function getAndWithPassWaitingDataProvider(): array
    {
        return [
            [null],
            ['0:0:0:0']
        ];
    }

    /**
     * @dataProvider getAndWithProcessingTimeEnabledDataProvider
     */
    public function testGetAndWithProcessingTimeEnabled(bool $IO): void
    {
        $ilObjTestSettingsTestBehaviour = new ilObjTestSettingsTestBehaviour(0);
        $ilObjTestSettingsTestBehaviour = $ilObjTestSettingsTestBehaviour->withProcessingTimeEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsTestBehaviour::class, $ilObjTestSettingsTestBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsTestBehaviour->getProcessingTimeEnabled());
    }

    public function getAndWithProcessingTimeEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithProcessingTimeDataProvider
     */
    public function testGetAndWithProcessingTime(?string $IO): void
    {
        $ilObjTestSettingsTestBehaviour = new ilObjTestSettingsTestBehaviour(0);
        $ilObjTestSettingsTestBehaviour = $ilObjTestSettingsTestBehaviour->withProcessingTime($IO);

        $this->assertInstanceOf(ilObjTestSettingsTestBehaviour::class, $ilObjTestSettingsTestBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsTestBehaviour->getProcessingTime());
    }

    public function getAndWithProcessingTimeDataProvider(): array
    {
        return [
            [null],
            [''],
            ['string']
        ];
    }

    /**
     * @dataProvider getAndWithResetProcessingTimeDataProvider
     */
    public function testGetAndWithResetProcessingTime(bool $IO): void
    {
        $ilObjTestSettingsTestBehaviour = new ilObjTestSettingsTestBehaviour(0);
        $ilObjTestSettingsTestBehaviour = $ilObjTestSettingsTestBehaviour->withResetProcessingTime($IO);

        $this->assertInstanceOf(ilObjTestSettingsTestBehaviour::class, $ilObjTestSettingsTestBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsTestBehaviour->getResetProcessingTime());
    }

    public function getAndWithResetProcessingTimeDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithKioskModeDataProvider
     */
    public function testGetAndWithKioskMode(int $IO): void
    {
        $ilObjTestSettingsTestBehaviour = new ilObjTestSettingsTestBehaviour(0);
        $ilObjTestSettingsTestBehaviour = $ilObjTestSettingsTestBehaviour->withKioskMode($IO);

        $this->assertInstanceOf(ilObjTestSettingsTestBehaviour::class, $ilObjTestSettingsTestBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsTestBehaviour->getKioskMode());
    }

    public function getAndWithKioskModeDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    // ExamIdInTestPassEnabled
    /**
     * @dataProvider getAndWithExamIdInTestPassEnabledDataProvider
     */
    public function testGetAndWithExamIdInTestPassEnabled(bool $IO): void
    {
        $ilObjTestSettingsTestBehaviour = new ilObjTestSettingsTestBehaviour(0);
        $ilObjTestSettingsTestBehaviour = $ilObjTestSettingsTestBehaviour->withExamIdInTestPassEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsTestBehaviour::class, $ilObjTestSettingsTestBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsTestBehaviour->getExamIdInTestPassEnabled());
    }

    public function getAndWithExamIdInTestPassEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
