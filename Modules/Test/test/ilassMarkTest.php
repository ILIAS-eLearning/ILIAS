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

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ASS_Mark
 * @author  Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 * @ingroup ModulesTest
 */
class ilassMarkTest extends TestCase
{
    /** @var $backupGlobals bool */
    protected $backupGlobals = false;

    /** @var  $ass_mark ASS_Mark */
    protected ASS_Mark $ass_mark;

    protected function setUp(): void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');


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
     * Tested method should accept double according to docblock
     * at getMinimumLevel(). Confusingly, setMinimumLevel states that it
     * accepts strings as param, which can be considered an oversight of
     * the author.
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
     * @see testSetMinimumLevel_High()
     */
    public function testSetMinimumLevel_TooLow()
    {
        $this->expectException(Exception::class);

        // Arrange
        $expected = -1;
        $this->ass_mark->setMinimumLevel($expected);

        // Act
        $actual = $this->ass_mark->getMinimumLevel();
    }
}
