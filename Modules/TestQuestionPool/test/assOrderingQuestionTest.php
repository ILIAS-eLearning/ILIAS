<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assOrderingQuestionTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp() : void
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
        require_once './Modules/TestQuestionPool/classes/class.assOrderingQuestion.php';

        // Act
        $instance = new assOrderingQuestion();

        $this->assertInstanceOf('assOrderingQuestion', $instance);
    }

    public function testOrderingElementListDefaults() : ilAssOrderingElementList
    {
        $question_id = 7;
        $list = new ilAssOrderingElementList($question_id);
        $this->assertInstanceOf('ilAssOrderingElementList', $list);
        $this->assertEquals($question_id, $list->getQuestionId());
        $this->assertEquals([], $list->getElements());
        return $list;
    }

    /**
     * @depends testOrderingElementListDefaults
     */
    public function testOrderingElementListMutation(ilAssOrderingElementList $list)
    {
        $original = $list;
        $this->assertNotEquals($original, $list->withElements([]));
    }

    public function testOrderingElementDefaults() : ilAssOrderingElement
    {
        $element_id = 12;
        $element = new ilAssOrderingElement($element_id);
        $this->assertInstanceOf('ilAssOrderingElement', $element);
        $this->assertEquals($element_id, $element->getId());
        return $element;
    }

    /**
     * @depends testOrderingElementDefaults
     */
    public function testOrderingElementMutation(ilAssOrderingElement $element)
    {
        $original = $element;
        $val = 21;

        $element = $original->withRandomIdentifier($val);
        $this->assertNotEquals($original, $element);
        $this->assertEquals($val, $element->getRandomIdentifier());

        $element = $original->withSolutionIdentifier($val);
        $this->assertNotEquals($original, $element);
        $this->assertEquals($val, $element->getSolutionIdentifier());
        
        $element = $original->withPosition($val);
        $this->assertNotEquals($original, $element);
        $this->assertEquals($val, $element->getPosition());
        
        $element = $original->withIndentation($val);
        $this->assertNotEquals($original, $element);
        $this->assertEquals($val, $element->getIndentation());

        $val = 'some string';
        $element = $original->withContent($val);
        $this->assertNotEquals($original, $element);
        $this->assertEquals($val, $element->getContent());
    }
}
