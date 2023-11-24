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

class ilTestManScoringParticipantNotificationTest extends ilTestBaseTestCase
{
    private ilTestManScoringParticipantNotification $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilClientIniFile();
        $this->addGlobal_ilLoggerFactory();

        $this->testObj = new ilTestManScoringParticipantNotification(0, 0);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestManScoringParticipantNotification::class, $this->testObj);
    }

    /**
     * @dataProvider dataProviderGetAndSetRecipient
     */
    public function testGetAndSetRecipient(int $IO): void
    {
        $this->assertNull(self::callMethod($this->testObj, 'setRecipient', [$IO]));
        $this->assertEquals($IO, self::callMethod($this->testObj, 'getRecipient'));
    }

    public function dataProviderGetAndSetRecipient(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }
}