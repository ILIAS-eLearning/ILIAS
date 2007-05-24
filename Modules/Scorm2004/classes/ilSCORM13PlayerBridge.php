<?php


//include dependent on calling path

if (file_exists("./Modules/Scorm2004/classes/ilSCORM13Player.php")) {
	include_once "./Modules/Scorm2004/classes/ilSCORM13Player.php";
} else {	
	include_once "classes/ilSCORM13Player.php";
}	

//TODO remove when database integration is finished
define('IL_OP_USER_NAME', '');
define('IL_OP_USER_PASSWORD', '');

class ilSCORM13PlayerBridge extends ilSCORM13Player{

	var $ilias;
	var $slm;
	var $tpl;
	var $lng;
	
	function __construct($basePath)
	{
		
		global $ilias, $tpl, $lng, $ilCtrl;
		
		
		require_once $basePath."classes/phpext.php";
		include_once ($basePath."classes/ilSCORM13DB.php");

		
		parent::__construct();
		
				
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ctrl =& $ilCtrl;
		

		if ($basePath) {
			$this->packageId=ilObject::_lookupObjectId($_GET['ref_id']);
		} else {
			$this->packageId=$_GET["packageId"];
		}
		
		//TODO remove when DB integration is done
		
		//ilSCORM13DB::init("sqlite2:".$basePath."data/sqlite2.db", "sqlite");
		ilSCORM13DB::init("sqlite2:/Users/hendrikh/Development/eclipse/ilias3_scorm2004/ilias3_scorm2004/Modules/Scorm2004/data/sqlite2.db", "sqlite");
	}

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		
		global $ilAccess, $ilLog;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("getPlayer");

		if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"), $ilias->error_obj->WARNING);
		}

		switch($next_class)
		{
			default:
				$this->$cmd();
		}
	}
	
	
	
	
	public function getPlayer()
	{
		global $ilUser;
		$packageData = ilSCORM13DB::getRecord(
			'cp_package',
			'obj_id',
			$this->packageId
		);
		
		
		//TODO workaround...should be moved into another table

		ilSCORM13DB::setRecord('usr_data', array(
		'usr_id' => $ilUser->getID(),
		'firstname' => $ilUser->getFirstname(),
		'lastname'=>$ilUser->getLastname(),
		'ilinc_id'=>0,
		'email'=>$ilUser->getLastname(),
		'passwd'=>'test12',
		'login'=>'',
		'title'=>''
		));
		
		ilSCORM13DB::setRecord('sahs_lm', array(
		'id' => $this->packageId,
		'credit' => "credit",
		'default_lesson_mode'=>"normal",
		'auto_review'=>"review"
		));
		
		
		$basedir = json_decode($packageData['jsdata']);
		$config = array
		(
			'cp_url' => './Modules/Scorm2004/player_ilias.php?' . 'call=cp&packageId=' . $this->packageId.'&ref_id='.$_GET["ref_id"],
			'cmi_url' => './Modules/Scorm2004/player_ilias.php?' .'call=cmi&packageId=' . $this->packageId.'&ref_id='.$_GET["ref_id"].'&learnerId='.$ilUser->getID(),
			'learner_id' => (string) $ilUser->getID(),
			'learner_name' => $ilUser->getFirstname()." ".$ilUser->getLastname(),
			'mode' => 'normal',
			'credit' => 'credit',
			'package_url' =>  $basedir->base,
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

		//header('Content-Type: text/html; charset=UTF-8');

		$this->tpl = new ilTemplate("tpl.scorm2004.player.html", false, false, "Modules/Scorm2004");
		$this->tpl->setVariable('DEBUG', (int) $_REQUEST['debug']);
		$this->tpl->setVariable('JSON_LANGSTRINGS', json_encode($langstrings));
		$this->tpl->setVariable($langstrings);
		$this->tpl->setVariable('DOC_TITLE', 'ILIAS SCORM 2004 Player');
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable('CSS_NEEDED', '');
		$this->tpl->setVariable('JS_NEEDED', '');
		$this->tpl->setVariable('JS_DATA', json_encode($config));
		list($tsfrac, $tsint) = explode(' ', microtime());
		$this->tpl->setVariable('TIMESTAMP', sprintf('%d%03d', $tsint, 1000*(float)$tsfrac));
		$this->tpl->setVariable('BASE_DIR', './Modules/Scorm2004/');
		$this->tpl->setVariable('ILIAS', '1');	
		$this->tpl->show("DEFAULT", false);
	}
	
	function cp()
	{
		parent::getCPData();
	}

	function cmi()
	{
		parent::fetchCMIData();
	}

}

?>