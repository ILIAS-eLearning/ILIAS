<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

/**
* Test session handler
*
* This class manages the test session for a participant
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTest
*/
class ilTestSession
{
	/**
	* The unique identifier of the test session
	*
	* @var integer
	*/
	var $active_id;

	/**
	* The user id of the participant
	*
	* @var integer
	*/
	var $user_id;

	/**
	* The anonymous id of the participant
	*
	* @var integer
	*/
	var $anonymous_id;

	/**
	* The database id of the test
	*
	* @var integer
	*/
	var $test_id;

	/**
	* The last sequence of the participant
	*
	* @var integer
	*/
	var $lastsequence;

	/**
	* Indicates if the test was submitted already
	*
	* @var boolean
	*/
	var $submitted;

	/**
	* The timestamp of the test submission
	*
	* @var string
	*/
	var $submittedTimestamp;

	/**
	* ilTestSession constructor
	*
	* The constructor takes possible arguments an creates an instance of 
	* the ilTestSession object.
	*
	* @access public
	*/
	function ilTestSession($active_id = "")
	{
		$this->active_id = 0;
		$this->user_id = 0;
		$this->anonymous_id = 0;
		$this->test_id = 0;
		$this->lastsequence = 0;
		$this->submitted = FALSE;
		$this->submittedTimestamp = "";
		$this->pass = 0;
		if ($active_id > 0)
		{
			$this->loadFromDb($active_id);
		}
	}
	
	function saveToDb()
	{
		global $ilDB, $ilLog;
		
		$submitted = ($this->isSubmitted()) ? 1 : 0;
		if ($this->active_id > 0)
		{
			$affectedRows = $ilDB->manipulateF("UPDATE tst_active SET lastindex = %s, tries = %s, submitted = %s, submittimestamp = %s, tstamp = %s WHERE active_id = %s",
				array('integer', 'integer', 'integer', 'timestamp', 'integer', 'integer'),
				array(
					$this->getLastSequence(),
					$this->getPass(),
					$submitted,
					(strlen($this->getSubmittedTimestamp())) ? $this->getSubmittedTimestamp() : NULL,
					time(),
					$this->getActiveId()
				)
			);
			$result = $ilDB->query($query);
		}
		else
		{
			$anonymous_id = ($this->getAnonymousId()) ? $this->getAnonymousId() : NULL;
			$next_id = $ilDB->nextId('tst_active');
			$affectedRows = $ilDB->manipulateF("INSERT INTO tst_active (active_id, user_fi, anonymous_id, test_fi, lastindex, tries, submitted, submittimestamp, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
				array('integer', 'integer', 'text', 'integer', 'integer', 'integer', 'integer', 'timestamp', 'integer'),
				array(
					$next_id,
					$this->getUserId(),
					$anonymous_id,
					$this->getTestId(),
					$this->getLastSequence(),
					$this->getPass(),
					$submitted,
					(strlen($this->getSubmittedTimestamp())) ? $this->getSubmittedTimestamp() : NULL,
					time()
				)
			);
			$result = $ilDB->query($query);
			$this->active_id = $next_id;
		}
	}
	
	function loadTestSession($test_id, $user_id = "", $anonymous_id = "")
	{
		global $ilDB;
		global $ilUser;

		if (!$user_id)
		{
			$user_id = $ilUser->getId();
		}
		if (($_SESSION["AccountId"] == ANONYMOUS_USER_ID) && (strlen($_SESSION["tst_access_code"][$test_id])))
		{
			$result = $ilDB->queryF("SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s",
				array('integer','integer','text'),
				array($user_id, $test_id, $_SESSION["tst_access_code"][$test_id])
			);
		}
		else if (strlen($anonymous_id))
		{
			$result = $ilDB->queryF("SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s",
				array('integer','integer','text'),
				array($user_id, $test_id, $anonymous_id)
			);
		}
		else
		{
			if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
			{
				return NULL;
			}
			$result = $ilDB->queryF("SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s",
				array('integer','integer'),
				array($user_id, $test_id)
			);
		}
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
			$this->active_id = $row["active_id"];
			$this->user_id = $row["user_fi"];
			$this->anonymous_id = $row["anonymous_id"];
			$this->test_id = $row["test_fi"];
			$this->lastsequence = $row["lastindex"];
			$this->pass = $row["tries"];
			$this->submitted = ($row["submitted"]) ? TRUE : FALSE;
			$this->submittedTimestamp = $row["submittimestamp"];
		}
	}
	
	/**
	* Loads the session data for a given active id
	*
	* @param integer $active_id The database id of the test session
	* @access private
	*/
	private function loadFromDb($active_id)
	{
		global $ilDB;
		$result = sprintf("SELECT * FROM tst_active WHERE active_id = %s", 
			array('integer'),
			array($active_id)
		);
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
			$this->active_id = $row["active_id"];
			$this->user_id = $row["user_fi"];
			$this->anonymous_id = $row["anonymous_id"];
			$this->test_id = $row["test_fi"];
			$this->lastsequence = $row["lastindex"];
			$this->pass = $row["tries"];
			$this->submitted = ($row["submitted"]) ? TRUE : FALSE;
			$this->submittedTimestamp = $row["submittimestamp"];
		}
	}
	
	function getActiveId()
	{
		return $this->active_id;
	}
	
	function setUserId($user_id)
	{
		$this->user_id = $user_id;
	}
	
	function getUserId()
	{
		return $this->user_id;
	}
	
	function setTestId($test_id)
	{
		$this->test_id = $test_id;
	}
	
	function getTestId()
	{
		return $this->test_id;
	}
	
	function setAnonymousId($anonymous_id)
	{
		$this->anonymous_id = $anonymous_id;
	}
	
	function getAnonymousId()
	{
		return $this->anonymous_id;
	}
	
	function setLastSequence($lastsequence)
	{
		$this->lastsequence = $lastsequence;
	}
	
	function getLastSequence()
	{
		return $this->lastsequence;
	}
	
	function setPass($pass)
	{
		$this->pass = $pass;
	}
	
	function getPass()
	{
		return $this->pass;
	}
	
	function increasePass()
	{
		$this->pass += 1;
	}
	
	function isSubmitted()
	{
		return $this->submitted;
	}
	
	function setSubmitted()
	{
		$this->submitted = TRUE;
	}
	
	function getSubmittedTimestamp()
	{
		return $this->submittedTimestamp;
	}
	
	function setSubmittedTimestamp()
	{
		$this->submittedTimestamp = strftime("%Y-%m-%d %H:%M:%S");
	}
}

?>
