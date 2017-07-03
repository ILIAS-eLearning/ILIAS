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
 * @classDescription Base class for all ILIAS Views
 * 
 * @author Stefan Schneider <schneider@hrz.uni-marburg.de
 * @version $id$
 *  
 * @ingroup ServicesView
 */
abstract class ilBaseViewGUI
{
	/*
	const TREE_HIDE = 1; // always hide tree
	const TREE_MODULES_HIDE = 2; // only hide if ref_id
	const TREE_CURRENT_CONTAINER = 2; // always show current container 
	const TREE_PARENT_CONTAINER = 3;
	const TREE_ROOT_CONTAINER = 4;
	*/
	
	/* Hook Actions */
	const UNSPECIFIED = "";
	const KEEP = "";
	const REPLACE = "r";
	const APPEND = "a";
	const PREPEND = "p";
	const SKIP = "s";
	
	/**
	 * Messsage Codes
	 */ 
	const MSG_ERROR = "0";
	const MSG_INFO = "1";
	const MSG_QUESTION = "2";
	const MSG_SUCCESS = "3";
	
	/*
	 * Spacer CSS Classes
	 */ 
	const TOP_SPACER = "ilFixedTopSpacer";
	const TOP_SPACER_BAR_ONLY = "ilFixedTopSpacerBarOnly";
	
	/**
	 * override these switches in the view constructor
	 */
	protected $topBarOnly = false; 
	protected $show_locator = true;	
	protected $show_main_menu = true;
	protected $show_tree_icon = true;
	protected $show_ilias_footer = true; 
	protected $show_get_messages = true;
	protected $allow_desktop = true;
	protected $show_action_menu = true;
	protected $show_right_column = true;
	protected $show_left_column = true;
	protected $ui_hook = false;
	
	/* 
	 * Main Menu component hooks
	 */
	protected $main_menu_list_entries = self::KEEP;
	protected $search = self::KEEP;
	protected $statusbox = self::KEEP;
	protected $main_header = self::KEEP;
	protected $user_logged_in = self::KEEP;
	protected $top_bar_header = self::KEEP;
	
	
	/**
	 * 
	 */ 
	protected $active = false;
	
	protected $dic = null;
	
	protected $ctrl = null;
	
	protected $ilias = null;
	
	protected $link_dir = "";
	
	/**
	 * home id (ref_id)
	 * The view maybe support a view home link in the topbar (static or dynamically, see topbar_back_url in Member- and LTIView)
	 */ 
	protected $home_id = '';
	
	/**
	 * home type (obj_type of ref_id)
	 * The view maybe support a view home link in the topbar (static or dynamically, see topbar_back_url in Member- and LTIView)
	 */ 
	protected $home_type = '';
	
	/**
	 * home Link (see topbar_back_url concept in MainMenuGUI)
	 */ 
	protected $home_url = '';
	
	/**
	 * cache ref_ids of home subtree items 
	 * if request is in home context $show_home_link = false
	 * else $show_home_link = true 
	 */
	protected $home_items = [];
	  
	/**
	 * show home link in topbar (similar to topbar_back_url = '' in MainMenu)
	 */ 
	protected $show_home_link = false;
	
	/**
	 * root folder id
	 * Don't allow any access to higher level objects in this view
	 */ 
	protected $root_folder_id = ROOT_FOLDER_ID;
	
	/**
	 * all allowed items
	 */ 
	protected $root_folder_items = []; // deprecated
	
	/**
	 * dynamic tree_root_folder_id
	 * show tree from tree_root_id
	 */
	public $tree_root_id = ROOT_FOLDER_ID;
	
	/**
	 * tree root types
	 * show tree from ref_id with obj_types
	 * if requested obj_type of ref_id is in array of $root_tree_types show this id as tree_root_id,
	 * else lookup if a parent obj_type of the ref_id matches the array and set tree_root_id to the first match
	 */
	protected $tree_root_types = [];
	
	/**
	 * Fix tree_id
	 * Show always tree from id
	 */
	protected $fix_tree_id = '';
	
	/**
	 * ref_id from current request
	 */
	protected $current_ref_id = "";
	
	/**
	 * obj_type of current ref_id
	 */
	protected $current_type = "";
	
	
	public function __construct() 
	{
		global $DIC, $ilCtrl, $ilias, $lng;
		$this->dic = $DIC;
		$this->ctrl = $ilCtrl;
		$this->ilias = $ilias;
		$this->link_dir = (defined("ILIAS_MODULE"))
					? "../"
					: "";
	}
	
	/*
	 * activate 
	 * the activation logic must be implemented in the view
	 * @return bool
	 */ 
	public abstract function activate();
	
