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
}