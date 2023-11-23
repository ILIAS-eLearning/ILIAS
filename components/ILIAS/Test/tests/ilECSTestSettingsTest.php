<?php

class ilECSTestSettingsTest extends ilTestBaseTestCase
{
    private ilECSTestSettings $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilLoggerFactory();
        $this->addGlobal_ilRbacAdmin();

        $this->testObj = new ilECSTestSettings($this->createMock(ilObject::class));
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilECSTestSettings::class, $this->testObj);
    }

    public function testGetECSObjectType(): void
    {
        $this->assertEquals('/campusconnect/tests', self::callMethod($this->testObj, 'getECSObjectType'));
    }

    public function testBuildJson(): void
    {
        $this->markTestSkipped();
    }
}