	/**
	 * if activated return true
	 * @return bool
	 */
	public function isActive() {
		return $this->active;
	}
	
	/**
	 * Activate view for this session and root_folder_id.
	 * @return 
	 * @param int $a_ref_id Reference Id of course or group. We have to discuss the handling of "not container" object types
	 */
	 
	/* 
	 * 
	public function activate($view, $a_ref_id = ROOT_FOLDER_ID)
	{
		global $DIC;
		
		if (isset($_SESSION['il_view_mode']) && $_SESSION['il_view_mode'] !== "") {			
			if (array_key_exists($_SESSION['il_view_mode'],$DIC)) {
				$current_view = $_SESSION['il_view_mode'];
				$current_view->deactivate();
				$this->last_view = $_SESSION['il_view_mode'];
				$_SESSION['il_view_mode_last'] = $this->last_view;
				$_SESSION['il_view_root_folder_last'] = $current_view->getRootFolderId(); // maybe not needed
			}
		}
		 
		$this->active = true;
		$_SESSION['il_view_mode'] = $view;
		$this->setRootFolderId = (int) $a_ref_id;
	}
	*/
	
	/**
	 * Deactivate view
	 * @return 
	 */
	 
	/* 
	public function deactivate()
	{
		$this->active = false;
		//$this->setRootFolderId = ROOT_FOLDER_ID;
	}
	*/
	/**
	 * Toggle activation status
	 * @return 
	 * @param int $a_ref_id
	 * @param bool $a_activation
	 */
	/* 
	public function toggleActivation($a_ref_id, $a_activation)
	{
		if($a_activation)
		{
			return $this->activate($a_ref_id);
		}
		else
		{
			return $this->deactivate();
		}
	}
	*/
	
	/**
	 * this means the whole header TopBar,MainMenu,MainMenuEntries
	 * @return bool
	 */
	public function showMainMenu() {
		return $this->show_main_menu;
	}
	
	/**
	 * @return bool
	 */
	public function showTreeIcon() {
		return $this->show_tree_icon;
	}
	
	/**
	 * @return bool
	 */
	public function showLocator() {
		return $this->show_locator;
	}
	
	/**
	 * @return bool
	 */
	public function allowDesktop() {
		return $this->allow_desktop;
	}
	
	/**
	 * @return bool
	 */
	public function showActionMenu() {
		return $this->show_action_menu;
	}
	
	/**
	 * @return bool
	 */
	public function showRightColumn() {
		return $this->show_right_column;
	}
	
	/**
	 * @return bool
	 */
	public function showLeftColumn() {
		return $this->show_left_column;
	}
	/**
	 * @return bool
	 */
	public function showGetMessages() {
		return $this->show_get_messages;
	}
	
	/**
	 * spacer_class
	 */
	public function spacerClass() {
		return ($this->topBarOnly) ? self::TOP_SPACER_BAR_ONLY : self::TOP_SPACER;
	}
	
	/**
	 * main_menu_list_entries
	 */
	public function hookMainMenuListEntries() {
		return $this->main_menu_list_entries;
	}
	  
	/**
	 * search
	 */
	public function hookSearch() {
		return $this->search;
	}
	
	/**
	 * statusbox
	 */
	public function hookStatusbox() {
		return $this->statusbox;
	}
	
	/**
	 * main_header
	 */
	public function hookMainHeader() {
		return $this->main_header;
	}
	
	/**
	 * user_logged_in
	 */
	public function hookUserLoggedIn() {
		return $this->user_logged_in;
	}
	
	/**
	 * top_bar_header
	 */
	public function hookTopBarHeader() {
		return $this->top_bar_header;
	} 
	
	/**
	 * the whole header TopBar,MainMenu,MainMenuEntries
	 * custom MainMenu HTML: deprecated
	 * @return string
	 */
	public function getMainMenuHTML() {
		return "";
	}
	
	/**
	 *  css files are added to the header
	 * @return array
	 */
	public function addCss() {
		return [];
	}
	
	/**
	 * content of css files are appended to the main page html just before </body> end tag
	 * @return array
	 */
	public function appendInlineCss() 
	{
		return [];
	}
	
	/**
	 * helper function for home link creation
	 */ 
	protected function getHomeLink() 
	{
		return $this->link_dir."goto.php?target=".$this->home_type."_".$this->home_id;
	}
	
