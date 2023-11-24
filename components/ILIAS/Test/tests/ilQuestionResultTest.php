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

namespace Test\tests;
use ilQuestionResult;
use ilTestBaseTestCase;

class ilQuestionResultTest extends ilTestBaseTestCase
{
    private ilQuestionResult $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilQuestionResult(
            0,
            '',
            '',
            0.0,
            0.0,
           '',
           '',
           '',
           true,
           true,
           '',
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilQuestionResult::class, $this->testObj);
    }
}