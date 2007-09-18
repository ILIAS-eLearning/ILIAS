<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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
 * @author  Hendrik Holtmann <holtmann@mac.com>, Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
*/

//TODO remove when database integration is finished
//define('IL_OP_USER_NAME', 'root');
//define('IL_OP_USER_PASSWORD', '');

	
class ilSCORM13Player
{

	const NONE = 0;
	const READONLY = 1;
	const WRITEONLY = 2;
	const READWRITE = 3;

	static private $schema = array // order of entries matters!
	(
		'package' => array(
			'user_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'learner_name' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'slm_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'mode' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'credit' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
		),
		'node' => array(
			'accesscount' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'accessduration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'accessed' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'activityAbsoluteDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'activityAttemptCount' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),	
			'activityExperiencedDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'activityProgressStatus' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'attemptAbsoluteDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'attemptCompletionAmount' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'attemptCompletionStatus' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'attemptExperiencedDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'attemptProgressStatus' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'audio_captioning' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'audio_level' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'availableChildren' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'cmi_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'completion' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'completion_status' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'completion_threshold' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'cp_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'created' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'credit' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'delivery_speed' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'exit' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'language' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'launch_data' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'learner_name' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'location' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'max' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'min' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'mode' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'modified' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'progress_measure' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'raw' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'scaled' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'scaled_passing_score' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),	
			'session_time' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'success_status' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'suspend_data' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'total_time' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'user_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
		),
		'comment' => array (
			'cmi_comment_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),	
			'cmi_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'comment' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),	
			'timestamp' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),	
			'location' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),	
			'sourceIsLMS' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
		),
		'correct_response' => array(
			'cmi_correct_response_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),	
			'cmi_interaction_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'pattern' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
		),
		'interaction' => array(
			'cmi_interaction_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),	
			'cmi_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'description' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'id' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'latency' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'learner_response' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'result' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'timestamp' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'type' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'weighting' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
		),
		'objective' => array(
			'cmi_interaction_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),	
			'cmi_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'cmi_objective_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'completion_status' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'description' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'id' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'max' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'min' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'raw' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'scaled' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'success_status' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'progress_measure' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'scope' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),			
		),
	);
	
	private $userId;
	public $packageId;
	public $jsMode;
	
	var $ilias;
	var $slm;
	var $tpl;
	
	function __construct()
	{
		
		global $ilias, $tpl, $ilCtrl, $ilUser, $lng;

		
		//include_once ("./Modules/Scorm2004/classes/ilSCORM13DB.php");

		
		if ($_REQUEST['learnerId']) {
				$this->userId = $_REQUEST['learnerId'];
			} else {
				$this->userId = $GLOBALS['USER']['usr_id'];
			}
		$this->packageId = (int) $_REQUEST['packageId'];
		$this->jsMode = strpos($_SERVER['HTTP_ACCEPT'], 'text/javascript')!==false;
		
		$this->page = $_REQUEST['page'];
		
		
				
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
				
        $this->packageId=ilObject::_lookupObjectId($_GET['ref_id']);
		$this->userId=$ilUser->getID();

		//ilSCORM13DB::init("sqlite2:".ILIAS_ABSOLUTE_PATH."/Modules/Scorm2004/data/sqlite2.db", "sqlite");
		
		//uncomment for usage with ILIAS DB
		
		//$pdo_dsn = ilSCORM13DB::il_to_pdo_dsn(IL_DSN);
		//ilSCORM13DB::init($pdo_dsn[0],$pdo_dsn[1],$pdo_dsn[2]);
		
	}

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $ilAccess, $ilLog, $ilUser;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"), $ilias->error_obj->WARNING);
		}
		
