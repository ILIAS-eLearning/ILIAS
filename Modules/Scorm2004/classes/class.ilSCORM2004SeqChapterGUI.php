<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilSCORM2004ChapterGUI
 *
 * User Interface for Scorm 2004 Chapter Nodes
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilSCORM2004SeqChapterGUI: ilMDEditorGUI, ilNoteGUI
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
