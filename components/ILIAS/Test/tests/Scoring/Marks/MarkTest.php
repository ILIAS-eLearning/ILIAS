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

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\Test\Scoring\Marks\Mark;

/**
 * Unit tests for Mark
 * @author  Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 * @ingroup components\ILIASTest
 */
class MarkTest extends TestCase
{
    protected $backupGlobals = false;

    protected Mark $ass_mark;

    protected function setUp(): void
    {
        chdir(dirname(__FILE__));
        chdir('../../../../');

        $this->ass_mark = new Mark();
    }

    /**
     * Basic Get/Set test on member short name using accessor methods.
     */
    public function testGetWithShortName()
    {
        // Arrange
        $expected = "Esther";
        $ass_mark = $this->ass_mark->withShortName($expected);

        // Act
        $actual = $ass_mark->getShortName();

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
    public function testGetWithPassed()
    {
        // Arrange
        $expected = true;
        $ass_mark = $this->ass_mark->withPassed($expected);

        // Act
        $actual = $ass_mark->getPassed();

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
    public function testGetWithOfficialName()
    {
        // Arrange
        $expected = "Esther The Tester";
        $ass_mark = $this->ass_mark->withOfficialName($expected);

        // Act
        $actual = $ass_mark->getOfficialName();

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
    public function testGetWithMinimumLevel()
    {
        // Arrange
        $expected = 50;
        $ass_mark = $this->ass_mark->withMinimumLevel($expected);

        // Act
        $actual = $ass_mark->getMinimumLevel();

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
    public function testWithMinimumLevel_High()
    {
        // Arrange
        $expected = 100;
        $ass_mark = $this->ass_mark->withMinimumLevel($expected);

        // Act
        $actual = $ass_mark->getMinimumLevel();

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
    public function testWithMinimumLevel_Low()
    {
        // Arrange
        $expected = 1E-14;
        $ass_mark = $this->ass_mark->withMinimumLevel($expected);

        // Act
        $actual = $ass_mark->getMinimumLevel();

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
    public function testWithMinimumLevel_TooLow()
    {
        $this->expectException(Exception::class);

        // Arrange
        $expected = -1;
        $ass_mark = $this->ass_mark->withMinimumLevel($expected);

        // Act
        $actual = $ass_mark->getMinimumLevel();
    }
}
