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

require_once("./Services/YUI/classes/class.ilYuiUtil.php");
require_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");

/**
* @author  Hendrik Holtmann <holtmann@mac.com>, Alfred Kohnert <alfred.kohnert@bigfoot.com>
* @version $Id$
* @ilCtrl_Calls ilSCORM13Player:
*/
class ilSCORM13Player
{

	const ENABLE_GZIP = 0;
	
	const ENABLE_JS_DEBUG = 1;
	
	const NONE = 0;
	const READONLY = 1;
	const WRITEONLY = 2;
	const READWRITE = 3;

	static private $schema = array // order of entries matters!
	(
		'package' => array(
			'user_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>user_id),
			'learner_name' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>learner_name),
			'slm_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>slm_id),
			'mode' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>c_mode),
			'credit' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>credit),
		),
		'node' => array(
			'accesscount' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>accesscount),
			'accessduration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>accessduration),
			'accessed' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>accessed),
			'activityAbsoluteDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>activityabsduration),
			'activityAttemptCount' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>activityattemptcount),	
			'activityExperiencedDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>activityexpduration),
			'activityProgressStatus' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>activityprogstatus),
			'attemptAbsoluteDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>attemptabsduration),
			'attemptCompletionAmount' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>attemptcomplamount),
			'attemptCompletionStatus' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>attemptcomplstatus),
			'attemptExperiencedDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>attemptexpduration),
			'attemptProgressStatus' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>attemptprogstatus),
			'audio_captioning' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>audio_captioning),
			'audio_level' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>audio_level),
			'availableChildren' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>availablechildren),
			'cmi_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>cmi_node_id),
			'completion' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>completion),
			'completion_status' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>completion_status),
			'completion_threshold' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>completion_threshold),
			'cp_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>cp_node_id),
			'created' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>created),
			'credit' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>credit),
			'delivery_speed' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>delivery_speed),
			'entry' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_entry),
			'exit' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_exit),
			'language' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_language),
			'launch_data' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>launch_data),
			'learner_name' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>learner_name),
			'location' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>location),
			'max' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_max),
			'min' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_min),
			'mode' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_mode),
			'modified' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>modified),
			'progress_measure' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>progress_measure),
			'raw' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_raw),
			'scaled' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>scaled),
			'scaled_passing_score' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>scaled_passing_score),	
			'session_time' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>session_time),
			'success_status' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>success_status),
			'suspend_data' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>suspend_data),
			'total_time' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>total_time),
			'user_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>user_id),
		),
		'comment' => array (
			'cmi_comment_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>cmi_comment_id),	
			'cmi_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>cmi_node_id),
			'comment' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_comment),	
			'timestamp' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_timestamp),	
			'location' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>location),	
			'sourceIsLMS' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>sourceislms),
		),
		'correct_response' => array(
			'cmi_correct_response_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>cmi_correct_resp_id),	
			'cmi_interaction_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>cmi_interaction_id),
			'pattern' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>pattern),
		),
		'interaction' => array(
			'cmi_interaction_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>cmi_interaction_id),	
			'cmi_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>cmi_node_id),
			'description' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>description),
			'id' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>id),
			'latency' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>latency),
			'learner_response' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>learner_response),
			'result' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>result),
			'timestamp' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_timestamp),
			'type' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_type),
			'weighting' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>weighting),
		),
		'objective' => array(
			'cmi_interaction_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>cmi_interaction_id),	
			'cmi_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>cmi_node_id),
			'cmi_objective_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null, 'dbfield'=>cmi_objective_id),
			'completion_status' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>completion_status),
			'description' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>description),
			'id' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>id),
			'max' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_max),
			'min' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_min),
			'raw' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>c_raw),
			'scaled' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>scaled),
			'progress_measure' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>progress_measure),
			'success_status' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>success_status),
			'scope' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null, 'dbfield'=>scope),			
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

		if ($_REQUEST['learnerId']) {
				$this->userId = $_REQUEST['learnerId'];
			} else {
				$this->userId = $GLOBALS['USER']['usr_id'];
			}
		$this->packageId = (int) $_REQUEST['packageId'];
		$this->jsMode = strpos($_SERVER['HTTP_ACCEPT'], 'text/javascript')!==false;
		
		$this->page = $_REQUEST['page'];
		
		$this->slm =& new ilObjSCORM2004LearningModule($_GET["ref_id"], true);
		
			
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
				
        $this->packageId=ilObject::_lookupObjectId($_GET['ref_id']);
		$this->userId=$ilUser->getID();
		
	}

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $ilAccess, $ilLog, $ilUser, $lng;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"), $ilias->error_obj->WARNING);
		}
		
