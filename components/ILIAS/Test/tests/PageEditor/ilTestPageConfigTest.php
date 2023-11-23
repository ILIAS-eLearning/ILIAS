<?php

namespace PageEditor;

use ilTestBaseTestCase;
use ilTestPageConfig;

class ilTestPageConfigTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestPageConfig = new ilTestPageConfig();
        $this->assertInstanceOf(ilTestPageConfig::class, $ilTestPageConfig);
    }
}