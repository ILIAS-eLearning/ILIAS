<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* Class ilObjTest
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @defgroup ModulesTest Modules/Test
* @extends ilObject
*/

include_once "./classes/class.ilObject.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

class ilObjTest extends ilObject
{
	/**
	* Kiosk mode
	*
	* Tells wheather the test runs in a kiosk mode or not
	*
	* @var integer
	*/
	protected $_kiosk;
	
/**
* The database id of the additional test data dataset
*
* @var integer
*/
  var $test_id;

/**
* Defines if the test will be placed on users personal desktops
*
* @var integer
*/
	var $invitation = INVITATION_OFF;


/**
* A text representation of the authors name. The name of the author must
* not necessary be the name of the owner.
*
* @var string
*/
  var $author;

/**
* A reference to an IMS compatible matadata set
*
* @var object
*/
  var $metadata;

/**
* An array which contains all the test questions
*
* @var array
*/
  var $questions;

/**
* An introduction text to give users more information
* on the test.
*
* @var string
*/
  var $introduction;

/**
* Defines the mark schema
*
* @var object
*/
  var $mark_schema;

/**
* Defines the sequence settings for the test user. There are two values:
* TEST_FIXED_SEQUENCE (=0) and TEST_POSTPONE (=1). The default value is
* TEST_FIXED_SEQUENCE.
*
* @var integer
*/
  var $sequence_settings;

/**
* Defines the score reporting for the test. There are two values:
* REPORT_AFTER_TEST (=1), REPORT_ALWAYS (=2) AND REPORT_AFTER_DATE (=3). The default
* value is REPORT_AFTER_TEST. If the score reporting is set to
* REPORT_AFTER_TEST, it is also possible to use the $reporting_date
* attribute to set a time/date for the earliest reporting time.
*
* @var integer
*/
  var $score_reporting;

/**
* Defines the question verification type for the test. When set to 1
* a instant verification button will be offered during the test to verify
* the question solution
*
* @var integer
*/
	var $instant_verification;

/**
* Defines wheather or not the reached points are shown as answer feedback
*
* @var integer
*/
	var $answer_feedback_points;

/**
* A time/date value to set the earliest reporting time for the test score.
* If you set this attribute, the sequence settings will be set to REPORT_AFTER_TEST
* automatically. If $reporting_date is not set, the user will get a direct feedback.
* The reporting date is given in database TIMESTAMP notation (yyyymmddhhmmss).
*
* @var string
*/
  var $reporting_date;

/**
* Contains the evaluation data settings the tutor defines for the user
*
* @var object
*/
  var $evaluation_data;

/**
* Number of tries the user is allowed to do. If set to 0, the user has
* infinite tries.
*
* @var integer
*/
  var $nr_of_tries;

/**
* Tells ILIAS to use the previous answers of a learner in a later test pass
* The default is 1 which shows the previous answers in the next pass.
*
* @var integer
*/
	var $use_previous_answers;

/**
* Tells ILIAS how to deal with the test titles. The test title will be shown with
* the full title and the points when title_output is 0. When title_output is 1,
* the available points will be hidden and when title_output is 2, the full title
* will be hidden.
*
* @var integer
*/
  var $title_output;

/**
* The maximum processing time as hh:mm:ss string the user is allowed to do.
*
* @var integer
*/
  var $processing_time;

/**
* Contains 0 if the processing time is disabled, 1 if the processing time is enabled
*
* @var integer
*/
	var $enable_processing_time;

/**
* Contains 0 if the processing time should not be reset, 1 if the processing time should be reset
*
* @var integer
*/
	var $reset_processing_time;

/**
* The starting time in database timestamp format which defines the earliest starting time for the test
*
* @var string
*/
  var $starting_time;

/**
* The ending time in database timestamp format which defines the latest ending time for the test
*
* @var string
*/
  var $ending_time;

/**
* Indicates if ECTS grades will be used
*
* @var integer
*/
  var $ects_output;

/**
* Contains the percentage of maximum points a failed user needs to get the FX ECTS grade
*
* @var float
*/
  var $ects_fx;

/**
* The percentiles of the ECTS grades for this test
*
* @var array
*/
  var $ects_grades;

/**
* Indicates if the questions of the test are generated by a random generator.
* If $random_test is 0, the questions are generated a the conventional way,
* if $random_test is 1 a random generator is used.
*
* @var integer
*/
	var $random_test;

/**
* Determines the number of questions which should be taken for a random test
*
* @var integer
*/
	var $random_question_count;

/**
* Indicates if the points for answers are counted for partial solutions
* or only for correct solutions
*
* @var integer
*/
	var $count_system;

/**
* Indicates if the points unchecked multiple choice questions are given or not
*
* @var integer
*/
	var $mc_scoring;

/**
* Defines which pass should be used for scoring
*
* @var integer
*/
	var $pass_scoring;

/**
* Indicates if the questions in a test are shuffled before
* a user accesses the test
*
* @var boolean
*/
	var $shuffle_questions;

/**
* Contains the presentation settings for the test results
*
* @var integer
*/
	var $results_presentation;

/**
* Determines wheather or not a question summary is shown to the users
*
* @var boolean
*/
	var $show_summary;

/**
* Determines if the score of every question should be cut at 0 points or the score of the complete test
*
* @var boolean
*/
	var $score_cutting;

/**
* Password access to enter the test
*
* @var string
*/
	var $password;

/**
* number of allowed users for the test
*
* @var int
*/
	var $allowedUsers;

/**
* inactivity time gap of the allowed users to let new users into the test
*
* @var int
*/
	var $allowedUsersTimeGap;

/**
* visiblity settings for a test certificate
*
* @var int
*/
	var $certificate_visibility;

/**
* Anonymity of the test users
*
* @var int
*/
	var $anonymity;

/**
* determines wheather a cancel test button is shown or not
*
* @var int
*/
	var $show_cancel;

/**
* determines wheather a marker button is shown or not
*
* @var int
*/
	var $show_marker;

/**
* determines wheather a test may have fixed participants or not
*
* @var int
*/
	var $fixed_participants;

/**
* determines wheather an answer specific feedback is shown or not
*
* @var int
*/
	var $answer_feedback;
	
	/**
	* contains the test session data
	*
	* @var object
	*/
	var $testSession;

	/**
	* contains the test sequence data
	*
	* @var object
	*/
	var $testSequence;
	
	/**
	* Determines whether or not a final statement should be shown on test completion
	*
	* @var integer
	*/
	private $_showfinalstatement;

	/**
	* A final statement for test completion
	*
	* @var string
	*/
	private $_finalstatement;

	/**
	* Show the complete data on the test information page
	*
	* @var boolean
	*/
	private $_showinfo;

	/**
	* Force JavaScript for test questions
	*
	* @var boolean
	*/
	private $_forcejs;
	
	/**
	* Name of a custom style sheet for the test
	*
	* @var string;
	*/
	private $_customStyle;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjTest($a_id = 0,$a_call_by_reference = true)
	{
		global $ilUser;
		$this->type = "tst";
		include_once "./Modules/Test/classes/class.assMarkSchema.php";
		$this->mark_schema = new ASS_MarkSchema();
		$this->test_id = -1;
		$this->author = $ilUser->fullname;
		$this->introduction = "";
		$this->questions = array();
		$this->sequence_settings = TEST_FIXED_SEQUENCE;
		$this->score_reporting = REPORT_AFTER_TEST;
		$this->instant_verification = 0;
		$this->answer_feedback_points = 0;
		$this->reporting_date = "";
		$this->nr_of_tries = 0;
		$this->_kiosk = 0;
		$this->use_previous_answers = 1;
		$this->title_output = 0;
		$this->starting_time = "";
		$this->ending_time = "";
		$this->processing_time = "00:00:00";
		$this->enable_processing_time = "0";
		$this->reset_processing_time = 0;
		$this->ects_output = 0;
		$this->ects_fx = NULL;
		$this->random_test = 0;
		$this->shuffle_questions = FALSE;
		$this->show_summary = 8;
		$this->random_question_count = "";
		$this->count_system = COUNT_PARTIAL_SOLUTIONS;
		$this->mc_scoring = SCORE_ZERO_POINTS_WHEN_UNANSWERED;
		$this->score_cutting = SCORE_CUT_QUESTION;
		$this->pass_scoring = SCORE_LAST_PASS;
		$this->answer_feedback = 0;
		$this->password = "";
		$this->certificate_visibility = 0;
		$this->allowedUsers = "";
		$this->_showfinalstatement = FALSE;
		$this->_finalstatement = "";
		$this->_showinfo = TRUE;
		$this->_forcejs = FALSE;
		$this->_customStyle = "";
		$this->allowedUsersTimeGap = "";
		$this->anonymity = 0;
		$this->show_cancel = 1;
		$this->show_marker = 0;
		$this->fixed_participants = 0;
		$this->setShowPassDetails(TRUE);
		$this->setShowSolutionDetails(TRUE);
		$this->setShowSolutionAnswersOnly(FALSE);
		$this->setShowSolutionSignature(FALSE);
		$this->testSession = FALSE;
		$this->testSequence = FALSE;
		global $lng;
		$lng->loadLanguageModule("assessment");
		$this->mark_schema->createSimpleSchema($lng->txt("failed_short"), $lng->txt("failed_official"), 0, 0, $lng->txt("passed_short"), $lng->txt("passed_official"), 50, 1);
		$this->ects_grades = array(
			"A" => 90,
			"B" => 65,
			"C" => 35,
			"D" => 10,
			"E" => 0
		);
		$this->ilObject($a_id, $a_call_by_reference);
	}

	/**
	* create test object
	*/
	function create($a_upload = false)
	{
		parent::create();

		// meta data will be created by
		// import parser
		if (!$a_upload)
		{
			$this->createMetaData();
		}
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		if (!parent::update())
		{
			return false;
		}

		// put here object specific stuff

		return true;
	}

/**
* Creates a database reference id for the object (saves the object
* to the database and creates a reference id in the database)
*
* @access public
*/
	function createReference() {
		$result = parent::createReference();
		$this->saveToDb();
		return $result;
	}

/**
	* read object data from db into object
	* @param	boolean
	* @access	public
	*/
	function read($a_force_db = false)
	{
		parent::read($a_force_db);
		$this->loadFromDb();
	}


	/**
	* delete object and all related data
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		// delet meta data
		$this->deleteMetaData();

		//put here your module specific stuff
		$this->deleteTest();

		return true;
	}

/**
* Deletes the test and all related objects, files and database entries
*
* @access	public
*/
	function deleteTest()
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT active_id FROM tst_active WHERE test_fi = %s",
			array('integer'),
			array($this->getTestId())
		);
		$active_array = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			array_push($active_array, $row["active_id"]);
		}

		$affectedRows = $ilDB->manipulateF("DELETE FROM tst_active WHERE test_fi = %s",
			array('integer'),
			array($this->getTestId())
		);

		if (count($active_array))
		{
			foreach ($active_array as $active_id)
			{
				$affectedRows = $ilDB->manipulateF("DELETE FROM tst_times WHERE active_fi = %s",
					array('integer'),
					array($active_id)
				);

				$affectedRows = $ilDB->manipulateF("DELETE FROM tst_sequence WHERE active_fi = %s",
					array('integer'),
					array($active_id)
				);
			}
		}

		$affectedRows = $ilDB->manipulateF("DELETE FROM tst_mark WHERE test_fi = %s",
			array('integer'),
			array($this->getTestId())
		);

		$result = $ilDB->queryF("SELECT question_fi FROM tst_test_question WHERE test_fi = %s",
			array('integer'),
			array($this->getTestId())
		);
		while ($row = $ilDB->fetchAssoc($result))
		{
			$this->removeQuestion($row["question_fi"]);
		}

		$affectedRows = $ilDB->manipulateF("DELETE FROM tst_tests WHERE test_id = %s",
			array('integer'),
			array($this->getTestId())
		);

		$affectedRows = $ilDB->manipulateF("DELETE FROM tst_test_random WHERE test_fi = %s",
			array('integer'),
			array($this->getTestId())
		);

		$affectedRows = $ilDB->manipulateF("DELETE FROM tst_test_rnd_qst USING tst_test_rnd_qst, tst_active WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_test_rnd_qst.active_fi",
			array('integer'),
			array($this->getTestId())
		);

		$this->removeAllTestEditings();

		$affectedRows = $ilDB->manipulateF("DELETE FROM tst_test_question WHERE test_fi = %s",
			array('integer'),
			array($this->getTestId())
		);

		// delete export files
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$tst_data_dir = ilUtil::getDataDir()."/tst_data";
		$directory = $tst_data_dir."/tst_".$this->getId();
		if (is_dir($directory))
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::delDir($directory);
		}
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mobs = ilObjMediaObject::_getMobsOfObject("tst:html", $this->getId());
		// remaining usages are not in text anymore -> delete them
		// and media objects (note: delete method of ilObjMediaObject
		// checks whether object is used in another context; if yes,
		// the object is not deleted!)
		foreach($mobs as $mob)
		{
			ilObjMediaObject::_removeUsage($mob, "tst:html", $this->getId());
			$mob_obj =& new ilObjMediaObject($mob);
			$mob_obj->delete();
		}
	}

	/**
	* init default roles settings
	*
	* If your module does not require any default roles, delete this method
	* (For an example how this method is used, look at ilObjForum)
	*
	* @access	public
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		global $rbacadmin;
		return array();
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	*
	* If you are not required to handle any events related to your module, just delete this method.
	* (For an example how this method is used, look at ilObjGroup)
	*
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional parameters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		global $tree;

		switch ($a_event)
		{
			case "link":

				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "cut":

				//echo "Module name ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "copy":

				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":

				//echo "Module name ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "new":

				//echo "Module name ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
				//exit;
				break;
		}

		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		if ($a_node_id==$_GET["ref_id"])
		{
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();
			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}

		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
	}

	/**
	* creates data directory for export files
	* (data_dir/tst_data/tst_<id>/export, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createExportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$tst_data_dir = ilUtil::getDataDir()."/tst_data";
		ilUtil::makeDir($tst_data_dir);
		if(!is_writable($tst_data_dir))
		{
			$this->ilias->raiseError("Test Data Directory (".$tst_data_dir
				.") not writeable.",$this->ilias->error_obj->MESSAGE);
		}

		// create learning module directory (data_dir/lm_data/lm_<id>)
		$tst_dir = $tst_data_dir."/tst_".$this->getId();
		ilUtil::makeDir($tst_dir);
		if(!@is_dir($tst_dir))
		{
			$this->ilias->raiseError("Creation of Test Directory failed.",$this->ilias->error_obj->MESSAGE);
		}
		// create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
		$export_dir = $tst_dir."/export";
		ilUtil::makeDir($export_dir);
		if(!@is_dir($export_dir))
		{
			$this->ilias->raiseError("Creation of Export Directory failed.",$this->ilias->error_obj->MESSAGE);
		}
	}

/**
* Get the location of the export directory for the test
*
* @access	public
*/
	function getExportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$export_dir = ilUtil::getDataDir()."/tst_data"."/tst_".$this->getId()."/export";
		return $export_dir;
	}

/**
* Get a list of the already exported files in the export directory
*
* @return array A list of file names
* @access	public
*/
	function getExportFiles($dir)
	{
		// quit if import dir not available
		if (!@is_dir($dir) or
			!is_writeable($dir))
		{
			return array();
		}

		// open directory
		$dir = dir($dir);

		// initialize array
		$file = array();

		// get files and save the in the array
		while ($entry = $dir->read())
		{
			if ($entry != "." and
				$entry != ".." and
				//substr($entry, -4) == ".zip" and
				ereg("^[0-9]{10}_{2}[0-9]+_{2}(test(__results)?__)*[0-9]+\.[a-z]{1,3}\$", $entry))
			{
				$file[] = $entry;
			}
		}

		// close import directory
		$dir->close();

		// sort files
		sort ($file);
		reset ($file);

		return $file;
	}


	/**
	* creates data directory for import files
	* (data_dir/tst_data/tst_<id>/import, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function _createImportDirectory()
	{
		global $ilias;

		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$tst_data_dir = ilUtil::getDataDir()."/tst_data";
		ilUtil::makeDir($tst_data_dir);

		if(!is_writable($tst_data_dir))
		{
			$ilias->raiseError("Test data directory (".$tst_data_dir
				.") not writeable.",$ilias->error_obj->FATAL);
		}

		// create test directory (data_dir/tst_data/tst_import)
		$tst_dir = $tst_data_dir."/tst_import";
		ilUtil::makeDir($tst_dir);
		if(!@is_dir($tst_dir))
		{
			$ilias->raiseError("Creation of test import directory failed.",$ilias->error_obj->FATAL);
		}
	}

/**
* Get the import directory location of the test
*
* @return string The location of the import directory or false if the directory doesn't exist
* @access	public
*/
	function _getImportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$import_dir = ilUtil::getDataDir()."/tst_data/tst_import";
		if(@is_dir($import_dir))
		{
			return $import_dir;
		}
		else
		{
			return false;
		}
	}

	/**
	* creates data directory for import files
	* (data_dir/tst_data/tst_<id>/import, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createImportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$tst_data_dir = ilUtil::getDataDir()."/tst_data";
		ilUtil::makeDir($tst_data_dir);

		if(!is_writable($tst_data_dir))
		{
			$this->ilias->raiseError("Test Data Directory (".$tst_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}

		// create test directory (data_dir/tst_data/tst_import)
		$tst_dir = $tst_data_dir."/tst_import";
		ilUtil::makeDir($tst_dir);
		if(!@is_dir($tst_dir))
		{
			$ilias->raiseError("Creation of test import directory failed.",$ilias->error_obj->FATAL);
		}
	}

/**
* Get the import directory location of the test
*
* @return string The location of the import directory or false if the directory doesn't exist
* @access	public
*/
	function getImportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$import_dir = ilUtil::getDataDir()."/tst_data/tst_import";
		if(@is_dir($import_dir))
		{
			return $import_dir;
		}
		else
		{
			return false;
		}
	}

/**
* Returns 1 (true), if a test is complete for use
*
* @return integer 1, if the test is complete for use, otherwise 0
* @access public
*/
	function isComplete()
	{
		if ((count($this->mark_schema->mark_steps)) and (count($this->questions)))
		{
			return 1;
		}
		else
		{
			if ($this->isRandomTest())
			{
				$arr = $this->getRandomQuestionpools();
				if (count($arr) && ($this->getRandomQuestionCount() > 0))
				{
					return 1;
				}
				$count = 0;
				foreach ($arr as $array)
				{
					$count += $array["count"];
				}
				if ($count)
				{
					return 1;
				}
			}
			return 0;
		}
		return 0;
	}

/**
* Returns true, if a test is complete for use
*
* @return boolean True, if the test is complete for use, otherwise false
* @access public
*/
	function _isComplete($obj_id)
	{
		$test = new ilObjTest($obj_id, false);
		$test->loadFromDb();
		return $test->isComplete();
	}

/**
* Saves the ECTS status (output of ECTS grades in a test) to the database
*
* @access public
*/
	function saveECTSStatus($ects_output = 0, $fx_support = "", $ects_a = 90, $ects_b = 65, $ects_c = 35, $ects_d = 10, $ects_e = 0)
	{
		global $ilDB;
		if ($this->test_id > 0) 
		{
			$fx_support = preg_replace("/,/", ".", $fx_support);
			if (preg_match("/\d+/", $fx_support))
			{
				$fx_support = $fx_support;
			}
			else
			{
				$fx_support = NULL;
			}
			$affectedRows = $ilDB->manipulateF("UPDATE tst_tests SET ects_output = %s, ects_a = %s, ects_b = %s, ects_c = %s, ects_d = %s, ects_e = %s, ects_fx = %s WHERE test_id = %s",
				array('text','float','float','float','float','float','float','integer'),
				array($ects_output, $ects_a, $ects_b, $ects_c, $ects_d, $ects_e, $fx_support, $this->getTestId())
			);
			$result = $ilDB->query($query);
			$this->ects_output = $ects_output;
			$this->ects_fx = $fx_support;
		}
	}

/**
* Checks if the test is complete and saves the status in the database
*
* @access public
*/
	function saveCompleteStatus()
	{
		global $ilDB;

		$complete = 0;
		if ($this->isComplete())
		{
			$complete = 1;
		}
		if ($this->test_id > 0)
		{
			$affectedRows = $ilDB->manipulateF("UPDATE tst_tests SET complete = %s WHERE test_id = %s",
				array('text','integer'),
				array($complete, $this->test_id)
			);
		}
	}
	
	/**
	* Returns the content of all RTE enabled text areas in the test
	*
	* @access private
	*/
	function getAllRTEContent()
	{
		$result = array();
		array_push($result, $this->getIntroduction());
		array_push($result, $this->getFinalStatement());
		return $result;
	}
	
	/**
	* Cleans up the media objects for all text fields in a test which are using an RTE field
	*
	* @access private
	*/
	function cleanupMediaobjectUsage()
	{
		include_once("./Services/RTE/classes/class.ilRTE.php");
		$completecontent = "";
		foreach ($this->getAllRTEContent() as $content)
		{
			$completecontent .= $content;
		}
		ilRTE::_cleanupMediaObjectUsage($completecontent, $this->getType() . ":html",
			$this->getId());
	}

	/**
	* Saves a ilObjTest object to a database
	*
	* @param object $db A pear DB object
	* @access public
	*/
	function saveToDb($properties_only = FALSE)
	{
		global $ilDB, $ilLog;

		// cleanup RTE images
		$this->cleanupMediaobjectUsage();

		include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
		if ($this->test_id == -1)
		{
			// Create new dataset
			$next_id = $ilDB->nextId('tst_tests');
			$affectedRows = $ilDB->manipulateF("INSERT INTO tst_tests (test_id, obj_fi, author, introduction, " .
				"finalstatement, showinfo, forcejs, customstyle, showfinalstatement, sequence_settings, " .
				"score_reporting, instant_verification, answer_feedback_points, answer_feedback, anonymity, show_cancel, show_marker, " .
				"fixed_participants, nr_of_tries, kiosk, use_previous_answers, title_output, processing_time, enable_processing_time, " .
				"reset_processing_time, reporting_date, starting_time, ending_time, complete, ects_output, ects_a, ects_b, ects_c, ects_d, " .
				"ects_e, ects_fx, random_test, random_question_count, count_system, mc_scoring, score_cutting, pass_scoring, " .
				"shuffle_questions, results_presentation, show_summary, password, allowedUsers, " .
				"allowedUsersTimeGap, certificate_visibility, created, tstamp) " .
				"VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, " .
				"%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				array(
					'integer', 'integer', 'text', 'text', 
					'text', 'integer', 'integer', 'text', 'integer', 'integer',
					'integer', 'text', 'text', 'text', 'text', 'text', 'integer',
					'text', 'integer', 'integer', 'text', 'text', 'time', 'text',
					'integer', 'text', 'text', 'text', 'text', 'text', 'float', 'float', 'float', 'float',
					'float', 'float', 'text', 'integer', 'text', 'text', 'text', 'text',
					'text', 'integer', 'integer', 'text', 'integer',
					'integer', 'text', 'integer', 'integer'
				),
				array(
					$next_id, 
					$this->getId(), 
					$this->getAuthor(), 
					ilRTE::_replaceMediaObjectImageSrc($this->getIntroduction(), 0),
					ilRTE::_replaceMediaObjectImageSrc($this->getFinalStatement(), 0),
					$this->getShowInfo(), 
					$this->getForceJS(),
					$this->getCustomStyle(),
					$this->getShowFinalStatement(),
					$this->getSequenceSettings(),
					$this->getScoreReporting(), 
					$this->getInstantFeedbackSolution(), 
					$this->getAnswerFeedbackPoints(),
					$this->getAnswerFeedback(),
					$this->getAnonymity(), 
					$this->getShowCancel(),
					$this->getShowMarker(),
					$this->getFixedParticipants(),
					$this->getNrOfTries(), 
					$this->getKiosk(),
					$this->getUsePreviousAnswers(),
					$this->getTitleOutput(), 
					$this->getProcessingTime(),
					$this->getEnableProcessingTime(),
					$this->getResetProcessingTime(),
					$this->getReportingDate(),
					$this->getStartingTime(), 
					$this->getEndingTime(),
					$this->isComplete(),
					$this->getECTSOutput(),
					strlen($this->ects_grades["A"]) ? $this->ects_grades["A"] : NULL, 
					strlen($this->ects_grades["B"]) ? $this->ects_grades["B"] : NULL, 
					strlen($this->ects_grades["C"]) ? $this->ects_grades["C"] : NULL, 
					strlen($this->ects_grades["D"]) ? $this->ects_grades["D"] : NULL, 
					strlen($this->ects_grades["E"]) ? $this->ects_grades["E"] : NULL, 
					$this->getECTSFX(),
					$this->isRandomTest(), 
					$this->getRandomQuestionCount(), 
					$this->getCountSystem(),
					$this->getMCScoring(), 
					$this->getScoreCutting(), 
					$this->getPassScoring(),
					$this->getShuffleQuestions(), 
					$this->getResultsPresentation(),
					$this->getListOfQuestionsSettings(), 
					$this->getPassword(),
					$this->getAllowedUsers(),
					$this->getAllowedUsersTimeGap(),
					"0", 
					time(), 
					time()
				)
			);
			$this->test_id = $next_id;

			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_create_new_test", ilObjAssessmentFolder::_getLogLanguage()));
			}
		}
		else
		{
			// Modify existing dataset
			$oldrow = array();
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$result = $ilDB->queryF("SELECT * FROM tst_tests WHERE test_id = %s",
					array('integer'),
					array($this->test_id)
				);
				if ($result->numRows() == 1)
				{
					$oldrow = $ilDB->fetchAssoc($result);
				}
			}

			$affectedRows = $ilDB->manipulateF("UPDATE tst_tests SET author = %s, introduction = %s, " .
				"finalstatement = %s, showinfo = %s, forcejs = %s, customstyle = %s, showfinalstatement = %s, sequence_settings = %s, " .
				"score_reporting = %s, instant_verification = %s, answer_feedback_points = %s, answer_feedback = %s, anonymity = %s, show_cancel = %s, show_marker = %s, " .
				"fixed_participants = %s, nr_of_tries = %s, kiosk = %s, use_previous_answers = %s, title_output = %s, processing_time = %s, enable_processing_time = %s, " . 
				"reset_processing_time = %s, reporting_date = %s, starting_time = %s, ending_time = %s, complete = %s, ects_output = %s, ects_a = %s, ects_b = %s, ects_c = %s, ects_d = %s, " .
				"ects_e = %s, ects_fx = %s, random_test = %s, random_question_count = %s, count_system = %s, mc_scoring = %s, score_cutting = %s, pass_scoring = %s, " . 
				"shuffle_questions = %s, results_presentation = %s, show_summary = %s, password = %s, allowedUsers = %s, " . 
				"allowedUsersTimeGap = %s, tstamp = %s WHERE test_id = %s",
				array(
					'text', 'text', 
					'text', 'integer', 'integer', 'text', 'integer', 'integer',
					'integer', 'text', 'text', 'text', 'text', 'text', 'integer',
					'text', 'integer', 'integer', 'text', 'text', 'time', 'text',
					'integer', 'text', 'text', 'text', 'text', 'text', 'float', 'float', 'float', 'float',
					'float', 'float', 'text', 'integer', 'text', 'text', 'text', 'text',
					'text', 'integer', 'integer', 'text', 'integer',
					'integer', 'integer', 'integer'
				),
				array(
					$this->getAuthor(), 
					ilRTE::_replaceMediaObjectImageSrc($this->getIntroduction(), 0),
					ilRTE::_replaceMediaObjectImageSrc($this->getFinalStatement(), 0),
					$this->getShowInfo(), 
					$this->getForceJS(),
					$this->getCustomStyle(),
					$this->getShowFinalStatement(),
					$this->getSequenceSettings(),
					$this->getScoreReporting(), 
					$this->getInstantFeedbackSolution(), 
					$this->getAnswerFeedbackPoints(),
					$this->getAnswerFeedback(),
					$this->getAnonymity(), 
					$this->getShowCancel(),
					$this->getShowMarker(),
					$this->getFixedParticipants(),
					$this->getNrOfTries(), 
					$this->getKiosk(),
					$this->getUsePreviousAnswers(),
					$this->getTitleOutput(), 
					$this->getProcessingTime(),
					$this->getEnableProcessingTime(),
					$this->getResetProcessingTime(),
					$this->getReportingDate(),
					$this->getStartingTime(), 
					$this->getEndingTime(),
					$this->isComplete(),
					$this->getECTSOutput(),
					strlen($this->ects_grades["A"]) ? $this->ects_grades["A"] : NULL, 
					strlen($this->ects_grades["B"]) ? $this->ects_grades["B"] : NULL, 
					strlen($this->ects_grades["C"]) ? $this->ects_grades["C"] : NULL, 
					strlen($this->ects_grades["D"]) ? $this->ects_grades["D"] : NULL, 
					strlen($this->ects_grades["E"]) ? $this->ects_grades["E"] : NULL, 
					$this->getECTSFX(),
					$this->isRandomTest(), 
					$this->getRandomQuestionCount(), 
					$this->getCountSystem(),
					$this->getMCScoring(), 
					$this->getScoreCutting(), 
					$this->getPassScoring(),
					$this->getShuffleQuestions(), 
					$this->getResultsPresentation(),
					$this->getListOfQuestionsSettings(), 
					$this->getPassword(),
					$this->getAllowedUsers(),
					$this->getAllowedUsersTimeGap(),
					time(), 
					$this->getTestId()
				)
			);

			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$logresult = $ilDB->queryF("SELECT * FROM tst_tests WHERE test_id = %s",
					array('integer'),
					array($this->getTestId())
				);
				$newrow = array();
				if ($logresult->numRows() == 1)
				{
					$newrow = $ilDB->fetchAssoc($logresult);
				}
				$changed_fields = array();
				foreach ($oldrow as $key => $value)
				{
					if (strcmp($oldrow[$key], $newrow[$key]) != 0)
					{
						array_push($changed_fields, "$key: " . $oldrow[$key] . " => " . $newrow[$key]);
					}
				}
				$changes = join($changed_fields, ", ");
				if (count($changed_fields) > 0)
				{
					$this->logAction($this->lng->txtlng("assessment", "log_modified_test", ilObjAssessmentFolder::_getLogLanguage()) . " [".$changes."]");
				}
			}
			if ($this->evalTotalPersons() > 0)
			{
				// reset the finished status of participants if the nr of test passes did change
				if ($this->getNrOfTries() > 0)
				{
					// set all unfinished tests with nr of passes >= allowed passes finished
					$aresult = $ilDB->queryF("SELECT active_id FROM tst_active WHERE test_fi = %s AND tries >= %s AND submitted = %s",
						array('integer', 'integer', 'integer'),
						array($this->getTestId(), $this->getNrOfTries(), 0)
					);
					while ($row = $ilDB->fetchAssoc($aresult))
					{
						$affectedRows = $ilDB->manipulateF("UPDATE tst_active SET submitted = %s, submittimestamp = %s WHERE active_id = %s",
							array('integer', 'timestamp', 'integer'),
							array(1, date('Y-m-d H:i:s'), $row["active_id"])
						);
					}

					// set all finished tests with nr of passes >= allowed passes not finished
					$aresult = $ilDB->queryF("SELECT active_id FROM tst_active WHERE test_fi = %s AND tries < %s AND submitted = %s",
						array('integer', 'integer', 'integer'),
						array($this->getTestId(), $this->getNrOfTries(), 1)
					);
					while ($row = $ilDB->fetchAssoc($aresult))
					{
						$affectedRows = $ilDB->manipulateF("UPDATE tst_active SET submitted = %s, submittimestamp = %s WHERE active_id = %s",
							array('integer', 'timestamp', 'integer'),
							array(0, NULL, $row["active_id"])
						);
					}
				}
				else
				{
					// set all finished tests with nr of passes >= allowed passes not finished
					$aresult = $ilDB->queryF("SELECT active_id FROM tst_active WHERE test_fi = %s AND submitted = %s",
						array('integer', 'integer'),
						array($this->getTestId(), 1)
					);
					while ($row = $ilDB->fetchAssoc($aresult))
					{
						$affectedRows = $ilDB->manipulateF("UPDATE tst_active SET submitted = %s, submittimestamp = %s WHERE active_id = %s",
							array('integer', 'timestamp', 'integer'),
							array(0, NULL, $row["active_id"])
						);
					}
				}
			}
    }
		if (!$properties_only)
		{
			if (PEAR::isError($result)) 
			{
				global $ilias;
				$ilias->raiseError($result->getMessage());
			}
			else
			{
				if (!$this->isRandomTest())
				{
					$this->saveQuestionsToDb();
				}
				$this->mark_schema->saveToDb($this->test_id);
			}
		}
  }

