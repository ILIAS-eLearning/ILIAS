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

    public function testIsInterestedInContexts(): void
    {
        $this->markTestSkipped();
    }

    public function testGetMainBarModification(): void
    {
        $this->markTestSkipped();
    }

    public function testGetMetaBarModification(): void
    {
        $this->markTestSkipped();
    }

    public function testGetFooterModification(): void
    {
        $this->markTestSkipped();
    }

    public function testIsKioskModeEnabled(): void
    {
        $this->markTestSkipped();
    }

    public function testGetShortTitleModification(): void
    {
        $this->markTestSkipped();
    }

    public function testGetViewTitleModification(): void
    {
        $this->markTestSkipped();
    }

    public function testGetTitleModification(): void
    {
        $this->markTestSkipped();
    }
}