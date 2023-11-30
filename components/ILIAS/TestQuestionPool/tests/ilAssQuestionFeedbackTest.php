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
class ilAssQuestionFeedbackTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilAssQuestionFeedback $object;

    protected function setUp(): void
    {
        parent::setUp();

        $questionOBJ = $this->createMock(assQuestion::class);
        $ctrl = $this->createMock(ilCtrl::class);
        $db = $this->createMock(ilDBInterface::class);
        $lng = $this->createMock(ilLanguage::class);

        $this->object = new class($questionOBJ, $ctrl, $db, $lng) extends ilAssQuestionFeedback{
            public function getSpecificAnswerFeedbackTestPresentation(int $questionId, int $questionIndex, int $answerIndex): string
            {
                return '';
            }

            public function completeSpecificFormProperties(ilPropertyFormGUI $form): void {}

            public function initSpecificFormProperties(ilPropertyFormGUI $form): void {}

            public function saveSpecificFormProperties(ilPropertyFormGUI $form): void {}

            public function getSpecificAnswerFeedbackContent(int $questionId, int $questionIndex, int $answerIndex): string
            {
                return '';
            }

            public function getAllSpecificAnswerFeedbackContents(int $questionId): string
            {
                return '';
            }

            public function saveSpecificAnswerFeedbackContent(int $questionId, int $questionIndex, int $answerIndex, string $feedbackContent): int
            {
                return 0;
            }

            public function deleteSpecificAnswerFeedbacks(int $questionId, bool $isAdditionalContentEditingModePageObject): void {}

            protected function duplicateSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId): void {}

            protected function isSpecificAnswerFeedbackId(int $feedbackId): bool
            {
                return true;
            }

            protected function syncSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId): void {}

            public function getSpecificAnswerFeedbackExportPresentation(int $questionId, int $questionIndex, int $answerIndex): string
            {
                return '';
            }

            public function importSpecificAnswerFeedback(int $questionId, int $questionIndex, int $answerIndex, string $feedbackContent): void {}
        };
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilAssQuestionFeedback::class, $this->object);
    }
}