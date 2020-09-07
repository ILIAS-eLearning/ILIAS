<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Handles user interface for wikis
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilWikiHandlerGUI: ilObjWikiGUI
*
* @ingroup ModulesWiki
*/
class ilWikiHandlerGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tpl = $DIC["tpl"];
        $this->nav_history = $DIC["ilNavigationHistory"];
        $ilCtrl = $DIC->ctrl();

        // initialisation stuff
        $this->ctrl = $ilCtrl;
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $lng = $this->lng;
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilNavigationHistory = $this->nav_history;
        
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);
        if ($next_class == "") {
            $this->ctrl->setCmdClass("ilobjwikigui");
            $next_class = $this->ctrl->getNextClass($this);
        }

        // add entry to navigation history
        if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
            $title = ilObject::_lookupTitle($obj_id);

            if ($_GET["page"] != "") {
                $page = $_GET["page"];
            } else {
                include_once("./Modules/Wiki/classes/class.ilObjWiki.php");
                $page = ilObjWiki::_lookupStartPage($obj_id);
            }

            include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
            if (ilWikiPage::exists($obj_id, $page)) {
                include_once("./Modules/Wiki/classes/class.ilWikiPage.php");

                $add = "_" . rawurlencode($page);

                $page_id = ilWikiPage::getPageIdForTitle($obj_id, $page);
                $ptitle = ilWikiPage::lookupTitle($page_id);
                
                $title .= ": " . $ptitle;
                
                $append = ($_GET["page"] != "")
                    ? "_" . ilWikiUtil::makeUrlTitle($page)
                    : "";
                include_once('./Services/Link/classes/class.ilLink.php');
                $goto = ilLink::_getStaticLink(
                    $_GET["ref_id"],
                    "wiki",
                    true,
                    $append
                );
                //var_dump($goto);
                $ilNavigationHistory->addItem(
                    $_GET["ref_id"],
                    "./goto.php?target=wiki_" . $_GET["ref_id"] . $add,
                    "wiki",
                    $title,
                    $page_id,
                    $goto
                );
            }
        }

        switch ($next_class) {
            case 'ilobjwikigui':
                require_once "./Modules/Wiki/classes/class.ilObjWikiGUI.php";
                $mc_gui = new ilObjWikiGUI("", (int) $_GET["ref_id"], true, false);
                $this->ctrl->forwardCommand($mc_gui);
                break;
        }

        $tpl->show();
    }
}
