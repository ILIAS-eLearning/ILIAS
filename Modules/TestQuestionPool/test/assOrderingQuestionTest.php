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
class assOrderingQuestionTest extends assBaseTestCase
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
        require_once './Modules/TestQuestionPool/classes/class.assOrderingQuestion.php';

        // Act
        $instance = new assOrderingQuestion();

        $this->assertInstanceOf('assOrderingQuestion', $instance);
    }

    public function testOrderingElementListDefaults(): ilAssOrderingElementList
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

    public function testOrderingElementDefaults(): ilAssOrderingElement
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
