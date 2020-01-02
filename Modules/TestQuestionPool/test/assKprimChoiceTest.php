<?php
/**
 * Unit tests
 *
 * @author Guido Vollbach <gvollbachdatabay.de>
 *
 * @ingroup ModulesTestQuestionPool
 */
class assKprimChoiceTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        require_once './Modules/TestQuestionPool/classes/class.assKprimChoice.php';
        require_once './Modules/TestQuestionPool/classes/class.ilAssKprimChoiceAnswer.php';
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');
        }
        require_once './Services/Utilities/classes/class.ilUtil.php';
    }

    public function test_instantiateObject_shouldReturnInstance()
    {
        $instance = new assKprimChoice();
        $this->assertInstanceOf('assKprimChoice', $instance);
    }
    
    public function test_getQuestionType_shouldReturnQuestionType()
    {
        $obj = new assKprimChoice();
        $this->assertEquals('assKprimChoice', $obj->getQuestionType());
    }

    public function test_getAdditionalTableName_shouldReturnAdditionalTableName()
    {
        $obj = new assKprimChoice();
        $this->assertEquals('qpl_qst_kprim', $obj->getAdditionalTableName());
    }

    public function test_getAnswerTableName_shouldReturnAnswerTableName()
    {
        $obj = new assKprimChoice();
        $this->assertEquals('qpl_a_kprim', $obj->getAnswerTableName());
    }

    public function test_isCompleteWithoutAnswer_shouldReturnTrue()
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

    public function test_isCompleteWithAnswer_shouldReturnTrue()
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
    
    public function test_isValidOptionLabel_shouldReturnTrue()
    {
        $obj = new assKprimChoice();
        $this->assertEquals(false, $obj->isValidOptionLabel('not valid'));
        $this->assertEquals(true, $obj->isValidOptionLabel($obj::OPTION_LABEL_RIGHT_WRONG));
    }

    public function test_isObligationPossible_shouldReturnTrue()
    {
        $obj = new assKprimChoice();
        $this->assertEquals(true, $obj->isObligationPossible(1));
    }

    public function test_getAnswer_shouldReturnAnswer()
    {
        $obj = new assKprimChoice();
        $ans = new ilAssKprimChoiceAnswer();
        $ans->setCorrectness(true);
        $ans->setAnswertext('Text');
        $obj->addAnswer($ans);
        $this->assertInstanceOf('ilAssKprimChoiceAnswer', $obj->getAnswer(0));
        $this->assertEquals(null, $obj->getAnswer(1));
    }
    
    public function test_isValidAnswerType_shouldReturnTrue()
    {
        $obj = new assKprimChoice();
        $this->assertEquals(false, $obj->isValidAnswerType('not valid'));
        $this->assertEquals(true, $obj->isValidAnswerType($obj::ANSWER_TYPE_SINGLE_LINE));
    }
}
