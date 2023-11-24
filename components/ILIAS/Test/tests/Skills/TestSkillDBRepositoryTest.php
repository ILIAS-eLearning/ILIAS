<?php

namespace Skills;

use ILIAS\Test\Skills\TestSkillDBRepository;
use ilTestBaseTestCase;

class TestSkillDBRepositoryTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $testSkillDBRepository = new TestSkillDBRepository();
        $this->assertInstanceOf(TestSkillDBRepository::class, $testSkillDBRepository);

        $testSkillDBRepository = new TestSkillDBRepository($this->createMock(\ilDBInterface::class));
        $this->assertInstanceOf(TestSkillDBRepository::class, $testSkillDBRepository);
    }
}