/**
* Saves the test questions to the database
*
* @access public
* @see $questions
*/
	function saveQuestionsToDb()
	{
		global $ilDB;

		$oldquestions = array();
		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$query = sprintf("SELECT question_fi FROM tst_test_question WHERE test_fi = %s ORDER BY sequence",
				$ilDB->quote($this->getTestId())
			);
			$result = $ilDB->query($query);
			if ($result->numRows() > 0)
			{
				while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
				{
					array_push($oldquestions, $row["question_fi"]);
				}
			}
		}

		// delete existing category relations
    $query = sprintf("DELETE FROM tst_test_question WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
		// create new category relations
		foreach ($this->questions as $key => $value) {
			$result = $ilDB->manipulateF("INSERT INTO tst_test_question (test_question_id, test_fi, question_fi, sequence, tstamp) VALUES (NULL, %s, %s, %s, %s)",
				array('integer', 'integer', 'integer', 'integer'),
				array($this->getTestId(), $value, $key, time())
			);
			$result = $ilDB->query($query);
		}
		include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$query = sprintf("SELECT question_fi FROM tst_test_question WHERE test_fi = %s ORDER BY sequence",
				$ilDB->quote($this->getTestId())
			);
			$result = $ilDB->query($query);
			$newquestions = array();
			if ($result->numRows() > 0)
			{
				while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
				{
					array_push($newquestions, $row["question_fi"]);
				}
			}
			foreach ($oldquestions as $index => $question_id)
			{
				if (strcmp($newquestions[$index], $question_id) != 0)
				{
					$pos = array_search($question_id, $newquestions);
					if ($pos === FALSE)
					{
						$this->logAction($this->lng->txtlng("assessment", "log_question_removed", ilObjAssessmentFolder::_getLogLanguage()), $question_id);
					}
					else
					{
						$this->logAction($this->lng->txtlng("assessment", "log_question_position_changed", ilObjAssessmentFolder::_getLogLanguage()) . ": " . ($index+1) . " => " . ($pos+1), $question_id);
					}
				}
			}
			foreach ($newquestions as $index => $question_id)
			{
				if (array_search($question_id, $oldquestions) === FALSE)
				{
					$this->logAction($this->lng->txtlng("assessment", "log_question_added", ilObjAssessmentFolder::_getLogLanguage()) . ": " . ($index+1), $question_id);
				}
			}
		}
	}

/**
* Saves a random question to the database
*
* Saves a random question to the database
*
* @access public
* @see $questions
*/
	function saveRandomQuestion($active_id, $question_id, $pass = NULL, $maxcount)
	{
		global $ilUser;
		global $ilDB;

		if (is_null($pass)) $pass = 0;
		$query = sprintf("SELECT test_random_question_id FROM tst_test_rnd_qst WHERE active_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() < $maxcount)
		{
			$duplicate_id = $this->getRandomQuestionDuplicate($question_id, $active_id);
			if ($duplicate_id === FALSE)
			{
				$duplicate_id = $this->duplicateQuestionForTest($question_id);
			}

			$result = $ilDB->manipulateF("INSERT INTO tst_test_rnd_qst (test_random_question_id, active_fi, question_fi, sequence, pass, tstamp) VALUES (NULL, %s, %s, %s, %s, %s)",
				array('integer','integer','integer','integer','integer'),
				array($active_id, $duplicate_id, $result->numRows()+1, $pass)
			);
		}
	}

/**
* Returns the question id of the duplicate of a question which is already in use in a random test
*
* @param integer $question_id Question ID of the original question
* @param integer $active_id Active ID of the user
* @return mixed The question ID of the duplicate or FALSE if no duplicate was found
* @access public
* @see $questions
*/
	function getRandomQuestionDuplicate($question_id, $active_id)
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT qpl_questions.question_id FROM qpl_questions, tst_test_rnd_qst WHERE qpl_questions.original_id = %s AND tst_test_rnd_qst.question_fi = qpl_questions.question_id AND tst_test_rnd_qst.active_fi = %s",
			array('integer', 'integer'),
			array($question_id, $active_id)
		);
		$num = $result->numRows();
		if ($num > 0)
		{
			$row = $ilDB->fetchAssoc($result);
			return $row["question_id"];
		}
		else
		{
			return FALSE;
		}
	}

/**
* Calculates the number of user results for a specific test pass
*
* Calculates the number of user results for a specific test pass
*
* @access private
*/
	function getNrOfResultsForPass($active_id, $pass)
	{
		global $ilDB;

		$query = sprintf("SELECT test_result_id FROM tst_test_result WHERE active_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		return $result->numRows();
	}

	/**
	* Checkes wheather a random test has already created questions for a given pass or not
	*
	* Checkes wheather a random test has already created questions for a given pass or not
	*
	* @access private
	* @param $active_id Active id of the test
	* @param $pass Pass of the test
	* @return boolean TRUE if the test already contains questions, FALSE otherwise
	*/
	function hasRandomQuestionsForPass($active_id, $pass)
	{
		global $ilDB;
		$query = sprintf("SELECT test_random_question_id FROM tst_test_rnd_qst WHERE active_fi = %s AND pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($pass . "")
			);
		$result = $ilDB->query($query);
		if ($result->numRows() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* Generates new random questions for the active user
	*
	* @access private
	* @see $questions
*/
	function generateRandomQuestions($active_id, $pass = NULL)
	{
		global $ilUser;
		global $ilDB;

		if ($active_id > 0)
		{
			if ($this->hasRandomQuestionsForPass($active_id, $pass) > 0)
			{
				// Something went wrong. Maybe the user pressed the start button twice
				// Questions already exist so there is no need to create new questions
				return;
			}
			if ($pass > 0)
			{
				if ($this->getNrOfResultsForPass($active_id, $pass - 1) == 0)
				{
					// This means that someone maybe reloaded the test submission page
					// If there are no existing results for the previous test, it makes
					// no sense to create a new set of random questions
					return;
				}
			}
		}
		else
		{
			// This may not happen! If it happens, raise a fatal error...
			global $ilias, $ilErr;
			$ilias->raiseError(sprintf($this->lng->txt("error_random_question_generation"), $ilUser->getId(), $this->getTestId()), $ilErr->FATAL);
		}

		$num = $this->getRandomQuestionCount();
		if ($num > 0)
		{
			$qpls =& $this->getRandomQuestionpools();
			$rndquestions = $this->randomSelectQuestions($num, 0, 1, $qpls, $pass);
			$allquestions = array();
			foreach ($rndquestions as $question_id)
			{
				array_push($allquestions, $question_id);
			}
			if ($this->getShuffleQuestions())
			{
				srand ((float)microtime()*1000000);
				shuffle($allquestions);
			}

			$maxcount = 0;
			foreach ($qpls as $data)
			{
				$maxcount += $data["contains"];
			}
			if ($num > $maxcount) $num = $maxcount;
			foreach ($allquestions as $question_id)
			{
				$this->saveRandomQuestion($active_id, $question_id, $pass, $num);
			}
		}
		else
		{
			$qpls =& $this->getRandomQuestionpools();
			$allquestions = array();
			$maxcount = 0;
			foreach ($qpls as $key => $value)
			{
				if ($value["count"] > 0)
				{
					$rndquestions = $this->randomSelectQuestions($value["count"], $value["qpl"], 1, "", $pass);
					foreach ($rndquestions as $question_id)
					{
						array_push($allquestions, $question_id);
					}
				}
				$add = ($value["count"] <= $value["contains"]) ? $value["count"] : $value["contains"];
				$maxcount += $add;
			}
			if ($this->getShuffleQuestions())
			{
				srand ((float)microtime()*1000000);
				shuffle($allquestions);
			}
			foreach ($allquestions as $question_id)
			{
				$this->saveRandomQuestion($active_id, $question_id, $pass, $maxcount);
			}
		}
	}

	/**
	* Saves the total amount of a tests random questions to the database
	*
	* Saves the total amount of a tests random questions to the database
	*
	* @param integer $total_questions The amount of random questions
	* @access public
	*/
	function saveRandomQuestionCount($total_questions = "NULL")
	{
		global $ilDB;

		if (strcmp($total_questions, "NULL") != 0)
		{
			$this->setRandomQuestionCount($total_questions);
			$total_questions = $ilDB->quote($total_questions);
		}
		$query = sprintf("UPDATE tst_tests SET random_question_count = %s WHERE test_id = %s",
			$total_questions,
			$ilDB->quote($this->getTestId() . "")
		);
		$result = $ilDB->query($query);
		include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			if (strcmp($total_questions, "NULL") == 0) $total_questions = '0';
			$this->logAction(sprintf($this->lng->txtlng("assessment", "log_total_amount_of_questions", ilObjAssessmentFolder::_getLogLanguage()), $total_questions));
		}
	}

/**
* Saves the question pools used for a random test
*
* Saves the question pools used for a random test
*
* @param array $qpl_array An array containing the questionpool id's
* @access public
* @see $questions
*/
	function saveRandomQuestionpools($qpl_array)
	{
		global $ilDB;

		include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
		// delete existing random questionpools
    $query = sprintf("DELETE FROM tst_test_random WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$this->logAction($this->lng->txtlng("assessment", "log_random_question_pool_deleted", ilObjAssessmentFolder::_getLogLanguage()));
		}
		// create new random questionpools
		foreach ($qpl_array as $key => $value) {
			if ($value["qpl"] > -1)
			{
				include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
				$count = ilObjQuestionPool::_getQuestionCount($value["qpl"]);
				if ($value["count"] > $count)
				{
					$value["count"] = $count;
				}
				$result = $ilDB->manipulateF("INSERT INTO tst_test_random (test_random_id, test_fi, questionpool_fi, num_of_q, tstamp) VALUES (NULL, %s, %s, %s, %s)",
					array('integer', 'integer', 'integer', 'integer'),
					array($this->getTestId(), $value["qpl"], sprintf("%d", $value["count"]), time())
				);
				if (ilObjAssessmentFolder::_enabledAssessmentLogging())
				{
					$this->logAction(sprintf($this->lng->txtlng("assessment", "log_random_question_pool_added", ilObjAssessmentFolder::_getLogLanguage()), $value["title"] . " (" . $value["qpl"] . ")", $value["count"]));
				}
			}
		}
	}

/**
* Returns an array containing the random questionpools saved to the database
*
* @access public
* @return array All saved random questionpools
* @see $questions
*/
	function &getRandomQuestionpools()
	{
		global $ilDB;

		$qpls = array();
		$counter = 0;
		$result = $ilDB->queryF("SELECT tst_test_random.*, qpl_questionpool.questioncount FROM tst_test_random, qpl_questionpool WHERE tst_test_random.test_fi = %s AND tst_test_random.questionpool_fi = qpl_questionpool.obj_fi ORDER BY test_random_id",
			array("integer"),
			array($this->getTestId())
		);
		if ($result->numRows())
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				$qpls[$counter] = array(
					"index" => $counter,
					"count" => $row["num_of_q"],
					"qpl"   => $row["questionpool_fi"],
					"contains" => $row["questioncount"]
				);
				$counter++;
			}
		}
		return $qpls;
	}

	/**
	* Loads a ilObjTest object from a database
	*
	* Loads a ilObjTest object from a database
	*
	* @param object $db A pear DB object
	* @param integer $test_id A unique key which defines the test in the database
	* @access public
	*/
	function loadFromDb()
	{
		global $ilDB;

		$query = sprintf("SELECT * FROM tst_tests WHERE obj_fi = %s",
		$ilDB->quote($this->getId())
			);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$data = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
			$this->test_id = $data->test_id;
			if (strlen($this->getAuthor()) == 0)
			{
				$this->saveAuthorToMetadata($data->author);
			}
			$this->author = $this->getAuthor();
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->introduction = ilRTE::_replaceMediaObjectImageSrc($data->introduction, 1);
			$this->setFinalStatement(ilRTE::_replaceMediaObjectImageSrc($data->finalstatement, 1));
			$this->setShowInfo($data->showinfo);
			$this->setForceJS($data->forcejs);
			$this->setCustomStyle($data->customstyle);
			$this->setShowFinalStatement($data->showfinalstatement);
			$this->sequence_settings = $data->sequence_settings;
			$this->score_reporting = $data->score_reporting;
			$this->instant_verification = $data->instant_verification;
			$this->answer_feedback_points = $data->answer_feedback_points;
			$this->answer_feedback = $data->answer_feedback;
			$this->anonymity = $data->anonymity;
			$this->show_cancel = $data->show_cancel;
			$this->show_marker = $data->show_marker;
			$this->fixed_participants = $data->fixed_participants;
			$this->nr_of_tries = $data->nr_of_tries;
			$this->setKiosk($data->kiosk);
			$this->setUsePreviousAnswers($data->use_previous_answers);
			$this->setTitleOutput($data->title_output);
			$this->processing_time = $data->processing_time;
			$this->enable_processing_time = $data->enable_processing_time;
			$this->reset_processing_time = $data->reset_processing_time;
			$this->reporting_date = $data->reporting_date;
			$this->setShuffleQuestions($data->shuffle_questions);
			$this->setResultsPresentation($data->results_presentation);
			$this->setListOfQuestionsSettings($data->show_summary);
			$this->starting_time = $data->starting_time;
			$this->ending_time = $data->ending_time;
			$this->ects_output = $data->ects_output;
			$this->ects_grades = array(
				"A" => $data->ects_a,
				"B" => $data->ects_b,
				"C" => $data->ects_c,
				"D" => $data->ects_d,
				"E" => $data->ects_e
			);
			$this->ects_fx = $data->ects_fx;
			$this->random_test = $data->random_test;
			$this->random_question_count = $data->random_question_count;
			$this->mark_schema->flush();
			$this->mark_schema->loadFromDb($this->test_id);
			$this->count_system = $data->count_system;
			$this->mc_scoring = $data->mc_scoring;
			$this->setScoreCutting($data->score_cutting);
			$this->setPassword($data->password);
			$this->setAllowedUsers($data->allowedUsers);
			$this->setAllowedUsersTimeGap($data->allowedUsersTimeGap);
			$this->setPassScoring($data->pass_scoring);
			$this->setCertificateVisibility($data->certificate_visibility);
			$this->loadQuestions();
		}
	}

/**
* Load the test question id's from the database
*
* Load the test question id's from the database
*
* @param integer $user_id The user id of the test user (necessary for random tests)
* @access	public
*/
function loadQuestions($active_id = "", $pass = NULL)
{
	global $ilUser;
	global $ilDB;

	$this->questions = array();
	if (strcmp($active_id, "") == 0)
	{
		$active_id = $this->getActiveIdOfUser($ilUser->getId());
	}
	if ($this->isRandomTest())
	{
		if (is_null($pass))
		{
			$pass = $this->_getPass($active_id);
		}
		$result = $ilDB->queryF("SELECT tst_test_rnd_qst.* FROM tst_test_rnd_qst, qpl_questions WHERE tst_test_rnd_qst.active_fi = %s AND qpl_questions.question_id = tst_test_rnd_qst.question_fi AND tst_test_rnd_qst.pass = %s ORDER BY sequence",
			array('integer', 'integer'),
			array($active_id, $pass)
		);
		// The following is a fix for random tests prior to ILIAS 3.8. If someone started a random test in ILIAS < 3.8, there
		// is only one test pass (pass = 0) in tst_test_rnd_qst while with ILIAS 3.8 there are questions for every test pass.
		// To prevent problems with tests started in an older version and continued in ILIAS 3.8, the first pass should be taken if
		// no questions are present for a newer pass.
		if ($result->numRows() == 0)
		{
			$result = $ilDB->queryF("SELECT tst_test_rnd_qst.* FROM tst_test_rnd_qst, qpl_questions WHERE tst_test_rnd_qst.active_fi = %s AND qpl_questions.question_id = tst_test_rnd_qst.question_fi AND tst_test_rnd_qst.pass = 0 ORDER BY sequence",
				array('integer'),
				array($active_id)
			);
			$result = $ilDB->query($query);
		}
	}
	else
	{
		$result = $ilDB->queryF("SELECT tst_test_question.* FROM tst_test_question, qpl_questions WHERE tst_test_question.test_fi = %s AND qpl_questions.question_id = tst_test_question.question_fi ORDER BY sequence",
			array('integer'),
			array($this->test_id)
		);
		$result = $ilDB->query($query);
	}
	$index = 1;
	while ($data = $ilDB->fetchAssoc($result))
	{
		$this->questions[$index++] = $data["question_fi"];
	}
}

/**
* Sets the introduction
*
* Sets the introduction text of the ilObjTest object
*
* @param string $introduction An introduction string for the test
* @access public
* @see $introduction
*/
  function setIntroduction($introduction = "")
	{
    $this->introduction = $introduction;
  }

	/**
	* Sets the final statement text of the ilObjTest object
	*
	* @param string $a_statement A final statement
	* @access public
	* @see $_finalstatement
	*/
	public function setFinalStatement($a_statement = "")
	{
		$this->_finalstatement = $a_statement;
	}

	/**
	* Set whether the complete information page is shown or the required data only
	*
	* @param integer $a_info 1 for the complete information, 0 otherwise
	* @access public
	* @see $_showinfo
	*/
	public function setShowInfo($a_info = 1)
	{
		$this->_showinfo = ($a_info) ? 1 : 0;
	}

	/**
	* Set whether JavaScript should be forced for tests
	*
	* @param integer $a_js 1 to force JavaScript, 0 otherwise
	* @access public
	* @see $_forcejs
	*/
	public function setForceJS($a_js = 1)
	{
		$this->_forcejs = ($a_js) ? 1 : 0;
	}
	
	/**
	* Set the custom style
	*
	* @param string $a_customStyle The custom style
	* @access public
	* @see $_customStyle
	*/
	public function setCustomStyle($a_customStyle = NULL)
	{
		$this->_customStyle = $a_customStyle;
	}
	
	/**
	* Get the custom style
	*
	* @return mixed The custom style, NULL if empty
	* @access public
	* @see $_customStyle
	*/
	public function getCustomStyle()
	{
		return (strlen($this->_customStyle)) ? $this->_customStyle : NULL;
	}
	
	/**
	* Return the available custom styles
	*
	* @return array An array of strings containing the available custom styles
	* @access public
	* @see $_customStyle
	*/
	public function getCustomStyles()
	{
		$css_path = ilUtil::getStyleSheetLocation("filesystem", "ta.css", "Modules/Test");
		$css_path = str_replace("ta.css", "customstyles", $css_path) . "/";
		$customstyles = array();
		if (is_dir($css_path))
		{
			$results = array();
			include_once "./Services/Utilities/classes/class.ilFileUtils.php";
			ilFileUtils::recursive_dirscan($css_path, $results);
			if (is_array($results["file"]))
			{
				foreach ($results["file"] as $filename)
				{
					if (strpos($filename, ".css"))
					{
						array_push($customstyles, $filename);
					}
				}
			}
		}
		return $customstyles;
	}
	
	/**
	* get full style sheet file name (path inclusive) of current user
	*
	* @param $mode string Output mode of the style sheet ("output" or "filesystem"). !"filesystem" generates the ILIAS
	* version number as attribute to force the reload of the style sheet in a different ILIAS version
	* @access	public
	*/
	public function getTestStyleLocation($mode = "output")
	{
		if (strlen($this->getCustomStyle()))
		{
			$default = ilUtil::getStyleSheetLocation("filesystem", "ta.css", "Modules/Test");
			$custom = str_replace("ta.css", "customstyles/" . $this->getCustomStyle(), $default);
			if (file_exists($custom))
			{
				$custom = ilUtil::getStyleSheetLocation($mode, "ta.css", "Modules/Test");
				$custom = str_replace("ta.css", "customstyles/" . $this->getCustomStyle(), $custom);
				return $custom;
			}
			else
			{
				return ilUtil::getStyleSheetLocation($mode, "ta.css", "Modules/Test");
			}
		}
		else
		{
			return ilUtil::getStyleSheetLocation($mode, "ta.css", "Modules/Test");
		}
	}

	/**
	* Sets whether the final statement should be shown or not
	*
	* @param integer $show 1 if TRUE or 0 if FALSE
	* @access public
	* @see $_finalstatement
	*/
	public function setShowFinalStatement($show = 0)
	{
		$this->_showfinalstatement = ($show) ? 1 : 0;
	}


/**
* Gets the status of the $random_test attribute
*
* @return integer The random test status. 0 = normal, 1 = questions are generated with random generator
* @access public
* @see $random_test
*/
	function isRandomTest()
	{
		return ($this->random_test) ? 1 : 0;
	}

/**
* Gets the number of random questions used for a random test
*
* @return integer The number of random questions
* @access public
* @see $random_question_count
*/
	function getRandomQuestionCount()
	{
		return ($this->random_question_count) ? $this->random_question_count : 0;
	}

/**
* Gets the introduction text of the ilObjTest object
*
* @return mixed The introduction text of the test, NULL if empty
* @see $introduction
*/
	public function getIntroduction()
	{
		return (strlen($this->introduction)) ? $this->introduction : NULL;
	}

	/**
	* Gets the final statement
	*
	* @return mixed The final statement, NULL if empty
	* @see $_finalstatement
	*/
	public function getFinalStatement()
	{
		return (strlen($this->_finalstatement)) ? $this->_finalstatement : NULL;
	}

	/**
	* Gets whether the complete information page is shown or the required data only
	*
	* @return integer 1 for the complete information, 0 otherwise
	* @access public
	* @see $_showinfo
	*/
	public function getShowInfo()
	{
		return ($this->_showinfo) ? 1 : 0;
	}

	/**
	* Gets whether JavaScript should be forced for tests
	*
	* @return integer 1 to force JavaScript, 0 otherwise
	* @access public
	* @see $_forcejs
	*/
	public function getForceJS()
	{
		return ($this->_forcejs) ? 1 : 0;
	}

	/**
	* Returns whether the final statement should be shown or not
	*
	* @return integer 0 if false, 1 if true
	* @access public
	* @see $_showfinalstatement
	*/
	public function getShowFinalStatement()
	{
		return ($this->_showfinalstatement) ? 1 : 0;
	}

/**
* Gets the database id of the additional test data
*
* @return integer The database id of the additional test data
* @access public
* @see $test_id
*/
  function getTestId()
	{
    return $this->test_id;
  }

	/**
	* Indicates if ECTS grades output is presented in this test
	*
	* @return integer 0 if there is no ECTS grades output, 1 otherwise
	* @access public
	* @see $ects_output
	*/
	function getECTSOutput()
	{
		return ($this->ects_output) ? 1 : 0;
	}

	/**
	* Enables/Disables ECTS grades output for this test
	*
	* @param integer $a_ects_output 0 if ECTS grades output should be deactivated, 1 otherwise
	* @access public
	* @see $ects_output
	*/
	function setECTSOutput($a_ects_output)
	{
		$this->ects_output = $a_ects_output ? 1 : 0;
	}

	/**
	* Returns the ECTS FX grade
	*
	* @return mixed The ECTS FX grade, NULL if empty
	* @access public
	* @see $ects_fx
	*/
	function getECTSFX()
	{
		return (strlen($this->ects_fx)) ? $this->ects_fx : NULL;
	}

	/**
	* Sets the ECTS FX grade
	*
	* @param string $a_ects_fx The ECTS FX grade
	* @access public
	* @see $ects_fx
	*/
	function setECTSFX($a_ects_fx)
	{
		$this->ects_fx = $a_ects_fx;
	}

	/**
	* Returns the ECTS grades
	*
	* @return array The ECTS grades
	* @access public
	* @see $ects_grades
	*/
	function &getECTSGrades()
	{
		return $this->ects_grades;
	}

	/**
	* Sets the ECTS grades
	*
	* @param array $a_ects_grades The ECTS grades
	* @access public
	* @see $ects_grades
	*/
	function setECTSGrades($a_ects_grades)
	{
		if (is_array($a_ects_grades))
		{
			$this->ects_grades = $a_ects_grades;
		}
	}

/**
* Sets the sequence settings of the ilObjTest object
*
* @param integer $sequence_settings The sequence settings
* @access public
* @see $sequence_settings
*/
  function setSequenceSettings($sequence_settings = 0)
	{
    $this->sequence_settings = $sequence_settings;
  }

/**
* Sets the score reporting of the ilObjTest object
*
* @param integer $score_reporting The score reporting
* @access public
* @see $score_reporting
*/
  function setScoreReporting($score_reporting = 0)
	{
    $this->score_reporting = $score_reporting;
  }

/**
* Sets the instant feedback for the solution
*
* @param integer $instant_feedback If 1, the solution will be shown after answering a question
* @access public
* @see $instant_verification
*/
  function setInstantFeedbackSolution($instant_feedback = 0)
	{
		switch ($instant_feedback)
		{
			case 1:
				$this->instant_verification = 1;
				break;
			default:
				$this->instant_verification = 0;
				break;
		}
  }

/**
* Sets the answer specific feedback for the test
*
* @param integer $answer_feedback If 1, answer specific feedback will be shown after answering a question
* @access public
* @see $answer_feedback
*/
  function setAnswerFeedback($answer_feedback = 0)
	{
		switch ($answer_feedback)
		{
			case 1:
				$this->answer_feedback = 1;
				break;
			default:
				$this->answer_feedback = 0;
				break;
		}
  }

/**
* Sets the answer specific feedback of reached points for the test
*
* Sets the answer specific feedback of reached points for the test
*
* @param integer $answer_feedback_points If 1, answer specific feedback will show the reached points after answering a question
* @access public
* @see $answer_feedback_points
*/
  function setAnswerFeedbackPoints($answer_feedback_points = 0)
	{
		switch ($answer_feedback_points)
		{
			case 1:
				$this->answer_feedback_points = 1;
				break;
			default:
				$this->answer_feedback_points = 0;
				break;
		}
  }

/**
* Sets the random test indicator
*
* Sets the random test indicator
*
* @param integer $a_random_test The random test indicator (0 = no random test, 1 = random test)
* @access public
* @see $random_test
*/
  function setRandomTest($a_random_test = 0)
	{
    $this->random_test = $a_random_test;
  }

/**
* Sets the random question count
*
* Sets the random question count
*
* @param integer $a_random_question_count The random question count
* @access public
* @see $random_question_count
*/
  function setRandomQuestionCount($a_random_question_count = "")
	{
    $this->random_question_count = $a_random_question_count;
  }

/**
* Sets the reporting date
*
* Sets the reporting date of the ilObjTest object
*
* @param timestamp $reporting_date The date and time the score reporting is available
* @access public
* @see $reporting_date
*/
  function setReportingDate($reporting_date)
  {
		if (!$reporting_date)
		{
			$this->reporting_date = "";
			$this->ects_output = 0;
		}
		else
		{
			$this->reporting_date = $reporting_date;
		}
  }

/**
* Gets the sequence settings of the ilObjTest object
*
* @return integer The sequence settings of the test
* @access public
* @see $sequence_settings
*/
	function getSequenceSettings()
	{
		return ($this->sequence_settings) ? $this->sequence_settings : 0;
	}

/**
* Gets the score reporting of the ilObjTest object
*
* @return integer The score reporting of the test
* @access public
* @see $score_reporting
*/
	function getScoreReporting()
	{
		return ($this->score_reporting) ? $this->score_reporting : 0;
	}

/**
* Returns 1 if the correct solution will be shown after answering a question
*
* @return integer The status of the solution instant feedback
* @access public
* @see $instant_verification
*/
  function getInstantFeedbackSolution()
	{
    return ($this->instant_verification) ? $this->instant_verification : 0;
  }

/**
* Returns 1 if answer specific feedback is activated
*
* @return integer The status of the answer specific feedback
* @access public
* @see $answer_feedback
*/
  function getAnswerFeedback()
	{
    return ($this->answer_feedback) ? $this->answer_feedback : 0;
  }

/**
* Returns 1 if answer specific feedback as reached points is activated
*
* @return integer The status of the answer specific feedback as reached points
* @access public
* @see $answer_feedback_points
*/
	function getAnswerFeedbackPoints()
	{
		return ($this->answer_feedback_points) ? $this->answer_feedback_points : 0;
	}

/**
* Gets the count system for the calculation of points
*
* @return integer The count system for the calculation of points
* @access public
* @see $count_system
*/
	function getCountSystem()
	{
		return ($this->count_system) ? $this->count_system : 0;
	}

