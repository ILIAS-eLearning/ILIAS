<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* Page History Table GUI Class
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPageHistoryTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    public function __construct($a_parent_obj, $a_parent_cmd = "")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->setId("ilCOPgHistoryTable");
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($lng->txt("content_page_history"));
        
        $this->addColumn("", "", "1");
        $this->addColumn("", "", "1");
        $this->addColumn($lng->txt("date"), "", "33%");
        $this->addColumn($lng->txt("user"), "", "33%");
        $this->addColumn($lng->txt("action"), "", "33%");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.page_history_row.html", "Services/COPage");
        $this->setDefaultOrderField("sortkey");
        $this->setDefaultOrderDirection("desc");
        $this->addMultiCommand("compareVersion", $lng->txt("cont_page_compare"));
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
    }
    
    /**
    * Should this field be sorted numeric?
    *
    * @return	boolean		numeric ordering; default is false
    */
    public function numericOrdering($a_field)
    {
        if ($a_field == "sortkey") {
            return true;
        }
        return false;
    }

    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        // rollback command
        if ($a_set["nr"] > 0) {
            $ilCtrl->setParameter($this->getParentObject(), "old_nr", $a_set["nr"]);
            $this->tpl->setCurrentBlock("command");
            $this->tpl->setVariable("TXT_COMMAND", $lng->txt("cont_rollback"));
            $this->tpl->setVariable(
                "HREF_COMMAND",
                $ilCtrl->getLinkTarget($this->getParentObject(), "rollbackConfirmation")
            );
            $this->tpl->parseCurrentBlock();
            $ilCtrl->setParameter($this->getParentObject(), "old_nr", "");
        }
        
        if (!$this->rselect) {
            $this->tpl->setVariable("RSELECT", 'checked="checked"');
            $this->rselect = true;
        } elseif (!$this->lselect) {
            $this->tpl->setVariable("LSELECT", 'checked="checked"');
            $this->lselect = true;
        }

        
        $this->tpl->setVariable("NR", $a_set["nr"]);
        $this->tpl->setVariable(
            "TXT_HDATE",
            ilDatePresentation::formatDate(new ilDateTime($a_set["hdate"], IL_CAL_DATETIME))
        );

        $ilCtrl->setParameter($this->getParentObject(), "old_nr", $a_set["nr"]);
        $ilCtrl->setParameter($this->getParentObject(), "history_mode", "1");
        $this->tpl->setVariable(
            "HREF_OLD_PAGE",
            $ilCtrl->getLinkTarget($this->getParentObject(), "preview")
        );
        $ilCtrl->setParameter($this->getParentObject(), "history_mode", "");
            
        if (ilObject::_exists($a_set["user"])) {
            // user name
            $user = ilObjUser::_lookupName($a_set["user"]);
            $login = ilObjUser::_lookupLogin($a_set["user"]);
            //$this->tpl->setVariable("TXT_LINKED_USER",
            //	$user["lastname"].", ".$user["firstname"]." [".$login."]");
                
            // profile link
            include_once("./Services/User/classes/class.ilUserUtil.php");
            $name_pres = ilUserUtil::getNamePresentation($a_set["user"], true, true, $ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCmd()));
            //$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "user", $a_set["user"]);
            //$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "back_url",
            //	rawurlencode($ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCmd())));
            //$this->tpl->setVariable("USER_LINK",
            //	$ilCtrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML"));
            //$img = ilObjUser::_getPersonalPicturePath($a_set["user"], "xxsmall");
            //$this->tpl->setVariable("IMG_USER", $img);
            $this->tpl->setVariable("TXT_USER", $name_pres);
        }
            
        $ilCtrl->setParameter($this->getParentObject(), "old_nr", "");
    }
}