$ilLog->write("SCORM: Player cmd: ".$cmd);
		
		switch($cmd){
			
			case 'cp':
				$this->getCPData();
				break;
				
			case 'adlact':
				$this->getADLActData();
				break;		
			
			case 'cmi':
				if ($_SERVER['REQUEST_METHOD']=='POST') {
					$this->persistCMIData();
					error_log("Saved CMI Data");
				} else {
					$this->fetchCMIData();
				}
				break;
			
			case 'specialPage':
			 	$this->specialPage();
				break;
				
				
			default:
				$this->getPlayer();
				break;
		}
		
	}
	

	
	function getDataDirectory()
	{
		$webdir=str_replace("/ilias.php","",$_SERVER["SCRIPT_URL"]);	
		//load ressources always with absolute URL..relative URLS fail on innersco navigation on certain browsers
		$lm_dir=$webdir."/".ILIAS_WEB_DIR."/".$this->ilias->client_id ."/lm_data"."/lm_".$this->packageId;
		return $lm_dir;
	}
		
	
	
	public function getLangStrings()
	{
		global $ilDB, $ilUser;
		
		$return = array();
		
		$set = $ilDB->query("SELECT identifier, value FROM lng_data 
			WHERE module = ".$ilDB->quote("scorm13").
			" AND lang_key = ".$ilDB->quote($ilUser->getLanguage()));
		
		while ($row = $set->fetchRow(DB_FETCHMODE_ASSOC)) 
		{
			$return[$row['identifier']] = $row['value']; 
		}
		return $return;
	}	
	
	
	

	public function getPlayer()
	{
		global $ilUser;
		
		// player basic config data
		$config = array
		(
			'cp_url' => 'ilias.php?baseClass=ilSAHSPresentationGUI' . '&cmd=cp&ref_id='.$_GET["ref_id"],
			'cmi_url'=> 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=cmi&ref_id='.$_GET["ref_id"],
			'adlact_url'=> 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=adlact&ref_id='.$_GET["ref_id"],
			'specialpage_url'=> 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=specialPage&ref_id='.$_GET["ref_id"],
			
			'learner_id' => (string) $ilUser->getID(),
			'course_id' => (string) $this->packageId,
			'learner_name' => $ilUser->getFirstname()." ".$ilUser->getLastname(),
			'mode' => 'normal',
			'credit' => 'credit',
			'package_url' =>  $this->getDataDirectory()."/"
		);
		
		// TODO  replace with ILIAS languages
		$langstrings = $this->getLangStrings();
		
		$langstrings['btnStart'] = 'Start';
		$langstrings['btnResumeAll'] = 'Resume All';
		$langstrings['btnBackward'] = 'backward';
		$langstrings['btnForward'] = 'Forward';
		$langstrings['btnExit'] = 'Exit';
		$langstrings['btnExitAll'] = 'Exit All';
		$langstrings['btnAbandon'] = 'Abandon';
		$langstrings['btnAbandonAll'] = 'Abandon All';
		$langstrings['btnSuspendAll'] = 'Suspend All';
		$langstrings['btnPrevious'] = 'Previous';
		$langstrings['btnContinue'] = 'Next';
		$langstrings['lblChoice'] = 'Select a choice from the tree.';
		
		$config['langstrings'] = $langstrings;
		
		$this->tpl = new ilTemplate("tpl.scorm2004.player.html", false, false, "Modules/Scorm2004");
		$this->tpl->setVariable('DEBUG', 1);
		$this->tpl->setVariable('JSON_LANGSTRINGS', json_encode($langstrings));
		$this->tpl->setVariable($langstrings);
		$this->tpl->setVariable('DOC_TITLE', 'ILIAS SCORM 2004 Player');
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable('CSS_NEEDED', '');
		$this->tpl->setVariable('JS_NEEDED', '');
		$this->tpl->setVariable('JS_DATA', json_encode($config));
		$this->tpl->setVariable('JS_SEQUENCER',$this->includeADLSequencer());
		list($tsfrac, $tsint) = explode(' ', microtime());
		$this->tpl->setVariable('TIMESTAMP', sprintf('%d%03d', $tsint, 1000*(float)$tsfrac));
		$this->tpl->setVariable('BASE_DIR', './Modules/Scorm2004/');
		$this->tpl->setVariable('ILIAS', '1');	
		
		//set icons path
		$this->tpl->setVariable('IC_ASSET', ilUtil::getImagePath("scorm/asset_s.gif",false));	
		$this->tpl->setVariable('IC_COMPLETED', ilUtil::getImagePath("scorm/completed_s.gif",false));	
		$this->tpl->setVariable('IC_NOTATTEMPTED', ilUtil::getImagePath("scorm/not_attempted_s.gif",false));	
		$this->tpl->setVariable('IC_RUNNING', ilUtil::getImagePath("scorm/running_s.gif",false));	
		$this->tpl->setVariable('IC_INCOMPLETE', ilUtil::getImagePath("scorm/incomplete_s.gif",false));	
		$this->tpl->setVariable('IC_PASSED', ilUtil::getImagePath("scorm/passed_s.gif",false));	
		$this->tpl->setVariable('IC_FAILED', ilUtil::getImagePath("scorm/failed_s.gif",false));	
		$this->tpl->setVariable('IC_BROWSED', ilUtil::getImagePath("scorm/browsed.gif",false));	
		
		//
		$this->tpl->show("DEFAULT", false);
	}
	
	
	
	public function includeADLSequencer()
	{
		$base_dir='./Modules/Scorm2004/scripts/adl2js';
		if ($handle = opendir($base_dir)) { 
		    // List all the files 
		    while (false !== ($file = readdir($handle))) { 
				if (!(strrpos($file, ".")<=1) && !is_int(strpos($file, "~")))  //skip directories and hidden files
					$js_include_string .="		\n<script type=\"text/javascript\" src=\"$base_dir/$file\"></script>";
		    } 
		    closedir($handle); 
		}
		return $js_include_string;
	}
		
	public function getCPData()
	{
		global $ilDB;
		
		/*$packageData = ilSCORM13DB::getRecord(
			'cp_package', 
			'obj_id', 
			$this->packageId
		);*/
		$set = $ilDB->query("SELECT * FROM cp_package WHERE obj_id = ".$ilDB->quote($this->packageId));
		$packageData = $set->fetchRow(DB_FETCHMODE_ASSOC);

		
		$jsdata = $packageData['jsdata'];
		if (!$jsdata) $jsdata = 'null';
		if ($this->jsMode) 
		{
			header('Content-Type: text/javascript; charset=UTF-8');
			print($jsdata);
		}
		else
		{
			header('Content-Type: text/plain; charset=UTF-8');
			$jsdata = json_decode($jsdata);
			print_r($jsdata);	
		}
	}
	
	public function getADLActData()
	{
		global $ilDB;
		
		/*$data = ilSCORM13DB::getRecord(
			'cp_package', 
			'obj_id', 
			$this->packageId
		);*/
		$set = $ilDB->query("SELECT * FROM cp_package WHERE obj_id = ".$ilDB->quote($this->packageId));
		$data = $set->fetchRow(DB_FETCHMODE_ASSOC);
		
		$activitytree=$data['activitytree'];
		
		//add the appropriate classes
		
		if (!$activitytree) $activitytree = 'null';
		if ($this->jsMode) 
		{
			header('Content-Type: text/javascript; charset=UTF-8');
			print($activitytree);
		}
		else
		{
			header('Content-Type: text/plain; charset=UTF-8');
			$activitytree = json_decode($activitytree);
			print_r($activitytree);	
		}
	}
	
	
	public function specialPage() {
		//mapping constants
		global $lng;
		
		$specialpages = array (
			"_COURSECOMPLETE_"	=>		"seq_coursecomplete",
			"_ENDSESSION_"		=> 		"seq_endsession",
			"_SEQBLOCKED_"		=> 		"seq_blocked",
			"_NOTHING_"			=> 		"seq_nothing",
			"_ERROR_"			=>  	"seq_error",
			"_DEADLOCK_"		=>		"seq_deadlock",
			"_INVALIDNAVREQ_"	=>		"seq_invalidnavreq",
			"_SEQABANDON_"		=>		"seq_abandon",
			"_SEQABANDONALL_"	=>		"seq_abandonall",
			"_TOC_"				=>		"seq_toc"
		);
		
		$this->tpl = new ilTemplate("tpl.scorm2004.specialpages.html", false, false, "Modules/Scorm2004");
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable('TXT_SPECIALPAGE',$lng->txt($specialpages[$this->page]));
		$this->tpl->show("DEFAULT", false);
				
	}
	
	
	public function fetchCMIData()
	{
		$data = $this->getCMIData($this->userId, $this->packageId);
		if ($this->jsMode) 
		{
			header('Content-Type: text/javascript; charset=UTF-8');
			print(json_encode($data));
		}
		else
		{
			header('Content-Type: text/plain; charset=UTF-8');
			print(var_export($data, true));
		}
	}
	
	public function persistCMIData($data = null)
	{
		global $ilLog;
		
$ilLog->write("SCORM: persistCMIData");
		
		$data = json_decode(is_string($data) ? $data : file_get_contents('php://input'));
		$return = $this->setCMIData($this->userId, $this->packageId, $data);
		if ($this->jsMode) 
		{
			header('Content-Type: text/javascript; charset=UTF-8');
			print(json_encode($return));
		}
		else
		{
			header('Content-Type: text/html; charset=UTF-8');
			print(var_export($return, true));
		}
	}
	
	/**
	 * maps API data structure type to internal datatype on a node	
	 * and accepts only valid values, dropping invalid ones from input	 
	 */
	private function normalizeFields($table, &$node) 
	{
		return;
		foreach (self::$schema[$table] as $k => $v) 
		{
			$value = $node->$k; 
			if (isset($value) && is_string($v) && !preg_match($v, $value)) 
			{
				unset($node->$k);
			}
		}
	}

	private function getCMIData($userId, $packageId) 
	{
		global $ilDB;
		
		$result = array(
			'schema' => array(), 
			'data' => array()
		);
		foreach (self::$schema as $k=>&$v)
		{
			$result['schema'][$k] = array_keys($v);
			
			switch ($k)
			{
				case "node":
					$q = 'SELECT cmi_node.* 
						FROM cmi_node 
						INNER JOIN cp_node ON cmi_node.cp_node_id = cp_node.cp_node_id
						WHERE cmi_node.user_id = '.$ilDB->quote($userId).
						' AND cp_node.slm_id = '.$ilDB->quote($packageId);
					break;

				case "comment":
					$q = 'SELECT cmi_comment.* 
						FROM cmi_comment 
						INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_comment.cmi_node_id 
						INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
						WHERE cmi_node.user_id = '.$ilDB->quote($userId).
						' AND cp_node.slm_id = '.$ilDB->quote($packageId);
					break;

				case "correct_response":
					$q = 'SELECT cmi_correct_response.* 
						FROM cmi_correct_response 
						INNER JOIN cmi_interaction 
						ON cmi_interaction.cmi_interaction_id = cmi_correct_response.cmi_interaction_id 
						INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_interaction.cmi_node_id 
						INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
						WHERE cmi_node.user_id = '.$ilDB->quote($userId).
						' AND cp_node.slm_id = '.$ilDB->quote($packageId);
					break;

				case "interaction":
					$q = 'SELECT cmi_interaction.* 
						FROM cmi_interaction 
						INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_interaction.cmi_node_id 
						INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
						WHERE cmi_node.user_id = '.$ilDB->quote($userId).
						' AND cp_node.slm_id = '.$ilDB->quote($packageId);
					break;

				case "objective":
					$q = 'SELECT cmi_objective.* 
						FROM cmi_objective 
						INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_objective.cmi_node_id 
						INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
						WHERE cmi_node.user_id = '.$ilDB->quote($userId).
						' AND cp_node.slm_id = '.$ilDB->quote($packageId);
					break;

				case "package":
					$q = 'SELECT usr_data.usr_id AS user_id, 
						CONCAT(usr_data.firstname, " ", usr_data.lastname) AS learner_name, 
						sahs_lm.id AS slm_id , sahs_lm.default_lesson_mode AS mode, sahs_lm.credit
						FROM usr_data , cp_package
						INNER JOIN sahs_lm ON cp_package.obj_id = sahs_lm.id 
						WHERE usr_data.usr_id = '.$ilDB->quote($userId).
						' AND sahs_lm.id = '.$ilDB->quote($packageId);
					break;

			}
			
			$set = $ilDB->query($q);
			$result['data'][$k] = array();
			while ($row = $set->fetchRow(DB_FETCHMODE_ORDERED))
			{
				$result['data'][$k][] = $row;
			}
			
			//$result['data'][$k] = ilSCORM13DB::query('view_cmi_' . $k, array($userId, $packageId), 
			//	null, null, PDO::FETCH_NUM);
		}
		return $result;
	}

	private function removeCMIData($userId, $packageId, $cp_node_id=null) 
	{
		global $ilDB;
		
		$delorder = array('correct_response', 'objective', 'interaction', 'comment', 'node');
		error_log("Delete, User:".$userId."Package".$packageId."Node: ".$cp_node_id);
		foreach ($delorder as $k) 
		{
			if (is_null($cp_node_id))
			{
				switch($k)
				{
					case "response":
					 	$q = 'DELETE FROM 
							cmi_correct_response WHERE cmi_interaction_id IN (
							SELECT cmi_interaction.cmi_interaction_id FROM cmi_interaction 
							INNER JOIN cmi_node ON cmi_node.cmi_node_id=cmi_interaction.cmi_node_id 
							INNER JOIN cp_node ON cmi_node.cp_node_id=cp_node.cp_node_id 
							WHERE cmi_node.user_id='.$ilDB->quote($userId).
							' AND cp_node.slm_id='.$ilDB->quote($packageId).')';
						break;
						
					case "interaction":
						$q = 'DELETE FROM cmi_interaction 
							WHERE cmi_node_id IN (
							SELECT cmi_node.cmi_node_id FROM cmi_node 
							INNER JOIN cp_node ON cmi_node.cp_node_id=cp_node.cp_node_id 
							WHERE cmi_node.user_id='.$ilDB->quote($userId).
							' AND cp_node.slm_id='.$ilDB->quote($packageId).')';
						break;
						
					case "comment":
						$q = 'DELETE FROM cmi_comment 
							WHERE cmi_node_id IN (
							SELECT cmi_node.cmi_node_id FROM cmi_node 
							INNER JOIN cp_node ON cmi_node.cp_node_id=cp_node.cp_node_id 
							WHERE cmi_node.user_id='.$ilDB->quote($userId).
							' AND cp_node.slm_id='.$ilDB->quote($packageId).')';
						break;
						
					case "objective":
						$q = 'DELETE FROM cmi_objective 
							WHERE cmi_node_id IN (
							SELECT cmi_node.cmi_node_id FROM cmi_node 
							INNER JOIN cp_node ON cmi_node.cp_node_id=cp_node.cp_node_id 
							WHERE cmi_node.user_id='.$ilDB->quote($userId).
							' AND cp_node.slm_id='.$ilDB->quote($packageId).')';
						break;
						
					case "node":
						$q = 'DELETE FROM cmi_node 
							WHERE user_id='.$ilDB->quote($userId).' AND cp_node_id IN (
							SELECT cp_node_id FROM cp_node 
							WHERE slm_id='.$ilDB->quote($packageId).')';
						break;
				}
				
				$ilDB->query($q);
				
				/*ilSCORM13DB::exec(
					'delete_cmi_' . $k . 's', 
					array($userId, $packageId)
				);*/
			}
			else
			{
				switch($k)
				{
					case "correct_response":
						$q = 'DELETE FROM cmi_correct_response 
							WHERE cmi_interaction_id IN (
							SELECT cmi_interaction.cmi_interaction_id FROM cmi_interaction 
							INNER JOIN cmi_node ON cmi_node.cmi_node_id=cmi_interaction.cmi_node_id 
							WHERE cmi_node.cp_node_id='.$ilDB->quote($cp_node_id).')';
						break;
						
					case "interaction":
						$q = 'DELETE FROM cmi_interaction 
							WHERE cmi_node_id IN (
							SELECT cmi_node.cmi_node_id FROM cmi_node 
							WHERE cmi_node.cp_node_id='.$ilDB->quote($cp_node_id).')';
						break;
						
					case "comment":
					 	$q = 'DELETE FROM cmi_comment 
							WHERE cmi_node_id IN (
							SELECT cmi_node.cmi_node_id FROM cmi_node 
							WHERE cmi_node.cp_node_id='.$ilDB->quote($cp_node_id).')';
						break;

					case "objective":
					 	$q = 'DELETE FROM cmi_objective 
							WHERE cmi_node_id IN (
							SELECT cmi_node.cmi_node_id FROM cmi_node 
							WHERE cmi_node.cp_node_id='.$ilDB->quote($cp_node_id).')';
						break;
						
					case "node":
						$q = 'DELETE FROM cmi_node WHERE cp_node_id='.$ilDB->quote($cp_node_id).'';
						break;
				}
				
				$ilDB->query($q);
				
				/* ilSCORM13DB::exec(
					'delete_cmi_' . $k , 
					array($cp_node_id)
				);
				*/
			}
		} 
	}
	
	private function setCMIData($userId, $packageId, $data) 
	{
		global $ilDB, $ilLog;
		
		$result = array();
		$map = array();
		if (!$data) return;
	
		//$ilLog->write("SCORM: setCMIData -".$data."-");

		// we don't want to have trouble with partially deleted or filled datasets
		// so we try transaction mode (hopefully your RDBS supports this)
		//ilSCORM13DB::begin();

		// we have to specify the exact order to add records
		// since there are dependencies
		$tables = array('node', 'comment', 'interaction', 'objective', 'correct_response');

		foreach ($tables as $table)
		{
			$schem = & self::$schema[$table];
			if (!is_array($data->$table)) continue;
			$i=0;
				
$ilLog->write("SCORM: setCMIData, table -".$table."-");
			
			// build up numerical index for schema fields
			foreach ($schem as &$field) 
			{
				$field['no'] = $i++;
			}
			// now iterate through data rows from input
			foreach ($data->$table as &$row)
			{
				// first fill some fields that could not be set from client side
				// namely the database id's depending on which table is processed  
				switch ($table)
				{
					case 'correct_response':
						$no = $schem['cmi_interaction_id']['no'];
						$row[$no] = $map['interaction'][$row[$no]];
					case 'comment':
					case 'interaction':
						$no = $schem['cmi_node_id']['no'];
						$row[$no] = $map['node'][$row[$no]];
						break;
					case 'objective':
						$no = $schem['cmi_interaction_id']['no'];
						$row[$no] = $map['interaction'][$row[$no]];
						$no = $schem['cmi_node_id']['no'];
						$row[$no] = $map['node'][$row[$no]];
						break;
					case 'node':
						$no = $schem['user_id']['no'];
						$row[$no] = $userId;
						break;
					
				}
$ilLog->write("SCORM: setCMIData, row b");
				$cp_no = $schem['cp_' . $table . '_id']['no'];						 
				$cmi_no = $schem['cmi_' . $table . '_id']['no'];
				
				// get current id for later use
				// this is either a real db id or document unique string generated by client 
				$cmi_id = $row[$cmi_no]; 
				// set if field to null, so it will be filled up by autoincrement
				$row[$cmi_no] = null;
				// TODO validate values
				// create sql statement, RDBS should support "REPLACE" command
				//for Mysql
				$keys=array();
				foreach(array_keys($schem) as $key) {
					array_push($keys,"`".$key."`");
				}
$ilLog->write("SCORM: setCMIData, row c");
//				$sql = 'REPLACE INTO cmi_' . $table . ' (' . implode(', ', array_values($keys)) 
//					. ') VALUES (' . implode(', ', array_fill(0, count($schem), '?')) . ')';
				// if we process a table we have to destroy all data on this activity 
				// and related sub elements in interactions etc.
				if ($table==='node') 
				{
					error_log("Lets remove old data");
					$this->removeCMIData($userId, $packageId, $row[$cp_no]);
				}
				// now insert the data record
				//$ret=ilSCORM13DB::exec($sql, $row);
				$ret = false;

				$sql = 'REPLACE INTO cmi_' . $table . ' (' . implode(', ', array_values($keys)). 
					') VALUES ('.implode(",", ilUtil::quoteArray($row)).')';
				$ilDB->query($sql);
				$ret = true;

				if (!$ret)
				{
					$return = false;
					break;
				}
				// and get the new cmi_id
				//$row[$cmi_no] = ilSCORM13DB::getLastId();
				$row[$cmi_no] = $ilDB->getLastInsertId();
				
				// if we process a node save new id into result object that will be feedback for client
				if ($table==='node') 
				{
					$result[(string)$row[$cp_no]] = $row[$cmi_no];
				}
				
				// add new id to mapping table for later use on dependend elements 
				$map[$table][$cmi_id] = $row[$cmi_no];
			}
		}
		//$return===false ? ilSCORM13DB::rollback() : ilSCORM13DB::commit();
		return $result;
	}
	
	/**
	 * estimate content type for a filename by extension
	 * first do it for common static web files from external list
	 * if not found peek into file by slow php function mime_content_type()
	 * @param $filename required
	 * @return string mimetype name e.g. image/jpeg
	 */
	public function getMimetype($filename) 
	{
		$mimetypes = array();
		require_once('classes/mimemap.php');
		$info = pathinfo($filename);
		$ext = $mimetypes[$info['extension']];
		return $ext ? $ext : mime_content_type($filename);
	}
	
	/**
	 * getting and setting Scorm2004 cookie
	 * Cookie contains enrypted associative array of sahs_lm.id and permission value
	 * you may enforce stronger symmetrical encryption by adding RC4 via mcrypt()
	 **/
	public function getCookie() 
	{
		return unserialize(base64_decode($_COOKIE[IL_OP_COOKIE_NAME]));
	}
	
	public function setCookie($cook) 
	{
		setCookie(IL_OP_COOKIE_NAME, base64_encode(serialize($cook)));
	}
	
	/**
	 * Try to find file, identify content type, write it to buffer, and stop immediatly
	 * If no file given, read file from PATH_INFO, check permission by cookie, and write out and stop.	 
	 * @param $path filename
	 * @return void	 
	 */	 	
	public function readFile($path) 
	{

		if (headers_sent()) 
		{
			die('Error: Cookie could not be established');
		}
		
		$SAHS_LM_POSITION = 1; // index position of sahs_lm id in splitted path_info
	
		$comp = explode('/', (string) $path);
		$sahs = $comp[$SAHS_LM_POSITION];
		$cook = $this->getCookie();
		$perm = $cook[$sahs];
		
		if (!$perm) 
		{
			// check login an package access
			// TODO add rbac check function here
			$perm = 1;
			if (!$perm) 
			{
				header('HTTP/1.0 401 Unauthorized');
				die('/* Unauthorized */');
			}
			// write cookie
			$cook[$sahs] = $perm;
			$this->setCookie($cook);
		}
		
		$path = '.' . $path;
		if (!is_file($path))
		{
			header('HTTP/1.0 404 Not Found');
			die('/* Not Found ' . $path . '*/');
		} 
		
		// send mimetype to client
		header('Content-Type: ' . $this->getMimetype($path));
	
		// let page be cached in browser for session duration
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + session_cache_expire()*60) . ' GMT');
		header('Cache-Control: private');
	
		// now show it to the user and be fine
		readfile($path);
		die();
	} 
	
}	

?>
