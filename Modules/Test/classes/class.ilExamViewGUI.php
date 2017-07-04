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
 * @classDescription class for ILIAS ExamView
 * 
 * @author Stefan Schneider <schneider@hrz.uni-marburg.de
 * @version $id$
 * @ingroup ServicesView
 * @ilCtrl_IsCalledBy ilExamViewGUI: ilViewRouterGUI
 * 
 */
 
include_once 'Services/View/classes/class.ilBaseViewGUI.php'; 

class ilExamViewGUI extends ilBaseViewGUI
{	
	private static $instance = null;
	
	// for showcase: define the Exam global role id
	const EXAM_ROLE_ID = 318;
	/**
	 * Constructor
	 * @return 
	 */
	public function __construct()
	{
		parent::__construct();
		
		/* view type */
		$this->topBarOnly = true;
		
		/* main components */
		$this->show_locator = false;
		$this->show_ilias_footer = false;
		$this->show_tree_icon = false;
		$this->allow_desktop = true;
		$this->show_get_messages = true;
		$this->show_action_menu = false;
		$this->show_right_column = false;
		$this->show_left_column = false;
		$this->ui_hook = true;
		
		/* MainMenu hooks */
		$this->main_menu_list_entries = self::KEEP;
		$this->search = self::SKIP;
		$this->statusbox = self::SKIP;
		$this->main_header = self::KEEP;
		$this->user_logged_in = self::KEEP;
		$this->top_bar_header = self::KEEP;
		
		// for Testing: 
		// With this settings a fix folder with id $this->root_folder_id is set for locator and tree
		//$this->root_folder_id = 69;
		//$this->fix_tree_id = 69;
		//$this->tree_root_types[] = 'crs';
	}
	
	/**
	 * Get instance
	 * @return object ilLTIViewGUI
	 */
	public static function getInstance()
	{
		if(self::$instance != null)
		{
			return self::$instance;
		}
		return self::$instance = new ilExamViewGUI();
	}
	
