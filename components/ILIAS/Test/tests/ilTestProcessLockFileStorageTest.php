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
}