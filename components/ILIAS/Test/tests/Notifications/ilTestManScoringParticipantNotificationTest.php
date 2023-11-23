<?php

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

    public function tetSend(): void
    {
        $this->markTestSkipped();
    }

    public function testBuildSubject(): void
    {
        $this->markTestSkipped();
    }

    public function testBuildBody(): void
    {
        $this->markTestSkipped();
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

    public function testConvertFeedbackForMail(): void
    {
        $this->markTestSkipped();
    }
}