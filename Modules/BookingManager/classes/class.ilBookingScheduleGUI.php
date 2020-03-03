<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilBookingScheduleGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilBookingScheduleGUI:
*/
class ilBookingScheduleGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilObjectDataCache
     */
    protected $obj_data_cache;

    /**
     * @var int
     */
    protected $schedule_id;

    /**
     * Constructor
     * @param	object	$a_parent_obj
     */
    public function __construct($a_parent_obj)
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->help = $DIC["ilHelp"];
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->ref_id = $a_parent_obj->ref_id;
        $this->schedule_id  = (int) $_REQUEST['schedule_id'];
    }

    /**
     * main switch
     */
    public function executeCommand()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass($this);

        switch ($next_class) {
            default:
                $cmd = $ilCtrl->getCmd("render");
                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * Render list of booking schedules
     *
     * uses ilBookingSchedulesTableGUI
     */
    public function render()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        include_once 'Modules/BookingManager/classes/class.ilBookingSchedulesTableGUI.php';
        $table = new ilBookingSchedulesTableGUI($this, 'render', $this->ref_id);
        
        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            // if we have schedules but no objects - show info
            if (sizeof($table->getData())) {
                include_once "Modules/BookingManager/classes/class.ilBookingObject.php";
                if (!sizeof(ilBookingObject::getList(ilObject::_lookupObjId($this->ref_id)))) {
                    ilUtil::sendInfo($lng->txt("book_type_warning"));
                }
            }
            
            include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
            $bar = new ilToolbarGUI;
            $bar->addButton($lng->txt('book_add_schedule'), $ilCtrl->getLinkTarget($this, 'create'));
            $bar = $bar->getHTML();
        }
        
        $tpl->setContent($bar . $table->getHTML());
    }

    /**
     * Render creation form
     */
    public function create()
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
    public function edit()
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
     * @param	string	$a_mode
     * @param	int		$id
     * @return	object
     */
    public function initForm($a_mode = "create", $id = null)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $lng->loadLanguageModule("dateplaner");

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

        $form_gui = new ilPropertyFormGUI();

        $title = new ilTextInputGUI($lng->txt("title"), "title");
        $title->setRequired(true);
        $title->setSize(40);
        $title->setMaxLength(120);
        $form_gui->addItem($title);

        include_once "Modules/BookingManager/classes/class.ilScheduleInputGUI.php";
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
        
        if ($a_mode == "edit") {
            include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
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
    
        if ($a_mode == "edit") {
            $form_gui->setTitle($lng->txt("book_edit_schedule"));

            $item = new ilHiddenInputGUI('schedule_id');
            $item->setValue($id);
            $form_gui->addItem($item);

            include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
            $schedule = new ilBookingSchedule($id);
            $title->setValue($schedule->getTitle());
            $from->setDate($schedule->getAvailabilityFrom());
            $to->setDate($schedule->getAvailabilityTo());
            
            if ($schedule->getDeadline() == 0) {
                $deadline_opts->setValue("slot_start");
            } elseif ($schedule->getDeadline() > 0) {
                $deadline->setValue($schedule->getDeadline());
                $deadline_opts->setValue("hours");
            } else {
                $deadline->setValue(0);
                $deadline_opts->setValue("slot_end");
            }

            /*
            if($schedule->getRaster())
            {
                $type->setValue("flexible");
                $raster->setValue($schedule->getRaster());
                $rent_min->setValue($schedule->getMinRental());
                $rent_max->setValue($schedule->getMaxRental());
                $break->setValue($schedule->getAutoBreak());
            }
            else
            {
                $type->setValue("fix");
            }
            */

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

    /**
     * Create new dataset
     */
    public function save()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        $form = $this->initForm();
        if ($form->checkInput()) {
            include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
            $obj = new ilBookingSchedule;
            $this->formToObject($form, $obj);
            $obj->save();

            ilUtil::sendSuccess($lng->txt("book_schedule_added"));
            $this->render();
        } else {
            $form->setValuesByPost();
            $this->setDefinitionFromPost($form);
            $tpl->setContent($form->getHTML());
        }
    }

    /**
     * Update dataset
     */
    public function update()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        $form = $this->initForm('edit', $this->schedule_id);
        if ($form->checkInput()) {
            include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
            $obj = new ilBookingSchedule($this->schedule_id);
            $this->formToObject($form, $obj);
            $obj->update();

            ilUtil::sendSuccess($lng->txt("book_schedule_updated"));
            $this->render();
        } else {
            $form->setValuesByPost();
            $this->setDefinitionFromPost($form);
            $tpl->setContent($form->getHTML());
        }
    }

    /**
     * Reload definition values from post data
     *
     * @param ilPropertyFormGUI $form
     */
    protected function setDefinitionFromPost(ilPropertyFormGUI $form)
    {
        $days = $form->getInput("days");
        if ($days) {
            $days_group = $form->getItemByPostVar("days");
            foreach ($days_group->getOptions() as $option) {
                $days_fields[$option->getValue()] = $option;
            }
            
            foreach ($days as $day) {
                $slot = $form->getInput($day . "_slot");
                $subs = $days_fields[$day]->getSubItems();
                if ($slot[0]) {
                    $subs[0]->setValue($slot[0]);
                }
                if ($slot[1]) {
                    $subs[1]->setValue($slot[1]);
                }
            }
        }
    }

    /**
     * Convert incoming form data to schedule object
     * @param	object	$form
     * @param	object	$schedule
     */
    protected function formToObject($form, $schedule)
    {
        $ilObjDataCache = $this->obj_data_cache;
        
        $schedule->setTitle($form->getInput("title"));
        $schedule->setPoolId($ilObjDataCache->lookupObjId($this->ref_id));
        
        $from = $form->getItemByPostVar("from");
        $schedule->setAvailabilityFrom($from->getDate());
        
        $to = $form->getItemByPostVar("to");
        $schedule->setAvailabilityTo($to->getDate());
        
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
        
        $schedule->setDefinitionBySlots(ilScheduleInputGUI::getPostData("days"));
    }

    /**
     * Confirm delete
     */
    public function confirmDelete()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilHelp = $this->help;

        $ilHelp->setSubScreenId("delete");


        include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($ilCtrl->getFormAction($this));
        $conf->setHeaderText($lng->txt('book_confirm_delete'));

        include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
        $type = new ilBookingSchedule($this->schedule_id);
        $conf->addItem('schedule_id', $this->schedule_id, $type->getTitle());
        $conf->setConfirm($lng->txt('delete'), 'delete');
        $conf->setCancel($lng->txt('cancel'), 'render');

        $tpl->setContent($conf->getHTML());
    }

    /**
     * Delete schedule
     */
    public function delete()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
        $obj = new ilBookingSchedule($this->schedule_id);
        $obj->delete();

        ilUtil::sendSuccess($lng->txt('book_schedule_deleted'), true);
        $ilCtrl->redirect($this, 'render');
    }
}
