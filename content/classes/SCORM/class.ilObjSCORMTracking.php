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

require_once("content/classes/SCORM/class.ilObjDebug.php");

/**
* Class ilObjSCORMTracking
*
* @author Ralph Barthel <ralph.barthel@21ll.com> 21 LearnLine AG
*
*/
class ilObjSCORMTracking
{
	// the following section should be handled by the session object
	
	var $usr_id;
	var $sco_id;
	
	var $errorDescription=array("0" =>"no error","101" => "General exception","201" => "Invalid argument error","202" => "Element cannot have children","203" => "Element not an array - cannot have count","301" => "not initialized","401" => "not implemented","402" => "Invalid set value, element is a keyword","403" => "Element is read only","404" => "Element is write only","405" => "Incorrect data type" );
	var $supportedElements=array( "cmi.core.student_name"=>"r", "cmi.core.exit"=>"w", "cmi.core.lesson_location"=>"rw", "cmi.core.credit"=>"r", "cmi.core.entry"=>"rw", "cmi.core.student_id"=>"r","cmi.core.lesson_status"=>"rw","cmi.core.score.raw"=>"rw", "cmi.core.session_time"=>"w", "cmi.core.total_time"=>"r", "cmi.suspend_data"=>"rw", "cmi.launch_data"=>"r","cmi.comments"=>"rw","cmi.student_data.mastery_score"=>"r");
	var $supportedElemArrays=array("cmi.core._children"=>"student_id,student_name,entry,exit,lesson_location,lesson_status,credit,score,session_time,total_time", "cmi.core.score._children"=>"raw","cmi.student_data._children"=>"mastery_score");
	/**
	* Constructor
	* @access	public
	*/
	function ilObjSCORMTracking($userID, $itemID)
	{
		global $ilias;
		$this->debug = new ilObjDebug("/opt/ilias/www/htdocs/ilias3/debug/debug.ilObjSCORMTracking");
		$this->debug->debug("Calling Constructor with userID: ".$userID." and itemID: ".$itemID);
	     $this->ilias =& $ilias;
		 //$this->db=$this->ilias->db;
	     //$this->objID=$objectIdentifier;
	     //$this->db=$dbHandle;
	     $this->usr_id=$userID;
	     $this->sco_id=$itemID;
		return $this;
	}

	/**
	* checks if instance of the learning object is running
	* set log and start timer or return error ->has to be shift
	* to session object
	*/
	function lmsInitialize($emptyString)
	{
		$this->debug->debug("Calling lmsInitialize with emptyString: ".$emptyString);
		
		$query="SELECT * from scorm_tracking WHERE sc_item_id='$this->sco_id' and usr_id='$this->usr_id'";
    	$record_set = $this->ilias->db->query($query);
    	
    	//no entry in database ->creating new entry getting values from related item table
    	if($record_set->numRows()==0)
    	{
    		
    		$query="SELECT datafromlms,masteryscore FROM sc_item WHERE obj_id='$this->sco_id'";
    		$record_set =$this->ilias->db->query($query);
    		while ($record = $record_set->fetchRow(DB_FETCHMODE_ASSOC))
			{
				 $launchData=$record["datafromlms"];
				 $masteryScore=$record["masteryscore"];
			}	
    		
    		$query="INSERT INTO scorm_tracking (sc_item_id,usr_id,entry,exit,lesson_location,credit,raw,comments,lesson_status,launch_data,suspend_data,mastery_score,student_name) VALUES ".
    		"('$this->sco_id','$this->usr_id','ab-initio','','','credit','','','not attempted','$launchData','','$masteryScore','".$this->ilias->account->login."')";
    		$this->ilias->db->query($query);
    		
    		//time capturing has to be added
    		
    		echo "true";
    	}
    	else 
    	{
    		//checking if session is already open ->to be done
    		if(true)
    		{
    			//logging object
    			/*
    			$query="UPDATE sco_tracking SET is_logged='1' WHERE sco_id='$this->objID'";
    			$result=$this->db->query($query);
    			$this->startTime=time();
    		
    			$this->initialized=true;
    			return "true";*/
    			echo "true";
    		}
    		else 
    		{
    			//instance already running
    			echo "false";
    		}
    	}
		 
	}
	
