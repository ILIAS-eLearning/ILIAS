<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* Unit tests for single choice questions
* 
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* 
*
* @ingroup ServicesTree
*/
class assMultipleChoiceTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
	{
		if (defined('ILIAS_PHPUNIT_CONTEXT'))
		{
			include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
			ilUnitUtil::performInitialisation();
		}
		else
		{
			chdir( dirname( __FILE__ ) );
			chdir('../../../');
		}
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
		include_once './Modules/TestQuestionPool/classes/class.assMultipleChoice.php';
		
		$mc = new assMultipleChoice('unit test multiple choice question', 'unit test multiple choice question comment', 'Helmut Schottmüller', -1, '<p><strong>unit tests</strong> are...</p>');
		$mc->addAnswer(
			'important',
			0.5,
			-0.5,
			1
		);
		$mc->addAnswer(
			'useless',
			-0.5,
			0.5,
			2
		);
		$mc->addAnswer(
			'stupid',
			-0.5,
			0.5,
			3
		);
		$mc->addAnswer(
			'cool',
			0.5,
			-0.5,
			4
		);
		$mc->setObjId($obj_id);
		$mc->saveToDb();
		return $mc->getId();
	}
	
	/**
	 * Question creation test 
	 * @param
	 * @return
	 */
	public function t_e_stCreation()
	{
		global $ilDB;
		
		include_once './Modules/TestQuestionPool/classes/class.assMultipleChoice.php';
		$insert_id = self::createSampleQuestion(null);
		$this->assertGreaterThan(0, $insert_id);
		if ($insert_id > 0)
		{
			$mc = new assMultipleChoice();
			$mc->loadFromDb($insert_id);
			$this->assertEquals($mc->getPoints(),2);
			$this->assertEquals($mc->getTitle(),"unit test multiple choice question");
			$this->assertEquals($mc->getComment(),"unit test multiple choice question comment");
			$this->assertEquals($mc->getAuthor(),"Helmut Schottmüller");
			$this->assertEquals($mc->getQuestion(),"<p><strong>unit tests</strong> are...</p>");
			$this->assertEquals(count($mc->getAnswers()), 4);
			$result = $mc->delete($insert_id);
			$this->assertEquals($result,true);
		}
	}	
}