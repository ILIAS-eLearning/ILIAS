<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
include_once 'Services/Search/classes/class.ilUserFilterGUI.php';

/**
* Class ilObjUserTrackingGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilLPListOfObjectsGUI: ilUserFilterGUI, ilTrUserObjectsPropsTableGUI, ilTrSummaryTableGUI, ilTrObjectUsersPropsTableGUI, ilTrMatrixTableGUI
*
* @package ilias-tracking
*
*/
class ilLPListOfObjectsGUI extends ilLearningProgressBaseGUI
{
    public $details_id = 0;
    public $details_type = '';
    public $details_mode = 0;

    public function __construct($a_mode, $a_ref_id)
    {
        parent::__construct($a_mode, $a_ref_id);
        
        // Set item id for details
        $this->__initDetails((int) $_REQUEST['details_id']);
    }
    /**
    * execute command
    */
    public function executeCommand()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        $this->ctrl->setReturn($this, "");

        switch ($this->ctrl->getNextClass()) {
            case 'iltruserobjectspropstablegui':
                $user_id = (int) $_GET["user_id"];
                $this->ctrl->setParameter($this, "user_id", $user_id);

                $this->ctrl->setParameter($this, "details_id", $this->details_id);

                include_once("./Services/Tracking/classes/repository_statistics/class.ilTrUserObjectsPropsTableGUI.php");
                $table_gui = new ilTrUserObjectsPropsTableGUI(
                    $this,
                    "userDetails",
                    $user_id,
                    $this->details_obj_id,
                    $this->details_id
                );
                $this->ctrl->forwardCommand($table_gui);
                break;
            
            case 'iltrsummarytablegui':
                $cmd = "showObjectSummary";
                if (!$this->details_id) {
                    $this->details_id = ROOT_FOLDER_ID;
                    $cmd = "show";
                }
                include_once './Services/Tracking/classes/repository_statistics/class.ilTrSummaryTableGUI.php';
                $table_gui = new ilTrSummaryTableGUI($this, $cmd, $this->details_id);
                $this->ctrl->forwardCommand($table_gui);
                break;

            case 'iltrmatrixtablegui':
                include_once './Services/Tracking/classes/repository_statistics/class.ilTrMatrixTableGUI.php';
                $table_gui = new ilTrMatrixTableGUI($this, "showUserObjectMatrix", $this->details_id);
                $this->ctrl->forwardCommand($table_gui);
                break;

            case 'iltrobjectuserspropstablegui':
                $this->ctrl->setParameter($this, "details_id", $this->details_id);
            
                include_once './Services/Tracking/classes/repository_statistics/class.ilTrObjectUsersPropsTableGUI.php';
                $table_gui = new ilTrObjectUsersPropsTableGUI($this, "details", $this->details_obj_id, $this->details_id);
                $this->ctrl->forwardCommand($table_gui);
                break;

            default:
                $cmd = $this->__getDefaultCommand();
                $this->$cmd();
        }

