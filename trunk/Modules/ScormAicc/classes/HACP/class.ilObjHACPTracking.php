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

require_once("./Modules/ScormAicc/classes/AICC/class.ilObjAICCTracking.php");
require_once("./Modules/ScormAicc/classes/HACP/class.ilHACPResponse.php");


/**
* @ingroup ModulesScormAicc
*/
class ilObjHACPTracking extends ilObjAICCTracking {

	/**
	* Constructor
	* @access	public
	*/
	function ilObjHACPTracking($ref_id, $obj_id)
	{
		global $ilias, $ilDB, $ilUser;
		
	
		//just to make sure to extract only this parameter 
		$mainKeys=array("command", "version", "session_id", "aicc_data");
		$postVars=array_change_key_case($_POST, CASE_LOWER);
		foreach($mainKeys as $key) {
			$$key=$postVars[$key];
		}
		
		//only allowed commands
		$command=strtolower($command);
		$allowedCommands=array("getparam", "putparam", "exitau");
		if (!in_array($command, $allowedCommands)) {
			exit;
		}
/*			
		$fp=fopen("././Modules/ScormAicc/log/hacp.log", "a+");
		fputs($fp, "$command ref_id=$ref_id, obj_id=$obj_id\n");	
		fclose($fp);			
*/		
		$this->$command($ref_id, $obj_id, $version, $aicc_data);
		
	}
	
	function getparam($ref_id, $obj_id, $version, $aicc_data) {
		//if (empty($aicc_data)) {
		//	$this->startau($ref_id, $obj_id, $version, $aicc_data);
		//	return;
		//}
		
		$response=new ilHACPResponse($ref_id, $obj_id);
		$response->sendParam();	
		
/*		
		$fp=fopen("././Modules/ScormAicc/log/hacp.log", "a+");
		fputs($fp, "getparam ref_id=$ref_id, obj_id=$obj_id, aicc_data=$aicc_data\n");	
		fclose($fp);	
*/
	}
	
	function putparam($ref_id, $obj_id, $version, $aicc_data) {
		//aiccdata is a non standard ini format
		//$data=parse_ini_file($tmpFilename, TRUE);
		$data=$this->parseAICCData($aicc_data);

		$hacp_id = ilObject::_lookupObjId($ref_id);
		
		//choose either insert or update to be able to inherit superclass
		global $ilDB, $ilUser, $ilLog;
		$this->update=array();
		$this->insert=array();
		if (is_object($ilUser)) {
			$user_id = $ilUser->getId();
			foreach ($data as $key=>$value) 
			{

				$set = $ilDB->queryF('
				SELECT * FROM scorm_tracking WHERE user_id = %s
				AND sco_id = %s
				AND lvalue = %s
				AND obj_id = %s',
				array('integer','integer','text','integer'),
				array($user_id,$obj_id,$key,$hacp_id));
				
				if ($rec = $ilDB->fetchAssoc($set))
					$this->update[] = array("left" => $key, "right" => $value);
				else
					$this->insert[] = array("left" => $key, "right" => $value);
			}
		}
		
		//store
		$this->store($hacp_id, $obj_id, 0);
		
		$response=new ilHACPResponse($ref_id, $obj_id);
		$response->sendOk();
	}

	function exitau($ref_id, $obj_id, $version, $aicc_data) {
		$response=new ilHACPResponse($ref_id, $obj_id);
		$response->sendOk();
	}
	
	function startau($ref_id, $obj_id, $version, $aicc_data) {
		$response=new ilHACPResponse($ref_id, $obj_id);
		$response->sendParam();	
	}

	function parseAICCData($string) {
		$data=array();
		if (!empty($string)) {
			$lines=explode("\n", $string);
			for($i=0;$i<count($lines);$i++) {
				$line=trim($lines[$i]);
				if (empty($line) || substr($line,0,1)==";" || substr($line,0,1)=="#"){
					continue;
				}
				if (substr($line,0,1)=="[") {
					$block=substr($line,1,-1);
					continue;
				}
				if (empty($block))
					continue;
				
				if (substr_count($line, "=")==0)
					$data[strtolower("cmi.".$block)]=$line;
				else if (substr_count($line, "=")==1) {
					$line=explode("=", $line);
					$data[strtolower("cmi.".$block.".".$line[0])]=$line[1];
				}
			}
		}
		return $data;
	}
	
}

?>