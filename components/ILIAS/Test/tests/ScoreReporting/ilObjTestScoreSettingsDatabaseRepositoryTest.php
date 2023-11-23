<?php

namespace ScoreReporting;

use ilObjTestScoreSettingsDatabaseRepository;
use ilTestBaseTestCase;

class ilObjTestScoreSettingsDatabaseRepositoryTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilObjTestScoreSettingsDatabaseRepository = new ilObjTestScoreSettingsDatabaseRepository(
            $this->createMock(\ilDBInterface::class),
        );
        $this->assertInstanceOf(ilObjTestScoreSettingsDatabaseRepository::class, $ilObjTestScoreSettingsDatabaseRepository);
    }

    public function testGetForObjFi(): void
    {
        $this->markTestSkipped();
    }

    public function testGetFor(): void
    {
        $this->markTestSkipped();
    }

    public function testDoSelect(): void
    {
        $this->markTestSkipped();
    }

    public function testStore(): void
    {
        $this->markTestSkipped();
    }
}