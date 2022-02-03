<?php declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjUserTrackingGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @ilCtrl_Calls ilLPListOfObjectsGUI: ilUserFilterGUI, ilTrUserObjectsPropsTableGUI, ilTrSummaryTableGUI, ilTrObjectUsersPropsTableGUI, ilTrMatrixTableGUI
*
* @package ilias-tracking
*
*/
class ilLPListOfObjectsGUI extends ilLearningProgressBaseGUI
{
    protected int $details_id = 0;
    protected int $details_obj_id = 0;
    protected string $details_type = '';
    protected int $details_mode = 0;

    public function __construct(int $a_mode, int $a_ref_id)
    {
        parent::__construct($a_mode, $a_ref_id);
        $this->__initDetails((int) $_REQUEST['details_id']);
    }

    public function executeCommand() : void
    {
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
    }

    public function updateUser()
    {
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

    public function editUser() : void
    {
        $cancel = '';
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

        $this->tpl->setVariable("ADM_CONTENT", $this->__showEditUser((int) $_GET['user_id'], $parent_id, strlen($cancel) > 0 ? $cancel : null, $sub_id) . "<br />" . $info->getHTML());
    }

    public function details() : void
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lp_loo.html', 'Services/Tracking');

        // Show back button
        if ($this->getMode() == self::LP_CONTEXT_PERSONAL_DESKTOP or
           $this->getMode() == self::LP_CONTEXT_ADMINISTRATION) {

            $this->toolbar->addButton(
                $this->lng->txt('trac_view_list'),
                $this->ctrl->getLinkTarget($this, 'show')
            );
        }

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        $info->setFormAction($this->ctrl->getFormAction($this));
        if ($this->__showObjectDetails($info, $this->details_obj_id)) {
            $this->tpl->setCurrentBlock("info");
            $this->tpl->setVariable("INFO_TABLE", $info->getHTML());
            $this->tpl->parseCurrentBlock();
        }
        $this->__showUsersList();
    }

    public function __showUsersList($a_print_view = false) : void
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
    }

    public function userDetails() : void
    {
        if ($this->isAnonymized()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            return;
        }

        $this->ctrl->setParameter($this, "details_id", $this->details_id);

        $print_view = (bool) $_GET['prt'];
        if (!$print_view) {
            // Show back button
            $this->toolbar->addButton($this->lng->txt('trac_view_list'), $this->ctrl->getLinkTarget($this, 'details'));
        }

        $user_id = (int) $_GET["user_id"];
        $this->ctrl->setParameter($this, "user_id", $user_id);
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lp_loo.html', 'Services/Tracking');

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        $info->setFormAction($this->ctrl->getFormAction($this));
        $this->__showObjectDetails($info, $this->details_obj_id);
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
    }

    public function show() : void
    {
        $this->ctrl->setParameter($this, 'offset', 0);

        // Show only detail of current repository item if called from repository
        switch ($this->getMode()) {
            case self::LP_CONTEXT_REPOSITORY:
                $this->__initDetails($this->getRefId());
                $this->details();
                return;
        }
        $this->__listObjects();
    }

    public function __listObjects() : void
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lp_list_objects.html', 'Services/Tracking');

        include_once("./Services/Tracking/classes/repository_statistics/class.ilTrSummaryTableGUI.php");
        $lp_table = new ilTrSummaryTableGUI($this, "", ROOT_FOLDER_ID);
        
        $this->tpl->setVariable("LP_OBJECTS", $lp_table->getHTML());
        $this->tpl->setVariable('LEGEND', $this->__getLegendHTML());
    }

    public function __initDetails(int $a_details_id) : void
    {
        if (!$a_details_id) {
            $a_details_id = $this->getRefId();
        }
        if ($a_details_id) {
            $_GET['details_id'] = $a_details_id;
            $this->details_id = $a_details_id;
            $this->details_obj_id = $this->ilObjectDataCache->lookupObjId($this->details_id);
            $this->details_type = $this->ilObjectDataCache->lookupType($this->details_obj_id);
            
            include_once 'Services/Object/classes/class.ilObjectLP.php';
            $olp = ilObjectLP::getInstance($this->details_obj_id);
            $this->details_mode = $olp->getCurrentMode();
        }
    }

    /**
     * Show object-based summarized tracking data
     */
    public function showObjectSummary() : void
    {
        include_once("./Services/Tracking/classes/repository_statistics/class.ilTrSummaryTableGUI.php");
        $table = new ilTrSummaryTableGUI($this, "showObjectSummary", $this->getRefId(), false);
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Show object user matrix
     */
    public function showUserObjectMatrix() : void
    {
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
