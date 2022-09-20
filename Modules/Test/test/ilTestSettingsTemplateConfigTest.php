<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSettingsTemplateConfigTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSettingsTemplateConfigTest extends ilTestBaseTestCase
{
    private ilTestSettingsTemplateConfig $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSettingsTemplateConfig(
            $this->createMock(ilLanguage::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSettingsTemplateConfig::class, $this->testObj);
    }
}