	/**
	 * for ctrl commands
	 */ 
	function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case 'exit' :
				$this->exitLti();
			break;
		}
	}
	
	/** 
	 * always enabled (ToDo: LTI Service Admin Setting)
	 * @return bool
	 */ 
	public function isEnabled() {
		return true;
	}
	
	/** 
	 * read environment or service settings and trigger activation or not 
	 * in other view scenarios like Safe-Exam-Browser, this depends on a combination of SEBService, User roles and User-Agent
	 * @return void
	 * */
	public function checkActivation() 
	{
		global $rbacreview; // ToDo $DIC
		//$this->dic->logger()->root()->write("checkActivation");
		if (!$this->isEnabled()) {
			//$this->dic->logger()->root()->write("view is not enabled");
			$this->active = false;
			return false;
		}
		if ($rbacreview->isAssigned($this->dic->user()->id, self::EXAM_ROLE_ID)) 
		{
			//$this->dic->logger()->root()->write("activate...");
			$this->active = true;
			return true;
		}
		else 
		{
			$this->active = false;
			return false;
		}
	}
	
	/**
	 * Set the environment backend for GUI (tree and locator behaviour, home link, ....)
	 * it is also possible to hide locator and treeicon, but if activated elsewhere a clean root folder is defined
	 * ToDo: conceptual discussion
	 */ 
	public function initGUI() 
	{
		$this->dic->logger()->root()->write("initGUI");
		$baseclass = strtolower($_GET['baseClass']);
		$cmdclass = strtolower($_GET['cmdClass']);
		$this->dic->logger()->root()->write("baseClass=".$baseclass);
		$this->dic->logger()->root()->write("cmdClass=".$cmdclass);
		
		// init home_id, home_type, home_url and home_items if not already set
		if ($this->home_id === '') 
		{
			$this->home_id = $_SESSION['lti_context_id'];
		}
		if ($this->home_type === '') 
		{
			$this->home_type = ilObject::_lookupType($this->home_id,true);
		}
		if ($this->home_url === '') 
		{
			$this->home_url = $this->getHomeLink();
		}
		if (count($this->home_items) == 0) 
		{
			$this->home_items = $this->dic['tree']->getSubTreeIds($this->home_id);
			// add home_id to the item list too
			$this->home_items[] = $this->home_id;
		}
		
		switch ($baseclass) 
		{
			case 'ilviewroutergui' :
				return;
				break;
			case 'ilpersonaldesktopgui' :
				//return;
				if (!$this->allowDesktop()) 
				{
					$this->dic->logger()->root()->write("desktop is not allowed"); 
					$this->redirectToHome(self::MSG_ERROR,"lti_not_allowed");
				} 
				break;
		}
		
		if ($this->current_ref_id === '') 
		{ // ToDo: conceptual discussion, only initGUI on baseClass=repositorygui? 
			return;
		}
		
		// set the tree_root_id for tree and locator if ref_id is sub_item or context itself
		if (in_array($this->current_ref_id, $this->home_items)) 
		{
			$this->dic->logger()->root()->write($this->current_ref_id . " in lti context"); 
			$this->setInContext();
		}
		else // check if another parent root_folder_id exists for the view
		{
			$this->dic->logger()->root()->write($this->current_ref_id . " NOT in lti context");
			$this->setOutContext();
		}
	}
	
	// Maybe moving to the BaseViewGUI Template?
	/**
	 * default view in home context
	 */  
	private function setInContext() 
	{
		$this->show_home_link = false;
		// save last context for redirecting
		/*
		$_SESSION['view_last_context_id'] = $this->current_ref_id;
		$_SESSION['view_last_context_type'] = $this->current_type;
		
		if (!is_int($pos = strpos($_url, "&view_msg_type="))) 
		{
			$_SESSION['view_last_http_path'] = $this->getHttpPath();
		}
		*/ 
		$this->tree_root_id = ($this->fix_tree_id === '') ? $this->home_id : $this->fix_tree_id;
	}
	
	/**
	 * view out of the home context with link back to home
	 */
	private function setOutContext() 
	{
		$this->show_home_link = true;
		// is there a root folder > ROOT_FOLDER_ID defined? check view access
		$allowed = false;
		if ($this->root_folder_id > ROOT_FOLDER_ID) 
		{
			if ($this->dic['tree']->isGrandChild($this->root_folder_id,$this->current_ref_id) || $this->current_ref_id == $this->root_folder_id) 
			{
				$this->dic->logger()->root()->write("isGrandChild of root_folder_id");
				$allowed = true;
			}
			else 
			{
				$this->dic->logger()->root()->write("is not allowed");
				$allowed = false;
			}
		}
		else 
		{
			$allowed = true;
		}
		if ($allowed) 
		{
			if (in_array($this->current_type, $this->tree_root_types)) 
			{
				$this->tree_root_id = ($this->fix_tree_id === '') ? $this->current_ref_id : $this->fix_tree_id;
			}
			else
			{
				foreach($this->tree_root_types as $obj_type) 
				{
					$ref_id = $this->dic['tree']->checkForParentType($this->current_ref_id,$obj_type);
					if ($ref_id > 0) 
					{
						$this->tree_root_id = ($this->fix_tree_id === '') ? $ref_id : $this->fix_tree_id;
					}
					else {
						$this->tree_root_id = ($this->fix_tree_id === '') ? $this->current_ref_id : $this->fix_tree_id;
					}
				}
			} 
		}
		else 
		{
			//$this->redirectToReferer();
			$this->redirectToHome(self::MSG_ERROR,"lti_not_allowed");
		}
	}
	
	public function replace($tpl,$part) 
	{
		global $lng;
		switch ($part) 
		{
			case 'user_logged_in' :
				$tpl->addBlockFile("USERLOGGEDIN","userisloggedin","tpl.user_logged_in.html","Services/LTI");
				$tpl->setVariable("TXT_LOGIN_AS",$lng->txt("login_as"));
				$user_img_src = $this->ilias->account->getPersonalPicturePath("small", true);
				$user_img_alt = $this->ilias->account->getFullname();
				$tpl->setVariable("USER_IMG", ilUtil::img($user_img_src, $user_img_alt));
				$tpl->setVariable("TXT_LTI_EXIT",$lng->txt("lti_exit_session"));
				$tpl->setVariable("LINK_LTI_EXIT", $this->getCmdLink('exit'));
				break;
			case 'top_bar_header' :
				if(!$this->show_home_link) {
					$tpl->addBlockFile("HEADER_TOP_TITLE","header_top_title","tpl.header_top_title.html","Services/LTI");
					$tpl->setVariable("TXT_HEADER_TITLE", "LTI header replaced");
				}
				else {
					$tpl->addBlockFile("HEADER_BACK_BL","header_back_bl","tpl.header_back_bl.html","Services/LTI");
					$tpl->setVariable("URL_HEADER_BACK", $this->home_url);
					$tpl->setVariable("TXT_HEADER_BACK", $lng->txt("lti_back_to_home")); // ToDo: $lng variable		
				}
				break;
		}
		
	}
	
	/**
	 * add css files to the header
	 */ 
	public function addCss() 
	{
		$arr = array();
		//$arr[] = "./Modules/Test/templates/default/exam.css";
		return $arr;
	}
	
	/**
	 * append css styles just before </body>
	 */ 
	public function appendInlineCss() 
	{
		$arr = array();
		$arr[] = "./Modules/Test/templates/default/exam.css";
		return $arr;
	}
	
	private function getSebObject() { // obsolet
		$login = ($this->dic->user()->getLogin()) ? $this->dic->user()->getLogin() : "";
		$firstname = ($this->dic->user()->getFirstname()) ? $this->dic->user()->getFirstname() : "";
		$lastname = ($this->dic->user()->getLastname()) ? $this->dic->user()->getLastname() : "";
		$matriculation = ($this->dic->user()->getMatriculation()) ? $this->dic->user()->getMatriculation() : "";
		
		$seb_user = array(
					"login" => $login,
					"firstname" => $firstname,
					"lastname" => $lastname,
					"matriculation" => $matriculation
				);
		$seb_object = array("user" => $seb_user);
		$ret = json_encode($seb_object); 
		return $ret;
	}
	
	public function getHTML($a_comp, $a_part, $a_par = array()) 
	{
		global $tpl;
		//$this->dic->logger()->root()->write("comp: ".$a_comp . " - part: ". $a_part . " - tpl_id: " . $a_par['tpl_id']);
		if ($a_comp == "Services/Container" && $a_part == "right_column") 
		{
			return array("mode" => self::REPLACE, "html" => "");
		}
		
		if ($a_part == "template_load" && $a_par["tpl_id"] == "Modules/Test/tpl.il_as_tst_kiosk_head.html") 
		{
			// ToDo: load template file
			$html = file_get_contents("./Modules/Test/templates/default/tpl.il_as_tst_kiosk_head_exam.html");
			return array("mode" => self::REPLACE, "html" => $html);
		}
		// JavaScript Injection of seb_object on PD kioskmode: ToDo
		if ($a_comp == "Services/MainMenu" && $a_part == "main_menu_list_entries") {	
			//$this->dic->logger()->root()->write("***********");		
			$tpl->addJavaScript("./Modules/Test/templates/default/exam.js");
			$seb_object = $this->getSebObject(); 
			return array("mode" => self::REPLACE, "html" => "<script type=\"text/javascript\">var seb_object = " . $seb_object . ";</script>");
		}
		return array("mode" => self::KEEP, "html" => "");
	}
	
	/**
	 * exit LTI session and if defined redirecting to returnUrl
	 * ToDo: Standard Template with delos ...
	 */
	public function exitLti() 
	{
		global $lng;
		$this->dic->logger()->root()->write("exitLti");
		if ($this->getSessionValue('lti_launch_presentation_return_url') === '') {
			$tplExit = new ilTemplate("tpl.lti_exit.html", true, true, "Services/LTI");
			$tplExit->setVariable('TXT_EXITED_TITLE',$lng->txt('exited_title'));
			$tplExit->setVariable('TXT_EXITED',$lng->txt('exited'));
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
		//$DIC->logger()->root()->write("logout");
		ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);		
		$this->dic['ilAuthSession']->logout();
		// reset cookie
		$client_id = $_COOKIE["ilClientId"];
		ilUtil::setCookie("ilClientId","");
	}
	
	/**
	 * get session value != ''
	 * 
	 * @param $sess_key string 
	 * @return string
	 */ 
	function getSessionValue($sess_key) 
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
