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
	}
	
	/**
	 * Get/Set test on member short name using accessors. 
	 * @param
	 * @return
	 */
	public function testGetSetShortName()
	{
                // Arrange
		include_once './Modules/Test/classes/class.assMark.php';
                $ass_mark = new ASS_Mark();
                
                // Act
                $expected = "Esther";
                $ass_mark->setShortName($expected);
                $actual = $ass_mark->getShortName();

                // Assert
		$this->assertEquals(
                    $actual,
                    $expected, 
                    "Get/Set on ASS_Mark failed, in/out not matching."
                );
	}	
}
?>
