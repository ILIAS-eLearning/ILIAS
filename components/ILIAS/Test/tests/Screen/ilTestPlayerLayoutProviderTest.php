<?php

namespace Screen;

use ILIAS\DI\Container;
use ilTestBaseTestCase;
use ilTestPlayerLayoutProvider;

class ilTestPlayerLayoutProviderTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestPlayerLayoutProvider = new ilTestPlayerLayoutProvider($this->createMock(Container::class));
        $this->assertInstanceOf(ilTestPlayerLayoutProvider::class, $ilTestPlayerLayoutProvider);
    }
}