        return true;
    }

    public function updateUser()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        
        if (isset($_GET["userdetails_id"])) {
            $parent = $this->details_id;
            $this->__initDetails((int) $_GET["userdetails_id"]);
        }
        
        include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
        if (!ilLearningProgressAccess::checkPermission('edit_learning_progress', $this->details_id)) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->returnToParent($this);
        }
        
        $this->__updateUser($_REQUEST['user_id'], $this->details_obj_id);
        ilUtil::sendSuccess($this->lng->txt('trac_update_edit_user'), true);
                        
        $this->ctrl->setParameter($this, "details_id", $this->details_id); // #15043
        
        // #14993
        if (!isset($_GET["userdetails_id"])) {
            $this->ctrl->redirect($this, "details");
        } else {
            $this->ctrl->setParameter($this, "userdetails_id", (int) $_GET["userdetails_id"]);
            $this->ctrl->redirect($this, "userdetails");
        }
    }

    public function editUser()
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $rbacsystem = $DIC['rbacsystem'];

        $parent_id = $this->details_id;
        if (isset($_GET["userdetails_id"])) {
            $this->__initDetails((int) $_GET["userdetails_id"]);
            $sub_id = $this->details_id;
            $cancel = "userdetails";
        } else {
            $sub_id = null;
            $cancel = "details";
        }
        
        include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
        if (!ilLearningProgressAccess::checkPermission('edit_learning_progress', $this->details_id)) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->returnToParent($this);
        }

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        $info->setFormAction($this->ctrl->getFormAction($this));
        $this->__showObjectDetails($info, $this->details_obj_id);
        $this->__appendUserInfo($info, (int) $_GET['user_id']);
        // $this->__appendLPDetails($info,$this->details_obj_id,(int)$_GET['user_id']);

        $this->tpl->setVariable("ADM_CONTENT", $this->__showEditUser((int) $_GET['user_id'], $parent_id, $cancel, $sub_id) . "<br />" . $info->getHTML());
    }

    public function details()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lp_loo.html', 'Services/Tracking');

        // Show back button
        if ($this->getMode() == self::LP_CONTEXT_PERSONAL_DESKTOP or
           $this->getMode() == self::LP_CONTEXT_ADMINISTRATION) {
            $print_view = false;
            
            $ilToolbar->addButton(
                $this->lng->txt('trac_view_list'),
                $this->ctrl->getLinkTarget($this, 'show')
            );
        } else {
            /*
            $print_view = (bool)$_GET['prt'];
            if(!$print_view)
            {
                $ilToolbar->setFormAction($this->ctrl->getFormAction($this));
                $this->ctrl->setParameter($this, 'prt', 1);
                $ilToolbar->addButton($this->lng->txt('print_view'),$this->ctrl->getLinkTarget($this,'details'), '_blank');
                $this->ctrl->setParameter($this, 'prt', '');
            }
            */
        }

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        $info->setFormAction($this->ctrl->getFormAction($this));
        if ($this->__showObjectDetails($info, $this->details_obj_id)) {
            $this->tpl->setCurrentBlock("info");
            $this->tpl->setVariable("INFO_TABLE", $info->getHTML());
            $this->tpl->parseCurrentBlock();
        }

        $this->__showUsersList($print_view);
    }

    public function __showUsersList($a_print_view = false)
    {
        if ($this->isAnonymized()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            return;
        }

        $this->ctrl->setParameter($this, "details_id", $this->details_id);

        include_once "Services/Tracking/classes/repository_statistics/class.ilTrObjectUsersPropsTableGUI.php";
        $gui = new ilTrObjectUsersPropsTableGUI($this, "details", $this->details_obj_id, $this->details_id, $a_print_view);
        
        $this->tpl->setVariable("LP_OBJECTS", $gui->getHTML());
        $this->tpl->setVariable("LEGEND", $this->__getLegendHTML());

        /*
        if($a_print_view)
        {
            echo $this->tpl->get("DEFAULT", false, false, false, false, false, false);
            exit();
        }
        */
    }

    public function userDetails()
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilToolbar = $DIC['ilToolbar'];

        if ($this->isAnonymized()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            return;
        }

        $this->ctrl->setParameter($this, "details_id", $this->details_id);

        $print_view = (bool) $_GET['prt'];
        if (!$print_view) {
            // Show back button
            $ilToolbar->addButton($this->lng->txt('trac_view_list'), $this->ctrl->getLinkTarget($this, 'details'));
        }

        $user_id = (int) $_GET["user_id"];
        $this->ctrl->setParameter($this, "user_id", $user_id);

        /*
        if(!$print_view)
        {
            $this->ctrl->setParameter($this, 'prt', 1);
            $ilToolbar->addButton($this->lng->txt('print_view'),$this->ctrl->getLinkTarget($this,'userDetails'), '_blank');
            $this->ctrl->setParameter($this, 'prt', '');
        };
        */
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lp_loo.html', 'Services/Tracking');

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        $info->setFormAction($this->ctrl->getFormAction($this));
        $this->__showObjectDetails($info, $this->details_obj_id);
        $this->__appendUserInfo($info, $user_id);
        // $this->__appendLPDetails($info,$this->details_obj_id,$user_id);
        $this->tpl->setVariable("INFO_TABLE", $info->getHTML());

        include_once("./Services/Tracking/classes/repository_statistics/class.ilTrUserObjectsPropsTableGUI.php");
        $table = new ilTrUserObjectsPropsTableGUI(
            $this,
            "userDetails",
            $user_id,
            $this->details_obj_id,
            $this->details_id,
            $print_view
        );
        $this->tpl->setVariable('LP_OBJECTS', $table->getHTML());
        $this->tpl->setVariable('LEGEND', $this->__getLegendHTML());

        /*
        if($print_view)
        {
            echo $this->tpl->get("DEFAULT", false, false, false, false, false, false);
            exit();
        }
        */
    }

    public function show()
    {
        // Clear table offset
        $this->ctrl->saveParameter($this, 'offset', 0);

        // Show only detail of current repository item if called from repository
        switch ($this->getMode()) {
            case self::LP_CONTEXT_REPOSITORY:
                $this->__initDetails($this->getRefId());
                $this->details();
                return true;
        }

        $this->__listObjects();
    }

    public function __listObjects()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lp_list_objects.html', 'Services/Tracking');

        include_once("./Services/Tracking/classes/repository_statistics/class.ilTrSummaryTableGUI.php");
        $lp_table = new ilTrSummaryTableGUI($this, "", ROOT_FOLDER_ID);
        
        $this->tpl->setVariable("LP_OBJECTS", $lp_table->getHTML());
        $this->tpl->setVariable('LEGEND', $this->__getLegendHTML());
    }

    public function __initDetails($a_details_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        if (!$a_details_id) {
            $a_details_id = $this->getRefId();
        }
        if ($a_details_id) {
            $_GET['details_id'] = $a_details_id;
            $this->details_id = $a_details_id;
            $this->details_obj_id = $ilObjDataCache->lookupObjId($this->details_id);
            $this->details_type = $ilObjDataCache->lookupType($this->details_obj_id);
            
            include_once 'Services/Object/classes/class.ilObjectLP.php';
            $olp = ilObjectLP::getInstance($this->details_obj_id);
            $this->details_mode = $olp->getCurrentMode();
        }
    }

    /**
     * Show object-based summarized tracking data
     */
    public function showObjectSummary()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $ilToolbar = $DIC['ilToolbar'];

        /*
        $print_view = (bool)$_GET['prt'];
        if(!$print_view)
        {
            $ilToolbar->setFormAction($this->ctrl->getFormAction($this));
            $this->ctrl->setParameter($this, 'prt', 1);
            $ilToolbar->addButton($this->lng->txt('print_view'),$this->ctrl->getLinkTarget($this,'showObjectSummary'), '_blank');
            $this->ctrl->setParameter($this, 'prt', '');
        }
        */

        include_once("./Services/Tracking/classes/repository_statistics/class.ilTrSummaryTableGUI.php");
        $table = new ilTrSummaryTableGUI($this, "showObjectSummary", $this->getRefId(), $print_view);
        if (!$print_view) {
            $tpl->setContent($table->getHTML());
        } else {
            $tpl->setVariable("ADM_CONTENT", $table->getHTML());
            echo $tpl->get("DEFAULT", false, false, false, false, false, false);
            exit();
        }
    }

    /**
     * Show object user matrix
     */
    public function showUserObjectMatrix()
    {
        global $DIC;

        $tpl = $DIC['tpl'];

        if ($this->isAnonymized()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            return;
        }
        

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lp_loo.html', 'Services/Tracking');

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        $info->setFormAction($this->ctrl->getFormAction($this));
        if ($this->__showObjectDetails($info, $this->details_obj_id)) {
            $this->tpl->setCurrentBlock("info");
            $this->tpl->setVariable("INFO_TABLE", $info->getHTML());
            $this->tpl->parseCurrentBlock();
        }

        include_once("./Services/Tracking/classes/repository_statistics/class.ilTrMatrixTableGUI.php");
        $table = new ilTrMatrixTableGUI($this, "showUserObjectMatrix", $this->getRefId());
        $this->tpl->setVariable('LP_OBJECTS', $table->getHTML());
        $this->tpl->setVariable('LEGEND', $this->__getLegendHTML());
    }
}
