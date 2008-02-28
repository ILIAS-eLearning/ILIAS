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

	function saveQuestionResult($sid,$user_id,$test_id,$question_id,$pass,$solution)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		// Include main header
		include_once './include/inc.header.php';
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$active_id = ilObjTest::_getActiveIdOfUser($user_id, $test_id);
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
		$saved_solutions = FALSE;
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
			$saved_solutions = TRUE;
			$ilDB->query($query);
		}
		return $saved_solutions;
	}

	function saveQuestion($sid,$active_id,$question_id,$pass,$solution)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
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

	function getQuestionSolution($sid,$active_id,$question_id,$pass)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			
		$solution = array();
		// Include main header
		include_once './include/inc.header.php';
		$ilDB = $GLOBALS['ilDB'];
		if (($active_id > 0) && ($question_id > 0) && (strlen($pass) > 0))
		{
			$query = sprintf("SELECT * FROM tst_solutions ".
				"WHERE active_fi = %s AND ".
				"question_fi = %s AND ".
				"pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote($pass . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
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
	 * get results of test
	 *
	 * @param string $sid
	 * @param int $test_ref_id
	 *
	 * @return XMLResultSet with columns firstname, lastname, matriculation, maximum points, received points
	 */

	function getTestResults ($sid, $test_ref_id) {
	    if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		if(!strlen($test_ref_id))
		{
			return $this->__raiseError('No test id given. Aborting!',
									   'Client');
		}
		
		if(ilObject::_isInTrash($test_ref_id))
		{
			return $this->__raiseError('Test is trashed. Aborting!',
									   'Client');
		}
	    include_once './include/inc.header.php';
		global $rbacsystem, $tree, $ilLog;

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

		include_once './Modules/Test/classes/class.ilObjTest.php';
		$test_obj = new IlObjTest($obj_id, false);
		$participants =  $test_obj->getTestParticipants();
   	    $data =  $test_obj->getAllTestResults($participants, false);

   	     // store into xml result set
		include_once './webservice/soap/classes/class.ilXMLResultSet.php';
		include_once './webservice/soap/classes/class.ilXMLResultSetWriter.php';
   	    // create xml
	    $xmlResultSet = new ilXMLResultSet();
	    $xmlResultSet->addColumn("firstname");
	    $xmlResultSet->addColumn("lastname");
	    $xmlResultSet->addColumn("matriculation");
	    $xmlResultSet->addColumn("maximum_points");
	    $xmlResultSet->addColumn("received_points");

	    // skip titles
	    $titles = array_shift($data);
	    foreach ($data as $row) {
            $xmlRow = new ilXMLResultSetRow();
            $xmlRow->setValue(0, $row["firstname"]);
            $xmlRow->setValue(1, $row["lastname"]);
            $xmlRow->setValue(2, $row["matriculation"]);
            $xmlRow->setValue(3, $row["max_points"]);
            $xmlRow->setValue(4, $row["reached_points"]);
            $xmlResultSet->addRow($xmlRow);
		}

		// create writer
		$xmlWriter = new ilXMLResultSetWriter($xmlResultSet);
		$xmlWriter->start();

		return $xmlWriter->getXML();
	} 
}
?>