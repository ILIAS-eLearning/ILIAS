<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source															  |
	|	Dateplaner Modul														  |													
	+-----------------------------------------------------------------------------+
	| Copyright (c) 2004 ILIAS open source & University of Applied Sciences Bremen|
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
* Database Class
*
* this class manages the access to the dateplaner an ilias database
*
* @author		Timo Richter <mail@timo-richter.de>
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version      $Id$                                    
*/

class Database
{
	

	/**
	* Database Link Identifier
	* @var string
	* @access private
	*/
	var $dlI = false;

	
	/**
	* Constructor
	*/
	function database($DP_dlI)
	{
		$this->dlI 			= $DP_dlI;	//Connect to database
		$this->alluser_id	= ALLUSERID;
	}


	/**
	* Destructor
	* @return boolean
	*/
	function disconnect()
	{
		mysql_close($this->dlI);	//Closes connection to database
	}	


	/**
	* Checks if connected to database
	* @return bool
	*/
	function isConnected()
	{
		if (isset($this->dlI))	//Checks if connected to database
		{
			return true;
		}
		else
		{
			return false;
		}
	}//end function	


	/**
	* Returns the text of the error message from previous operation 
	* @return string
	*/
	function dbError()
	{
		return mysql_error();
	}//end function	


	/**
	* Deletes a changed date by id and user_id
	* @param int user_id
	* @param int date_id
	* @param int timestamp
	* @return bool
	*/
	function applyChangedDate ($a_user_id, $a_date_id, $a_timestamp)
	{
		if (isset($this->dlI) && isset($a_user_id) && isset($a_date_id) && isset($a_timestamp))	//Checks if connected to database and if all parameters are set
		{
			$result = mysql_query ("SELECT DISTINCT status FROM ".$this->dbase_cscw.".dp_changed_dates WHERE user_id = '".$a_user_id."' AND date_id = '".$a_date_id."' AND timestamp = '".$a_timestamp."'", $this->dlI);	//Gets the status of the supplied date_id from database
			$status = mysql_fetch_array($result);
			mysql_free_result ($result);
			if ($status[0] == '0')	//Status of the date = new
			{
				mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_neg_dates WHERE user_id = '".$a_user_id."' AND date_id = '".$a_date_id."' AND timestamp = '".$a_timestamp."'", $this->dlI);	//Delete all entries for this user and date from the table of negative dp_dates
				mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE user_id = '".$a_user_id."' AND date_id = '".$a_date_id."' AND timestamp = '".$a_timestamp."'", $this->dlI);	//Delete all entries for this user and date from the table of changed dp_dates
				if (mysql_errno ($this->dlI) == 0) return true;
			}
			elseif ($status[0] == '1')	//Status of the date = changed
			{
				mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_neg_dates WHERE user_id = '".$a_user_id."' AND date_id = '".$a_date_id."' AND timestamp = '".$a_timestamp."'", $this->dlI);	//Delete all entries for this user and date from the table of negative dp_dates
				mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE user_id = '".$a_user_id."' AND date_id = '".$a_date_id."' AND timestamp = '".$a_timestamp."'", $this->dlI);	//Delete all entries for this user and date from the table of changed dp_dates
				if (mysql_errno ($this->dlI) == 0) return true;
			}
			else
			{
				return true;
			}
		}
		else
		{
			return false;
		}
	}//end function
	
	
	/**
	* Changes a changed date to a negative date by user_id and date_id
	* @param int user_id
	* @param int date_id
	* @param int timestamp
	* @return bool
	*/
	function discardChangedDate ($a_user_id, $a_date_id, $a_timestamp)
	{
		if (isset($this->dlI) && isset($a_user_id) && isset($a_date_id) && isset($a_timestamp))	//Checks if connected to database and if all parameters are set
		{
			$result = mysql_query ("SELECT DISTINCT status FROM ".$this->dbase_cscw.".dp_changed_dates WHERE user_id = '".$a_user_id."' AND date_id = '".$a_date_id."' AND timestamp = '".$a_timestamp."'", $this->dlI);	//Gets the status of the supplied date_id from database
			$status = mysql_fetch_array($result);
			mysql_free_result ($result);
			if ($status[0] == '0')	//Status of the date = new
			{
				mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE user_id = '".$a_user_id."' AND date_id = '".$a_date_id."' AND timestamp = '".$a_timestamp."'", $this->dlI);	//Delete all entries for this user and date from the table of changed dp_dates
				if (mysql_errno ($this->dlI) == 0) return true;
			}
			elseif ($status[0] == '1')	//Status of the date = changed
			{
				mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE user_id = '".$a_user_id."' AND date_id = '".$a_date_id."' AND timestamp = '".$a_timestamp."'", $this->dlI);	//Delete all entries for this user and date from the table of changed dp_dates
				mysql_query ("INSERT INTO ".$this->dbase_cscw.".dp_neg_dates (id, date_id, user_id, timestamp) VALUES ('', '".$a_date_id."', '".$a_user_id."', '".$a_timestamp."')", $this->dlI);		//Inserts a negative date for this user and date into the table of negative dp_dates
				if (mysql_errno ($this->dlI) == 0) return true;
			}
			elseif ($status[0] == '2')	//Status of the date = deleted
			{
				mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE user_id = '".$a_user_id."' AND date_id = '".$a_date_id."' AND timestamp = '".$a_timestamp."'", $this->dlI);	//Delete all entries for this user and date from the table of changed dp_dates
				mysql_query ("INSERT INTO ".$this->dbase_cscw.".dp_neg_dates (id, date_id, user_id, timestamp) VALUES ('', '".$a_date_id."', '".$a_user_id."', '".$a_timestamp."')", $this->dlI);		//Inserts a negative date for this user and date into the table of negative dp_dates
				$result = mysql_query ("SELECT DISTINCT group_id FROM ".$this->dbase_cscw.".dp_dates WHERE id = '".$a_date_id."'", $this->dlI);	//Get the group_id of this date from database
				$group_id = mysql_fetch_array($result);
				mysql_free_result ($result);
				$result = mysql_query ("SELECT DISTINCT count(id) as numOfNegDates FROM ".$this->dbase_cscw.".dp_neg_dates WHERE date_id = '".$a_date_id."' AND timestamp = '0'", $this->dlI);	//Counts how many users have deleted this date
				$numOfNegDates = mysql_fetch_array($result);
				mysql_free_result ($result);
				if ($numOfNegDates[0] >= ilCalInterface::getNumOfMembers($group_id[0]))	//Checks if the number of users who have deleted this date is equal to the number of members of this group
				{
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_dates WHERE id = '".$a_date_id."'", $this->dlI);				//Deletes all traces of this date from all tables
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE date_id = '".$a_date_id."'", $this->dlI);	//			/
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_neg_dates WHERE date_id = '".$a_date_id."'", $this->dlI);		//		/
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_keywords WHERE date_id = '".$a_date_id."'", $this->dlI);		//	/
				}

				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}//end function

	
	/**
	* Fetches changed dp_dates by user_id and status
	* @param int user_id
	* @param int status
	* @return array
	*/
	function getChangedDates ($a_user_id, $a_status)
	{
		if (isset($this->dlI) && isset($a_user_id) && isset($a_status))	//Checks if connected to database and if all parameters are set
		{
			$result = mysql_query ("SELECT DISTINCT dp_dates.id as date_id, dp_dates.begin, dp_dates.end, dp_dates.shorttext, dp_dates.group_id, dp_dates.rotation, dp_changed_dates.timestamp FROM ".$this->dbase_cscw.".dp_dates, ".$this->dbase_cscw.".dp_changed_dates WHERE dp_changed_dates.user_id = '".$a_user_id."' AND dp_changed_dates.status = '".$a_status."' AND dp_changed_dates.date_id = dp_dates.id ORDER BY dp_dates.begin, dp_dates.end DESC", $this->dlI);	//Gets all information on all changed dp_dates for this user according to its status from the table of dp_dates
			$dp_dates = false;
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				$dp_dates[$i] = $row;
			}
			mysql_free_result ($result);
			return $dp_dates;
		}
		else
		{
			return false;
		}
	}//end function


	/**
	* Fetches all single dp_dates from the database
	* @param int user_id
	* @param int begin
	* @param int end
	* @param array keyword_ids
	* @return array
	*/
	function getDates ($a_user_id, $a_begin, $a_end, $a_keyword_ids)
	{
		if (isset($this->dlI) && $a_begin <= $a_end && isset($a_user_id) && isset($a_begin) && isset($a_end) && isset($a_keyword_ids))	//Checks if connected to database and if all parameters are set
		{
			$result = mysql_query ("SELECT DISTINCT dp_dates.id FROM ".$this->dbase_cscw.".dp_dates, ".$this->dbase_cscw.".dp_neg_dates WHERE dp_neg_dates.user_id = '".$a_user_id."' AND dp_neg_dates.date_id = dp_dates.id AND dp_neg_dates.timestamp = '0'", $this->dlI);	//Gets all negative dp_dates of an user from the table of negative dp_dates
			$dp_neg_dates = false;
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				$dp_neg_dates[$i] = $row[0];
			}
			mysql_free_result ($result);
			$group_ids = ilCalInterface::getMemberGroups($a_user_id);//Gets all groups in which the user is a member via the interface class

			if ($group_ids == false) $group_ids = array('-2');	//Adds a dummy to the array to avoid "Cannot implode" errors
			if ($dp_neg_dates == false) $dp_neg_dates = array('-2');	//Adds a dummy to the array to avoid "Cannot implode" errors
			if ($a_keyword_ids[0] == '*') $result = mysql_query ("SELECT DISTINCT dp_dates.id as date_id, dp_dates.begin, dp_dates.end, dp_dates.group_id, dp_dates.user_id, dp_dates.shorttext, dp_dates.text FROM ".$this->dbase_cscw.".dp_dates WHERE (dp_dates.group_id IN ('".$this->alluser_id."','".implode("','",$group_ids)."') OR dp_dates.user_id = '".$a_user_id."') AND dp_dates.id NOT IN ('-2','".implode("','",$dp_neg_dates)."') AND (dp_dates.begin <= '".$a_end."' AND dp_dates.end >= '".$a_begin."') AND NOT (dp_dates.begin < '".$a_begin."' AND dp_dates.end = '".$a_begin."') AND NOT (dp_dates.begin = '".$a_end."' AND dp_dates.end > '".$a_end."') AND dp_dates.rotation = '0' AND (dp_dates.end - dp_dates.begin != 86399) ORDER BY dp_dates.begin, dp_dates.end DESC, dp_dates.changed, dp_dates.created", $this->dlI);	//Gets all dp_dates by user and time period which have the following stats: date is NOT a full day date, date is NOT a rotation date, user is owner of date or user is member in the group of date, NOT in $dp_neg_dates
			else			 			  $result = mysql_query ("SELECT DISTINCT dp_dates.id as date_id, dp_dates.begin, dp_dates.end, dp_dates.group_id, dp_dates.user_id, dp_dates.shorttext, dp_dates.text FROM ".$this->dbase_cscw.".dp_dates, ".$this->dbase_cscw.".dp_keyword, ".$this->dbase_cscw.".dp_keywords WHERE (dp_dates.group_id IN ('".$this->alluser_id."','".implode("','",$group_ids)."') OR dp_dates.user_id = '".$a_user_id."') AND dp_dates.id NOT IN ('-2','".implode("','",$dp_neg_dates)."') AND (dp_dates.begin <= '".$a_end."' AND dp_dates.end >= '".$a_begin."') AND NOT (dp_dates.begin < '".$a_begin."' AND dp_dates.end = '".$a_begin."') AND NOT (dp_dates.begin = '".$a_end."' AND dp_dates.end > '".$a_end."') AND dp_dates.rotation = '0' AND (dp_dates.end - dp_dates.begin != 86399) AND dp_keywords.keyword_id IN ('!�$%&/=', '".implode("','",$a_keyword_ids)."') AND dp_keywords.date_id = dp_dates.id ORDER BY dp_dates.begin, dp_dates.end DESC, dp_dates.changed, dp_dates.created", $this->dlI);	//Gets all dp_dates by user, dp_keyword and time period which have the following stats: date is NOT a full day date, date is NOT a rotation date, user is owner of date or user is member in the group of date, NOT in $dp_neg_dates
			$dp_dates = false;
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				$dp_dates[$i] = $row;
			}
			mysql_free_result ($result);
			return $dp_dates;
		}
		else
		{
			return false;
		}
	}//end function


	/**
	* Fetches all single whole day dp_dates from the database
	* @param int user_id
	* @param int begin
	* @param int end
	* @param array keyword_ids
	* @return array
	*/
	function getFullDayDates ($a_user_id, $a_begin, $a_end, $a_keyword_ids)
	{
		if (isset($this->dlI) && $a_begin <= $a_end && isset($a_user_id) && isset($a_begin) && isset($a_end) && isset($a_keyword_ids))	//Checks if connected to database and if all parameters are set
		{
			$result = mysql_query ("SELECT DISTINCT dp_dates.id FROM ".$this->dbase_cscw.".dp_dates, ".$this->dbase_cscw.".dp_neg_dates WHERE dp_neg_dates.user_id = '".$a_user_id."' AND dp_neg_dates.date_id = dp_dates.id  AND dp_neg_dates.timestamp = '0'", $this->dlI);	//Gets all negative dp_dates of an user from the table of negative dp_dates
			$dp_neg_dates = false;
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				$dp_neg_dates[$i] = $row[0];
			}
			mysql_free_result ($result);
			$group_ids = ilCalInterface::getMemberGroups($a_user_id);	//Gets all groups in which the user is a member via the interface class
			if ($group_ids == false) $group_ids = array('-2');	//Adds a dummy to the array to avoid "Cannot implode" errors
			if ($dp_neg_dates == false) $dp_neg_dates = array('-2');	//Adds a dummy to the array to avoid "Cannot implode" errors
			if ($a_keyword_ids[0] == '*') $result = mysql_query ("SELECT DISTINCT dp_dates.id as date_id, dp_dates.begin, dp_dates.end, dp_dates.group_id, dp_dates.user_id, dp_dates.shorttext, dp_dates.text FROM ".$this->dbase_cscw.".dp_dates WHERE (dp_dates.group_id IN ('".$this->alluser_id."','".implode("','",$group_ids)."') OR dp_dates.user_id = '".$a_user_id."') AND dp_dates.id NOT IN ('-2','".implode("','",$dp_neg_dates)."') AND (dp_dates.begin <= '".$a_end."' AND dp_dates.end >= '".$a_begin."') AND NOT (dp_dates.begin < '".$a_begin."' AND dp_dates.end = '".$a_begin."') AND NOT (dp_dates.begin = '".$a_end."' AND dp_dates.end > '".$a_end."') AND dp_dates.rotation = '0' AND (dp_dates.end - dp_dates.begin = 86399) ORDER BY dp_dates.begin, dp_dates.end DESC, dp_dates.changed, dp_dates.created", $this->dlI);		//Gets all dp_dates by user and time period which have the following stats: is a full day date, date is NOT a rotation date, user is owner of date or user is member in the group of date, NOT in $dp_neg_dates
			else			 			  $result = mysql_query ("SELECT DISTINCT dp_dates.id as date_id, dp_dates.begin, dp_dates.end, dp_dates.group_id, dp_dates.user_id, dp_dates.shorttext, dp_dates.text FROM ".$this->dbase_cscw.".dp_dates, ".$this->dbase_cscw.".dp_keyword, ".$this->dbase_cscw.".dp_keywords WHERE (dp_dates.group_id IN ('".$this->alluser_id."','".implode("','",$group_ids)."') OR dp_dates.user_id = '".$a_user_id."') AND dp_dates.id NOT IN ('-2','".implode("','",$dp_neg_dates)."') AND (dp_dates.begin <= '".$a_end."' AND dp_dates.end >= '".$a_begin."') AND NOT (dp_dates.begin < '".$a_begin."' AND dp_dates.end = '".$a_begin."') AND NOT (dp_dates.begin = '".$a_end."' AND dp_dates.end > '".$a_end."') AND dp_dates.rotation = '0' AND (dp_dates.end - dp_dates.begin = 86399) AND dp_keywords.keyword_id IN ('!�$%&/=', '".implode("','",$a_keyword_ids)."') AND dp_keywords.date_id = dp_dates.id ORDER BY dp_dates.begin, dp_dates.end DESC, dp_dates.changed, dp_dates.created", $this->dlI);	//Gets all dp_dates by user, dp_keyword and time period which have the following stats: date is full day date, date is NOT a rotation date, user is owner of date or user is member in the group of date, NOT in $dp_neg_dates
			$dp_dates = false;
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				$dp_dates[$i] = $row;
			}
			mysql_free_result ($result);
		return $dp_dates;
		}
		else
		{
			return false;
		}
	}//end function
	
	/**
	* Fetches all rotation dp_dates from the database
	* @param int user_id
	* @param int begin
	* @param int end
	* @param array keyword_ids
	* @return array
	*/
	function getRotationDates ($a_user_id, $a_begin, $a_end, $a_keyword_ids)
	{
		if (isset($this->dlI) && $a_begin <= $a_end && isset($a_user_id) && isset($a_begin) && isset($a_end) && isset($a_keyword_ids))	//Checks if connected to database and if all parameters are set
		{
			$result = mysql_query ("SELECT DISTINCT dp_dates.id FROM ".$this->dbase_cscw.".dp_dates, ".$this->dbase_cscw.".dp_neg_dates WHERE dp_neg_dates.user_id = '".$a_user_id."' AND dp_neg_dates.timestamp = '0' AND dp_neg_dates.date_id = dp_dates.id", $this->dlI);	//Gets all negative dp_dates of an user from the table of negative dp_dates
			$dp_neg_dates = false;
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				$dp_neg_dates[$i] = $row[0];
			}
			mysql_free_result ($result);
			$group_ids = ilCalInterface::getMemberGroups($a_user_id);	//Gets all groups in which the user is a member via the interface class
			if ($group_ids == false) $group_ids = array('-2');		//Adds a dummy to the array to avoid "Cannot implode" errors
			if ($dp_neg_dates == false) $dp_neg_dates = array('-2');	//Adds a dummy to the array to avoid "Cannot implode" errors
			if ($a_keyword_ids[0] == '*') $result = mysql_query ("SELECT DISTINCT dp_dates.id as date_id, dp_dates.begin, dp_dates.end, dp_dates.group_id, dp_dates.user_id, dp_dates.shorttext, dp_dates.text, dp_dates.rotation, dp_dates.end_rotation FROM ".$this->dbase_cscw.".dp_dates WHERE (dp_dates.group_id IN ('".$this->alluser_id."','".implode("','",$group_ids)."') OR dp_dates.user_id = '".$a_user_id."') AND dp_dates.id NOT IN ('-2','".implode("','",$dp_neg_dates)."') AND (dp_dates.begin <= '".$a_end."' AND dp_dates.end_rotation >= '".$a_begin."') AND NOT (dp_dates.begin < '".$a_begin."' AND dp_dates.end_rotation = '".$a_begin."') AND NOT (dp_dates.begin = '".$a_end."' AND dp_dates.end_rotation > '".$a_end."') AND dp_dates.rotation != '0' AND (dp_dates.end - dp_dates.begin != 86399) ORDER BY dp_dates.begin, dp_dates.end DESC, dp_dates.changed, dp_dates.created", $this->dlI);	//Gets all dp_dates by user and time period which have the following stats: date is NOT a full day date, date is a rotation date, user is owner of date or user is member in the group of date, NOT in $dp_neg_dates
			else			 			  $result = mysql_query ("SELECT DISTINCT dp_dates.id as date_id, dp_dates.begin, dp_dates.end, dp_dates.group_id, dp_dates.user_id, dp_dates.shorttext, dp_dates.text, dp_dates.rotation, dp_dates.end_rotation FROM ".$this->dbase_cscw.".dp_dates, ".$this->dbase_cscw.".dp_keyword, ".$this->dbase_cscw.".dp_keywords WHERE (dp_dates.group_id IN ('".$this->alluser_id."','".implode("','",$group_ids)."') OR dp_dates.user_id = '".$a_user_id."') AND dp_dates.id NOT IN ('-2','".implode("','",$dp_neg_dates)."') AND (dp_dates.begin <= '".$a_end."' AND dp_dates.end_rotation >= '".$a_begin."') AND NOT (dp_dates.begin < '".$a_begin."' AND dp_dates.end_rotation = '".$a_begin."') AND NOT (dp_dates.begin = '".$a_end."' AND dp_dates.end_rotation > '".$a_end."') AND dp_dates.rotation != '0' AND (dp_dates.end - dp_dates.begin != 86399) AND dp_keywords.keyword_id IN ('!�$%&/=', '".implode("','",$a_keyword_ids)."') AND dp_keywords.date_id = dp_dates.id ORDER BY dp_dates.begin, dp_dates.end DESC, dp_dates.changed, dp_dates.created", $this->dlI);	//Gets all dp_dates by user, dp_keyword and time period which have the following stats: date is NOT a full day date, date is a rotation date, user is owner of date or user is member in the group of date, NOT in $dp_neg_dates
			$dp_dates = false;
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				$dp_dates[$i] = $row;
			}
			mysql_free_result ($result);
			return $dp_dates;
		}
		else
		{
			return false;
		}
	}//end function

	/**
	* Fetches all whole day rotation dp_dates from the database
	* @param int user_id
	* @param int begin
	* @param int end
	* @param array keyword_ids
	* @return array
	*/
	function getFullDayRotationDates ($a_user_id, $a_begin, $a_end, $a_keyword_ids)
	{
		if (isset($this->dlI) && $a_begin <= $a_end && isset($a_user_id) && isset($a_begin) && isset($a_end) && isset($a_keyword_ids))	//Checks if connected to database and if all parameters are set
		{
			$result = mysql_query ("SELECT DISTINCT dp_dates.id FROM ".$this->dbase_cscw.".dp_dates, ".$this->dbase_cscw.".dp_neg_dates WHERE dp_neg_dates.user_id = '".$a_user_id."' AND dp_neg_dates.timestamp = '0' AND dp_neg_dates.date_id = dp_dates.id", $this->dlI);	//Gets all negative dp_dates of an user from the table of negative dp_dates
			$dp_neg_dates = false;
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				$dp_neg_dates[$i] = $row[0];
			}
			mysql_free_result ($result);
			$group_ids = ilCalInterface::getMemberGroups($a_user_id);	//Gets all groups in which the user is a member via the interface class
			if ($group_ids == false) $group_ids = array('-2');	//Adds a dummy to the array to avoid "Cannot implode" errors
			if ($dp_neg_dates == false) $dp_neg_dates = array('-2');	//Adds a dummy to the array to avoid "Cannot implode" errors
			if ($a_keyword_ids[0] == '*') $result = mysql_query ("SELECT DISTINCT dp_dates.id as date_id, dp_dates.begin, dp_dates.end, dp_dates.group_id, dp_dates.user_id, dp_dates.shorttext, dp_dates.text, dp_dates.rotation, dp_dates.end_rotation FROM ".$this->dbase_cscw.".dp_dates WHERE (dp_dates.group_id IN ('".$this->alluser_id."','".implode("','",$group_ids)."') OR dp_dates.user_id = '".$a_user_id."') AND dp_dates.id NOT IN ('-2','".implode("','",$dp_neg_dates)."') AND (dp_dates.begin <= '".$a_end."' AND dp_dates.end_rotation >= '".$a_begin."') AND NOT (dp_dates.begin < '".$a_begin."' AND dp_dates.end_rotation = '".$a_begin."') AND NOT (dp_dates.begin = '".$a_end."' AND dp_dates.end_rotation > '".$a_end."') AND dp_dates.rotation != '0' AND (dp_dates.end - dp_dates.begin = 86399) ORDER BY dp_dates.begin, dp_dates.end DESC, dp_dates.changed, dp_dates.created", $this->dlI);	//Gets all dp_dates by user and time period which have the following stats: date is NOT a full day date, date is a rotation date, user is owner of date or user is member in the group of date, NOT in $dp_neg_dates
			else			 			  $result = mysql_query ("SELECT DISTINCT dp_dates.id as date_id, dp_dates.begin, dp_dates.end, dp_dates.group_id, dp_dates.user_id, dp_dates.shorttext, dp_dates.text, dp_dates.rotation, dp_dates.end_rotation FROM ".$this->dbase_cscw.".dp_dates, ".$this->dbase_cscw.".dp_keyword, ".$this->dbase_cscw.".dp_keywords WHERE (dp_dates.group_id IN ('".$this->alluser_id."','".implode("','",$group_ids)."') OR dp_dates.user_id = '".$a_user_id."') AND dp_dates.id NOT IN ('-2','".implode("','",$dp_neg_dates)."') AND (dp_dates.begin <= '".$a_end."' AND dp_dates.end_rotation >= '".$a_begin."') AND NOT (dp_dates.begin < '".$a_begin."' AND dp_dates.end_rotation = '".$a_begin."') AND NOT (dp_dates.begin = '".$a_end."' AND dp_dates.end_rotation > '".$a_end."') AND dp_dates.rotation != '0' AND (dp_dates.end - dp_dates.begin = 86399) AND dp_keywords.keyword_id IN ('!�$%&/=', '".implode("','",$a_keyword_ids)."') AND dp_keywords.date_id = dp_dates.id ORDER BY dp_dates.begin, dp_dates.end DESC, dp_dates.changed, dp_dates.created", $this->dlI);	//Gets all dp_dates by user, dp_keyword and time period which have the following stats: date is a full day date, date is a rotation date, user is owner of date or user is member in the group of date, NOT in $dp_neg_dates
			$dp_dates = false;
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				$dp_dates[$i] = $row;
			}
			mysql_free_result ($result);
			return $dp_dates;
		}
		else
		{
			return false;
		}
	}//end function


	/**
	* Returns all data about a date_id and the keyword_id and dp_keyword of a specific user
	* @param int date_id
	* @param int user_id
	* @return array
	*/
	function getDate ($a_date_id, $a_user_id)
	{
		if (isset($this->dlI) && isset($a_date_id) && isset($a_user_id))	//Checks if connected to database and if all parameters are set
		{
			$result = mysql_query ("SELECT DISTINCT id as date_id, begin, end, group_id, user_id, created, changed, rotation, shorttext, text, end_rotation FROM ".$this->dbase_cscw.".dp_dates WHERE id = '".$a_date_id."'", $this->dlI);	//Gets all information on a date from the table of dp_dates
			$date = false;
			$date = mysql_fetch_array($result);
			mysql_free_result ($result);
			if ($date)
			{
				$result = mysql_query ("SELECT DISTINCT dp_keyword.id as keyword_id, dp_keyword.keyword FROM ".$this->dbase_cscw.".dp_keyword, ".$this->dbase_cscw.".dp_keywords WHERE dp_keywords.date_id = '".$a_date_id."' AND dp_keyword.user_id = '".$a_user_id."' AND dp_keyword.id = dp_keywords.keyword_id", $this->dlI);	//Gets the dedicated keyword_id of the date from the table of dp_keywords
				$keyword_id = false;
				$keyword_id = mysql_fetch_array($result);
				mysql_free_result ($result);
				if ($keyword_id)
				{
					$date = array_merge ($date, $keyword_id);	//Merges the keyword_id with the other information of the date
				}
				return $date;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}//end function


	/**
	* Fetches all negative rotation dp_dates from the database
	* @param int user_id
	* @param int begin
	* @param int end
	* @return array
	*/
	function getNegRotationDates ($a_user_id, $a_begin, $a_end)
	{
		if (isset($this->dlI) && $a_begin <= $a_end && isset($a_user_id) && isset($a_begin) && isset($a_end))	//Checks if connected to database and if all parameters are set
		{
			$result = mysql_query ("SELECT DISTINCT dp_dates.id as date_id, dp_dates.begin, dp_dates.end, dp_dates.group_id, dp_dates.user_id, dp_dates.shorttext, dp_dates.text, dp_dates.rotation, dp_dates.end_rotation, dp_neg_dates.timestamp FROM ".$this->dbase_cscw.".dp_neg_dates, ".$this->dbase_cscw.".dp_dates WHERE dp_neg_dates.user_id = '".$a_user_id."' AND dp_neg_dates.timestamp != '0' AND (dp_dates.id = dp_neg_dates.date_id AND dp_dates.rotation != '0') AND dp_neg_dates.timestamp between '".$a_begin."' AND '".$a_end."' ORDER BY dp_neg_dates.timestamp", $this->dlI);	//Gets all negative rotation dp_dates by user and time period
			$dp_neg_dates = false;
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				$dp_neg_dates[$i] = $row;
			}
			mysql_free_result ($result);
			return $dp_neg_dates;
		}
		else
		{
			return false;
		}
	}//end function


	/**
	* Returns all groups which are owned by the user
	* @param int user_id
	* @return array
	*/
	function getUserGroups ($a_user_id)
	{
		if (isset($this->dlI) && isset($a_user_id))	//Checks if connected to database and if all parameters are set
		{
			$groups = ilCalInterface::getUserGroups($a_user_id);	//Forwards the request to the interface class
			return $groups;
		}
		else
		{
			return false;
		}
	}//end function
	
	
	/**
	* Adds a dp_keyword to the table of dp_keywords
	* @param int user_id
	* @param string dp_keyword
	* @return bool
	*/
	function addKeyword ($a_user_id, $a_keyword)
	{
		if (isset($this->dlI) && isset($a_user_id) && isset($a_keyword))	//Checks if connected to database and if all parameters are set
		{
			$result = mysql_query ("SELECT id FROM ".$this->dbase_cscw.".dp_keyword WHERE user_id = '".$a_user_id."' AND keyword = '".$a_keyword."'", $this->dlI);	//Verfies that this dp_keyword does not exist already
			if (mysql_fetch_array($result) == false)
			{
				mysql_query ("INSERT INTO ".$this->dbase_cscw.".dp_keyword (id, user_id, keyword) VALUES ('', '".$a_user_id."', '".$a_keyword."')", $this->dlI);	//Inserts the dp_keyword into the dp_keyword table
				if (mysql_errno ($this->dlI) == 0) return true;
				else return false;
			}
			else
			{
				return true;
			}
			mysql_free_result ($result);
		}
		else
		{
			return false;
		}
	}//end function


	/**
	* Fetches all dp_keywords associated to an user_id
	* @param int user_id
	* @return array
	*/
	function getKeywords ($a_user_id)
	{
		if (isset($this->dlI) && isset($a_user_id))	//Checks if connected to database and if all parameters are set
		{
			$result = mysql_query ("SELECT DISTINCT id as keyword_id, keyword FROM ".$this->dbase_cscw.".dp_keyword WHERE user_id = '".$a_user_id."' ORDER BY keyword", $this->dlI);	//Gets all dp_keywords by user from the dp_keyword table
			$dp_keywords = false;
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				   $dp_keywords[$i] = $row;
			}
			return $dp_keywords;
			mysql_free_result ($result);
		}
		else
		{
			return false;
		}
	}//end function


	/**
	* Deletes a dp_keyword from the table of dp_keywords
	* @param int keyword_id
	* @return bool
	*/
	function delKeyword ($a_keyword_id)
	{
		if (isset($this->dlI) && isset($a_keyword_id))	//Checks if connected to database and if all parameters are set
		{
			$return = false;
			mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_keyword WHERE id = '".$a_keyword_id."'", $this->dlI);	//Deletes the dp_keyword from the dp_keyword table
			mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_keywords WHERE keyword_id = '".$a_keyword_id."'", $this->dlI);	//Deletes all assigned entries in the table of dp_keywords
			if (mysql_errno ($this->dlI) == 0) $return = true;
		}
		else
		{
			$return = false;
		}
		return $return;
	}//end function


	/**
	* Updates a dp_keyword in the table of dp_keywords
	* @param int keyword_id
	* @param string dp_keyword
	* @return bool
	*/
	function updateKeyword ($a_keyword_id, $a_keyword)
	{
		if (isset($this->dlI) && isset($a_keyword_id) && isset($a_keyword))	//Checks if connected to database and if all parameters are set
		{
			mysql_query ("UPDATE ".$this->dbase_cscw.".dp_keyword SET keyword = '".$a_keyword."' WHERE id = '".$a_keyword_id."'", $this->dlI);	//Changes the dp_keyword in the dp_keyword table
			if (mysql_errno ($this->dlI) == 0) return true;
			else 
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}//end function


	/**
	* Fetches all dates of a group from the database
	* @param int group_id
	* @param int begin
	* @param int end
	* @return array
	*/
	function getGroupDates ($a_group_id, $a_begin, $a_end)
	{	
		if (isset($this->dlI) && $a_begin <= $a_end && isset($a_group_id) && isset($a_begin) && isset($a_end))	//Checks if connected to database and if all parameters are set
		{   
			$user_ids = ilCalInterface::getOtherMembers($a_group_id, -1);	//Gets all members of this group via the interface class
			$result = mysql_query ("SELECT DISTINCT dp_dates.id as date_id, dp_dates.begin, dp_dates.end, dp_dates.group_id, dp_dates.user_id, dp_dates.shorttext, dp_dates.text, dp_dates.rotation, dp_dates.end_rotation  FROM ".$this->dbase_cscw.".dp_dates WHERE (dp_dates.group_id = '".$a_group_id."' OR dp_dates.user_id IN ('".$this->alluser_id."','".implode("','",$user_ids)."')) AND rotation = '0' AND (dp_dates.begin <= '".$a_end."' AND dp_dates.end >= '".$a_begin."') AND NOT (dp_dates.begin < '".$a_begin."' AND dp_dates.end = '".$a_begin."')  ORDER BY begin, end DESC", $this->dlI);	//Gets all dp_dates by group and time period and all dp_dates of all members of this group which have the following stats: date is NOT a full day date, date is NOT a Rotationdate
			$dp_dates = false;

			echo(mysql_error());
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				$dp_dates[$i] = $row;
			}
			mysql_free_result ($result);

			return $dp_dates;
		}
		else
		{
			return false;
		}
	}//end function

	/**
	* Fetches all rotation dp_dates from the database
	* @param int group_id
	* @param int begin
	* @param int end
	* @return array
	*/
	function getGroupRotationDates ($a_group_id, $a_begin, $a_end)
	{
		if (isset($this->dlI) && $a_begin <= $a_end && isset($a_group_id) && isset($a_begin) && isset($a_end))	//Checks if connected to database and if all parameters are set
		{
			$user_ids = ilCalInterface::getOtherMembers($a_group_id, -1);	//Gets all members of this group via the interface class
			$result = mysql_query ("SELECT DISTINCT dp_dates.id as date_id, dp_dates.begin, dp_dates.end, dp_dates.group_id, dp_dates.user_id, dp_dates.shorttext, dp_dates.text, dp_dates.rotation, dp_dates.end_rotation  FROM ".$this->dbase_cscw.".dp_dates WHERE (dp_dates.group_id = '".$a_group_id."' OR dp_dates.user_id IN ('".$this->alluser_id."','".implode("','",$user_ids)."')) AND (dp_dates.begin <= '".$a_end."' AND dp_dates.end_rotation >= '".$a_begin."') AND NOT (dp_dates.begin < '".$a_begin."' AND dp_dates.end_rotation = '".$a_begin."') AND NOT (dp_dates.begin = '".$a_end."' AND dp_dates.end_rotation > '".$a_end."') AND dp_dates.rotation != '0' ORDER BY begin, end DESC", $this->dlI);	
			//Gets all dp_dates by group and time period and all dp_dates of all members of this group which have the following stats: date is NOT a full day date, date is NOT a Rotationdate
			$dp_dates = false;
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				$dp_dates[$i] = $row;
			}
			mysql_free_result ($result);
			return $dp_dates;
		}
		else
		{
			return false;
		}
	}//end function

	/**
	* Fetches the name of a group according to its group_id from the database
	* @param int group_ids
	* @return string
	*/
	function getGroupName ($a_group_id)
	{
		if (isset($this->dlI) && isset($a_group_id))	//Checks if connected to database and if all parameters are set
		{
			$groupname = ilCalInterface::getGroupName($a_group_id);	//Forwards the request to the interface class
			return $groupname;
		}
		else
		{
			return false;
		}
	}//end function


	/**
	* Adds starttime and endtime to the table of dp_properties
	* @param int user_id
	* @param int start
	* @param int end
	* @return bool
	*/
	function addStartEnd ($a_user_id, $a_start, $a_end)
	{
		if (isset($this->dlI) && isset($a_user_id) && isset($a_start) && isset($a_end))	//Checks if connected to database and if all parameters are set
		{
			$result = mysql_query ("SELECT id FROM ".$this->dbase_cscw.".dp_properties WHERE user_id = '".$a_user_id."' AND dv_starttime  = '".$a_start."' AND dv_endtime  = '".$a_end."'", $this->dlI);	//Verfies that this start&endtime does not exist already
			if (mysql_fetch_array($result) == false)
			{
				mysql_query ("INSERT INTO ".$this->dbase_cscw.".dp_properties (id, user_id, dv_starttime, dv_endtime) VALUES ('', '".$a_user_id."', '".$a_start."', '".$a_end."')", $this->dlI);	//Inserts the start- and endtime into the table of dp_properties
				if (mysql_errno ($this->dlI) == 0) return true;
				else return false;
			}
			else
			{
				return true;
			}
			mysql_free_result ($result);
		}
		else
		{
			return false;
		}
	}//end function


	/**
	* Fetches the starttime and endtime associated to an user_id from the table of dp_properties
	* @param int user_id
	* @return bool
	*/
	function getStartEnd ($a_user_id)
	{
		if (isset($this->dlI) && isset($a_user_id))	//Checks if connected to database and if all parameters are set
		{
			$result = mysql_query ("SELECT DISTINCT id, dv_starttime, dv_endtime FROM ".$this->dbase_cscw.".dp_properties WHERE user_id = '".$a_user_id."'", $this->dlI);	//Gets the start- and endtime for this user from the table of dp_properties
			if ($times = mysql_fetch_array($result)) return $times;
			else return false;
			mysql_free_result ($result);
		}
		else
		{
			return false;
		}
	}//end function


	/**
	* Updates the starttime and endtime from the table of dp_properties
	* @param int keyword_id
	* @param int start
	* @param int end
	* @return bool
	*/
	function updateStartEnd ($a_properties_id, $a_start, $a_end)
	{
		if (isset($this->dlI) && isset($a_properties_id) && isset($a_start) && isset($a_end))	//Checks if connected to database and if all parameters are set
		{
			mysql_query ("UPDATE ".$this->dbase_cscw.".dp_properties SET dv_starttime = '".$a_start."', dv_endtime = '".$a_end."' WHERE id = '".$a_properties_id."'", $this->dlI);	//Changes the start- and endtime in the table of dp_properties
			if (mysql_errno ($this->dlI) == 0) return true;
			else 
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}//end function


	/**
	* Adds a date to the table of dp_dates
	* @param int begin
	* @param int end
	* @param int group_id
	* @param int user_id
	* @param int created
	* @param int rotation
	* @param string shorttext
	* @param string text
	* @param int keyword_id
	* @return int error_code
	*/
	function addDate ($a_begin, $a_end, $a_group_id, $a_user_id, $a_created, $a_rotation, $a_end_rotation, $a_shorttext, $a_text, $a_keyword_id)
	{
		if (isset($this->dlI) && $a_begin <= $a_end && isset($a_begin) && isset($a_end) && isset($a_group_id) && isset($a_user_id) && isset($a_created) && isset($a_rotation) && isset($a_end_rotation) && isset($a_shorttext) && isset($a_keyword_id))	//Checks if connected to database and if all parameters are set except the text
		{
			$result = mysql_query ("SELECT begin FROM ".$this->dbase_cscw.".dp_dates WHERE begin = '".$a_begin."' AND end = '".$a_end."' AND group_id = '".$a_group_id."' AND user_id = '".$a_user_id."' AND rotation = '".$a_rotation."' AND shorttext = '".$a_shorttext."' AND text = '".$a_text."' AND end_rotation = '".$a_end_rotation."'", $this->dlI);	//Verifies that date does not exist in table of dp_dates
			$test = mysql_fetch_array($result);
			mysql_free_result ($result);
			if ($a_begin != $test[0])	//Checks if date is already in table of dp_dates (see SQL-query)
			{
				mysql_query ("INSERT INTO ".$this->dbase_cscw.".dp_dates (id, begin, end, group_id, user_id, created, changed, rotation, shorttext, text, end_rotation) VALUES ('', '".$a_begin."', '".$a_end."' , '".$a_group_id."', '".$a_user_id."', '".$a_created."' , '".$a_created."' , '".$a_rotation."' , '".$a_shorttext."', '".$a_text."', '".$a_end_rotation."')", $this->dlI);	//Inserts date into table of dp_dates
				if (mysql_errno ($this->dlI) == 0) $return = 0;
				$result = mysql_query ("SELECT id FROM ".$this->dbase_cscw.".dp_dates WHERE begin = '".$a_begin."' AND end = '".$a_end."' AND group_id = '".$a_group_id."' AND user_id = '".$a_user_id."' AND rotation = '".$a_rotation."' AND shorttext = '".$a_shorttext."' AND text = '".$a_text."' AND end_rotation = '".$a_end_rotation."'", $this->dlI);	//Get date_id of the inserted date
				$date_id = mysql_fetch_array($result);
				mysql_free_result ($result);
				if ($a_keyword_id != '0') mysql_query ("INSERT INTO ".$this->dbase_cscw.".dp_keywords (id, date_id, keyword_id) VALUES ('', '".$date_id[0]."', '".$a_keyword_id."')", $this->dlI);	//Insert dp_keyword allocation into the table of dp_keywords if dp_keyword is set
				if ($a_group_id != '0')	//Checks if date is a date of a group
				{
					$users = false;
					$users = ilCalInterface::getOtherMembers($a_group_id, $a_user_id);	//Gets all other members of this group from database via the interface class
					if($users) {
						for ($i = 0; $i < count($users); $i++)
						{
							mysql_query ("INSERT INTO ".$this->dbase_cscw.".dp_changed_dates (id, user_id, date_id, status, timestamp) VALUES ('', '".$users[$i]."', '".$date_id[0]."', '0', '0')", $this->dlI);	//Inserts a "new" date into the table of changed dp_dates
							mysql_query ("INSERT INTO ".$this->dbase_cscw.".dp_neg_dates (id, date_id, user_id, timestamp) VALUES ('', '".$date_id[0]."', '".$users[$i]."', '0')", $this->dlI);	//Inserts a negative date into the table of negative dp_dates
						}
					}
				}
			}
			else $return = 1;	//Errorcode 1 = Date exists already
		}
		else $return = 2;	//No DB-connection or one or more parameters missing
		return $return;
	}//end function


	/**
	* Alters a date in the table of dp_dates
	* @param int date_id
	* @param int begin
	* @param int end
	* @param int user_id
	* @param int changed
	* @param int rotation
	* @param int end_rotation
	* @param string shorttext
	* @param string text
	* @return bool
	*/
	function updateDate ($a_date_id, $a_begin, $a_end, $a_user_id, $a_changed, $a_rotation, $a_end_rotation, $a_shorttext, $a_text)
	{
		if (isset($this->dlI) && $a_begin <= $a_end && isset($a_date_id) && isset($a_begin) && isset($a_end) && isset($a_user_id) && isset($a_changed) && isset($a_rotation) && isset($a_end_rotation) && isset($a_shorttext))	//Checks if connected to database and if all parameters are set except the text
		{
			$return = false;
			$result = mysql_query ("SELECT user_ID, group_ID, begin, end, rotation, end_rotation, shorttext, text FROM ".$this->dbase_cscw.".dp_dates WHERE id = '".$a_date_id."'", $this->dlI);	//Gets the all information of this date from the table of dp_dates
			$date = mysql_fetch_row($result);
			mysql_free_result ($result);
			if ($a_user_id == $date[0] && ($a_begin != $date[2] || $a_end != $date[3] || $a_rotation != $date[4] || $a_end_rotation != $date[5] || $a_shorttext != $date[6] || $a_text != $date[7]))	//User is owner of date
			{
				mysql_query ("UPDATE ".$this->dbase_cscw.".dp_dates SET begin = '".$a_begin."', end = '".$a_end."', changed = '".$a_changed."', rotation = '".$a_rotation."', shorttext = '".$a_shorttext."', text = '".$a_text."', end_rotation = '".$a_end_rotation."' WHERE id = '".$a_date_id."'", $this->dlI);	//Update the date in the table of dp_dates
				if (mysql_errno ($this->dlI) == 0) $return = true;
				if ($date[1] != '0')	//Date is a date of a group
				{
					$users = false;
					$users = ilCalInterface::getOtherMembers($date[1], $a_user_id);	//Gets all other members of this group from database via the interface class
					if($users) {
					for ($i = 0; $i < count($users); $i++)
					{
						$test = false;
						$result2 = mysql_query ("SELECT DISTINCT date_id FROM ".$this->dbase_cscw.".dp_changed_dates WHERE date_id = '".$a_date_id."' AND user_id = '".$users[$i]."' AND timestamp = '0' AND status = '0' ", $this->dlI);	//Checks if the date is stated as new date for this user in the table dp_changed_dates
						$test = mysql_fetch_array($result2);
						mysql_free_result ($result2);
						if ($test[0] != $a_date_id)	//Checks if the date is stated as new date for this user in the table dp_changed_dates (see SQL-query)
						{
								mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE date_id = '".$a_date_id."' AND user_id = '".$users[$i]."' AND timestamp = '0' AND status = '1'", $this->dlI);	//Delete all entries in the table dp_changed_dates for this user which are stated as updated
								mysql_query ("INSERT INTO ".$this->dbase_cscw.".dp_changed_dates (id, user_id, date_id, status, timestamp) VALUES ('', '".$users[$i]."', '".$a_date_id."', '1', '0')", $this->dlI);	//Inserts a "updated" date into the table of changed dp_dates
						}
					}
					}
				}
				if ($a_rotation != $date[4])	//Rotation has changed
				{
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE date_id = '".$a_date_id."' AND timestamp != '0'", $this->dlI);	//Delete all single rotating dp_dates from the table of changed dp_dates
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_neg_dates WHERE date_id = '".$a_date_id."' AND timestamp != '0'", $this->dlI);	//Delete all single rotating dp_dates from the table of negative dp_dates
				}
			}
			else
			{
				$return = false;
			}
		}
		else
		{
			$return = false;
		}
		return $return;
	}//end function


	/**
	* Deletes a date by id and user_id
	* @param int date_id
	* @param int user_id
	* @param int timestamp
	* @return bool
	*/
	function delDate ($a_date_id, $a_user_id, $a_timestamp)
	{
		if (isset($this->dlI) && isset($a_date_id) && isset($a_user_id) && isset($a_timestamp))	//Checks if connected to database and if all parameters are set
		{
			$date = false;
			$result = mysql_query ("SELECT group_id, user_id, rotation FROM ".$this->dbase_cscw.".dp_dates WHERE id = '".$a_date_id."'", $this->dlI);	//Gets the group_id, user_id, rotation and date_id of this date from the table of dp_dates
			$date = mysql_fetch_row($result);
			mysql_free_result ($result);
			if ($date[0] == 0 && $a_user_id == $date[1]) //Not a date of a group / user is owner of the date
			{
				if ($a_timestamp != 0 && $date[2] != 0) //Single rotating date
				{
					mysql_query ("INSERT INTO ".$this->dbase_cscw.".dp_neg_dates (id, date_id, user_id, timestamp) VALUES ('', '".$a_date_id."', '".$a_user_id."' , '".$a_timestamp."')", $this->dlI);	//Insert a negative single rotating date into the table of negative dp_dates
				}
				else //Whole rotating date or single date
				{
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_dates WHERE id = '".$a_date_id."'", $this->dlI);			//Deletes all traces of this date from all tables
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_neg_dates WHERE date_id = '".$a_date_id."'", $this->dlI);	//		/
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_keywords WHERE date_id = '".$a_date_id."'", $this->dlI);	//	/
				}
				$return	= true;
			}
			elseif ($date[0] != 0) //Date of a group
			{
				if ($a_timestamp == 0)	//Whole rotating date or single date
				{
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE date_id = '".$a_date_id."' AND user_id = '".$a_user_id."'", $this->dlI);	//Delete all entries of this date for this user from the table of changed dp_dates
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_neg_dates WHERE date_id = '".$a_date_id."' AND user_id = '".$a_user_id."'", $this->dlI);	//Delete all entries of this date for this user from the table of negative dp_dates
					$result = mysql_query ("SELECT DISTINCT dp_keywords.keyword_id FROM ".$this->dbase_cscw.".dp_keyword, ".$this->dbase_cscw.".dp_keywords WHERE dp_keywords.date_id = '".$a_date_id."' AND dp_keyword.id = dp_keywords.keyword_id AND dp_keyword.user_id = '".$a_user_id."'", $this->dlI);	//Get keyword_id of this date for this date
					$keyword_id = mysql_fetch_array($result);
					mysql_free_result ($result);
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_keywords WHERE date_id = '".$a_date_id."' AND keyword_id = '".$keyword_id[0]."'", $this->dlI);	//Delete association of this dp_keyword to this date from the table of dp_keywords
				}
				else	//Single rotating date
				{
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE date_id = '".$a_date_id."' AND user_id = '".$a_user_id."' AND timestamp = '".$a_timestamp."'", $this->dlI);	//Delete this single rotation date for this user from the table of changed dp_dates
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_neg_dates WHERE date_id = '".$a_date_id."' AND user_id = '".$a_user_id."' AND timestamp = '".$a_timestamp."'", $this->dlI);	//Delete this single rotation date for this user from the table of negative dp_dates
				}
				mysql_query ("INSERT INTO ".$this->dbase_cscw.".dp_neg_dates (id, date_id, user_id, timestamp) VALUES ('', '".$a_date_id."', '".$a_user_id."' , '".$a_timestamp."')", $this->dlI);	
				//Inserts a negative date into the table of negative dp_dates
				if ($a_user_id == $date[1])	//User is owner of date
				{

					$users = false;
					$users = ilCalInterface::getOtherMembers($date[0], $a_user_id);	//Gets all other members of this group from database via the interface class
					if($users) {
						for ($i = 0; $i < count($users); $i++)
						{
						//Date has been deleted by member
						$test = false;
						$test2 = false;
						$result = mysql_query ("SELECT DISTINCT date_id FROM ".$this->dbase_cscw.".dp_neg_dates WHERE date_id = '".$a_date_id."' AND user_id = '".$users[$i]."'", $this->dlI);
						$test = mysql_fetch_array($result);
						mysql_free_result ($result);
						$result = mysql_query ("SELECT DISTINCT date_id, status FROM ".$this->dbase_cscw.".dp_changed_dates WHERE date_id = '".$a_date_id."' AND user_id = '".$users[$i]."'", $this->dlI);
						$test2 = mysql_fetch_row($result);
						mysql_free_result ($result);
							if ($a_timestamp == 0)	//Whole rotating date or single date
							{
								mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE date_id = '".$a_date_id."' AND user_id = '".$users[$i]."'", $this->dlI);	//Delete all entries of this date for this member from the table of changed dp_dates
								mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_neg_dates WHERE date_id = '".$a_date_id."' AND user_id = '".$users[$i]."'", $this->dlI);	//Delete all entries of this date for this member from the table of negative dp_dates
							}
							else	//Single rotating date
							{
								mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE date_id = '".$a_date_id."' AND user_id = '".$users[$i]."' AND timestamp = '".$a_timestamp."'", $this->dlI);	//Delete this single rotation date for this member from the table of changed dp_dates
								mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_neg_dates WHERE date_id = '".$a_date_id."' AND user_id = '".$users[$i]."' AND timestamp = '".$a_timestamp."'", $this->dlI);	//Delete this single rotation date for this member from the table of negative dp_dates
							}

							if ($test2[1] != "0") {mysql_query ("INSERT INTO ".$this->dbase_cscw.".dp_changed_dates (id, user_id, date_id, status, timestamp) VALUES ('', '".$users[$i]."', '".$a_date_id."' , '2', '".$a_timestamp."')", $this->dlI);	//Inserts a "deleted" date for this member into the table of changed dp_dates
							}
						}
					}
				}
				$result = mysql_query ("SELECT DISTINCT count(id) as numOfNegDates FROM ".$this->dbase_cscw.".dp_neg_dates WHERE date_id = '".$a_date_id."' AND timestamp = '0'", $this->dlI);	//Counts how many users have deleted this date
				$numOfNegDates = mysql_fetch_array($result);
				mysql_free_result ($result);

				if ($numOfNegDates[0] >= ilCalInterface::getNumOfMembers($date[0]))	//Checks if the number of users who have deleted this date is equal to the number of members of this group

				{
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_dates WHERE id = '".$a_date_id."'", $this->dlI);				//Deletes all traces of this date from all tables
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE date_id = '".$a_date_id."'", $this->dlI);	//			/
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_neg_dates WHERE date_id = '".$a_date_id."'", $this->dlI);		//		/
					mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_keywords WHERE date_id = '".$a_date_id."'", $this->dlI);		//	/
				}
				$return	= true;	
			}	
			else
			{
				$return = false;
			}
		}
		else
		{
			$return = false;
		}
		return $return;
	}//end function


	/**
	* Updates a the allocation of a dp_keyword to a date
	* @param int user_id
	* @param int date_id
	* @param int keyword_id
	* @return bool
	*/
	function updateKeyword2Date ($a_user_id, $a_date_id, $a_keyword_id)
	{
		if (isset($this->dlI) && isset($a_user_id) && isset($a_date_id) && isset($a_keyword_id))	//Checks if connected to database and if all parameters are set
		{
				$keyword_id_old = false;
				$result = mysql_query ("SELECT DISTINCT dp_keywords.keyword_id FROM ".$this->dbase_cscw.".dp_keyword, ".$this->dbase_cscw.".dp_keywords WHERE dp_keywords.date_id = '".$a_date_id."' AND dp_keyword.id = dp_keywords.keyword_id AND dp_keyword.user_id = '".$a_user_id."'", $this->dlI);	//Gets the old dp_keyword associated with this date
				$keyword_id_old = mysql_fetch_array($result);
				mysql_free_result ($result);
				if ($a_keyword_id != $keyword_id_old[0])	// If dp_keyword has changed
				{
					if ($a_keyword_id == 0)				 mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_keywords WHERE date_id = '".$a_date_id."' AND keyword_id = '".$keyword_id_old[0]."'", $this->dlI);	//Delete association from table of dp_keywords
					elseif ($keyword_id_old[0] == false) mysql_query ("INSERT INTO ".$this->dbase_cscw.".dp_keywords (id, date_id, keyword_id) VALUES ('', '".$a_date_id."', '".$a_keyword_id."')", $this->dlI);		//Add association from table of dp_keywords
					else								 mysql_query ("UPDATE ".$this->dbase_cscw.".dp_keywords SET keyword_id = '".$a_keyword_id."' WHERE date_id = '".$a_date_id."' AND keyword_id = '".$keyword_id_old[0]."'", $this->dlI);		//Change association from table of dp_keywords
				}
				return true;
		}
		else
		{
			return false;
		}
	}//end function


	/**
	* Cleans the database of garbage
	* @return bool
	*/
	function cleanDatabase ()
	{
		if (isset($this->dlI))	//Checks if connected to database
		{
			$groups = ilCalInterface::getGroups();	//Gets all groups of the system
			$ago = strtotime ("-6 month");	//Sets the time which date have to be ago to be deleted
			$tobedeleted = false;
			
			$result = mysql_query ("SELECT DISTINCT id FROM ".$this->dbase_cscw.".dp_dates WHERE rotation != '0' AND end_rotation <= '".$ago."'", $this->dlI);	//Gets all single dp_dates that are older than 6 month
			for ($i = 0; $row = mysql_fetch_array($result); $i++)
			{
				$tobedeleted[$i] = $row[0];
			}
			mysql_free_result ($result);
			
			$result = mysql_query ("SELECT DISTINCT id FROM ".$this->dbase_cscw.".dp_dates WHERE rotation = '0' AND end <= '".$ago."'", $this->dlI);		//Gets all rotating dp_dates that are older than 6 month
			for ($i = 0, $temp = false; $row = mysql_fetch_array($result); $i++)
			{
				$temp[$i] = $row[0];
			}
			$tobedeleted = array_merge ($tobedeleted, $temp);
			mysql_free_result ($result);
			
			$result = mysql_query ("SELECT DISTINCT id FROM ".$this->dbase_cscw.".dp_dates WHERE user_id NOT IN ('-1','".implode("','",$users)."')", $this->dlI);	//Gets all dp_dates where user has been deleted
			for ($i = 0, $temp = false; $row = mysql_fetch_array($result); $i++)
			{
				$temp[$i] = $row[0];
			}
			$tobedeleted = array_merge ($tobedeleted, $temp);
			mysql_free_result ($result);
			
			$result = mysql_query ("SELECT DISTINCT id FROM ".$this->dbase_cscw.".dp_dates WHERE group_id NOT IN ('".$this->alluser_id."','".implode("','",$groups)."')", $this->dlI);	//Gets all dp_dates where group has been deleted
			for ($i = 0, $temp = false; $row = mysql_fetch_array($result); $i++)
			{
				$temp[$i] = $row[0];
			}
			$tobedeleted = array_merge ($tobedeleted, $temp);
			mysql_free_result ($result);
			
			mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_dates WHERE id IN ('-1','".implode("','",$tobedeleted)."')", $this->dlI);	//Delete all collected dp_dates from the table of dp_dates
			mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE date_id IN ('-1','".implode("','",$tobedeleted)."')", $this->dlI);		//Delete all collected dp_dates from the table of changed dp_dates
			mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_neg_dates WHERE date_id IN ('-1','".implode("','",$tobedeleted)."')", $this->dlI);	//Delete all collected dp_dates from the table of negative dp_dates
			mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_keywords WHERE date_id IN ('-1','".implode("','",$tobedeleted)."')", $this->dlI);		//Delete all collected dp_dates from the table of dp_keywords
						
			mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_changed_dates WHERE user_id NOT IN ('-1','".implode("','",$users)."')", $this->dlI);	//Delete all entries of deleted users from the table of changed dp_dates
			mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_neg_dates WHERE user_id NOT IN ('-1','".implode("','",$users)."')", $this->dlI);	//Delete all entries of deleted users from the table of negative dp_dates
			
			mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_keyword WHERE user_id NOT IN ('-1','".implode("','",$users)."')", $this->dlI);	//Delete all entries of deleted users from the table dp_keyword
			
			$result = mysql_query ("SELECT DISTINCT id FROM ".$this->dbase_cscw.".dp_keyword", $this->dlI);	//Gets all keyword_ids
			for ($i = 0, $tobedeleted = false; $row = mysql_fetch_array($result); $i++)
			{
				$tobedeleted[$i] = $row[0];
			}
			mysql_free_result ($result);

			mysql_query ("DELETE FROM ".$this->dbase_cscw.".dp_keywords WHERE keyword_id NOT IN ('-1','".implode("','",$tobedeleted)."')", $this->dlI);	//Deletes all entries of deleted dp_keywords from the table dp_keywords
			
			return true;		
		}
		else
		{
			return false;
		}
	}//end function


} //end Class
?>