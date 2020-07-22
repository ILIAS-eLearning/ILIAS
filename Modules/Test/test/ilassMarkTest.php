<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Unit tests for ASS_Mark
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup ModulesTest
 */
class ilassMarkTest extends PHPUnit_Framework_TestCase
{
    /** @var $backupGlobals bool  */
    protected $backupGlobals = false;

    /** @var  $ass_mark ASS_Mark */
    protected $ass_mark;

    protected function setUp()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            require_once './Services/PHPUnit/classes/class.ilUnitUtil.php';
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');
        }

        // Arrange
        require_once './Modules/Test/classes/class.assMark.php';
        $this->ass_mark = new ASS_Mark();
    }

    /**
     * Basic Get/Set test on member short name using accessor methods.
     */
    public function testGetSetShortName()
    {
        // Arrange
        $expected = "Esther";
        $this->ass_mark->setShortName($expected);

        // Act
        $actual = $this->ass_mark->getShortName();

        // Assert
        $this->assertEquals(
            $actual,
            $expected,
            "Get/Set on shortName failed, in/out not matching."
        );
    }

    /**
     * Basic Get/Set test on member passed using accessor methods.
     */
    public function testGetSetPassed()
    {
        // Arrange
        $expected = 1;
        $this->ass_mark->setPassed($expected);

        // Act
        $actual = $this->ass_mark->getPassed();

        // Assert
        $this->assertEquals(
            $actual,
            $expected,
            "Get/Set on passed failed, in/out not matching."
        );
    }

    /**
     * Basic Get/Set test on member officialName using accessor methods.
     */
    public function testGetSetOfficialName()
    {
        // Arrange
        $expected = "Esther The Tester";
        $this->ass_mark->setOfficialName($expected);

        // Act
        $actual = $this->ass_mark->getOfficialName();

        // Assert
        $this->assertEquals(
            $actual,
            $expected,
            "Get/Set on officialName failed, in/out not matching."
        );
    }

    /**
     * Basic Get/Set test on member minimumLevel using accessor methods.
     */
    public function testGetSetMinimumLevel()
    {
        // Arrange
        $expected = 50;
        $this->ass_mark->setMinimumLevel($expected);

        // Act
        $actual = $this->ass_mark->getMinimumLevel();

        // Assert
        $this->assertEquals(
            $actual,
            $expected,
            "Get/Set on minimumLevel failed, in/out not matching."
        );
    }

    /**
     * Set test on member minimumLevel using accessor method with a high
     * level.
     *
     * Tested method should accept double according to docblock
     * at getMinimumLevel(). Confusingly, setMinimumLevel states that it
     * accepts strings as param, which can be considered an oversight of
     * the author.
     *
     * @todo Enhance documentation of class.assMark.php::setMinimumLevel();
     * @todo Enhance documentation of class.assMark.php::getMinimumLevel();
     */
    public function testSetMinimumLevel_High()
    {
        // Arrange
        $expected = 100;
        $this->ass_mark->setMinimumLevel($expected);

        // Act
        $actual = $this->ass_mark->getMinimumLevel();

        // Assert
        $this->assertEquals(
            $actual,
            $expected,
            "Set low on minimumLevel failed, in/out not matching."
        );
    }

    /**
     * Set test on member minimumLevel using accessor methods with a very
     * low level.
     *
     * @see testSetMinimumLevel_High()
     */
    public function testSetMinimumLevel_Low()
    {
        // Arrange
        $expected = 1E-14;
        $this->ass_mark->setMinimumLevel($expected);
            
        // Act
        $actual = $this->ass_mark->getMinimumLevel();
            
        // Assert
        $this->assertEquals(
            $actual,
            $expected,
            "Set low on minimumLevel failed, in/out not matching."
        );
    }

    /**
     * Set test on member minimumLevel using accessor methods with a too
     * low level.
     *
     * @expectedException Exception
     * @see testSetMinimumLevel_High()
     */
    public function testSetMinimumLevel_TooLow()
    {
        // Arrange
        $expected = -1;
        $this->ass_mark->setMinimumLevel($expected);

        // Act
        $actual = $this->ass_mark->getMinimumLevel();
    }
}
