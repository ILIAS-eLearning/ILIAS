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
    const LTI_DEBUG = false; // deprecated
    
    /**
     * messsage codes
     */
    const MSG_ERROR = "failure";
    const MSG_INFO = "info";
    const MSG_QUESTION = "question";
    const MSG_SUCCESS = "success";
    
    /**
     * private variables
     */
    private $user = null;
    private $home_id = "";
    private $home_obj_id = "";
    private $home_type = "";
    private $home_title = "";
    private $home_url = "";
    private $link_dir = "";
    private $current_ref_id = "";
    private $current_type = "";
    
    /**
     * public variables
     */
    public $show_locator = true;
    public $member_view = false;
    public $member_view_url = "";
    public $member_view_close_txt = "";
    
    /**
     * Constructor
     * @return
     */
    public function __construct(\ilObjUser $user)
    {
        if (ilContext::hasUser()) {
            $this->user = $user;
            $this->init();
        } else {
            if ($this->isActive()) {
                $this->deactivate();
            }
        }
    }
    
    /**
     * Init LTI mode for lit authenticated users
     */
    private function init()
    {
        $this->link_dir = (defined("ILIAS_MODULE"))
                    ? "../"
                    : "";
        if ($this->isLTIUser()) {
            $this->activate();
        } else {
            if ($this->isActive()) {
                $this->deactivate();
            }
        }
    }
    
    
    /**
     * for compatiblity with ilLTIRouterGUI
     */
    public static function getInstance()
    {
        global $DIC;
        return $DIC["lti"];
    }
    
    /**
     * get LTI Mode from Users->getAuthMode
     * @return boolean
     */
    private function isLTIUser()
    {
        if (!$this->user instanceof ilObjUser) {
            return false;
        }
        return (strpos($this->user->getAuthMode(), 'lti_') === 0);
    }
    
    /**
     * for ctrl commands
     */
    public function executeCommand()
    {
        global $ilCtrl;
        $cmd = $ilCtrl->getCmd();
        switch ($cmd) {
            case 'exit':
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
        $_SESSION['il_lti_mode'] = "1";
        $this->initGUI();
        $this->log("lti view activated");
    }
    
    /**
     * deactivate LTI GUI
     * @return void
     * */
    public function deactivate()
    {
        unset($_SESSION['il_lti_mode']);
        $this->log("lti view deactivated");
    }
    
    /**
     * LTI is active
     * @return boolean
     * */
    public function isActive()
    {
        return (isset($_SESSION['il_lti_mode']));
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
        
        // init home_id, home_type, home_url and home_items if not already set
        if ($this->home_id === '') {
            $this->home_id = $_SESSION['lti_context_id'];
        }
        if ($this->home_obj_id === '') {
            $this->home_obj_id = ilObject::_lookupObjectId($this->home_id);
        }
        if ($this->home_type === '') {
            $this->home_type = ilObject::_lookupType($this->home_id, true);
            $this->show_locator = $this->showLocator($this->home_type);
        }
        if ($this->home_url === '') {
            $this->home_url = $this->getHomeLink();
        }
        if ($this->home_title === '') {
            $this->home_title = $this->getHomeTitle();
        }
    
        switch ($baseclass) {
            case 'illtiroutergui':
                return;
                break;
        }
    }
    
    public function render($tpl, $part)
    {
        global $lng, $DIC;
        $lng->loadLanguageModule("lti");
        $f = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        switch ($part) {
            case 'top_bar_header':
                if (!$this->member_view) {
                    if (!$tpl->blockExists("header_top_title")) {
                        $tpl->addBlockFile("HEADER_TOP_TITLE", "header_top_title", "tpl.header_top_title.html", "Services/LTI");
                    }
                    $tpl->setVariable("TXT_HEADER_TITLE", $lng->txt("lti_session"));
                } else {
                    if (!$tpl->blockExists("header_back_bl")) {
                        $tpl->addBlockFile("HEADER_BACK_BL", "header_back_bl", "tpl.header_back_bl.html", "Services/LTI");
                    }
                    $tpl->setVariable("URL_HEADER_BACK", $this->member_view_url);
                    //$tpl->setVariable("TXT_HEADER_BACK", $lng->txt("lti_back_to_home")); // ToDo: $lng variable
                    $tpl->setVariable("TXT_HEADER_BACK", $this->member_view_close_txt); // ToDo: $lng variable
                }
                break;
            case 'view_nav':
                $tpl->setVariable("TXT_VIEW_NAV", $lng->txt("lti_navigation")); // ToDo: language files
                $nav_entries = $this->getNavEntries();
                $tpl->setVariable("VIEW_NAV_EN", $nav_entries);
                
                break;
            case 'user_logged_in':
                if (!$tpl->blockExists("userisloggedin")) {
                    $tpl->addBlockFile("USERLOGGEDIN", "userisloggedin", "tpl.user_logged_in.html", "Services/LTI");
                }
                $tpl->setVariable("LINK_LTI_EXIT", $this->getCmdLink('exit'));
                $tpl->setVariable("TXT_LTI_EXIT", $lng->txt("lti_exit"));
                $btn = $f->button()->close();
                $btnHtml = $renderer->render($btn);
                $tpl->setVariable("EXIT_BUTTON", $btnHtml);
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
        
        $icon = ilUtil::img(ilObject::_getIcon((int) $this->home_obj_id, "tiny"));
        
        $gl->addEntry(
            $icon . " " . $this->getHomeTitle(),
            $this->getHomeLink(),
            "_self"
        );
        
        
        $items = $ilNavigationHistory->getItems();
        reset($items);
        $cnt = 0;
        $first = true;

        foreach ($items as $k => $item) {
            if ($cnt >= 10) {
                break;
            }
            
            if (!isset($item["ref_id"]) || !isset($_GET["ref_id"]) ||
                ($item["ref_id"] != $_GET["ref_id"] || !$first) && $this->home_id != $item["ref_id"]) { // do not list current item
                if ($cnt == 0) {
                    $gl->addGroupHeader($lng->txt("last_visited"), "ilLVNavEnt");
                }
                $obj_id = ilObject::_lookupObjId($item["ref_id"]);
                $cnt++;
                $icon = ilUtil::img(ilObject::_getIcon($obj_id, "tiny"));
                $ititle = ilUtil::shortenText(strip_tags($item["title"]), 50, true); // #11023
                $gl->addEntry($icon . " " . $ititle, $item["link"], "_self", "", "ilLVNavEnt");
            }
            $first = false;
        }
        
        if ($cnt > 0) {
            $gl->addEntry(
                "» " . $lng->txt("remove_entries"),
                "#",
                "",
                "return il.MainMenu.removeLastVisitedItems('" .
                $ilCtrl->getLinkTargetByClass("ilnavigationhistorygui", "removeEntries", "", true) . "');",
                "ilLVNavEnt"
            );
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
     * helper function for home link creation
     */
    protected function getHomeLink()
    {
        return $this->link_dir . "goto.php?target=" . $this->home_type . "_" . $this->home_id;
    }
    
    public function getHomeTitle()
    {
        return ilObject::_lookupTitle($this->home_obj_id);
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
            $tplExit->setVariable('TXT_LTI_EXITED', $lng->txt('lti_exited'));
            $tplExit->setVariable('LTI_EXITED_INFO', $lng->txt('lti_exited_info'));
            $html = $tplExit->get();
            $this->logout();
            print $html;
            exit;
        } else {
            $this->logout();
            header('Location: ' . $_SESSION['lti_launch_presentation_return_url']);
            exit;
        }
    }
    
    /**
     * logout ILIAS and destroys Session and ilClientId cookie
     */
    public function logout()
    {
        //$DIC->logger()->root()->debug("logout");
        $this->deactivate();
        ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
        //$this->dic['ilAuthSession']->logout();
        $GLOBALS['DIC']['ilAuthSession']->logout();
        // reset cookie
        $client_id = $_COOKIE["ilClientId"];
        ilUtil::setCookie("ilClientId", "");
    }
    
    /**
     * Find effective ref_id for request
     */
    private function findEffectiveRefId()
    {
        if ((int) $_GET['ref_id']) {
            $this->current_type = ilObject::_lookupType($_GET['ref_id'], true);
            return $this->current_ref_id = (int) $_GET['ref_id'];
        }
        
        $target_arr = explode('_', (string) $_GET['target']);
        if (isset($target_arr[1]) and (int) $target_arr[1]) {
            $this->current_type = ilObject::_lookupType($target_arr[1], true);
            $this->home_title = ilObject::_lookupTitle(ilObject::_lookupObjectId($target_arr[1]));
            return $this->current_ref_id = (int) $target_arr[1];
        }
    }
    
    /**
     * @return bool
     */
    private function showLocator($obj_type)
    {
        //return true;
        return preg_match("/(crs|grp|cat|root|fold|lm)/", $obj_type);
    }
    
    /**
     * helper function for cmd link creation
     */
    protected function getCmdLink($cmd)
    {
        global $ilCtrl;
        $targetScript = ($ilCtrl->getTargetScript() !== 'ilias.php') ? "ilias.php" : "";
        return $this->link_dir . $targetScript . $ilCtrl->getLinkTargetByClass(array('illtiroutergui',strtolower(get_class($this))), $cmd) . "&baseClass=illtiroutergui";
    }
    
    /**
     * get session value != ''
     *
     * @param $sess_key string
     * @return string
     */
    public function getSessionValue($sess_key)
    {
        if (isset($_SESSION[$sess_key]) && $_SESSION[$sess_key] != '') {
            return $_SESSION[$sess_key];
        } else {
            return '';
        }
    }
    
    private function log($txt)
    {
        global $DIC;
        if (self::LTI_DEBUG) {
            $DIC->logger()->lti()->write($txt);
        }
    }
}
