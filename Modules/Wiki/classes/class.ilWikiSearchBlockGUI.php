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

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for wiki searchblock
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesWiki
*/
class ilWikiSearchBlockGUI extends ilBlockGUI
{
    public static $block_type = "wikisearch";
    public static $st_data;
    
    /**
    * Constructor
    */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $lng = $DIC->language();
        
        parent::__construct();
        
        $lng->loadLanguageModule("wiki");
        $this->setEnableNumInfo(false);
        
        $this->setTitle($lng->txt("wiki_wiki_search"));
        $this->allow_moving = false;
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }
    
    /**
    * Get Screen Mode for current command.
    */
    public static function getScreenMode()
    {
        return IL_SCREEN_SIDE;
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");

        switch ($next_class) {
            default:
                return $this->$cmd();
        }
    }

    /**
    * Get bloch HTML code.
    */
    public function getHTML()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;
        
        return parent::getHTML();
    }

    /**
    * Fill data section
    */
    public function fillDataSection()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;
        
        $tpl = new ilTemplate("tpl.wiki_search_block.html", true, true, "Modules/Wiki");
        
        // go
        $tpl->setVariable("TXT_PERFORM", $lng->txt("wiki_search"));
        $tpl->setVariable(
            "FORMACTION",
            $ilCtrl->getFormActionByClass("ilobjwikigui", "performSearch")
        );
        $tpl->setVariable(
            "SEARCH_TERM",
            ilUtil::prepareFormOutput(ilUtil::stripSlashes($_POST["search_term"]))
        );

        $this->setDataSection($tpl->get());
    }
}
