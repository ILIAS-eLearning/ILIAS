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
class ilassMarkSchemaTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
	{
		include_once "./Services/PHPUnit/classes/class.ilUnitUtil.php";
		ilUnitUtil::performInitialisation();
                
                // Arrange
		include_once './Modules/Test/classes/class.assMarkSchema.php';
                $this->ass_mark_schema = new ASS_MarkSchema();
	}
        
        /**
         * Test constructor 
         */
        public function testConstructor()
        {
            // Arrange
            $expected = is_array(array());
            
            // Act
            $actual = is_array($this->ass_mark_schema->mark_steps);
            
            // Assert
            $this->assertEquals(
                $actual,
                $expected, 
                "Constructor failed, mark_steps not an array."
            );
            
        }

        /**
         * Test for createSimpleSchema using defaults. 
         */
        public function testCreateSimpleSchemaDefaults()
        {
            // Arrange
            
            
            $txt_failed_short = "failed";
            $txt_failed_official = "failed"; 
            $percentage_failed = 0;
            $failed_passed = 0;
            $txt_passed_short = "passed";
            $txt_passed_official = "passed";
            $percentage_passed = 50;
            $passed_passed = 1;
            
            // Act
            $this->ass_mark_schema->createSimpleSchema();
            $marks = $this->ass_mark_schema->mark_steps;
            
            $failed = $marks[0];
            $passed = $marks[1];
            
            // Assert
            $this->assertEquals(
                $failed->getShortName(), 
                $txt_failed_short, 
                'Failed on $txt_failed_short'
            );
            $this->assertEquals(
                $failed->getOfficialName(), 
                $txt_failed_official, 
                'Failed on $txt_failed_official'
            );
            $this->assertEquals(
                $failed->getMinimumLevel(), 
                $percentage_failed, 
                'Failed on $percentage_failed'
            );
            $this->assertEquals(
                $failed->getPassed(), 
                $failed_passed, 
                'Failed on $failed_passed'
            );

            $this->assertEquals(
                $passed->getShortName(), 
                $txt_passed_short, 
                'Failed on $txt_passed_short'
            );
            $this->assertEquals(
                $passed->getOfficialName(), 
                $txt_passed_official, 
                'Failed on $txt_passed_official'
            );
            $this->assertEquals(
                $passed->getMinimumLevel(), 
                $percentage_passed, 
                'Failed on $percetage_passed'
            );
            $this->assertEquals(
                $passed->getPassed(), 
                $passed_passed, 
                'Failed on $passed_passed'
            );

        }
 
        /**
         * Test for createSimpleSchema using custom values. 
         */
        public function testCreateSimpleSchemaCustom()
        {
            // Arrange
            $txt_failed_short = "failed";
            $txt_failed_official = "failed"; 
            $percentage_failed = 0;
            $failed_passed = 0;
            $txt_passed_short = "passed";
            $txt_passed_official = "passed";
            $percentage_passed = 50;
            $passed_passed = 1;
            
            // Act
            $this->ass_mark_schema->createSimpleSchema(
                $txt_failed_short,
                $txt_failed_official, 
                $percentage_failed,
                $failed_passed,
                $txt_passed_short,
                $txt_passed_official,
                $percentage_passed,
                $passed_passed            
            );

            $marks = $this->ass_mark_schema->mark_steps;
            
            $failed = $marks[0];
            $passed = $marks[1];
            
            // Assert
            $this->assertEquals(
                $failed->getShortName(), 
                $txt_failed_short, 
                'Failed on $txt_failed_short'
            );
            $this->assertEquals(
                $failed->getOfficialName(), 
                $txt_failed_official, 
                'Failed on $txt_failed_official'
            );
            $this->assertEquals(
                $failed->getMinimumLevel(), 
                $percentage_failed, 
                'Failed on $percentage_failed'
            );
            $this->assertEquals(
                $failed->getPassed(), 
                $failed_passed, 
                'Failed on $failed_passed'
            );

            $this->assertEquals(
                $passed->getShortName(), 
                $txt_passed_short, 
                'Failed on $txt_passed_short'
            );
            $this->assertEquals(
                $passed->getOfficialName(), 
                $txt_passed_official, 
                'Failed on $txt_passed_official'
            );
            $this->assertEquals(
                $passed->getMinimumLevel(), 
                $percentage_passed, 
                'Failed on $percetage_passed'
            );
            $this->assertEquals(
                $passed->getPassed(), 
                $passed_passed, 
                'Failed on $passed_passed'
            );
        }

        /**
         * Test for flush() 
         */
        public function testFlush()
        {
            // Arrange
            $expected = is_array(array());
            $this->ass_mark_schema->mark_steps = "a string";
            $this->assertEquals($this->ass_mark_schema->mark_steps, "a string");
            $this->ass_mark_schema->flush();
            
            // Act
            $actual = is_array($this->ass_mark_schema->mark_steps);
            
            // Assert
            $this->assertEquals(
                $actual,
                $expected, 
                "Method failed, mark_steps not an array."
            );
            
        }
}