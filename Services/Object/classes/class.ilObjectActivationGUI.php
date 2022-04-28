<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
/**
 * Class ilObjectActivationGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @ilCtrl_Calls ilObjectActivationGUI: ilConditionHandlerGUI
 */
class ilObjectActivationGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilErrorHandling $error;
    protected ilTabsGUI $tabs_gui;
    protected ilAccessHandler $access;
    protected ilTree $tree;
    protected ilObjUser $user;
    protected ilHelpGUI $help;
    protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper;
    protected ILIAS\Refinery\Factory $refinery;

    protected int $parent_ref_id;
    protected int $item_id;

    protected ?int $timing_mode = null;
    protected ?ilObjectActivation $activation = null;

    public function __construct(int $ref_id, int $item_id)
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
        $this->request_wrapper = $DIC->http()->wrapper()->query();
        $this->refinery = $DIC->refinery();

        $this->parent_ref_id = $ref_id;
        $this->item_id = $item_id;

        $this->ctrl->saveParameter($this, 'item_id');
    }

    public function executeCommand() : void
    {
        $this->__setTabs();

        $cmd = $this->ctrl->getCmd();

        // Check if item id is given and valid
        if (!$this->item_id) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("crs_no_item_id_given"), true);
            $this->ctrl->returnToParent($this);
        }
        
        $this->tpl->loadStandardTemplate();
        
        switch ($this->ctrl->getNextClass($this)) {
            case 'ilconditionhandlergui':
                // preconditions for single course items
                $this->ctrl->saveParameter($this, 'item_id');
                $item_id = $this->request_wrapper->retrieve("item_id", $this->refinery->kindlyTo()->int());
                $new_gui = new ilConditionHandlerGUI($item_id);
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
        
        $this->tpl->printToStdout();
    }

    public function getItemId() : int
    {
        return $this->item_id;
    }

    public function getTimingMode() : ?int
    {
        return $this->timing_mode;
    }

    public function getParentId() : int
    {
        return $this->parent_ref_id;
    }

    public function getActivation() : ?ilObjectActivation
    {
        return $this->activation;
    }

    public function cancel() : void
    {
        $this->ctrl->setParameterByClass('ilrepositorygui', 'ref_id', $this->parent_ref_id);
        $this->ctrl->redirectByClass('ilrepositorygui');
    }

    public function edit(ilPropertyFormGUI $form = null) : void
    {
        // #19997 - see ilObjectListGUI::insertTimingsCommand()
        if (
            !$this->access->checkAccess('write', '', $this->parent_ref_id) &&
            !$this->access->checkAccess('write', '', $this->getItemId())
        ) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
        
        if (!$form instanceof ilPropertyFormGUI) {
            // show edit warning if timings are on
            if ($this->tree->checkForParentType($this->getParentId(), 'crs')) {
                if ($this->getActivation()->getTimingType() == ilObjectActivation::TIMINGS_PRESETTING) {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt('crs_timings_warning_timing_exists'));
                }
            }

            $form = $this->initFormEdit();
        }
        $this->tpl->setContent($form->getHTML());
    }
    
    protected function initFormEdit() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $title = ilObject::_lookupTitle(ilObject::_lookupObjId($this->getItemId()));
        $form->setTitle($title . ': ' . $this->lng->txt('crs_edit_timings'));

        $availability = new ilCheckboxInputGUI($this->lng->txt('crs_timings_availability_enabled'), 'availability');
        $availability->setValue("1");
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
        $isv->setValue("1");
        $isv->setChecked($this->getActivation()->enabledVisible());
        $availability->addSubItem($isv);

        $form->addItem($availability);

        $form->addCommandButton('update', $this->lng->txt('save'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }

    public function update() : void
    {
        // #19997 - see ilObjectListGUI::insertTimingsCommand()
        if (
            !$this->access->checkAccess('write', '', $this->parent_ref_id) &&
            !$this->access->checkAccess('write', '', $this->getItemId())
        ) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->initFormEdit();
        if ($form->checkInput()) {
            $valid = true;
            $activation = new ilObjectActivation();
            $activation->read($this->getItemId());

            if ($form->getInput('availability')) {
                $this->getActivation()->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);

                $timing_start = $form->getItemByPostVar('timing_start')->getDate();
                $timing_end = $form->getItemByPostVar('timing_end')->getDate();

                if ($timing_start && $timing_end && ilDateTime::_after($timing_start, $timing_end)) {
                    $form->getItemByPostVar('timing_start')->setAlert($this->lng->txt('crs_timing_err_start_end'));
                    $form->getItemByPostVar('timing_end')->setAlert($this->lng->txt('crs_timing_err_start_end'));
                    $valid = false;
                }

                $this->getActivation()->setTimingStart($timing_start ? $timing_start->get(IL_CAL_UNIX) : null);
                $this->getActivation()->setTimingEnd($timing_end ? $timing_end->get(IL_CAL_UNIX) : null);

                $this->getActivation()->toggleVisible((bool) $form->getInput('visible'));
            } elseif ($this->getActivation()->getTimingType() != ilObjectActivation::TIMINGS_PRESETTING) {
                $this->getActivation()->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
            }

            if ($valid) {
                $this->getActivation()->update($this->getItemId(), $this->getParentId());
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
                $this->ctrl->redirect($this, "edit");
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            }
        }

        $form->setValuesByPost();
        $this->edit($form);
    }

    protected function __setTabs() : bool
    {
        $this->tabs_gui->clearTargets();

        $this->help->setScreenIdComponent("obj");

        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->parent_ref_id);
        $back_link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", "");
        $ref_id = $this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->string());
        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
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

    protected function initTimingMode() : void
    {
        // Check for parent course and if available read timing mode (abs | rel)
        $crs_ref_id = $this->tree->checkForParentType($this->parent_ref_id, 'crs');
        $crs_obj_id = ilObject::_lookupObjId($crs_ref_id);

        if ($crs_obj_id) {
            $this->timing_mode = ilObjCourse::lookupTimingMode($crs_obj_id);
        } else {
            $this->timing_mode = ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE;
        }
    }

    protected function initItem() : void
    {
        $this->activation = new ilObjectActivation();
        $this->getActivation()->read($this->item_id, $this->getParentId());
    }
}
