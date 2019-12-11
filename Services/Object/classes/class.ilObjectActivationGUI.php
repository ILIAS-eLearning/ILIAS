<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjectActivationGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @ilCtrl_Calls ilObjectActivationGUI: ilConditionHandlerGUI
 */
class ilObjectActivationGUI
{
    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilTabsGUI
     */
    protected $tabs_gui;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var int
     */
    protected $parent_ref_id;

    /**
     * @var int
     */
    protected $item_id;

    /**
     * @var \ilTemplate
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var int|null
     */
    protected $timing_mode = null;

    /**
     * @var int|null
     */
    protected $activation = null;

    /**
     * ilObjectActivationGUI constructor.
     * @param $a_ref_id
     * @param $a_item_id
     */
    public function __construct($a_ref_id, $a_item_id)
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('crs');

        $this->error = $DIC['ilErr'];
        $this->tabs_gui = $DIC->tabs();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();
        $this->help = $DIC["ilHelp"];


        $this->parent_ref_id = $a_ref_id;
        $this->item_id = $a_item_id;

        $this->ctrl->saveParameter($this, 'item_id');
    }

    /**
     * Execute command
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $tpl = $this->tpl;

        $this->__setTabs();

        $cmd = $this->ctrl->getCmd();

        // Check if item id is given and valid
        if (!$this->item_id) {
            ilUtil::sendFailure($this->lng->txt("crs_no_item_id_given"), true);
            $this->ctrl->returnToParent($this);
        }
        
        $tpl->getStandardTemplate();
        
        switch ($this->ctrl->getNextClass($this)) {
            case 'ilconditionhandlergui':
                // preconditions for single course items
                $this->ctrl->saveParameter($this, 'item_id', $_GET['item_id']);
                $new_gui = new ilConditionHandlerGUI($this, (int) $_GET['item_id']);
                $this->ctrl->forwardCommand($new_gui);
                $this->tabs_gui->setTabActive('preconditions');
                break;

            default:
                $this->initTimingMode();
                $this->initItem();
                $this->tabs_gui->setTabActive('timings');
                if (!$cmd) {
                    $cmd = 'edit';
                }
                $this->$cmd();
                $this->tabs_gui->setTabActive('timings');
                break;
        }
        
        $tpl->show();
    }

    /**
     * @return int
     */
    public function getItemId()
    {
        return $this->item_id;
    }

    public function getTimingMode()
    {
        return $this->timing_mode;
    }

    /**
     * Get parent ref_id
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_ref_id;
    }

    /**
     * Get item object
     * @return ilObjectActivation
     */
    public function getActivation()
    {
        return $this->activation;
    }


    /**
     * cancel action handler
     */
    public function cancel()
    {
        $this->ctrl->setParameterByClass('ilrepositorygui', 'ref_id', $this->parent_ref_id);
        $this->ctrl->redirectByClass('ilrepositorygui');
    }

    /**
     * edit timings
     *
     * @access public
     * @return
     */
    public function edit(ilPropertyFormGUI $form = null)
    {
        $ilErr = $this->error;
        $ilAccess = $this->access;
        $tpl = $this->tpl;

        // #19997 - see ilObjectListGUI::insertTimingsCommand()
        if (
            !$ilAccess->checkAccess('write', '', $this->parent_ref_id) &&
            !$ilAccess->checkAccess('write', '', $this->getItemId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
        }
        
        if (!$form instanceof ilPropertyFormGUI) {
            // show edit warning if timings are on
            if ($GLOBALS['tree']->checkForParentType($this->getParentId(), 'crs')) {
                if ($this->getActivation()->getTimingType() == ilObjectActivation::TIMINGS_PRESETTING) {
                    ilUtil::sendInfo($this->lng->txt('crs_timings_warning_timing_exists'));
                }
            }

            $form = $this->initFormEdit();
        }
        $tpl->setContent($form->getHTML());
    }
    
    /**
     * init form edit
     *
     * @access protected
     * @return
     */
    protected function initFormEdit()
    {
        $tree = $this->tree;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $title = ilObject::_lookupTitle(ilObject::_lookupObjId($this->getItemId()));
        $form->setTitle($title . ': ' . $this->lng->txt('crs_edit_timings'));


        $availability = new ilCheckboxInputGUI($this->lng->txt('crs_timings_availability_enabled'), 'availability');
        $availability->setValue(1);
        $availability->setChecked($this->getActivation()->getTimingType() == ilObjectActivation::TIMINGS_ACTIVATION);

        $start = new ilDateTimeInputGUI($this->lng->txt('crs_timings_start'), 'timing_start');
        $start->setDate(new ilDateTime($this->getActivation()->getTimingStart(), IL_CAL_UNIX));
        $start->setShowTime(true);
        $availability->addSubItem($start);

        $end = new ilDateTimeInputGUI($this->lng->txt('crs_timings_end'), 'timing_end');
        $end->setDate(new ilDateTime($this->getActivation()->getTimingEnd(), IL_CAL_UNIX));
        $end->setShowTime(true);
        $availability->addSubItem($end);

        $isv = new ilCheckboxInputGUI($this->lng->txt('crs_timings_visibility_short'), 'visible');
        $isv->setInfo($this->lng->txt('crs_timings_visibility'));
        $isv->setValue(1);
        $isv->setChecked((bool) $this->getActivation()->enabledVisible());
        $availability->addSubItem($isv);


        $form->addItem($availability);

        $form->addCommandButton('update', $this->lng->txt('save'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }

    /**
     * update
     *
     * @access public
     * @return
     */
    public function update()
    {
        $ilErr = $this->error;
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilUser = $this->user;

        // #19997 - see ilObjectListGUI::insertTimingsCommand()
        if (
            !$ilAccess->checkAccess('write', '', $this->parent_ref_id) &&
            !$ilAccess->checkAccess('write', '', $this->getItemId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
        }

        $form = $this->initFormEdit();
        if ($form->checkInput()) {
            $activation = new ilObjectActivation();
            $activation->read($this->getItemId());

            if ($form->getInput('availability')) {
                $this->getActivation()->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);

                $timing_start = $form->getItemByPostVar('timing_start')->getDate();
                $this->getActivation()->setTimingStart($timing_start ? $timing_start->get(IL_CAL_UNIX) : null);

                $timing_end = $form->getItemByPostVar('timing_end')->getDate();
                $this->getActivation()->setTimingEnd($timing_end ? $timing_end->get(IL_CAL_UNIX) : null);

                $this->getActivation()->toggleVisible((bool) $form->getInput('visible'));
            } elseif ($this->getActivation()->getTimingType() != ilObjectActivation::TIMINGS_PRESETTING) {
                $this->getActivation()->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
            }

            $this->getActivation()->update($this->getItemId(), $this->getParentId());
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, "edit");
        } else {
            $form->setValuesByPost();
            $this->edit($form);
        }
    }

    /**
     * @return bool
     */
    protected function __setTabs()
    {
        $ilCtrl = $this->ctrl;
        $ilHelp = $this->help;
        
        $this->tabs_gui->clearTargets();

        $ilHelp->setScreenIdComponent("obj");

        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->parent_ref_id);
        $back_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "");
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
        $this->tabs_gui->setBackTarget($this->lng->txt('btn_back'), $back_link);
        
        $this->tabs_gui->addTarget(
            "timings",
            $this->ctrl->getLinkTarget($this, 'edit'),
            "edit",
            get_class($this)
        );
        
        $this->ctrl->setParameterByClass('ilconditionhandlergui', 'item_id', $this->item_id);
        $this->tabs_gui->addTarget(
            "preconditions",
            $this->ctrl->getLinkTargetByClass('ilConditionHandlerGUI', 'listConditions'),
            "",
            "ilConditionHandlerGUI"
        );
        return true;
    }

    /**
     * Init type of timing mode
     */
    protected function initTimingMode()
    {
        // Check for parent course and if available read timing mode (abs | rel)
        $crs_ref_id = $GLOBALS['tree']->checkForParentType(
            $this->parent_ref_id,
            'crs'
        );
        $crs_obj_id = ilObject::_lookupObjId($crs_ref_id);

        if ($crs_obj_id) {
            $this->timing_mode = ilObjCourse::lookupTimingMode($crs_obj_id);
        } else {
            $this->timing_mode = ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE;
        }
    }

    /**
     * Init item
     */
    protected function initItem()
    {
        $this->activation = new ilObjectActivation();
        $this->getActivation()->read($this->item_id, $this->getParentId());
    }
}
