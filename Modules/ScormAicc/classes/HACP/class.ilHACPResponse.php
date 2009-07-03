<?
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

require_once "./Modules/ScormAicc/classes/AICC/class.ilAICCUnit.php";

/**
* @ingroup ModulesScormAicc
*/
class ilHACPResponse {

	var $unit;
	var $ref_id;
	var $obj_id;

	function ilHACPResponse($ref_id=0, $obj_id=0) {
	
		if (!empty($ref_id) && !empty($obj_id)) {
			$this->ref_id=$ref_id;
			$this->obj_id=$obj_id;
			
			$this->unit=new ilAICCUnit($obj_id);
			$this->unit->read();
		}
	
	}
	
	function sendOk() {
		echo "error=0\n";
		echo "error_txt=Successful\n";
		echo "version=3.0\n";
	}
	
	function getStudentId() {
		global $ilUser;
		return $ilUser->getId();
	}
	
	function getStudentName() {
		global $ilUser;
		return $ilUser->getFullname();
	}
	
	function sendParam() {
		global $ilUser, $ilDB, $ilLog;
		
		echo "error=0\n";
		echo "error_txt=Successful\n";
		echo "version=3.0\n";
		echo "aicc_data=\n";
		
		$data["core"]["student_id"]=$this->getStudentId();  
		$data["core"]["student_name"]=$this->getStudentName();  
		$data["core"]["time"]="00:00:00";
		
		$data["core_vendor"]=$this->unit->getCoreVendor();
		
		$data["student_data"]["mastery_score"]=$this->unit->getMasteryScore();
		$data["student_data"]["max_time_allowed"]=$this->unit->getMaxTimeAllowed();
		$data["student_data"]["time_limit_action"]=$this->unit->getTimeLimitAction();
		
		$data["student_preferences"]["audio"]="-1";
		$data["student_preferences"]["text"]="1";
		
		$data["core_lesson"]="";
		$data["core"]["output_file"]="";
		$data["core"]["credit"]="c";
		$data["core"]["lesson_location"]="";
		$data["core"]["lesson_status"]="n,a";
		$data["core"]["path"]="";
		$data["core"]["score"]="";
		
//$ilLog->write("Read Trackingdata A");

		$slm_id = ilObject::_lookupObjId($this->ref_id);
		//Read Trackingdata
		if (is_object($ilUser)) 
		{
			$user_id = $ilUser->getId();

			$set = $ilDB->queryF('
			SELECT * FROM scorm_tracking 
			WHERE user_id = %s
			AND sco_id = %s
			AND obj_id = %s',
			array('integer','integer','integer'),
			array($user_id, $this->obj_id,$slm_id));
	
			while ($rec = $ilDB->fetchAssoc($set))
			{
				$key=$rec["lvalue"];
				$value=$rec["rvalue"];
				$arr=explode(".", $key);
				//delete useless prefix
				array_shift($arr);
				//analyse key and add it to the data array
				if (count($arr)==1)
					$data[$arr[0]]=$value;
				else if (count($arr)==2)
					$data[$arr[0]][$arr[1]]=$value;
			}
		}
		
		foreach ($data as $block=>$paar) {
			echo "[$block]\n";
			if (!is_array($paar)) {
				$paar=str_replace("<CR>", "\n", $paar);
				$paar=str_replace("<cr>", "\n", $paar);
				echo $paar."\n";
				continue;
			}
			
			foreach ($paar as $key=>$value)
				echo "$key=$value\n";
		}
		
		//echo "[evaluation]";		
		
	}
	
	
}

?>
	 