/**
* Gets the count system for the calculation of points
*
* @return integer The count system for the calculation of points
* @access public
* @see $count_system
*/
	function _getCountSystem($active_id)
	{
		global $ilDB;
		$query = sprintf("SELECT tst_tests.count_system FROM tst_tests, tst_active WHERE tst_active.active_id = %s AND tst_active.test_fi = tst_tests.test_id",
			$ilDB->quote($active_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row["count_system"];
		}
		return FALSE;
	}

/**
* Gets the scoring type for multiple choice questions
*
* @return integer The scoring type for multiple choice questions
* @access public
* @see $mc_scoring
*/
	function getMCScoring()
	{
		return ($this->mc_scoring) ? $this->mc_scoring : 0;
	}

/**
* Determines if the score of a question should be cut at 0 points or the score of the whole test
*
* @return integer The score cutting type. 0 for question cutting, 1 for test cutting
* @access public
* @see $score_cutting
*/
	function getScoreCutting()
	{
		return ($this->score_cutting) ? $this->score_cutting : 0;
	}

/**
* Returns the password for test access
*
* @return striong  Password for test access
* @access public
* @see $password
*/
	function getPassword()
	{
		return (strlen($this->password)) ? $this->password : NULL;
	}

/**
* Gets the pass scoring type
*
* @return integer The pass scoring type
* @access public
* @see $pass_scoring
*/
	function getPassScoring()
	{
		return ($this->pass_scoring) ? $this->pass_scoring : 0;
	}

/**
* Gets the pass scoring type
*
* @return integer The pass scoring type
* @access public
* @see $pass_scoring
*/
	function _getPassScoring($active_id)
	{
		global $ilDB;
		$query = sprintf("SELECT tst_tests.pass_scoring FROM tst_tests, tst_active WHERE tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row["pass_scoring"];
		}
		return 0;
	}

/**
* Gets the scoring type for multiple choice questions
*
* @return mixed The scoring type for multiple choice questions
* @access public
* @see $mc_scoring
*/
	function _getMCScoring($active_id)
	{
		global $ilDB;
		$query = sprintf("SELECT tst_tests.mc_scoring FROM tst_tests, tst_active WHERE tst_active.active_id = %s AND tst_active.test_fi = tst_tests.test_id",
			$ilDB->quote($active_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row["mc_scoring"];
		}
		return FALSE;
	}

/**
* Determines if the score of a question should be cut at 0 points or the score of the whole test
*
* @return boolean The score cutting type. 0 for question cutting, 1 for test cutting
* @access public
* @see $score_cutting
*/
	function _getScoreCutting($active_id)
	{
		global $ilDB;
		$query = sprintf("SELECT tst_tests.score_cutting FROM tst_tests, tst_active WHERE tst_active.active_id = %s AND tst_tests.test_id = tst_active.test_fi",
			$ilDB->quote($active_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row["score_cutting"];
		}
		return FALSE;
	}

/**
* Gets the reporting date of the ilObjTest object
*
* @return string The reporting date of the test of an empty string (=FALSE) if no reporting date is set
* @access public
* @see $reporting_date
*/
	function getReportingDate()
	{
		return (strlen($this->reporting_date)) ? $this->reporting_date : NULL;
	}

/**
* Returns the nr of tries for the test
*
* @return integer The maximum number of tries
* @access public
* @see $nr_of_tries
*/
	function getNrOfTries()
	{
		return ($this->nr_of_tries) ? $this->nr_of_tries : 0;
	}

	/**
	* Returns the kiosk mode
	*
	* @return integer Kiosk mode
	* @access public
	* @see $_kiosk
	*/
	function getKiosk()
	{
		return ($this->_kiosk) ? $this->_kiosk : 0;
	}


	/**
	* Sets the kiosk mode for the test
	*
	* @param integer $kiosk The value for the kiosk mode.
	* @access public
	* @see $_kiosk
	*/
	function setKiosk($kiosk = 0)
	{
		$this->_kiosk = $kiosk;
	}

	/**
	* Returns the kiosk mode
	*
	* @return boolean Kiosk mode
	* @access public
	* @see $_kiosk
	*/
	function getKioskMode()
	{
		if (($this->_kiosk & 1) > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* Sets the kiosk mode for the test
	*
	* @param boolean $kiosk The value for the kiosk mode
	* @access public
	* @see $_kiosk
	*/
	public function setKioskMode($a_kiosk = FALSE)
	{
		if ($a_kiosk)
		{
			$this->_kiosk = $this->_kiosk | 1;
		}
		else
		{
			if ($this->getKioskMode())
			{
				$this->_kiosk = $this->_kiosk ^ 1;
			}
		}
	}

	/**
	* Returns the status of the kiosk mode title
	*
	* @return boolean Kiosk mode title
	* @access public
	* @see $_kiosk
	*/
	public function getShowKioskModeTitle()
	{
		if (($this->_kiosk & 2) > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* Set to true, if the full test title should be shown in kiosk mode
	*
	* @param boolean $a_title TRUE if the test title should be shown in kiosk mode, FALSE otherwise
	* @access public
	*/
	public function setShowKioskModeTitle($a_title = FALSE)
	{
		if ($a_title)
		{
			$this->_kiosk = $this->_kiosk | 2;
		}
		else
		{
			if ($this->getShowKioskModeTitle())
			{
				$this->_kiosk = $this->_kiosk ^ 2;
			}
		}
	}

	/**
	* Returns the status of the kiosk mode participant
	*
	* @return boolean Kiosk mode participant
	* @access public
	* @see $_kiosk
	*/
	public function getShowKioskModeParticipant()
	{
		if (($this->_kiosk & 4) > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* Set to true, if the participant's name should be shown in kiosk mode
	*
	* @param boolean $a_title TRUE if the participant's name should be shown in kiosk mode, FALSE otherwise
	* @access public
	*/
	public function setShowKioskModeParticipant($a_participant = FALSE)
	{
		if ($a_participant)
		{
			$this->_kiosk = $this->_kiosk | 4;
		}
		else
		{
			if ($this->getShowKioskModeParticipant())
			{
				$this->_kiosk = $this->_kiosk ^ 4;
			}
		}
	}

/**
* Returns if the previous answers should be shown for a learner
*
* @return integer 1 if the previous answers should be shown, 0 otherwise
* @access public
* @see $use_previous_answers
*/
	function getUsePreviousAnswers()
	{
		return ($this->use_previous_answers) ? $this->use_previous_answers : 0;
	}

/**
* Returns the value of the title_output status
*
* @return integer 0 for full title, 1 for title without points, 2 for no title
* @access public
* @see $title_output
*/
	function getTitleOutput()
	{
		return ($this->title_output) ? $this->title_output : 0;
	}

/**
* Returns the value of the title_output status
*
* @param integer $active_id The active id of a user
* @return integer 0 for full title, 1 for title without points, 2 for no title
* @access public
* @see $title_output
*/
	function _getTitleOutput($active_id)
	{
		global $ilDB;

		$query = sprintf("SELECT tst_tests.title_output FROM tst_tests, tst_active WHERE tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row["title_output"];
		}
		return 0;
	}

/**
* Returns if the previous results should be hidden for a learner
*
* @param integer $test_id The test id
* @param boolean $use_active_user_setting If true, the tst_use_previous_answers- of the active user should be used as well
* @return integer 1 if the previous results should be hidden, 0 otherwise
* @access public
* @see $use_previous_answers
*/
	function _getUsePreviousAnswers($active_id, $user_active_user_setting = false)
	{
		global $ilDB;
		global $ilUser;

		$use_previous_answers = 1;

		$result = $ilDB->queryF("SELECT tst_tests.use_previous_answers FROM tst_tests, tst_active WHERE tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s",
			array("integer"),
			array($active_id)
		);
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
			$use_previous_answers = $row["use_previous_answers"];
		}

		if ($use_previous_answers == 1)
		{
			if ($user_active_user_setting)
			{
				$res = $ilUser->getPref("tst_use_previous_answers");
				if ($res !== FALSE)
				{
					$use_previous_answers = $res;
				}
			}
		}
		return $use_previous_answers;
	}

/**
* Returns the processing time for the test
*
* @return string The processing time for the test
* @access public
* @see $processing_time
*/
	function getProcessingTime()
	{
		return (strlen($this->processing_time)) ? $this->processing_time : NULL;
	}

/**
* Returns the processing time for the test in seconds
*
* @return integer The processing time for the test in seconds
* @access public
* @see $processing_time
*/
	function getProcessingTimeInSeconds()
	{
		if (preg_match("/(\d{2}):(\d{2}):(\d{2})/", $this->getProcessingTime(), $matches))
		{
			return ($matches[1] * 3600) + ($matches[2] * 60) + $matches[3];
		}
			else
		{
			return 0;
		}
	}

	/**
	* Returns the seconds left from the actual time until the ending time
	*
	* @return integer The seconds left until the ending time is reached
	* @access public
	* @see $ending_time
	*/
		function getSecondsUntilEndingTime()
		{
			if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getEndingTime(), $matches))
			{
				$ending = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
				$now = time();
				return $ending - $now;
			}
			else
			{
				return 0;
			}
		}

	/**
	* Returns the state of the processing time (enabled/disabled)
	*
	* @return integer The processing time state (0 for disabled, 1 for enabled)
	* @access public
	* @see $processing_time
	*/
	function getEnableProcessingTime()
	{
		return ($this->enable_processing_time) ? $this->enable_processing_time : 0;
	}

	/**
	* Returns wheather the processing time should be reset or not
	*
	* @return integer 0 for no reset, 1 for a reset
	* @access public
	* @see $reset_processing_time
	*/
	function getResetProcessingTime()
	{
		return ($this->reset_processing_time) ? $this->reset_processing_time : 0;
	}

/**
* Returns the starting time of the test
*
* @return string The starting time of the test
* @access public
* @see $starting_time
*/
	function getStartingTime()
	{
		return (strlen($this->starting_time)) ? $this->starting_time : NULL;
	}

/**
* Returns the ending time of the test
*
* @return string The ending time of the test
* @access public
* @see $ending_time
*/
	function getEndingTime()
	{
		return (strlen($this->ending_time)) ? $this->ending_time : NULL;
	}

/**
* Sets the nr of tries for the test
*
* @param integer $nr_of_tries The maximum number of tries for the test. 0 for infinite tries.
* @access public
* @see $nr_of_tries
*/
	function setNrOfTries($nr_of_tries = 0)
	{
		$this->nr_of_tries = $nr_of_tries;
	}

/**
* Sets the status of the visibility of previous learner answers
**
* @param integer $use_previous_answers 1 if the previous answers should be shown
* @access public
* @see $use_previous_answers
*/
	function setUsePreviousAnswers($use_previous_answers = 1)
	{
		if ($use_previous_answers)
		{
			$this->use_previous_answers = 1;
		}
		else
		{
			$this->use_previous_answers = 0;
		}
	}

/**
* Sets the status of the title output
**
* @param integer $title_output 0 for full title, 1 for title without points, 2 for no title
* @access public
* @see $title_output
*/
	function setTitleOutput($title_output = 0)
	{
		switch ($title_output)
		{
			case 1:
				$this->title_output = 1;
				break;
			case 2:
				$this->title_output = 2;
				break;
			default:
				$this->title_output = 0;
				break;
		}
	}

/**
* Sets the processing time for the test
*
* @param string $processing_time The maximum processing time for the test given in hh:mm:ss
* @access public
* @see $processing_time
*/
	function setProcessingTime($processing_time = "00:00:00")
	{
		$this->processing_time = $processing_time;
	}

/**
* Sets the processing time enabled or disabled
*
* @param integer $enable 0 to disable the processing time, 1 to enable the processing time
* @access public
* @see $processing_time
*/
	function setEnableProcessingTime($enable = 0)
	{
		if ($enable) {
			$this->enable_processing_time = "1";
		} else {
			$this->enable_processing_time = "0";
		}
	}

/**
* Sets wheather the processing time should be reset or not
*
* @param integer $reset 1 to reset the processing time, 0 otherwise
* @access public
* @see $processing_time
*/
	function setResetProcessingTime($reset = 0)
	{
		if ($reset) 
		{
			$this->reset_processing_time = 1;
		} 
		else 
		{
			$this->reset_processing_time = 0;
		}
	}

/**
* Sets the starting time in database timestamp format for the test
*
* @param string $starting_time The starting time for the test. Empty string for no starting time.
* @access public
* @see $starting_time
*/
	function setStartingTime($starting_time = NULL)
	{
		$this->starting_time = $starting_time;
	}

/**
* Sets the ending time in database timestamp format for the test
*
* @param string $ending_time The ending time for the test. Empty string for no ending time.
* @access public
* @see $ending_time
*/
	function setEndingTime($ending_time = NULL)
	{
		$this->ending_time = $ending_time;
	}

/**
* Sets the count system for the calculation of points
*
* @param integer $a_count_system The count system for the calculation of points.
* @access public
* @see $count_system
*/
	function setCountSystem($a_count_system = COUNT_PARTIAL_SOLUTIONS)
	{
		$this->count_system = $a_count_system;
	}

/**
* Sets the password for test access
*
* @param string $a_password The password for test access
* @access public
* @see $password
*/
	function setPassword($a_password = NULL)
	{
		$this->password = $a_password;
	}

/**
* Sets the type of score cutting
*
* @param integer $a_score_cutting The type of score cutting. 0 for cut questions, 1 for cut tests
* @access public
* @see $score_cutting
*/
	function setScoreCutting($a_score_cutting = SCORE_CUT_QUESTION)
	{
		$this->score_cutting = $a_score_cutting;
	}

/**
* Sets the multiple choice scoring
*
* @param integer $a_mc_scoring The scoring for multiple choice questions
* @access public
* @see $mc_scoring
*/
	function setMCScoring($a_mc_scoring = SCORE_ZERO_POINTS_WHEN_UNANSWERED)
	{
		$this->mc_scoring = $a_mc_scoring;
	}

/**
* Sets the pass scoring
*
* @param integer $a_pass_scoring The pass scoring type
* @access public
* @see $pass_scoring
*/
	function setPassScoring($a_pass_scoring = SCORE_LAST_PASS)
	{
		switch ($a_pass_scoring)
		{
			case SCORE_BEST_PASS:
				$this->pass_scoring = SCORE_BEST_PASS;
				break;
			default:
				$this->pass_scoring = SCORE_LAST_PASS;
				break;
		}
	}

/**
* Removes a question from the test object
*
* @param integer $question_id The database id of the question to be removed
* @access public
* @see $test_id
*/
	function removeQuestion($question_id)
	{
		$question =& ilObjTest::_instanciateQuestion($question_id);
		include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$this->logAction($this->lng->txtlng("assessment", "log_question_removed", ilObjAssessmentFolder::_getLogLanguage()), $question_id);
		}
		$question->delete($question_id);
		$this->removeAllTestEditings($question_id);
		$this->loadQuestions();
		$this->saveQuestionsToDb();
	}

/**
* Removes all references to the question in executed tests in case the question has been changed.
* If a question was changed it cannot be guaranteed that the content and the meaning of the question
* is the same as before. So we have to delete all already started or completed tests using that question.
* Therefore we have to delete all references to that question in tst_solutions and the tst_active
* entries which were created for the user and test in the tst_solutions entry.
*
* @access public
*/
	function removeAllTestEditings($question_id = "")
	{
		global $ilDB;
		// remove the question from tst_solutions
		if ($question_id)
		{
			$query = sprintf("DELETE FROM tst_solutions USING tst_solutions, tst_active where tst_solutions.active_fi = tst_active.active_id AND tst_active.test_fi = %s AND tst_solutions.question_fi = %s",
				$ilDB->quote($this->getTestId()),
				$ilDB->quote($question_id)
			);
			$query2 = sprintf("DELETE FROM tst_qst_solved USING tst_qst_solved, tst_active where tst_qst_solved.active_fi = tst_active.active_id AND tst_active.test_fi = %s AND tst_qst_solved.question_fi = %s",
				$ilDB->quote($this->getTestId()),
				$ilDB->quote($question_id)
			);
			$query3 = sprintf("DELETE FROM tst_test_result USING tst_test_result, tst_active WHERE tst_active.test_fi = %s AND tst_test_result.question_fi = %s AND tst_active.active_id = tst_test_result.active_fi",
				$ilDB->quote($this->getTestId()),
				$ilDB->quote($question_id)
			);
			$query4 = sprintf("DELETE FROM tst_pass_result USING tst_pass_result, tst_active WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_pass_result.active_fi",
				$ilDB->quote($this->getTestId())
			);
		} 
		else 
		{
			$query = sprintf("DELETE FROM tst_solutions USING tst_solutions, tst_active where tst_solutions.active_fi = tst_active.active_id AND tst_active.test_fi = %s",
				$ilDB->quote($this->getTestId())
			);
			$query2 = sprintf("DELETE FROM tst_qst_solved USING tst_qst_solved, tst_active where tst_qst_solved.active_fi = tst_active.active_id AND tst_active.test_fi = %s",
				$ilDB->quote($this->getTestId())
			);
			$query3 = sprintf("DELETE FROM tst_test_result USING tst_test_result, tst_active WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_test_result.active_fi",
				$ilDB->quote($this->getTestId())
			);
			$query4 = sprintf("DELETE FROM tst_pass_result USING tst_pass_result, tst_active WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_pass_result.active_fi",
				$ilDB->quote($this->getTestId())
			);
			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_data_removed", ilObjAssessmentFolder::_getLogLanguage()));
			}
		}
		$query5 = sprintf("DELETE FROM tst_sequence USING tst_sequence, tst_active WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_sequence.active_fi",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
		$result = $ilDB->query($query2);
		$result = $ilDB->query($query3);
		$result = $ilDB->query($query4);
		$result = $ilDB->query($query5);

		if ($this->isRandomTest())
		{
			$query = sprintf("DELETE FROM tst_test_rnd_qst USING tst_test_rnd_qst, tst_active WHERE tst_active.test_fi = %s AND tst_test_rnd_qst.active_fi = tst_active.active_id",
				$ilDB->quote($this->getTestId())
			);
			$result = $ilDB->query($query);
		}

		// remove test_active entries, because test has changed
		$query = sprintf("DELETE FROM tst_active WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);

		// remove saved user passwords
		$query = sprintf("DELETE FROM usr_pref WHERE keyword = %s",
			$ilDB->quote("tst_password_".$this->getTestId(), "text")
		);
		$result = $ilDB->manipulate($query);
	}

	function removeSelectedTestResults($active_ids)
	{
		global $ilDB;

		// remove the question from tst_solutions
		foreach ($active_ids as $active_id)
		{
			$query = sprintf("DELETE FROM tst_solutions WHERE active_fi = %s",
				$ilDB->quote($active_id . "")
			);
			$query2 = sprintf("DELETE FROM tst_qst_solved WHERE active_fi = %s",
				$ilDB->quote($active_id . "")
			);
			$query3 = sprintf("DELETE FROM tst_test_result WHERE active_fi = %s",
				$ilDB->quote($active_id . "")
			);
			$query4 = sprintf("DELETE FROM tst_pass_result WHERE active_fi = %s",
				$ilDB->quote($active_id . "")
			);
			$result = $ilDB->query($query);
			$result = $ilDB->query($query2);
			$result = $ilDB->query($query3);
			$result = $ilDB->query($query4);

			if ($this->isRandomTest())
			{
				$query = sprintf("DELETE FROM tst_test_rnd_qst WHERE active_fi = %s",
					$ilDB->quote($active_id . "")
				);
				$result = $ilDB->query($query);
			}

			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction(sprintf($this->lng->txtlng("assessment", "log_selected_user_data_removed", ilObjAssessmentFolder::_getLogLanguage()), $this->userLookupFullName($this->_getUserIdFromActiveId($active_id))));
			}
		}

		// remove test_active entries of selected users
		foreach ($active_ids as $active_id)
		{
			$usr_id = $this->_getUserIdFromActiveId($active_id);

			$query = sprintf("DELETE FROM tst_active WHERE active_id = %s",
				$ilDB->quote($active_id . "")
			);
			$result = $ilDB->query($query);

			$query = sprintf("DELETE FROM tst_sequence WHERE active_fi = %s",
				$ilDB->quote($active_id)
			);
			$result = $ilDB->query($query);

			// remove saved user password
			if ($usr_id > 0)
			{
				$query = sprintf("DELETE FROM usr_pref WHERE usr_id = %s AND keyword = %s",
					$ilDB->quote($usr_id, "integer"),
					$ilDB->quote("tst_password_".$this->getTestId(), "text")
				);
				$result = $ilDB->manipulate($query);
			}
		}
	}

	function removeTestResultsForUser($user_id)
	{
		global $ilDB;

		$active_id = $this->getActiveIdOfUser($user_id);

		// remove the question from tst_solutions
		$query = sprintf("DELETE FROM tst_solutions WHERE active_fi = %s",
			$ilDB->quote($active_id . "")
		);
		$query2 = sprintf("DELETE FROM tst_qst_solved WHERE active_fi = %s",
			$ilDB->quote($active_id . "")
		);
		$query3 = sprintf("DELETE FROM tst_test_result WHERE active_fi = %s",
			$ilDB->quote($active_id . "")
		);
		$query4 = sprintf("DELETE FROM tst_pass_result WHERE active_fi = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		$result = $ilDB->query($query2);
		$result = $ilDB->query($query3);
		$result = $ilDB->query($query4);

		if ($this->isRandomTest())
		{
			$query = sprintf("DELETE FROM tst_test_rnd_qst WHERE active_fi = %s",
				$ilDB->quote($active_id . "")
			);
			$result = $ilDB->query($query);
		}

		include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$this->logAction(sprintf($this->lng->txtlng("assessment", "log_selected_user_data_removed", ilObjAssessmentFolder::_getLogLanguage()), $this->userLookupFullName($this->_getUserIdFromActiveId($active_id))));
		}

		$query = sprintf("DELETE FROM tst_sequence WHERE active_fi = %s",
			$ilDB->quote($active_id)
		);
		$result = $ilDB->query($query);

		// remove test_active entry
		$query = sprintf("DELETE FROM tst_active WHERE active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);

		// remove saved user password
		if ($user_id > 0)
		{
			$query = sprintf("DELETE FROM usr_pref WHERE usr_id = %s AND keyword = %s",
				$ilDB->quote($user_id, "integer"),
				$ilDB->quote("tst_password_".$this->getTestId(), "text")
			);
			$result = $ilDB->manipulate($query);
		}
	}

/**
* Moves a question up in order
*
* @param integer $question_id The database id of the question to be moved up
* @access public
* @see $test_id
*/
	function questionMoveUp($question_id)
	{
		global $ilDB;

		// Move a question up in sequence
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND question_fi=%s",
			$ilDB->quote($this->getTestId()),
			$ilDB->quote($question_id)
		);
		$result = $ilDB->query($query);
		$data = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
		if ($data->sequence > 1) {
			// OK, it's not the top question, so move it up
			$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND sequence=%s",
				$ilDB->quote($this->getTestId()),
				$ilDB->quote($data->sequence - 1)
			);
			$result = $ilDB->query($query);
			$data_previous = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
			// change previous dataset
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$ilDB->quote($data->sequence),
				$ilDB->quote($data_previous->test_question_id)
			);
			$result = $ilDB->query($query);
			// move actual dataset up
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$ilDB->quote($data->sequence - 1),
				$ilDB->quote($data->test_question_id)
			);
			$result = $ilDB->query($query);
			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_question_position_changed", ilObjAssessmentFolder::_getLogLanguage()) . ": " . ($data->sequence) . " => " . ($data->sequence-1), $question_id);
			}
		}
		$this->loadQuestions();
	}

/**
* Moves a question down in order
*
* @param integer $question_id The database id of the question to be moved down
* @access public
* @see $test_id
*/
	function questionMoveDown($question_id)
	{
		global $ilDB;

		// Move a question down in sequence
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND question_fi=%s",
			$ilDB->quote($this->getTestId()),
			$ilDB->quote($question_id)
		);
		$result = $ilDB->query($query);
		$data = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND sequence=%s",
			$ilDB->quote($this->getTestId()),
			$ilDB->quote($data->sequence + 1)
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			// OK, it's not the last question, so move it down
			$data_next = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
			// change next dataset
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$ilDB->quote($data->sequence),
				$ilDB->quote($data_next->test_question_id)
			);
			$result = $ilDB->query($query);
			// move actual dataset down
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$ilDB->quote($data->sequence + 1),
				$ilDB->quote($data->test_question_id)
			);
			$result = $ilDB->query($query);
			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_question_position_changed", ilObjAssessmentFolder::_getLogLanguage()) . ": " . ($data->sequence) . " => " . ($data->sequence+1), $question_id);
			}
		}
		$this->loadQuestions();
	}

	/**
	* Takes a question and creates a copy of the question for use in the test
	*
	* @param integer $question_id The database id of the question
	* @result integer The database id of the copied question
	* @access public
	*/
	function duplicateQuestionForTest($question_id)
	{
		global $ilUser;
		$question =& ilObjTest::_instanciateQuestion($question_id);
		$duplicate_id = $question->duplicate(true);

		return $duplicate_id;
	}

/**
* Insert a question in the list of questions
*
* @param integer $question_id The database id of the inserted question
* @access	public
*/
	function insertQuestion($question_id)
	{
		global $ilDB;

		$duplicate_id = $this->duplicateQuestionForTest($question_id);

		// get maximum sequence index in test
		$query = sprintf("SELECT MAX(sequence) seq FROM tst_test_question WHERE test_fi=%s",
			$ilDB->quote($this->getTestId())
			);
		$result = $ilDB->query($query);
		$sequence = 1;

		if ($result->numRows() == 1)
		{
			$data = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
			$sequence = $data->seq + 1;
		}

		$result = $ilDB->manipulateF("INSERT INTO tst_test_question (test_question_id, test_fi, question_fi, sequence, tstamp) VALUES (NULL, %s, %s, %s, %s)",
			array('integer','integer','integer','integer'),
			array($this->getTestId(), $duplicate_id, $sequence, time())
		);
		if (PEAR::isError($result)) 
		{
			global $ilias;
			$ilias->raiseError($result->getMessage());
		}
		else
		{
			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_question_added", ilObjAssessmentFolder::_getLogLanguage()) . ": " . $sequence, $duplicate_id);
			}
		}
		// remove test_active entries, because test has changed
		$query = sprintf("DELETE FROM tst_active WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
			);
		$result = $ilDB->query($query);
		$this->loadQuestions();
		$this->saveCompleteStatus();
	}

/**
* Returns the titles of the test questions in question sequence
*
* @return array The question titles
* @access public
* @see $questions
*/
	function &getQuestionTitles()
	{
		$titles = array();
		if (!$this->isRandomTest())
		{
			global $ilDB;
			$result = $ilDB->queryF("SELECT qpl_questions.title FROM tst_test_question, qpl_questions WHERE tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id ORDER BY tst_test_question.sequence",
				array('integer'),
				array($this->getTestId())
			);
			$result = $ilDB->query($query);
			while ($row = $ilDB->fetchAssoc($result))
			{
				array_push($titles, $row["title"]);
			}
		}
		return $titles;
	}

	/**
	* Returns the title of a test question and checks if the title output is allowed.
	* If not, the localized text "question" will be returned.
	*
	* @param string $title The original title of the question
	* @return string The title for the question title output
	* @access public
	*/
	function getQuestionTitle($title)
	{
		if ($this->getTitleOutput() == 2)
		{
			return $this->lng->txt("ass_question");
		}
		else
		{
			return $title;
		}
	}

/**
* Returns the dataset for a given question id
*
* @param integer $question_id The database id of the question
* @return object Question dataset
* @access public
* @see $questions
*/
	function getQuestionDataset($question_id)
	{
		global $ilDB;

		$result = sprintf("SELECT qpl_questions.*, qpl_qst_type.type_tag FROM qpl_questions, qpl_qst_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id",
			array('integer'),
			array($question_id)
		);
		$result = $ilDB->query($query);
		$row = $ilDB->fetchObject($result);
		return $row;
	}

/**
* Get the id's of the questions which are already part of the test
*
* @return array An array containing the already existing questions
* @access	public
*/
	function &getExistingQuestions($pass = NULL)
	{
		global $ilUser;
		global $ilDB;

		$existing_questions = array();
		$active_id = $this->getActiveIdOfUser($ilUser->getId());
		if ($this->isRandomTest())
		{
			if (is_null($pass)) $pass = 0;
			$result = $ilDB->queryF("SELECT qpl_questions.original_id FROM qpl_questions, tst_test_rnd_qst WHERE tst_test_rnd_qst.active_fi = %s AND tst_test_rnd_qst.question_fi = qpl_questions.question_id AND tst_test_rnd_qst.pass = %s",
				array('integer','integer'),
				array($active_id, $pass)
			);
		}
		else
		{
			$result = $ilDB->queryF("SELECT qpl_questions.original_id FROM qpl_questions, tst_test_question WHERE tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id",
				array('integer'),
				array($this->getTestId())
			);
		}
		$result = $ilDB->query($query);
		while ($data = $ilDB->fetchObject($result)) 
		{
			array_push($existing_questions, $data->original_id);
		}
		return $existing_questions;
	}

/**
* Returns the question type of a question with a given id
*
* @param integer $question_id The database id of the question
* @result string The question type string
* @access private
*/
  function getQuestionType($question_id)
	{
		global $ilDB;

		if ($question_id < 1) return -1;
		$result = $ilDB->queryF("SELECT type_tag FROM qpl_questions, qpl_qst_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id",
			array('integer'),
			array($question_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1) 
		{
			$data = $ilDB->fetchObject($result);
			return $data->type_tag;
		} 
		else 
		{
			return "";
		}
	}

/**
* Write the initial entry for the tests working time to the database
*
* @param integer $user_id The database id of the user working with the test
* @access	public
*/
	function startWorkingTime($active_id, $pass)
	{
		global $ilDB;

		$affectedRows = $ilDB->manipulateF("INSERT INTO tst_times (times_id, active_fi, started, finished, pass, tstamp) VALUES (NULL, %s, %s, %s, %s, %s)",
			array('integer', 'timedate', 'timedate', 'integer', 'integer'),
			array($active_id, strftime("%Y-%m-%d %H:%M:%S"), strftime("%Y-%m-%d %H:%M:%S"), $pass, time())
		);
		return $ilDB->getLastInsertId();
	}

/**
* Update the working time of a test when a question is answered
*
* @param integer $times_id The database id of a working time entry
* @access	public
*/
	function updateWorkingTime($times_id)
	{
		global $ilDB;

		$q = sprintf("UPDATE tst_times SET finished = %s WHERE times_id = %s",
			$ilDB->quote(strftime("%Y-%m-%d %H:%M:%S")),
			$ilDB->quote($times_id)
		);
		$result = $ilDB->query($q);
	}

/**
* Gets the id's of all questions a user already worked through
*
* @return array The question id's of the questions already worked through
* @access	public
*/
	function &getWorkedQuestions($active_id, $pass = NULL)
	{
		global $ilUser;
		global $ilDB;

		if (is_null($pass))
		{
			$query = sprintf("SELECT question_fi FROM tst_solutions WHERE active_fi = %s AND pass = 0 GROUP BY question_fi",
				$ilDB->quote($active_id . "")
			);
		}
		else
		{
			$query = sprintf("SELECT question_fi FROM tst_solutions WHERE active_fi = %s AND pass = %s GROUP BY question_fi",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($pass . "")
			);
		}
		$result = $ilDB->query($query);
		$result_array = array();
		while ($row = $result->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			array_push($result_array, $row->question_fi);
		}
		return $result_array;
	}

	/**
	* Returns true if an active user completed a test pass and did not start a new pass
	*
	* @param integer $active_id The active id of the user
	* @param integer $currentpass The current test pass of the user
	* @return boolean true if an active user completed a test pass and did not start a new pass, false otherwise
	* @access public
	*/
	function isTestFinishedToViewResults($active_id, $currentpass)
	{
		$num = $this->getPassFinishDate($active_id, $currentpass);
		if (($currentpass > 0) && ($num == 0))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

/**
* Returns all questions of a test in test order
*
* @return array An array containing the id's as keys and the database row objects as values
* @access public
*/
	function &getAllQuestions($pass = NULL)
	{
		global $ilUser;
		global $ilDB;

		$result_array = array();
		if ($this->isRandomTest())
		{
			$active_id = $this->getActiveIdOfUser($ilUser->getId());
			$this->loadQuestions($active_id, $pass);
			if (count($this->questions) == 0) return $result_array;
			if (is_null($pass))
			{
				$pass = $this->_getPass($active_id);
			}
			$values = array($active_id, $pass);
			$values = array_merge($values, $this->questions);
			$types = array('integer','integer');
			$types = $ilDB->addTypesToArray($types, 'integer', count($this->questions));
			$phs = array('%s','%s');
			$phs = $ilDB->addTypesToArray($phs, '%s', count($this->questions));			
			$result = $ilDB->queryF("SELECT qpl_questions.* FROM qpl_questions, tst_test_rnd_qst WHERE tst_test_rnd_qst.question_fi = qpl_questions.question_id AND tst_test_rnd_qst.active_fi = %s AND tst_test_rnd_qst.pass = %s AND qpl_questions.question_id IN (" . join($phs, ",") . ")",
				$types,
				$values
			);
		}
		else
		{
			if (count($this->questions) == 0) return $result_array;
			$types = array();
			$types = $ilDB->addTypesToArray($types, 'integer', count($this->questions));
			$phs = array();
			$phs = $ilDB->addTypesToArray($phs, '%s', count($this->questions));			
			$result = $ilDB->queryF("SELECT qpl_questions.* FROM qpl_questions, tst_test_question WHERE tst_test_question.question_fi = qpl_questions.question_id AND qpl_questions.question_id IN (" . join($phs, ",") . ")",
				$types,
				$this->questions
			);
		}
		$result = $ilDB->query($query);
		while ($row = $ilDB->fetchAssoc($result))
		{
			$result_array[$row["question_id"]] = $row;
		}
		return $result_array;
	}

	/**
	* Gets the active id of a given user
	*
	* @param integer $user_id The database id of the user
	* @param string $anonymous_id The anonymous id if the test is an anonymized test
	* @return integer The active ID
	* @access	public
	*/
		function getActiveIdOfUser($user_id = "", $anonymous_id = "")
		{
			global $ilDB;
			global $ilUser;

			if (!$user_id) $user_id = $ilUser->getId();
			if (($_SESSION["AccountId"] == ANONYMOUS_USER_ID) && (strlen($_SESSION["tst_access_code"][$this->getTestId()])))
			{
				$query = sprintf("SELECT active_id FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s",
					$ilDB->quote($user_id),
					$ilDB->quote($this->test_id),
					$ilDB->quote($_SESSION["tst_access_code"][$this->getTestId()])
				);
			}
			else if (strlen($anonymous_id))
			{
				$query = sprintf("SELECT active_id FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s",
					$ilDB->quote($user_id),
					$ilDB->quote($this->test_id),
					$ilDB->quote($anonymous_id)
				);
			}
			else
			{
				if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
				{
					return NULL;
				}
				$query = sprintf("SELECT active_id FROM tst_active WHERE user_fi = %s AND test_fi = %s",
					$ilDB->quote($user_id),
					$ilDB->quote($this->test_id)
				);
			}
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
				return $row["active_id"];
			}
			else
			{
				return 0;
			}
		}

/**
* Gets the active id of the tst_active table for the active user
*
* @param integer $user_id The database id of the user
* @param integer $test_id The database id of the test
* @return object The database row of the tst_active table
* @access	public
*/
	function _getActiveIdOfUser($user_id = "", $test_id = "") 
	{
		global $ilDB;
		global $ilUser;

		if (!$user_id) {
			$user_id = $ilUser->id;
		}
		if (!$test_id)
		{
			return "";
		}
		$query = sprintf("SELECT tst_active.active_id FROM tst_active WHERE user_fi = %s AND test_fi = %s",
			$ilDB->quote($user_id . ""),
			$ilDB->quote($test_id . "")
		);

		$result = $ilDB->query($query);
		if ($result->numRows()) 
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row["active_id"];
		} 
		else 
		{
			return "";
		}
	}

	/**
	* Shuffles the values of a given array
	*
	* @param array $array An array which should be shuffled
	* @access public
	*/
	function pcArrayShuffle($array)
	{
		mt_srand((double)microtime()*1000000);
		$i = count($array);
		if ($i > 0)
		{
			while(--$i)
			{
				$j = mt_rand(0, $i);
				if ($i != $j)
				{
					// swap elements
					$tmp = $array[$j];
					$array[$j] = $array[$i];
					$array[$i] = $tmp;
				}
			}
		}
		return $array;
	}
	
	/**
	* Calculates the results of a test for a given user
	* and returns an array with all test results
	*
	* @return array An array containing the test results for the given user
	* @access public
	*/
	function &getTestResult($active_id, $pass = NULL, $ordered_sequence = FALSE)
	{
		//		global $ilBench;
		$total_max_points = 0;
		$total_reached_points = 0;

		$key = 1;
		$result_array = array();
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$workedthrough = 0;

		if (is_null($pass))
		{
			$pass = $this->_getResultPass($active_id);
		}
		include_once "./Modules/Test/classes/class.ilTestSequence.php";
		$testSequence = new ilTestSequence($active_id, $pass, $this->isRandomTest());
		$sequence = array();
		if ($ordered_sequence)
		{
			$sequence = $testSequence->getOrderedSequence();
		}
		else
		{
			$sequence = $testSequence->getUserSequence();
		}
		foreach ($sequence as $sequenceindex)
		{
			$value = $testSequence->getQuestionForSequence($sequenceindex);
			$max_points = assQuestion::_getMaximumPoints($value);
			$total_max_points += $max_points;
			$reached_points = assQuestion::_getReachedPoints($active_id, $value, $pass);
			if (assQuestion::_isWorkedThrough($active_id, $value, $pass))
			{
				$workedthrough = 1;
			}
			else
			{
				$workedthrough = 0;
			}
			$total_reached_points += $reached_points;
			if ($max_points > 0)
			{
				$percentvalue = $reached_points / $max_points;
			}
			else
			{
				$percentvalue = 0;
			}
			if ($percentvalue < 0) $percentvalue = 0.0;
			$info =& assQuestion::_getQuestionInfo($value);
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			$row = array(
				"nr" => "$key",
				"title" => ilUtil::prepareFormOutput($this->getQuestionTitle($info["title"])),
				"max" => $max_points,
				"reached" => $reached_points,
				"percent" => sprintf("%2.2f ", ($percentvalue) * 100) . "%",
				"solution" => assQuestion::_getSuggestedSolutionOutput($value),
				"type" => $info["type_tag"],
				"qid" => $value,
				"original_id" => $info["original_id"],
				"workedthrough" => $workedthrough
			);
			array_push($result_array, $row);
			$key++;
		}

		if ($this->getScoreCutting() == 1)
		{
			if ($total_reached_points < 0)
			{
				$total_reached_points = 0;
			}
		}
		$result_array["test"]["total_max_points"] = $total_max_points;
		$result_array["test"]["total_reached_points"] = $total_reached_points;
		if ((!$total_reached_points) or (!$total_max_points))
		{
			$percentage = 0.0;
		}
		else
		{
			$percentage = ($total_reached_points / $total_max_points) * 100.0;
			if ($percentage < 0) $percentage = 0.0;
		}
		$mark_obj = $this->mark_schema->getMatchingMark($percentage);
		$passed = "";
		if ($mark_obj)
		{
			if ($mark_obj->getPassed())
			{
				$passed = 1;
			}
			else
			{
				$passed = 0;
			}
		}
		$result_array["test"]["passed"] = $passed;
		return $result_array;
	}

