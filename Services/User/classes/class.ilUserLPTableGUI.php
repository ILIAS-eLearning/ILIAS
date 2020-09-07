<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* Learning progress account list for user administration
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ServicesUser
*/
class ilUserLPTableGUI extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];
        
        $this->ref_id = $a_ref_id;
        $this->setId("admusrlp");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        // $this->setTitle($this->lng->txt("obj_usr"));
        
        $this->addColumn($this->lng->txt("login"), "login");
        $this->addColumn($this->lng->txt("firstname"), "firstname");
        $this->addColumn($this->lng->txt("lastname"), "lastname");
        $this->addColumn($this->lng->txt("online_time"), "online_time");
        $this->addColumn($this->lng->txt("last_login"), "last_login");
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
    
        $this->setFormAction($ilCtrl->getFormAction($this->parent_obj));
        
        $this->setRowTemplate("tpl.user_list_lp_row.html", "Services/User");
        
        $this->setDefaultOrderField("login");
        $this->setDefaultOrderDirection("asc");
        
        $this->setExportFormats(array(self::EXPORT_EXCEL));

        $this->getItems();
    }
    
    public function getItems()
    {
        $this->determineOffsetAndOrder();
            
        include_once("./Services/User/classes/class.ilUserQuery.php");
        $usr_data = ilUserQuery::getUserListData(
            ilUtil::stripSlashes($this->getOrderField()),
            ilUtil::stripSlashes($this->getOrderDirection()),
            ilUtil::stripSlashes($this->getOffset()),
            ilUtil::stripSlashes($this->getLimit()),
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            array("online_time"),
            null,
            null
        );
            
        if (count($usr_data["set"]) == 0 && $this->getOffset() > 0) {
            $this->resetOffset();
            $usr_data = ilUserQuery::getUserListData(
                ilUtil::stripSlashes($this->getOrderField()),
                ilUtil::stripSlashes($this->getOrderDirection()),
                ilUtil::stripSlashes($this->getOffset()),
                ilUtil::stripSlashes($this->getLimit()),
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                array("online_time"),
                null,
                null
            );
        }

        $this->setMaxCount($usr_data["cnt"]);
        $this->setData($usr_data["set"]);
        
        include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';
        $this->lp_active = ilObjUserTracking::_enabledLearningProgress();
    }
    
    protected function fillRow($user)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        if ($this->lp_active) {
            $ilCtrl->setParameterByClass("illearningprogressgui", "ref_id", $this->ref_id);
            $ilCtrl->setParameterByClass("illearningprogressgui", "obj_id", $user["usr_id"]);
            $link = $ilCtrl->getLinkTargetByClass(array("ilobjusergui",'illearningprogressgui'), "");

            $this->tpl->setCurrentBlock("login_link");
            $this->tpl->setVariable("HREF_LOGIN", $link);
            $this->tpl->setVariable("VAL_LOGIN", $user["login"]);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock("login_plain");
            $this->tpl->setVariable("VAL_LOGIN_PLAIN", $user["login"]);
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("VAL_FIRSTNAME", $user["firstname"]);
        $this->tpl->setVariable("VAL_LASTNAME", $user["lastname"]);
        $this->tpl->setVariable(
            "VAL_ONLINE_TIME",
            self::secondsToShortString($user["online_time"])
        );
        $this->tpl->setVariable(
            "VAL_LAST_LOGIN",
            ilDatePresentation::formatDate(new ilDateTime($user["last_login"], IL_CAL_DATETIME))
        );
    }
    
    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $a_set)
    {
        $a_excel->setCell($a_row, 0, $a_set["login"]);
        $a_excel->setCell($a_row, 1, $a_set["firstname"]);
        $a_excel->setCell($a_row, 2, $a_set["lastname"]);
        $a_excel->setCell(
            $a_row,
            3,
            self::secondsToShortString($a_set["online_time"])
        );
        $a_excel->setCell($a_row, 4, new ilDateTime($a_set["last_login"], IL_CAL_DATETIME));
    }
    
    /**
     * converts seconds to string:
     * Long: 7 days 4 hour(s) ...
     *
     * @param	string	datetime
     * @return	integer	unix timestamp
     */
    protected static function secondsToShortString($seconds)
    {
        global $DIC;

        $lng = $DIC['lng'];

        $seconds = $seconds ? $seconds : 0;

        global $DIC;

        $lng = $DIC['lng'];

        $days = floor($seconds / 86400);
        $rest = $seconds % 86400;

        $hours = floor($rest / 3600);
        $rest = $rest % 3600;

        $minutes = floor($rest / 60);
        $rest = $rest % 60;

        return sprintf("%02d:%02d:%02d:%02d", $days, $hours, $minutes, $rest);
    }
}
