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

/**
 * @classDescription class for ILIAS ViewLTI
 * 
 * @author Stefan Schneider <schneider@hrz.uni-marburg.de
 * @version $id$
 * @ingroup ServicesLTI
 * @ilCtrl_IsCalledBy ilLTIViewGUI: ilLTIRouterGUI
 * 
 */
class ilLTIViewGUI
{
	/**
	 * private variables
	 */ 
	private $dic = null;
	private $user = null;
	private $log = null;
	private $link_dir = "";

	/**
	 * public variables
	 */ 
	public $lng = null;

	public function __construct() {
		global $DIC;
		$this->dic = $DIC;
		$this->user = $this->dic->user();
		$this->log = $this->dic->logger()->lti();
		$this->lng = $this->dic->language();
		$this->lng->loadLanguageModule('lti');
	}

	/**
	 * Init LTI mode for lit authenticated users
	 */
	public function init()
	{
		if ($this->getSessionValue['lti_link_dir'] === '') {
			$_SESSION['lti_link_dir'] = (defined("ILIAS_MODULE"))
					? "../"
					: "";
			$this->link_dir = $_SESSION['lti_link_dir'];
		}
		if ($this->isLTIUser())
		{
			$context = $this->dic->globalScreen()->tool()->context();
			$context->claim()->lti();
			$this->activate();
			$this->log->info("LTI ScreenContext claimed");
		}
		else
		{
			if ($this->isActive()) {
				$this->deactivate();
			}
		}
	}

	/**
	 * for compatiblity with ilLTIRouterGUI
	 */ 
	public static function getInstance() {
		global $DIC;
		return $DIC["lti"];
	}

	/**
	 * get LTI Mode from Users->getAuthMode
	 * @return boolean 
	 */ 
	private function isLTIUser() {
		if(!$this->user instanceof ilObjUser)
		{
			return false;
		}
		return (strpos($this->user->getAuthMode(),'lti_') === 0);
		/* for testing standalone faking a LTI session by special user with login name '*_lti' */
		//$_SESSION['lti_launch_css_url'] = "https://ilias.example.com/lti.css";
		/*
		if ($this->getSessionValue('lti_context_id') === '') {
			$target_arr = explode('_',(string) $_GET['target']);
			if(isset($target_arr[1]) and (int) $target_arr[1]) {
				$_SESSION['lti_context_id'] = $target_arr[1];
			}
		}
		return (strpos($this->user->getLogin(),'lti_') === 0);
		*/ 
	}

	/**
	 * for ctrl commands
	 */ 
	public function executeCommand() {
		global $ilCtrl;
		$cmd = $ilCtrl->getCmd();
		switch ($cmd) {
			case 'exit' :
				$this->exitLti();
			break;
		}
	}

	/** 
	 * activate LTI GUI
	 * @return void
	 * */
	public function activate() 
	{
		if ($this->isActive()) {
			return;
		}
		$_SESSION['il_lti_mode'] = "1";
		$this->initGUI();
	}

	/** 
	 * deactivate LTI GUI
	 * @return void
	 * */
	public function deactivate() 
	{
		unset($_SESSION['il_lti_mode']);
		unset($_SESSION['lti_home_id']);
		unset($_SESSION['lti_home_obj_id']);
		unset($_SESSION['lti_home_url']);
		unset($_SESSION['lti_home_title']);
		$this->log->info("lti view deactivated");
	}

	/** 
	 * LTI is active
	 * @return boolean
	 * */
	public function isActive() 
	{
		return (isset($_SESSION['il_lti_mode']));
	}

