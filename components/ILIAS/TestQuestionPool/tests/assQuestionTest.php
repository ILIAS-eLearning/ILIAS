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

/**
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*
* This test was automatically generated.
*/
class assQuestionTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private assQuestion $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new class extends assQuestion{
            public function isComplete(): bool
            {
                return true;
            }

            public function saveWorkingData(int $active_id, int $pass, bool $authorized = true): bool
            {
                return true;
            }

            public function getAdditionalTableName(){}

            public function getAnswerTableName(){}

            public function calculateReachedPoints($active_id, $pass = null, $authorizedSolution = true, $returndetails = false){}

            public function getQuestionType(): string
            {
                return '';
            }

            public function duplicate(bool $for_test = true, string $title = "", string $author = "", string $owner = "", $testObjId = null): int
            {
                return 0;
            }
        };
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(assQuestion::class, $this->object);
    }
}