	/**
	* stops timer upadtes database values
	* for last session_time and total_time
	* using subfunction. unlocks the object
	* in database
	*/
	 function lmsFinish($emptyString)
    {
		$this->debug->debug("Calling lmsFinish with emptyString: ".$emptyString);
         $this->endTime=time();
         $this->calculateTime();
         $status='browsed';
    	 
    	 //some values have eventually  to be updated
    	 $query="SELECT * from scorm_tracking WHERE sc_item_id='$this->sco_id' and usr_id='$this->usr_id'";
    	 $record_set = $this->ilias->db->query($query);
    	 while ($record = $record_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if($record['exit']=="suspend" && $record['entry']!="resume")
			{
				$query1="UPDATE scorm_tracking SET entry='resume' WHERE sc_item_id='$this->sco_id' AND usr_id='$this->usr_id'";
			}
			
			if($record['mastery_score']<=$record['raw'])
			{	
				if($record['mastery_score']==0)
				{
					$status="completed";
				}
				else 
				{
					$status="passed";
				}
					
			}
			//mastery_score is set and performance below mastery_score ->setting to failed after specification
			else 
			{
				$status="failed";
				
			}
			
			$query2="UPDATE scorm_tracking SET lesson_status='$status' WHERE sc_item_id='$this->sco_id' AND usr_id='$this->usr_id'";
			
			
			if($record['entry']=='ab-initio')
			{
				if(!isset($query1))
				{
					$query3="UPDATE scorm_tracking SET entry='',lesson_status='$status' WHERE sc_item_id='$this->sco_id' AND usr_id='$this->usr_id'";
				}
				else 
				{
					$query3="UPDATE scorm_tracking SET entry='resume',lesson_status='$status' WHERE sc_item_id='$this->sco_id' AND usr_id='$this->usr_id'";
				}
			}
		}	
		
		// executing necessary queries
		 if(isset($query1))
		 {
		 	$this->ilias->db->query($query1);
		 }
		 if(isset($query2))
		 {
		 	$this->ilias->db->query($query2);
		 }
		 if(isset($query3))
		 {
		 	$this->ilias->db->query($query3);
		 }
		 
    	 
    	 echo "true";
    }

    /**
	* gets value from database for the passed data
	* model element return value or error
	* !!! obj_id is hardcoded for test purpose has to be kept
	* in session object
	* 
	*/
    function lmsGetValue($dataModelElement)
    {
	$this->debug->debug("Calling lmsGetValue with dataModelElement: ".$dataModelElement);
    	if(substr_count($dataModelElement,"_children")>0)
    	{
    		if(array_key_exists($dataModelElement,$this->supportedElemArrays))
    		{
    			return $this->supportedElemArrays[$dataModelElement];
    		}
    		else 
    		{
    			$this->errorCode="401";
			$this->debug->debug("Errorcode 401 in Line 209");
    			echo "";
    		}
    	}
    	
        if(array_key_exists($dataModelElement,$this->supportedElements))
        {
        	//extracting elem name dropping datamodel name
        	
        	if($this->supportedElements[$dataModelElement]==r || $this->supportedElements[$dataModelElement]==rw)
        	{
			
        		$elemName=$this->extractElement($dataModelElement);
        		$query="SELECT " .$elemName." FROM scorm_tracking WHERE sc_item_id='$this->sco_id' AND usr_id='$this->usr_id'";
				$record_set = $this->ilias->db->query($query);
				
				while ($record = $record_set->fetchRow(DB_FETCHMODE_ASSOC))
				{
						echo $record[$elemName];
				}			
        	}
        	else 
        	{
        		$this->errorCode="404";//Element is write only
        		echo "";
        	}
        	
        }
        else 
        {
        	$this->errorCode="401";//Element not implemented
		$this->debug->debug("Errorcode 401 in Line 240");
        	echo "";
        }
    }