/**
* Returns the number of persons who started the test
*
* @return integer The number of persons who started the test
* @access public
*/
	function evalTotalPersons()
	{
		global $ilDB;

		$q = sprintf("SELECT COUNT(*) as total FROM tst_active WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$row = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
		return $row->total;
	}

/**
* Returns the complete working time in seconds a user worked on the test
*
* @return integer The working time in seconds
* @access public
*/
	function getCompleteWorkingTime($user_id)
	{
		global $ilDB;

		$q = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi AND tst_active.user_fi = %s",
			$ilDB->quote($this->getTestId()),
			$ilDB->quote($user_id)
		);
		$result = $ilDB->query($q);
		$time = 0;
		while ($row = $result->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->started, $matches);
			$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->finished, $matches);
			$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			$time += ($epoch_2 - $epoch_1);
		}
		return $time;
	}

	/**
	* Returns the complete working time in seconds for all test participants
	*
	* @return array An array containing the working time in seconds for all test participants
	* @access public
	*/
	function &getCompleteWorkingTimeOfParticipants()
	{
		return $this->_getCompleteWorkingTimeOfParticipants($this->getTestId());
	}

	/**
	* Returns the complete working time in seconds for all test participants
	*
	* @param integer $test_id The database ID of the test
	* @return array An array containing the working time in seconds for all test participants
	* @access public
	*/
	function &_getCompleteWorkingTimeOfParticipants($test_id)
	{
		global $ilDB;

		$query = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi ORDER BY tst_times.active_fi, tst_times.started",
			$ilDB->quote($test_id . "")
		);
		$result = $ilDB->query($query);
		$time = 0;
		$times = array();
		while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			if (!array_key_exists($row["active_fi"], $times))
			{
				$times[$row["active_fi"]] = 0;
			}
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches);
			$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["finished"], $matches);
			$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			$times[$row["active_fi"]] += ($epoch_2 - $epoch_1);
		}
		return $times;
	}

	/**
	* Returns the complete working time in seconds for a test participant
	*
	* @return integer The working time in seconds for the test participant
	* @access public
	*/
	function getCompleteWorkingTimeOfParticipant($active_id)
	{
		global $ilDB;

		$query = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi AND tst_active.active_id = %s ORDER BY tst_times.active_fi, tst_times.started",
			$ilDB->quote($this->getTestId() . ""),
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		$time = 0;
		while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches);
			$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["finished"], $matches);
			$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			$time += ($epoch_2 - $epoch_1);
		}
		return $time;
	}

	/**
	* Returns the complete working time in seconds for a test participant
	*
	* @return integer The working time in seconds for the test participant
	* @access public
	*/
	function _getWorkingTimeOfParticipantForPass($active_id, $pass)
	{
		global $ilDB;

		$query = sprintf("SELECT * FROM tst_times WHERE active_fi = %s AND pass = %s ORDER BY started",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		$time = 0;
		while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches);
			$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["finished"], $matches);
			$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			$time += ($epoch_2 - $epoch_1);
		}
		return $time;
	}

	/**
	* Returns the first and last visit of a participant
	*
	* @param integer $active_id The active ID of the participant
	* @return array The first and last visit of a participant
	* @access public
	*/
	function getVisitTimeOfParticipant($active_id)
	{
		return ilObjTest::_getVisitTimeOfParticipant($this->getTestId(), $active_id);
	}

	/**
	* Returns the first and last visit of a participant
	*
	* @param integer $test_id The database ID of the test
	* @param integer $active_id The active ID of the participant
	* @return array The first and last visit of a participant
	* @access public
	*/
	function _getVisitTimeOfParticipant($test_id, $active_id)
	{
		global $ilDB;

		$query = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi AND tst_active.active_id = %s ORDER BY tst_times.started",
			$ilDB->quote($test_id . ""),
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		$firstvisit = 0;
		$lastvisit = 0;
		while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches);
			$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			if ($firstvisit == 0 || $epoch_1 < $firstvisit) $firstvisit = $epoch_1;
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["finished"], $matches);
			$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			if ($epoch_2 > $lastvisit) $lastvisit = $epoch_2;
		}
		return array("firstvisit" => $firstvisit, "lastvisit" => $lastvisit);
	}

/**
* Returns the statistical evaluation of the test for a specified user
*
* @return arrary The statistical evaluation array of the test
* @access public
*/
	function &evalStatistical($active_id)
	{
		global $ilDB;
//		global $ilBench;
		$pass = ilObjTest::_getResultPass($active_id);
		$test_result =& $this->getTestResult($active_id, $pass);
		$q = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.active_id = %s AND tst_active.active_id = tst_times.active_fi",
			$ilDB->quote($active_id)
		);
		$result = $ilDB->query($q);
		$times = array();
		$first_visit = 0;
		$last_visit = 0;
		while ($row = $result->fetchRow(MDB2_FETCHMODE_OBJECT)) {
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->started, $matches);
			$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			if (!$first_visit) {
				$first_visit = $epoch_1;
			}
			if ($epoch_1 < $first_visit) {
				$first_visit = $epoch_1;
			}
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->finished, $matches);
			$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			if (!$last_visit) {
				$last_visit = $epoch_2;
			}
			if ($epoch_2 > $last_visit) {
				$last_visit = $epoch_2;
			}
			$times[$row->active_fi] += ($epoch_2 - $epoch_1);
		}
		$max_time = 0;
		foreach ($times as $key => $value) {
			$max_time += $value;
		}
		if ((!$test_result["test"]["total_reached_points"]) or (!$test_result["test"]["total_max_points"]))
		{
			$percentage = 0.0;
		}
		else
		{
			$percentage = ($test_result["test"]["total_reached_points"] / $test_result["test"]["total_max_points"]) * 100.0;
			if ($percentage < 0) $percentage = 0.0;
		}
		$mark_obj = $this->mark_schema->getMatchingMark($percentage);
		$first_date = getdate($first_visit);
		$last_date = getdate($last_visit);
		$qworkedthrough = 0;
		foreach ($test_result as $key => $value)
		{
			if (preg_match("/\d+/", $key))
			{
				$qworkedthrough += $value["workedthrough"];
			}
		}
		if (!$qworkedthrough)
		{
			$atimeofwork = 0;
		}
		else
		{
			$atimeofwork = $max_time / $qworkedthrough;
		}
		$result_mark = "";
		$passed = "";
		if ($mark_obj)
		{
			$result_mark = $mark_obj->getShortName();
			if ($mark_obj->getPassed())
			{
				$passed = 1;
			}
			else
			{
				$passed = 0;
			}
		}
		$percent_worked_through = 0;
		if (count($this->questions))
		{
			$percent_worked_through = $qworkedthrough / count($this->questions);
		}
		$result_array = array(
			"qworkedthrough" => $qworkedthrough,
			"qmax" => count($this->questions),
			"pworkedthrough" => $percent_worked_through,
			"timeofwork" => $max_time,
			"atimeofwork" => $atimeofwork,
			"firstvisit" => $first_date,
			"lastvisit" => $last_date,
			"resultspoints" => $test_result["test"]["total_reached_points"],
			"maxpoints" => $test_result["test"]["total_max_points"],
			"resultsmarks" => $result_mark,
			"passed" => $passed,
			"distancemedian" => "0"
		);
		foreach ($test_result as $key => $value)
		{
			if (preg_match("/\d+/", $key))
			{
				$result_array[$key] = $value;
			}
		}
		return $result_array;
	}

/**
* Returns an array with the total points of all users who passed the test
* This array could be used for statistics
*
* @return array The total point values
* @access public
*/
	function &getTotalPointsPassedArray()
	{
		$totalpoints_array = array();
		$all_users =& $this->evalTotalParticipantsArray();
		foreach ($all_users as $active_id => $user_name)
		{
			$test_result =& $this->getTestResult($active_id);
			$reached = $test_result["test"]["total_reached_points"];
			$total = $test_result["test"]["total_max_points"];
			$percentage = $total != 0 ? $reached/$total : 0;
			$mark = $this->mark_schema->getMatchingMark($percentage*100.0);
			if ($mark)
			{
				if ($mark->getPassed())
				{
					array_push($totalpoints_array, $test_result["test"]["total_reached_points"]);
				}
			}
		}
		return $totalpoints_array;
	}

/**
* Returns all persons who started the test
*
* @return arrary The active ids, names and logins of the persons who started the test
* @access public
*/
	function &getParticipants()
	{
		global $ilDB;
		$q = sprintf("SELECT tst_active.active_id, usr_data.usr_id, usr_data.firstname, usr_data.lastname, usr_data.title, usr_data.login FROM tst_active LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id WHERE tst_active.test_fi = %s ORDER BY usr_data.lastname ASC",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$persons_array = array();
		while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$name = $this->lng->txt("unknown");
			$fullname = $this->lng->txt("unknown");
			$login = "";
			if (!$this->getAnonymity())
			{
				if (strlen($row["firstname"].$row["lastname"].$row["title"]) == 0)
				{
					$name = $this->lng->txt("deleted_user");
					$fullname = $this->lng->txt("deleted_user");
					$login = $this->lng->txt("unknown");
				}
				else
				{
					$login = $row["login"];
					if ($row["user_fi"] == ANONYMOUS_USER_ID)
					{
						$name = $this->lng->txt("unknown");
						$fullname = $this->lng->txt("unknown");
					}
					else
					{
						$name = trim($row["lastname"] . ", " . $row["firstname"] . " " .  $row["title"]);
						$fullname = trim($row["title"] . " " . $row["firstname"] . " " .  $row["lastname"]);
					}
				}
			}
			$persons_array[$row["active_id"]] = array(
				"name" => $name,
				"fullname" => $fullname,
				"login" => $login
			);
		}
		return $persons_array;
	}

/**
* Returns all persons who started the test
*
* @return arrary The user id's and names of the persons who started the test
* @access public
*/
	function &evalTotalPersonsArray($name_sort_order = "asc")
	{
		global $ilDB;
		$q = sprintf("SELECT tst_active.active_id, usr_data.firstname, usr_data.lastname, usr_data.title FROM tst_active LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id WHERE tst_active.test_fi = %s ORDER BY usr_data.lastname " . strtoupper($name_sort_order),
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$persons_array = array();
		while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			if ($this->getAnonymity())
			{
				$persons_array[$row["active_id"]] = $this->lng->txt("unknown");
			}
			else
			{
				if (strlen($row["firstname"].$row["lastname"].$row["title"]) == 0)
				{
					$persons_array[$row["active_id"]] = $this->lng->txt("deleted_user");
				}
				else
				{
					if ($row["user_fi"] == ANONYMOUS_USER_ID)
					{
						$persons_array[$row["active_id"]] = $row["lastname"];
					}
					else
					{
						$persons_array[$row["active_id"]] = trim($row["lastname"] . ", " . $row["firstname"] . " " .  $row["title"]);
					}
				}
			}
		}
		return $persons_array;
	}

/**
* Returns all participants who started the test
*
* @return arrary The active user id's and names of the persons who started the test
* @access public
*/
	function &evalTotalParticipantsArray($name_sort_order = "asc")
	{
		global $ilDB;
		$q = sprintf("SELECT tst_active.active_id, usr_data.login, usr_data.firstname, usr_data.lastname, usr_data.title FROM tst_active LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id WHERE tst_active.test_fi = %s ORDER BY usr_data.lastname " . strtoupper($name_sort_order),
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$persons_array = array();
		while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			if ($this->getAnonymity())
			{
				$persons_array[$row["active_id"]] = array("name" => $this->lng->txt("unknown"));
			}
			else
			{
				if (strlen($row["firstname"].$row["lastname"].$row["title"]) == 0)
				{
					$persons_array[$row["active_id"]] = array("name" => $this->lng->txt("deleted_user"));
				}
				else
				{
					if ($row["user_fi"] == ANONYMOUS_USER_ID)
					{
						$persons_array[$row["active_id"]] = array("name" => $row["lastname"]);
					}
					else
					{
						$persons_array[$row["active_id"]] = array("name" => trim($row["lastname"] . ", " . $row["firstname"] . " " .  $row["title"]), "login" => $row["login"]);
					}
				}
			}
		}
		return $persons_array;
	}

/**
* Returns the number of total finished tests
*
* @return integer The number of total finished tests
* @access public
*/
	function evalTotalFinished()
	{
		global $ilDB;

		$q = sprintf("SELECT COUNT(*) as total FROM tst_active WHERE test_fi = %s AND tries > 0",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$row = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
		return $row->total;
	}

	/**
	* Retrieves all the assigned questions for all test passes of a test participant
	*
	* @return array An associated array containing the questions
	* @access public
	*/
	function &getQuestionsOfTest($active_id)
	{
		global $ilDB;
		if ($this->isRandomTest())
		{
			$ilDB->setLimit($this->getQuestionCount(), 0);
			$query = sprintf("SELECT tst_test_rnd_qst.sequence, tst_test_rnd_qst.question_fi, " .
				"tst_test_rnd_qst.pass, qpl_questions.points " .
				"FROM tst_test_rnd_qst, qpl_questions " .
				"WHERE tst_test_rnd_qst.question_fi = qpl_questions.question_id " .
				"AND tst_test_rnd_qst.active_fi = %s ORDER BY tst_test_rnd_qst.sequence",
				$ilDB->quote($active_id . "")
			);
		}
		else
		{
			$query = sprintf("SELECT tst_test_question.sequence, tst_test_question.question_fi, " .
				"qpl_questions.points " .
				"FROM tst_test_question, tst_active, qpl_questions " .
				"WHERE tst_test_question.question_fi = qpl_questions.question_id " .
				"AND tst_active.active_id = %s AND tst_active.test_fi = tst_test_question.test_fi",
				$ilDB->quote($active_id . "")
			);
		}
		$result = $ilDB->query($query);
		$qtest = array();
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				array_push($qtest, $row);
			}
		}
		return $qtest;
	}
	
	/**
	* Retrieves all the assigned questions for a test participant in a given test pass
	*
	* @return array An associated array containing the questions
	* @access public
	*/
	function &getQuestionsOfPass($active_id, $pass)
	{
		global $ilDB;
		if ($this->isRandomTest())
		{
			$ilDB->setLimit($this->getQuestionCount(), 0);
			$query = sprintf("SELECT tst_test_rnd_qst.sequence, tst_test_rnd_qst.question_fi, " .
				"qpl_questions.points " .
				"FROM tst_test_rnd_qst, qpl_questions " .
				"WHERE tst_test_rnd_qst.question_fi = qpl_questions.question_id " .
				"AND tst_test_rnd_qst.active_fi = %s AND tst_test_rnd_qst.pass = %s " .
				"ORDER BY tst_test_rnd_qst.sequence",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($pass . "")
			);
		}
		else
		{
			$query = sprintf("SELECT tst_test_question.sequence, tst_test_question.question_fi, " .
				"qpl_questions.points " .
				"FROM tst_test_question, tst_active, qpl_questions " .
				"WHERE tst_test_question.question_fi = qpl_questions.question_id " .
				"AND tst_active.active_id = %s AND tst_active.test_fi = tst_test_question.test_fi",
				$ilDB->quote($active_id . "")
			);
		}
		$result = $ilDB->query($query);
		$qpass = array();
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				array_push($qpass, $row);
			}
		}
		return $qpass;
	}
	
	function getUnfilteredEvaluationData()
	{
		global $ilDB;
		include_once "./Modules/Test/classes/class.ilTestEvaluationPassData.php";
		include_once "./Modules/Test/classes/class.ilTestEvaluationUserData.php";
		include_once "./Modules/Test/classes/class.ilTestEvaluationData.php";
		$data = new ilTestEvaluationData($this);
		$result = $ilDB->queryF("SELECT tst_test_result.*, qpl_questions.original_id, qpl_questions.title questiontitle, " .
			"qpl_questions.points maxpoints " .
			"FROM tst_test_result, qpl_questions, tst_active " .
			"WHERE tst_active.active_id = tst_test_result.active_fi " .
			"AND qpl_questions.question_id = tst_test_result.question_fi " .
			"AND tst_active.test_fi = %s " .
			"ORDER BY active_id, pass, tstamp",
			array('integer'),
			array($this->getTestId())
		);
		$pass = NULL;
		$checked = array();
		$datasets = 0;
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$data->getParticipant($row["active_fi"])->getPass($row["pass"])->addAnsweredQuestion($row["original_id"] ? $row["original_id"] : $row["question_fi"], $row["maxpoints"], $row["points"]);
		}

		foreach (array_keys($data->getParticipants()) as $active_id)
		{
			if ($this->isRandomTest())
			{
				for ($testpass = 0; $testpass <= $data->getParticipant($active_id)->getLastPass(); $testpass++)
				{
					$ilDB->setLimit($this->getQuestionCount(), 0);
					$result = $ilDB->queryF("SELECT tst_test_rnd_qst.sequence, tst_test_rnd_qst.question_fi, qpl_questions.original_id, " .
						"tst_test_rnd_qst.pass, qpl_questions.points, qpl_questions.title " .
						"FROM tst_test_rnd_qst, qpl_questions " .
						"WHERE tst_test_rnd_qst.question_fi = qpl_questions.question_id " .
						"AND tst_test_rnd_qst.pass = %s " .
						"AND tst_test_rnd_qst.active_fi = %s ORDER BY tst_test_rnd_qst.sequence",
						array('integer','integer'),
						array($testpass, $active_id)
					);
					$result = $ilDB->query($query);
					if ($result->numRows())
					{
						while ($row = $ilDB->fetchAssoc($result))
						{
							$tpass = array_key_exists("pass", $row) ? $row["pass"] : 0;
							$data->getParticipant($active_id)->addQuestion($row["original_id"] ? $row["original_id"] : $row["question_fi"], $row["question_fi"], $row["points"], $row["sequence"], $tpass);
							$data->addQuestionTitle($row["original_id"] ? $row["original_id"] : $row["question_fi"], $row["title"]);
						}
					}
				}
			}
			else
			{
				$result = $ilDB->queryF("SELECT tst_test_question.sequence, tst_test_question.question_fi, " .
					"qpl_questions.points, qpl_questions.title, qpl_questions.original_id " .
					"FROM tst_test_question, tst_active, qpl_questions " .
					"WHERE tst_test_question.question_fi = qpl_questions.question_id " .
					"AND tst_active.active_id = %s AND tst_active.test_fi = tst_test_question.test_fi ORDER BY tst_test_question.sequence",
					array('integer'),
					array($active_id)
				);
				if ($result->numRows())
				{
					$questionsbysequence = array();
					while ($row = $ilDB->fetchAssoc($result))
					{
						$questionsbysequence[$row["sequence"]] = $row;
					}
					$seqresult = $ilDB->queryF("SELECT * FROM tst_sequence WHERE active_fi = %s",
						array('integer'),
						array($active_id)
					);
					while ($seqrow = $ilDB->fetchAssoc($seqresult))
					{
						$questionsequence = unserialize($seqrow["sequence"]);
						foreach ($questionsequence as $sidx => $seq)
						{
							$qsid = $questionsbysequence[$seq]["original_id"] ? $questionsbysequence[$seq]["original_id"] : $questionsbysequence[$seq]["question_fi"];
							$data->getParticipant($active_id)->addQuestion($qsid, $questionsbysequence[$seq]["question_fi"], $questionsbysequence[$seq]["points"], $sidx + 1, $seqrow["pass"]);
							$data->addQuestionTitle($qsid, $questionsbysequence[$seq]["title"]);
						}
					}
				}
			}
		}

		foreach (array_keys($data->getParticipants()) as $active_id)
		{
			$percentage = $data->getParticipant($active_id)->getReachedPointsInPercent();
			$mark = $this->mark_schema->getMatchingMark($percentage);
			if (is_object($mark))
			{
				$data->getParticipant($active_id)->setMark($mark->getShortName());
				$data->getParticipant($active_id)->setMarkOfficial($mark->getOfficialName());
				$data->getParticipant($active_id)->setPassed($mark->getPassed());
			}
			if ($this->ects_output)
			{
				// TODO: This is a performance killer!!!!
				$ects_mark = $this->getECTSGrade($data->getParticipant($active_id)->getReached(), $data->getParticipant($active_id)->getMaxPoints());
				$data->getParticipant($active_id)->setECTSMark($ects_mark);
			}
			$visitingTime =& $this->getVisitTimeOfParticipant($active_id);
			$data->getParticipant($active_id)->setFirstVisit($visitingTime["firstvisit"]);
			$data->getParticipant($active_id)->setLastVisit($visitingTime["lastvisit"]);
		}
		return $data;
	}
	
	function _getQuestionCountAndPointsForPassOfParticipant($active_id, $pass)
	{
		global $ilDB;
		$random = ilObjTest::_lookupRandomTestFromActiveId($active_id);
		if ($random)
		{
			$result = $ilDB->queryF("SELECT tst_test_rnd_qst.pass, COUNT(tst_test_rnd_qst.question_fi) qcount, " .
				"SUM(qpl_questions.points) qsum FROM tst_test_rnd_qst, qpl_questions " .
				"WHERE tst_test_rnd_qst.question_fi = qpl_questions.question_id AND " .
				"tst_test_rnd_qst.active_fi = %s and pass = %s GROUP BY tst_test_rnd_qst.active_fi, " .
				"tst_test_rnd_qst.pass, qcount, qsum",
				array('integer', 'integer'),
				array($active_id, $pass)
			);
		}
		else
		{
			$result = $ilDB->queryF("SELECT COUNT(tst_test_question.question_fi) qcount, " .
				"SUM(qpl_questions.points) qsum FROM tst_test_question, qpl_questions, tst_active " .
				"WHERE tst_test_question.question_fi = qpl_questions.question_id AND tst_test_question.test_fi = tst_active.test_fi AND " .
				"tst_active.active_id = %s GROUP BY tst_test_question.test_fi, qcount, qsum",
				array('integer'),
				array($active_id)
			);
		}
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
			return array("count" => $row["qcount"], "points" => $row["qsum"]);
		}
		else
		{
			return array("count" => 0, "points" => 0);
		}
	}

	function &getCompleteEvaluationData($withStatistics = TRUE, $filterby = "", $filtertext = "")
	{
		include_once "./Modules/Test/classes/class.ilTestEvaluationData.php";
		include_once "./Modules/Test/classes/class.ilTestEvaluationPassData.php";
		include_once "./Modules/Test/classes/class.ilTestEvaluationUserData.php";
		$data = $this->getUnfilteredEvaluationData();
		if ($withStatistics)
		{
			$data->calculateStatistics();
		}
		$data->setFilter($filterby, $filtertext);
		return $data;
	}
	
	/**
	* Creates an associated array with the results of all participants of a test
	*
	* @return array An associated array containing the results
	* @access public
	*/
	function &evalResultsOverview()
	{
		return $this->_evalResultsOverview($this->getTestId());
	}

	/**
	* Creates an associated array with the results of all participants of a test
	*
	* @return array An associated array containing the results
	* @access public
	*/
	function &_evalResultsOverview($test_id)
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT usr_data.usr_id, usr_data.firstname, usr_data.lastname, usr_data.title, usr_data.login, " .
			"tst_test_result.*, qpl_questions.original_id, qpl_questions.title questiontitle, " .
			"qpl_questions.points maxpoints " .
			"FROM tst_test_result, qpl_questions, tst_active " .
			"LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id " .
			"WHERE tst_active.active_id = tst_test_result.active_fi " .
			"AND qpl_questions.question_id = tst_test_result.question_fi " .
			"AND tst_active.test_fi = %s " .
			"ORDER BY active_id, pass, tstamp",
			array('integer'),
			array($test_id)
		);
		$overview = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			if (!array_key_exists($row["active_fi"], $overview))
			{
				$overview[$row["active_fi"]] = array();
				$overview[$row["active_fi"]]["firstname"] = $row["firstname"];
				$overview[$row["active_fi"]]["lastname"] = $row["lastname"];
				$overview[$row["active_fi"]]["title"] = $row["title"];
				$overview[$row["active_fi"]]["login"] = $row["login"];
				$overview[$row["active_fi"]]["usr_id"] = $row["usr_id"];
				$overview[$row["active_fi"]]["started"] = $row["started"];
				$overview[$row["active_fi"]]["finished"] = $row["finished"];
			}
			if (!array_key_exists($row["pass"], $overview[$row["active_fi"]]))
			{
				$overview[$row["active_fi"]][$row["pass"]] = array();
				$overview[$row["active_fi"]][$row["pass"]]["reached"] = 0;
				$overview[$row["active_fi"]][$row["pass"]]["maxpoints"] = $row["maxpoints"];
			}
			array_push($overview[$row["active_fi"]][$row["pass"]], $row);
			$overview[$row["active_fi"]][$row["pass"]]["reached"] += $row["points"];
		}
		return $overview;
	}

	/**
	* Creates an associated array with the results for a given participant of a test
	*
	* @param integer $active_id The active id of the participant
	* @return array An associated array containing the results
	* @access public
	*/
	function &evalResultsOverviewOfParticipant($active_id)
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT usr_data.usr_id, usr_data.firstname, usr_data.lastname, usr_data.title, usr_data.login, " .
			"tst_test_result.*, qpl_questions.original_id, qpl_questions.title questiontitle, " .
			"qpl_questions.points maxpoints " .
			"FROM tst_test_result, qpl_questions, tst_active " .
			"LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id " .
			"WHERE tst_active.active_id = tst_test_result.active_fi " .
			"AND qpl_questions.question_id = tst_test_result.question_fi " .
			"AND tst_active.test_fi = %s AND tst_active.active_id = %s" .
			"ORDER BY active_id, pass, tstamp",
			array('integer', 'integer'),
			array($this->getTestId(), $active_id)
		);
		$overview = array();
		while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			if (!array_key_exists($row["active_fi"], $overview))
			{
				$overview[$row["active_fi"]] = array();
				$overview[$row["active_fi"]]["firstname"] = $row["firstname"];
				$overview[$row["active_fi"]]["lastname"] = $row["lastname"];
				$overview[$row["active_fi"]]["title"] = $row["title"];
				$overview[$row["active_fi"]]["login"] = $row["login"];
				$overview[$row["active_fi"]]["usr_id"] = $row["usr_id"];
				$overview[$row["active_fi"]]["started"] = $row["started"];
				$overview[$row["active_fi"]]["finished"] = $row["finished"];
			}
			if (!array_key_exists($row["pass"], $overview[$row["active_fi"]]))
			{
				$overview[$row["active_fi"]][$row["pass"]] = array();
				$overview[$row["active_fi"]][$row["pass"]]["reached"] = 0;
				$overview[$row["active_fi"]][$row["pass"]]["maxpoints"] = $row["maxpoints"];
			}
			array_push($overview[$row["active_fi"]][$row["pass"]], $row);
			$overview[$row["active_fi"]][$row["pass"]]["reached"] += $row["points"];
		}
		return $overview;
	}

	/**
	* Builds a user name for the output depending on test type and existence of
	* the user
	*
	* @param int $user_id The database ID of the user
	* @param string $firstname The first name of the user
	* @param string $lastname The last name of the user
	* @param string $title The title of the user
	* @return string The output name of the user
	* @access public
	*/
	function buildName($user_id, $firstname, $lastname, $title)
	{
		$name = "";
		if (strlen($firstname.$lastname.$title) == 0)
		{
			$name = $this->lng->txt("deleted_user");
		}
		else
		{
			if ($user_id == ANONYMOUS_USER_ID)
			{
				$name = $lastname;
			}
			else
			{
				$name = trim($lastname . ", " . $firstname . " " .  $title);
			}
			if ($this->getAnonymity())
			{
				$name = $this->lng->txt("anonymous");
			}
		}
		return $name;
	}

	/**
	* Builds a user name for the output depending on test type and existence of
	* the user
	*
	* @param boolean $is_anonymous Indicates if it is an anonymized test or not
	* @param int $user_id The database ID of the user
	* @param string $firstname The first name of the user
	* @param string $lastname The last name of the user
	* @param string $title The title of the user
	* @return string The output name of the user
	* @access public
	*/
	function _buildName($is_anonymous, $user_id, $firstname, $lastname, $title)
	{
		global $lng;
		$name = "";
		if (strlen($firstname.$lastname.$title) == 0)
		{
			$name = $lng->txt("deleted_user");
		}
		else
		{
			if ($user_id == ANONYMOUS_USER_ID)
			{
				$name = $lastname;
			}
			else
			{
				$name = trim($lastname . ", " . $firstname . " " .  $title);
			}
			if ($is_anonymous)
			{
				$name = $lng->txt("anonymous");
			}
		}
		return $name;
	}

/**
* Returns the average processing time for all started tests
*
* @return integer The average processing time for all started tests
* @access public
*/
	function evalTotalStartedAverageTime()
	{
		global $ilDB;

		$q = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$times = array();
		while ($row = $result->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->started, $matches);
			$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->finished, $matches);
			$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			$times[$row->active_fi] += ($epoch_2 - $epoch_1);
		}
		$max_time = 0;
		$counter = 0;
		foreach ($times as $key => $value)
		{
			$max_time += $value;
			$counter++;
		}
		if ($counter)
		{
			$average_time = round($max_time / $counter);
		}
			else
		{
			$average_time = 0;
		}
		return $average_time;
	}

/**
* Returns the available question pools for the active user
*
* @return array The available question pools
* @access public
*/
	function &getAvailableQuestionpools($use_object_id = false, $equal_points = false, $could_be_offline = false, $show_path = FALSE, $with_questioncount = FALSE, $permission = "read")
	{
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		return ilObjQuestionPool::_getAvailableQuestionpools($use_object_id, $equal_points, $could_be_offline, $show_path, $with_questioncount, $permission);
	}

