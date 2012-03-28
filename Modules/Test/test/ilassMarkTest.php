<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* Unit tests for single choice questions
* 
* @author Maximilian Becker <mbecker@databay.de>
* @version $Id: ilassMultipleChoiceTest.php 19383 2009-03-15 11:27:25Z hschottm $
* 
*
* @ingroup ServicesTree
*/
class ilassMarkTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
	{
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
                
                // Arrange
		include_once './Modules/Test/classes/class.assMark.php';
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
}
?>