    /**
	* sets value of named element and updates database
	* return true or false if error occurs
	* 
	*/
    function lmsSetValue($dataModelElement, $elemValue)
    {
	$this->debug->debug("Calling lmsSetValue with dataModelElement: ".$dataModelElement." and elemValue: ".$elemValue);
      //checking parameter
      if(!is_string($elemValue))
      {
      	$this->errorCode="405";//Incorrect data type
      	echo "false";
      }
    	
       if(array_key_exists($dataModelElement,$this->supportedElements))
        {
        	//extracting elem name dropping datamodel name
        	$this->debug->debug("Calling lmsSetValue key exists: ".$dataModelElement." and elemValue: ".$elemValue);
        	
        	if($this->supportedElements[$dataModelElement]==w || $this->supportedElements[$dataModelElement]==rw)
        	{
        		$elemName=$this->extractElement($dataModelElement);
        		//in case of total_time element a calculation has to be done
        		if($elemName=="total_time")
        		{
        		}
        		$this->debug->debug("Calling lmsSetValue query: ".$dataModelElement." and elemValue: ".$elemValue);
        		$query="UPDATE scorm_tracking SET " .$elemName."='$elemValue' WHERE sc_item_id='$this->sco_id' AND usr_id='$this->usr_id'";
    			$this->ilias->db->query($query);
    			//error coding. probably for enum fields in scorm_tracking data checkin already here
    			echo "true";
    			
        	}
        	else 
        	{
        		$this->errorCode="403";//Element is read only
        		echo "false";
        	}
        	
        }
        else 
        {
        	$this->errorCode="401";//Element not implemented
		$this->debug->debug("Errorcode 401 in Line 286");
        	echo "false";
        }
    }

    function lmsCommit($emptyString)
    {
	$this->debug->debug("Calling lmsCommit with emptyString: ".$emptyString);
       echo "true";
    }

    function lmsGetLastError()
    {
	$this->debug->debug("Calling lmsGetLastError");
       echo $this->errorCode;//eventually changing cause error code might need to be set back to be "0"
    }

    function lmsGetErrorString($errNum)
    { 
	$this->debug->debug("Calling lmsGetErrorString with errNum: ".$errNum);
      echo $this->errorDescription[$errNum];
    }

    	/**
	* vendor specific information on error, not used yet
	* 
	*/
    function lmsGetDiagnostics($emptyString)
    {
	$this->debug->debug("Calling lmsGetDiagnostics with emptyString: ".$emptyString);
       echo "";
    }

    function calculateTime()
    {
	$this->debug->debug("Calling calculateTime");
    	$this->sessionTime=($this->endTime-$this->startTime);
    	//datbase call
  
    }
	
	function extractElement($dataModelElement)
	{
			$this->debug->debug("Calling extractElement with dataModelElement: ".$dataModelElement);
			if(!(strpos($dataModelElement,"cmi.core.score")===false))
			{
				return $elemName=eregi_replace("cmi.core.score.","",$dataModelElement);
			}
			elseif (!(strpos($dataModelElement,"cmi.core")===false))
			{
				return $elemName=eregi_replace("cmi.core.","",$dataModelElement);
			}
			elseif (!(strpos($dataModelElement,"cmi.student_data")===false))
			{
				return $elemName=eregi_replace("cmi.student_data.","",$dataModelElement);
			}
			else 
			{
				
				return $elemName=eregi_replace("cmi.","",$dataModelElement);
				
			}	
			
	}
    /**
	* change the value of the internal errorCode due to scorm specs
	* content application should always be able to access the result
	* of the latest call
	*/
    
} // END class.ilObjSCORMTracking
?>
