<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';

/**
* Class ilObjUserTrackingGUI
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ilCtrl_Calls ilLearningProgressGUI: ilLPListOfObjectsGUI, ilLPListOfSettingsGUI, ilLPListOfProgressGUI
* @ilCtrl_Calls ilLearningProgressGUI: ilLPObjectStatisticsGUI
*
*/
class ilLearningProgressGUI extends ilLearningProgressBaseGUI
{
    /**
    * execute command
    */
    public function executeCommand()
    {
        global $DIC;

        $ilHelp = $DIC['ilHelp'];
        $ilAccess = $DIC['ilAccess'];
        
        $this->ctrl->setReturn($this, "");

        // E.g personal desktop mode needs locator header icon ...
        $this->__buildHeader();
        switch ($this->__getNextClass()) {
            case 'illplistofprogressgui':
                include_once 'Services/Tracking/classes/repository_statistics/class.ilLPListOfProgressGUI.php';
                
                $ilHelp->setScreenIdComponent("lp_" . ilObject::_lookupType($this->getRefId(), true));

                $this->__setSubTabs(self::LP_ACTIVE_PROGRESS);
                $this->__setCmdClass('illplistofprogressgui');
                $lop_gui = new ilLPListOfProgressGUI($this->getMode(), $this->getRefId(), $this->getUserId());
                $this->ctrl->forwardCommand($lop_gui);
                break;

            case 'illplistofobjectsgui':
                include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
                if ($this->getRefId() &&
                    !ilLearningProgressAccess::checkPermission('read_learning_progress', $this->getRefId())) {
                    return;
                }
                
                include_once 'Services/Tracking/classes/repository_statistics/class.ilLPListOfObjectsGUI.php';
                if (stristr($this->ctrl->getCmd(), "matrix")) {
                    $this->__setSubTabs(self::LP_ACTIVE_MATRIX);
                } elseif (stristr($this->ctrl->getCmd(), "summary")) {
                    $this->__setSubTabs(self::LP_ACTIVE_SUMMARY);
                } else {
                    $this->__setSubTabs(self::LP_ACTIVE_OBJECTS);
                }
                $loo_gui = new ilLPListOfObjectsGUI($this->getMode(), $this->getRefId());
                $this->__setCmdClass('illplistofobjectsgui');
                $this->ctrl->forwardCommand($loo_gui);
                break;

            case 'illplistofsettingsgui':
                include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
                if ($this->getRefId() &&
                    !ilLearningProgressAccess::checkPermission('edit_learning_progress', $this->getRefId())) {
                    return;
                }
                
                include_once 'Services/Tracking/classes/repository_statistics/class.ilLPListOfSettingsGUI.php';

                $this->__setSubTabs(self::LP_ACTIVE_SETTINGS);
                $los_gui = new ilLPListOfSettingsGUI($this->getMode(), $this->getRefId());
                $this->__setCmdClass('illplistofsettingsgui');
                $this->ctrl->forwardCommand($los_gui);
                break;
            
            case 'illpobjectstatisticsgui':
                include_once 'Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsGUI.php';
                if (stristr($this->ctrl->getCmd(), "access")) {
                    $this->__setSubTabs(self::LP_ACTIVE_OBJSTATACCESS);
                } elseif (stristr($this->ctrl->getCmd(), "types")) {
                    $this->__setSubTabs(self::LP_ACTIVE_OBJSTATTYPES);
                } elseif (stristr($this->ctrl->getCmd(), "daily")) {
                    $this->__setSubTabs(self::LP_ACTIVE_OBJSTATDAILY);
                } else {
                    $this->__setSubTabs(self::LP_ACTIVE_OBJSTATADMIN);
                }
                $this->__setCmdClass('illpobjectstatisticsgui');
                $ost_gui = new ilLPObjectStatisticsGUI($this->getMode(), $this->getRefId());
                $this->ctrl->forwardCommand($ost_gui);
                break;
            
            default:
                $cmd = $this->ctrl->getCmd();
                if (!$cmd) {
                    return;
                }
                $this->$cmd();
                $this->tpl->show(true);
                break;
        }

        // E.G personal desktop mode needs $tpl->show();
        $this->__buildFooter();


        return true;
    }

    public function __setCmdClass($a_class)
    {
        // If cmd class == 'illearningprogressgui' the cmd class is set to the the new forwarded class
        // otherwise e.g illplistofprogressgui tries to forward (back) to illearningprogressgui.

        if ($this->ctrl->getCmdClass() == strtolower(get_class($this))) {
            $this->ctrl->setCmdClass(strtolower($a_class));
        }
        return true;
    }

