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
}