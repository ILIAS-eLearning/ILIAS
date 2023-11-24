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

use ILIAS\Test\TestManScoringDoneHelper;

class TestManScoringDoneHelperTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $testManScoringDoneHelper = new TestManScoringDoneHelper();
        $this->assertInstanceOf(TestManScoringDoneHelper::class, $testManScoringDoneHelper);

        $testManScoringDoneHelper = new TestManScoringDoneHelper($this->createMock(ilDBInterface::class));
        $this->assertInstanceOf(TestManScoringDoneHelper::class, $testManScoringDoneHelper);
    }
}