    public function __getNextClass()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilUser = $DIC['ilUser'];
        
        // #9857
        if (!ilObjUserTracking::_enabledLearningProgress()) {
            return;
        }

        if (strlen($next_class = $this->ctrl->getNextClass())) {
            if ($this->getMode() == self::LP_CONTEXT_PERSONAL_DESKTOP) {
                $_SESSION['il_lp_history'] = $next_class;
            }
            return $next_class;
        }
        switch ($this->getMode()) {
            case self::LP_CONTEXT_ADMINISTRATION:
                return 'illplistofobjectsgui';

            case self::LP_CONTEXT_REPOSITORY:
                $cmd = $this->ctrl->getCmd();
                if (in_array($cmd, array("editManual", "updatemanual", "showtlt"))) {
                    return "";
                }
                
                // #12771
                include_once './Services/Object/classes/class.ilObjectLP.php';
                $olp = ilObjectLP::getInstance(ilObject::_lookupObjId($this->getRefId()));
                if (!$olp->isActive()) {
                    include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
                    if (!($olp instanceof ilPluginLP) &&
                        ilLearningProgressAccess::checkPermission('edit_learning_progress', $this->getRefId())) {
                        return 'illplistofsettingsgui';
                    } else {
                        return '';
                    }
                }
                                            
                include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
                if (!$this->anonymized &&
                    ilLearningProgressAccess::checkPermission('read_learning_progress', $this->getRefId())) {
                    return 'illplistofobjectsgui';
                }
                if (
                    ilLearningProgressAccess::checkPermission('edit_learning_progress', $this->getRefId())) {
                    return 'illplistofsettingsgui';
                }
                return 'illplistofprogressgui';

            case self::LP_CONTEXT_PERSONAL_DESKTOP:
                                
                include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
                $has_edit = ilObjUserTracking::_hasLearningProgressOtherUsers();
                $has_personal = ilObjUserTracking::_hasLearningProgressLearner();
                
                if ($has_edit || $has_personal) {
                    // default (#10928)
                    $tgt = null;
                    if ($has_personal) {
                        $tgt = 'illplistofprogressgui';
                    } elseif ($has_edit) {
                        $tgt = 'illplistofobjectsgui';
                    }

                    // validate session
                    switch ($_SESSION['il_lp_history']) {
                        case 'illplistofobjectsgui':
                            if (!$has_edit) {
                                $_SESSION['il_lp_history'] = null;
                            }
                            break;

                        case 'illplistofprogressgui':
                            if (!$has_personal) {
                                $_SESSION['il_lp_history'] = null;
                            }
                            break;
                    }

                    if ($_SESSION['il_lp_history']) {
                        return $_SESSION['il_lp_history'];
                    } elseif ($tgt) {
                        return $tgt;
                    }
                }
                
                // should not happen
                ilUtil::redirect("ilias.php?baseClass=ilPersonalDesktopGUI");
                
                // no break
            case self::LP_CONTEXT_USER_FOLDER:
            case self::LP_CONTEXT_ORG_UNIT:
                if (ilObjUserTracking::_enabledUserRelatedData()) {
                    return 'illplistofprogressgui';
                }
                break;
        }
    }
    
    /**
     * Show progress screen for "edit manual"
     * @global type $tpl
     */
    protected function editManual()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        
        if (ilLearningProgressAccess::checkAccess($this->getRefId())) {
            $olp = ilObjectLP::getInstance(ilObject::_lookupObjId($this->getRefId()));
            if ($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_MANUAL) {
                $form = $this->initCollectionManualForm();
                $tpl->setContent($form->getHTML());
            }
        }
    }
    
    protected function initCollectionManualForm()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "updatemanual"));
        $form->setTitle($lng->txt("learning_progress"));
        $form->setDescription($lng->txt("trac_collection_manual_learner_info"));
        
        $coll_items = array();
        
        include_once './Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($this->getObjId());
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $coll_items = $collection->getItems();
            $possible_items = $collection->getPossibleItems($this->getRefId()); // for titles
            
            switch (ilObject::_lookupType($this->getObjId())) {
                case "lm":
                    $subitem_title = $lng->txt("objs_st");
                    $subitem_info = $lng->txt("trac_collection_manual_learner_lm_info");
                    break;
            }
        }
        
        include_once "Services/Tracking/classes/class.ilLPStatusFactory.php";
        $class = ilLPStatusFactory::_getClassById($this->getObjId(), ilLPObjSettings::LP_MODE_COLLECTION_MANUAL);
        $lp_data = $class::_getObjectStatus($this->getObjId(), $this->usr_id);
                
        $grp = new ilCheckboxGroupInputGUI($subitem_title, "sids");
        $grp->setInfo($subitem_info);
        $form->addItem($grp);
        
        // #14994 - using possible items for proper sorting
        
        $completed = array();
        foreach (array_keys($possible_items) as $item_id) {
            if (!in_array($item_id, $coll_items)) {
                continue;
            }
            
            $info = null;
            $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
            
            if (isset($lp_data[$item_id])) {
                $changed = new ilDateTime($lp_data[$item_id][1], IL_CAL_UNIX);
                $info = $lng->txt("trac_collection_manual_learner_changed_ts") . ": " .
                    ilDatePresentation::formatDate($changed);
                
                if ($lp_data[$item_id][0]) {
                    $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                    $completed[] = $item_id;
                }
            }
            
            $path = ilLearningProgressBaseGUI::_getImagePathForStatus($status);
            $text = ilLearningProgressBaseGUI::_getStatusText($status);
            $icon = ilUtil::img($path, $text);
            
            $opt = new ilCheckboxOption($icon . " " . $possible_items[$item_id]["title"], $item_id);
            if ($info) {
                $opt->setInfo($info);
            }
            $grp->addOption($opt);
        }
        
        if ($completed) {
            $grp->setValue($completed);
        }
        
        $form->addCommandButton("updatemanual", $lng->txt("save"));
        
        return $form;
    }
    
    protected function updateManual()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
        if (ilLearningProgressAccess::checkAccess($this->getRefId())) {
            include_once './Services/Object/classes/class.ilObjectLP.php';
            $olp = ilObjectLP::getInstance(ilObject::_lookupObjId($this->getRefId()));
            if ($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_MANUAL) {
                $form = $this->initCollectionManualForm();
                if ($form->checkInput()) {
                    include_once "Services/Tracking/classes/class.ilLPStatusFactory.php";
                    $class = ilLPStatusFactory::_getClassById($this->getObjId(), ilLPObjSettings::LP_MODE_COLLECTION_MANUAL);
                    $class::_setObjectStatus($this->getObjId(), $this->usr_id, $form->getInput("sids"));
                    
                    ilUtil::sendSuccess($lng->txt("settings_saved"), true);
                }
                
                $ilCtrl->redirect($this, "editManual");
            }
        }
    }
    
    protected function showtlt()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilUser = $DIC['ilUser'];
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "showtlt"));
        $form->setTitle($lng->txt("learning_progress"));
        $form->setDescription($lng->txt("trac_collection_tlt_learner_info"));
        
        $coll_items = array();
        
        include_once './Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($this->getObjId());
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $coll_items = $collection->getItems();
            $possible_items = $collection->getPossibleItems($this->getRefId()); // for titles
        }
            
        include_once "Services/Tracking/classes/class.ilLPStatusFactory.php";
        $class = ilLPStatusFactory::_getClassById($this->getObjId(), ilLPObjSettings::LP_MODE_COLLECTION_TLT);
        $info = $class::_getStatusInfo($this->getObjId(), true);
        
        foreach ($coll_items as $item_id) {
            // #16599 - deleted items should not be displayed
            if (!array_key_exists($item_id, $possible_items)) {
                continue;
            }
            
            $field = new ilCustomInputGUI($possible_items[$item_id]["title"]);
            
            // lp status
            $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
            if (isset($info["completed"][$item_id]) &&
                in_array($ilUser->getId(), $info["completed"][$item_id])) {
                $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
            } elseif (isset($info["in_progress"][$item_id]) &&
                in_array($ilUser->getId(), $info["in_progress"][$item_id])) {
                $status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
            }
            $path = ilLearningProgressBaseGUI::_getImagePathForStatus($status);
            $text = ilLearningProgressBaseGUI::_getStatusText($status);
            $field->setHtml(ilUtil::img($path, $text));
            
            // stats
            $spent = 0;
            if (isset($info["tlt_users"][$item_id][$ilUser->getId()])) {
                $spent = $info["tlt_users"][$item_id][$ilUser->getId()];
            }
            $needed = $info["tlt"][$item_id];
            if ($needed) {
                $field->setInfo(sprintf(
                    $lng->txt("trac_collection_tlt_learner_subitem"),
                    ilDatePresentation::secondsToString($spent),
                    ilDatePresentation::secondsToString($needed),
                    min(100, round(abs($spent)/$needed*100))
                ));
            }
            
            $form->addItem($field);
        }
        
        $tpl->setContent($form->getHTML());
    }
}
