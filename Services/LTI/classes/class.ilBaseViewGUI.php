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
     * Spacer CSS Classes
     */
    const TOP_SPACER = "ilFixedTopSpacer";
    const TOP_SPACER_BAR_ONLY = "ilFixedTopSpacerBarOnly";
    
    
    const ROOT_CONTAINER = "container";
    const ROOT_CRS = "crs";
    const ROOT_GRP = "grp";
    
    /**
     * override these switches in the view constructor
     */

    protected $allow_desktop = true; // deprecated but we should discuss concepts
    
    protected $view_nav = true;
    
    protected $use_top_bar_url = false;
    
    protected $root_type = ROOT_CONTAINER;
    
    /**
     *
     */
    
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
     * home obj_id
     */
    protected $home_obj_id = '';
    
    /**
     * home type (obj_type of ref_id)
     * The view maybe support a view home link in the topbar (static or dynamically, see topbar_back_url in Member- and LTIView)
     */
    protected $home_type = '';
    
    /**
     * home title (title of ref_id)
     */
    protected $home_title = '';
    
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
    
    /**
     * @return bool
     */
    public function allowDesktop()
    {
        return $this->allow_desktop;
    }
    
    /**
     * @return bool
     */
    public function showViewNav()
    {
        return $this->view_nav;
    }
    
    /**
     * spacer_class
     */
    public function spacerClass()
    {
        return ($this->topBarOnly) ? self::TOP_SPACER_BAR_ONLY : self::TOP_SPACER;
    }
    
    /**
     *  css files are added to the header
     * @return array
     */
    public function addCss()
    {
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
        return $this->link_dir . "goto.php?target=" . $this->home_type . "_" . $this->home_id;
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
     *
     */
    public function redirectToHome($_msg_type = self::MSG_INFO, $_msg = '')
    {
        //$msg = ($_msg !== '') ? '&view_msg='.$_msg.'&view_msg_type='.$_msg_type : '';
        //$link = $this->getHomeLink().$msg;
        $_SESSION[$_msg_type] = $_msg;
        $link = $this->getHomeLink();
        $this->dic->logger()->lti()->write("redirectLink: " . $link);
        ilUtil::redirect($link);
        exit;
    }
    
    public function getHomeTitle()
    {
        return ilObject::_lookupTitle($this->home_obj_id);
    }
    
    /**
     * Find effective ref_id for request
     */
    public function findEffectiveRefId()
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
    
    public function getHttpPath()
    {
        include_once './Services/Http/classes/class.ilHTTPS.php';
        $https = new ilHTTPS();

        if ($https->isDetected()) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        $host = $_SERVER['HTTP_HOST'];
        $rq_uri = $_SERVER['REQUEST_URI'];
        return ilUtil::removeTrailingPathSeparators($protocol . $host . $rq_uri);
    }
    
    /**
     * @return bool
     */
    public function isContainer($obj_type)
    {
        //return true;
        return preg_match("/(crs|grp|cat|root|folder)/", $obj_type);
    }
    
    protected function log($txt)
    {
        $this->dic->logger()->lti()->write($txt);
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
}
