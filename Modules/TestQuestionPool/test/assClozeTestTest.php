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
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assClozeTestTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        parent::setUp();

        $ilCtrl_mock = $this->getMockBuilder(ilCtrl::class)
                            ->disableOriginalConstructor()
                            ->getMock();
        $ilCtrl_mock->method('saveParameter');
        $ilCtrl_mock->method('saveParameterByClass');
        $this->setGlobalVariable('ilCtrl', $ilCtrl_mock);

        $lng_mock = $this->getMockBuilder(ilLanguage::class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['txt'])
                         ->getMock();
        $lng_mock->method('txt')->will($this->returnValue('Test'));
        $this->setGlobalVariable('lng', $lng_mock);

        $this->setGlobalVariable('ilias', $this->getIliasMock());
        $this->setGlobalVariable('tpl', $this->getGlobalTemplateMock());
        $this->setGlobalVariable('ilDB', $this->getDatabaseMock());
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';

        // Act
        $instance = new assClozeTest();

        $this->assertInstanceOf('assClozeTest', $instance);
    }

    public function test_cleanQuestionText_shouldReturnCleanedText(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $in_text = 'Ein <gap>Männlein</gap> steht <gap id="Walter">im</gap> <b>Walde</b> ganz <gap 2>still</gap> und [gap]stumm[/gap]<hr />';
        $expected = 'Ein [gap]Männlein[/gap] steht [gap]im[/gap] <b>Walde</b> ganz [gap]still[/gap] und [gap]stumm[/gap]<hr />';

        $actual = $instance->cleanQuestiontext($in_text);

        $this->assertEquals($expected, $actual);
    }

    public function test_isComplete_shouldReturnFalseIfIncomplete(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = false;

        $actual = $instance->isComplete();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetStartTag_shouldReturnValueUnchanged(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = '<gappo_the_great>';

        $instance->setStartTag($expected);
        $actual = $instance->getStartTag();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetStartTag_defaultShoulBeApplied(): void
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

    public function test_setGetEndTag_shouldReturnValueUnchanged(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = '</gappo_the_great>';

        $instance->setEndTag($expected);
        $actual = $instance->getEndTag();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetEndTag_defaultShoulBeApplied(): void
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

    public function test_getQuestionType_shouldReturnQuestionType(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = 'assClozeTest';

        $actual = $instance->getQuestionType();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetIdenticalScoring_shouldReturnValueUnchanged(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = 1;

        $instance->setIdenticalScoring(true);
        $actual = $instance->getIdenticalScoring();

        $this->assertEquals($expected, $actual);
    }

    public function test_getAdditionalTableName_shouldReturnAdditionalTableName(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = 'qpl_qst_cloze';

        $actual = $instance->getAdditionalTableName();

        $this->assertEquals($expected, $actual);
    }

    public function test_getAnswerTableName_shouldReturnAnswerTableName(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeTest.php';
        $instance = new assClozeTest();
        $expected = array("qpl_a_cloze",'qpl_a_cloze_combi_res');

        $actual = $instance->getAnswerTableName();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetFixedTextLength_shouldReturnValueUnchanged(): void
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
