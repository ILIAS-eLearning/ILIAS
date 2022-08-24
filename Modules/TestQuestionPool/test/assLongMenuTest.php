<?php
/**
 * Unit tests
 *
 * @author Guido Vollbach <gvollbachdatabay.de>
 *
 * @ingroup ModulesTestQuestionPool
 */
class assLongmenuTest extends assBaseTestCase
{
    protected $backupGlobals = false;


    protected static function getMethod($name): ReflectionMethod
    {
        $class = new ReflectionClass('assLongMenu');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    protected function setUp(): void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');

        parent::setUp();

        require_once './Services/UICore/classes/class.ilCtrl.php';
        $ilCtrl_mock = $this->createMock('ilCtrl');
        $ilCtrl_mock->expects($this->any())->method('saveParameter');
        $ilCtrl_mock->expects($this->any())->method('saveParameterByClass');
        $this->setGlobalVariable('ilCtrl', $ilCtrl_mock);

        require_once './Services/Language/classes/class.ilLanguage.php';
        $lng_mock = $this->createMock('ilLanguage', array('txt'), array(), '', false);
        //$lng_mock->expects( $this->once() )->method( 'txt' )->will( $this->returnValue('Test') );
        $this->setGlobalVariable('lng', $lng_mock);

        $this->setGlobalVariable('ilias', $this->getIliasMock());
        $this->setGlobalVariable('tpl', $this->getGlobalTemplateMock());
        $this->setGlobalVariable('ilDB', $this->getDatabaseMock());
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $instance = new assLongMenu();
        $this->assertInstanceOf('assLongMenu', $instance);
    }

    public function test_getAdditionalTableName_shouldReturnString(): void
    {
        $instance = new assLongMenu();
        $this->assertEquals('qpl_qst_lome', $instance->getAdditionalTableName());
    }

    public function test_getQuestionType_shouldReturnString(): void
    {
        $instance = new assLongMenu();
        $this->assertEquals('assLongMenu', $instance->getQuestionType());
    }

    public function test_getAnswerTableName_shouldReturnString(): void
    {
        $instance = new assLongMenu();
        $this->assertEquals('qpl_a_lome', $instance->getAnswerTableName());
    }

    public function test_correctAnswerDoesNotExistInAnswerOptions_shouldReturnTrue(): void
    {
        $method = self::getMethod('correctAnswerDoesNotExistInAnswerOptions');
        $obj = new assLongMenu();
        $value = $method->invokeArgs($obj, array(array(array(5),1,1), array(1,2,3,4)));
        $this->assertEquals(true, $value);
    }

    public function test_correctAnswerDoesNotExistInAnswerOptions_shouldReturnFalse(): void
    {
        $method = self::getMethod('correctAnswerDoesNotExistInAnswerOptions');
        $obj = new assLongMenu();
        $value = $method->invokeArgs($obj, array(array(array(1),1,1), array(1,2,3,4)));
        $this->assertEquals(false, $value);
    }

    public function test_getMaximumPoints_shouldBeFour(): void
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2', 2 => '1'),
                                        1 => array( 0 => array(0 => 'answer'),1 => '2', 2 => '1')));
        $value = $obj->getMaximumPoints();
        $this->assertEquals(4, $value);
    }

    public function test_getMaximumPoints_shouldBeFourPointFive(): void
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1'),
                                           1 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1')));
        $value = $obj->getMaximumPoints();
        $this->assertEquals(4.5, $value);
    }

    public function test_isComplete_shouldBeFalse(): void
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1'),
                                           1 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1')));
        $obj->setAnswers(array(array(1,2,3,4)));
        $this->assertEquals($obj->isComplete(), false);
    }

    public function test_isComplete_shouldBeTrue(): void
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1'),
                                           1 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1')));
        $obj->setAnswers(array(array(1,2,3,4)));
        $obj->setAuthor("Tester");
        $obj->setPoints(4.5);
        $obj->setTitle('LongMenu Title');
        $obj->setLongMenuTextValue('LongMenu Question');
        $this->assertEquals($obj->isComplete(), true);
    }

    public function test_checkQuestionCustomPart_shouldBeFalseBecauseNoCustomPart(): void
    {
        $obj = new assLongMenu();
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }

    public function test_checkQuestionCustomPart_shouldBeFalseBecauseOnlyAnswers(): void
    {
        $obj = new assLongMenu();
        $obj->setAnswers(array(array(1,2,3,4)));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }

    public function test_checkQuestionCustomPart_shouldBeFalseBecauseOnlyCorrectAnswers(): void
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1'),
                                           1 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1')));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }
    public function test_checkQuestionCustomPart_shouldBeFalseBecauseToManyCorrectAnswers(): void
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1'),
                                        1 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1')));
        $obj->setAnswers(array(array('answer')));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }
    public function test_checkQuestionCustomPart_shouldBeFalseBecauseCorrectAnswerDoesNotExistsInAnswers(): void
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(0 => 'answer'),1 => '2.25', 2 => '1')));
        $obj->setAnswers(array(array(1)));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }

    public function test_checkQuestionCustomPart_shouldBeFalseBecauseCorrectAnswerHasNoAnswers(): void
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array(),1 => '2.25', 2 => '1')));
        $obj->setAnswers(array(array('answer')));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }

    public function test_checkQuestionCustomPart_shouldBeFalseBecauseCorrectAnswerHasNoPoints(): void
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array())));
        $obj->setAnswers(array(array('answer')));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }

    public function test_checkQuestionCustomPart_shouldBeFalseBecauseCorrectAnswerPointsAreZero(): void
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array('answer'),1 => 0, 2 => '1')));
        $obj->setAnswers(array(array('answer')));
        $this->assertEquals($obj->checkQuestionCustomPart(), false);
    }

    public function test_checkQuestionCustomPart_shouldBeTrue(): void
    {
        $obj = new assLongMenu();
        $obj->setCorrectAnswers(array(	0 => array( 0 => array('answer'),1 => 1, 2 => '1')));
        $obj->setAnswers(array(array('answer')));
        $this->assertEquals($obj->checkQuestionCustomPart(), true);
    }

    public function test_getSolutionSubmit_shouldReturnSolution(): void
    {
        $obj = new assLongMenu();
        $array = array( 0 => 'squirrel', 1 => 'icebear');
        $_POST['answer'] = $array;
        $this->assertEquals($obj->getSolutionSubmit(), $array);
    }

    public function test_setAnswerType_shouldReturnGetAnswerType(): void
    {
        $obj = new assLongMenu();
        $obj->setAnswerType(0);
        $this->assertEquals(0, $obj->getAnswerType());
    }
    public function test_setLongMenuTextValue_shouldReturnGetLongMenuTextValue(): void
    {
        $obj = new assLongMenu();
        $this->assertEquals('', $obj->getLongMenuTextValue());
        $obj->setLongMenuTextValue('dummy text');
        $this->assertEquals('dummy text', $obj->getLongMenuTextValue());
    }

    public function test_setJsonStructure_shouldReturnGetJsonStructure(): void
    {
        $obj = new assLongMenu();
        $obj->setJsonStructure(json_encode(array(1 => 'bla')));
        $this->assertEquals('{"1":"bla"}', $obj->getJsonStructure());
    }

    public function test_isShuffleAnswersEnabled_shouldReturnFalse(): void
    {
        $obj = new assLongMenu();
        $this->assertEquals(false, $obj->isShuffleAnswersEnabled());
        $this->assertNotEquals(true, $obj->isShuffleAnswersEnabled());
    }
}
