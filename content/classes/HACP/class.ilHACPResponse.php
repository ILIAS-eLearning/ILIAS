<?

class ilHACPResponse {


	function ilHACPResponse() {
		global $ilDB, $ilUser;

		if (is_object($ilUser))
		{
			$user_id = $ilUser->getId();
		}
		
		echo "user_id=$user_id\n";
		
	}
	
	function getStudentId() {
		global $ilUser;
		return $ilUser->getId();
	}
	
	function getStudentName() {
		return "Hallo";
	}
	
	function send() {
		echo "error=0\n";
		echo "error_txt=Successful\n";
		echo "version=3.0\n";
		echo "aicc_data=\n";
		
		$data["core"]["student_id"]=$this->getStudentId();  
		$data["core"]["student_name"]="Herbert Bloemeier";
		$data["core"]["output_file"]="";
		$data["core"]["credit"]="c";
		$data["core"]["lesson_location"]="";
		$data["core"]["lesson_status"]="n,a";
		$data["core"]["path"]="";
		$data["core"]["score"]="";
		$data["core"]["time"]="00:00:00";
		
		//$data["core_vendor"]="";
		$data["core_vendor"]["F_FWD"]="1";
		$data["core_vendor"]["DEBUG"]="1";
		$data["core_vendor"]["EXIT_URL"]="http://aicc.org/pages/done_sl.html";
		
		$data["core_lesson"]="";
		
		$data["student_data"]["mastery_score"]="100";
		$data["student_preferences"]["audio"]="-1";
		$data["student_preferences"]["text"]="-1";
		
		foreach ($data as $block=>$paar) {
			echo "[$block]\n";
			if (!is_array($paar))
				continue;
			
			foreach ($paar as $key=>$value)
				echo "$key=$value\n";
		}
		
		echo "[evaluation]";		
		
	}
	
	
}

?>
	 