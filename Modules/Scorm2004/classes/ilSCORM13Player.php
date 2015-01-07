<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/YUI/classes/class.ilYuiUtil.php");
require_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");

/**
* @author  Hendrik Holtmann <holtmann@mac.com>, Alfred Kohnert <alfred.kohnert@bigfoot.com>, Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
* @version $Id$
* @ilCtrl_Calls ilSCORM13Player:
*/
class ilSCORM13Player
{

	const ENABLE_GZIP = 0;
	
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

		//erase next?
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
		$this->ref_id = $_GET['ref_id'];
		$this->userId=$ilUser->getID();
	
		if ($_GET['envEditor'] != null) {
			$this->envEditor = $_GET['envEditor'];
		} else {	
			$this->envEditor = 0;
		}	
		
	}

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $ilAccess, $ilLog, $ilUser, $lng, $ilias;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"), $ilias->error_obj->WARNING);
		}
		
//$ilLog->write("SCORM2004 Player cmd: ".$cmd);

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
//				$this->writeGObjective();
				break;		

			case 'getGobjective':	
				$this->readGObjective();
				break;

			case 'getSharedData':
				$this->readSharedData($_GET['node_id']);
				break;
				
			case 'setSharedData':
				$this->writeSharedData($_GET['node_id']);
				break;
				
			case 'cmi':

				if ($_SERVER['REQUEST_METHOD']=='POST') {
					include_once './Modules/Scorm2004/classes/class.ilSCORM2004StoreData.php';
					ilSCORM2004StoreData::persistCMIData($this->userId, $this->packageId, 
					$this->slm->getDefaultLessonMode(), $this->slm->getComments(), 
					$this->slm->getInteractions(), $this->slm->getObjectives());
					//error_log("Saved CMI Data");
				} else {
					$this->fetchCMIData();
				}
				break;
			
			case 'specialPage':
			 	$this->specialPage();
				break;

			case 'debugGUI':	
			 	$this->debugGUI();
				break;
			case 'postLogEntry':
				$this->postLogEntry();
				break;
			case 'liveLogContent':
				$this->liveLogContent();
				break;
			case 'downloadLog':
				$this->downloadLog();
				break;	
			case 'openLog':
				$this->openLog();
				break;	

			case 'pingSession':
				$this->pingSession();
				break;
			case 'scormPlayerUnload':
				include_once './Modules/Scorm2004/classes/class.ilSCORM2004StoreData.php';
				ilSCORM2004StoreData::scormPlayerUnload($this->userId, $this->packageId);
				break;
				
			// case 'getConfigForPlayer':
				// $this->getConfigForPlayer();
				// break;
			default:
				$this->getPlayer();
				break;
		}
		
	}
	
	function getRTEjs()
	{
		$js_data = file_get_contents("./Modules/Scorm2004/scripts/buildrte/rte.js");
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
		
	//config data also used for SOP
	public function getConfigForPlayer()
	{
		global $ilUser,$ilias,$ilSetting;
		$initSuspendData = null;
		$cmi_learner_id = (string) $ilUser->getID();
		$lm_set = new ilSetting("lm");
		if($lm_set->get("scorm_login_as_learner_id") == 1) {
			$cmi_learner_id = (string) $ilUser->getLogin();
		}
		$config = array
		(
			'scope'=>$this->getScope(),
			'learner_id' => (string) $ilUser->getID(),
			'cmi_learner_id' => $cmi_learner_id,
			'course_id' => (string) $this->packageId,
			'learner_name' => $ilUser->getFirstname()." ".$ilUser->getLastname(),
			'mode' => 'normal',//TODO CHECK CP_PACKAGE
			'credit' => 'credit',
			'auto_review' => $this->slm->getAutoReviewChar(),
			'hide_navig' => $this->slm->getHideNavig(),
			'hide_menu' => $this->slm->getNoMenu(),
			'ie_force_render' => $this->slm->getIe_force_render(),
			'fourth_edition' => $this->slm->getFourth_edition(),
			'sequencing_enabled' => $this->slm->getSequencing(),
			'interactions_storable' => $this->slm->getInteractions(),
			'objectives_storable' => $this->slm->getObjectives(),
			'comments_storable' => $this->slm->getComments(),
			'time_from_lms' => $this->slm->getTime_from_lms(),
			'auto_last_visited' => $this->slm->getAuto_last_visited(),
			'checkSetValues' => $this->slm->getCheck_values(),
			'auto_suspend' => $this->slm->getAutoSuspend(),
			'suspend_data' => $initSuspendData,
			'cp_data' => null,
			'cmi_data' => null,
			'adlact_data' => null,
			'globalobj_data' => null
		);
		include_once './Modules/ScormAicc/classes/SCORM/class.ilObjSCORMInitData.php';
		$status=ilObjSCORMInitData::getStatus($this->packageId, $ilUser->getID());
		$status['last_visited']=null;
		if($this->slm->getAuto_last_visited()) 
		{
			$status['last_visited']=$this->get_last_visited($this->packageId, $ilUser->getID());
		}
		$config['status'] = $status;

		return $config;
	}

	public function getPlayer()
	{
		global $ilUser,$lng, $ilias, $ilSetting;
		// player basic config data
		

		$initSuspendData = null;
		$initAdlactData = null;
		if ($this->slm->getSequencing() == true) {
			$initSuspendData = json_decode($this->getSuspendDataInit());
			$initAdlactData = json_decode($this->getADLActDataInit());
			$initGlobalobjData = $this->readGObjectiveInit();
		}

		$config = $this->getConfigForPlayer();

		//session
		if ($this->slm->getSession()) {
//			$session_timeout = (int)($ilias->ini->readVariable("session","expire"))/2;
			$session_timeout = (int)ilSession::getIdleValue()/2;
		} else {
			$session_timeout = 0;
		}
		$config['session_ping'] = $session_timeout;

		//url strings
		$store_url = 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=cmi&ref_id='.$_GET["ref_id"];
		$unload_url = 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=scormPlayerUnload&ref_id='.$_GET["ref_id"];
		if ($this->slm->getSessionDeactivated()){
			$store_url = 'storeScorm2004.php?package_id='.$this->packageId.'&ref_id='.$_GET["ref_id"].'&client_id='.$this->ilias->client_id.'&do=store';
			$unload_url = 'storeScorm2004.php?package_id='.$this->packageId.'&ref_id='.$_GET["ref_id"].'&client_id='.$this->ilias->client_id.'&do=unload';
		}
		$config['cp_url']				= 'ilias.php?baseClass=ilSAHSPresentationGUI' . '&cmd=cp&ref_id='.$_GET["ref_id"];
		$config['cmi_url']				= 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=cmi&ref_id='.$_GET["ref_id"];
		$config['store_url']			= $store_url;
		$config['get_adldata_url']		= 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=getSharedData&ref_id='.$_GET["ref_id"];
		$config['set_adldata_url']		= 'ilias.php?baseClass=ilSAHSPresentationGUI' . '&cmd=setSharedData&ref_id=' . $_GET["ref_id"];
		$config['adlact_url']			= 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=adlact&ref_id='.$_GET["ref_id"];
		$config['specialpage_url']		= 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=specialPage&ref_id='.$_GET["ref_id"];
		$config['suspend_url']			= 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=suspend&ref_id='.$_GET["ref_id"];
		$config['get_suspend_url']		= 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=getSuspend&ref_id='.$_GET["ref_id"];
		//next 2 lines could be deleted later
		$config['gobjective_url']		= 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=gobjective&ref_id='.$_GET["ref_id"];
		$config['get_gobjective_url']	= 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=getGobjective&ref_id='.$_GET["ref_id"];
		$config['ping_url']				= 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=pingSession&ref_id='.$_GET["ref_id"];
		$config['scorm_player_unload_url']= $unload_url;
		$config['post_log_url']			= 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=postLogEntry&ref_id='.$_GET["ref_id"];
		$config['livelog_url']			= 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=liveLogContent&ref_id='.$_GET["ref_id"];
		$config['package_url']			= $this->getDataDirectory()."/";

		//editor
		$config['envEditor']			= $this->envEditor;
		
		//debug
		$config['debug']				= $this->slm->getDebug();
		$config['debug_fields']			= $this->getDebugValues();
		$config['debug_fields_test']	= $this->getDebugValues(true);


		//language strings
		$langstrings['btnStart'] = $lng->txt('scplayer_start');
		$langstrings['btnExit'] = $lng->txt('scplayer_exit');
		$langstrings['btnExitAll'] = $lng->txt('scplayer_exitall');
		$langstrings['btnSuspendAll'] = $lng->txt('scplayer_suspendall');
		$langstrings['btnPrevious'] = $lng->txt('scplayer_previous');
		$langstrings['btnContinue'] = $lng->txt('scplayer_continue');
		$langstrings['btnhidetree']=$lng->txt('scplayer_hidetree');
		$langstrings['btnshowtree']=$lng->txt('scplayer_showtree');
		$langstrings['linkexpandTree']=$lng->txt('scplayer_expandtree');
		$langstrings['linkcollapseTree']=$lng->txt('scplayer_collapsetree');
		$config['langstrings'] = $langstrings;
		
		//template variables	
		//$this->tpl = new ilTemplate("tpl.scorm2004.player.html", false, false, "Modules/Scorm2004");
		$this->tpl = new ilTemplate("tpl.scorm2004.player.html", true, true, "Modules/Scorm2004");

		// include ilias rte css, if given
		$rte_css = $this->slm->getDataDirectory()."/ilias_css_4_2/css/style.css";
		if (is_file($rte_css))
		{
			$this->tpl->setCurrentBlock("rte_css");
			$this->tpl->setVariable("RTE_CSS", $rte_css);
			$this->tpl->parseCurrentBlock();
		}


		$this->tpl->setVariable('JSON_LANGSTRINGS', json_encode($langstrings));
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		$this->tpl->setVariable('YUI_PATH', ilYuiUtil::getLocalPath());
		$this->tpl->setVariable('TREE_JS', "./Services/UIComponent/NestedList/js/ilNestedList.js");
		$this->tpl->setVariable($langstrings);
		$this->tpl->setVariable('DOC_TITLE', 'ILIAS SCORM 2004 Player');
		if ($this->slm->getIe_compatibility()) $this->tpl->setVariable('IE_COMPATIBILITY', '<meta http-equiv="X-UA-Compatible" content="IE=7" />');
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable('INIT_CP_DATA', json_encode(json_decode($this->getCPDataInit())));
		$this->tpl->setVariable('INIT_CMI_DATA', json_encode($this->getCMIData($this->userId, $this->packageId)));
		$this->tpl->setVariable('INIT_ADLACT_DATA', json_encode($initAdlactData));
		$this->tpl->setVariable('INIT_GLOBALOBJ_DATA', json_encode($initGlobalobjData));
		$this->tpl->setVariable('JS_DATA', json_encode($config));
		list($tsfrac, $tsint) = explode(' ', microtime());
		$this->tpl->setVariable('TIMESTAMP', sprintf('%d%03d', $tsint, 1000*(float)$tsfrac));
		$this->tpl->setVariable('BASE_DIR', './Modules/Scorm2004/');
		$this->tpl->setVariable('TXT_COLLAPSE',$lng->txt('scplayer_collapsetree'));
		if ($this->slm->getDebug()) {
			$this->tpl->setVariable('TXT_DEBUGGER',$lng->txt('scplayer_debugger'));
			$this->tpl->setVariable('DEBUG_URL',"PopupCenter('ilias.php?baseClass=ilSAHSPresentationGUI&cmd=debugGUI&ref_id=".$_GET["ref_id"]."','Debug',800,600);");
		} else {
			$this->tpl->setVariable('TXT_DEBUGGER','');
			$this->tpl->setVariable('DEBUG_URL','');
		}

		//set icons path
		$this->tpl->setVariable('INLINE_CSS', ilSCORM13Player::getInlineCss());

		//include scripts
		if ($this->slm->getCacheDeactivated()){
			$this->tpl->setVariable('JS_SCRIPTS', 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=getRTEjs&ref_id='.$_GET["ref_id"]);
		} else {
			$this->tpl->setVariable('JS_SCRIPTS', './Modules/Scorm2004/scripts/buildrte/rte-min.js');
		}

		//disable top menu
		if ($this->slm->getNoMenu()=="y") {
			$this->tpl->setVariable("VAL_DISPLAY", "style=\"display:none;\"");
		} else {
			$this->tpl->setVariable("VAL_DISPLAY", "");
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
		$this->increase_attemptAndsave_module_version();
		$this->resetSharedData();

		$this->tpl->show("DEFAULT", false);
	}

	/**
	 * Get inline css
	 */
	function getInlineCSS()
	{
		$is_tpl = new ilTemplate("tpl.scorm2004.inlinecss.html", true, true, "Modules/Scorm2004");
		$is_tpl->setVariable('IC_ASSET', ilUtil::getImagePath("scorm/asset.svg",false));
		$is_tpl->setVariable('IC_COMPLETED', ilUtil::getImagePath("scorm/completed.svg",false));
		$is_tpl->setVariable('IC_NOTATTEMPTED', ilUtil::getImagePath("scorm/not_attempted.svg",false));
		$is_tpl->setVariable('IC_RUNNING', ilUtil::getImagePath("scorm/running.svg",false));
		$is_tpl->setVariable('IC_INCOMPLETE', ilUtil::getImagePath("scorm/incomplete.svg",false));
		$is_tpl->setVariable('IC_PASSED', ilUtil::getImagePath("scorm/passed.svg",false));
		$is_tpl->setVariable('IC_FAILED', ilUtil::getImagePath("scorm/failed.svg",false));
		$is_tpl->setVariable('IC_BROWSED', ilUtil::getImagePath("scorm/browsed.svg",false));
		return $is_tpl->get();
	}

	public function getCPData()
	{
		$jsdata = $this->getCPDataInit();
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
	function getCPDataInit()
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

		return $jsdata;
	}

	
	public function getADLActDataInit()
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
		return $activitytree;
	}

	public function getADLActData()
	{
		$activitytree = $this->getADLActDataInit();
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

	public function getSuspendDataInit()
	{
		global $ilDB, $ilUser;
		
		$res = $ilDB->queryF(
			'SELECT data FROM cp_suspend WHERE obj_id = %s AND user_id = %s',
			array('integer', 'integer'),
			array($this->packageId, $ilUser->getId())
		);
		$data = $ilDB->fetchAssoc($res);

		//delete delivered suspend data
		$ilDB->manipulateF(
			'DELETE FROM cp_suspend WHERE obj_id = %s AND user_id = %s',
			array('integer', 'integer'),
			array($this->packageId, $ilUser->getId())
		);
		return $data['data'];
	}

	public function getSuspendData()
	{
		$suspend_data = $this->getSuspendDataInit();
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
	
	public function readGObjectiveInit()
	{
		global $ilDB, $ilUser;
		
		//get json string
		$g_data = new stdClass();

		$global_to_system = 1;

		$res = $ilDB->queryF('SELECT global_to_system FROM cp_package WHERE obj_id = %s',
			array('integer'),
			array($this->packageId)
		);
		while($data = $ilDB->fetchAssoc($res)) 
		{
			$global_to_system = $data['global_to_system'];
		}

		$query = 'SELECT objective_id, scope_id, satisfied, measure, user_id, 
						 score_min, score_max, score_raw, completion_status, 
						 progress_measure '
		       . 'FROM cmi_gobjective, cp_node, cp_mapinfo ' 
			   . 'WHERE (cmi_gobjective.objective_id <> %s AND cmi_gobjective.status IS NULL ' 
			   . 'AND cp_node.slm_id = %s AND cp_node.nodename = %s '
			   . 'AND cp_node.cp_node_id = cp_mapinfo.cp_node_id '  
			   . 'AND cmi_gobjective.objective_id = cp_mapinfo.targetobjectiveid) '
			   . 'GROUP BY objective_id, scope_id, satisfied, measure, user_id,
			               score_min, score_max, score_raw, completion_status, 
			               progress_measure';
		$res = $ilDB->queryF(
			$query,
			array('text', 'integer', 'text'),
			array('-course_overall_status-', $this->packageId, 'mapInfo')
		);		
		while($row = $ilDB->fetchAssoc($res))
		{
			if (($global_to_system == 1 && $row['scope_id'] == 0) || ($global_to_system == 0 && $row['scope_id'] == $this->packageId)) {
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
				
				if($row['score_raw'] != NULL)
				{
					$toset = $row['score_raw'];
					$g_data->{"score_raw"}->{$objective_id}->{$learner}->{$scope} = $toset;
				}
				
				if($row['score_min'] != NULL)
				{
					$toset = $row['score_min'];
					$g_data->{"score_min"}->{$objective_id}->{$learner}->{$scope} = $toset;
				}
				
				if($row['score_max'] != NULL)
				{
					$toset = $row['score_max'];
					$g_data->{"score_max"}->{$objective_id}->{$learner}->{$scope} = $toset;
				}
				
				if($row['progress_measure'] != NULL)
				{
					$toset = $row['progress_measure'];
					$g_data->{"progress_measure"}->{$objective_id}->{$learner}->{$scope} = $toset;
				}
				
				if($row['completion_status'] != NULL)
				{
					$toset = $row['completion_status'];
					$g_data->{"completion_status"}->{$objective_id}->{$learner}->{$scope} = $toset;
				}
			}
		}
		return $g_data;
	}

	public function readGObjective()
	{

		$gobjective_data = json_encode($this->readGObjectiveInit());
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
	

	//Read the shared datascores for a given SCO 
	public function readSharedData($sco_node_id)
	{

		global $ilDB, $ilUser;
		$dataStores = array( "data" => array(),
							 "permissions" => array());
		$readPermissions = array();
		
		$query = 'SELECT target_id, read_shared_data, write_shared_data '
		       . 'FROM cp_datamap ' 
			   . 'WHERE slm_id = %s '
			   . 'AND sco_node_id = %s ' 
			   . 'GROUP BY target_id, read_shared_data, write_shared_data';
			   
		
		$res = $ilDB->queryF(
			$query,
			array('integer', 'integer'),
			array($this->packageId, $sco_node_id)
		);
		
		//Pass 1: Get all the shared data target_ids	
		//		  for this content package
		while($row = $ilDB->fetchAssoc($res))
		{
			$storeVal = ($row['read_shared_data'] == 0 && $row['write_shared_data'] == 1 )
				    ? 'notWritten' 
				    : null;

			$dataStores["data"][$row['target_id']] = array( "store" => $storeVal,
									"readSharedData" => $row['read_shared_data'],
									"writeSharedData" => $row['write_shared_data']);
			$dataStores["readPermissions"][$row['target_id']] = $row['read_shared_data'];	
		}
		
		if(count($dataStores) < 1)
		{
			//If there are no datastores, then return nothing
			echo "";
			exit();		
		}
		else if ($dataStores["readPermissions"] != null && array_sum($dataStores["readPermissions"]) != 0)
		{
			
			//If there exists at least one readSharedData permission, then 
			//fill in the existing values (if any) already in the store.
			
			//Create the params to add to the Pass 2 query (get existing values)
			$params = array("types" => array("integer", "integer"),
						    "values" => array($this->userId, $this->packageId));
			
			$paramTemplate = '';
			
			//See if readSharedData is set for each datamap.
			//If set to true, then add it to the search query
			foreach($dataStores["data"] as $key => $val)
			{
				if($dataStores["readPermissions"][$key] == 1 
					&& $dataStores["data"][$key]["store"] != 'notWritten')
				{
					$params["types"][] = "text";
					$params["values"][] = $key;
					$paramTemplate .= '%s, ';
				} 
			}
			
			//Get rid of the trailing ', '
			$paramTemplate = substr($paramTemplate, 0, strlen($paramTemplate) - 2);
			
			//Pass 2: Query for values previously saved by the user
			$query = 'SELECT target_id, store '
				   . 'FROM adl_shared_data '
				   . 'WHERE user_id = %s '
				   . 'AND slm_id = %s '
				   . 'AND target_id IN (' . $paramTemplate . ')';
				   
			
			$res = $ilDB->queryF( 
				$query,
				$params["types"],
				$params["values"]
			);
		
			while($row = $ilDB->fetchAssoc($res))
			{
				$dataStores["data"][$row['target_id']]["store"] = $row['store'];
			}
		}	

		header('Content-Type: text/javascript; charset=UTF-8');
		
		echo json_encode($dataStores["data"]);	
	}
	
	public function writeSharedData($sco_node_id)
	{
		global $ilDB, $ilUser;
		$g_data = json_decode(file_get_contents('php://input'));
			
		//Step 1: Get the writeable stores for this SCO that already have values
		$query = 'SELECT dm.target_id, sd.store '
			   . 'FROM cp_datamap dm '
			   . 'LEFT JOIN adl_shared_data sd '
			   . 'ON(dm.slm_id = sd.slm_id AND dm.target_id = sd.target_id) '
			   . 'WHERE sco_node_id = %s '
			   . 'AND dm.slm_id = %s '
			   . 'AND write_shared_data = 1 '
			   . 'AND user_id = %s';
		
		$res = $ilDB->QueryF(
			$query,
			array('integer', 'integer', 'integer'),
			array($sco_node_id, $this->packageId, $this->userId)
		);
		
		$dataStores = array();
		$originalVals = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$id = $row['target_id'];
			$dataStores[$id] = $g_data->{$id};
			$originalVals[$id] = $row['store'];
		}
		

		//Step 2: Add the writeable stores
		foreach($g_data as $key => $obj)
		{
			//If it's already created in adl_shared_data, we 
			//need to update it.
			if(array_key_exists($key, $dataStores) )	
			{
				if($obj == 'notWritten') continue;

				$query = 'UPDATE adl_shared_data '
					   . 'SET store = %s '
					   . 'WHERE user_id = %s '
					   . 'AND target_id = %s '
					   . 'AND slm_id = %s ';
				
				$ilDB->manipulateF(
					$query,
					array('text', 'integer', 'text', 'integer'),
					array($dataStores[$key], $this->userId, $key, $this->packageId)	
					);
			} else
			{
				//Check for writability
				$res = $ilDB->queryF(
					'SELECT write_shared_data '
					  . 'FROM cp_datamap '
					  . 'WHERE target_id = %s '
					  . 'AND slm_id = %s '
					  . 'AND sco_node_id = %s',
					 array('text', 'integer', 'integer'),
					 array($key, $this->packageId, $sco_node_id));
				
				$row = $ilDB->fetchAssoc($res);
				if($row["write_shared_data"] != 1)
				{
					 continue;
				}
				
				//If it's writeable, then add the new value into the database
				$res = $ilDB->manipulateF(
					'INSERT INTO adl_shared_data VALUES (%s, %s, %s, %s)',
					array('integer', 'integer', 'text', 'text'),
					array($this->packageId, $this->userId, $key, $obj));			
			}
		}
	    echo "1";
	    exit();
		
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

	public function getCMIData($userId, $packageId) 
	{
		global $ilDB;
		
		$i_check=0;
		$result = array(
			'schema' => array(), 
			'data' => array()
		);

		foreach(self::$schema as $k => &$v)
		{
			$result['schema'][$k] = array_keys($v);
			$q = '';
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
					if ($i_check>7) {
						$i_check-=8;
						if ($this->slm->getComments()) $q = 'SELECT 
							cmi_comment.cmi_comment_id, 
							cmi_comment.cmi_node_id, 
							cmi_comment.c_comment, 
							cmi_comment.c_timestamp, 
							cmi_comment.location, 
							cmi_comment.sourceislms 
							FROM cmi_comment 
							INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_comment.cmi_node_id 
							INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
							WHERE cmi_node.user_id = %s
							AND cp_node.slm_id = %s 
							ORDER BY cmi_comment.cmi_comment_id';
					}

					break;

				case "correct_response":
					if ($i_check>3) {
						$i_check-=4;
						if ($this->slm->getInteractions()) $q = 'SELECT cmi_correct_response.* 
							FROM cmi_correct_response 
							INNER JOIN cmi_interaction 
							ON cmi_interaction.cmi_interaction_id = cmi_correct_response.cmi_interaction_id 
							INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_interaction.cmi_node_id 
							INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
							WHERE cmi_node.user_id = %s
							AND cp_node.slm_id = %s 
							ORDER BY cmi_correct_response.cmi_correct_resp_id';
					}
					break;

				case "interaction":
					if ($i_check>1) {
						$i_check-=2;
						if ($this->slm->getInteractions()) $q = 'SELECT 
							cmi_interaction.cmi_interaction_id, 
							cmi_interaction.cmi_node_id, 
							cmi_interaction.description, 
							cmi_interaction.id, 
							cmi_interaction.latency, 
							cmi_interaction.learner_response, 
							cmi_interaction.result, 
							cmi_interaction.c_timestamp, 
							cmi_interaction.c_type, 
							cmi_interaction.weighting
							FROM cmi_interaction 
							INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_interaction.cmi_node_id 
							INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
							WHERE cmi_node.user_id = %s
							AND cp_node.slm_id = %s 
							ORDER BY cmi_interaction.cmi_interaction_id';
					}
					break;

				case "objective":
					if ($i_check>0) {
						if ($this->slm->getObjectives()) $q = 'SELECT 
							cmi_objective.cmi_interaction_id, 
							cmi_objective.cmi_node_id, 
							cmi_objective.cmi_objective_id, 
							cmi_objective.completion_status, 
							cmi_objective.description, 
							cmi_objective.id, 
							cmi_objective.c_max, 
							cmi_objective.c_min, 
							cmi_objective.c_raw, 
							cmi_objective.scaled, 
							cmi_objective.progress_measure, 
							cmi_objective.success_status, 
							cmi_objective.scope 
							FROM cmi_objective 
							INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_objective.cmi_node_id 
							INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
							WHERE cmi_node.user_id = %s
							AND cp_node.slm_id = %s 
							ORDER BY cmi_objective.cmi_objective_id';
					}
					break;

				case "package"://delete because data exist except of learner_name
					$q = 'SELECT usr_data.usr_id user_id, 
						CONCAT(CONCAT(COALESCE(usr_data.firstname, \'\'), \' \'), COALESCE(usr_data.lastname, \'\')) learner_name, 
						sahs_lm.id slm_id , sahs_lm.default_lesson_mode "mode", sahs_lm.credit
						FROM usr_data, cp_package
						INNER JOIN sahs_lm ON cp_package.obj_id = sahs_lm.id 
						WHERE usr_data.usr_id = %s
						AND sahs_lm.id = %s';

					break;

			}

			$result['data'][$k] = array();
			if ($q != '') {
				$types = array('integer', 'integer');
				$values = array($userId, $packageId);
				$res = $ilDB->queryF($q, $types, $values);

				while($row = $ilDB->fetchAssoc($res))
				{
					$tmp_result = array();
					foreach($row as $key => $value)
					{
						if ($k == "comment" && $key == "c_timestamp" && strpos($value,' ')==10) $value = str_replace(' ','T',$value);
						$tmp_result[] = $value;
						if($k=="node" && $key=="additional_tables" && $i_check<$value){
							$i_check=$value;
//							$GLOBALS['ilLog']->write($i_check);
						}
					}
					$result['data'][$k][] = $tmp_result;
				}
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
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		return ilObjMediaObject::getMimeType($filename);
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
		include_once "./Modules/ScormAicc/classes/SCORM/class.ilObjSCORMInitData.php";
		return ilObjSCORMInitData::get_max_attempts($this->packageId);
	}
	
	function get_Module_Version()
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
		$val_set = $ilDB->queryF('SELECT package_attempts FROM sahs_user WHERE obj_id = %s AND user_id = %s',
			array('integer','integer'), array($this->packageId,$this->userId));
		$val_rec = $ilDB->fetchAssoc($val_set);
		$attempts = $val_rec["package_attempts"];
		if ($attempts == null) $attempts = 0;
		return $attempts;
	}
	
	/**
	* Increases attempts by one and saves module_version for this package
	*/
	function increase_attemptAndsave_module_version()
	{
		global $ilDB, $ilUser;
		$res = $ilDB->queryF(
			'SELECT package_attempts,count(*) cnt FROM sahs_user WHERE obj_id = %s AND user_id = %s GROUP BY package_attempts',
			array('integer','integer'),
			array($this->slm->getId(),$ilUser->getId()));
		$val_rec = $ilDB->fetchAssoc($res);
		if ($val_rec["cnt"] == 0) { //offline_mode could be inserted
			$attempts = 1;
			$ilDB->manipulateF(
				'INSERT INTO sahs_user (obj_id,user_id,package_attempts,module_version,last_access) VALUES(%s,%s,%s,%s,%s)',
				array('integer', 'integer', 'integer', 'integer', 'timestamp'),
				array($this->slm->getId(), $ilUser->getId(), $attempts, $this->slm->getModuleVersion(), date('Y-m-d H:i:s')));
		} else {
			$attempts = $val_rec["package_attempts"];
			if ($attempts == null) $attempts = 0;
			$attempts++;
			$ilDB->manipulateF(
				'UPDATE sahs_user SET package_attempts = %s, module_version = %s, last_access=%s WHERE obj_id = %s AND user_id = %s ',
				array('integer', 'integer', 'timestamp', 'integer', 'integer'),
				array($attempts, $this->slm->getModuleVersion(), date('Y-m-d H:i:s'), $this->slm->getId(), $ilUser->getId()));
		}
	}

	function resetSharedData()
	{
		global $ilDB;
		//Reset the shared data stores if sharedDataGlobalToSystem is false
		$res = $ilDB->queryF(' 
					  SELECT shared_data_global_to_system   
			   	      FROM cp_package  
			          WHERE obj_id = %s',
			          array('integer'),
			          array($this->packageId)
				);

		$shared_global_to_sys = $ilDB->fetchObject($res)->shared_data_global_to_system;
		
		$res = $ilDB->queryF('
					  SELECT data
					  FROM cp_suspend
					  WHERE obj_id = %s 
					  AND user_id = %s',
					  array('integer', 'integer'),
					  array($this->packageId, $this->userId)
			   );
		
		$suspended = false;
		
		$dat = $ilDB->fetchObject($res)->data;
		if($dat != null && $dat != '' ) $suspended = true;
		
		if($shared_global_to_sys == 0 && !$suspended)
		{
			$ilDB->manipulateF('
				DELETE FROM adl_shared_data 
				WHERE slm_id = %s 
				AND user_id = %s',
				array('integer', 'integer'),
				array($this->packageId, $this->userId)
			);
		}
	}

	//debug extentions
	
	private function getNodeData($sco_id,$fh)
	{
		global $ilDB,$ilLog;
		
		$fieldList = "cmi_node.cp_node_id, cmi_node.completion_threshold, cmi_node.c_exit, cmi_node.completion_status, cmi_node.progress_measure, cmi_node.success_status, cmi_node.scaled, cmi_node.session_time,".
		  		  	 "cmi_node.c_min, cmi_node.c_max, cmi_node.c_raw, cmi_node.location, cmi_node.suspend_data, cmi_node.scaled_passing_score, cmi_node.total_time";
		
		
		$res = $ilDB->queryF('
					  SELECT '.$fieldList.'
					  FROM cmi_node,cp_node,cp_item
					  WHERE cp_node.slm_id = %s
					  AND cp_node.cp_node_id = cp_item.cp_node_id
					  AND cp_item.id = %s
					  AND cmi_node.cp_node_id = cp_item.cp_node_id
					  AND cmi_node.user_id = %s',
					  array('integer','text','integer'),
					  array($this->packageId, $sco_id, $this->userId)
				);
		$row = $ilDB->fetchAssoc($res);
		$ilLog->write("DEBUG SQL".$row);
		return $row;
	}

	private function logTmpName()
	{
		$filename = $this->logDirectory()."/".$this->packageId.".tmp";
		if (!file_exists($filename)) {
			umask(0000);
			$fHandle = fopen($filename, 'a') or die("can't open file");
			fwrite($fHandle, $string);
			fclose($fHandle);
		}
		return $filename;
	}
	
	private function summaryFileName()
	{
		$filename = $this->logDirectory()."/".$this->packageId."_summary_".$this->get_actual_attempts();
		$adder = "0";
		$suffix = ".csv";
		$i = 0;
		while (file_exists($filename."_".$adder.$suffix)) {
			$i++;
			$adder = (string) $i;
		}
		$retname = $filename."_".$adder.$suffix;
		
		if (!file_exists($retname)) {
			umask(0000);
			$fHandle = fopen($retname, 'a') or die("can't open file");
			fwrite($fHandle, $string);
			fclose($fHandle);
		}
		return $retname;
	}
	
	private function logFileName()
	{
		global $lng;
		$lng->loadLanguageModule("scormdebug");

		$filename = $this->logDirectory()."/".$this->packageId."_".$this->get_actual_attempts();
		$path_csv = $filename.".csv";
		$path_txt = $filename.".html";
		if (!file_exists($path_csv)) {
			umask(0000);
			$fHandle = fopen($path_csv, 'a') or die("can't open file");
			$string = '"CourseId";"ScoId";"ScoTitle";"Timestamp";"Action";"Key";"Value";"Return Value";"Errorcode";"Timespan";"ErrorDescription"'."\n";
			fwrite($fHandle, $string);
			fclose($fHandle);
		} 
		if (!file_exists($path_txt)) {
			if (file_exists($this->logTmpName())) {
				unlink($this->logTmpName());
			}
			umask(0000);
			$fHandle2 = fopen($path_txt, 'a') or die("can't open file");
			$logtpl = $this->getLogTemplate();
			$logtpl->setCurrentBlock('NewLog');
			$logtpl->setVariable("COURSETITLE", $this->slm->getTitle());
			$logtpl->setVariable("COURSEID", $this->packageId);
			$logtpl->setVariable("TIMESTAMP", date("d.m.Y H:i",time()));
			$logtpl->setVariable("SESSION", $this->get_actual_attempts());
			$logtpl->setVariable("error0", $lng->txt("error0"));
			$logtpl->setVariable("error101", $lng->txt("error101"));
			$logtpl->setVariable("error102", $lng->txt("error102"));
			$logtpl->setVariable("error103", $lng->txt("error103"));
			$logtpl->setVariable("error104", $lng->txt("error104"));
			$logtpl->setVariable("error111", $lng->txt("error111"));
			$logtpl->setVariable("error112", $lng->txt("error112"));
			$logtpl->setVariable("error113", $lng->txt("error113"));
			$logtpl->setVariable("error122", $lng->txt("error122"));
			$logtpl->setVariable("error123", $lng->txt("error123"));
			$logtpl->setVariable("error132", $lng->txt("error132"));
			$logtpl->setVariable("error133", $lng->txt("error133"));
			$logtpl->setVariable("error142", $lng->txt("error142"));
			$logtpl->setVariable("error143", $lng->txt("error143"));
			$logtpl->setVariable("error201", $lng->txt("error201"));
			$logtpl->setVariable("error301", $lng->txt("error301"));
			$logtpl->setVariable("error351", $lng->txt("error351"));
			$logtpl->setVariable("error391", $lng->txt("error391"));
			$logtpl->setVariable("error401", $lng->txt("error401"));
			$logtpl->setVariable("error402", $lng->txt("error402"));
			$logtpl->setVariable("error403", $lng->txt("error403"));
			$logtpl->setVariable("error404", $lng->txt("error404"));
			$logtpl->setVariable("error405", $lng->txt("error405"));
			$logtpl->setVariable("error406", $lng->txt("error406"));
			$logtpl->setVariable("error407", $lng->txt("error407"));
			$logtpl->setVariable("error408", $lng->txt("error408"));
			$logtpl->setVariable("SetValue", $lng->txt("SetValue"));
			$logtpl->setVariable("GetValue", $lng->txt("GetValue"));
			$logtpl->setVariable("Commit", $lng->txt("Commit"));
			$logtpl->setVariable("Initialize", $lng->txt("Initialize"));
			$logtpl->setVariable("Terminate", $lng->txt("Terminate"));
			$logtpl->setVariable("GetErrorString", $lng->txt("GetErrorString"));
			$logtpl->setVariable("GetLastError", $lng->txt("GetLastError"));
			$logtpl->setVariable("GetDiagnostic", $lng->txt("GetDiagnostic"));
			$logtpl->setVariable("cmi._version", $lng->txt("cmi._version"));
			$logtpl->setVariable("cmi.comments_from_learner._children", $lng->txt("cmi.comments_from_learner._children"));
			$logtpl->setVariable("cmi.comments_from_learner._count", $lng->txt("cmi.comments_from_learner._count"));
			$logtpl->setVariable("cmi.comments_from_learner.n.comment", $lng->txt("cmi.comments_from_learner.n.comment"));
			$logtpl->setVariable("cmi.comments_from_learner.n.location", $lng->txt("cmi.comments_from_learner.n.location"));
			$logtpl->setVariable("cmi.comments_from_learner.n.timestamp", $lng->txt("cmi.comments_from_learner.n.timestamp"));
			$logtpl->setVariable("cmi.comments_from_lms._children", $lng->txt("cmi.comments_from_lms._children"));
			$logtpl->setVariable("cmi.comments_from_lms._count", $lng->txt("cmi.comments_from_lms._count"));
			$logtpl->setVariable("cmi.comments_from_lms.n.comment", $lng->txt("cmi.comments_from_lms.n.comment"));
			$logtpl->setVariable("cmi.comments_from_lms.n.location", $lng->txt("cmi.comments_from_lms.n.location"));
			$logtpl->setVariable("cmi.comments_from_lms.n.timestamp", $lng->txt("cmi.comments_from_lms.n.timestamp"));
			$logtpl->setVariable("cmi.completion_status", $lng->txt("cmi.completion_status"));
			$logtpl->setVariable("cmi.completion_threshold", $lng->txt("cmi.completion_threshold"));
			$logtpl->setVariable("cmi.credit", $lng->txt("cmi.credit"));
			$logtpl->setVariable("cmi.entry", $lng->txt("cmi.entry"));
			$logtpl->setVariable("cmi.exit", $lng->txt("cmi.exit"));
			$logtpl->setVariable("cmi.interactions._children", $lng->txt("cmi.interactions._children"));
			$logtpl->setVariable("cmi.interactions._count", $lng->txt("cmi.interactions._count"));
			$logtpl->setVariable("cmi.interactions.n.id", $lng->txt("cmi.interactions.n.id"));
			$logtpl->setVariable("cmi.interactions.n.type", $lng->txt("cmi.interactions.n.type"));
			$logtpl->setVariable("cmi.interactions.n.objectives._count", $lng->txt("cmi.interactions.n.objectives._count"));
			$logtpl->setVariable("cmi.interactions.n.objectives.n.id", $lng->txt("cmi.interactions.n.objectives.n.id"));
			$logtpl->setVariable("cmi.interactions.n.timestamp", $lng->txt("cmi.interactions.n.timestamp"));
			$logtpl->setVariable("cmi.interactions.n.correct_responses._count", $lng->txt("cmi.interactions.n.correct_responses._count"));
			$logtpl->setVariable("cmi.interactions.n.correct_responses.n.pattern", $lng->txt("cmi.interactions.n.correct_responses.n.pattern"));
			$logtpl->setVariable("cmi.interactions.n.weighting", $lng->txt("cmi.interactions.n.weighting"));
			$logtpl->setVariable("cmi.interactions.n.learner_response", $lng->txt("cmi.interactions.n.learner_response"));
			$logtpl->setVariable("cmi.interactions.n.result", $lng->txt("cmi.interactions.n.result"));
			$logtpl->setVariable("cmi.interactions.n.latency", $lng->txt("cmi.interactions.n.latency"));
			$logtpl->setVariable("cmi.interactions.n.description", $lng->txt("cmi.interactions.n.description"));
			$logtpl->setVariable("cmi.launch_data", $lng->txt("cmi.launch_data"));
			$logtpl->setVariable("cmi.learner_id", $lng->txt("cmi.learner_id"));
			$logtpl->setVariable("cmi.learner_name", $lng->txt("cmi.learner_name"));
			$logtpl->setVariable("cmi.learner_preference._children", $lng->txt("cmi.learner_preference._children"));
			$logtpl->setVariable("cmi.learner_preference.audio_level", $lng->txt("cmi.learner_preference.audio_level"));
			$logtpl->setVariable("cmi.learner_preference.language", $lng->txt("cmi.learner_preference.language"));
			$logtpl->setVariable("cmi.learner_preference.delivery_speed", $lng->txt("cmi.learner_preference.delivery_speed"));
			$logtpl->setVariable("cmi.learner_preference.audio_captioning", $lng->txt("cmi.learner_preference.audio_captioning"));
			$logtpl->setVariable("cmi.location", $lng->txt("cmi.location"));
			$logtpl->setVariable("cmi.max_time_allowed", $lng->txt("cmi.max_time_allowed"));
			$logtpl->setVariable("cmi.mode", $lng->txt("cmi.mode"));
			$logtpl->setVariable("cmi.objectives._children", $lng->txt("cmi.objectives._children"));
			$logtpl->setVariable("cmi.objectives._count", $lng->txt("cmi.objectives._count"));
			$logtpl->setVariable("cmi.objectives.n.id", $lng->txt("cmi.objectives.n.id"));
			$logtpl->setVariable("cmi.objectives.n.score._children", $lng->txt("cmi.objectives.n.score._children"));
			$logtpl->setVariable("cmi.objectives.n.score.scaled", $lng->txt("cmi.objectives.n.score.scaled"));
			$logtpl->setVariable("cmi.objectives.n.score.raw", $lng->txt("cmi.objectives.n.score.raw"));
			$logtpl->setVariable("cmi.objectives.n.score.min", $lng->txt("cmi.objectives.n.score.min"));
			$logtpl->setVariable("cmi.objectives.n.score.max", $lng->txt("cmi.objectives.n.score.max"));
			$logtpl->setVariable("cmi.objectives.n.success_status", $lng->txt("cmi.objectives.n.success_status"));
			$logtpl->setVariable("cmi.objectives.n.completion_status", $lng->txt("cmi.objectives.n.completion_status"));
			$logtpl->setVariable("cmi.objectives.n.progress_measure", $lng->txt("cmi.objectives.n.progress_measure"));
			$logtpl->setVariable("cmi.objectives.n.description", $lng->txt("cmi.objectives.n.description"));
			$logtpl->setVariable("cmi.progress_measure", $lng->txt("cmi.progress_measure"));
			$logtpl->setVariable("cmi.scaled_passing_score", $lng->txt("cmi.scaled_passing_score"));
			$logtpl->setVariable("cmi.score._children", $lng->txt("cmi.score._children"));
			$logtpl->setVariable("cmi.score.scaled", $lng->txt("cmi.score.scaled"));
			$logtpl->setVariable("cmi.score.raw", $lng->txt("cmi.score.raw"));
			$logtpl->setVariable("cmi.score.min", $lng->txt("cmi.score.min"));
			$logtpl->setVariable("cmi.score.max", $lng->txt("cmi.score.max"));
			$logtpl->setVariable("cmi.session_time", $lng->txt("cmi.session_time"));
			$logtpl->setVariable("cmi.success_status", $lng->txt("cmi.success_status"));
			$logtpl->setVariable("cmi.suspend_data", $lng->txt("cmi.suspend_data"));
			$logtpl->setVariable("cmi.time_limit_action", $lng->txt("cmi.time_limit_action"));
			$logtpl->setVariable("cmi.total_time", $lng->txt("cmi.total_time"));
			$logtpl->setVariable("adl.nav.request", $lng->txt("adl.nav.request"));
			$logtpl->setVariable("adl.nav.request_valid.continue", $lng->txt("adl.nav.request_valid.continue"));
			$logtpl->setVariable("adl.nav.request_valid.previous", $lng->txt("adl.nav.request_valid.previous"));
			$logtpl->setVariable("adl.nav.request_valid.choice", $lng->txt("adl.nav.request_valid.choice"));
			$logtpl->setVariable("i_green", $lng->txt("i_green"));
			$logtpl->setVariable("i_red", $lng->txt("i_red"));
			$logtpl->setVariable("i_orange", $lng->txt("i_orange"));
			$logtpl->setVariable("i_fuchsia", $lng->txt("i_fuchsia"));
			$logtpl->setVariable("i_gray", $lng->txt("i_gray"));
			$logtpl->setVariable("error", $lng->txt("error"));
			$logtpl->setVariable("strange_error", $lng->txt("strange_error"));
			$logtpl->setVariable("strange_API-Call", $lng->txt("strange_API-Call"));
			$logtpl->setVariable("unknown", $lng->txt("unknown"));
			$logtpl->setVariable("undefined_color", $lng->txt("undefined_color"));
			$logtpl->setVariable("description_for", $lng->txt("description_for"));
			$logtpl->setVariable("hide", $lng->txt("hide"));
			$logtpl->setVariable("all_API-calls_shown", $lng->txt("all_API-calls_shown"));
			$logtpl->setVariable("show_only_important_API-calls", $lng->txt("show_only_important_API-calls"));
			$logtpl->setVariable("only_important_API-Calls_shown", $lng->txt("only_important_API-Calls_shown"));
			$logtpl->setVariable("show_all_API-calls", $lng->txt("show_all_API-calls"));
			$logtpl->setVariable("log_for", $lng->txt("log_for"));
			$logtpl->setVariable("started", $lng->txt("started"));
			$logtpl->setVariable("nr_session", $lng->txt("nr_session"));
			$logtpl->setVariable("id_learning_module", $lng->txt("id_learning_module"));
			if($this->slm->getCheck_values()==false) $logtpl->setVariable("CHECK_VALUES", $lng->txt("sent_values_not_checked"));
			$logtpl->parseCurrentBlock();
			fwrite($fHandle2,$logtpl->get());
			fclose($fHandle2);
		} 
		return $filename;		
	}

	function getDataDirectory2()
	{
		$webdir=str_replace("/ilias.php","",$_SERVER["SCRIPT_NAME"]);	
		//load ressources always with absolute URL..relative URLS fail on innersco navigation on certain browsers
		$lm_dir=$webdir."/".ILIAS_WEB_DIR."/".$this->ilias->client_id ."/lm_data"."/lm_".$this->packageId;
		return $lm_dir;
	}

	private function logDirectory()
	{
//		$logDir=ilUtil::getDataDir()."/SCORMlogs"."/lm_".$this->packageId;
//		if (!file_exists($logDir)) ilUtil::makeDirParents($logDir);
		$logDir=$this->slm->getDataDirectory()."/logs";
		if (!file_exists($logDir)) {
			ilUtil::makeDir($logDir);
		}		
		return $logDir;
	}

	public function openLog(){
		$filename = $_GET['logFile'];
		//Header
		header('Content-Type: text/html; charset=UTF-8');
		echo file_get_contents($this->logDirectory()."/".$filename);
		exit;	
	}
	
	public function downloadLog(){
		$filename = $_GET['logFile'];
		//Header
		header("Expires: 0");
		header("Cache-Control: private");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma: cache");
		header("Content-Description: File Transfer");
		header("Content-Type: application/octet-stream");
		header("Content-disposition: attachment; filename=$filename");
		echo file_get_contents($this->logDirectory()."/".$filename);
		exit;	
	}

	private function getLogFileList($s_delete,$s_download,$s_open)
	{
		$data = array();
		foreach (new DirectoryIterator($this->logDirectory()) as $fileInfo) {
			if ($fileInfo->isDot()) {
       			continue;
   			}
			$item['filename'] = $fileInfo->getFilename();
			$parts = pathinfo($item['filename']);
			$fnameparts = preg_split('/_/', $parts['filename'], -1, PREG_SPLIT_NO_EMPTY);
			$deleteUrl = '&nbsp;<a href=#'." onclick=\"javascript:deleteFile('".$item['filename']."');\">".$s_delete."</a>";
			//no delete for most recent file
			if ($this->get_actual_attempts()==$fnameparts[1]) {$deleteUrl="";}
			
			$urlDownload = 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=downloadLog&ref_id='.$_GET["ref_id"].'&logFile='.$fileInfo->getFilename();
			$urlOpen = 'ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=openLog&ref_id='.$_GET["ref_id"].'&logFile='.$fileInfo->getFilename();
			$item['date'] = date('Y/m/d H:i:s', $fileInfo->getCTime());
			if ($parts['extension'] == "html") {
				$item['action'] =$deleteUrl."&nbsp;<a href=".$urlDownload.">".$s_download."</a>&nbsp;<a target=_new href=".$urlOpen.">".$s_open."</a>";
			} else {
				$item['action'] =$deleteUrl."&nbsp;<a href=".$urlDownload.">".$s_download."</a>";
			}	
			if ($parts['extension'] == "html" || $parts['extension'] == "csv") {
				array_push($data,$item);
			}
		}
		usort($data,"datecmp");
		return $data;
	}
	
	public function liveLogContent()
	{
		header('Content-Type: text/html; charset=UTF-8');
		print file_get_contents($this->logFileName().".html");
	}
	
	public function debugGUI()
	{
		global $lng;
		$lng->loadLanguageModule("scormdebug");

/*		if ($_POST['password'] == $this->slm->getDebugPw()) {
			$_SESSION["debug_pw"] = $this->slm->getDebugPw();
		}
		if ($_SESSION["debug_pw"]!=$this->slm->getDebugPw()) {
			$this->tpl = new ilTemplate("tpl.scorm2004.debug_pw.html", false, false, "./Modules/Scorm2004");
			$this->tpl->setVariable('SUBMIT', $lng->txt("debugwindow_submit"));
			$this->tpl->setVariable('CANCEL', $lng->txt("debugwindow_cancel"));
			$this->tpl->setVariable('PASSWORD_ENTER', $lng->txt("debugwindow_password_enter"));
			$this->tpl->setVariable('DEBUG_URL','ilias.php?baseClass=ilSAHSPresentationGUI' .'&cmd=debugGUI&ref_id='.$_GET["ref_id"]);
		} else {*/
			$this->tpl = new ilTemplate("tpl.scorm2004.debug.html", false, false, "./Modules/Scorm2004");
			$this->tpl->setVariable('CONSOLE', $lng->txt("debugwindow_console"));
			$this->tpl->setVariable('LOGS', $lng->txt("debugwindow_logs"));
			$this->tpl->setVariable('COMMENT', $lng->txt("debugwindow_comment"));
			$this->tpl->setVariable('COMMENT_ENTER', $lng->txt("debugwindow_comment_enter"));
			$this->tpl->setVariable('START_RECORDING', $lng->txt("debugwindow_start_recording"));
			$this->tpl->setVariable('STOP_RECORDING', $lng->txt("debugwindow_stop_recording"));
			$this->tpl->setVariable('DELETE_LOGFILE', $lng->txt("debugwindow_delete_logfile"));
			$this->tpl->setVariable('SUBMISSION_FAILED', $lng->txt("debugwindow_submission_failed"));
			$this->tpl->setVariable('SUBMIT', $lng->txt("debugwindow_submit"));
			$this->tpl->setVariable('CANCEL', $lng->txt("debugwindow_cancel"));
			$this->tpl->setVariable('FILENAME', $lng->txt("debugwindow_filename"));
			$this->tpl->setVariable('DATE', $lng->txt("debugwindow_date"));
			$this->tpl->setVariable('ACTION', $lng->txt("debugwindow_action"));
			$this->tpl->setVariable('RECORD_IMG', ilUtil::getImagePath("record.png","./Modules/Scorm2004"));	
			$this->tpl->setVariable('STOP_IMG', ilUtil::getImagePath("stop.png","./Modules/Scorm2004"));	
			$this->tpl->setVariable('COMMENT_IMG', ilUtil::getImagePath("comment.png","./Modules/Scorm2004"));	
			$logfile = $this->logFileName().".html";
			$this->tpl->setVariable('LOGFILE',$this->logFileName().".html");		
			$this->tpl->setVariable('FILES_DATA', json_encode($this->getLogFileList($lng->txt("debugwindow_delete"), $lng->txt("debugwindow_download"), $lng->txt("debugwindow_open"))));
			
			// path to latest yui distribution
			include_once "Services/YUI/classes/class.ilYuiUtil.php";
			$this->tpl->setVariable('PATH_YUI', ilYuiUtil::getLocalPath());			
		//}
		echo $this->tpl->get("DEFAULT", true);	
	}
	
	private function getLogTemplate()
	{
		return new ilTemplate("tpl.scorm2004.debugtxt.txt", true, true, "Modules/Scorm2004");
	}
	
	private function getDebugValues($test_sco = false)
	{
		global $ilDB,$ilLog;
		$ini_array = null;
		$dvalues = array();
/*		
		$res = $ilDB->queryF('
					  SELECT debug_fields
					  FROM sahs_lm
					  WHERE id = %s',
					  array('integer'),
					  array($this->packageId)
				);
		$row = $ilDB->fetchAssoc($res);
		$debug_fields = $row['debug_fields'];
		if ($debug_fields == null) {*/
			$debug_fields = parse_ini_file("./Modules/Scorm2004/scripts/rtemain/debug_default.ini",true);
//		} 
		if ($test_sco) {
			$ini_array = $debug_fields['test_sco'];	
		} else {
			$ini_array = $debug_fields['normal_sco'];	
		}
		foreach ($ini_array as $key => $value) {
			if ($value == 1) {
				array_push($dvalues,$key);
			}
		}
		return $dvalues;
	}
	
	public function postLogEntry()
	{
		global $ilLog,$lng;
		$lng->loadLanguageModule("scormdebug");
		
		$logdata = json_decode(file_get_contents('php://input'));
		$filename = $this->logFileName();
		$tmp_name = $this->logTmpName();
				
		$fh_txt = fopen($filename.".html", 'a') or die("can't open txt file");
		$fh_csv = fopen($filename.".csv", 'a') or die("can't open csv file");
		$fh_tmp = fopen($tmp_name, 'r') or die("can't open tmp file");
		
		//init tmp file
		if (filesize($tmp_name)>0) {
			$tmp_content = unserialize(fread($fh_tmp,filesize($tmp_name)));
		} else {
			$tmp_content = null;
		}
		
		fclose($fh_tmp);
		 
		//reopen for writing
		$fh_tmp2 = fopen($tmp_name, 'w') or die("can't open tmp file");

		
		//write tmp
		$tmp_content[$logdata->scoid][$logdata->key]['value'] = $logdata->value; 
		$tmp_content[$logdata->scoid][$logdata->key]['status'] = $logdata->result; 
		$tmp_content[$logdata->scoid][$logdata->key]['action'] = $logdata->action; 

		fwrite($fh_tmp2,serialize($tmp_content));
		fclose($fh_tmp2);

		$timestamp = date("d.m.Y H:i",time());


		$errorcode = $logdata->errorcode;
		$fixedFailure = false;
		$toleratedFailure = false;
		$extraErrorDescription = "";
		if ($errorcode == 200000) {
			$errorcode = 0;
			$toleratedFailure = true;
			$extraErrorDescription = "tolerated failure";
		}
		if ($errorcode>99999) {
			$errorcode-=100000;
			$fixedFailure = true;
			$extraErrorDescription = " failure corrected by ILIAS";
		}
		if (strpos($logdata->action,"ANALYZE")===false)
		{
			$errorDescriptions = array("0" => "",
				"101" => "General Exeption",
				"102" => "General Initialization Failure",
				"103" => "Already Initialized",
				"104" => "Content Instance Terminated",
				"111" => "General Termination Failure",
				"112" => "Termination Before Initialization",
				"113" => "Termination After Termination",
				"122" => "Retrieve Data Before Initialization",
				"123" => "Retrieve Data After Termination",
				"132" => "Store Data Before Initialization",
				"133" => "Store Data After Termination",
				"142" => "Commit Before Initialization",
				"143" => "Commit After Termination",
				"201" => "General Argument Error",
				"301" => "General Get Failure",
				"351" => "General Set Failure",
				"391" => "General Commit Failure",
				"401" => "Undefined Data Model Element",
				"402" => "Unimplemented Data Model Element",
				"403" => "Data Model Element Value Not Initialized",
				"404" => "Data Model Element Is Read Only",
				"405" => "Data Model Element Is Write Only",
				"406" => "Data Model Element Type Mismatch",
				"407" => "Data Model Element Value Out Of Range",
				"408" => "Data Model Dependency Not Established");
			$csv_string = $this->packageId.';"'
				.$logdata->scoid.'";"'
				.$logdata->scotitle.'";'
				.date("d.m.Y H:i",time()).';"'
				.$logdata->action.'";"'
				.$logdata->key.'";"'
				.str_replace("\"","\"\"",$logdata->value).'";"'
				.str_replace("\"","\"\"",$logdata->result).'";'
				.$errorcode.';'
				.$logdata->timespan.';"'
				.$errorDescriptions[$errorcode].$extraErrorDescription.'"'."\n";
			fwrite($fh_csv,$csv_string);
		}

		$sqlwrite = false;
		if($logdata->action == "Commit" || $logdata->action == "Terminate")
		{
			$sqlwrite = true;
			$sql_data = $this->getNodeData($logdata->scoid,$fh_csv);
			foreach ($sql_data as $key => $value) {
				$sql_string =  $this->packageId.';"'
					.$logdata->scoid.'";"'
					.$logdata->scotitle.'";'
					.$timestamp.';"SQL";"'
					.$key.'";"'
					.str_replace("\"","\"\"",$value).'";;;;'."\n";
				fwrite($fh_csv,$sql_string);
			}
		}
		
		//delete files
		if ($logdata->action == "DELETE")
		{
			$filename = $logdata->value;
			$path = $this->logDirectory()."/".$filename;
			unlink($path);
			return;
		}
		
		//write TXT
		$logtpl = $this->getLogTemplate();
		$color = "red";
		$importantkey=1;
		$ArGetValues = array('comments_from_lms','completion_threshold','credit','entry','launch_data','learner_id','learner_name','max_time_allowed','mode','scaled_passing_score','time_limit_action','total_time');

		switch ($logdata->action) {
			case 'SetValue':
				if ($logdata->result == "true" && $errorcode == 0) $color = "green";
				if ($color=="green" && $logdata->key == "cmi.exit" && $logdata->value!="suspend") $color = "orange";
				if ($fixedFailure == false && $errorcode!=406) $logdata->value = '"'.$logdata->value.'"';
				if ($toleratedFailure == true) $color = "fuchsia";
				if ($fixedFailure == true) $color = "gray";
				break;
			case 'GetValue':
				if ($errorcode == 0) $color = "green";
				break;
			case 'Initialize':
				if ($errorcode == 0)
				{
					$color = "green";
					$logtpl->setCurrentBlock("InitializeStart");
					$logtpl->setVariable("SCO-title", $lng->txt("SCO-title"));
					$logtpl->setVariable("SCO_TITLE", $logdata->scotitle);
					$logtpl->setVariable("SCO-name", $lng->txt("SCO-name"));
					$logtpl->setVariable("SCO_NAME", $logdata->scoid);
					$logtpl->setVariable("started", $lng->txt("started"));
					$logtpl->setVariable("TIMESTAMP",  $timestamp);
					$logtpl->setVariable("milliseconds", $lng->txt("milliseconds"));
					$logtpl->setVariable("API-call", $lng->txt("API-call"));
					$logtpl->setVariable("return_value", $lng->txt("return_value"));
					$logtpl->setVariable("error", $lng->txt("error"));
					$logtpl->parseCurrentBlock();
				}
				break;
			case 'Commit':
				if ($errorcode == 0) $color = "green";
				if ($fixedFailure == true) $color = "gray";
				break;
			case 'Terminate':
				if ($errorcode == 0) $color = "green";
				break;
			case 'GetErrorString':
				$importantkey=0;
				if ($errorcode == 0) $color = "green";
				break;
			case 'GetLastError':
				$logtpl->setCurrentBlock("GetLastError");
				$logtpl->setVariable("TIMESPAN",  $logdata->timespan);
				$logtpl->setVariable("RESULT",  $logdata->result);
				$logtpl->parseCurrentBlock();
				break;
			case 'GetDiagnostic':
				$logtpl->setCurrentBlock("GetDiagnostic");
				$logtpl->setVariable("TIMESPAN",  $logdata->timespan);
				$logtpl->setVariable("KEY", $logdata->key);
				$logtpl->setVariable("RESULT",  $logdata->result);
				$logtpl->parseCurrentBlock();
				break;
			case 'INFO':
				$logtpl->setCurrentBlock("INFO");
				$logtpl->setVariable("hint", $lng->txt("hint"));
				$logtpl->setVariable("KEY", $lng->txt($logdata->key));
				$logtpl->setVariable("VALUE", $logdata->value);
				$logtpl->parseCurrentBlock();
				break;
			case 'COMMENT':
				$logtpl->setCurrentBlock("COMMENT");
				$logtpl->setVariable("comment", $lng->txt("comment"));
				$logtpl->setVariable("generated", $lng->txt("generated"));
				$logtpl->setVariable("TIMESTAMP",  $timestamp);
				$logtpl->setVariable("VALUE",  $logdata->value);
				$logtpl->parseCurrentBlock();
				break;
			case 'ANALYZE':
				$logtpl->setCurrentBlock("ANALYZE");
				if (count($logdata->value) == 0) {
					$color = "green";
					$logtpl->setVariable("ANALYZE_SUMMARY", $lng->txt("no_missing_API-calls"));
					$logtpl->setVariable("VALUE", "");
				} else {
					$tmpvalue = "SetValue(\"".implode("\", ... ),<br/>SetValue(\"",$logdata->value)."\", ... )";
					for ($i=0; $i <count($ArGetValues); $i++){
						$tmpvalue = str_replace("SetValue(\"cmi.".$ArGetValues[$i]."\", ... )","GetValue(\"cmi.".$ArGetValues[$i]."\")",$tmpvalue);
					}
					$logtpl->setVariable("ANALYZE_SUMMARY", $lng->txt("missing_API-calls"));
					$logtpl->setVariable("VALUE", $tmpvalue);
				}
				$logtpl->setVariable("summary_for_SCO_without_test", $lng->txt("summary_for_SCO_without_test"));
				$logtpl->setVariable("generated", $lng->txt("generated"));
				$logtpl->setVariable("TIMESTAMP",  $timestamp);
				$logtpl->setVariable("COLOR", $color);
				$logtpl->parseCurrentBlock();
				break;	
			case 'ANALYZETEST':
				$logtpl->setCurrentBlock("ANALYZETEST");
				if (count($logdata->value) == 0) {
					$color = "green";
					$logtpl->setVariable("ANALYZE_SUMMARY", $lng->txt("no_missing_API-calls"));
					$logtpl->setVariable("VALUE", "");
				} else {
					$tmpvalue = "SetValue(\"".implode("\", ... ),<br/>SetValue(\"",$logdata->value)."\", ... )";
					for ($i=0; $i <count($ArGetValues); $i++){
						$tmpvalue = str_replace("SetValue(\"cmi.".$ArGetValues[$i]."\", ... )","GetValue(\"cmi.".$ArGetValues[$i]."\")",$tmpvalue);
					}
					$logtpl->setVariable("ANALYZE_SUMMARY", $lng->txt("missing_API-calls"));
					$logtpl->setVariable("VALUE", $tmpvalue);
				}
				$logtpl->setVariable("summary_for_SCO_with_test", $lng->txt("summary_for_SCO_with_test"));
				$logtpl->setVariable("generated", $lng->txt("generated"));
				$logtpl->setVariable("TIMESTAMP",  $timestamp);
				$logtpl->setVariable("COLOR", $color);
				$logtpl->parseCurrentBlock();
				break;		
			case 'SUMMARY':
				$logtpl->setCurrentBlock("SUMMARY");
				$logtpl->setVariable("summary_csv", $lng->txt("summary_csv"));
				$logtpl->setVariable("TIMESTAMP",  $timestamp);
				$logtpl->setVariable("summary_download", $lng->txt("summary_download"));
				$logtpl->parseCurrentBlock();
				break;			
			default:
				$importantkey=0;
				$color = "orange";
				break;
		}
		if ($logdata->action == 'SetValue' || $logdata->action == 'GetValue')
		{
			$logtpl->setCurrentBlock($logdata->action);
			$logtpl->setVariable("ACTION",  $logdata->action);
			$logtpl->setVariable("TIMESPAN",  $logdata->timespan);
			$logtpl->setVariable("KEY", $logdata->key);
			$logtpl->setVariable("VALUE",  $logdata->value);
			$logtpl->setVariable("RESULT",  $logdata->result);
			$logtpl->setVariable("ERRORCODE", $errorcode);
			$debugfields=$this->getDebugValues(true);
			$importantkey=0;
			for ($i=0; $i <count($debugfields) ; $i++){
				if ($logdata->key == $debugfields[$i]) $importantkey=1;
			}
			$logtpl->setVariable("IMPORTANTKEY", "".$importantkey);
			$logtpl->setVariable("COLOR", $color);
			$logtpl->parseCurrentBlock();
		}
		else if ($logdata->action != 'INFO' && $logdata->action != 'ANALYZE' && $logdata->action != 'ANALYZETEST' && $logdata->action != 'SUMMARY' && $logdata->action != 'COMMENT' && $logdata->action != 'GetDiagnostic' && $logdata->action != 'GetLastError')
		{
			$logtpl->setCurrentBlock("defaultCall");
			$logtpl->setVariable("ACTION",  $logdata->action);
			$logtpl->setVariable("TIMESPAN",  $logdata->timespan);
			$logtpl->setVariable("KEY", $logdata->key);
			$logtpl->setVariable("VALUE",  $logdata->value);
			$logtpl->setVariable("RESULT",  $logdata->result);
			$logtpl->setVariable("ERRORCODE", $errorcode);
			$logtpl->setVariable("IMPORTANTKEY", "".$importantkey);
			$logtpl->setVariable("COLOR", $color);
			$logtpl->parseCurrentBlock();
		}
		
		/*
		if ($sqlwrite == true) {
			$ilLog->write("SQL WRITE");
			$logtpl->setCurrentBlock("SqlLog");			
			$logtpl->setVariable("SQL_STRING", $sql_text);
			$logtpl->parseCurrentBlock();
		}
		*/
		
			//create summary
		if ($logdata->action == "SUMMARY") {
			$this->createSummary($tmp_content);
		}
		
		fwrite($fh_txt,$logtpl->get());
		fclose($fh_txt);
		fclose($fh_csv);
	}

	private function getStructureFlat($data)
	{
		for ($i=0; $i <count($data) ; $i++) { 
			$element = array();
			$element['title'] = $data[$i]['title'];
			$element['id'] = $data[$i]['id'];
			if ($data[$i]['sco'] == 1) {
				$element['sco'] = "sco";
			} else {
				$element['sco'] = "assset";
			}	
			if ( $data[$i]['href'] !=null ) {
				array_push($this->flat_structure,$element);
			}	
			if ($data[$i]['item']!=null) {
				$this->getStructureFlat($data[$i]['item']);
			}
		}
	}	

	private function createSummary($api_data)
	{
		global $ilDB;

		$csv_data = null;
		//csv columns
		$columns_fixed = array('id','title','type','attempted');
	
		$ini_data = parse_ini_file("./Modules/Scorm2004/scripts/rtemain/debug_default.ini",true);
		$ini_array = $ini_data['summary'];	
		$colums_variable = array();
		$api_keys = array();

		foreach ($ini_array as $key => $value) {
			if ($value == 1) {
				array_push($colums_variable,$key);
				array_push($api_keys,$key);
				array_push($colums_variable,"Status");
			}
		}
	
		$header_array = array_merge($columns_fixed, $colums_variable);

		$csv_header = implode(";",$header_array);
	
		//get strcuture
		$res = $ilDB->queryF(
			'SELECT jsdata FROM cp_package WHERE obj_id = %s',
			array('integer'),
			array($this->packageId)
		);
	
		$packageData = $ilDB->fetchAssoc($res);		
				
		$structure = json_decode($packageData['jsdata'],true);
		
	
		$this->flat_structure = array();  //used for recursion
		$this->getStructureFlat($structure['item']['item']);

		foreach ($this->flat_structure as $tree_element) {
		
			$csv_data = $csv_data.$tree_element['id'].";".$tree_element['title'].";".$tree_element['sco'].";";
			if ($api_data[$tree_element['id']] != null) {
				$csv_data = $csv_data."X".";";
			} else {
				$csv_data = $csv_data.";";
			}
		
			//write api data
			$id = $tree_element['id'];
			foreach ($api_keys as $api_element) {
				if ($api_data[$id]!=null) {
					if ($api_data[$id][$api_element]!=null) {
						$csv_data = $csv_data.$api_data[$id][$api_element]['value'].";".$api_data[$id][$api_element]['status'].";";
					} else {
						$csv_data = $csv_data.";;";
					}
				}
			}
			$csv_data = $csv_data."\n";		
		}
	
		$fh = fopen($this->summaryFileName(),"w");
		fwrite($fh,$csv_header."\n".$csv_data);
		fclose($fh);
		unlink($this->logTmpName());
	}
	/**
	*	functions for last_visited_sco
	*/

	function get_last_visited($a_obj_id, $a_user_id)
	{
		global $ilDB;
		$val_set = $ilDB->queryF('SELECT last_visited FROM sahs_user WHERE obj_id = %s AND user_id = %s',
		array('integer','integer'),
		array($a_obj_id,$a_user_id));
		
		$val_rec = $ilDB->fetchAssoc($val_set);
		return $val_rec["last_visited"];
	}
}

function datecmp($a, $b){
    if (strtotime($a['date']) == strtotime($b['date'])) {
       	return 0;
    }
    return (strtotime($a['date']) < strtotime($b['date'])) ? 1 :-1;
}

?>
