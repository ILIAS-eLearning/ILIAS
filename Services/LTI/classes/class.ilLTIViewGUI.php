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
class ilLTIViewGUI extends ilBaseViewGUI
{	
	private static $instance = null;
	
	/**
	 * Constructor
	 * @return 
	 */
	public function __construct()
	{
		
		parent::__construct();
		
		$this->allow_desktop = false;
		$this->view_nav = true;
		$this->use_top_bar_url = false;
		
		
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
		return self::$instance = new ilLTIViewGUI();
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
	 * activate LTI GUI
	 * @return void
	 * */
	public function activate() 
	{
		$this->findEffectiveRefId();
		$this->active = true;
		$_SESSION['il_lti_mode'] = "1";
 		$this->initGUI();
	}
	
	/**
	 * Set the environment backend for GUI (tree and locator behaviour, home link, ....)
	 * it is also possible to hide locator and treeicon, but if activated elsewhere a clean root folder is defined
	 * ToDo: conceptual discussion
	 */ 
	public function initGUI() 
	{
		global $lng;
		$this->log("initGUI");
		$lng->loadLanguageModule("lti");
		$baseclass = strtolower($_GET['baseClass']);
		$cmdclass = strtolower($_GET['cmdClass']);
		$this->log("baseClass=".$baseclass);
		$this->log("cmdClass=".$cmdclass);
		// init home_id, home_type, home_url and home_items if not already set
		if ($this->home_id === '') 
		{
			$this->home_id = $_SESSION['lti_context_id'];
		}
		if ($this->home_obj_id === '') 
		{
			$this->home_obj_id = ilObject::_lookupObjectId($this->home_id);
		}
		if ($this->home_type === '') 
		{
			$this->home_type = ilObject::_lookupType($this->home_id,true);
		}
		if ($this->home_url === '') 
		{
			$this->home_url = $this->getHomeLink();
		}
		if ($this->home_title === '') 
		{
			$this->home_title = $this->getHomeTitle();
		}
		if (count($this->home_items) == 0) 
		{
			$this->home_items = $this->dic['tree']->getSubTreeIds($this->home_id);
			// add home_id to the item list too
			$this->home_items[] = $this->home_id;
		}
		$this->log("home_id: " . $this->home_id);
		$this->log("home_obj_id: " . $this->home_obj_id);
		$this->log("home_type: " . $this->home_type);
		$this->log("home_url: " . $this->home_url);
		$this->log("home_title: " . $this->home_title);
		$this->log("home_items: " . $this->home_items);
		$this->log("current_ref_id: " . $this->current_ref_id);
		$this->log("current_type: " . $this->current_type);
		
		switch ($baseclass) 
		{
			case 'illtiroutergui' :
				return;
				break;
			case 'ilpersonaldesktopgui' :
				//return;
				if (!$this->allowDesktop()) 
				{
					$this->log("desktop is not allowed");
					//$_SESSION['failure'] = "lti_not_allowed";
					$this->redirectToHome(self::MSG_ERROR,$lng->txt("lti_not_allowed"));
					$this->redirectToHome();
					return;
				} 
				break;
		}
		
		if ($this->current_ref_id === '') 
		{ // ToDo: conceptual discussion, only initGUI on baseClass=repositorygui? 
			return;
		}
		if ($this->use_top_bar_url) {
		
			// set the tree_root_id for tree and locator if ref_id is sub_item or context itself
			if (in_array($this->current_ref_id, $this->home_items)) 
			{
				$this->log($this->current_ref_id . " in lti context"); 
				$this->setInContext();
			}
			else // check if another parent root_folder_id exists for the view
			{
				$this->log($this->current_ref_id . " NOT in lti context");
				$this->setOutContext();
			}
		}
		else {
			$this->setContext();
		}
	}
	
	// Maybe moving to the BaseViewGUI Template?
	/**
	 * default view in home context
	 */  
	private function setInContext() 
	{
		global $tpl;
		$this->log($tpl);
		$this->show_home_link = false;
		$this->tree_root_id = ($this->fix_tree_id === '') ? $this->home_id : $this->fix_tree_id;
		$_SESSION['lti_tree_root_id'] = $this->tree_root_id;
		ilUtil::sendInfo("sdfsdfsdfsdf");
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
				$this->log("isGrandChild of root_folder_id");
				$allowed = true;
			}
			else 
			{
				$this->log("is not allowed");
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
			$_SESSION['lti_tree_root_id'] = $this->tree_root_id;
		}
		else 
		{
			//$this->redirectToReferer();
			$this->redirectToHome(self::MSG_ERROR,"lti_not_allowed");
		}
		
	}
	
	/**
	 * current container object is set as root for locator and tree
	 */ 
	private function setContext() 
	{
		$this->log("setContext");
		if ($this->isContainer($this->current_type)) 
		{
			$this->tree_root_id = $this->current_ref_id;
			$this->log("set lti_tree_root_id: " . $this->tree_root_id);
			$_SESSION['lti_tree_root_id'] = $this->tree_root_id;
		}
	}
	
