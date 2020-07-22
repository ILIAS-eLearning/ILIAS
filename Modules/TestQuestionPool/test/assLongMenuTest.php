<?php
/**
 * Unit tests
 *
 * @author Guido Vollbach <gvollbachdatabay.de>
 *
 * @ingroup ModulesTestQuestionPool
 */
class assLongmenuTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;


    protected static function getMethod($name)
    {
        $class = new ReflectionClass('assLongMenu');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
    
    protected function setUp()
    {
        $GLOBALS['DIC']['ilDB'] = 'test';

        require_once './Modules/TestQuestionPool/classes/class.assLongMenu.php';
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
        $instance = new assLongMenu();
        $this->assertInstanceOf('assLongMenu', $instance);
    }

    public function test_getAdditionalTableName_shouldReturnString()
    {
        $instance = new assLongMenu();
        $this->assertEquals('qpl_qst_lome', $instance->getAdditionalTableName());
    }

    public function test_getQuestionType_shouldReturnString()
    {
        $instance = new assLongMenu();
        $this->assertEquals('assLongMenu', $instance->getQuestionType());
    }
    
    public function test_getAnswerTableName_shouldReturnString()
    {
        $instance = new assLongMenu();
        $this->assertEquals('qpl_a_lome', $instance->getAnswerTableName());
    }

    public function test_correctAnswerDoesNotExistInAnswerOptions_shouldReturnTrue()
    {
        $method = self::getMethod('correctAnswerDoesNotExistInAnswerOptions');
        $obj = new assLongMenu();
        $value = $method->invokeArgs($obj, array(array(array(5),1,1), array(1,2,3,4)));
        $this->assertEquals(true, $value);
    }

    public function test_correctAnswerDoesNotExistInAnswerOptions_shouldReturnFalse()
    {
        $method = self::getMethod('correctAnswerDoesNotExistInAnswerOptions');
        $obj = new assLongMenu();
        $value = $method->invokeArgs($obj, array(array(array(1),1,1), array(1,2,3,4)));
        $this->assertEquals(false, $value);
    }

    public function test_getMaximumPoints_shouldBeFour()
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2', 2 => '1'),
                                        1 => array( 0 => array(0 => 'answer'),1 => '2', 2 => '1')));
        $value = $obj->getMaximumPoints();
        $this->assertEquals(4, $value);
    }

    public function test_getMaximumPoints_shouldBeFourPointFive()
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1'),
                                           1 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1')));
        $value = $obj->getMaximumPoints();
        $this->assertEquals(4.5, $value);
    }

    public function test_isComplete_shouldBeFalse()
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1'),
                                           1 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1')));
        $obj->setAnswers(array(array(1,2,3,4)));
        $this->assertEquals($obj->isComplete(), false);
    }

    public function test_isComplete_shouldBeTrue()
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1'),
                                           1 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1')));
        $obj->setAnswers(array(array(1,2,3,4)));
        $obj->setPoints(4.5);
        $obj->setTitle('LongMenu Title');
        $obj->setLongMenuTextValue('LongMenu Question');
        $this->assertEquals($obj->isComplete(), true);
    }

    public function test_checkQuestionCustomPart_shouldBeFalseBecauseNoCustomPart()
    {
        $obj = new assLongMenu();
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }

    public function test_checkQuestionCustomPart_shouldBeFalseBecauseOnlyAnswers()
    {
        $obj = new assLongMenu();
        $obj->setAnswers(array(array(1,2,3,4)));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }

    public function test_checkQuestionCustomPart_shouldBeFalseBecauseOnlyCorrectAnswers()
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1'),
                                           1 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1')));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }
    public function test_checkQuestionCustomPart_shouldBeFalseBecauseToManyCorrectAnswers()
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1'),
                                        1 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1')));
        $obj->setAnswers(array(array('answer')));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }
    public function test_checkQuestionCustomPart_shouldBeFalseBecauseCorrectAnswerDoesNotExistsInAnswers()
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1')));
        $obj->setAnswers(array(array(1)));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }

    public function test_checkQuestionCustomPart_shouldBeFalseBecauseCorrectAnswerHasNoAnswers()
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(),1 => '2.25', 2 => '1')));
        $obj->setAnswers(array(array('answer')));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }

    public function test_checkQuestionCustomPart_shouldBeFalseBecauseCorrectAnswerHasNoPoints()
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array())));
        $obj->setAnswers(array(array('answer')));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }

    public function test_checkQuestionCustomPart_shouldBeFalseBecauseCorrectAnswerPointsAreZero()
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array('answer'),1 => 0, 2 => '1')));
        $obj->setAnswers(array(array('answer')));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }

    public function test_checkQuestionCustomPart_shouldBeTrue()
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array('answer'),1 => 1, 2 => '1')));
        $obj->setAnswers(array(array('answer')));
        $this->assertEquals($obj->checkQuestionCustomPart(), true);
    }

    public function test_getSolutionSubmit_shouldReturnSolution()
    {
        $obj = new assLongMenu();
        $array = array( 0 => 'squirrel', 1 => 'icebear');
        $_POST['answer'] = $array;
        $this->assertEquals($obj->getSolutionSubmit(), $array);
    }

    public function test_setAnswerType_shouldReturnGetAnswerType()
    {
        $obj = new assLongMenu();
        $obj->setAnswerType(0);
        $this->assertEquals(0, $obj->getAnswerType());
    }
    public function test_setLongMenuTextValue_shouldReturnGetLongMenuTextValue()
    {
        $obj = new assLongMenu();
        $this->assertEquals('', $obj->getLongMenuTextValue());
        $obj->setLongMenuTextValue('dummy text');
        $this->assertEquals('dummy text', $obj->getLongMenuTextValue());
    }

    public function test_setJsonStructure_shouldReturnGetJsonStructure()
    {
        $obj = new assLongMenu();
        $obj->setJsonStructure(json_encode(array(1 => 'bla')));
        $this->assertEquals('{"1":"bla"}', $obj->getJsonStructure());
    }

    public function test_isShuffleAnswersEnabled_shouldReturnFalse()
    {
        $obj = new assLongMenu();
        $this->assertEquals(false, $obj->isShuffleAnswersEnabled());
        $this->assertNotEquals(true, $obj->isShuffleAnswersEnabled());
    }
}
