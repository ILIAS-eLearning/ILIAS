<?php

define('IL_OP_DB_TYPE', 'sqlite');
define('IL_OP_DB_DSN', 'sqlite2:./Modules/Scorm2004/data/sqlite2.db');
define('IL_OP_USER_NAME', '');
define('IL_OP_USER_PASSWORD', '');

require_once "./Modules/Scorm2004/classes/ilSCORM13Player.php";
require_once "./Modules/Scorm2004/classes/phpext.php";

include_once ("./Modules/Scorm2004/classes/ilSCORM13DB.php");


class ilSCORM13PlayerBridge extends ilSCORM13Player{

	var $ilias;
	var $slm;
	var $tpl;
	var $lng;
	
	function ilSCORM13PlayerBridge($packageId)
	{
		global $ilias, $tpl, $lng, $ilCtrl;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ctrl =& $ilCtrl;
		ilSCORM13DB::init(IL_OP_DB_DSN, IL_OP_DB_TYPE);
		$this->packageId=ilObject::_lookupObjectId($_GET['ref_id']);
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
		
		$packageData = ilSCORM13DB::getRecord(
		'cp_package',
		'obj_id',
		$this->packageId
		);
		$basedir = json_decode($packageData['jsdata']);
		$config = array
		(
		'cp_url' => './Modules/Scorm2004/player.php?' . 'call=cp&packageId=' . $this->packageId.'&ref_id='.$_GET["ref_id"],
		'cmi_url' => './Modules/Scorm2004/player.php?' .'call=cmi&packageId=' . $this->packageId.'&ref_id='.$_GET["ref_id"],

		'learner_id' => (string) $GLOBALS["USER"]["id_usr"],
		'learner_name' => $GLOBALS["USER"]["login"],
		'mode' => 'normal',
		'credit' => 'credit',
		'package_url' =>  $basedir->base,
		);

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