	public function initGUI() 
	{
		$this->log->info("initGUI");
		$baseclass = strtolower($_GET['baseClass']);
		$cmdclass = strtolower($_GET['cmdClass']);
		if ($this->getSessionValue('lti_home_id') === '') {
			 $_SESSION['lti_home_id'] = $_SESSION['lti_context_id'];
		}
		if ($this->getSessionValue('lti_home_obj_id') === '') {
			
			$_SESSION['lti_home_obj_id'] = ilObject::_lookupObjectId($_SESSION['lti_home_id']);
		}
		if ($this->getSessionValue('lti_home_type') === '') {
			$_SESSION['lti_home_type'] = ilObject::_lookupType($_SESSION['lti_home_id'],true);
		}
		if ($this->getSessionValue('lti_home_url') === '') {
			$_SESSION['lti_home_url'] = $this->getHomeLink();
		}
		if ($this->getSessionValue('lti_home_title') === '') {
			$_SESSION['lti_home_title'] = $this->getHomeTitle();
		}
		switch ($baseclass) 
		{
			case 'illtiroutergui' :
				return;
				break;
		}
	}

	/**
	 * helper function for home link creation
	 */ 
	public function getHomeLink() 
	{
		return $_SESSION['lti_link_dir']."goto.php?target=".$_SESSION['lti_home_type']."_".$_SESSION['lti_home_id'];
	}

	public function getHomeTitle() 
	{
		return ilObject::_lookupTitle($_SESSION['lti_home_obj_id']);
	}
	
	public function getTitleBar($withExitText) {
		$tplMetaBar = new ilTemplate("tpl.lti_metabar.html", true, true, "Services/LTI");
		$tplMetaBar->setVariable('LTI_METABAR_TITLE',$this->getTitle());
		if ($withExitText) {
			$tplMetaBar->setVariable('LTI_METABAR_EXIT',$this->lng->txt('lti_exit'));
		}
		else {
			$tplMetaBar->setVariable('LTI_METABAR_EXIT','');
		}
		$html = $tplMetaBar->get();
		return $html;
	}

	public function getTitle() {
		return $this->getShortTitle() . ": " . $this->getViewTitle();
	}

	public function getShortTitle() {
		return $this->lng->txt('lti_mode'); 
	}

	public function getViewTitle() {
		return $this->getHomeTitle(); 
	}

	/**
	 * exit LTI session and if defined redirecting to returnUrl
	 * ToDo: Standard Template with delos ...
	 */
	public function exitLti() 
	{
		$this->dic->logger()->lti()->info("exitLTI");
		if ($this->getSessionValue('lti_launch_presentation_return_url') === '') { // ToDo
			$tplExit = new ilTemplate("tpl.lti_exit.html", true, true, "Services/LTI");
			$tplExit->setVariable('TXT_LTI_EXITED',$this->lng->txt('lti_exited'));
			$tplExit->setVariable('LTI_EXITED_INFO',$this->lng->txt('lti_exited_info'));
			$html = $tplExit->get();
			$this->logout();
			print $html;
			exit;
		}
		else {
			$this->logout();
			header('Location: ' . $_SESSION['lti_launch_presentation_return_url']);
			exit; 
		}	
	}

	/**
	 * logout ILIAS and destroys Session and ilClientId cookie
	 */
	function logout() 
	{
		$this->dic->logger()->lti()->info("logout");
		$this->deactivate();
		ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
		//$this->dic['ilAuthSession']->logout();
		$GLOBALS['DIC']['ilAuthSession']->logout();
		// reset cookie
		$client_id = $_COOKIE["ilClientId"];
		ilUtil::setCookie("ilClientId","");
	}

	public function getCmdLink(String $cmd) : String {
		global $ilCtrl;
		$targetScript = ($ilCtrl->getTargetScript() !== 'ilias.php') ? "ilias.php" : "";
		return $this->link_dir.$targetScript.$ilCtrl->getLinkTargetByClass(array('illtiroutergui',strtolower(get_class($this))),$cmd)."&baseClass=illtiroutergui";
	}

	private function getSessionValue(String $sess_key) : String
	{
		if (isset($_SESSION[$sess_key]) && $_SESSION[$sess_key] != '') {
			return $_SESSION[$sess_key];
		}
		else {
			return '';
		}
	}
}
?>
