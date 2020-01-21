<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilLPListOfProgress
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilLPListOfProgressGUI: ilLPProgressTableGUI
*
* @package ilias-tracking
*
*/
class ilLPListOfProgressGUI extends ilLearningProgressBaseGUI
{
    public $tracked_user = null;
    public $details_id = 0;
    public $details_type = '';
    public $details_mode = 0;

    public function __construct($a_mode, $a_ref_id, $a_user_id = 0)
    {
        parent::__construct($a_mode, $a_ref_id, $a_user_id);
        $this->__initUser($a_user_id);
        
        // Set item id for details
        $this->__initDetails((int) $_GET['details_id']);
        $this->ctrl->saveParameter($this, 'details_id', $_REQUEST['details_id']);
    }
        

    /**
     * execute command
     */
    public function executeCommand()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        $this->ctrl->setReturn($this, "show");
        $this->ctrl->saveParameter($this, 'user_id', $this->getUserId());
        switch ($this->ctrl->getNextClass()) {
            case 'illpprogresstablegui':
                include_once './Services/Tracking/classes/repository_statistics/class.ilLPProgressTableGUI.php';
                $table_gui = new ilLPProgressTableGUI($this, "", $this->tracked_user);
                $this->ctrl->setReturn($this, 'show');
                $this->ctrl->forwardCommand($table_gui);
                break;

            default:
                $cmd = $this->__getDefaultCommand();
                $this->$cmd();

        }
        return true;
    }

    public function show()
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        switch ($this->getMode()) {
            // Show only detail of current repository item if called from repository
            case self::LP_CONTEXT_REPOSITORY:
                $this->__initDetails($this->getRefId());
                return $this->details();

            case self::LP_CONTEXT_USER_FOLDER:
            case self::LP_CONTEXT_ORG_UNIT:
                // if called from user folder obj_id is id of current user
                $this->__initUser($this->getUserId());
                break;
        }

        // not called from repository
        $this->__showProgressList();
    }

    /**
     *
     */
    protected function saveProgress()
    {
        $info = new ilInfoScreenGUI($this);
        $info->setContextRefId((int) $this->ref_id);
        $info->setContextObjId((int) $this->details_obj_id);
        $info->setContentObjType((string) $this->obj_type);
        $info->saveProgress(false);
        $this->ctrl->redirect($this);
    }

    public function details()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        /**
         * @var $ilAccess ilAccessHandler
         */

        // Show back button to crs if called from crs. Otherwise if called from personal desktop or administration
        // show back to list
        if ((int) $_GET['crs_id']) {
            $this->ctrl->setParameter($this, 'details_id', (int) $_GET['crs_id']);
            
            $ilToolbar->addButton(
                $this->lng->txt('trac_view_crs'),
                $this->ctrl->getLinkTarget($this, 'details')
            );
        } elseif ($this->getMode() == self::LP_CONTEXT_PERSONAL_DESKTOP or
               $this->getMode() == self::LP_CONTEXT_ADMINISTRATION or
               $this->getMode() == self::LP_CONTEXT_USER_FOLDER) {
            $ilToolbar->addButton(
                $this->lng->txt('trac_view_list'),
                $this->ctrl->getLinkTarget($this, 'show')
            );
        }

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lp_progress_container.html', 'Services/Tracking');

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        $info->setContextRefId((int) $this->details_id);
        $info->setContextObjId((int) $this->details_obj_id);
        $info->setContentObjType((string) $this->obj_type);
        $info->enableLearningProgress(true);
        $info->setFormAction($ilCtrl->getFormAction($this));
        $this->__appendUserInfo($info, $this->tracked_user);
        $this->__appendLPDetails($info, $this->details_obj_id, $this->tracked_user->getId());
        $this->__showObjectDetails($info, $this->details_obj_id, false);
        
        // Finally set template variable
        $this->tpl->setVariable("LM_INFO", $info->getHTML());
        
        include_once './Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($this->details_obj_id);
        $collection = $olp->getCollectionInstance();
        $obj_ids = array();
        if ($collection) {
            foreach ($collection->getItems() as $item_id) {
                if ($collection instanceof ilLPCollectionOfRepositoryObjects) {
                    $obj_id = ilObject::_lookupObjectId($item_id);
                    if ($ilAccess->checkAccessOfUser($this->tracked_user->getId(), 'visible', '', $item_id)) {
                        $obj_ids[$obj_id] = array( $item_id );
                    }
                } else {
                    $obj_ids[] = $item_id;
                }
            }
        }
        
        // #15247
        if (count($obj_ids) > 0) {
            // seems obsolete
            include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
            $personal_only = !ilLearningProgressAccess::checkPermission('read_learning_progress', $this->getRefId());

            include_once("./Services/Tracking/classes/repository_statistics/class.ilLPProgressTableGUI.php");
            $lp_table = new ilLPProgressTableGUI($this, "details", $this->tracked_user, $obj_ids, true, $this->details_mode, $personal_only, $this->details_obj_id, $this->details_id);
            $this->tpl->setVariable("LP_OBJECTS", $lp_table->getHTML());
        }
        
        $this->tpl->setVariable("LEGEND", $this->__getLegendHTML());
    }

    public function __showProgressList()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lp_list_progress.html', 'Services/Tracking');
        
        // User info
        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        $info->setFormAction($ilCtrl->getFormAction($this));
        
        if ($this->__appendUserInfo($info, $this->tracked_user)) {
            $this->tpl->setCurrentBlock("info_user");
            $this->tpl->setVariable("USER_INFO", $info->getHTML());
            $this->tpl->parseCurrentBlock();
        }

        include_once("./Services/Tracking/classes/repository_statistics/class.ilLPProgressTableGUI.php");
        $lp_table = new ilLPProgressTableGUI($this, "", $this->tracked_user, null, false, null, false, null, null, $this->getMode());
        $this->tpl->setVariable("LP_OBJECTS", $lp_table->getHTML());

        $this->tpl->setVariable("LEGEND", $this->__getLegendHTML());
    }

    public function __initUser($a_usr_id = 0)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $rbacreview = $DIC['rbacreview'];
        $rbacsystem = $DIC['rbacsystem'];

        if ($_POST['user_id']) {
            $a_usr_id = $_POST['user_id'];
            $this->ctrl->setParameter($this, 'user_id', $_POST['user_id']);
        }

        if ($a_usr_id) {
            $this->tracked_user = ilObjectFactory::getInstanceByObjId($a_usr_id);
        } else {
            $this->tracked_user = $ilUser;
        }
        
        // #8762: see ilObjUserGUI->getTabs()
        if ($this->mode == self::LP_CONTEXT_USER_FOLDER && $rbacsystem->checkAccess('read', $this->ref_id)) {
            return true;
        }

        if ($this->mode == self::LP_CONTEXT_ORG_UNIT && ilObjOrgUnitAccess::_checkAccessToUserLearningProgress($this->ref_id, $a_usr_id)) {
            return true;
        }

        // Check access
        if (!$rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID)) {
            $this->tracked_user = $ilUser;
        }
        
        return true;
    }

    public function __initDetails($a_details_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        if (!$a_details_id) {
            $a_details_id = $this->getRefId();
        }
        if ($a_details_id) {
            $ref_ids = ilObject::_getAllReferences($a_details_id);
            
            $this->details_id = $a_details_id;
            $this->details_obj_id = $ilObjDataCache->lookupObjId($this->details_id);
            $this->details_type = $ilObjDataCache->lookupType($this->details_obj_id);
                        
            include_once 'Services/Object/classes/class.ilObjectLP.php';
            $olp = ilObjectLP::getInstance($this->details_obj_id);
            $this->details_mode = $olp->getCurrentMode();
        }
    }
}