//$ilLog->write("SCORM: Player cmd: ".$cmd);

		switch($cmd){
			
			case 'getRTEjs':
				$this->getRTEjs();
				break;
				
			case 'cp':
				$this->getCPData();
				break;
				
			case 'adlact':
				$this->getADLActData();
				break;	
				
			case 'suspend':
				$this->suspendADLActData();
				break;		
			
			case 'getSuspend':	
				$this->getSuspendData();
				break;
				
			case 'gobjective':
				$this->writeGObjective();
				break;		

			case 'getGobjective':	
				$this->readGObjective();
				break;
				
			case 'cmi':

				if ($_SERVER['REQUEST_METHOD']=='POST') {
					$this->persistCMIData();
					//error_log("Saved CMI Data");
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
	
	function getRTEjs()
	{
		$filename = "rte-min.js";
		if (self::ENABLE_JS_DEBUG==1) {
			$filename = "rte.js";
		}
		$js_data = file_get_contents("./Modules/Scorm2004/scripts/buildrte/".$filename);
		if (self::ENABLE_GZIP==1) {
			ob_start("ob_gzhandler");
			header('Content-Type: text/javascript; charset=UTF-8');
		} else {
			header('Content-Type: text/javascript; charset=UTF-8');
		}
		

		echo $js_data;
	}
	
	
	function getDataDirectory()
	{
		$webdir=str_replace("/ilias.php","",$_SERVER["SCRIPT_NAME"]);	
		//load ressources always with absolute URL..relative URLS fail on innersco navigation on certain browsers
		$lm_dir=$webdir."/".ILIAS_WEB_DIR."/".$this->ilias->client_id ."/lm_data"."/lm_".$this->packageId;
		return $lm_dir;
	}
		
	
	
	public function getPlayer()
	{
		global $ilUser,$lng, $ilias, $ilSetting;
		// player basic config data
		
		if ($this->slm->getSession()) {		
			$session_timeout = (int)($ilias->ini->readVariable("session","expire"))/2;
		} else {
			$session_timeout = 0;
		}
		
		$config = array
		(
			'cp_url' => 'ilias.php?baseClass=ilSAHSPresentationGUI' . '&cmd=cp&ref_id='.$_GET["ref_id"],
			'cmi_url'=> 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=cmi&ref_id='.$_GET["ref_id"],
			'adlact_url'=> 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=adlact&ref_id='.$_GET["ref_id"],
			'specialpage_url'=> 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=specialPage&ref_id='.$_GET["ref_id"],
			'suspend_url'=>'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=suspend&ref_id='.$_GET["ref_id"],
			'get_suspend_url'=>'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=getSuspend&ref_id='.$_GET["ref_id"],
			'gobjective_url'=>'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=gobjective&ref_id='.$_GET["ref_id"],
			'get_gobjective_url'=>'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=getGobjective&ref_id='.$_GET["ref_id"],
			'ping_url' =>'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=pingSession&ref_id='.$_GET["ref_id"],
			'scope'=>$this->getScope(),
			'learner_id' => (string) $ilUser->getID(),
			'course_id' => (string) $this->packageId,
			'learner_name' => $ilUser->getFirstname()." ".$ilUser->getLastname(),
			'mode' => 'normal',
			'credit' => 'credit',
			'auto_review' => $this->slm->getAutoReview(),
			'hide_navig' => $this->slm->getHideNavig(),
			'debug' => $this->slm->getDebug(),
			'package_url' =>  $this->getDataDirectory()."/",
			'session_ping' => $session_timeout
		);
				
		
		//language strings
		$langstrings['btnStart'] = $lng->txt('scplayer_start');
		$langstrings['btnExit'] = $lng->txt('scplayer_exit');
		$langstrings['btnExitAll'] = $lng->txt('scplayer_exitall');
		$langstrings['btnSuspendAll'] = $lng->txt('scplayer_suspendall');
		$langstrings['btnPrevious'] = $lng->txt('scplayer_previous');
		$langstrings['btnContinue'] = $lng->txt('scplayer_continue');		
		$langstrings['btnhidetree']=$lng->txt('scplayer_hidetree');
		$langstrings['btnshowtree']=$lng->txt('scplayer_showtree');
		$config['langstrings'] = $langstrings;
		
		//template variables	
		$this->tpl = new ilTemplate("tpl.scorm2004.player.html", false, false, "Modules/Scorm2004");
		$this->tpl->setVariable('JSON_LANGSTRINGS', json_encode($langstrings));
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		$this->tpl->setVariable('YUI_PATH', ilYuiUtil::getLocalPath());
		$this->tpl->setVariable($langstrings);
		$this->tpl->setVariable('DOC_TITLE', 'ILIAS SCORM 2004 Player');
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable('JS_DATA', json_encode($config));
		list($tsfrac, $tsint) = explode(' ', microtime());
		$this->tpl->setVariable('TIMESTAMP', sprintf('%d%03d', $tsint, 1000*(float)$tsfrac));
		$this->tpl->setVariable('BASE_DIR', './Modules/Scorm2004/');
		
		//set icons path
		$this->tpl->setVariable('IC_ASSET', ilUtil::getImagePath("scorm/asset_s.gif",false));	
		$this->tpl->setVariable('IC_COMPLETED', ilUtil::getImagePath("scorm/completed_s.gif",false));	
		$this->tpl->setVariable('IC_NOTATTEMPTED', ilUtil::getImagePath("scorm/not_attempted_s.gif",false));	
		$this->tpl->setVariable('IC_RUNNING', ilUtil::getImagePath("scorm/running_s.gif",false));	
		$this->tpl->setVariable('IC_INCOMPLETE', ilUtil::getImagePath("scorm/incomplete_s.gif",false));	
		$this->tpl->setVariable('IC_PASSED', ilUtil::getImagePath("scorm/passed_s.gif",false));	
		$this->tpl->setVariable('IC_FAILED', ilUtil::getImagePath("scorm/failed_s.gif",false));	
		$this->tpl->setVariable('IC_BROWSED', ilUtil::getImagePath("scorm/browsed.gif",false));	
		
		//include scripts
		$this->tpl->setVariable('JS_SCRIPTS', 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=getRTEjs&ref_id='.$_GET["ref_id"]);	
		
		//disable top menu
		if ($this->slm->getNoMenu()=="y") {
			$this->tpl->setVariable("VAL_DISPLAY", "style=\"display:none;\"");	
		}


		//check for max_attempts and raise error if max_attempts is exceeded
		if ($this->get_max_attempts()!=0) {
			if ($this->get_actual_attempts() >= $this->get_max_attempts()) {
				header('Content-Type: text/html; charset=utf-8');
				echo($lng->txt("cont_sc_max_attempt_exceed"));
				exit;		
			}
		}
		
		//count attempt
		//Cause there is no way to check if the Java-Applet is sucessfully loaded, an attempt equals opening the SCORM player
		
		$this->increase_attempt();
		$this->save_module_version();
		
		$this->tpl->show("DEFAULT", false);
	}
		
	public function getCPData()
	{
		global $ilDB;

		$res = $ilDB->queryF(
			'SELECT jsdata FROM cp_package WHERE obj_id = %s',
			array('integer'),
			array($this->packageId)
		);
		$packageData = $ilDB->fetchAssoc($res);		
		
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

		$res = $ilDB->queryF(
			'SELECT activitytree FROM cp_package WHERE obj_id = %s',
			array('integer'),
			array($this->packageId)
		);
		$data = $ilDB->fetchAssoc($res);
				
		$activitytree = $data['activitytree'];
				
		if(!$activitytree) 
		{
			$activitytree = 'null';
		}
		if($this->jsMode) 
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
	
	public function pingSession()
	{
		//do nothing except returning header
		header('Content-Type: text/plain; charset=UTF-8');
		print("");
	}
	
	public function getScope()
	{
		global $ilDB, $ilUser;

		$res = $ilDB->queryF(
			'SELECT global_to_system FROM cp_package WHERE obj_id = %s',
			array('integer'),
			array($this->packageId)
		);
		$data = $ilDB->fetchAssoc($res);
		
		$gystem = $data['global_to_system'];
		if($gystem == 1)
			$gsystem = 'null';
		else
			$gsystem = $this->packageId;
			
		return $gsystem;
	}	
	
	public function getSuspendData()
	{
		global $ilDB, $ilUser;
		
		$res = $ilDB->queryF(
			'SELECT data FROM cp_suspend WHERE obj_id = %s AND user_id = %s',
			array('integer', 'integer'),
			array($this->packageId, $ilUser->getId())
		);
		$data = $ilDB->fetchAssoc($res);
		
		$suspend_data = $data['data'];
		if($this->jsMode) 
		{
			header('Content-Type: text/javascript; charset=UTF-8');
			print($suspend_data);
		}
		else
		{
			header('Content-Type: text/plain; charset=UTF-8');
			$suspend_data = json_decode($suspend_data);
			print_r($suspend_data);	
		}
		
		//delete delivered suspend data
		$ilDB->manipulateF(
			'DELETE FROM cp_suspend WHERE obj_id = %s AND user_id = %s',
			array('integer', 'integer'),
			array($this->packageId, $ilUser->getId())
		);
	}
	
	public function suspendADLActData()
	{
		global $ilDB, $ilUser;

		$res = $ilDB->queryF(
			'SELECT * FROM cp_suspend WHERE obj_id = %s	AND user_id = %s',
			array('integer', 'integer'), 
			array($this->packageId, $ilUser->getId())
		);

		if(!$ilDB->numRows($res))
		{
			$ilDB->insert('cp_suspend', array(
				'data'		=> array('clob', file_get_contents('php://input')),
				'obj_id'	=> array('integer', $this->packageId),
				'user_id'	=> array('integer', $ilUser->getId())
			));
		}
		else
		{
			$ilDB->update('cp_suspend',
				array(
					'data'		=> array('clob', file_get_contents('php://input'))
				),
				array(
					'obj_id'	=> array('integer', $this->packageId),
					'user_id'	=> array('integer', $ilUser->getId())
				)
			);
		}
	}	
	
	public function readGObjective()
	{
		global $ilDB, $ilUser;
		
		//get json string
		$g_data = new stdClass();

		$query = 'SELECT objective_id, scope_id, satisfied, measure, user_id '
		       . 'FROM cmi_gobjective, cp_node, cp_mapinfo ' 
			   . 'WHERE (cmi_gobjective.objective_id <> %s AND cmi_gobjective.status IS NULL ' 
			   . 'AND cp_node.slm_id = %s AND cp_node.nodename = %s '
			   . 'AND cp_node.cp_node_id = cp_mapinfo.cp_node_id '  
			   . 'AND cmi_gobjective.objective_id = cp_mapinfo.targetobjectiveid) '
			   . 'GROUP BY objective_id, scope_id, satisfied, measure, user_id';
		$res = $ilDB->queryF(
			$query,
			array('text', 'integer', 'text'),
			array('-course_overall_status-', $this->packageId, 'mapinfo')
		);		
		while($row = $ilDB->fetchAssoc($res))
		{
			$learner = $row['user_id'];
			$objective_id = $row['objective_id'];
			if($row['scope_id'] == 0)
			{
				$scope = "null"; 
			}
			else
			{
				$scope = $row['scope_id'];
			}
			
			if($row['satisfied'] != NULL)
			{
				$toset = $row['satisfied'];
				$g_data->{"satisfied"}->{$objective_id}->{$learner}->{$scope} = $toset;
			}
			
			if($row['measure'] != NULL)
			{
				$toset = $row['measure'];
				$g_data->{"measure"}->{$objective_id}->{$learner}->{$scope} = $toset;
			}
		}
		$gobjective_data = json_encode($g_data);
		if ($this->jsMode) 
		{
			header('Content-Type: text/javascript; charset=UTF-8');
			print($gobjective_data);
		}
		else
		{
			header('Content-Type: text/plain; charset=UTF-8');
			$gobjective_data = json_decode($gobjective_data);
			print_r($gobjective_data);	
		}
	}	
	
	//saves global_objectives to database	
	public function writeGObjective()
	{
		global $ilDB, $ilUser, $ilLog;
		
		$user = $ilUser->getId();
		$package = $this->packageId;
		
		//get json string
		$g_data = json_decode(file_get_contents('php://input'));
		
		//iterate over assoziative array
		if($g_data == null)
			return null;
		
		foreach($g_data as $key => $value)
		{			
			//objective 
			//learner = ilias learner id
			//scope = null / course
		    foreach($value as $skey => $svalue)
			{
		    	//we always have objective and learner id
		    	if($g_data->$key->$skey->$user->$package)
				{
		    		$o_value = $g_data->$key->$skey->$user->$package;
		    		$scope = $package;
		    	}
				else
				{
		    		//scope 0
		    		$o_value = $g_data->$key->$skey->$user->{"null"};
		    		//has to be converted to NULL in JS Later
		    		$scope = 0;
		    	}
				
		    	//insert into database
		    	$objective_id = $skey;
		    	$toset = $o_value;
		    	$dbuser = $ilUser->getId();
		    	
				//check for existence (if not, create)		    	
		    	if($key == "satisfied") 
		    	{
		    		$res = $ilDB->queryF('
			    		SELECT * FROM cmi_gobjective
			    		WHERE objective_id = %s 
			    		AND user_id = %s
			    		AND scope_id = %s', 
		    			array('text', 'integer', 'integer'), 
		    			array($objective_id, $dbuser, $scope)
					);
					$ilLog->write("Count is: ".$ilDB->numRows($res));
		    		if(!$ilDB->numRows($res))	
		    		{
		    			$ilDB->manipulateF('
				    		INSERT INTO cmi_gobjective
				    		(objective_id, user_id, satisfied, scope_id) 
				    		VALUES (%s, %s, %s, %s)',
				    		array('text', 'integer', 'text', 'integer'), 
				    		array($objective_id, $dbuser, $toset, $scope)
						);
		    		}
		    		else
		    		{
		    			$ilDB->manipulateF('
				    		UPDATE cmi_gobjective
				    		SET satisfied = %s
		    				WHERE objective_id = %s 
			    			AND user_id = %s
			    			AND scope_id = %s', 
				    		array('text', 'text', 'integer', 'integer'), 
				    		array($toset, $objective_id, $dbuser, $scope)
						);		    			
		    		}
		    	}
		    	if($key == "measure") 
		    	{
		    		$res = $ilDB->queryF('
			    		SELECT * FROM cmi_gobjective
			    		WHERE objective_id = %s 
			    		AND user_id = %s
			    		AND scope_id = %s', 
			    		array('text', 'integer', 'integer'), 
			    		array($objective_id, $dbuser, $scope)
					);
		    		$ilLog->write("Count is: ".$ilDB->numRows($res));
		    		if(!$ilDB->numRows($res))	
		    		{
		    			$ilDB->manipulateF('
				    		INSERT INTO cmi_gobjective
				    		(objective_id, user_id, measure, scope_id) 
				    		VALUES (%s, %s, %s, %s)',
				    		array('text', 'integer', 'text', 'integer'), 
				    		array($objective_id, $dbuser, $toset, $scope)
						);
		    		}
		    		else
		    		{
		    			$ilDB->manipulateF('
				    		UPDATE cmi_gobjective
				    		SET measure = %s
		    				WHERE objective_id =%s 
			    			AND user_id = %s
			    			AND scope_id = %s', 
				    		array('text', 'text', 'integer', 'integer'), 
				    		array($toset, $objective_id, $dbuser, $scope)
						);		    			
		    		}		    		
		    	}
		    	if($key == "status")
				{
					//special handling for status
					$completed = $g_data->$key->$skey->$user->{completed};
					$measure = $g_data->$key->$skey->$user->{measure};
					$satisfied = $g_data->$key->$skey->$user->{satisfied};
					$obj = '-course_overall_status-';	
					$pkg_id = $this->packageId;
					
		    		$res = $ilDB->queryF('
			    		SELECT * FROM cmi_gobjective
			    		WHERE objective_id =%s 
			    		AND user_id = %s
			    		AND scope_id = %s', 
		    			array('text', 'integer', 'integer'), 
		    			array($obj, $dbuser, $pkg_id)
					);
		    		$ilLog->write("Count is: ".$ilDB->numRows($res));
		    		if(!$ilDB->numRows($res))	
		    		{
		    			$ilDB->manipulateF('
				    		INSERT INTO cmi_gobjective
				    		(user_id, status, scope_id, measure, satisfied, objective_id) 
				    		VALUES (%s, %s, %s, %s, %s, %s)',
				    		array('integer', 'text', 'integer', 'text', 'text', 'text'), 
				    		array($dbuser, $completed, $pkg_id, $measure, $satisfied, $obj)
						);
		    		}
		    		else
		    		{
		    			$ilDB->manipulateF('
				    		UPDATE cmi_gobjective
				    		SET status = %s, 
				    			measure = %s,
				    			satisfied = %s 
		    				WHERE objective_id = %s 
			    			AND user_id = %s
			    			AND scope_id = %s', 
				    		array('text', 'text', 'text', 'text', 'integer', 'integer'), 
				    		array($completed, $measure, $satisfied, $obj, $dbuser, $pkd_id)
						);		    			
		    		}
				}	
		    }
		}
	}	
	
	public function specialPage() {

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
		if ($this->page!="_TOC_" && $this->page!="_SEQABANDON_" && $this->page!="_SEQABANDONALL_" ) {
			$this->tpl->setVariable('CLOSE_WINDOW',$lng->txt('seq_close'));
		} else {
			$this->tpl->setVariable('CLOSE_WINDOW',"");	
		}
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
		
		if ($this->slm->getDefaultLessonMode() == "browse") {return;}
				
		$data = json_decode(is_string($data) ? $data : file_get_contents('php://input'));
		$ilLog->write("Got data:". file_get_contents('php://input'));

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

		foreach(self::$schema as $k => &$v)
		{
			$result['schema'][$k] = array_keys($v);

			switch ($k)
			{
				case "node":
					$q = 'SELECT cmi_node.* 
						FROM cmi_node 
						INNER JOIN cp_node ON cmi_node.cp_node_id = cp_node.cp_node_id
						WHERE cmi_node.user_id = %s
						AND cp_node.slm_id = %s';

					break;

				case "comment":
					$q = 'SELECT cmi_comment.* 
						FROM cmi_comment 
						INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_comment.cmi_node_id 
						INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
						WHERE cmi_node.user_id = %s
						AND cp_node.slm_id = %s';
	
					break;

				case "correct_response":
					$q = 'SELECT cmi_correct_response.* 
						FROM cmi_correct_response 
						INNER JOIN cmi_interaction 
						ON cmi_interaction.cmi_interaction_id = cmi_correct_response.cmi_interaction_id 
						INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_interaction.cmi_node_id 
						INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
						WHERE cmi_node.user_id = %s
						AND cp_node.slm_id = %s';

					break;

				case "interaction":
					$q = 'SELECT cmi_interaction.* 
						FROM cmi_interaction 
						INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_interaction.cmi_node_id 
						INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
						WHERE cmi_node.user_id = %s
						AND cp_node.slm_id = %s';
		
					break;

				case "objective":
					$q = 'SELECT cmi_objective.* 
						FROM cmi_objective 
						INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_objective.cmi_node_id 
						INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
						WHERE cmi_node.user_id = %s
						AND cp_node.slm_id = %s';
			
					break;

				case "package":
					$q = 'SELECT usr_data.usr_id user_id, 
						CONCAT(CONCAT(COALESCE(usr_data.firstname, \'\'), \' \'), COALESCE(usr_data.lastname, \'\')) learner_name, 
						sahs_lm.id slm_id , sahs_lm.default_lesson_mode "mode", sahs_lm.credit
						FROM usr_data, cp_package
						INNER JOIN sahs_lm ON cp_package.obj_id = sahs_lm.id 
						WHERE usr_data.usr_id = %s
						AND sahs_lm.id = %s';

					break;

			}
			
			$types = array('integer', 'integer');
			$values = array($userId, $packageId);			
			$res = $ilDB->queryF($q, $types, $values);
			
			$result['data'][$k] = array();
	 		$tmp_result = array();

			while($row = $ilDB->fetchAssoc($res))
			{			
				foreach($row as $key => $value)
				{
					$tmp_result[] = $value;
				}
				$result['data'][$k][] = $tmp_result;
			}
		}
		return $result;	
	}

	private function removeCMIData($userId, $packageId, $cp_node_id=null) 
	{
		global $ilDB;
		
		$delorder = array('correct_response', 'objective', 'interaction', 'comment', 'node');
		//error_log("Delete, User:".$userId."Package".$packageId."Node: ".$cp_node_id);
		foreach($delorder as $k) 
		{
			if(is_null($cp_node_id))
			{
				switch($k)
				{
					case "response":
					 	$q = 'DELETE FROM 
							cmi_correct_response WHERE cmi_interaction_id IN (
							SELECT cmi_interaction.cmi_interaction_id FROM cmi_interaction 
							INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_interaction.cmi_node_id 
							INNER JOIN cp_node ON cmi_node.cp_node_id = cp_node.cp_node_id 
							WHERE cmi_node.user_id = %s
							AND cp_node.slm_id = %s)';
						break;
						
					case "interaction":
						$q = 'DELETE FROM cmi_interaction 
							WHERE cmi_node_id IN (
							SELECT cmi_node.cmi_node_id FROM cmi_node 
							INNER JOIN cp_node ON cmi_node.cp_node_id = cp_node.cp_node_id 
							WHERE cmi_node.user_id = %s
							AND cp_node.slm_id = %s)';
						break;
						
					case "comment":
						$q = 'DELETE FROM cmi_comment 
							WHERE cmi_node_id IN (
							SELECT cmi_node.cmi_node_id FROM cmi_node 
							INNER JOIN cp_node ON cmi_node.cp_node_id = cp_node.cp_node_id 
							WHERE cmi_node.user_id = %s
							AND cp_node.slm_id = %s)';
						break;
						
					case "objective":
						$q = 'DELETE FROM cmi_objective 
							WHERE cmi_node_id IN (
							SELECT cmi_node.cmi_node_id FROM cmi_node 
							INNER JOIN cp_node ON cmi_node.cp_node_id = cp_node.cp_node_id 
							WHERE cmi_node.user_id = %s
							AND cp_node.slm_id = %s)';
						break;
						
					case "node":
						$q = 'DELETE FROM cmi_node 
							WHERE user_id = %s AND cp_node_id IN (
							SELECT cp_node_id FROM cp_node 
							WHERE slm_id = %s)';
						break;
				}
				
				$types = array('integer', 'integer');
				$values = array($userId, $packageId);			
				$ilDB->manipulateF($q, $types, $values);
			}
			else
			{
				switch($k)
				{
					case "correct_response":
						$q = 'DELETE FROM cmi_correct_response 
							WHERE cmi_interaction_id IN (
							SELECT cmi_interaction.cmi_interaction_id FROM cmi_interaction 
							INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_interaction.cmi_node_id 
							WHERE cmi_node.cp_node_id = %s
							AND cmi_node.user_id = %s)';
						break;
						
					case "interaction":
						$q = 'DELETE FROM cmi_interaction 
							WHERE cmi_node_id IN (
							SELECT cmi_node.cmi_node_id FROM cmi_node 
							WHERE cmi_node.cp_node_id = %s
							AND cmi_node.user_id = %s)';
						break;
						
					case "comment":
					 	$q = 'DELETE FROM cmi_comment 
							WHERE cmi_node_id IN (
							SELECT cmi_node.cmi_node_id FROM cmi_node 
							WHERE cmi_node.cp_node_id = %s
							AND cmi_node.user_id = %s)';
						break;

					case "objective":
					 	$q = 'DELETE FROM cmi_objective 
							WHERE cmi_node_id IN (
							SELECT cmi_node.cmi_node_id FROM cmi_node 
							WHERE cmi_node.cp_node_id = %s
							AND cmi_node.user_id = %s)';
						break;
						
					case "node":
						$q = 'DELETE FROM cmi_node WHERE cp_node_id = %s
							AND cmi_node.user_id = %s';
						break;
				}
				
				$types = array('integer', 'integer');
				$values = array($cp_node_id, $userId);			
				$ilDB->manipulateF($q, $types, $values);
			}
		} 
	}
	
	private function setCMIData($userId, $packageId, $data) 
	{
		global $ilDB, $ilLog;	
	
		$result = array();
		$map = array();
		
		if (!$data) return;
	
		$tables = array('node', 'comment', 'interaction', 'objective', 'correct_response');

		foreach($tables as $table)
		{
			$schem = & self::$schema[$table];
			$ilLog->write("SCORM: setCMIData, table -".$table."-".$data->objective);

			if (!is_array($data->$table)) continue;			
				
$ilLog->write("SCORM: setCMIData, table -".$table."-");
			
			// build up numerical index for schema fields
			$i = 0;
			foreach($schem as &$field) 
			{
				$field['no'] = $i++;
			}
			// now iterate through data rows from input
			foreach($data->$table as &$row)
			{
				// first fill some fields that could not be set from client side
				// namely the database id's depending on which table is processed  				
				switch ($table)				
				{
					case 'correct_response':
						$no = $schem['cmi_interaction_id']['no'];
						$ilLog->write("correct_response no: ".$no);
						$ilLog->write("The Row: ".count($row));
						$row[$no] = $map['interaction'][$row[$no]];
						$ilLog->write("Value: ".print_r($map['interaction'],true));
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
		
//$ilLog->write("SCORM: setCMIData, row b");
				$cp_no = $schem['cp_' . $table . '_id']['no'];						 
				$cmi_no = $schem['cmi_' . $table . '_id']['no'];
				
				// get current id for later use
				// this is either a real db id or document unique string generated by client 
				$cmi_id = $row[$cmi_no]; 
				
				// set if field to null, so it will be filled up by autoincrement
				$row[$cmi_no] = null;
				
				$keys = array();
				foreach(array_keys($schem) as $key) 
				{
					$keys[] = $key;					
				}
//$ilLog->write("SCORM: setCMIData, row c");
				if($table === 'node') 
				{
					$this->removeCMIData($userId, $packageId, $row[$cp_no]);
				}				

				$ret = false;

				$ilLog->write("Checking table: ".$table);

				switch($table)
				{
					case 'correct_response':
						$row[$cmi_no] = $ilDB->nextId('cmi_correct_response');

						$ilDB->manipulateF('
							INSERT INTO cmi_correct_response
							(cmi_correct_resp_id, cmi_interaction_id, pattern)
							VALUES (%s, %s, %s)',
							array('integer', 'integer', 'text'),
							$row
						);
						break;
						
					case 'comment':
						$row[$cmi_no] = $ilDB->nextId('cmi_comment');
	
						$ilDB->insert('cmi_comment', array(
							'cmi_comment_id'	=> array('integer', $row[$cmi_no]),
							'cmi_node_id'		=> array('integer', $row[1]),
							'c_comment'			=> array('clob', $row[2]),
							'c_timestamp'		=> array('timestamp', $row[3]),
							'location'			=> array('text', $row[4]),
							'sourceislms'		=> array('integer', $row[5])
						));
						break;
						
					case 'interaction':
						$row[$cmi_no] = $ilDB->nextId('cmi_interaction');
	
						$ilDB->insert('cmi_interaction', array(
							'cmi_interaction_id'	=> array('integer', $row[$cmi_no]),
							'cmi_node_id'			=> array('integer', $row[1]),
							'description'			=> array('clob', $row[2]),
							'id'					=> array('text', $row[3]),
							'latency'				=> array('text', $row[4]),
							'learner_response'		=> array('clob', $row[5]),
							'result'				=> array('text', $row[6]),
							'c_timestamp'			=> array('text', $row[7]),
							'c_type'				=> array('text', $row[8]),
							'weighting'				=> array('float', $row[9])
						));
						break;
						
					case 'objective':
						$row[$cmi_no] = $ilDB->nextId('cmi_objective');
						
						$ilDB->insert('cmi_objective', array(
							'cmi_interaction_id'	=> array('integer', $row[0]),
							'cmi_node_id'			=> array('integer', $row[1]),
							'cmi_objective_id'		=> array('integer', $row[$cmi_no]),
							'completion_status'		=> array('float', $row[3]),
							'description'			=> array('clob', $row[4]),
							'id'					=> array('text', $row[5]),
							'c_max'					=> array('float', $row[6]),
							'c_min'					=> array('float', $row[7]),
							'c_raw'					=> array('float', $row[8]),
							'scaled'				=> array('float', $row[9]),
							'progress_measure'		=> array('float', $row[10]),
							'success_status'		=> array('text', $row[11]),
							'scope'					=> array('text', $row[12])
						));
						break;
						
					case 'node':
						$row[$cmi_no] = $ilDB->nextId('cmi_node');
					
						$node_fields = array(
							'accesscount', 'accessduration', 'accessed', 'activityabsduration', 'activityattemptcount',
							'activityexpduration', 'activityprogstatus', 'attemptabsduration', 'attemptcomplamount', 'attemptcomplstatus',
							'attemptexpduration', 'attemptprogstatus', 'audio_captioning', 'audio_level', 'availablechildren',
							'cmi_node_id', 'completion', 'completion_status', 'completion_threshold', 'cp_node_id',
							'created', 'credit', 'delivery_speed', 'c_entry', 'c_exit',
							'c_language', 'launch_data', 'learner_name', 'location', 'c_max',
							'c_min', 'c_mode', 'modified', 'progress_measure', 'c_raw',
							'scaled', 'scaled_passing_score', 'session_time', 'success_status', 'suspend_data',
							'total_time', 'user_id', 'c_timestamp'
						);
						
						$node_types = array(
							'integer', 'text', 'text', 'text', 'integer', 'text', 'integer', 'text', 'float', 'integer',
							'text', 'integer', 'integer', 'float', 'text', 'integer', 'float', 'text', 'text', 'integer',
							'text', 'text', 'float', 'text', 'text', 'text', 'clob', 'text', 'text', 'float',
							'float', 'text', 'text', 'float', 'float', 'float', 'float', 'text', 'text', 'clob',
							'text', 'integer', 'timestamp'
						);
						
						$node_data = array();
						foreach($node_fields as $key => $node_field)
						{
							if($key == 15)
								$value = $row[$cmi_no];
							else if($key == 42)
								$value = date('Y-m-d H:i:s');
							else
								$value = $row[$key];
							$node_data[$node_field] = array($node_types[$key], $value);
						}					

						$ilLog->write("Want to insert row: ".count($row) );
						$ilDB->insert('cmi_node', $node_data);									
						break;
				}				
				
				$ret = true;

				if(!$ret)
				{
					$return = false;
					break;
				}
				
				// if we process a node save new id into result object that will be feedback for client
				if($table === 'node') 
				{
					$result[(string)$row[$cp_no]] = $row[$cmi_no];
				}
				
				// add new id to mapping table for later use on dependend elements 
				$map[$table][$cmi_id] = $row[$cmi_no];
			}
		}
		return $result;
	}
	
	function quoteJSONArray($a_array)
	{
		global $ilDB;

		if(!is_array($a_array) or !count($a_array))
		{
			return array("''");
		}

		foreach($a_array as $k => $item)
		{	
			if ($item !=  null) {
				$a_array[$k] = $ilDB->quote($item);
			} else {
				$a_array[$k] = "NULL";
			}
		}

		return $a_array;
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
	
	/**
	* Get max. number of attempts allowed for this package
	*/
	function get_max_attempts()
	{		
		global $ilDB;

		$res = $ilDB->queryF(
			'SELECT max_attempt FROM sahs_lm WHERE id = %s', 
			array('integer'),
			array($this->packageId)
		);
		$row = $ilDB->fetchAssoc($res);
		
		return $row['max_attempt']; 
	}
	
	function get_module_version()
	{		
		global $ilDB;

		$res = $ilDB->queryF(
			'SELECT module_version FROM sahs_lm WHERE id = %s', 
			array('integer'),
			array($this->packageId));
		$row = $ilDB->fetchAssoc($res);
		
		return $row['module_version']; 
	}
	
	/**
	* Get number of actual attempts for the user
	*/
	function get_actual_attempts() 
	{
		global $ilDB, $ilUser;

		$res = $ilDB->queryF('
			SELECT rvalue FROM cmi_custom 
			WHERE user_id = %s AND sco_id = %s
			AND lvalue = %s	AND obj_id = %s',
			array('integer', 'integer', 'text', 'integer'),
			array($this->userId, 0, 'package_attempts', $this->packageId)
		);
		$row = $ilDB->fetchAssoc($res);		
		
		$row['rvalue'] = str_replace("\r\n", "\n", $row['rvalue']);
		if($row['rvalue'] == null)
		{
			$row['rvalue'] = 0;
		}
		return $row['rvalue'];
	}
	
	/**
	* Increases attempts by one for this package
	*/
	function increase_attempt()
	{
		global $ilDB, $ilUser;
		
		//get existing account - sco id is always 0
		$res = $ilDB->queryF('
			SELECT rvalue FROM cmi_custom 
			WHERE user_id = %s
			AND sco_id = %s
			AND lvalue = %s
			AND obj_id = %s',
			array('integer', 'integer','text', 'integer'),
			array($this->userId, 0, 'package_attempts', $this->packageId)
		);
		$row = $ilDB->fetchAssoc($res);
		
		$tmp_row = $row;
		
		$row['rvalue'] = str_replace("\r\n", "\n", $row['rvalue']);
		if($row['rvalue'] == null)
		{
			$row['rvalue'] = 0;
		}
		$new_rec =  $row['rvalue'] + 1;
		
		//increase attempt by 1
		if(!is_array($tmp_row) || !count($tmp_row))
		{
			$ilDB->manipulateF('
				INSERT INTO cmi_custom (rvalue, user_id, sco_id, obj_id, lvalue, c_timestamp) 
				VALUES(%s, %s, %s, %s, %s, %s)', 
				array('text', 'integer', 'integer', 'integer', 'text', 'timestamp'), 
				array($new_rec, $this->userId, 0, $this->packageId, 'package_attempts', date('Y-m-d H:i:s'))
			);
		}
		else
		{
			$ilDB->manipulateF('
				UPDATE cmi_custom 
				SET rvalue = %s,
					c_timestamp = %s
				WHERE 	user_id = %s 
				AND		sco_id = %s 
				AND		obj_id = %s 
				AND		lvalue = %s',
				array('text', 'timestamp', 'integer', 'integer', 'integer','text'), 
				array($new_rec, date('Y-m-d H:i:s'), $this->userId, 0, $this->packageId, 'package_attempts')
			);
		}
	}	
	
	/**
	* save the active module version to scorm_tracking
	*/
	function save_module_version()
	{
		global $ilDB, $ilUser;

		$res = $ilDB->queryF('
			SELECT * FROM cmi_custom 
			WHERE user_id = %s
			AND sco_id = %s
			AND lvalue = %s
			AND obj_id = %s',
			array('integer', 'integer', 'text', 'integer'),
			array($this->userId, 0, 'module_version', $this->packageId)
		);		
		if(!$ilDB->numRows($res))
		{
			$ilDB->manipulateF('
				INSERT INTO cmi_custom (rvalue, user_id, sco_id, obj_id, lvalue, c_timestamp)
				VALUES(%s, %s, %s, %s, %s, %s)',  
				array('text', 'integer', 'integer', 'integer', 'text', 'timestamp'),
				array($this->get_Module_Version(), $this->userId, 0, $this->packageId, 'module_version', date('Y-m-d H:i:s'))
			);
		}
		else
		{
			$ilDB->manipulateF('
				UPDATE cmi_custom 
				SET rvalue = %s, 
					c_timestamp = %s
				WHERE user_id = %s 
				AND	sco_id = %s 
				AND obj_id = %s 
				AND	lvalue = %s',  
				array('text', 'timestamp', 'integer', 'integer', 'integer', 'text'),
				array($this->get_Module_Version(), date('Y-m-d H:i:s'),	$this->userId, 0, $this->packageId, 'module_version')
			);	
		}
	}
}
?>