/**
* Returns the estimated working time for the test calculated from the working time of the contained questions
*
* @return array An associative array containing the working time. array["h"] = hours, array["m"] = minutes, array["s"] = seconds
* @access public
*/
	function getEstimatedWorkingTime()
	{
		$time_in_seconds = 0;
		foreach ($this->questions as $question_id)
		{
			$question =& ilObjTest::_instanciateQuestion($question_id);
			$est_time = $question->getEstimatedWorkingTime();
			$time_in_seconds += $est_time["h"] * 3600 + $est_time["m"] * 60 + $est_time["s"];
		}
		$hours = (int)($time_in_seconds / 3600)	;
		$time_in_seconds = $time_in_seconds - ($hours * 3600);
		$minutes = (int)($time_in_seconds / 60);
		$time_in_seconds = $time_in_seconds - ($minutes * 60);
		$result = array("h" => $hours, "m" => $minutes, "s" => $time_in_seconds);
		return $result;
	}

/**
* Returns a random selection of questions
*
* @param integer $nr_of_questions Number of questions to return
* @param integer $questionpool ID of questionpool to choose the questions from (0 = all available questionpools)
* @param boolean $user_obj_id Use the object id instead of the reference id when set to true
* @param array $qpls An array of questionpool id's if the random questions should only be chose from the contained questionpools
* @return array A random selection of questions
* @access public
*/
	function randomSelectQuestions($nr_of_questions, $questionpool, $use_obj_id = 0, $qpls = "", $pass = NULL)
	{
		global $rbacsystem;
		global $ilDB;

		// retrieve object id instead of ref id if necessary
		if (($questionpool != 0) && (!$use_obj_id)) $questionpool = ilObject::_lookupObjId($questionpool);

		// get original ids of all existing questions in the test
		$result = $ilDB->queryF("SELECT qpl_questions.original_id FROM qpl_questions, tst_test_question WHERE qpl_questions.question_id = tst_test_question.question_fi AND qpl_questions.owner > 0 AND tst_test_question.test_fi = %s",
			array("integer"),
			array($this->getTestId())
		);
		$original_ids = array();
		$paramtypes = array();
		$paramvalues = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			array_push($original_ids, $row['original_id']);
		}

		// get a list of all available questionpools
		if (($questionpool == 0) && (!is_array($qpls)))
		{
			include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
			$available_pools = array_keys(ilObjQuestionPool::_getAvailableQuestionpools($use_object_id = TRUE, $equal_points = FALSE, $could_be_offline = FALSE, $showPath = FALSE, $with_questioncount = FALSE, "read", ilObject::_lookupOwner($this->getId())));
			$available = "";
			$constraint_qpls = "";
			$phs = array();
			if (count($available_pools))
			{
				$paramtypes = $ilDB->addTypesToArray($paramtypes, 'integer', count($available_pools));
				$phs = $ilDB->addTypesToArray($phs, '%s', count($available_pools));
				$paramvalues = array_merge($paramvalues, $available_pools);
				$available = " AND obj_fi IN (" . implode(",", $phs) . ")";
			}
			else
			{
				return array();
			}
		}

		$result_array = array();
		if ($questionpool == 0)
		{
			if (is_array($qpls))
			{
				if (count($qpls) > 0)
				{
					$phs = array();
					$paramtypes = $ilDB->addTypesToArray($paramtypes, 'integer', count($qpls));
					$phs = $ilDB->addTypesToArray($phs, '%s', count($qpls));
					foreach ($qpls as $idx => $arr)
					{
						array_push($paramvalues, $arr["qpl"]);
					}
					$constraint_qpls = " AND obj_fi IN (" . implode(",", $phs) . ")";
				}
			}
		}

		if ($questionpool > 0)
		{
			array_push($paramtypes, 'integer');
			array_push($paramvalues, $questionpool);
		}
		array_push($paramtypes, 'integer');
		array_push($paramvalues, 0);
		array_push($paramtypes, 'text');
		array_push($paramvalues, "1");

		$original_clause = "";
		if (count($original_ids))
		{
			$phs = array();
			$paramtypes = $ilDB->addTypesToArray($paramtypes, 'integer', count($original_ids));
			$paramvalues = array_merge($paramvalues, $original_ids);
			$phs = $ilDB->addTypesToArray($phs, '%s', count($original_ids));
			$original_clause = " AND question_id NOT IN (" . implode(",", $phs) . ")";
		}

		if ($questionpool == 0)
		{
			$result = $ilDB->queryF("SELECT question_id FROM qpl_questions WHERE ISNULL(original_id) $available $constraint_qpls AND owner > %s AND complete = %s $original_clause",
				$paramtypes,
				$paramvalues
			);
		}
		else
		{
			$result = $ilDB->queryF("SELECT question_id FROM qpl_questions WHERE ISNULL(original_id) AND obj_fi = %s AND owner > %s AND complete = %s $original_clause",
				$paramtypes,
				$paramvalues
			);
		}
		$found_ids = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			array_push($found_ids, $row['question_id']);
		}
		$nr_of_questions = ($nr_of_questions > count($found_ids)) ? count($found_ids) : $nr_of_questions;
		if ($nr_of_questions == 0) return array();
		$rand_keys = array_rand($found_ids, $nr_of_questions);
		$result = array();
		foreach ($rand_keys as $key)
		{
			$result[$found_ids[$key]] = $found_ids[$key];
		}
		return $result;
	}

/**
* Returns the image path for web accessable images of a test
* The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_TEST/images
*
* @access public
*/
	function getImagePath()
	{
		return CLIENT_WEB_DIR . "/assessment/" . $this->getId() . "/images/";
	}

/**
* Returns the web image path for web accessable images of a test
* The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_TEST/images
*
* @access public
*/
	function getImagePathWeb()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/" . $this->getId() . "/images/";
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
	}

/**
* Creates a question GUI instance of a given question type
*
* @param integer $question_type The question type of the question
* @param integer $question_id The question id of the question, if available
* @return object The question GUI instance
* @access	public
*/
  function &createQuestionGUI($question_type, $question_id = -1)
	{
		if ((!$question_type) and ($question_id > 0))
		{
			$question_type = $this->getQuestionType($question_id);
		}
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		assQuestion::_includeClass($question_type, 1);
		$question_type_gui = $question_type . "GUI";
		$question = new $question_type_gui();
		if ($question_id > 0)
		{
			$question->object->loadFromDb($question_id);
		}
		return $question;
  }

/**
* Creates an instance of a question with a given question id
*
* @param integer $question_id The question id
* @return object The question instance
* @access public
*/
  function &_instanciateQuestion($question_id)
	{
		if (strcmp($question_id, "") != 0)
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			return assQuestion::_instanciateQuestion($question_id);
		}
  }

/**
* Move questions to another position
*
* @param array $move_questions An array with the question id's of the questions to move
* @param integer $target_index The question id of the target position
* @param integer $insert_mode 0, if insert before the target position, 1 if insert after the target position
* @access public
*/
	function moveQuestions($move_questions, $target_index, $insert_mode)
	{
		$this->questions = array_values($this->questions);
		$array_pos = array_search($target_index, $this->questions);
		if ($insert_mode == 0)
		{
			$part1 = array_slice($this->questions, 0, $array_pos);
			$part2 = array_slice($this->questions, $array_pos);
		}
		else if ($insert_mode == 1)
		{
			$part1 = array_slice($this->questions, 0, $array_pos + 1);
			$part2 = array_slice($this->questions, $array_pos + 1);
		}
		foreach ($move_questions as $question_id)
		{
			if (!(array_search($question_id, $part1) === FALSE))
			{
				unset($part1[array_search($question_id, $part1)]);
			}
			if (!(array_search($question_id, $part2) === FALSE))
			{
				unset($part2[array_search($question_id, $part2)]);
			}
		}
		$part1 = array_values($part1);
		$part2 = array_values($part2);
		$new_array = array_values(array_merge($part1, $move_questions, $part2));
		$this->questions = array();
		$counter = 1;
		foreach ($new_array as $question_id)
		{
			$this->questions[$counter] = $question_id;
			$counter++;
		}
		$this->saveQuestionsToDb();
	}


/**
* Returns true if the starting time of a test is reached
* A starting time is not available for self assessment tests
*
* @return boolean true if the starting time is reached, otherwise false
* @access public
*/
	function startingTimeReached()
	{
		if ($this->getStartingTime())
		{
			if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getStartingTime(), $matches))
			{
				$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
				$now = mktime();
				if ($now < $epoch_time)
				{
					// starting time not reached
					return false;
				}
			}
		}
		return true;
	}

/**
* Returns true if the ending time of a test is reached
* An ending time is not available for self assessment tests
*
* @return boolean true if the ending time is reached, otherwise false
* @access public
*/
	function endingTimeReached()
	{
		if ($this->getEndingTime())
		{
			if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getEndingTime(), $matches))
			{
				$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
				$now = mktime();
				if ($now > $epoch_time)
				{
					// ending time reached
					return true;
				}
			}
		}
		return false;
	}

/**
* Calculates the data for the output of the questionpool
*
* @access public
*/
	function getQuestionsTable($sort, $sortorder, $textfilter, $startrow = 0, $completeonly = 0, $filter_question_type = "", $filter_questionpool = "")
	{
		global $ilUser;
		global $ilDB;

		$where = "";
		foreach ($textfilter as $sel_filter_type => $filter_text)
		{
			if (strlen($filter_text) > 0) 
			{
				switch($sel_filter_type) 
				{
					case "title":
						$where .= " AND " . $ilDB->like('qpl_questions.title', 'text', "%" . $filter_text . "%");
						break;
					case "comment":
						$where .= " AND " . $ilDB->like('qpl_questions.description', 'text', "%" . $filter_text . "%");
						break;
					case "author":
						$where .= " AND " . $ilDB->like('qpl_questions.author', 'text', "%" . $filter_text . "%");
						break;
					case "qpl":
						$where .= " AND " . $ilDB->like('object_data.title', 'text', "%" . $filter_text . "%");
						break;
				}
			}
		}

		if ($filter_question_type && (strcmp($filter_question_type, "all") != 0))
		{
			$where .= " AND qpl_qst_type.type_tag = " . $ilDB->quote($filter_question_type, 'text');
		}

		if ($filter_questionpool && (strcmp($filter_questionpool, "all") != 0))
		{
			$where .= " AND qpl_questions.obj_fi = " . $ilDB->quote($filter_questionpool, 'integer');
		}

		// build sort order for sql query
		$order = "";
		$images = array();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		switch($sort) 
		{
			case "title":
				$order = " ORDER BY qpl_questions.title $sortorder";
				$images["title"] = " <img src=\"" . ilUtil::getImagePath(strtolower($sortorder) . "_order.gif") . "\" alt=\"" . $this->lng->txt(strtolower($sortorder) . "ending_order")."\" />";
				break;
			case "comment":
				$order = " ORDER BY description $sortorder";
				$images["comment"] = " <img src=\"" . ilUtil::getImagePath(strtolower($sortorder) . "_order.gif") . "\" alt=\"" . $this->lng->txt(strtolower($sortorder) . "ending_order")."\" />";
				break;
			case "type":
				$order = " ORDER BY question_type_id $sortorder";
				$images["type"] = " <img src=\"" . ilUtil::getImagePath(strtolower($sortorder) . "_order.gif") . "\" alt=\"" . $this->lng->txt(strtolower($sortorder) . "ending_order")."\" />";
				break;
			case "author":
				$order = " ORDER BY author $sortorder";
				$images["author"] = " <img src=\"" . ilUtil::getImagePath(strtolower($sortorder) . "_order.gif") . "\" alt=\"" . $this->lng->txt(strtolower($sortorder) . "ending_order")."\" />";
				break;
			case "created":
				$order = " ORDER BY created $sortorder";
				$images["created"] = " <img src=\"" . ilUtil::getImagePath(strtolower($sortorder) . "_order.gif") . "\" alt=\"" . $this->lng->txt(strtolower($sortorder) . "ending_order")."\" />";
				break;
			case "updated":
				$order = " ORDER BY tstamp $sortorder";
				$images["updated"] = " <img src=\"" . ilUtil::getImagePath(strtolower($sortorder) . "_order.gif") . "\" alt=\"" . $this->lng->txt(strtolower($sortorder) . "ending_order")."\" />";
				break;
			case "qpl":
				$order = " ORDER BY UPPER(object_data.title) $sortorder";
				$images["qpl"] = " <img src=\"" . ilUtil::getImagePath(strtolower($sortorder) . "_order.gif") . "\" alt=\"" . $this->lng->txt(strtolower($sortorder) . "ending_order")."\" />";
				break;
		}
		$maxentries = $ilUser->prefs["hits_per_page"];
		if ($maxentries < 1)
		{
			$maxentries = 9999;
		}
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		$available_pools = array_keys(ilObjQuestionPool::_getAvailableQuestionpools($use_object_id = TRUE, $equal_points = FALSE, $could_be_offline = FALSE, $showPath = FALSE, $with_questioncount = FALSE));
		$available = "";
		if (count($available_pools))
		{
			$available = " AND " . $ilDB->in('qpl_questions.obj_fi', $available_pools, false, 'integer');
		}
		else
		{
			return array();
		}
		if ($completeonly)
		{
			$available .= " AND qpl_questions.complete = " . $ilDB->quote("1", 'text');
		}

		// get all questions in the test
		$result = $ilDB->queryF("SELECT qpl_questions.original_id, qpl_questions.tstamp FROM qpl_questions, tst_test_question WHERE qpl_questions.question_id = tst_test_question.question_fi AND tst_test_question.test_fi = %s",
			array('integer'),
			array($this->getTestId())
		);
		$original_ids = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			if (strcmp($row[0], "") != 0)
			{
				array_push($original_ids, $row[0]);
			}
		}
		$original_clause = " ISNULL(qpl_questions.original_id)";
		if (count($original_ids))
		{
			$original_clause = " ISNULL(qpl_questions.original_id) AND " . $ilDB->in('qpl_questions.question_id',  $original_ids, true, 'integer');
		}

		$query_result = $ilDB->query("SELECT qpl_questions.question_id, qpl_questions.tstamp FROM qpl_questions, qpl_qst_type, object_data WHERE $original_clause$available AND object_data.obj_id = qpl_questions.obj_fi AND qpl_questions.owner > 0 AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id $where$order$limit");
		$max = $query_result->numRows();
		if ($startrow > $max -1)
		{
			$startrow = $max - ($max % $maxentries);
		}
		else if ($startrow < 0)
		{
			$startrow = 0;
		}
		$ilDB->setLimit($maxentries, $startrow);
		$query_result = $ilDB->query("SELECT qpl_questions.*, qpl_questions.tstamp, qpl_qst_type.type_tag, qpl_qst_type.plugin FROM qpl_questions, qpl_qst_type, object_data WHERE $original_clause $available AND object_data.obj_id = qpl_questions.obj_fi AND qpl_questions.owner > 0 AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id $where$order");
		$rows = array();
		if ($query_result->numRows())
		{
			while ($row = $ilDB->fetchAssoc($query_result))
			{
				if ($row["plugin"])
				{
					if ($this->isPluginActive($row["type_tag"]))
					{
						array_push($rows, $row);
					}
				}
				else
				{
					array_push($rows, $row);
				}
			}
		}
		$nextrow = $startrow + $maxentries;
		if ($nextrow > $max - 1)
		{
			$nextrow = $startrow;
		}
		$prevrow = $startrow - $maxentries;
		if ($prevrow < 0)
		{
			$prevrow = 0;
		}
		return array(
			"rows" => $rows,
			"images" => $images,
			"startrow" => $startrow,
			"nextrow" => $nextrow,
			"prevrow" => $prevrow,
			"step" => $maxentries,
			"rowcount" => $max
		);
	}

	/**
	* Receives parameters from a QTI parser and creates a valid ILIAS test object
	*
	* @param object $assessment The QTI assessment object
	* @access public
	*/
	function fromXML(&$assessment)
	{
		unset($_SESSION["import_mob_xhtml"]);

		$this->setDescription($assessment->getComment());
		$this->setTitle($assessment->getTitle());

		foreach ($assessment->objectives as $objectives)
		{
			foreach ($objectives->materials as $material)
			{
				$this->setIntroduction($this->QTIMaterialToString($material));
			}
		}
		if ($assessment->getPresentationMaterial())
		{
			$this->setFinalStatement($this->QTIMaterialToString($assessment->getPresentationMaterial()->getMaterial(0)));
		}
		
		foreach ($assessment->assessmentcontrol as $assessmentcontrol)
		{
			switch ($assessmentcontrol->getSolutionswitch())
			{
				case "Yes":
					$this->setInstantFeedbackSolution(1);
					break;
				default:
					$this->setInstantFeedbackSolution(0);
					break;
			}
		}

		foreach ($assessment->qtimetadata as $metadata)
		{
			switch ($metadata["label"])
			{
				case "test_type":
					// for old tests with a test type
					$type = $metadata["entry"];
					switch ($type)
					{
						case 1:
							// assessment
							$this->setAnonymity(1);
							break;
						case 2:
							// self assessment
							break;
						case 4:
							// online exam
							$this->setFixedParticipants(1);
							$this->setListOfQuestionsSettings(7);
							$this->setShowSolutionPrintview(1);
							break;
						case 5:
							// varying random test
							break;
					}
					break;
				case "sequence_settings":
					$this->setSequenceSettings($metadata["entry"]);
					break;
				case "author":
					$this->setAuthor($metadata["entry"]);
					break;
				case "nr_of_tries":
					$this->setNrOfTries($metadata["entry"]);
					break;
				case "kiosk":
					$this->setKiosk($metadata["entry"]);
					break;
				case "showfinalstatement":
					$this->setShowFinalStatement($metadata["entry"]);
					break;
				case "showinfo":
					$this->setShowInfo($metadata["entry"]);
					break;
				case "forcejs":
					$this->setForceJS($metadata["entry"]);
					break;
				case "customstyle":
					$this->setCustomStyle($metadata["entry"]);
					break;
				case "hide_previous_results":
					if ($metadata["entry"] == 0)
					{
						$this->setUsePreviousAnswers(1);
					}
					else
					{
						$this->setUsePreviousAnswers(0);
					}
					break;
				case "use_previous_answers":
					$this->setUsePreviousAnswers($metadata["entry"]);
					break;
				case "answer_feedback":
					$this->setAnswerFeedback($metadata["entry"]);
					break;
				case "hide_title_points":
					$this->setTitleOutput($metadata["entry"]);
					break;
				case "title_output":
					$this->setTitleOutput($metadata["entry"]);
					break;
				case "random_test":
					$this->setRandomTest($metadata["entry"]);
					break;
				case "random_question_count":
					$this->setRandomQuestionCount($metadata["entry"]);
					break;
				case "results_presentation":
					$this->setResultsPresentation($metadata["entry"]);
					break;
				case "reset_processing_time":
					$this->setResetProcessingTime($metadata["entry"]);
					break;
				case "instant_verification":
					$this->setInstantFeedbackSolution($metadata["entry"]);
					break;
				case "answer_feedback_points":
					$this->setAnswerFeedbackPoints($metadata["entry"]);
					break;
				case "anonymity":
					$this->setAnonymity($metadata["entry"]);
					break;
				case "show_cancel":
					$this->setShowCancel($metadata["entry"]);
					break;
				case "show_marker":
					$this->setShowMarker($metadata["entry"]);
					break;
				case "fixed_participants":
					$this->setFixedParticipants($metadata["entry"]);
					break;
				case "score_reporting":
					$this->setScoreReporting($metadata["entry"]);
					break;
				case "shuffle_questions":
					$this->setShuffleQuestions($metadata["entry"]);
					break;
				case "count_system":
					$this->setCountSystem($metadata["entry"]);
					break;
				case "mc_scoring":
					$this->setMCScoring($metadata["entry"]);
					break;
				case "score_cutting":
					$this->setScoreCutting($metadata["entry"]);
					break;
				case "password":
					$this->setPassword($metadata["entry"]);
					break;
				case "allowedUsers":
					$this->setAllowedUsers($metadata["entry"]);
					break;
				case "allowedUsersTimeGap":
					$this->setAllowedUsersTimeGap($metadata["entry"]);
					break;
				case "pass_scoring":
					$this->setPassScoring($metadata["entry"]);
					break;
				case "show_summary":
					$this->setListOfQuestionsSettings($metadata["entry"]);
					break;
				case "reporting_date":
					$iso8601period = $metadata["entry"];
					if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $iso8601period, $matches))
					{
						$this->setReportingDate(sprintf("%02d%02d%02d%02d%02d%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
					}
					break;
				case "starting_time":
					$iso8601period = $metadata["entry"];
					if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $iso8601period, $matches))
					{
						$this->setStartingTime(sprintf("%02d%02d%02d%02d%02d%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
					}
					break;
				case "ending_time":
					$iso8601period = $metadata["entry"];
					if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $iso8601period, $matches))
					{
						$this->setEndingTime(sprintf("%02d%02d%02d%02d%02d%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
					}
					break;
			}
			if (preg_match("/mark_step_\d+/", $metadata["label"]))
			{
				$xmlmark = $metadata["entry"];
				preg_match("/<short>(.*?)<\/short>/", $xmlmark, $matches);
				$mark_short = $matches[1];
				preg_match("/<official>(.*?)<\/official>/", $xmlmark, $matches);
				$mark_official = $matches[1];
				preg_match("/<percentage>(.*?)<\/percentage>/", $xmlmark, $matches);
				$mark_percentage = $matches[1];
				preg_match("/<passed>(.*?)<\/passed>/", $xmlmark, $matches);
				$mark_passed = $matches[1];
				$this->mark_schema->addMarkStep($mark_short, $mark_official, $mark_percentage, $mark_passed);
			}
		}
		// handle the import of media objects in XHTML code
		if (is_array($_SESSION["import_mob_xhtml"]))
		{
			include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
			include_once "./Services/RTE/classes/class.ilRTE.php";
			include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
			foreach ($_SESSION["import_mob_xhtml"] as $mob)
			{
				$importfile = $this->getImportDirectory() . "/" . $_SESSION["tst_import_subdir"] . "/" . $mob["uri"];
				if (file_exists($importfile))
				{
					$media_object =& ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, FALSE);
					ilObjMediaObject::_saveUsage($media_object->getId(), "tst:html", $this->getId());
					$this->setIntroduction(ilRTE::_replaceMediaObjectImageSrc(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->getIntroduction()), 1));
					$this->setFinalStatement(ilRTE::_replaceMediaObjectImageSrc(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->getFinalStatement()), 1));
				}
				else
				{
					global $ilLog;
					$ilLog->write("Error: Could not open XHTML mob file for test introduction during test import. File $importfile does not exist!");
				}
			}
			$this->saveToDb();
		}
	}

	/**
	* Returns a QTI xml representation of the test
	*
	* @return string The QTI xml representation of the test
	* @access public
	*/
	function toXML()
	{
		include_once("./classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;
		// set xml header
		$a_xml_writer->xmlHeader();
		$a_xml_writer->xmlSetDtdDef("<!DOCTYPE questestinterop SYSTEM \"ims_qtiasiv1p2p1.dtd\">");
		$a_xml_writer->xmlStartTag("questestinterop");

		$attrs = array(
			"ident" => "il_".IL_INST_ID."_tst_".$this->getTestId(),
			"title" => $this->getTitle()
		);
		$a_xml_writer->xmlStartTag("assessment", $attrs);
		// add qti comment
		$a_xml_writer->xmlElement("qticomment", NULL, $this->getDescription());

		// add qti duration
		if ($this->enable_processing_time)
		{
			preg_match("/(\d+):(\d+):(\d+)/", $this->processing_time, $matches);
			$a_xml_writer->xmlElement("duration", NULL, sprintf("P0Y0M0DT%dH%dM%dS", $matches[1], $matches[2], $matches[3]));
		}

		// add the rest of the preferences in qtimetadata tags, because there is no correspondent definition in QTI
		$a_xml_writer->xmlStartTag("qtimetadata");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "ILIAS_VERSION");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->ilias->getSetting("ilias_version"));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// anonymity
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "anonymity");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getAnonymity()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// random test
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "random_test");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->isRandomTest()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// sequence settings
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "sequence_settings");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getSequenceSettings());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// author
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "author");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getAuthor());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// reset processing time
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "reset_processing_time");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getResetProcessingTime());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// count system
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "count_system");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getCountSystem());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// multiple choice scoring
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "mc_scoring");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getMCScoring());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// multiple choice scoring
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "score_cutting");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getScoreCutting());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// multiple choice scoring
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "password");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getPassword());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// allowed users
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "allowedUsers");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getAllowedUsers());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// allowed users time gap
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "allowedUsersTimeGap");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getAllowedUsersTimeGap());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// pass scoring
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "pass_scoring");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getPassScoring());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// score reporting date
		if ($this->getReportingDate())
		{
			$a_xml_writer->xmlStartTag("qtimetadatafield");
			$a_xml_writer->xmlElement("fieldlabel", NULL, "reporting_date");
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->reporting_date, $matches);
			$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("P%dY%dM%dDT%dH%dM%dS", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
			$a_xml_writer->xmlEndTag("qtimetadatafield");
		}
		// number of tries
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "nr_of_tries");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getNrOfTries()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// kiosk
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "kiosk");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getKiosk()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// use previous answers
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "use_previous_answers");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getUsePreviousAnswers());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// hide title points
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "title_output");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getTitleOutput()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// random question count
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "random_question_count");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getRandomQuestionCount()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// results presentation
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "results_presentation");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getResultsPresentation()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// solution details
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "show_summary");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getListOfQuestionsSettings()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// solution details
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "score_reporting");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getScoreReporting()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// solution details
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "instant_verification");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getInstantFeedbackSolution()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// answer specific feedback
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "answer_feedback");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getAnswerFeedback()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// answer specific feedback of reached points
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "answer_feedback_points");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getAnswerFeedbackPoints()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// show cancel
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "show_cancel");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getShowCancel()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// show marker
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "show_marker");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getShowMarker()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// fixed participants
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "fixed_participants");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getFixedParticipants()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// show final statement
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "showfinalstatement");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", (($this->getShowFinalStatement()) ? "1" : "0")));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// show introduction only
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "showinfo");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", (($this->getShowInfo()) ? "1" : "0")));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// force JavaScript
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "forcejs");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", (($this->getForceJS()) ? "1" : "0")));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// custom style
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "customstyle");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getCustomStyle());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// shuffle questions
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "shuffle_questions");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getShuffleQuestions()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// starting time
		if ($this->getStartingTime())
		{
			$a_xml_writer->xmlStartTag("qtimetadatafield");
			$a_xml_writer->xmlElement("fieldlabel", NULL, "starting_time");
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->starting_time, $matches);
			$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("P%dY%dM%dDT%dH%dM%dS", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
			$a_xml_writer->xmlEndTag("qtimetadatafield");
		}
		// ending time
		if ($this->getEndingTime())
		{
			$a_xml_writer->xmlStartTag("qtimetadatafield");
			$a_xml_writer->xmlElement("fieldlabel", NULL, "ending_time");
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->ending_time, $matches);
			$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("P%dY%dM%dDT%dH%dM%dS", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
			$a_xml_writer->xmlEndTag("qtimetadatafield");
		}
		foreach ($this->mark_schema->mark_steps as $index => $mark)
		{
			// mark steps
			$a_xml_writer->xmlStartTag("qtimetadatafield");
			$a_xml_writer->xmlElement("fieldlabel", NULL, "mark_step_$index");
			$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("<short>%s</short><official>%s</official><percentage>%.2f</percentage><passed>%d</passed>", $mark->getShortName(), $mark->getOfficialName(), $mark->getMinimumLevel(), $mark->getPassed()));
			$a_xml_writer->xmlEndTag("qtimetadatafield");
		}
		$a_xml_writer->xmlEndTag("qtimetadata");

		// add qti objectives
		$a_xml_writer->xmlStartTag("objectives");
		$this->addQTIMaterial($a_xml_writer, $this->getIntroduction());
		$a_xml_writer->xmlEndTag("objectives");

		// add qti assessmentcontrol
		if ($this->getInstantFeedbackSolution() == 1)
		{
			$attrs = array(
				"solutionswitch" => "Yes"
			);
		}
		else
		{
			$attrs = NULL;
		}
		$a_xml_writer->xmlElement("assessmentcontrol", $attrs, NULL);

		if (strlen($this->getFinalStatement()))
		{
			// add qti presentation_material
			$a_xml_writer->xmlStartTag("presentation_material");
			$a_xml_writer->xmlStartTag("flow_mat");
			$this->addQTIMaterial($a_xml_writer, $this->getFinalStatement());
			$a_xml_writer->xmlEndTag("flow_mat");
			$a_xml_writer->xmlEndTag("presentation_material");
		}
		
		$attrs = array(
			"ident" => "1"
		);
		$a_xml_writer->xmlElement("section", $attrs, NULL);
		$a_xml_writer->xmlEndTag("assessment");
		$a_xml_writer->xmlEndTag("questestinterop");

		$xml = $a_xml_writer->xmlDumpMem(FALSE);

		foreach ($this->questions as $question_id)
		{
			$question =& ilObjTest::_instanciateQuestion($question_id);
			$qti_question = $question->toXML(false);
			$qti_question = preg_replace("/<questestinterop>/", "", $qti_question);
			$qti_question = preg_replace("/<\/questestinterop>/", "", $qti_question);
			if (strpos($xml, "</section>") !== false)
			{
				$xml = str_replace("</section>", "$qti_question</section>", $xml);
			}
			else
			{
				$xml = str_replace("<section ident=\"1\"/>", "<section ident=\"1\">\n$qti_question</section>", $xml);
			}
		}
		return $xml;
	}

	/**
	* export pages of test to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportPagesXML(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
	{
		global $ilBench;

		$this->mob_ids = array();
		$this->file_ids = array();

		$attrs = array();
		$attrs["Type"] = "Test";
		$a_xml_writer->xmlStartTag("ContentObject", $attrs);

		// MetaData
		$this->exportXMLMetaData($a_xml_writer);

		// PageObjects
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Page Objects");
		$ilBench->start("ContentObjectExport", "exportPageObjects");
		$this->exportXMLPageObjects($a_xml_writer, $a_inst, $expLog);
		$ilBench->stop("ContentObjectExport", "exportPageObjects");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Page Objects");

		// MediaObjects
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Media Objects");
		$ilBench->start("ContentObjectExport", "exportMediaObjects");
		$this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);
		$ilBench->stop("ContentObjectExport", "exportMediaObjects");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Media Objects");

		// FileItems
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export File Items");
		$ilBench->start("ContentObjectExport", "exportFileItems");
		$this->exportFileItems($a_target_dir, $expLog);
		$ilBench->stop("ContentObjectExport", "exportFileItems");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export File Items");

		$a_xml_writer->xmlEndTag("ContentObject");
	}

	/**
	* export content objects meta data to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLMetaData(&$a_xml_writer)
	{
		include_once "./Services/MetaData/classes/class.ilMD2XML.php";
		$md2xml = new ilMD2XML($this->getId(), 0, $this->getType());
		$md2xml->setExportMode(true);
		$md2xml->startExport();
		$a_xml_writer->appendXML($md2xml->getXML());
	}

/**
* Returns the installation id for a given identifier
*
* @access	private
*/
	function modifyExportIdentifier($a_tag, $a_param, $a_value)
	{
		if ($a_tag == "Identifier" && $a_param == "Entry")
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			$a_value = ilUtil::insertInstIntoID($a_value);
		}

		return $a_value;
	}


	/**
	* export page objects to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLPageObjects(&$a_xml_writer, $a_inst, &$expLog)
	{
		global $ilBench;

		include_once "./Modules/LearningModule/classes/class.ilLMPageObject.php";

		foreach ($this->questions as $question_id)
		{
			$ilBench->start("ContentObjectExport", "exportPageObject");
			$expLog->write(date("[y-m-d H:i:s] ")."Page Object ".$question_id);

			$attrs = array();
			$a_xml_writer->xmlStartTag("PageObject", $attrs);


			// export xml to writer object
			$ilBench->start("ContentObjectExport", "exportPageObject_XML");
			$page_object = new ilPageObject("qpl", $question_id);
			$page_object->buildDom();
			$page_object->insertInstIntoIDs($a_inst);
			$mob_ids = $page_object->collectMediaObjects(false);
			$file_ids = $page_object->collectFileItems();
			$xml = $page_object->getXMLFromDom(false, false, false, "", true);
			$xml = str_replace("&","&amp;", $xml);
			$a_xml_writer->appendXML($xml);
			$page_object->freeDom();
			unset ($page_object);

			$ilBench->stop("ContentObjectExport", "exportPageObject_XML");

			// collect media objects
			$ilBench->start("ContentObjectExport", "exportPageObject_CollectMedia");
			//$mob_ids = $page_obj->getMediaObjectIDs();
			foreach($mob_ids as $mob_id)
			{
				$this->mob_ids[$mob_id] = $mob_id;
			}
			$ilBench->stop("ContentObjectExport", "exportPageObject_CollectMedia");

			// collect all file items
			$ilBench->start("ContentObjectExport", "exportPageObject_CollectFileItems");
			//$file_ids = $page_obj->getFileItemIds();
			foreach($file_ids as $file_id)
			{
				$this->file_ids[$file_id] = $file_id;
			}
			$ilBench->stop("ContentObjectExport", "exportPageObject_CollectFileItems");

			$a_xml_writer->xmlEndTag("PageObject");
			//unset($page_obj);

			$ilBench->stop("ContentObjectExport", "exportPageObject");


		}
	}

	/**
	* export media objects to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLMediaObjects(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
	{
		include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";

		foreach ($this->mob_ids as $mob_id)
		{
			$expLog->write(date("[y-m-d H:i:s] ")."Media Object ".$mob_id);
			$media_obj = new ilObjMediaObject($mob_id);
			$media_obj->exportXML($a_xml_writer, $a_inst);
			$media_obj->exportFiles($a_target_dir);
			unset($media_obj);
		}
	}

	/**
	* export files of file itmes
	*
	*/
	function exportFileItems($a_target_dir, &$expLog)
	{
		include_once "./Modules/File/classes/class.ilObjFile.php";

		foreach ($this->file_ids as $file_id)
		{
			$expLog->write(date("[y-m-d H:i:s] ")."File Item ".$file_id);
			$file_obj = new ilObjFile($file_id, false);
			$file_obj->export($a_target_dir);
			unset($file_obj);
		}
	}

	/**
	* get array of (two) new created questions for
	* import id
	*/
	function getImportMapping()
	{
		if (!is_array($this->import_mapping))
		{
			return array();
		}
		else
		{
			return $this->import_mapping;
		}
	}

	/**
	* Returns the ECTS grade for a number of reached points
	*
	* @param double $reached_points The points reached in the test
	* @param double $max_points The maximum number of points for the test
	* @return string The ECTS grade short description
	* @access public
	*/
	function getECTSGrade($reached_points, $max_points)
	{
		$passed_array =& $this->getTotalPointsPassedArray();
		return ilObjTest::_getECTSGrade($passed_array, $reached_points, $max_points, $this->ects_grades["A"], $this->ects_grades["B"], $this->ects_grades["C"], $this->ects_grades["D"], $this->ects_grades["E"], $this->ects_fx);
	}

	/**
	* Returns the ECTS grade for a number of reached points
	*
	* @param double $reached_points The points reached in the test
	* @param double $max_points The maximum number of points for the test
	* @return string The ECTS grade short description
	* @access public
	*/
	function _getECTSGrade($points_passed, $reached_points, $max_points, $a, $b, $c, $d, $e, $fx)
	{
		include_once "./classes/class.ilStatistics.php";
		// calculate the median
		$passed_statistics = new ilStatistics();
		$passed_statistics->setData($points_passed);
		$ects_percentiles = array
		(
			"A" => $passed_statistics->quantile($a),
			"B" => $passed_statistics->quantile($b),
			"C" => $passed_statistics->quantile($c),
			"D" => $passed_statistics->quantile($d),
			"E" => $passed_statistics->quantile($e)
		);
		if (count($points_passed) && ($reached_points >= $ects_percentiles["A"]))
		{
			return "A";
		}
		else if (count($points_passed) && ($reached_points >= $ects_percentiles["B"]))
		{
			return "B";
		}
		else if (count($points_passed) && ($reached_points >= $ects_percentiles["C"]))
		{
			return "C";
		}
		else if (count($points_passed) && ($reached_points >= $ects_percentiles["D"]))
		{
			return "D";
		}
		else if (count($points_passed) && ($reached_points >= $ects_percentiles["E"]))
		{
			return "E";
		}
		else if (strcmp($fx, "") != 0)
		{
			if ($max_points > 0)
			{
				$percentage = ($reached_points / $max_points) * 100.0;
				if ($percentage < 0) $percentage = 0.0;
			}
			else
			{
				$percentage = 0.0;
			}
			if ($percentage >= $fx)
			{
				return "FX";
			}
			else
			{
				return "F";
			}
		}
		else
		{
			return "F";
		}
	}

	function checkMarks()
	{
		return $this->mark_schema->checkMarks();
	}
	
	function getMarkSchema()
	{
		return $this->mark_schema;
	}

