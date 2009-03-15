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
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* 
*
* @ingroup ServicesTree
*/
class ilassSingleChoiceTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
	{
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
	}
	
	/**
	* Create a sample question and save it to the database
	*
	* @param integer $obj_id Object ID of the containing question pool object (optional)
	* @return integer ID of the newly created question
	*/
	public static function createSampleQuestion($obj_id = null)
	{
		$obj_id = ($obj_id) ? $obj_id : 99999999;
		include_once './Modules/TestQuestionPool/classes/class.assSingleChoice.php';
		$sc = new assSingleChoice('unit test single choice question', 'unit test single choice question comment', 'Helmut Schottmüller', -1, '<p>is a <strong>unit test</strong> required?</p>');
		$sc->addAnswer(
			'Yes',
			1,
			0,
			1
		);
		$sc->addAnswer(
			'No',
			-1,
			0,
			2
		);
		$sc->setObjId($obj_id);
		$sc->saveToDb();
		return $sc->getId();
	}
	
	/**
	 * Question creation test 
	 * @param
	 * @return
	 */
	public function testCreation()
	{
		global $ilDB;
		
		include_once './Modules/TestQuestionPool/classes/class.assSingleChoice.php';
		$insert_id = ilassSingleChoiceTest::createSampleQuestion();
		$this->assertGreaterThan(0, $insert_id);
		if ($insert_id > 0)
		{
			$sc = new assSingleChoice();
			$sc->loadFromDb($insert_id);
			$this->assertEquals($sc->getPoints(),1);
			$this->assertEquals($sc->getTitle(),"unit test single choice question");
			$this->assertEquals($sc->getComment(),"unit test single choice question comment");
			$this->assertEquals($sc->getAuthor(),"Helmut Schottmüller");
			$this->assertEquals($sc->getQuestion(),"<p>is a <strong>unit test</strong> required?</p>");
			$this->assertEquals(count($sc->getAnswers()), 2);
			$result = $sc->delete($insert_id);
			$this->assertEquals($result,true);
		}
	}
}
?>
