<?php
  /*
   +-----------------------------------------------------------------------------+
   | ILIAS open source                                                           |
   +-----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
   * Test & Assessment Soap functions
   *
   * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
   * @version $Id$
   *
   * @package ilias
   */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapTestAdministration extends ilSoapAdministration
{
	function ilSoapTestAdministration()
	{
		parent::ilSoapAdministration();
	}
	
	function isAllowedCall($sid, $active_id)
	{
		global $ilDB;
		
		$statement = $ilDB->prepare("SELECT * FROM tst_times WHERE active_fi = ? ORDER BY started DESC",
			array(
				"integer"
			)
		);
		$result = $ilDB->execute($statement, 
			array(
				$active_id
			)
		);
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
			if (preg_match("/(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})/", $row["started"], $matches))
			{
				$time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
				$now = time();
				$diff = $now - $time;
				$client = explode("::", $sid);
				include_once './include/inc.header.php';
				global $ilClientIniFile;
				$expires = $ilClientIniFile->readVariable('session','expire');
				if ($diff <= $expires)
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

	function saveQuestion($sid,$active_id,$question_id,$pass,$solution)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		if (!$this->isAllowedCall($sid, $active_id))
		{
			return $this->__raiseError("The required user information is only available for active users.", "");
		}
		
		if (is_array($solution) && (array_key_exists("item", $solution))) $solution = $solution["item"];

		// Include main header
		include_once './include/inc.header.php';
		$ilDB = $GLOBALS['ilDB'];
		if (($active_id > 0) && ($question_id > 0) && (strlen($pass) > 0))
		{
			$deletequery = sprintf("DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote($pass . "")
			);
			$ilDB->query($deletequery);
		}
		for($i = 0; $i < count($solution); $i += 3)
		{
			$query = sprintf("INSERT INTO tst_solutions ".
				"SET active_fi = %s, ".
				"question_fi = %s, ".
				"value1 = %s, ".
				"value2 = %s, ".
				"points = %s, ".
				"pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote($solution[$i]),
				$ilDB->quote($solution[$i+1]),
				$ilDB->quote($solution[$i+2]),
				$ilDB->quote($pass . "")
			);
			$ilDB->query($query);
		}
		return true;
	}

	/**
	 * Save the solution of a question
	 *
	 * @param string $sid Session ID
	 * @param long $active_id Active user ID
	 * @param integer $question_id Question ID
	 * @param integer $pass Test pass
	 * @param string $solution XML string containing the solution
	 *
	 * @return array String array containing the question solution (in triplets of value1, value2, points)
	 */
	function saveQuestionSolution($sid,$active_id,$question_id,$pass,$solution)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		if (!$this->isAllowedCall($sid, $active_id))
		{
			return $this->__raiseError("The required user information is only available for active users.", "");
		}
		
		$solutions = array();
		if (preg_match("/<values>(.*?)<\\/values>/is", $solution, $matches))
		{
			if (preg_match_all("/<value>(.*?)<\\/value><value>(.*?)<\\/value><points>(.*?)<\\/points>/is", $solution, $matches, PREG_SET_ORDER))
			{
				foreach ($matches as $match)
				{
					if (count($match) == 4)
					{
						for ($i = 1; $i < count($match); $i++)
						{
							array_push($solutions, trim($match[$i]));
						}
					}
				}
			}
		}

		if (count($solutions) == 0)
		{
			return $this->__raiseError("Wrong solution data. ILIAS did not find one or more solution triplets: $solution", "");
		}
		
		// Include main header
		include_once './include/inc.header.php';
		$ilDB = $GLOBALS['ilDB'];
		if (($active_id > 0) && ($question_id > 0) && (strlen($pass) > 0))
		{
			$deletequery = sprintf("DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote($pass . "")
			);
			$ilDB->query($deletequery);
		}
		for($i = 0; $i < count($solutions); $i += 3)
		{
			$query = sprintf("INSERT INTO tst_solutions ".
				"SET active_fi = %s, ".
				"question_fi = %s, ".
				"value1 = %s, ".
				"value2 = %s, ".
				"points = %s, ".
				"pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote($solutions[$i]),
				$ilDB->quote($solutions[$i+1]),
				$ilDB->quote($solutions[$i+2]),
				$ilDB->quote($pass . "")
			);
			$ilDB->query($query);
		}
		return "TRUE";
	}

	/**
	 * Get the the answers of a given question and pass for a given user
	 *
	 * @param string $sid Session ID
	 * @param long $active_id Active user ID
	 * @param integer $question_id Question ID
	 * @param integer $pass Test pass
	 *
	 * @return array String array containing the question solution (in triplets of value1, value2, points)
	 */
	function getQuestionSolution($sid,$active_id,$question_id,$pass)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		if (!$this->isAllowedCall($sid, $active_id))
		{
			return $this->__raiseError("The required user information is only available for active users.", "");
		}
		$solution = array();
		// Include main header
		global $ilDB;

		$use_previous_answers = 1;

		$statement = $ilDB->prepare("SELECT tst_tests.use_previous_answers FROM tst_tests, tst_active WHERE tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = ?",
			array(
				"integer"
			)
		);
		$result = $ilDB->execute($statement, 
			array(
				$active_id
			)
		);
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
			$use_previous_answers = $row["use_previous_answers"];
		}
		$lastpass = 0;
		if ($use_previous_answers)
		{
			$statement = $ilDB->prepare("SELECT MAX(pass) as maxpass FROM tst_test_result WHERE active_fi = ? AND question_fi = ?",
				array(
					"integer",
					"integer"
				)
			);
			$result = $ilDB->execute($statement, 
				array(
					$active_id,
					$question_id
				)
			);
			if ($result->numRows() == 1)
			{
				$row = $ilDB->fetchAssoc($result);
				$lastpass = $row["maxpass"];
			}
		}
		else
		{
			$lastpass = $pass;
		}

		if (($active_id > 0) && ($question_id > 0) && (strlen($lastpass) > 0))
		{
			$statement = $ilDB->prepare("SELECT * FROM tst_solutions WHERE active_fi = ? AND question_fi = ? AND pass = ?",
				array(
					"integer",
					"integer",
					"integer"
				)
			);
			$result = $ilDB->execute($statement, 
				array(
					$active_id,
					$question_id,
					$lastpass
				)
			);
			if ($result->numRows())
			{
				while ($row = $ilDB->fetchAssoc($result))
				{
					array_push($solution, $row["value1"]);
					array_push($solution, $row["value2"]);
					array_push($solution, $row["points"]);
				}
			}
		}
		return $solution;
	}
	
	/**
	 * get active user data
	 *
	 * @param string $sid
	 * @param long $active_id
	 *
	 * @return array String array containing fullname, title, firstname, lastname, login
	 */
	function getTestUserData($sid, $active_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		if (!$this->isAllowedCall($sid, $active_id))
		{
			return $this->__raiseError("The required user information is only available for active users.", "");
		}

		global $lng, $ilDB;

		$statement = $ilDB->prepare("SELECT user_fi, test_fi FROM tst_active WHERE active_id = ?",
			array(
				"integer"
			)
		);
		$result = $ilDB->execute($statement, 
			array(
				$active_id
			)
		);
		$row = $ilDB->fetchAssoc($result);
		$user_id = $row["user_fi"];
		$test_id = $row["test_fi"];

		$statement = $ilDB->prepare("SELECT anonymity FROM tst_tests WHERE test_id = ?",
			array(
				"integer"
			)
		);
		$result = $ilDB->execute($statement, 
			array(
				$test_id
			)
		);
		$row = $ilDB->fetchAssoc($result);
		$anonymity = $row["anonymity"];
		
		$statement = $ilDB->prepare("SELECT firstname, lastname, title, login FROM usr_data WHERE usr_id = ?",
			array(
				"integer"
			)
		);
		$result = $ilDB->execute($statement, 
			array(
				$user_id
			)
		);

		$userdata = array();
		if ($result->numRows() == 0)
		{
			$userdata["fullname"] = $lng->txt("deleted_user");
			$userdata["title"] = "";
			$userdata["firstname"] = "";
			$userdata["lastname"] = $lng->txt("anonymous");
			$userdata["login"] = "";
		}
		else
		{
			$data = $ilDB->fetchAssoc($result);
			if (($user_id == ANONYMOUS_USER_ID) || ($anonymity))
			{
				$userdata["fullname"] = $lng->txt("anonymous");
				$userdata["title"] = "";
				$userdata["firstname"] = "";
				$userdata["lastname"] = $lng->txt("anonymous");
				$userdata["login"] = "";
			}
			else
			{
				$userdata["fullname"] = trim($data["title"] . " " . $data["firstname"] . " " . $data["lastname"]);
				$userdata["title"] = $data["title"];
				$userdata["firstname"] = $data["firstname"];
				$userdata["lastname"] = $data["lastname"];
				$userdata["login"] = $data["login"];
			}
		}
		return array_values($userdata);
	}
	
	/**
	 * get active user data
	 *
	 * @param string $sid Session ID
	 * @param long $active_id Active user ID
	 * @param integer $question_id Question ID
	 * @param integer $pass Test pass
	 *
	 * @return integer Question position in the given test pass
	 */
	function getPositionOfQuestion($sid, $active_id, $question_id, $pass)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		if (!$this->isAllowedCall($sid, $active_id))
		{
			return $this->__raiseError("The required user information is only available for active users.", "");
		}

		global $lng, $ilDB;

		$statement = $ilDB->prepare("SELECT tst_tests.random_test FROM tst_active, tst_tests WHERE tst_active.active_id = ? AND tst_tests.test_id = tst_active.test_fi",
			array(
				"integer"
			)
		);
		$result = $ilDB->execute($statement, 
			array(
				$active_id
			)
		);
		if ($result->numRows() != 1) return -1;
		$row = $ilDB->fetchAssoc($result);
		$is_random = $row["random_test"];

		include_once "./Modules/Test/classes/class.ilTestSequence.php";
		$sequence = new ilTestSequence($active_id, $pass, $is_random);
		return $sequence->getSequenceForQuestion($question_id);
	}
	
	/**
	 * Returns the previous reached points in a given pass
	 *
	 * @param string $sid Session ID
	 * @param long $active_id Active user ID
	 * @param integer $question_id Question ID
	 * @param integer $pass Test pass
	 *
	 * @return array Reached points of the previous questions in this pass
	 */
	function getPreviousReachedPoints($sid, $active_id, $question_id, $pass)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		if (!$this->isAllowedCall($sid, $active_id))
		{
			return $this->__raiseError("The required user information is only available for active users.", "");
		}

		global $lng, $ilDB;

		$statement = $ilDB->prepare("SELECT tst_tests.random_test FROM tst_active, tst_tests WHERE tst_active.active_id = ? AND tst_tests.test_id = tst_active.test_fi",
			array(
				"integer"
			)
		);
		$result = $ilDB->execute($statement, 
			array(
				$active_id
			)
		);
		if ($result->numRows() != 1) return -1;
		$row = $ilDB->fetchAssoc($result);
		$is_random = $row["random_test"];

		include_once "./Modules/Test/classes/class.ilTestSequence.php";
		$sequence = new ilTestSequence($active_id, $pass, $is_random);
		$statement = $ilDB->prepare("SELECT question_fi, points FROM tst_test_result WHERE active_fi = ? AND pass = ?",
			array(
				"integer",
				"integer"
			)
		);
		$result = $ilDB->execute($statement, 
			array(
				$active_id,
				$pass
			)
		);
		$reachedpoints = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			$reachedpoints[$row["question_fi"]] = $row["points"];
		}
		$atposition = FALSE;
		$pointsforposition = array();
		foreach ($sequence->getUserSequence() as $seq)
		{
			if (!$atposition)
			{
				$qid = $sequence->getQuestionForSequence($seq);
				if ($qid == $question_id)
				{
					$atposition = TRUE;
				}
				else
				{
					array_push($pointsforposition, $reachedpoints[$qid]);
				}
			}
		}
		return $pointsforposition;
	}
	
	/**
	 * Get the number of questions in a given pass for a given user
	 *
	 * @param string $sid Session ID
	 * @param long $active_id Active user ID
	 * @param integer $pass Test pass
	 *
	 * @return integer Question position in the given test pass
	 */
	function getNrOfQuestionsInPass($sid, $active_id, $pass)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		if (!$this->isAllowedCall($sid, $active_id))
		{
			return $this->__raiseError("The required user information is only available for active users.", "");
		}

		global $lng, $ilDB;

		$statement = $ilDB->prepare("SELECT tst_tests.random_test FROM tst_active, tst_tests WHERE tst_active.active_id = ? AND tst_tests.test_id = tst_active.test_fi",
			array(
				"integer"
			)
		);
		$result = $ilDB->execute($statement, 
			array(
				$active_id
			)
		);
		if ($result->numRows() != 1) return 0;
		$row = $ilDB->fetchAssoc($result);
		$is_random = $row["random_test"];

		include_once "./Modules/Test/classes/class.ilTestSequence.php";
		$sequence = new ilTestSequence($active_id, $pass, $is_random);
		return $sequence->getUserQuestionCount();
	}
	
	/**
	 * get results of test
	 *
	 * @param string $sid
	 * @param int $test_ref_id
	 * @param boolean $sum_only
	 *
	 * @return XMLResultSet with columns 
	 * 	sum only = true: user_id, login, firstname, lastname, matriculation, maximum points, received points
	 *  sum only = false: user_id, login, firstname, lastname, matriculation, question id, question title, question points, received points
	 */

	function getTestResults ($sid, $test_ref_id, $sum_only) 
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		if(!strlen($test_ref_id))
		{
			return $this->__raiseError('No test id given. Aborting!',
									   'Client');
		}
	    include_once './include/inc.header.php';
		global $rbacsystem, $tree, $ilLog;

		if(ilObject::_isInTrash($test_ref_id))
		{
			return $this->__raiseError('Test is trashed. Aborting!',
									   'Client');
		}
		
		// get obj_id
		if(!$obj_id = ilObject::_lookupObjectId($test_ref_id))
		{
			return $this->__raiseError('No test found for id: '.$test_ref_id,
									   'Client');
		}


		// Check access
		$permission_ok = false;
		foreach($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id)
		{
			if($rbacsystem->checkAccess('edit',$ref_id))
			{
				$permission_ok = true;
				break;
			}
		}
		if(!$permission_ok)
		{
			return $this->__raiseError('No permission to edit the object with id: '.$test_ref_id,
									   'Server');
		}
   	     // store into xml result set
		include_once './webservice/soap/classes/class.ilXMLResultSet.php';
		include_once './webservice/soap/classes/class.ilXMLResultSetWriter.php';
		
		$xmlResultSet = new ilXMLResultSet();
		$xmlResultSet->addColumn("user_id");
		$xmlResultSet->addColumn("login");
		$xmlResultSet->addColumn("firstname");
		$xmlResultSet->addColumn("lastname");
		$xmlResultSet->addColumn("matriculation");
		
		include_once './Modules/Test/classes/class.ilObjTest.php';
		$test_obj = new ilObjTest($obj_id, false);
		$participants =  $test_obj->getTestParticipants();
		

		if ($sum_only)  
		{
			$data =  $test_obj->getAllTestResults($participants, false);
#print_r($data);
	   	    // create xml
		    $xmlResultSet->addColumn("maximum_points");
		    $xmlResultSet->addColumn("received_points");
		   	// skip titles
	    	$titles = array_shift($data);
		    foreach ($data as $row) {
	            $xmlRow = new ilXMLResultSetRow();
	            $xmlRow->setValue(0, $row["user_id"]);
	            $xmlRow->setValue(1, $row["login"]);	            
	            $xmlRow->setValue(2, $row["firstname"]);
	            $xmlRow->setValue(3, $row["lastname"]);
	            $xmlRow->setValue(4, $row["matriculation"]);
	            $xmlRow->setValue(5, $row["max_points"]);
	            $xmlRow->setValue(6, $row["reached_points"]);
	            $xmlResultSet->addRow($xmlRow);
		    }
		} else {
			$data =  $test_obj->getDetailedTestResults($participants);
	   	    // create xml
		    $xmlResultSet->addColumn("question_id");
		    $xmlResultSet->addColumn("question_title");			
			$xmlResultSet->addColumn("maximum_points");
		    $xmlResultSet->addColumn("received_points");
		   	foreach ($data as $row) {
	            $xmlRow = new ilXMLResultSetRow();
	            $xmlRow->setValue(0, $row["user_id"]);
	            $xmlRow->setValue(1, $row["login"]);	            
	            $xmlRow->setValue(2, $row["firstname"]);
	            $xmlRow->setValue(3, $row["lastname"]);
	            $xmlRow->setValue(4, $row["matriculation"]);
	            $xmlRow->setValue(5, $row["question_id"]);
	            $xmlRow->setValue(6, $row["question_title"]);
	            $xmlRow->setValue(7, $row["max_points"]);
	            $xmlRow->setValue(8, $row["reached_points"]);
	            $xmlResultSet->addRow($xmlRow);
		    }
		}


		// create writer
		$xmlWriter = new ilXMLResultSetWriter($xmlResultSet);
		$xmlWriter->start();

		return $xmlWriter->getXML();
	}

	
}
?>