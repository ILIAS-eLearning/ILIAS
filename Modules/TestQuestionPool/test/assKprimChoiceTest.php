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
 * @author Guido Vollbach <gvollbachdatabay.de>
 *
 * @ingroup ModulesTestQuestionPool
 */
class assKprimChoiceTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setGlobalVariable('ilias', $this->getIliasMock());
        $this->setGlobalVariable('tpl', $this->getGlobalTemplateMock());
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $instance = new assKprimChoice();
        $this->assertInstanceOf('assKprimChoice', $instance);
    }

    public function test_getQuestionType_shouldReturnQuestionType(): void
    {
        $obj = new assKprimChoice();
        $this->assertEquals('assKprimChoice', $obj->getQuestionType());
    }

    public function test_getAdditionalTableName_shouldReturnAdditionalTableName(): void
    {
        $obj = new assKprimChoice();
        $this->assertEquals('qpl_qst_kprim', $obj->getAdditionalTableName());
    }

    public function test_getAnswerTableName_shouldReturnAnswerTableName(): void
    {
        $obj = new assKprimChoice();
        $this->assertEquals('qpl_a_kprim', $obj->getAnswerTableName());
    }

    public function test_isCompleteWithoutAnswer_shouldReturnTrue(): void
    {
        $obj = new assKprimChoice();
        $this->assertEquals(false, $obj->isComplete());
        $obj->setTitle('Tilte');
        $obj->setAuthor('Me or another');
        $obj->setQuestion('My great Question.');
        $this->assertEquals(false, $obj->isComplete());
        $obj->setPoints(1);
        $this->assertEquals(true, $obj->isComplete());
    }

    public function test_isCompleteWithAnswer_shouldReturnTrue(): void
    {
        $obj = new assKprimChoice();
        $this->assertEquals(false, $obj->isComplete());
        $obj->setTitle('Tilte');
        $obj->setAuthor('Me or another');
        $obj->setQuestion('My great Question.');
        $obj->setPoints(1);
        $ans = new ilAssKprimChoiceAnswer();
        $obj->addAnswer($ans);
        $this->assertEquals(false, $obj->isComplete());
        $ans->setCorrectness(true);
        $obj->addAnswer($ans);
        $this->assertEquals(false, $obj->isComplete());
        $ans->setAnswertext('Text');
        $obj->addAnswer($ans);
        $this->assertEquals(true, $obj->isComplete());
    }

    public function test_isValidOptionLabel_shouldReturnTrue(): void
    {
        $obj = new assKprimChoice();
        $this->assertEquals(false, $obj->isValidOptionLabel('not valid'));
        $this->assertEquals(true, $obj->isValidOptionLabel($obj::OPTION_LABEL_RIGHT_WRONG));
    }

    public function test_isObligationPossible_shouldReturnTrue(): void
    {
        $obj = new assKprimChoice();
        $this->assertEquals(true, $obj->isObligationPossible(1));
    }

    public function test_getAnswer_shouldReturnAnswer(): void
    {
        $obj = new assKprimChoice();
        $ans = new ilAssKprimChoiceAnswer();
        $ans->setCorrectness(true);
        $ans->setAnswertext('Text');
        $obj->addAnswer($ans);
        $this->assertInstanceOf('ilAssKprimChoiceAnswer', $obj->getAnswer(0));
        $this->assertEquals(null, $obj->getAnswer(1));
    }

    public function test_isValidAnswerType_shouldReturnTrue(): void
    {
        $obj = new assKprimChoice();
        $this->assertEquals(false, $obj->isValidAnswerType('not valid'));
        $this->assertEquals(true, $obj->isValidAnswerType($obj::ANSWER_TYPE_SINGLE_LINE));
    }
}
