<?

require_once("content/classes/AICC/class.ilObjAICCTracking.php");
require_once("content/classes/HACP/class.ilHACPResponse.php");


class ilObjHACPTracking extends ilObjAICCTracking {

	/**
	* Constructor
	* @access	public
	*/
	function ilObjHACPTracking()
	{
		global $ilias, $HTTP_POST_VARS;
		
		
		global $ilDB, $ilUser;

		if (is_object($ilUser))
		{
			$user_id = $ilUser->getId();
		}
		
		$fp=fopen("./content/hacp.log", "a+");
		fputs($fp, "test 17\n");	
		fputs($fp, "ilDB=$ilDB\n");	
		fputs($fp, "user_id=$user_id\n");	
		fclose($fp);	
		
		
		//just to make sure to extract only this parameter 
		$mainKeys=array("command", "version", "session_id", "aicc_data");
		$postVars=array_change_key_case($HTTP_POST_VARS, CASE_LOWER);
		foreach($mainKeys as $key) {
			$$key=$postVars[$key];
		}
		
		//only allowed commands
		$allowedCommands=array("getparam", "putparam", "exitau");
		if (!in_array($command, $allowedCommands)) {
			exit;
		}
			
		$this->$command($version, $session_id, $aicc_data);
		
	}
	
	function getparam($version, $session_id, $aicc_data) {
		if (empty($aicc_data)) {
			$this->startau($version, $session_id, $aicc_data);
			return;
		}
		
		$fp=fopen("./content/hacp.log", "a+");
		fputs($fp, "getparam session_id=$session_id, aicc_data=$aicc_data\n");	
		fclose($fp);	
	}
	
	function putparam($version, $session_id, $aicc_data) {
		$fp=fopen("./content/hacp.log", "a+");
		fputs($fp, "putparam session_id=$session_id, aicc_data=$aicc_data\n");	
		fclose($fp);			
	}

	function exitau($version, $session_id, $aicc_data) {
		$fp=fopen("./content/hacp.log", "a+");
		fputs($fp, "exitau session_id=$session_id, aicc_data=$aicc_data\n");	
		fclose($fp);			
	}
	
	function startau($version, $session_id, $aicc_data) {
		$fp=fopen("./content/hacp.log", "a+");
		fputs($fp, "startau session_id=$session_id, aicc_data=$aicc_data\n");	
		fclose($fp);		
		
		$response=new ilHACPResponse();
		$response->send();
			
	}

	
}

?>