	/**
	 * helper function for cmd link creation
	 */ 
	protected function getCmdLink($cmd) {
		$targetScript = ($this->ctrl->getTargetScript() !== 'ilias.php') ? "ilias.php" : "";
		return $this->link_dir.$targetScript.$this->ctrl->getLinkTargetByClass(array('illtiroutergui',strtolower(get_class($this))),$cmd)."&baseClass=illtiroutergui";
	}
	
	//https://www.simple.org:9443/ilias_lti/ilias.php?ref_id=75&link_id=4&cmd=callLink&cmdClass=ilobjlinkresourcegui&cmdNode=ue:uc&baseClass=ilLinkResourceHandlerGUI
	/**
	 * switch on|off uiHook
	 * @return bool
	 */
	public function uiHook() {
		return $this->ui_hook;
	}  

	/**
	 * Modify HTML output of GUI elements. Modifications modes are:
	 * - ilUIHookPluginGUI::KEEP (No modification)
	 * - ilUIHookPluginGUI::REPLACE (Replace default HTML with your HTML)
	 * - ilUIHookPluginGUI::APPEND (Append your HTML to the default HTML)
	 * - ilUIHookPluginGUI::PREPEND (Prepend your HTML to the default HTML)
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param string $a_par array of parameters (depend on $a_comp and $a_part)
	 *
	 * @return array array with entries "mode" => modification mode, "html" => your html
	 */
	function getHTML($a_comp, $a_part, $a_par = array()) {
		return array("mode" => self::KEEP, "html" => "");
	}		
	
	/**
	 * Modify user interface, paramters contain classes that can be modified
	 *
	 * @param
	 * @return
	 */
	function modifyGUI($a_comp, $a_part, $a_par = array())
	{
	}

	/**
	 * Modify HTML based on default html and plugin response
	 *
	 * @param	string	default html
	 * @param	string	resonse from plugin
	 * @return	string	modified html
	 */
	final function modifyHTML($a_def_html, $a_resp)
	{
		switch ($a_resp["mode"])
		{
			case self::REPLACE:
				$a_def_html = $a_resp["html"];
				break;
			case self::APPEND:
				$a_def_html.= $a_resp["html"];
				break;
			case self::PREPEND:
				$a_def_html = $a_resp["html"].$a_def_html;
				break;
		}
		return $a_def_html;
	}
	
	/**
	 * 
	 */ 
	public function checkMessages() {
		global $lng;
		$msg = $_GET["view_msg"];
		$msg_type = $_GET["view_msg_type"];
		switch ($msg_type) {
			case  self::MSG_ERROR:
				ilUtil::sendFailure($lng->txt($msg));
				break;
			case  self::MSG_INFO:
				ilUtil::sendInfo($lng->txt($msg));
				break;
			case  self::MSG_QUESTION:
				ilUtil::sendQuestion($lng->txt($msg));
				break;
			case  self::MSG_SUCCESS:
				ilUtil::sendSuccess($lng->txt($msg));
				break;
		}
	}
	
	/**
	 * 
	 */ 
	function redirectToHome($_msg_type=self::MSG_INFO, $_msg='') {
		$msg = ($_msg !== '') ? '&view_msg='.$_msg.'&view_msg_type='.$_msg_type : '';
		$link = $this->getHomeLink().$msg;
		$this->dic->logger()->root()->write("redirectLink: " . $link);
		ilUtil::redirect($link);
		exit;
	}
	
	/**
	 * Find effective ref_id for request
	 */
	public function findEffectiveRefId()
	{
		if((int) $_GET['ref_id'])
		{
			$this->current_type = ilObject::_lookupType($_GET['ref_id'],true);
			return $this->current_ref_id = (int) $_GET['ref_id'];
		}
		
		$target_arr = explode('_',(string) $_GET['target']);
		if(isset($target_arr[1]) and (int) $target_arr[1])
		{
			$this->current_type = ilObject::_lookupType($target_arr[1],true);
			return $this->current_ref_id = (int) $target_arr[1];
		}
	}
	
	public function getHttpPath() {
		include_once './Services/Http/classes/class.ilHTTPS.php';
		$https = new ilHTTPS();

		if($https->isDetected())
		{
			$protocol = 'https://';
		}
		else
		{
			$protocol = 'http://';
		}
		$host = $_SERVER['HTTP_HOST'];
		$rq_uri = $_SERVER['REQUEST_URI'];
		return ilUtil::removeTrailingPathSeparators($protocol.$host.$rq_uri);
	}
	
	/**
	 * @return bool
	 */
	function isContainer($obj_type) {
		//return true;
		return preg_match("/(crs|grp|cat|root|folder)/",$obj_type);
	} 
	
	protected function log($txt) 
	{
		$this->dic->logger()->root()->write($txt);
	} 
}
?>
