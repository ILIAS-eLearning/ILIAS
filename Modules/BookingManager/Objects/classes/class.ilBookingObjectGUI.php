<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBookingObjectGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ilCtrl_Calls ilBookingObjectGUI: ilPropertyFormGUI, ilBookingProcessGUI
 */
class ilBookingObjectGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilBookingHelpAdapter
     */
    protected $help;

    /**
     * @var ilObjectDataCache
     */
    protected $obj_data_cache;

    /**
     * @var ilObjUser
     */
    protected $user;

    protected $pool_has_schedule; // [bool]
    protected $pool_overall_limit; // [int]

    /**
     * @var bool 
     */
    protected $pool_uses_preferences = false;

    /**
     * Is management of objects (create/edit/delete) activated?
     * @var bool
     */
    protected $management = true;

    /**
     * Context object id (e.g. course with booking service activated)
     * @var int
     */
    protected $context_obj_id;

    /**
     * @var int
     */
    protected $object_id;

    /**
     * Constructor
     * @param	object	$a_parent_obj
     */
    public function __construct($a_parent_obj, $seed, $sseed, ilBookingHelpAdapter $help, int $context_obj_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tabs = $DIC->tabs();
        $this->help = $help;
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->user = $DIC->user();

        $this->seed = $seed;
        $this->sseed = $sseed;

        $this->context_obj_id = $context_obj_id;

        /** @var ilObjBookingPoolGUI $this->pool_gui */
        $this->pool_gui = $a_parent_obj;
        $this->pool_has_schedule =
            ($a_parent_obj->object->getScheduleType() == ilObjBookingPool::TYPE_FIX_SCHEDULE);
        $this->pool_uses_preferences =
            ($a_parent_obj->object->getScheduleType() == ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES);
        $this->pool_overall_limit = $this->pool_has_schedule
            ? null
            : $a_parent_obj->object->getOverallLimit();

        $this->object_id = (int) $_REQUEST['object_id'];
        $this->ref_id = (int) $_REQUEST['ref_id'];
        $this->ctrl->saveParameter($this, "object_id");

        $this->rsv_ids = array_map('intval', explode(";", $_GET["rsv_ids"]));
    }

    /**
     * Activate management
     *
     * @param bool $a_val
     */
    public function activateManagement($a_val)
    {
        $this->management = $a_val;
    }

    /**
     * Is management activated?
     *
     * @return bool
     */
    public function isManagementActivated()
    {
        return $this->management;
    }


    /**
     * Get ref id of booking pool
     *
     * @return int
     */
    protected function getPoolRefId() : int
    {
        return $this->pool_gui->ref_id;
    }

    /**
     * Get obj id of booking pool
     *
     * @return int
     */
    protected function getPoolObjId() : int
    {
        return $this->pool_gui->object->getId();
    }

    /**
     * Has booking pool a schedule?
     *
     * @return bool
     */
    protected function hasPoolSchedule() : bool
    {
        return ($this->pool_gui->object->getScheduleType() == ilObjBookingPool::TYPE_FIX_SCHEDULE);
    }

    /**
     * Get booking pool overall limit
     *
     * @return int
     */
    protected function getPoolOverallLimit()
    {
        return $this->hasPoolSchedule()
            ? null
            : $this->pool_gui->object->getOverallLimit();
    }

    /**
     * @return bool
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
                
        $next_class = $ilCtrl->getNextClass($this);
        
        switch ($next_class) {

            case "ilpropertyformgui":
                // only case is currently adv metadata internal link in info settings, see #24497
                $form = $this->initForm();
                $this->ctrl->forwardCommand($form);
                break;

            case "ilbookingprocessgui":
                if (!$this->pool_uses_preferences) {
                    $ilCtrl->setReturn($this, "render");
                } else {
                    $ilCtrl->setReturn($this, "returnToPreferences");
                }
                $process_gui = new ilBookingProcessGUI(
                    $this->pool_gui->object,
                    $this->object_id,
                    $this->help,
                    $this->seed,
                    $this->sseed,
                    $this->context_obj_id
                );
                $this->ctrl->forwardCommand($process_gui);
                break;

            default:
                $cmd = $ilCtrl->getCmd("render");
                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * Show no schedule message
     */
    protected function showNoScheduleMessage()
    {
        $this->pool_gui->showNoScheduleMessage();
    }

    /**
     * Return to preferences
     */
    protected function returnToPreferences()
    {
        $this->ctrl->redirectByClass("ilBookingPreferencesGUI");
    }

    /**
     * Render list of booking objects
     *
     * uses ilBookingObjectsTableGUI
     */
    public function render()
    {
        $this->showNoScheduleMessage();

        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;

        if ($this->isManagementActivated() && $ilAccess->checkAccess('write', '', $this->getPoolRefId())) {
            $bar = new ilToolbarGUI;
            $bar->addButton($lng->txt('book_add_object'), $ilCtrl->getLinkTarget($this, 'create'));
            $bar = $bar->getHTML();
        }
        
        $tpl->setPermanentLink('book', $this->getPoolRefId());
        
        $table = new ilBookingObjectsTableGUI($this, 'render', $this->getPoolRefId(), $this->getPoolObjId(), $this->hasPoolSchedule(), $this->getPoolOverallLimit(), $this->isManagementActivated());
        $tpl->setContent($bar . $table->getHTML());
    }
    
    public function applyFilter()
    {
        $table = new ilBookingObjectsTableGUI($this, 'render', $this->getPoolRefId(), $this->getPoolObjId(), $this->hasPoolSchedule(), $this->getPoolOverallLimit(), $this->isManagementActivated());
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->render();
    }
    
    public function resetFilter()
    {
        $table = new ilBookingObjectsTableGUI($this, 'render', $this->getPoolRefId(), $this->getPoolObjId(), $this->hasPoolSchedule(), $this->getPoolOverallLimit(), $this->isManagementActivated());
        $table->resetOffset();
        $table->resetFilter();
        $this->render();
    }

    /**
     * Render creation form
     */
    public function create(ilPropertyFormGUI $a_form = null)
    {
        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            return;
        }

        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

        $this->setHelpId('create');
        
        if (!$a_form) {
            $a_form = $this->initForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Render edit form
     */
    public function edit(ilPropertyFormGUI $a_form = null)
    {
        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            return;
        }

        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));
        
        $this->setHelpId('edit');

        if (!$a_form) {
            $a_form = $this->initForm('edit', $this->object_id);
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function setHelpId($a_id)
    {
        $this->help->setHelpId($a_id);
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
        $ilObjDataCache = $this->obj_data_cache;

        $form_gui = new ilPropertyFormGUI();

        $title = new ilTextInputGUI($lng->txt("title"), "title");
        $title->setRequired(true);
        $title->setSize(40);
        $title->setMaxLength(120);
        $form_gui->addItem($title);
        
        $desc = new ilTextAreaInputGUI($lng->txt("description"), "desc");
        $desc->setCols(70);
        $desc->setRows(15);
        $form_gui->addItem($desc);
        
        $file = new ilFileInputGUI($lng->txt("book_additional_info_file"), "file");
        $file->setALlowDeletion(true);
        $form_gui->addItem($file);
        
        $nr = new ilNumberInputGUI($lng->txt("booking_nr_of_items"), "items");
        $nr->setRequired(true);
        $nr->setSize(3);
        $nr->setMaxLength(3);
        $form_gui->addItem($nr);
        
        if ($this->hasPoolSchedule()) {
            $options = array();
            foreach (ilBookingSchedule::getList($ilObjDataCache->lookupObjId($this->getPoolRefId())) as $schedule) {
                $options[$schedule["booking_schedule_id"]] = $schedule["title"];
            }
            $schedule = new ilSelectInputGUI($lng->txt("book_schedule"), "schedule");
            $schedule->setRequired(true);
            $schedule->setOptions($options);
            $form_gui->addItem($schedule);
        }
        
        $post = new ilFormSectionHeaderGUI();
        $post->setTitle($lng->txt("book_post_booking_information"));
        $form_gui->addItem($post);
        
        $pdesc = new ilTextAreaInputGUI($lng->txt("book_post_booking_text"), "post_text");
        $pdesc->setCols(70);
        $pdesc->setRows(15);
        $pdesc->setInfo($lng->txt("book_post_booking_text_info"));
        $form_gui->addItem($pdesc);
        
        $pfile = new ilFileInputGUI($lng->txt("book_post_booking_file"), "post_file");
        $pfile->setALlowDeletion(true);
        $form_gui->addItem($pfile);

        // #18214 - should also work for new objects
        $this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR, "book", $this->getPoolObjId(), "bobj", $id);
        $this->record_gui->setPropertyForm($form_gui);
        $this->record_gui->parse();

        if ($a_mode == "edit") {
            $form_gui->setTitle($lng->txt("book_edit_object"));
            
            $item = new ilHiddenInputGUI('object_id');
            $item->setValue($id);
            $form_gui->addItem($item);

            $obj = new ilBookingObject($id);
            $title->setValue($obj->getTitle());
            $desc->setValue($obj->getDescription());
            $nr->setValue($obj->getNrOfItems());
            $pdesc->setValue($obj->getPostText());
            $file->setValue($obj->getFile());
            $pfile->setValue($obj->getPostFile());
            
            if (isset($schedule)) {
                $schedule->setValue($obj->getScheduleId());
            }
            
            $form_gui->addCommandButton("update", $lng->txt("save"));
        } else {
            $form_gui->setTitle($lng->txt("book_add_object"));
            $form_gui->addCommandButton("save", $lng->txt("save"));
            $form_gui->addCommandButton("render", $lng->txt("cancel"));
        }
        $form_gui->setFormAction($ilCtrl->getFormAction($this));

        return $form_gui;
    }

    /**
     * Create new object dataset
     */
    public function save()
    {
        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            return;
        }

        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $form = $this->initForm();
        if ($form->checkInput()) {
            $valid = true;
            if ($this->record_gui &&
                !$this->record_gui->importEditFormPostValues()) {
                $valid = false;
            }
            if ($valid) {
                $obj = new ilBookingObject;
                $obj->setPoolId($this->getPoolObjId());
                $obj->setTitle($form->getInput("title"));
                $obj->setDescription($form->getInput("desc"));
                $obj->setNrOfItems($form->getInput("items"));
                $obj->setPostText($form->getInput("post_text"));

                if ($this->hasPoolSchedule()) {
                    $obj->setScheduleId($form->getInput("schedule"));
                }

                $obj->save();

                $file = $form->getItemByPostVar("file");
                if ($_FILES["file"]["tmp_name"]) {
                    $obj->uploadFile($_FILES["file"]);
                } elseif ($file->getDeletionFlag()) {
                    $obj->deleteFile();
                }

                $pfile = $form->getItemByPostVar("post_file");
                if ($_FILES["post_file"]["tmp_name"]) {
                    $obj->uploadPostFile($_FILES["post_file"]);
                } elseif ($pfile->getDeletionFlag()) {
                    $obj->deletePostFile();
                }

                $obj->update();
                
                if ($this->record_gui) {
                    $this->record_gui->writeEditForm(null, $obj->getId());
                }

                ilUtil::sendSuccess($lng->txt("book_object_added"), true);
                $ilCtrl->redirect($this, "render");
            }
        }
            
        $form->setValuesByPost();
        $this->create($form);
    }

    /**
     * Update object dataset
     */
    public function update()
    {
        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            return;
        }

        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = $this->initForm('edit', $this->object_id);
        if ($form->checkInput()) {
            $valid = true;
            if ($this->record_gui &&
                !$this->record_gui->importEditFormPostValues()) {
                $valid = false;
            }
            
            if ($valid) {
                $obj = new ilBookingObject($this->object_id);
                $obj->setTitle($form->getInput("title"));
                $obj->setDescription($form->getInput("desc"));
                $obj->setNrOfItems($form->getInput("items"));
                $obj->setPostText($form->getInput("post_text"));

                $file = $form->getItemByPostVar("file");
                if ($_FILES["file"]["tmp_name"]) {
                    $obj->uploadFile($_FILES["file"]);
                } elseif ($file->getDeletionFlag()) {
                    $obj->deleteFile();
                }

                $pfile = $form->getItemByPostVar("post_file");
                if ($_FILES["post_file"]["tmp_name"]) {
                    $obj->uploadPostFile($_FILES["post_file"]);
                } elseif ($pfile->getDeletionFlag()) {
                    $obj->deletePostFile();
                }

                if ($this->hasPoolSchedule()) {
                    $obj->setScheduleId($form->getInput("schedule"));
                }

                $obj->update();
                
                if ($this->record_gui) {
                    $this->record_gui->writeEditForm();
                }

                ilUtil::sendSuccess($lng->txt("book_object_updated"), true);
                $ilCtrl->redirect($this, "render");
            }
        }
        
        $form->setValuesByPost();
        $this->edit($form);
    }

    /**
     * Confirm delete
     */
    public function confirmDelete()
    {
        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            return;
        }

        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($ilCtrl->getFormAction($this));
        $conf->setHeaderText($lng->txt('book_confirm_delete'));

        $type = new ilBookingObject($this->object_id);
        $conf->addItem('object_id', $this->object_id, $type->getTitle());
        $conf->setConfirm($lng->txt('delete'), 'delete');
        $conf->setCancel($lng->txt('cancel'), 'render');

        $tpl->setContent($conf->getHTML());
    }

    /**
     * Delete object
     */
    public function delete()
    {
        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            return;
        }

        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $obj = new ilBookingObject($this->object_id);
        $obj->delete();

        ilUtil::sendSuccess($lng->txt('book_object_deleted'), true);
        $ilCtrl->setParameter($this, 'object_id', "");
        $ilCtrl->redirect($this, 'render');
    }
        

    public function deliverInfo()
    {
        $id = $this->object_id;
        if (!$id) {
            return;
        }
        
        $obj = new ilBookingObject($id);
        $file = $obj->getFileFullPath();
        if ($file) {
            ilUtil::deliverFile($file, $obj->getFile());
        }
    }
}
