<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assClozeTestTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
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
    }

    public function test_instantiateObject_shouldReturnInstance()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';

        // Act
        $instance = new assClozeTest();

        $this->assertInstanceOf('assClozeTest', $instance);
    }

    public function test_cleanQuestionText_shouldReturnCleanedText()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $in_text = 'Ein <gap>Männlein</gap> steht <gap id="Walter">im</gap> <b>Walde</b> ganz <gap 2>still</gap> und [gap]stumm[/gap]<hr />';
        $expected = 'Ein [gap]Männlein[/gap] steht [gap]im[/gap] <b>Walde</b> ganz [gap]still[/gap] und [gap]stumm[/gap]<hr />';

        $actual = $instance->cleanQuestiontext($in_text);

        $this->assertEquals($expected, $actual);
    }

    public function test_isComplete_shouldReturnFalseIfIncomplete()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = false;

        $actual = $instance->isComplete();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetStartTag_shouldReturnValueUnchanged()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = '<gappo_the_great>';
        
        $instance->setStartTag($expected);
        $actual = $instance->getStartTag();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetStartTag_defaultShoulBeApplied()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $alternate_tag = '<gappo_the_great>';
        $expected = '[gap]';

        $instance->setStartTag($alternate_tag);
        $intermediate = $instance->getStartTag();
        $this->assertEquals($alternate_tag, $intermediate);
        
        $instance->setStartTag();
        $actual = $instance->getStartTag();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetEndTag_shouldReturnValueUnchanged()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = '</gappo_the_great>';

        $instance->setEndTag($expected);
        $actual = $instance->getEndTag();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetEndTag_defaultShoulBeApplied()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $alternate_tag = '</gappo_the_great>';
        $expected = '[/gap]';

        $instance->setEndTag($alternate_tag);
        $intermediate = $instance->getEndTag();
        $this->assertEquals($alternate_tag, $intermediate);

        $instance->setEndTag();
        $actual = $instance->getEndTag();

        $this->assertEquals($expected, $actual);
    }

    public function test_getQuestionType_shouldReturnQuestionType()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = 'assClozeTest';

        $actual = $instance->getQuestionType();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetIdenticalScoring_shouldReturnValueUnchanged()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = 1;

        $instance->setIdenticalScoring(true);
        $actual = $instance->getIdenticalScoring();

        $this->assertEquals($expected, $actual);
    }

    public function test_getAdditionalTableName_shouldReturnAdditionalTableName()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = 'qpl_qst_cloze';

        $actual = $instance->getAdditionalTableName();

        $this->assertEquals($expected, $actual);
    }

    public function test_getAnswerTableName_shouldReturnAnswerTableName()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = array("qpl_a_cloze",'qpl_a_cloze_combi_res');

        $actual = $instance->getAnswerTableName();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetFixedTextLength_shouldReturnValueUnchanged()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = 123;

        $instance->setFixedTextLength($expected);
        $actual = $instance->getFixedTextLength();

        $this->assertEquals($expected, $actual);
    }
}