	public function render($tpl,$part) 
	{
		global $lng, $DIC;
		$lng->loadLanguageModule("lti");
		$f = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();
		switch ($part) 
		{
			case 'top_bar_header' :
				if(!$this->show_home_link) 
				{
					if (!$tpl->blockExists("header_top_title"))
					{
						$tpl->addBlockFile("HEADER_TOP_TITLE","header_top_title","tpl.header_top_title.html","Services/LTI");
					}
					//$tpl->setVariable("TXT_HEADER_TITLE", "LTI header replaced");
					$tpl->setVariable("TXT_HEADER_TITLE", "LTI Sitzung");
				}
				else {
					if (!$tpl->blockExists("header_back_bl")) 
					{
						$tpl->addBlockFile("HEADER_BACK_BL","header_back_bl","tpl.header_back_bl.html","Services/LTI");
					}
					$tpl->setVariable("URL_HEADER_BACK", $this->home_url);
					//$tpl->setVariable("TXT_HEADER_BACK", $lng->txt("lti_back_to_home")); // ToDo: $lng variable
					$tpl->setVariable("TXT_HEADER_BACK", "Zurück zu ". $this->home_title); // ToDo: $lng variable		
				}
				break;
			case 'view_nav' :
				if (!$this->view_nav) 
				{
					break;
				}
				$tpl->setVariable("TXT_VIEW_NAV", $lng->txt("lti_navigation")); // ToDo: language files
				$nav_entries = $this->getNavEntries();
				$tpl->setVariable("VIEW_NAV_EN", $nav_entries);
				
				break;
			case 'user_logged_in' :
				if (!$tpl->blockExists("userisloggedin"))
				{
					$tpl->addBlockFile("USERLOGGEDIN","userisloggedin","tpl.user_logged_in.html","Services/LTI");
				}
				$tpl->setVariable("LINK_LTI_EXIT", $this->getCmdLink('exit'));
				$tpl->setVariable("TXT_LTI_EXIT",$lng->txt("lti_exit"));
				$btn = $f->button()->close();
				$btnHtml = $renderer->render($btn);
				$tpl->setVariable("EXIT_BUTTON",$btnHtml);
				break;
		}
	}
	
	private function getNavEntries() 
	{
		global $lng, $ilNavigationHistory, $ilSetting, $ilCtrl;
		include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
		$gl = new ilGroupedListGUI();
		$gl->setAsDropDown(true);
		
		include_once("./Services/Link/classes/class.ilLink.php");
		
		$icon = ilUtil::img(ilObject::_getIcon((int)$this->home_obj_id, "tiny"));
		
		$gl->addEntry($icon." ". $this->getHomeTitle(), $this->getHomeLink(),
			"_top");
		
		
		$items = $ilNavigationHistory->getItems();
		reset($items);
		$cnt = 0;
		$first = true;

		foreach($items as $k => $item)
		{
			if ($cnt >= 10) break;
			
			if (!isset($item["ref_id"]) || !isset($_GET["ref_id"]) ||
				($item["ref_id"] != $_GET["ref_id"] || !$first) && $this->home_id != $item["ref_id"]) // do not list current item
			{
				if ($cnt == 0)
				{
					$gl->addGroupHeader($lng->txt("last_visited"), "ilLVNavEnt");
				}
				$obj_id = ilObject::_lookupObjId($item["ref_id"]);
				$cnt ++;
				$icon = ilUtil::img(ilObject::_getIcon($obj_id, "tiny"));
				$ititle = ilUtil::shortenText(strip_tags($item["title"]), 50, true); // #11023
				$gl->addEntry($icon." ".$ititle, $item["link"],	"_top", "", "ilLVNavEnt");

			}
			$first = false;
		}
		
		if ($cnt > 0)
		{
			$gl->addEntry("» ".$lng->txt("remove_entries"), "#", "",
				"return il.MainMenu.removeLastVisitedItems('".
				$ilCtrl->getLinkTargetByClass("ilnavigationhistorygui", "removeEntries", "", true)."');",
				"ilLVNavEnt");
		}
		
		return $gl->getHTML();
	}
	
	/**
	 * add css files to the header
	 */ 
	public function addCss() 
	{
		$arr = array();
		//$arr[] = "./Services/LTI/templates/default/hide.css";
		return $arr;
	}
	
	/**
	 * append css styles just before </body>
	 */ 
	public function appendInlineCss() 
	{
		$arr = array();
		$arr[] = "./Services/LTI/templates/default/lti.css";
		
		if (isset($_SESSION['lti_launch_css_url']) && $_SESSION['lti_launch_css_url'] != "") {
			$arr[] = $_SESSION['lti_launch_css_url'];
		} 
		return $arr;
	}
	
	/**
	 * exit LTI session and if defined redirecting to returnUrl
	 * ToDo: Standard Template with delos ...
	 */
	public function exitLti() 
	{
		global $lng;
		$lng->loadLanguageModule("lti");
		$this->log("exitLti");
		if ($this->getSessionValue('lti_launch_presentation_return_url') === '') {
			$tplExit = new ilTemplate("tpl.lti_exit.html", true, true, "Services/LTI");
			$tplExit->setVariable('TXT_LTI_EXITED',$lng->txt('lti_exited'));
			$tplExit->setVariable('LTI_EXITED_INFO',$lng->txt('lti_exited_info'));
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
		//$DIC->logger()->root()->debug("logout");
		ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);		
		$this->dic['ilAuthSession']->logout();
		// reset cookie
		$client_id = $_COOKIE["ilClientId"];
		ilUtil::setCookie("ilClientId","");
	}
}
?>
