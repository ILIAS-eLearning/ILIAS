<?php

class ilTestProcessLockFileStorageTest extends ilTestBaseTestCase
{
    private ilTestProcessLockFileStorage $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_filesystem();

        $this->testObj = new ilTestProcessLockFileStorage(0);
    }
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestProcessLockFileStorage::class, $this->testObj);
    }

    public function testGetPathPrefix(): void
    {
        $this->assertEquals('ilTestProcessLocks', self::callMethod($this->testObj, 'getPathPrefix'));
    }

    public function testGetPathPostfix(): void
    {
        $this->assertEquals('context', self::callMethod($this->testObj, 'getPathPostfix'));
    }

    public function testCreate(): void
    {
        $this->markTestSkipped();
    }
}