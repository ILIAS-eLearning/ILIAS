<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/Database/interfaces/interface.ilDBInterface.php';
/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerOrderingTest extends PHPUnit_Framework_TestCase
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
        }
    }

    public function test_instantiateObject_shouldReturnInstance()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElement.php';

        // Act
        $instance = new ilAssOrderingElement();

        $this->assertInstanceOf('ilAssOrderingElement', $instance);
    }

    public function test_setGetRandomId()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElement.php';
        $instance = new ilAssOrderingElement();
        $expected = 13579;

        // Act
        $instance->setRandomIdentifier($expected);
        $actual = $instance->getRandomIdentifier();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetAnswerId()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElement.php';
        $instance = new ilAssOrderingElement();
        $expected = 13579;

        // Act
        $instance->setId($expected);
        $actual = $instance->getId();

        // Assert
        $this->assertEquals($expected, $actual);
    }


    public function test_setGetOrdeingDepth()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElement.php';
        $instance = new ilAssOrderingElement();
        $expected = 13579;

        // Act
        $instance->setIndentation($expected);
        $actual = $instance->getIndentation();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}
