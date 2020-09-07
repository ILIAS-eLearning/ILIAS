<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004ChapterGUI.php");
require_once("./Modules/Scorm2004/classes/class.ilSCORM2004SeqChapter.php");

/**
* Class ilSCORM2004ChapterGUI
*
* User Interface for Scorm 2004 Chapter Nodes
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilSCORM2004SeqChapterGUI: ilMDEditorGUI, ilNoteGUI
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004SeqChapterGUI extends ilSCORM2004ChapterGUI
{

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_slm_obj, $a_node_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilCtrl->saveParameter($this, "obj_id");
        parent::__construct($a_slm_obj, $a_node_id);
    }


    public function setTabs()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        
        parent::setTabs();
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_seqc.svg"));
        $tpl->setTitle($lng->txt("sahs_chapter") . ": " . $this->node_object->getTitle());
    }
    
    /**
    * Get Node Type
    */
    public function getType()
    {
        return "seqc";
    }
}