/**
* Sets the authors name of the ilObjTest object
*
* @param string $author A string containing the name of the test author
* @access public
* @see $author
*/
  function setAuthor($author = "")
	{
    $this->author = $author;
  }

/**
* Saves an authors name into the lifecycle metadata if no lifecycle metadata exists
* This will only be called for conversion of "old" tests where the author hasn't been
* stored in the lifecycle metadata
*
* @param string $a_author A string containing the name of the test author
* @access private
* @see $author
*/
	function saveAuthorToMetadata($a_author = "")
	{
		$md =& new ilMD($this->getId(), 0, $this->getType());
		$md_life =& $md->getLifecycle();
		if (!$md_life)
		{
			if (strlen($a_author) == 0)
			{
				global $ilUser;
				$a_author = $ilUser->getFullname();
			}

			$md_life =& $md->addLifecycle();
			$md_life->save();
			$con =& $md_life->addContribute();
			$con->setRole("Author");
			$con->save();
			$ent =& $con->addEntity();
			$ent->setEntity($a_author);
			$ent->save();
		}
	}

/**
* Create meta data entry
*
* @access public
*/
	function createMetaData()
	{
		parent::createMetaData();
		$this->saveAuthorToMetadata();
	}

/**
* Gets the authors name of the ilObjTest object
*
* @return string The string containing the name of the test author
* @access public
* @see $author
*/
  function getAuthor()
	{
		$author = array();
		include_once "./Services/MetaData/classes/class.ilMD.php";
		$md =& new ilMD($this->getId(), 0, $this->getType());
		$md_life =& $md->getLifecycle();
		if ($md_life)
		{
			$ids =& $md_life->getContributeIds();
			foreach ($ids as $id)
			{
				$md_cont =& $md_life->getContribute($id);
				if (strcmp($md_cont->getRole(), "Author") == 0)
				{
					$entids =& $md_cont->getEntityIds();
					foreach ($entids as $entid)
					{
						$md_ent =& $md_cont->getEntity($entid);
						array_push($author, $md_ent->getEntity());
					}
				}
			}
		}
		return join($author, ",");
  }

/**
* Gets the authors name of the ilObjTest object
*
* @return string The string containing the name of the test author
* @access public
* @see $author
*/
  function _lookupAuthor($obj_id)
	{
		$author = array();
		include_once "./Services/MetaData/classes/class.ilMD.php";
		$md =& new ilMD($obj_id, 0, "tst");
		$md_life =& $md->getLifecycle();
		if ($md_life)
		{
			$ids =& $md_life->getContributeIds();
			foreach ($ids as $id)
			{
				$md_cont =& $md_life->getContribute($id);
				if (strcmp($md_cont->getRole(), "Author") == 0)
				{
					$entids =& $md_cont->getEntityIds();
					foreach ($entids as $entid)
					{
						$md_ent =& $md_cont->getEntity($entid);
						array_push($author, $md_ent->getEntity());
					}
				}
			}
		}
		return join($author, ",");
  }

/**
* Returns the available tests for the active user
*
* @return array The available tests
* @access public
*/
	function &_getAvailableTests($use_object_id = FALSE)
	{
		global $ilUser;
		global $ilDB;

		$result_array = array();
		$tests = ilUtil::_getObjectsByOperations("tst","write", $ilUser->getId(), -1);
		if (count($tests))
		{
			$titles = ilObject::_prepareCloneSelection($tests, "tst");
			foreach ($tests as $ref_id)
			{
				if ($use_object_id)
				{
					$obj_id = ilObject::_lookupObjId($ref_id);
					$result_array[$obj_id] = $titles[$ref_id];
				}
				else
				{
					$result_array[$ref_id] = $titles[$ref_id];
				}
			}
		}
		return $result_array;
	}

/**
* Duplicates the source random questionpools for another test
*
* @param integer $new_id Test id of the new test which should take the random questionpools
* @access public
*/
	function cloneRandomQuestions($new_id)
	{
		global $ilDB;

		if ($new_id > 0)
		{
			$query = sprintf("SELECT * FROM tst_test_random WHERE test_fi = %s",
				$ilDB->quote($this->getTestId() . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
				{
					$insertresult = $ilDB->manipulateF("INSERT INTO tst_test_random (test_random_id, test_fi, questionpool_fi, num_of_q, tstamp) VALUES (NULL, %s, %s, %s, %s)",
						array('integer', 'integer', 'integer', 'integer'),
						array($new_id, $row["questionpool_fi"], $row["num_of_q"], time())
					);
				}
			}
		}
	}
	
	
	/**
	* Clone object
	*
	* @access public
	* @param int ref id of parent container
	* @param int copy id
	* @return object new test object
	*/
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $ilDB,$ilLog;
		
		$this->loadFromDb();
		
		// Copy settings
		$newObj = parent::cloneObject($a_target_id,$a_copy_id);
		$this->cloneMetaData($newObj);
		$newObj->setAnonymity($this->getAnonymity());
		$newObj->setAnswerFeedback($this->getAnswerFeedback());
		$newObj->setAnswerFeedbackPoints($this->getAnswerFeedbackPoints());
		$newObj->setAuthor($this->getAuthor());
		$newObj->setCountSystem($this->getCountSystem());
		$newObj->setECTSFX($this->getECTSFX());
		$newObj->setECTSGrades($this->getECTSGrades());
		$newObj->setECTSOutput($this->getECTSOutput());
		$newObj->setEnableProcessingTime($this->getEnableProcessingTime());
		$newObj->setEndingTime($this->getEndingTime());
		$newObj->setFixedParticipants($this->getFixedParticipants());
		$newObj->setInstantFeedbackSolution($this->getInstantFeedbackSolution());
		$newObj->setIntroduction($this->getIntroduction());
		$newObj->setFinalStatement($this->getFinalStatement());
		$newObj->setShowInfo($this->getShowInfo());
		$newObj->setForceJS($this->getForceJS());
		$newObj->setCustomStyle($this->getCustomStyle());
		$newObj->setShowFinalStatement($this->getShowFinalStatement());
		$newObj->setListOfQuestionsSettings($this->getListOfQuestionsSettings());
		$newObj->setMCScoring($this->getMCScoring());
		$newObj->setNrOfTries($this->getNrOfTries());
		$newObj->setPassScoring($this->getPassScoring());
		$newObj->setPassword($this->getPassword());
		$newObj->setProcessingTime($this->getProcessingTime());
		$newObj->setRandomQuestionCount($this->getRandomQuestionCount());
		$newObj->setRandomTest($this->isRandomTest());
		$newObj->setReportingDate($this->getReportingDate());
		$newObj->setResetProcessingTime($this->getResetProcessingTime());
		$newObj->setResultsPresentation($this->getResultsPresentation());
		$newObj->setScoreCutting($this->getScoreCutting());
		$newObj->setScoreReporting($this->getScoreReporting());
		$newObj->setSequenceSettings($this->getSequenceSettings());
		$newObj->setShowCancel($this->getShowCancel());
		$newObj->setShowMarker($this->getShowMarker());
		$newObj->setShuffleQuestions($this->getShuffleQuestions());
		$newObj->setStartingTime($this->getStartingTime());
		$newObj->setTitleOutput($this->getTitleOutput());
		$newObj->setUsePreviousAnswers($this->getUsePreviousAnswers());
		$newObj->mark_schema = clone $this->mark_schema;
		$newObj->saveToDb();
		
		if ($this->isRandomTest())
		{
			$newObj->saveRandomQuestionCount($newObj->getRandomQuestionCount());
			$this->cloneRandomQuestions($newObj->getTestId());
		}
		else
		{
			include_once("./Services/CopyWizard/classes/class.ilCopyWizardOptions.php");
			$cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
			
			// clone the questions
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			foreach ($this->questions as $key => $question_id)
			{
				$question = ilObjTest::_instanciateQuestion($question_id);
				$newObj->questions[$key] = $question->duplicate();
				$original_id = assQuestion::_getOriginalId($question_id);
				$question = ilObjTest::_instanciateQuestion($newObj->questions[$key]);
				$question->saveToDb($original_id);
				
				// Save the mapping of old question id <-> new question id
				// This will be used in class.ilObjCourse::cloneDependencies to copy learning objectives
				$cwo->appendMapping($this->getRefId().'_'.$question_id,$newObj->getRefId().'_'.$newObj->questions[$key]);
				$ilLog->write(__METHOD__.': Added mapping '.$this->getRefId().'_'.$question_id.' <-> ' .
						$newObj->getRefId().'_'.$newObj->questions[$key]);
			}
		}
		$newObj->saveToDb();
		return $newObj;
	}

	function _getRefIdFromObjId($obj_id)
	{
		global $ilDB;

		// TODO: please use ilObject::_getAllReferences() stefan
		$query = sprintf("SELECT ref_id FROM object_reference WHERE obj_id=%s",
			$ilDB->quote($obj_id,'integer')

		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row["ref_id"];
		}
		return 0;
	}

	/**
	* Returns the number of questions in the test
	*
	* @return integer The number of questions
	* @access	public
	*/
	function getQuestionCount()
	{
		$num = 0;

		if ($this->isRandomTest())
		{
			if ($this->getRandomQuestionCount())
			{
				$num = $this->getRandomQuestionCount();
				$qpls =& $this->getRandomQuestionpools();
				$maxcount = 0;
				foreach ($qpls as $data)
				{
					$maxcount += $data["contains"];
				}
				if ($num > $maxcount) $num = $maxcount;
			}
				else
			{
				$qpls =& $this->getRandomQuestionpools();
				foreach ($qpls as $data)
				{
					$add = ($data["count"] <= $data["contains"]) ? $data["count"] : $data["contains"];
					$num += $add;
				}
			}
		}
			else
		{
			$num = count($this->questions);
		}
		return $num;
	}

	/**
	* Returns the number of questions in the test for a given user
	*
	* @return integer The number of questions
	* @access	public
	*/
	function _getQuestionCount($test_id)
	{
		global $ilDB;

		$num = 0;

		$result = $ilDB->queryF("SELECT * FROM tst_tests WHERE test_id = %s",
			array('integer'),
			array($test_id)
		);
		if (!$result->numRows())
		{
			return 0;
		}
		$test = $ilDB->fetchAssoc($result);

		if ($test["random_test"] == 1)
		{
			$qpls = array();
			$counter = 0;
			$query = sprintf("SELECT * FROM tst_test_random WHERE test_fi = %s ORDER BY test_random_id",
				$ilDB->quote($test_id . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				while ($row = $ilDB->fetchAssoc($result))
				{
					$countresult = $ilDB->queryF("SELECT question_id FROM qpl_questions WHERE obj_fi =  %s AND qpl_questions.owner > 0 AND original_id IS NULL",
						array('integer'),
						$row["questionpool_fi"]
					);
					$contains = $countresult->numRows();
					$qpls[$counter] = array(
						"index" => $counter,
						"count" => $row["num_of_q"],
						"qpl"   => $row["questionpool_fi"],
						"contains" => $contains
					);
					$counter++;
				}
			}
			if ($test["random_question_count"] > 0)
			{
				$num = $test["random_question_count"];
				$maxcount = 0;
				foreach ($qpls as $data)
				{
					$maxcount += $data["contains"];
				}
				if ($num > $maxcount) $num = $maxcount;
			}
				else
			{
				$num = 0;
				foreach ($qpls as $data)
				{
					$add = ($data["count"] <= $data["contains"]) ? $data["count"] : $data["contains"];
					$num += $add;
				}
			}
		}
		else
		{
			$query = sprintf("SELECT test_question_id FROM tst_test_question WHERE test_fi = %s",
				$ilDB->quote($test_id . "")
			);
			$result = $ilDB->query($query);
			$num = $result->numRows();
		}
		return $num;
	}

/**
* Removes all test data of a non random test when a test was set to random test
*
* @access	private
*/
	function removeNonRandomTestData()
	{
		global $ilDB;

		// delete eventually set questions of a previous non-random test
		$this->removeAllTestEditings();
		$query = sprintf("DELETE FROM tst_test_question WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
		$this->questions = array();
		$this->saveCompleteStatus();
	}

/**
* Removes all test data of a random test when a test was set to non random test
*
* @access	private
*/
	function removeRandomTestData()
	{
		global $ilDB;
		// delete eventually set random question pools of a previous random test
		$this->removeAllTestEditings();
		$query = sprintf("DELETE FROM tst_test_random WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
		$this->questions = array();
		$this->saveCompleteStatus();
	}

/**
* Logs an action into the Test&Assessment log
*
* @param string $logtext The log text
* @param integer $question_id If given, saves the question id to the database
* @access public
*/
	function logAction($logtext = "", $question_id = "")
	{
		global $ilUser;

		$original_id = "";
		if (strcmp($question_id, "") != 0)
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$original_id = assQuestion::_getOriginalId($question_id);
		}
		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
		ilObjAssessmentFolder::_addLog($ilUser->getId(), $this->getId(), $logtext, $question_id, $original_id, TRUE, $this->getRefId());
	}

/**
* Returns the ILIAS test object id for a given test id
*
* @param integer $test_id The test id
* @return mixed The ILIAS test object id or FALSE if the query was not successful
* @access public
*/
	function _getObjectIDFromTestID($test_id)
	{
		global $ilDB;
		$object_id = FALSE;
		$query = sprintf("SELECT obj_fi FROM tst_tests WHERE test_id = %s",
			$ilDB->quote($test_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			$object_id = $row["obj_fi"];
		}
		return $object_id;
	}

/**
* Returns the ILIAS test object id for a given active id
*
* @param integer $active_id The active id
* @return mixed The ILIAS test object id or FALSE if the query was not successful
* @access public
*/
	function _getObjectIDFromActiveID($active_id)
	{
		global $ilDB;
		$object_id = FALSE;
		$query = sprintf("SELECT tst_tests.obj_fi FROM tst_tests, tst_active WHERE tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			$object_id = $row["obj_fi"];
		}
		return $object_id;
	}

/**
* Returns the ILIAS test id for a given object id
*
* @param integer $object_id The object id
* @return mixed The ILIAS test id or FALSE if the query was not successful
* @access public
*/
	function _getTestIDFromObjectID($object_id)
	{
		global $ilDB;
		$test_id = FALSE;
		$query = sprintf("SELECT test_id FROM tst_tests WHERE obj_fi = %s",
			$ilDB->quote($object_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			$test_id = $row["test_id"];
		}
		return $test_id;
	}

/**
* Returns the text answer of a given user for a given question
*
* @param integer $user_id The user id
* @param integer $question_id The question id
* @return string The answer text
* @access public
*/
	function getTextAnswer($active_id, $question_id, $pass = NULL)
	{
		global $ilDB;

		$res = "";
		if (($active_id) && ($question_id))
		{
			if (is_null($pass))
			{
				include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
				$pass = assQuestion::_getSolutionMaxPass($question_id, $active_id);
			}
			$query = sprintf("SELECT value1 FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote($pass . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows() == 1)
			{
				$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
				$res = $row["value1"];
			}
		}
		return $res;
	}

/**
* Returns the question text for a given question
*
* @param integer $question_id The question id
* @return string The question text
* @access public
*/
	function getQuestiontext($question_id)
	{
		global $ilDB;

		$res = "";
		if ($question_id)
		{
			$result = $ilDB->queryF("SELECT question_text FROM qpl_questions WHERE question_id = %s",
				array('integer'),
				array($question_id)
			);
			$result = $ilDB->query($query);
			if ($result->numRows() == 1)
			{
				$row = $ilDB->fetchAssoc($result);
				$res = $row["question_text"];
			}
		}
		return $res;
	}

/**
* Returns a list of all invited users in a test
*
* @return array array of invited users
* @access public
*/
	function &getInvitedUsers($user_id="", $order="login, lastname, firstname")
	{
		global $ilDB;

		$result_array = array();

		if ($this->getAnonymity())
		{
			if (is_numeric($user_id))
			{
				$query = sprintf("SELECT tst_active.active_id, tst_active.tries, usr_id, '' login, %s lastname, '' firstname, tst_invited_user.clientip, " .
					"tst_active.submitted as test_finished, matriculation, IF(tst_active.active_id IS NULL,0,1) as test_started " .
					"FROM usr_data, tst_invited_user " .
					"LEFT JOIN tst_active ON tst_active.user_fi = tst_invited_user.user_fi AND tst_active.test_fi = tst_invited_user.test_fi " .
					"WHERE tst_invited_user.test_fi = %s and tst_invited_user.user_fi=usr_data.usr_id AND usr_data.usr_id=%s " .
					"ORDER BY %s",
					$ilDB->quote($this->lng->txt("unknown")),
					$ilDB->quote($this->test_id),
					$user_id,
					$order
				);
			}
			else
			{
				$query = sprintf("SELECT tst_active.active_id, usr_id, '' login, %s lastname, '' firstname, tst_invited_user.clientip, " .
					"tst_active.submitted as test_finished, matriculation, IF(tst_active.active_id IS NULL,0,1) as test_started " .
					"FROM usr_data, tst_invited_user " .
					"LEFT JOIN tst_active ON tst_active.user_fi = tst_invited_user.user_fi AND tst_active.test_fi = tst_invited_user.test_fi " .
					"WHERE tst_invited_user.test_fi = %s and tst_invited_user.user_fi=usr_data.usr_id " .
					"ORDER BY %s",
					$ilDB->quote($this->lng->txt("unknown")),
					$ilDB->quote($this->test_id),
					$order
				);
			}
		}
		else
		{
			if (is_numeric($user_id))
			{
				$query = sprintf("SELECT tst_active.active_id, tst_active.tries, usr_id, login, lastname, firstname, tst_invited_user.clientip, " .
					"tst_active.submitted as test_finished, matriculation, IF(tst_active.active_id IS NULL,0,1) as test_started " .
					"FROM usr_data, tst_invited_user " .
					"LEFT JOIN tst_active ON tst_active.user_fi = tst_invited_user.user_fi AND tst_active.test_fi = tst_invited_user.test_fi " .
					"WHERE tst_invited_user.test_fi = %s and tst_invited_user.user_fi=usr_data.usr_id AND usr_data.usr_id=%s " .
					"ORDER BY %s",
					$ilDB->quote($this->test_id),
					$user_id,
					$order
				);
			}
			else
			{
				$query = sprintf("SELECT tst_active.active_id, tst_active.tries, usr_id, login, lastname, firstname, tst_invited_user.clientip, " .
					"tst_active.submitted as test_finished, matriculation, IF(tst_active.active_id IS NULL,0,1) as test_started " .
					"FROM usr_data, tst_invited_user " .
					"LEFT JOIN tst_active ON tst_active.user_fi = tst_invited_user.user_fi AND tst_active.test_fi = tst_invited_user.test_fi " .
					"WHERE tst_invited_user.test_fi = %s and tst_invited_user.user_fi=usr_data.usr_id " .
					"ORDER BY %s",
					$ilDB->quote($this->test_id),
					$order
				);
			}
		}
		return $this->getArrayData($query, "usr_id");
	}

/**
* Returns a list of all participants in a test
*
* @return array The user id's of the participants
* @access public
*/
	function &getTestParticipants()
	{
		global $ilDB;

		if ($this->getAnonymity())
		{
			$q = sprintf("SELECT tst_active.active_id, tst_active.tries, tst_active.user_fi usr_id, '' login, %s lastname, '' firstname, tst_active.submitted as test_finished, usr_data.matriculation, usr_data.active, IF(tst_active.active_id IS NULL,0,1) as test_started ".
				"FROM tst_active LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id WHERE tst_active.test_fi = %s ORDER BY usr_data.lastname " . strtoupper($name_sort_order),
				$ilDB->quote($this->lng->txt("unknown")),
				$ilDB->quote($this->getTestId())
			);
		}
		else
		{
			$q = sprintf("SELECT tst_active.active_id, tst_active.tries, tst_active.user_fi usr_id, usr_data.login, usr_data.lastname, usr_data.firstname, tst_active.submitted as test_finished, usr_data.matriculation, usr_data.active, IF(tst_active.active_id IS NULL,0,1) as test_started ".
				"FROM tst_active LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id WHERE tst_active.test_fi = %s ORDER BY usr_data.lastname " . strtoupper($name_sort_order),
				$ilDB->quote($this->getTestId())
			);
		}
		$data = $this->getArrayData($q, "active_id");
		foreach ($data as $index => $participant)
		{
			if (strlen(trim($participant["firstname"].$participant["lastname"])) == 0)
			{
				$data[$index]["lastname"] = $this->lng->txt("deleted_user");
			}
		}
		return $data;
	}
	
	public function getTestParticipantsForManualScoring($filter = NULL)
	{
		global $ilDB;
		
		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
		$scoring = ilObjAssessmentFolder::_getManualScoring();
		if (count($scoring) == 0) return array();

		$participants =& $this->getTestParticipants();
		$filtered_participants = array();
		foreach ($participants as $active_id => $participant)
		{
			$result = $ilDB->queryF("SELECT tst_test_result.manual FROM tst_test_result,qpl_questions WHERE tst_test_result.question_fi = qpl_questions.question_id AND " . $ilDB->in('qpl_questions.question_type_fi', $scoring, false, 'integer') . " AND tst_test_result.active_fi = %s", 
				array("integer"),
				array($active_id)
			);
			$count = $result->numRows();
			if ($count > 0)
			{
				switch ($filter)
				{
					case 1: // only active users
						if ($participant->active) $filtered_participants[$active_id] = $participant;
						break;
					case 2: // only inactive users
						if (!$participant->active) $filtered_participants[$active_id] = $participant;
						break;
					case 3: // all users
						$filtered_participants[$active_id] = $participant;
						break;
					case 4:
						// already scored participants
						$found = 0;
						while ($row = $ilDB->fetchAssoc($result))
						{
							if ($row["manual"]) $found++;
						}
						if ($found == $count) 
						{
							$filtered_participants[$active_id] = $participant;
						}
						else
						{
							$assessmentSetting = new ilSetting("assessment");
							$manscoring_done = $assessmentSetting->get("manscoring_done_" . $active_id);
							if ($manscoring_done) $filtered_participants[$active_id] = $participant;
						}
						break;
					case 5:
						// unscored participants
						$found = 0;
						while ($row = $ilDB->fetchAssoc($result))
						{
							if ($row["manual"]) $found++;
						}
						if ($found == 0) 
						{
							$assessmentSetting = new ilSetting("assessment");
							$manscoring_done = $assessmentSetting->get("manscoring_done_" . $active_id);
							if (!$manscoring_done) $filtered_participants[$active_id] = $participant;
						}
						break;
					case 6:
						// partially scored participants
						$found = 0;
						while ($row = $ilDB->fetchAssoc($result))
						{
							if ($row["manual"]) $found++;
						}
						if (($found > 0) && ($found < $count)) $filtered_participants[$active_id] = $participant;
						break;
					default:
						$filtered_participants[$active_id] = $participant;
						break;
				}
			}
		}
		return $filtered_participants;
	}

/**
* Returns a data of all users specified by id list
*
* @param $usr_ids kommaseparated list of ids
* @return array The user data "usr_id, login, lastname, firstname, clientip" of the users with id as key
* @access public
*/
	function &getUserData($ids)
	{
		if (!is_array($ids) || count($ids) ==0)
			return array();

		$result_array = array();

		if ($this->getAnonymity())
		{
			$query = sprintf("SELECT usr_id, '' login, %s lastname, '' firstname, client_ip as clientip FROM usr_data WHERE usr_id IN ('%s') ORDER BY login",
				$ilDB->quote($this->lng->txt("unknown")),
				join ($ids,"','")
			);
		}
		else
		{
			$query = sprintf("SELECT usr_id, login, lastname, firstname, client_ip as clientip FROM usr_data WHERE usr_id IN ('%s') ORDER BY login",
				join ($ids,"','")
			);
		}

		return $this->getArrayData ($query, "usr_id");
	}

/**
* Returns a data as id key list
*
* @param $query
* @param $id_field index for array
* @return array with data with id as key
*/
	protected function &getArrayData($query, $id_field)
	{
		global $ilDB;

		$statement = $ilDB->prepare($query);
		$result = $ilDB->execute($statement);
		$result_array = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			$result_array[$row[$id_field]]= $row;
		}
		return $result_array;
	}

	/**
	* Returns a data as id key list
	*
	* @param $query
	* @param $id_field index for array
	* @return array with data with id as key
	*/
	public static function &_getArrayData($query, $id_field)
	{
		global $ilDB;

		$statement = $ilDB->prepare($query);
		$result = $ilDB->execute($statement);
		$result_array = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			$result_array[$row[$id_field]]= $row;
		}
		return $result_array;
	}

	function &getGroupData($ids)
	{
		if (!is_array($ids) || count($ids) ==0) return array();
		$result = array();
		foreach ($ids as $ref_id)
		{
			$obj_id = ilObject::_lookupObjId($ref_id);
			$result[$ref_id] = array("ref_id" => $ref_id, "title" => ilObject::_lookupTitle($obj_id), "description" => ilObject::_lookupDescription($obj_id));
		}
		return $result;
	}

	function &getRoleData($ids)
	{
		if (!is_array($ids) || count($ids) ==0) return array();
		$result = array();
		foreach ($ids as $obj_id)
		{
			$result[$obj_id] = array("obj_id" => $ref_id, "title" => ilObject::_lookupTitle($obj_id), "description" => ilObject::_lookupDescription($obj_id));
		}
		return $result;
	}


/**
* Invites all users of a group to a test
*
* @param integer $group_id The database id of the invited group
* @access public
*/
	function inviteGroup($group_id)
	{
		include_once "./Modules/Group/classes/class.ilObjGroup.php";
		$group = new ilObjGroup($group_id);
		$members = $group->getGroupMemberIds();
		include_once './Services/User/classes/class.ilObjUser.php';
		foreach ($members as $user_id)
		{
			$this->inviteUser($user_id, ilObjUser::_lookupClientIP($user_id));
		}
	}

/**
* Invites all users of a role to a test
*
* @param integer $group_id The database id of the invited group
* @access public
*/
	function inviteRole($role_id)
	{
		global $rbacreview;
		$members =  $rbacreview->assignedUsers($role_id,"usr_id");
		include_once './Services/User/classes/class.ilObjUser.php';
		foreach ($members as $user_id)
		{
			$this->inviteUser($user_id, ilObjUser::_lookupClientIP($user_id));
		}
	}



/**
* Disinvites a user from a test
*
* @param integer $user_id The database id of the disinvited user
* @access public
*/
	function disinviteUser($user_id)
	{
		global $ilDB;

		$query = sprintf("DELETE FROM tst_invited_user WHERE test_fi = %s AND user_fi = %s",
			$ilDB->quote($this->test_id),
			$ilDB->quote($user_id)
		);
		$result = $ilDB->query($query);
	}

/**
* Invites a user to a test
*
* @param integer $user_id The database id of the invited user
* @access public
*/
	function inviteUser($user_id, $client_ip="")
	{
		global $ilDB;

		$query = sprintf("INSERT IGNORE INTO tst_invited_user (test_fi, user_fi, clientip) VALUES (%s, %s, %s)",
			$ilDB->quote($this->test_id),
			$ilDB->quote($user_id),
			$ilDB->quote($client_ip)
		);
		$result = $ilDB->query($query);
	}


	function setClientIP($user_id, $client_ip)
	{
		global $ilDB;

		$query = sprintf("UPDATE tst_invited_user SET clientip=%s WHERE test_fi=%s and user_fi=%s",
				$ilDB->quote($client_ip),
				$ilDB->quote($this->test_id),
				$ilDB->quote($user_id)
		);
		$insertresult = $ilDB->query($query);
	}

	/**
	 * get solved questions
	 *
	 * @return array of int containing all question ids which have been set solved for the given user and test
	 */
	function _getSolvedQuestions($active_id, $question_fi = null)
	{
		global $ilDB;
		if (is_numeric($question_fi))
			$query = sprintf("SELECT question_fi, solved FROM tst_qst_solved " .
						 "WHERE active_fi = %s AND question_fi=%s",
							$ilDB->quote($active_id),
							$question_fi
			);
		else $query = sprintf("SELECT question_fi, solved FROM tst_qst_solved " .
						 "WHERE active_fi = %s",
			$ilDB->quote($active_id)
		);
		return ilObjTest::_getArrayData ($query, "question_fi");
	}


	/**
	 * sets question solved state to value for given user_id
	 */
	function setQuestionSetSolved($value, $question_id, $user_id)
	{
		global $ilDB;

		$active_id = $this->getActiveIdOfUser($user_id);
		$query = sprintf("REPLACE INTO tst_qst_solved SET solved=%s, question_fi=%s, active_fi = %s",
			$ilDB->quote($value),
			$ilDB->quote($question_id),
			$ilDB->quote($active_id)
		);

		$ilDB->query($query);
	}


	/**
	 * submits active test for user user_id
	 */
	function setActiveTestSubmitted($user_id)
	{
		global $ilDB, $ilLog;

		$query = sprintf("UPDATE tst_active SET submitted = 1, submittimestamp = %s WHERE test_fi = %s AND user_fi = %s",
			$ilDB->quote(date('Y-m-d H:i:s')),
			$ilDB->quote($this->getTestId() . ""),
			$ilDB->quote($user_id . "")
		);
		$result = $ilDB->query($query);
		$this->testSession = NULL;
	}

	/**
	 * returns if the active for user_id has been submitted
	 */
	function isActiveTestSubmitted($user_id = null)
	{
		global $ilUser;
		global $ilDB;

		if (!is_numeric($user_id))
			$user_id = $ilUser->getId();

		$query = sprintf("SELECT submitted FROM tst_active WHERE test_fi=%s AND user_fi=%s AND submitted=1",
			$ilDB->quote($this->test_id),
			$ilDB->quote($user_id)
		);
		$result = $ilDB->query($query);

		return 	$result->numRows() == 1;

	}
	/**
	 * returns if the numbers of tries have to be checked
	 */
	function hasNrOfTriesRestriction()
	{
		return $this->getNrOfTries() != 0;
	}


	/**
	 * returns if number of tries are reached
	 */

	function isNrOfTriesReached($tries)
	{
		return $tries >= (int) $this->getNrOfTries();
	}


	/**
	 * returns all test results for all participants
	 *
	 * @param array $partipants array of user ids
	 * @param boolean if true, the result will be prepared for csv output (see processCSVRow)
	 *
	 * @return array of fields, see code for column titles
	 */
	function getAllTestResults($participants, $prepareForCSV = true)
	{
		$results = array();
		$row = array(
			"user_id" => $this->lng->txt("user_id"),
			"matriculation" =>  $this->lng->txt("matriculation"),
			"lastname" =>  $this->lng->txt("lastname"),
			"firstname" => $this->lng->txt("firstname"),
			"login" =>$this->lng->txt("login"),
			"reached_points" => $this->lng->txt("tst_reached_points"),
			"max_points" => $this->lng->txt("tst_maximum_points"),
			"percent_value" => $this->lng->txt("tst_percent_solved"),
			"mark" => $this->lng->txt("tst_mark"),
			"ects" => $this->lng->txt("ects_grade")
		);
		$results[] = $row;
		#print_r($participants);
		if (count($participants))
		{
			foreach ($participants as $active_id => $user_rec)
			{
				$row = array();
				$reached_points = 0;
				$max_points = 0;
				foreach ($this->questions as $value)
				{
					$question =& ilObjTest::_instanciateQuestion($value);
					if (is_object($question))
					{
						$max_points += $question->getMaximumPoints();
						$reached_points += $question->getReachedPoints($active_id);
					}
				}
				if ($max_points > 0)
				{
					$percentvalue = $reached_points / $max_points;
					if ($percentvalue < 0) $percentvalue = 0.0;
				}
				else
				{
					$percentvalue = 0;
				}
				$mark_obj = $this->mark_schema->getMatchingMark($percentvalue * 100);
				$passed = "";
				if ($mark_obj)
				{
					$mark = $mark_obj->getOfficialName();
					$ects_mark = $this->getECTSGrade($reached_points, $max_points);
				}
				if ($this->getAnonymity())
				{
					$user_rec->firstname = "";
					$user_rec->lastname = $this->lng->txt("unknown");
				}
				$row = array(
"user_id"=>$user_rec->usr_id,
				"matriculation" =>  $user_rec->matriculation,
					"lastname" =>  $user_rec->lastname,
					"firstname" => $user_rec->firstname,
"login"=>$user_rec->login,
				"reached_points" => $reached_points,
					"max_points" => $max_points,
					"percent_value" => $percentvalue,
					"mark" => $mark,
					"ects" => $ects_mark
				);
				$results[] = $prepareForCSV ? $this->processCSVRow ($row, true) : $row;
			}
		}
		return $results;
	}

/**
* Processes an array as a CSV row and converts the array values to correct CSV
* values. The "converted" array is returned
*
* @param array $row The array containing the values for a CSV row
* @param string $quoteAll Indicates to quote every value (=TRUE) or only values containing quotes and separators (=FALSE, default)
* @param string $separator The value separator in the CSV row (used for quoting) (; = default)
* @return array The converted array ready for CSV use
* @access public
*/
	function &processCSVRow($row, $quoteAll = FALSE, $separator = ";")
	{
		$resultarray = array();
		foreach ($row as $rowindex => $entry)
		{
			$surround = FALSE;
			if ($quoteAll)
			{
				$surround = TRUE;
			}
			if (strpos($entry, "\"") !== FALSE)
			{
				$entry = str_replace("\"", "\"\"", $entry);
				$surround = TRUE;
			}
			if (strpos($entry, $separator) !== FALSE)
			{
				$surround = TRUE;
			}
			// replace all CR LF with LF (for Excel for Windows compatibility
			$entry = str_replace(chr(13).chr(10), chr(10), $entry);
			if ($surround)
			{
			    $resultarray[$rowindex] = utf8_decode("\"" . $entry . "\"");
			}
			else
			{
				$resultarray[$rowindex] = utf8_decode($entry);
			}
		}
		return $resultarray;
	}

/**
* Retrieves the actual pass of a given user for a given test
*
* @param integer $user_id The user id
* @param integer $test_id The test id
* @return integer The pass of the user for the given test
* @access public
*/
	function _getPass($active_id)
	{
		global $ilDB;
		$query = sprintf("SELECT tries FROM tst_active WHERE active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row["tries"];
		}
		else
		{
			return 0;
		}
	}

	/**
	* Retrieves the maximum pass of a given user for a given test
	* in which the user answered at least one question
	*
	* @param integer $user_id The user id
	* @param integer $test_id The test id
	* @return integer The pass of the user for the given test
	* @access public
	*/
		function _getMaxPass($active_id)
		{
			global $ilDB;
			$query = sprintf("SELECT MAX(pass) as maxpass FROM tst_test_result WHERE active_fi = %s",
				$ilDB->quote($active_id . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
				$max = $row["maxpass"];
			}
			else
			{
				$max = NULL;
			}
			return $max;
		}

/**
* Retrieves the best pass of a given user for a given test
*
* @param integer $user_id The user id
* @param integer $test_id The test id
* @return integer The best pass of the user for the given test
* @access public
*/
	function _getBestPass($active_id)
	{
		global $ilDB;
		
		$query = sprintf("SELECT * FROM tst_pass_result WHERE active_fi = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$bestrow = null;
			$bestpoints = -1;
			while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				if ($row["points"] > $bestpoints) 
				{
					$bestrow = $row;
					$bestpoints = $row["points"];
				}
			}
			if (is_array($bestrow))
			{
				return $bestrow["pass"];
			}
			else
			{
				return 0;
			}
		}
		else
		{
			return 0;
		}
	}

/**
* Retrieves the pass number that should be counted for a given user
*
* @param integer $user_id The user id
* @param integer $test_id The test id
* @return integer The result pass of the user for the given test
* @access public
*/
	function _getResultPass($active_id)
	{
		$counted_pass = NULL;
		if (ilObjTest::_getPassScoring($active_id) == SCORE_BEST_PASS)
		{
			$counted_pass = ilObjTest::_getBestPass($active_id);
		}
		else
		{
			$counted_pass = ilObjTest::_getMaxPass($active_id);
		}
		return $counted_pass;
	}

/**
* Retrieves the number of answered questions for a given user in a given test
*
* @param integer $user_id The user id
* @param integer $test_id The test id
* @param integer $pass The pass of the test (optional)
* @return integer The number of answered questions
* @access public
*/
	function getAnsweredQuestionCount($active_id, $pass = NULL)
	{
		if ($this->isRandomTest())
		{
			$this->loadQuestions($active_id, $pass);
		}
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$workedthrough = 0;
		foreach ($this->questions as $value)
		{
			if (assQuestion::_isWorkedThrough($active_id, $value, $pass))
			{
				$workedthrough += 1;
			}
		}
		return $workedthrough;
	}

/**
* Retrieves the number of answered questions for a given user in a given test
*
* @param integer $user_id The user id
* @param integer $test_id The test id
* @param integer $pass The pass of the test
* @return timestamp The SQL timestamp of the finished pass
* @access public
*/
	function getPassFinishDate($active_id, $pass)
	{
		global $ilDB;
		if (is_null($pass)) $pass = 0;
		$result = $ilDB->queryF("SELECT tst_test_result.tstamp FROM tst_test_result WHERE active_fi = %s AND pass = %s ORDER BY tst_test_result.tstamp DESC",
			array('integer', 'integer'),
			array($active_id, $pass)
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
			return date("YmdHIs", $row["tstamp"]);
		}
		else
		{
			return 0;
		}
	}

/**
* Checks if the test is executable by the given user
*
* @param integer $user_id The user id
* @return array Result array
* @access public
*/
	function isExecutable($user_id, $allowPassIncrease = FALSE)
	{
		$result = array(
			"executable" => true,
			"errormessage" => ""
		);
		if (!$this->startingTimeReached())
		{
			$result["executable"] = false;
			$result["errormessage"] = sprintf($this->lng->txt("detail_starting_time_not_reached"), ilFormat::ftimestamp2datetimeDB($this->getStartingTime()));
			return $result;
		}
		if ($this->endingTimeReached())
		{
			$result["executable"] = false;
			$result["errormessage"] = sprintf($this->lng->txt("detail_ending_time_reached"), ilFormat::ftimestamp2datetimeDB($this->getEndingTime()));
			return $result;
		}

		$active_id = $this->getActiveIdOfUser($user_id);

		if ($this->getEnableProcessingTime())
		{
			if ($active_id > 0)
			{
				$starting_time = $this->getStartingTimeOfUser($active_id);
				if ($starting_time !== FALSE)
				{
					if ($this->isMaxProcessingTimeReached($starting_time))
					{
						if ($allowPassIncrease && $this->getResetProcessingTime() && (($this->getNrOfTries() == 0) || ($this->getNrOfTries() > ($this->_getPass($active_id)+1))))
						{
							// a test pass was quitted because the maximum processing time was reached, but the time
							// will be resetted for future passes, so if there are more passes allowed, the participant may
							// start the test again.
							// This code block is only called when $allowPassIncrease is TRUE which only happens when
							// the test info page is opened. Otherwise this will lead to unexpected results!
							$this->getTestSession()->increasePass();
							$this->getTestSession()->setLastSequence(0);
							$this->getTestSession()->saveToDb();
						}
						else
						{
							$result["executable"] = false;
							$result["errormessage"] = $this->lng->txt("detail_max_processing_time_reached");
						}
						return $result;
					}
				}
			}
		}

		if ($this->hasNrOfTriesRestriction() && ($active_id > 0) && $this->isNrOfTriesReached($this->getTestSession($active_id)->getPass()))
		{
			$result["executable"] = false;
			$result["errormessage"] = $this->lng->txt("maximum_nr_of_tries_reached");
			return $result;
		}
		
		if ($this->getTestSession($active_id)->isSubmitted())
		{
			$result["executable"] = FALSE;
			$result["errormessage"] = $this->lng->txt("maximum_nr_of_tries_reached");
			return $result;
		}

		// TODO: max. processing time

		return $result;
	}

/**
* Returns true, if the test results can be viewed
*
* @return boolean True, if the test results can be viewed, else false
* @access public
*/
	function canViewResults()
	{
		$result = true;
		if ($this->getReportingDate())
		{
			if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getReportingDate(), $matches))
			{
				$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
				$now = mktime();
				if ($now < $epoch_time)
				{
					$result = false;
				}
			}
		}
		return $result;
	}

	function canShowTestResults($user_id)
	{
		$active_id = $this->getActiveIdOfUser($user_id);
		if ($active > 0)
		{
			$starting_time = $this->getStartingTimeOfUser($active_id);
		}
		$notimeleft = FALSE;
		if ($starting_time !== FALSE)
		{
			if ($this->isMaxProcessingTimeReached($starting_time))
			{
				$notimeleft = TRUE;
			}
		}
		$result = TRUE;
		if (!$this->isTestFinishedToViewResults($active_id, $this->getTestSession($active_id)->getPass()) && ($this->getScoreReporting() == REPORT_AFTER_TEST))
		{
			$result = FALSE;
		}
		if (($this->endingTimeReached()) || $notimeleft) $result = TRUE;
		$result = $result & $this->canViewResults();
		return $result;
	}

	function canEditMarks()
	{
		$total = $this->evalTotalPersons();
		if ($total > 0)
		{
			if ($this->getReportingDate())
			{
				if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getReportingDate(), $matches))
				{
					$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
					$now = mktime();
					if ($now < $epoch_time)
					{
						return true;
					}
				}
			}
			return false;
		}
		else
		{
			return true;
		}
	}

