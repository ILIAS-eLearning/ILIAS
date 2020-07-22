<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
* Table GUI for Bookmark management
*
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
* @ingroup ServicesBookmarks
*
*/
class ilBookmarkAdministrationTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
    * @param object	the object to which this table refers
    */
    
    
    
    public function __construct($a_ref)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->help = $DIC["ilHelp"];
        $this->user = $DIC->user();
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilHelp = $DIC["ilHelp"];
        
        
        $ilHelp->setScreenIdComponent("bookm");
        
        parent::__construct($a_ref);
        
        //$this->setTitle($lng->txt('bookmarks'));
        
        $this->setRowTemplate('tpl.bookmark_administration_row.html', 'Services/Bookmarks');
        $this->addColumn('', '', '3%', true);
        $this->addColumn($lng->txt('type'), '', '3%');
        $this->addColumn($lng->txt('title'), 'title', '84%');
        $this->addColumn($lng->txt('actions'), '', '10%');
        
        $hash = ($ilUser->prefs["screen_reader_optimization"])
            ? "bookmark_top"
            : "";
 
        $this->setFormAction($ilCtrl->getFormAction($a_ref, $hash));
        $this->setSelectAllCheckbox('bm_id');
                
        $this->addMultiCommand('export', $lng->txt('export'));
        $this->addMultiCommand('sendmail', $lng->txt('bkm_sendmail'));
        $this->addMultiCommand('move', $lng->txt('move'));
        $this->addMultiCommand('delete', $lng->txt('delete'));
        
        $this->setTopAnchor("bookmark_top");
        
        $ilCtrl->saveParameter($this->getParentObject(), 'bmf_id');
    }

    public function fillRow($a_data)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        
        include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $current_selection_list = new ilAdvancedSelectionListGUI();
        $current_selection_list->setListTitle($this->lng->txt("actions"));
        $current_selection_list->setId("act_" . $a_data['obj_id']);
    
        
        $this->tpl->setVariable("VAL_ID", $a_data["obj_id"]);
        
        // edit link
        $edit_link = '';
        $delete_link = '';
        $sendmail_link = '';
        $export_link = '';
        
        if ($a_data["type"] != "parent") {
            $hash = ($ilUser->prefs["screen_reader_optimization"])
                ? "bookmark_top"
                : "";

            $ilCtrl->setParameter($this->parent_obj, "bmf_id", $this->parent_obj->id);
            $ilCtrl->setParameter($this->parent_obj, "obj_id", $a_data["obj_id"]);
            $edit_link = ($a_data["type"] == "bmf")
                ? $ilCtrl->getLinkTarget($this->parent_obj, "editFormBookmarkFolder", $hash)
                : $ilCtrl->getLinkTarget($this->parent_obj, "editFormBookmark", $hash);
            
            $ilCtrl->clearParameters($this->parent_obj);
            $ilCtrl->setParameter($this->parent_obj, "bm_id", $a_data['obj_id']);
            $delete_link = $ilCtrl->getLinkTarget($this->parent_obj, 'delete', $hash);
            $sendmail_link = $ilCtrl->getLinkTarget($this->parent_obj, 'sendmail', $hash);
            $export_link = $ilCtrl->getLinkTarget($this->parent_obj, 'export', $hash);
        }

        if ($edit_link) {
            $current_selection_list->addItem($this->lng->txt('edit'), '', $edit_link);
        }
        
        if ($delete_link) {
            $current_selection_list->addItem($this->lng->txt('delete'), '', $delete_link);
        }

        if ($export_link) {
            $current_selection_list->addItem($this->lng->txt('export'), '', $export_link);
        }

        if ($sendmail_link) {
            $current_selection_list->addItem($this->lng->txt('bkm_sendmail'), '', $sendmail_link);
        }
            
        $this->tpl->setVariable("COMMAND_SELECTION_LIST", $current_selection_list->getHTML());
            
        // icon
        $img_type = ($a_data["type"] == "bmf" || $a_data["type"] == "parent") ? "bmf" : $a_data["type"]; // #10556
        $val = ilUtil::getImagePath("icon_" . $img_type . ".svg");
        $this->tpl->setVariable("VAL_ICON", $val);
        $this->tpl->setVariable("VAL_ICON_ALT", $lng->txt("icon") . " " . $lng->txt($a_data["type"]));
        
        // folder links
        if ($a_data["type"] == "bmf" || $a_data["type"] == "parent") {
            $this->tpl->setVariable("VAL_BMF_TITLE", $a_data["title"]);
            $ilCtrl->setParameter($this->parent_obj, "bmf_id", $a_data["obj_id"]);
            $this->tpl->setVariable("VAL_BMF_TARGET", $ilCtrl->getLinkTarget($this->parent_obj));
        //$this->tpl->setVariable("FRAME_TARGET_BMF", ilFrameTargetInfo::_getFrame("MainContent"));
        } else {
            $this->tpl->setVariable("VAL_BM_TITLE", $a_data["title"]);
            $this->tpl->setVariable("VAL_BM_TARGET", ilUtil::secureUrl($a_data["target"]));
            $this->tpl->setVariable("VAL_BM_REL", 'noopener');
            $this->tpl->setVariable("VAL_BM_DESCRIPTION", $a_data["description"]);
            $this->tpl->setVariable("FRAME_TARGET_BM", ilFrameTargetInfo::_getFrame("ExternalContent"));
        }
        $ilCtrl->clearParameters($this->parent_obj);
    }
}
