<?php

class ilTestMailNotificationTest extends ilTestBaseTestCase
{
    private ilTestMailNotification $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilClientIniFile();
        $this->addGlobal_ilLoggerFactory();

        $this->testObj = new ilTestMailNotification();
    }
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestMailNotification::class, $this->testObj);
    }
}