/**
* Returns the unix timestamp of the time a user started a test
*
* @param integer $active_id The active id of the user
* @return mixed The unix timestamp if the user started the test, FALSE otherwise
* @access public
*/
	function getStartingTimeOfUser($active_id)
	{
		global $ilDB;

		if ($active_id < 1) return FALSE;
		$pass = ($this->getResetProcessingTime()) ? $this->_getPass($active_id) : 0;
		$query = sprintf("SELECT tst_times.started FROM tst_times WHERE tst_times.active_fi = %s AND tst_times.pass = %s ORDER BY tst_times.started",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			if (preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches))
			{
				return mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			}
			else
			{
				return mktime();
			}
		}
		else
		{
			return mktime();
		}
	}

/**
* Returns wheather the maximum processing time for a test is reached or not
*
* @param long $starting_time The unix timestamp of the starting time of the test
* @return boolean TRUE if the maxium processing time is reached, FALSE if the
*					maximum processing time is not reached or no maximum processing time is given
* @access public
*/
	function isMaxProcessingTimeReached($starting_time)
	{
		if ($this->getEnableProcessingTime())
		{
			$processing_time = $this->getProcessingTimeInSeconds();
			$now = mktime();
			if ($now > ($starting_time + $processing_time))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

	function &getTestQuestions()
	{
		global $ilDB;
		$query_result = $ilDB->queryF("SELECT qpl_questions.*, qpl_qst_type.type_tag FROM qpl_questions, qpl_qst_type, tst_test_question WHERE qpl_questions.question_type_fi = qpl_qst_type.question_type_id AND tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id ORDER BY sequence",
			array('integer'),
			array($this->getTestId())
		);
		$removableQuestions = array();
		while ($row = $ilDB->fetchAssoc($query_result))
		{
			array_push($removableQuestions, $row);
		}
		return $removableQuestions;
	}

/**
* Returns the status of the shuffle_questions variable
*
* @return integer 0 if the test questions are not shuffled, 1 if the test questions are shuffled
* @access public
*/
	function getShuffleQuestions()
	{
		return ($this->shuffle_questions) ? 1 : 0;
	}

/**
* Sets the status of the shuffle_questions variable
*
* @param boolean $a_shuffle 0 if the test questions are not shuffled, 1 if the test questions are shuffled
* @access public
*/
	function setShuffleQuestions($a_shuffle)
	{
		$this->shuffle_questions = ($a_shuffle) ? 1 : 0;
	}

/**
* Returns the settings for the list of questions options in the test properties
* This could contain one of the following values:
*   0 = No list of questions offered
*   1 = A list of questions is offered
*   3 = A list of questions is offered and the list of questions is shown as first page of the test
*   5 = A list of questions is offered and the list of questions is shown as last page of the test
*   7 = A list of questions is offered and the list of questions is shown as first and last page of the test
*
* @return integer TRUE if the list of questions should be presented, FALSE otherwise
* @access public
*/
	function getListOfQuestionsSettings()
	{
		return ($this->show_summary) ? $this->show_summary : 0;
	}

/**
* Sets the settings for the list of questions options in the test properties
* This could contain one of the following values:
*   0 = No list of questions offered
*   1 = A list of questions is offered
*   3 = A list of questions is offered and the list of questions is shown as first page of the test
*   5 = A list of questions is offered and the list of questions is shown as last page of the test
*   7 = A list of questions is offered and the list of questions is shown as first and last page of the test
*
* @param integer $a_value 0, 1, 3, 5 or 7
* @access public
*/
	function setListOfQuestionsSettings($a_value = 0)
	{
		$this->show_summary = $a_value;
	}

/**
* Returns if the list of questions should be presented to the user or not
*
* @return boolean TRUE if the list of questions should be presented, FALSE otherwise
* @access public
*/
	function getListOfQuestions()
	{
		if (($this->show_summary & 1) > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

/**
* Sets if the the list of questions should be presented to the user or not
*
* @param boolean $a_value TRUE if the list of questions should be presented, FALSE otherwise
* @access public
*/
	function setListOfQuestions($a_value = TRUE)
	{
		if ($a_value)
		{
			$this->show_summary = 1;
		}
		else
		{
			$this->show_summary = 0;
		}
	}

/**
* Returns if the list of questions should be presented as the first page of the test
*
* @return boolean TRUE if the list of questions is shown as first page of the test, FALSE otherwise
* @access public
*/
	function getListOfQuestionsStart()
	{
		if (($this->show_summary & 2) > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

/**
* Sets if the the list of questions as the start page of the test
*
* @param boolean $a_value TRUE if the list of questions should be the start page, FALSE otherwise
* @access public
*/
	function setListOfQuestionsStart($a_value = TRUE)
	{
		if ($a_value && $this->getListOfQuestions())
		{
			$this->show_summary = $this->show_summary | 2;
		}
		if (!$a_value && $this->getListOfQuestions())
		{
			if ($this->getListOfQuestionsStart())
			{
				$this->show_summary = $this->show_summary ^ 2;
			}
		}
	}

/**
* Returns if the list of questions should be presented as the last page of the test
*
* @return boolean TRUE if the list of questions is shown as last page of the test, FALSE otherwise
* @access public
*/
	function getListOfQuestionsEnd()
	{
		if (($this->show_summary & 4) > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

/**
* Sets if the the list of questions as the end page of the test
*
* @param boolean $a_value TRUE if the list of questions should be the end page, FALSE otherwise
* @access public
*/
	function setListOfQuestionsEnd($a_value = TRUE)
	{
		if ($a_value && $this->getListOfQuestions())
		{
			$this->show_summary = $this->show_summary | 4;
		}
		if (!$a_value && $this->getListOfQuestions())
		{
			if ($this->getListOfQuestionsEnd())
			{
				$this->show_summary = $this->show_summary ^ 4;
			}
		}
	}

	/**
	* Returns TRUE if the list of questions should be presented with the question descriptions
	*
	* @return boolean TRUE if the list of questions is shown with the question descriptions, FALSE otherwise
	* @access public
	*/
		function getListOfQuestionsDescription()
		{
			if (($this->show_summary & 8) > 0)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}

	/**
	* Sets the show_summary attribute to TRUE if the list of questions should be presented with the question descriptions
	*
	* @param boolean $a_value TRUE if the list of questions should be shown with question descriptions, FALSE otherwise
	* @access public
	*/
		function setListOfQuestionsDescription($a_value = TRUE)
		{
			if ($a_value && $this->getListOfQuestions())
			{
				$this->show_summary = $this->show_summary | 8;
			}
			if (!$a_value && $this->getListOfQuestions())
			{
				if ($this->getListOfQuestionsDescription())
				{
					$this->show_summary = $this->show_summary ^ 8;
				}
			}
		}

/**
* Returns the combined results presentation value
*
* @return integer The combined results presentation value
* @access public
*/
	function getResultsPresentation()
	{
		return ($this->results_presentation) ? $this->results_presentation : 0;
	}

/**
* Returns if the pass details should be shown when a test is not finished
*
* @return boolean TRUE if the pass details should be shown, FALSE otherwise
* @access public
*/
	function getShowPassDetails()
	{
		if (($this->results_presentation & 1) > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

/**
* Returns if the solution details should be presented to the user or not
*
* @return boolean TRUE if the solution details should be presented, FALSE otherwise
* @access public
*/
	function getShowSolutionDetails()
	{
		if (($this->results_presentation & 2) > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

/**
* Returns if the solution printview should be presented to the user or not
*
* @return boolean TRUE if the solution printview should be presented, FALSE otherwise
* @access public
*/
	function getShowSolutionPrintview()
	{
		if (($this->results_presentation & 4) > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

/**
* Returns if the feedback should be presented to the solution or not
*
* @return boolean TRUE if the feedback should be presented in the solution, FALSE otherwise
* @access public
*/
	function getShowSolutionFeedback()
	{
		if (($this->results_presentation & 8) > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* Returns if the full solution (including ILIAS content) should be presented to the solution or not
	*
	* @return boolean TRUE if the full solution should be presented in the solution output, FALSE otherwise
	* @access public
	*/
		function getShowSolutionAnswersOnly()
		{
			if (($this->results_presentation & 16) > 0)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}

		/**
		* Returns if the signature field should be shown in the test results
		*
		* @return boolean TRUE if the signature field should be shown, FALSE otherwise
		* @access public
		*/
		function getShowSolutionSignature()
		{
			if (($this->results_presentation & 32) > 0)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}

	/**
	* @return boolean TRUE if the suggested solutions should be shown, FALSE otherwise
	* @access public
	*/
	function getShowSolutionSuggested()
	{
		if (($this->results_presentation & 64) > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

/**
* Sets the combined results presentation value
*
* @param integer $a_results_presentation The combined results presentation value
* @access public
*/
	function setResultsPresentation($a_results_presentation = 3)
	{
		$this->results_presentation = $a_results_presentation;
	}

/**
* Sets if the pass details should be shown when a test is not finished
*
* Sets if the pass details should be shown when a test is not finished
*
* @param boolean $a_details TRUE if the pass details should be shown, FALSE otherwise
* @access public
*/
	function setShowPassDetails($a_details = 1)
	{
		if ($a_details)
		{
			$this->results_presentation = $this->results_presentation | 1;
		}
		else
		{
			if ($this->getShowPassDetails())
			{
				$this->results_presentation = $this->results_presentation ^ 1;
			}
		}
	}

/**
* Sets if the the solution details should be presented to the user or not
*
* @param integer $a_details 1 if the solution details should be presented, 0 otherwise
* @access public
*/
	function setShowSolutionDetails($a_details = 1)
	{ 
		if ($a_details)
		{
			$this->results_presentation = $this->results_presentation | 2;
		}
		else
		{
			if ($this->getShowSolutionDetails())
			{
				$this->results_presentation = $this->results_presentation ^ 2;
			}
		}
	}

/**
* Calculates if a user may see the solution printview of his/her test results
*
* @return boolean TRUE if the user may see the printview, FALSE otherwise
* @access public
*/
	function canShowSolutionPrintview($user_id = NULL)
	{
		return $this->getShowSolutionPrintview();
	}

/**
* Sets if the the solution printview should be presented to the user or not
*
* @param boolean $a_details TRUE if the solution printview should be presented, FALSE otherwise
* @access public
*/
	function setShowSolutionPrintview($a_printview = 1)
	{
		if ($a_printview)
		{
			$this->results_presentation = $this->results_presentation | 4;
		}
		else
		{
			if ($this->getShowSolutionPrintview())
			{
				$this->results_presentation = $this->results_presentation ^ 4;
			}
		}
	}

/**
* Sets if the the feedback should be presented to the user in the solution or not
*
* @param boolean $a_feedback TRUE if the feedback should be presented in the solution, FALSE otherwise
* @access public
*/
	function setShowSolutionFeedback($a_feedback = TRUE)
	{
		if ($a_feedback)
		{
			$this->results_presentation = $this->results_presentation | 8;
		}
		else
		{
			if ($this->getShowSolutionFeedback())
			{
				$this->results_presentation = $this->results_presentation ^ 8;
			}
		}
	}

	/**
	* Set to true, if the full solution (including the ILIAS content pages) should be shown in the solution output
	*
	* @param boolean $a_full TRUE if the full solution should be shown in the solution output, FALSE otherwise
	* @access public
	*/
		function setShowSolutionAnswersOnly($a_full = TRUE)
		{
			if ($a_full)
			{
				$this->results_presentation = $this->results_presentation | 16;
			}
			else
			{
				if ($this->getShowSolutionAnswersOnly())
				{
					$this->results_presentation = $this->results_presentation ^ 16;
				}
			}
		}

		/**
		* Set to TRUE, if the signature field should be shown in the solution
		*
		* @param boolean $a_signature TRUE if the signature field should be shown, FALSE otherwise
		* @access public
		*/
		function setShowSolutionSignature($a_signature = FALSE)
		{
			if ($a_signature)
			{
				$this->results_presentation = $this->results_presentation | 32;
			}
			else
			{
				if ($this->getShowSolutionSignature())
				{
					$this->results_presentation = $this->results_presentation ^ 32;
				}
			}
		}

	/**
	* Set to TRUE, if the suggested solution should be shown in the solution
	*
	* @param boolean $a_solution TRUE if the suggested solution should be shown, FALSE otherwise
	* @access public
	*/
	function setShowSolutionSuggested($a_solution = FALSE)
	{
		if ($a_solution)
		{
			$this->results_presentation = $this->results_presentation | 64;
		}
		else
		{
			if ($this->getShowSolutionSuggested())
			{
				$this->results_presentation = $this->results_presentation ^ 64;
			}
		}
	}

	/**
	* Returns a new, unused test access code
	*
	* @return	string A new test access code
	*/
	function createNewAccessCode()
	{
		// create a 5 character code
		$codestring = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		mt_srand();
		$code = "";
		for ($i = 1; $i <=5; $i++)
		{
			$index = mt_rand(0, strlen($codestring)-1);
			$code .= substr($codestring, $index, 1);
		}
		// verify it against the database
		while ($this->isAccessCodeUsed($code))
		{
			$code = $this->createNewAccessCode();
		}
		return $code;
	}

	function isAccessCodeUsed($code)
	{
		global $ilDB;

		$query = sprintf("SELECT anonymous_id FROM tst_active WHERE test_fi = %s AND anonymous_id = %s",
			$ilDB->quote($this->getTestId() . ""),
			$ilDB->quote($code . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function _getUserIdFromActiveId($active_id)
	{
		global $ilDB;
		$query = sprintf("SELECT user_fi FROM tst_active WHERE active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row["user_fi"];
		}
		else
		{
			return -1;
		}
	}

	function getAccessCodeSession()
	{
		$id = $this->getTestId();
		if (!is_array($_SESSION["tst_access_code"]))
		{
			return "";
		}
		else
		{
			return $_SESSION["tst_access_code"]["$id"];
		}
	}

	function setAccessCodeSession($access_code)
	{
		$id = $this->getTestId();
		if (!is_array($_SESSION["tst_access_code"]))
		{
			$_SESSION["tst_access_code"] = array();
		}
		$_SESSION["tst_access_code"]["$id"] = $access_code;
	}

	function unsetAccessCodeSession()
	{
		$id = $this->getTestId();
		unset($_SESSION["tst_access_code"]["$id"]);
	}

	function getAllowedUsers()
	{
		return ($this->allowedUsers) ? $this->allowedUsers : 0;
	}

	function setAllowedUsers($a_allowed_users)
	{
		$this->allowedUsers = $a_allowed_users;
	}

	function getAllowedUsersTimeGap()
	{
		return ($this->allowedUsersTimeGap) ? $this->allowedUsersTimeGap : 0;
	}

	function setAllowedUsersTimeGap($a_allowed_users_time_gap)
	{
		$this->allowedUsersTimeGap = $a_allowed_users_time_gap;
	}

	function checkMaximumAllowedUsers()
	{
		global $ilDB;

		$nr_of_users = $this->getAllowedUsers();
		$time_gap = ($this->getAllowedUsersTimeGap()) ? $this->getAllowedUsersTimeGap() : 60;
		if (($nr_of_users > 0) && ($time_gap > 0))
		{
			$now = mktime();
			$time_border = $now - $time_gap;
			$str_time_border = strftime("%Y%m%d%H%M%S", $time_border);
			$result = $ilDB->queryF("SELECT DISTINCT tst_times.active_fi FROM tst_times, tst_active WHERE tst_times.tstamp > %s AND tst_times.active_fi = tst_active.active_id AND tst_active.test_fi = %s",
				array('integer', 'integer'),
				array($time_border, $this->getTestId())
			);
			if ($result->numRows() >= $nr_of_users)
			{
				include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
				if (ilObjAssessmentFolder::_enabledAssessmentLogging())
				{
					$this->logAction($this->lng->txtlng("assessment", "log_could_not_enter_test_due_to_simultaneous_users", ilObjAssessmentFolder::_getLogLanguage()));
				}
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		return TRUE;
	}

	function _getLastAccess($active_id)
	{
		global $ilDB;

		$query = sprintf("SELECT finished FROM tst_times WHERE active_fi = %s ORDER BY finished DESC",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row["finished"];
		}
		return "";
	}

	/**
	* Checks if a given string contains HTML or not
	*
	* @param string $a_text Text which should be checked
	* @return boolean
	* @access public
	*/
	function isHTML($a_text)
	{
		if (preg_match("/<[^>]*?>/", $a_text))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* Reads an QTI material tag an creates a text string
	*
	* @param string $a_material QTI material tag
	* @return string text or xhtml string
	* @access public
	*/
	function QTIMaterialToString($a_material)
	{
		$result = "";
		for ($i = 0; $i < $a_material->getMaterialCount(); $i++)
		{
			$material = $a_material->getMaterial($i);
			if (strcmp($material["type"], "mattext") == 0)
			{
				$result .= $material["material"]->getContent();
			}
			if (strcmp($material["type"], "matimage") == 0)
			{
				$matimage = $material["material"];
				if (preg_match("/(il_([0-9]+)_mob_([0-9]+))/", $matimage->getLabel(), $matches))
				{
					// import an mediaobject which was inserted using tiny mce
					if (!is_array($_SESSION["import_mob_xhtml"])) $_SESSION["import_mob_xhtml"] = array();
					array_push($_SESSION["import_mob_xhtml"], array("mob" => $matimage->getLabel(), "uri" => $matimage->getUri()));
				}
			}
		}
		global $ilLog;
		$ilLog->write(print_r($_SESSION["import_mob_xhtml"], true));
		return $result;
	}

	/**
	* Creates a QTI material tag from a plain text or xhtml text
	*
	* @param object $a_xml_writer Reference to the ILIAS XML writer
	* @param string $a_material plain text or html text containing the material
	* @return string QTI material tag
	* @access public
	*/
	function addQTIMaterial(&$a_xml_writer, $a_material)
	{
		include_once "./Services/RTE/classes/class.ilRTE.php";
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

		$a_xml_writer->xmlStartTag("material");
		$attrs = array(
			"texttype" => "text/plain"
		);
		if ($this->isHTML($a_material))
		{
			$attrs["texttype"] = "text/xhtml";
		}
		$a_xml_writer->xmlElement("mattext", $attrs, ilRTE::_replaceMediaObjectImageSrc($a_material, 0));

		$mobs = ilObjMediaObject::_getMobsOfObject("tst:html", $this->getId());
		foreach ($mobs as $mob)
		{
			$moblabel = "il_" . IL_INST_ID . "_mob_" . $mob;
			if (strpos($a_material, "mm_$mob") !== FALSE)
			{
				$mob_obj =& new ilObjMediaObject($mob);
				$imgattrs = array(
					"label" => $moblabel,
					"uri" => "objects/" . "il_" . IL_INST_ID . "_mob_" . $mob . "/" . $mob_obj->getTitle()
				);
				$a_xml_writer->xmlElement("matimage", $imgattrs, NULL);
			}
		}
		$a_xml_writer->xmlEndTag("material");
	}

	/**
	* Prepares a string for a text area output in tests
	*
	* @param string $txt_output String which should be prepared for output
	* @access public
	*/
	function prepareTextareaOutput($txt_output, $prepare_for_latex_output = FALSE)
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		return ilUtil::prepareTextareaOutput($txt_output, $prepare_for_latex_output);
	}

	/**
	* Saves the visibility settings of the certificate
	*
	* @param integer $a_value The value for the visibility settings (0 = always, 1 = only passed,  2 = never)
	* @access private
	*/
	function saveCertificateVisibility($a_value)
	{
		global $ilDB;

		$query = sprintf("UPDATE tst_tests SET certificate_visibility = %s WHERE test_id = %s",
			$ilDB->quote($a_value),
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
	}

	/**
	* Returns the visibility settings of the certificate
	*
	* @return integer The value for the visibility settings (0 = always, 1 = only passed,  2 = never)
	* @access public
	*/
	function getCertificateVisibility()
	{
		return (strlen($this->certificate_visibility)) ? $this->certificate_visibility : 0;
	}

	/**
	* Sets the visibility settings of the certificate
	*
	* @param integer $a_value The value for the visibility settings (0 = always, 1 = only passed,  2 = never)
	* @access public
	*/
	function setCertificateVisibility($a_value)
	{
		$this->certificate_visibility = $a_value;
	}

	/**
	* Returns the anonymity status of the test
	*
	* @return integer The value for the anonymity status (0 = personalized, 1 = anonymized)
	* @access public
	*/
	function getAnonymity()
	{
		return ($this->anonymity) ? 1 : 0;
	}

	/**
	* Sets the anonymity status of the test
	*
	* @param integer $a_value The value for the anonymity status (0 = personalized, 1 = anonymized)
	* @access public
	*/
	function setAnonymity($a_value = 0)
	{
		switch ($a_value)
		{
			case 1:
				$this->anonymity = 1;
				break;
			default:
				$this->anonymity = 0;
				break;
		}
	}

	/**
	* Returns wheather the cancel test button is shown or not
	*
	* @return integer The value for the show cancel status (0 = don't show, 1 = show)
	* @access public
	*/
	function getShowCancel()
	{
		return ($this->show_cancel) ? 1 : 0;
	}

	/**
	* Sets the cancel test button status
	*
	* @param integer $a_value The value for the cancel test status (0 = don't show, 1 = show)
	* @access public
	*/
	function setShowCancel($a_value = 1)
	{
		switch ($a_value)
		{
			case 1:
				$this->show_cancel = 1;
				break;
			default:
				$this->show_cancel = 0;
				break;
		}
	}

	/**
	* Returns wheather the marker button is shown or not
	*
	* @return integer The value for the marker status (0 = don't show, 1 = show)
	* @access public
	*/
	function getShowMarker()
	{
		return ($this->show_marker) ? 1 : 0;
	}

	/**
	* Sets the marker button status
	*
	* @param integer $a_value The value for the marker status (0 = don't show, 1 = show)
	* @access public
	*/
	function setShowMarker($a_value = 1)
	{
		switch ($a_value)
		{
			case 1:
				$this->show_marker = 1;
				break;
			default:
				$this->show_marker = 0;
				break;
		}
	}

	/**
	* Returns the fixed participants status
	*
	* @return integer The value for the fixed participants status (0 = don't allow, 1 = allow)
	* @access public
	*/
	function getFixedParticipants()
	{
		return ($this->fixed_participants) ? 1 : 0;
	}

	/**
	* Sets the fixed participants status
	*
	* @param integer $a_value The value for the fixed participants status (0 = don't allow, 1 = allow)
	* @access public
	*/
	function setFixedParticipants($a_value = 1)
	{
		switch ($a_value)
		{
			case 1:
				$this->fixed_participants = 1;
				break;
			default:
				$this->fixed_participants = 0;
				break;
		}
	}

	/**
	* Returns the anonymity status of a test with a given object id
	*
	* @param int $a_obj_id The object id of the test object
	* @return integer The value for the anonymity status (0 = personalized, 1 = anonymized)
	* @access public
	*/
	function _lookupAnonymity($a_obj_id)
	{
	  global $ilDB;

	  $query = "SELECT anonymity FROM tst_tests ".
		  "WHERE obj_fi = '".$a_obj_id."'";
	  $res = $ilDB->query($query);
	  while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
	  {
		  return $row['anonymity'];
	  }
	  return 0;
	}

	/**
	* Returns the random status of a test with a given object id
	*
	* @param int $a_obj_id The object id of the test object
	* @return integer The value for the anonymity status (0 = no random, 1 = random)
	* @access public
	*/
	function _lookupRandomTestFromActiveId($active_id)
	{
	  global $ilDB;

	  $query = sprintf("SELECT tst_tests.random_test FROM tst_tests, tst_active WHERE tst_active.active_id = %s AND tst_active.test_fi = tst_tests.test_id",
			$ilDB->quote($active_id . "")
		);
	  $res = $ilDB->query($query);
	  while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
	  {
		  return $row['random_test'];
	  }
	  return 0;
	}

	/**
	* Returns the full name of a test user according to the anonymity status
	*
	* @param int $user_id The database ID of the user
	* @param boolean $overwrite_anonymity Indicates if the anonymity status should be ignored
	* @return string The full name of the user or UNKNOWN if the anonymity status is affected
	* @access public
	*/
	function userLookupFullName($user_id, $overwrite_anonymity = FALSE, $sorted_order = FALSE, $suffix = "")
	{
		if ($this->getAnonymity() && !$overwrite_anonymity)
		{
			return $this->lng->txt("unknown") . $suffix;
		}
		else
		{
			include_once './Services/User/classes/class.ilObjUser.php';
			$uname = ilObjUser::_lookupName($user_id);
			if (strlen($uname["firstname"].$uname["lastname"]) == 0) $uname["firstname"] = $this->lng->txt("deleted_user");
			if ($sorted_order)
			{
				return trim($uname["lastname"] . ", " . $uname["firstname"]) .  $suffix;
			}
			else
			{
				return trim($uname["firstname"] . " " . $uname["lastname"]) .  $suffix;
			}
		}
	}

	/**
	* Returns the "Start the Test" label for the Info page
	*
	* @param int $active_id The active id of the current user
	* @return string The "Start the Test" label
	* @access public
	*/
	function getStartTestLabel($active_id)
	{
		if ($this->getNrOfTries() == 1)
		{
			return $this->lng->txt("tst_start_test");
		}
		$active_pass = $this->_getPass($active_id);
		$res = $this->getNrOfResultsForPass($active_id, $active_pass);
		if ($res == 0)
		{
			if ($active_pass == 0)
			{
				return $this->lng->txt("tst_start_test");
			}
			else
			{
				return $this->lng->txt("tst_start_new_test_pass");
			}
		}
		else
		{
			return $this->lng->txt("tst_resume_test");
		}
	}

	/**
	* Returns the available test defaults for the active user
	*
	* @param string $sortby Sort field for the database query
	* @param string $sortorder Sort order for the database query
	* @return array An array containing the defaults
	* @access public
	*/
	function &getAvailableDefaults($sortby = "name", $sortorder = "asc")
	{
		global $ilDB;
		global $ilUser;
		
		$query = sprintf("SELECT * FROM tst_test_defaults WHERE user_fi = %s ORDER BY $sortby $sortorder",
			$ilDB->quote($ilUser->getId() . "")
		);
		$result = $ilDB->query($query);
		$defaults = array();
		while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$defaults[$row["test_defaults_id"]] = $row;
		}
		return $defaults;
	}
	
	/**
	* Returns the test defaults for a given id
	*
	* @param integer $test_defaults_id The database id of a test defaults dataset
	* @return array An array containing the test defaults
	* @access public
	*/
	function &getTestDefaults($test_defaults_id)
	{
		global $ilDB;
		
		$query = sprintf("SELECT * FROM tst_test_defaults WHERE test_defaults_id = %s",
			$ilDB->quote($test_defaults_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row;
		}
		else
		{
			return NULL;
		}
	}
	
	/**
	* Deletes the defaults for a test
	*
	* @param integer $test_default_id The database ID of the test defaults
	* @access public
	*/
	function deleteDefaults($test_default_id)
	{
		global $ilDB;
		$query = sprintf("DELETE FROM tst_test_defaults WHERE test_defaults_id = %s",
			$ilDB->quote($test_default_id . "")
		);
		$result = $ilDB->query($query);
	}
	
	/**
	* Adds the defaults of this test to the test defaults
	*
	* @param string $a_name The name of the test defaults
	* @access public
	*/
	function addDefaults($a_name)
	{
		global $ilDB;
		global $ilUser;
		$testsettings = array(
			"TitleOutput" => $this->getTitleOutput(),
			"PassScoring" => $this->getPassScoring(),
			"Introduction" => $this->getIntroduction(),
			"FinalStatement" => $this->getFinalStatement(),
			"ShowInfo" => $this->getShowInfo(),
			"ForceJS" => $this->getForceJS(),
			"CustomStyle" => $this->getCustomStyle(),
			"ShowFinalStatement" => $this->getShowFinalStatement(),
			"SequenceSettings" => $this->getSequenceSettings(),
			"ScoreReporting" => $this->getScoreReporting(),
			"InstantFeedbackSolution" => $this->getInstantFeedbackSolution(),
			"AnswerFeedback" => $this->getAnswerFeedback(),
			"AnswerFeedbackPoints" => $this->getAnswerFeedbackPoints(),
			"ResultsPresentation" => $this->getResultsPresentation(),
			"Anonymity" => $this->getAnonymity(),
			"ShowCancel" => $this->getShowCancel(),
			"ShowMarker" => $this->getShowMarker(),
			"ReportingDate" => $this->getReportingDate(),
			"NrOfTries" => $this->getNrOfTries(),
			"Kiosk" => $this->getKiosk(),
			"UsePreviousAnswers" => $this->getUsePreviousAnswers(),
			"ProcessingTime" => $this->getProcessingTime(),
			"EnableProcessingTime" => $this->getEnableProcessingTime(),
			"ResetProcessingTime" => $this->getResetProcessingTime(),
			"StartingTime" => $this->getStartingTime(),
			"EndingTime" => $this->getEndingTime(),
			"ECTSOutput" => $this->getECTSOutput(),
			"ECTSFX" => $this->getECTSFX(),
			"ECTSGrades" => $this->getECTSGrades(),
			"isRandomTest" => $this->isRandomTest(),
			"RandomQuestionCount" => $this->getRandomQuestionCount(),
			"CountSystem" => $this->getCountSystem(),
			"MCScoring" => $this->getMCScoring()
		);
		$query = sprintf("INSERT INTO tst_test_defaults (test_defaults_id, name, user_fi, defaults, marks) VALUES (NULL, %s, %s, %s, %s)",
			$ilDB->quote($a_name . ""),
			$ilDB->quote($ilUser->getId(). ""),
			$ilDB->quote(serialize($testsettings)),
			$ilDB->quote(serialize($this->mark_schema))
		);
		$result = $ilDB->query($query);
	}
	
	/**
	* Applies given test defaults to this test
	*
	* @param integer $test_defaults_id The database id of the test defaults
	* @return boolean TRUE if the application succeeds, FALSE otherwise
	* @access public
	*/
	function applyDefaults($test_defaults_id)
	{
		$total = $this->evalTotalPersons();
		$result = FALSE;
		if (($this->getQuestionCount() == 0) && ($total == 0))
		{
			// only apply if there are no questions added and not user datasets exist
			$defaults =& $this->getTestDefaults($test_defaults_id);
			$testsettings = unserialize($defaults["defaults"]);
			include_once "./Modules/Test/classes/class.assMarkSchema.php";
			$this->mark_schema = unserialize($defaults["marks"]);
			$this->setTitleOutput($testsettings["TitleOutput"]);
			$this->setPassScoring($testsettings["PassScoring"]);
			$this->setIntroduction($testsettings["Introduction"]);
			$this->setFinalStatement($testsettings["FinalStatement"]);
			$this->setShowInfo($testsettings["ShowInfo"]);
			$this->setForceJS($testsettings["ForceJS"]);
			$this->setCustomStyle($testsettings["CustomStyle"]);
			$this->setShowFinalStatement($testsettings["ShowFinalStatement"]);
			$this->setSequenceSettings($testsettings["SequenceSettings"]);
			$this->setScoreReporting($testsettings["ScoreReporting"]);
			$this->setInstantFeedbackSolution($testsettings["InstantFeedbackSolution"]);
			$this->setAnswerFeedback($testsettings["AnswerFeedback"]);
			$this->setAnswerFeedbackPoints($testsettings["AnswerFeedbackPoints"]);
			$this->setResultsPresentation($testsettings["ResultsPresentation"]);
			$this->setAnonymity($testsettings["Anonymity"]);
			$this->setShowCancel($testsettings["ShowCancel"]);
			$this->setShowMarker($testsettings["ShowMarker"]);
			$this->setReportingDate($testsettings["ReportingDate"]);
			$this->setNrOfTries($testsettings["NrOfTries"]);
			$this->setUsePreviousAnswers($testsettings["UsePreviousAnswers"]);
			$this->setProcessingTime($testsettings["ProcessingTime"]);
			$this->setResetProcessingTime($testsettings["ResetProcessingTime"]);
			$this->setEnableProcessingTime($testsettings["EnableProcessingTime"]);
			$this->setStartingTime($testsettings["StartingTime"]);
			$this->setKiosk($testsettings["Kiosk"]);
			$this->setEndingTime($testsettings["EndingTime"]);
			$this->setECTSOutput($testsettings["ECTSOutput"]);
			$this->setECTSFX($testsettings["ECTSFX"]);
			$this->setECTSGrades($testsettings["ECTSGrades"]);
			$this->setRandomTest($testsettings["isRandomTest"]);
			$this->setRandomQuestionCount($testsettings["RandomQuestionCount"]);
			$this->setCountSystem($testsettings["CountSystem"]);
			$this->setMCScoring($testsettings["MCScoring"]);
			$this->saveToDb();
			$result = TRUE;
		}
		return $result;
	}

	/**
	* Convert a print output to XSL-FO
	*
	* @param string $print_output The print output
	* @return string XSL-FO code
	* @access public
	*/
	function processPrintoutput2FO($print_output)
	{
		if (extension_loaded("tidy"))
		{
			$config = array(
				"indent"         => false,
				"output-xml"     => true,
				"numeric-entities" => true
			);
			$tidy = new tidy();
			$tidy->parseString($print_output, $config, 'utf8');
			$tidy->cleanRepair();
			$print_output = tidy_get_output($tidy);
			$print_output = preg_replace("/^.*?(<html)/", "\\1", $print_output);
		}
		else
		{
			$print_output = str_replace("&nbsp;", "&#160;", $print_output);
			$print_output = str_replace("&otimes;", "X", $print_output);
		}

		$xsl = file_get_contents("./Modules/Test/xml/question2fo.xsl");
		$args = array( '/_xml' => $print_output, '/_xsl' => $xsl );
		$xh = xslt_create();
		$params = array();
		$output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", NULL, $args, $params);
		xslt_error($xh);
		xslt_free($xh);
		return $output;
	}
	
	/**
	* Delivers a PDF file from XHTML
	*
	* @param string $html The XHTML string
	* @access public
	*/
	public function deliverPDFfromHTML($content, $title = NULL)
	{
		$content = preg_replace("/href=\".*?\"/", "", $content);
		$printbody = new ilTemplate("tpl.il_as_tst_print_body.html", TRUE, TRUE, "Modules/Test");
		$printbody->setVariable("TITLE", ilUtil::prepareFormOutput($this->getTitle()));
		$printbody->setVariable("ADM_CONTENT", $content);
		$printbody->setCurrentBlock("css_file");
		$printbody->setVariable("CSS_FILE", $this->getTestStyleLocation("filesystem"));
		$printbody->parseCurrentBlock();
		$printbody->setCurrentBlock("css_file");
		$printbody->setVariable("CSS_FILE", ilUtil::getStyleSheetLocation("filesystem", "delos.css"));
		$printbody->parseCurrentBlock();
		$printoutput = $printbody->get();
		$html = str_replace("href=\"./", "href=\"" . ILIAS_HTTP_PATH . "/", $printoutput);
		$html = preg_replace("/<div id=\"dontprint\">.*?<\\/div>/ims", "", $html);
		if (extension_loaded("tidy"))
		{
			$config = array(
				"indent"         => false,
				"output-xml"     => true,
				"numeric-entities" => true
			);
			$tidy = new tidy();
			$tidy->parseString($html, $config, 'utf8');
			$tidy->cleanRepair();
			$html = tidy_get_output($tidy);
			$html = preg_replace("/^.*?(<html)/", "\\1", $html);
		}
		else
		{
			$html = str_replace("&nbsp;", "&#160;", $html);
			$html = str_replace("&otimes;", "X", $html);
		}
		
		// remove the following two lines if the new HTML2PDF RPC function works
		$this->deliverPDFfromFO($this->processPrintoutput2FO($html), $title);
		return;
		
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		include_once "./Services/Transformation/classes/class.ilHTML2PDF.php";
		$html2pdf = new ilHTML2PDF();
		$html2pdf->setHTMLString($html);
		$result = $html2pdf->send();
		$filename = (strlen($title)) ? $title : $this->getTitle();
		ilUtil::deliverData($result, ilUtil::getASCIIFilename($filename) . ".pdf", "application/pdf");
	}
	
	/**
	* Delivers a PDF file from a XSL-FO string
	*
	* @param string $fo The XSL-FO string
	* @access public
	*/
	public function deliverPDFfromFO($fo, $title = null)
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$fo_file = ilUtil::ilTempnam() . ".fo";
		$fp = fopen($fo_file, "w"); fwrite($fp, $fo); fclose($fp);
		include_once "./Services/Transformation/classes/class.ilFO2PDF.php";
		$fo2pdf = new ilFO2PDF();
		$fo2pdf->setFOString($fo);
		$result = $fo2pdf->send();
		$filename = (strlen($title)) ? $title : $this->getTitle();
		ilUtil::deliverData($result, ilUtil::getASCIIFilename($filename) . ".pdf", "application/pdf", false, true);
	}
	
	/**
	* Retrieves the manual feedback for a question in a test
	*
	* @param integer $active_id Active ID of the user
	* @param integer $question_id Question ID
	* @param integer $pass Pass number
	* @return string The feedback text
	* @access public
	*/
	static function getManualFeedback($active_id, $question_id, $pass)
	{
		global $ilDB;
		$feedback = "";
		$query = sprintf("SELECT feedback FROM tst_manual_fb WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($question_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$feedback = ilRTE::_replaceMediaObjectImageSrc($row["feedback"], 1);
		}
		return $feedback;
	}
	
	/**
	* Saves the manual feedback for a question in a test
	*
	* @param integer $active_id Active ID of the user
	* @param integer $question_id Question ID
	* @param integer $pass Pass number
	* @param string $feedback The feedback text
	* @return boolean TRUE if the operation succeeds, FALSE otherwise
	* @access public
	*/
	function saveManualFeedback($active_id, $question_id, $pass, $feedback)
	{
		global $ilDB;

		$query = sprintf("DELETE FROM tst_manual_fb WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($question_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);

		if (strlen($feedback))
		{
			$query = sprintf("INSERT INTO tst_manual_fb (active_fi, question_fi, pass, feedback) VALUES (%s, %s, %s, %s)",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote($pass . ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($feedback, 0) . "")
			);
			$result = $ilDB->query($query);
			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				global $lng, $ilUser;
				include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
				$username = ilObjTestAccess::_getParticipantData($active_id);
				include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
				$this->logAction(sprintf($lng->txtlng("assessment", "log_manual_feedback", ilObjAssessmentFolder::_getLogLanguage()), $ilUser->getFullname() . " (" . $ilUser->getLogin() . ")", $username, assQuestion::_getQuestionTitle($question_id), $feedback));
			}
		}
		if (PEAR::isError($result)) 
		{
			global $ilias;
			$ilias->raiseError($result->getMessage());
		}
		else
		{
			return TRUE;
		}
	}
	
	/**
	* Returns if Javascript should be chosen for drag & drop actions
	* for the active user
	*
	* @return boolean TRUE if Javascript should be chosen, FALSE otherwise
	* @access public
	*/
	function getJavaScriptOutput()
	{
		global $ilUser;
		if (strcmp($_GET["tst_javascript"], "0") == 0) return FALSE;
		if ($this->getForceJS()) return TRUE;
		$assessmentSetting = new ilSetting("assessment");
		return ($ilUser->getPref("tst_javascript") === FALSE) ? $assessmentSetting->get("use_javascript") : $ilUser->getPref("tst_javascript");
	}
	
	/**
	* Creates the test session data for the active user
	*
	* @return object The ilTestSession object or FALSE if the creation of the object fails
	* @access public
	*/
	function &createTestSession()
	{
		global $ilUser;
		
		include_once "./Modules/Test/classes/class.ilTestSession.php";
		$testSession = FALSE;
		$testSession = new ilTestSession();
		$testSession->setTestId($this->getTestId());
		$testSession->setUserId($ilUser->getId());
		$testSession->setAnonymousId($_SESSION["tst_access_code"][$this->getTestId()]);
		$testSession->saveToDb();
		$this->testSession =& $testSession;
		return $this->testSession;
	}

	/**
	* Sets the test session data for the active user
	*
	* @param integer $active_id The active id of the active user
	* @return object The ilTestSession object or FALSE if the creation of the object fails
	* @access public
	*/
	function &setTestSession($active_id = "")
	{
		if (is_object($this->testSession) && ($this->testSession->getActiveId() > 0)) return $this->testSession;
		
		global $ilUser;
		
		include_once "./Modules/Test/classes/class.ilTestSession.php";
		$testSession = FALSE;
		if ($active_id > 0)
		{
			$testSession = new ilTestSession($active_id);
		}
		else
		{
			$testSession = new ilTestSession();
			$testSession->loadTestSession($this->getTestId(), $ilUser->getId(), $_SESSION["tst_access_code"][$this->getTestId()]);
		}
		$this->testSession =& $testSession;
		return $this->testSession;
	}
	
	/**
	* Returns the test session data for the active user
	*
	* @return object The ilTestSession object or FALSE if the creation of the object fails
	* @access public
	*/
	function &getTestSession($active_id = "")
	{
		if (is_object($this->testSession) && ($this->testSession->getActiveId() > 0)) return $this->testSession;
		return $this->setTestSession($active_id);
	}
	
	function &createTestSequence($active_id, $pass, $shuffle)
	{
		include_once "./Modules/Test/classes/class.ilTestSequence.php";
		$this->testSequence = new ilTestSequence($active_id, $pass, $this->isRandomTest());
		if (!$this->testSequence->hasSequence())
		{
			$this->testSequence->createNewSequence($this->getQuestionCount(), $shuffle);
			$this->testSequence->saveToDb();
		}
	}
	
	function &getTestSequence($active_id = "", $pass = "")
	{
		if (is_object($this->testSequence) && ($this->testSequence->getActiveId() > 0)) return $this->testSequence;
		
		include_once "./Modules/Test/classes/class.ilTestSequence.php";
		if (($active_id > 0) && (strlen($pass)))
		{
			$this->testSequence = new ilTestSequence($active_id, $pass, $this->isRandomTest());
		}
		else
		{
			$this->testSequence = new ilTestSequence($this->getTestSession()->getActiveId(), $this->getTestSession()->getPass(), $this->isRandomTest());
		}
		return $this->testSequence;
	}
	
	function hideCorrectAnsweredQuestions()
	{
		if ($this->getTestSession()->getActiveId() > 0)
		{
			$result = $this->getTestResult($this->getTestSession()->getActiveId(), $this->getTestSession()->getPass(), TRUE);
			foreach ($result as $sequence => $question)
			{
				if (is_numeric($sequence))
				{
					if ($question["reached"] == $question["max"])
					{
						$this->getTestSequence()->hideQuestion($question["qid"]);
					}
				}
			}
			$this->getTestSequence()->saveToDb();
		}
	}
	
	/**
	 * returns all test results for all participants
	 *
	 * @param array $partipants array of user ids
	 * @param boolean if true, the result will be prepared for csv output (see processCSVRow)
	 *
	 * @return array of fields, see code for column titles
	 */
	function getDetailedTestResults($participants)
	{
		$results = array();
		if (count($participants))
		{
			foreach ($participants as $active_id => $user_rec)
			{
				$row = array();
				$reached_points = 0;
				$max_points = 0;
				foreach ($this->questions as $value)
				{
					$question =& ilObjTest::_instanciateQuestion($value);
					if (is_object($question))
					{
						$max_points += $question->getMaximumPoints();
						$reached_points += $question->getReachedPoints($active_id);
						if ($max_points > 0)
						{
							$percentvalue = $reached_points / $max_points;
							if ($percentvalue < 0) $percentvalue = 0.0;
						}
						else
						{
							$percentvalue = 0;
						}
						if ($this->getAnonymity())
						{
							$user_rec->firstname = "";
							$user_rec->lastname = $this->lng->txt("unknown");
						}
						$row = array(
							"user_id"=>$user_rec->usr_id,
							"matriculation" =>  $user_rec->matriculation,
							"lastname" =>  $user_rec->lastname,
							"firstname" => $user_rec->firstname,
							"login"=>$user_rec->login,
							"question_id" => $question->getId(),
							"question_title" => $question->getTitle(),
							"reached_points" => $reached_points,
							"max_points" => $max_points
						);
						$results[] = $row;
					}
				}
			}
		}
		return $results;
	}

	/**
	* Get test Object ID for question ID
	*/
	function _lookupTestObjIdForQuestionId($a_q_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT t.obj_fi obj_id FROM tst_test_question q, tst_tests t WHERE".
			" q.test_fi = t.test_id AND q.question_fi = ".$ilDB->quote($a_q_id));
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
		return $rec["obj_id"];
	}

	/**
	* Checks wheather or not a question plugin with a given name is active
	*
	* @param string $a_pname The plugin name
	* @access public
	*/
	function isPluginActive($a_pname)
	{
		global $ilPluginAdmin;
		if ($ilPluginAdmin->isActive(IL_COMP_MODULE, "TestQuestionPool", "qst", $a_pname))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Sets additional user fields that should be shown in the user evaluation
	 *
	 * @param array $fields Array of database fields that should be shown in the evaluation
	 */
	function setEvaluationAdditionalFields($fields)
	{
		$assessmentSetting = new ilSetting("assessment");
		$assessmentSetting->set("evalFields_" . $this->getId(), serialize($fields));
	}
	
	/**
	 * Gets additional user fields that should be shown in the user evaluation
	 *
	 * @return array An array containing the database fields that should be shown in the evaluation
	 */
	function getEvaluationAdditionalFields()
	{
			$assessmentSetting = new ilSetting("assessment");
			$found = $assessmentSetting->get("evalFields_" . $this->getId());
			$fields = array();
			if (strlen($found)) $fields = unserialize($found);
			if (is_array($fields)) return $fields; else return array();
	}

	/**
	* Checks whether the certificate button could be shown on the info page or not
	*
	* @access public
	*/
	function canShowCertificate($user_id, $active_id)
	{
		if ($this->canShowTestResults($user_id))
		{
			$counted_pass = ilObjTest::_getResultPass($active_id);
			$result_array =& $this->getTestResult($active_id, $counted_pass);

			include_once "./Services/Certificate/classes/class.ilCertificate.php";
			include_once "./Modules/Test/classes/class.ilTestCertificateAdapter.php";
			$cert = new ilCertificate(new ilTestCertificateAdapter($this));
			if ($cert->isComplete())
			{
				$vis = $this->getCertificateVisibility();
				$showcert = FALSE;
				switch ($vis)
				{
					case 0:
						$showcert = TRUE;
						break;
					case 1:
						if ($result_array["test"]["passed"] == 1)
						{
							$showcert = TRUE;
						}
						break;
					case 2:
						$showcert = FALSE;
						break;
				}
				if ($showcert)
				{
					return TRUE;
				}
				else
				{
					return FALSE;
				}
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* Creates an associated array with all active id's for a given test and original question id
	*
	* @access public
	*/
	function getParticipantsForTestAndQuestion($test_id, $question_id)
	{
		global $ilDB;
		$query = sprintf("SELECT tst_test_result.active_fi, tst_test_result.question_fi, tst_test_result.pass FROM tst_test_result, tst_active, qpl_questions WHERE tst_active.active_id = tst_test_result.active_fi AND tst_active.test_fi = %s AND tst_test_result.question_fi = qpl_questions.question_id AND qpl_questions.original_id = %s",
			$ilDB->quote($test_id),
			$ilDB->quote($question_id)
		);
		$result = $ilDB->query($query);
		$foundusers = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if (!array_key_exists($row["active_fi"], $foundusers))
			{
				$foundusers[$row["active_fi"]] = array();
			}
			array_push($foundusers[$row["active_fi"]], array("pass" => $row["pass"], "qid" => $row["question_fi"]));
		}
		return $foundusers;
	}

	/**
	* Returns true if PDF processing is enabled, false otherwise
	*
	* @access public
	*/
	public function hasPDFProcessing()
	{
		global $ilias;
		if ((strlen($ilias->getSetting("rpc_server_host"))) && (strlen($ilias->getSetting("rpc_server_port"))))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	* Returns the aggregated test results
	*
	* @access public
	*/
	public function getAggregatedResultsData()
	{
		$data =& $this->getCompleteEvaluationData();
		$foundParticipants =& $data->getParticipants();
		$results = array("overview" => array(), "questions" => array());
		if (count($foundParticipants)) 
		{
			$results["overview"][$this->lng->txt("tst_eval_total_persons")] = count($foundParticipants);
			$total_finished = $this->evalTotalFinished();
			$results["overview"][$this->lng->txt("tst_eval_total_finished")] = $total_finished;
			$average_time = $this->evalTotalStartedAverageTime();
			$diff_seconds = $average_time;
			$diff_hours    = floor($diff_seconds/3600);
			$diff_seconds -= $diff_hours   * 3600;
			$diff_minutes  = floor($diff_seconds/60);
			$diff_seconds -= $diff_minutes * 60;
			$results["overview"][$this->lng->txt("tst_eval_total_finished_average_time")] = sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds);
			$total_passed = 0;
			$total_passed_reached = 0;
			$total_passed_max = 0;
			$total_passed_time = 0;
			foreach ($foundParticipants as $userdata)
			{
				if ($userdata->getPassed()) 
				{
					$total_passed++;
					$total_passed_reached += $userdata->getReached();
					$total_passed_max += $userdata->getMaxpoints();
					$total_passed_time += $userdata->getTimeOfWork();
				}
			}
			$average_passed_reached = $total_passed ? $total_passed_reached / $total_passed : 0;
			$average_passed_max = $total_passed ? $total_passed_max / $total_passed : 0;
			$average_passed_time = $total_passed ? $total_passed_time / $total_passed : 0;
			$results["overview"][$this->lng->txt("tst_eval_total_passed")] = $total_passed;
			$results["overview"][$this->lng->txt("tst_eval_total_passed_average_points")] = sprintf("%2.2f", $average_passed_reached) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%2.2f", $average_passed_max);
			$average_time = $average_passed_time;
			$diff_seconds = $average_time;
			$diff_hours    = floor($diff_seconds/3600);
			$diff_seconds -= $diff_hours   * 3600;
			$diff_minutes  = floor($diff_seconds/60);
			$diff_seconds -= $diff_minutes * 60;
			$results["overview"][$this->lng->txt("tst_eval_total_passed_average_time")] = sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds);
		} 

		foreach ($data->getQuestionTitles() as $question_id => $question_title)
		{
			$answered = 0;
			$reached = 0;
			$max = 0;
			foreach ($foundParticipants as $userdata)
			{
				for ($i = 0; $i <= $userdata->getLastPass(); $i++)
				{
					if (is_object($userdata->getPass($i)))
					{
						$question =& $userdata->getPass($i)->getAnsweredQuestionByQuestionId($question_id);
						if (is_array($question))
						{
							$answered++;
							$reached += $question["reached"];
							$max += $question["points"];
						}
					}
				}
			}
			$percent = $max ? $reached/$max * 100.0 : 0;
			$counter++;
			$results["questions"][$question_id] = array(
				$question_title, 
				sprintf("%.2f", $answered ? $reached / $answered : 0) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%.2f", $answered ? $max / $answered : 0),
				sprintf("%.2f", $percent) . "%",
				$answered,
				sprintf("%.2f", $answered ? $reached / $answered : 0),
				sprintf("%.2f", $answered ? $max / $answered : 0),
				$percent / 100.0
			);
		}
		return $results;
	}
	
	/**
	* Get zipped xml file for test
	*/
	function getXMLZip()
	{
		include_once("./Modules/Test/classes/class.ilTestExport.php");
		$test_exp = new ilTestExport($this, "xml");
		return $test_exp->buildExportFile();
	}
} // END class.ilObjTest

?>
