<?php

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
 * Class ilBookingScheduleGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilBookingScheduleGUI:
 */
class ilBookingScheduleGUI
{
    protected \ILIAS\BookingManager\StandardGUIRequest $book_request;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilAccessHandler $access;
    protected ilHelpGUI $help;
    protected ilObjectDataCache $obj_data_cache;
    protected int $schedule_id;
    protected int $ref_id;

    public function __construct(
        ilObjBookingPoolGUI $a_parent_obj
    ) {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->help = $DIC["ilHelp"];
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->ref_id = $a_parent_obj->getRefId();
        $this->book_request = $DIC->bookingManager()
                                  ->internal()
                                  ->gui()
                                  ->standardRequest();
        $this->schedule_id = $this->book_request->getScheduleId();
    }

    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass($this);

        switch ($next_class) {
            default:
                $cmd = $ilCtrl->getCmd("render");
                $this->$cmd();
                break;
        }
    }

    /**
     * Render list of booking schedules
     * uses ilBookingSchedulesTableGUI
     */
    public function render() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        $table = new ilBookingSchedulesTableGUI($this, 'render', $this->ref_id);

        $bar = "";
        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            // if we have schedules but no objects - show info
            if (count($table->getData())) {
                if (!count(ilBookingObject::getList(ilObject::_lookupObjId($this->ref_id)))) {
                    $this->tpl->setOnScreenMessage('info', $lng->txt("book_type_warning"));
                }
            }
            
            $bar = new ilToolbarGUI();
            $bar->addButton($lng->txt('book_add_schedule'), $ilCtrl->getLinkTarget($this, 'create'));
            $bar = $bar->getHTML();
        }
        
        $tpl->setContent($bar . $table->getHTML());
    }

    /**
     * Render creation form
     */
    public function create() : void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilHelp = $this->help;

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));
        $ilHelp->setScreenIdComponent("book");
        $ilHelp->setScreenId("schedules");
        $ilHelp->setSubScreenId("create");

        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Render edit form
     */
    public function edit() : void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilHelp = $this->help;

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));
        $ilHelp->setScreenIdComponent("book");
        $ilHelp->setScreenId("schedules");
        $ilHelp->setSubScreenId("edit");

        $form = $this->initForm('edit', $this->schedule_id);
        $tpl->setContent($form->getHTML());
    }

    /**
     * Build property form
     */
    public function initForm(
        string $a_mode = "create",
        ?int $id = null
    ) : ilPropertyFormGUI {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $lng->loadLanguageModule("dateplaner");

        $form_gui = new ilPropertyFormGUI();

        $title = new ilTextInputGUI($lng->txt("title"), "title");
        $title->setRequired(true);
        $title->setSize(40);
        $title->setMaxLength(120);
        $form_gui->addItem($title);

        $definition = new ilScheduleInputGUI($lng->txt("book_schedule_days"), "days");
        $definition->setInfo($lng->txt("book_schedule_days_info"));
        $definition->setRequired(true);
        $form_gui->addItem($definition);

        $deadline_opts = new ilRadioGroupInputGUI($lng->txt("book_deadline_options"), "deadline_opts");
        $deadline_opts->setRequired(true);
        $form_gui->addItem($deadline_opts);

        $deadline_time = new ilRadioOption($lng->txt("book_deadline_hours"), "hours");
        $deadline_opts->addOption($deadline_time);

        $deadline = new ilNumberInputGUI($lng->txt("book_deadline"), "deadline");
        $deadline->setInfo($lng->txt("book_deadline_info"));
        $deadline->setSuffix($lng->txt("book_hours"));
        $deadline->setMinValue(1);
        $deadline->setSize(3);
        $deadline->setMaxLength(3);
        $deadline_time->addSubItem($deadline);

        $deadline_start = new ilRadioOption($lng->txt("book_deadline_slot_start"), "slot_start");
        $deadline_opts->addOption($deadline_start);

        $deadline_slot = new ilRadioOption($lng->txt("book_deadline_slot_end"), "slot_end");
        $deadline_opts->addOption($deadline_slot);
        
        if ($a_mode === "edit") {
            $schedule = new ilBookingSchedule($id);
        }
        
        $av = new ilFormSectionHeaderGUI();
        $av->setTitle($lng->txt("obj_activation_list_gui"));
        $form_gui->addItem($av);
                
        // #18221
        $lng->loadLanguageModule('rep');
        
        $from = new ilDateTimeInputGUI($lng->txt("rep_activation_limited_start"), "from");
        $from->setShowTime(true);
        $form_gui->addItem($from);
        
        $to = new ilDateTimeInputGUI($lng->txt("rep_activation_limited_end"), "to");
        $to->setShowTime(true);
        $form_gui->addItem($to);
    
        if ($a_mode === "edit") {
            $form_gui->setTitle($lng->txt("book_edit_schedule"));

            $item = new ilHiddenInputGUI('schedule_id');
            $item->setValue($id);
            $form_gui->addItem($item);

            $schedule = new ilBookingSchedule($id);
            $title->setValue($schedule->getTitle());
            $from->setDate($schedule->getAvailabilityFrom());
            $to->setDate($schedule->getAvailabilityTo());
            
            if ($schedule->getDeadline() === 0) {
                $deadline_opts->setValue("slot_start");
            } elseif ($schedule->getDeadline() > 0) {
                $deadline->setValue($schedule->getDeadline());
                $deadline_opts->setValue("hours");
            } else {
                $deadline->setValue(0);
                $deadline_opts->setValue("slot_end");
            }

            $definition->setValue($schedule->getDefinitionBySlots());

            $form_gui->addCommandButton("update", $lng->txt("save"));
        } else {
            $form_gui->setTitle($lng->txt("book_add_schedule"));
            $form_gui->addCommandButton("save", $lng->txt("save"));
            $form_gui->addCommandButton("render", $lng->txt("cancel"));
        }
        $form_gui->setFormAction($ilCtrl->getFormAction($this));

        return $form_gui;
    }

    public function save() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        $form = $this->initForm();
        if ($form->checkInput()) {
            $obj = new ilBookingSchedule();
            $this->formToObject($form, $obj);
            $obj->save();

            $this->tpl->setOnScreenMessage('success', $lng->txt("book_schedule_added"));
            $this->render();
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }

    public function update() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        $form = $this->initForm('edit', $this->schedule_id);
        if ($form->checkInput()) {
            $obj = new ilBookingSchedule($this->schedule_id);
            $this->formToObject($form, $obj);
            $obj->update();

            $this->tpl->setOnScreenMessage('success', $lng->txt("book_schedule_updated"));
            $this->render();
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }
    
    /**
     * Set form data into schedule object
     */
    protected function formToObject(
        ilPropertyFormGUI $form,
        ilBookingSchedule $schedule
    ) : void {
        $ilObjDataCache = $this->obj_data_cache;
        
        $schedule->setTitle($form->getInput("title"));
        $schedule->setPoolId($ilObjDataCache->lookupObjId($this->ref_id));
        
        $from = $form->getItemByPostVar("from");
        if ($from !== null) {
            $schedule->setAvailabilityFrom($from->getDate());
        }

        $to = $form->getItemByPostVar("to");
        if ($to !== null) {
            $schedule->setAvailabilityTo($to->getDate());
        }

        switch ($form->getInput("deadline_opts")) {
            case "slot_start":
                $schedule->setDeadline(0);
                break;
            
            case "hours":
                $schedule->setDeadline($form->getInput("deadline"));
                break;
            
            case "slot_end":
                $schedule->setDeadline(-1);
                break;
        }

        /*
        if($form->getInput("type") == "flexible")
        {
            $schedule->setRaster($form->getInput("raster"));
            $schedule->setMinRental($form->getInput("rent_min"));
            $schedule->setMaxRental($form->getInput("rent_max"));
            $schedule->setAutoBreak($form->getInput("break"));
        }
        else
        {
            $schedule->setRaster(NULL);
            $schedule->setMinRental(NULL);
            $schedule->setMaxRental(NULL);
            $schedule->setAutoBreak(NULL);
        }
        */

        $days = $form->getInput("days");
        $schedule->setDefinitionBySlots($days);
    }

    /**
     * Confirm delete
     */
    public function confirmDelete() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilHelp = $this->help;

        $ilHelp->setSubScreenId("delete");


        $conf = new ilConfirmationGUI();
        $conf->setFormAction($ilCtrl->getFormAction($this));
        $conf->setHeaderText($lng->txt('book_confirm_delete'));

        $type = new ilBookingSchedule($this->schedule_id);
        $conf->addItem('schedule_id', $this->schedule_id, $type->getTitle());
        $conf->setConfirm($lng->txt('delete'), 'delete');
        $conf->setCancel($lng->txt('cancel'), 'render');

        $tpl->setContent($conf->getHTML());
    }

    /**
     * Delete schedule
     */
    public function delete() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $obj = new ilBookingSchedule($this->schedule_id);
        $obj->delete();

        $this->tpl->setOnScreenMessage('success', $lng->txt('book_schedule_deleted'), true);
        $ilCtrl->redirect($this, 'render');
    }
}
