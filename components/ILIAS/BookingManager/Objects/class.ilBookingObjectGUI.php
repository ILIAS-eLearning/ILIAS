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
 ******************************************************************** */

use ILIAS\BookingManager\BookableItem\BookableItemTable;
use ILIAS\BookingManager\BookableItem\BookableItemTableData;

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilBookingObjectGUI: ilPropertyFormGUI, ilBookingProcessWithScheduleGUI, ilBookingProcessWithoutScheduleGUI
 * @ilCtrl_Calls ilBookingObjectGUI: ilBookBulkCreationGUI
 */
class ilBookingObjectGUI
{
    protected \ILIAS\BookingManager\Objects\ObjectsManager $objects_manager;
    protected \ILIAS\BookingManager\Schedule\ScheduleManager $schedule_manager;
    protected ilBookBulkCreationGUI $bulk_creation_gui;
    protected ilObjBookingPool $pool;
    protected \ILIAS\BookingManager\InternalGUIService $gui;
    protected \ILIAS\BookingManager\Access\AccessManager $access;
    protected \ILIAS\BookingManager\StandardGUIRequest $book_request;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilBookingHelpAdapter $help;
    protected ilObjectDataCache $obj_data_cache;
    protected ilObjUser $user;
    protected bool $pool_has_schedule;
    protected ?int $pool_overall_limit;
    protected bool $pool_uses_preferences = false;
    // Is management of objects (create/edit/delete) activated?
    protected bool $management = true;
    // Context object id (e.g. course with booking service activated)
    protected int $context_obj_id;
    protected int $object_id;
    protected string $seed;
    protected string $sseed;
    protected ilObjBookingPoolGUI $pool_gui;
    protected array $rsv_ids = [];
    protected ilAdvancedMDRecordGUI $record_gui;
    protected int $ref_id;

    public function __construct(
        ilObjBookingPoolGUI $a_parent_obj,
        string $seed,
        string $sseed,
        ilBookingHelpAdapter $help,
        int $context_obj_id = 0
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->access = $DIC->bookingManager()->internal()->domain()->access();
        $this->tabs = $DIC->tabs();
        $this->help = $help;
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->user = $DIC->user();

        /** @var ilObjBookingPool $pool */
        $pool = $a_parent_obj->getObject();
        $this->pool = $pool;

        $this->book_request = $DIC
            ->bookingManager()
            ->internal()
            ->gui()
            ->standardRequest();
        $this->gui = $DIC->bookingManager()->internal()->gui();
        $this->schedule_manager = $DIC
            ->bookingManager()
            ->internal()
            ->domain()
            ->schedules($this->pool->getId());

        $this->seed = $seed;
        $this->sseed = $sseed;

        $this->context_obj_id = $context_obj_id;

        $this->pool_gui = $a_parent_obj;
        $this->bulk_creation_gui = $this->gui->objects()
            ->ilBookBulkCreationGUI($this->pool);

        $this->pool_has_schedule =
            ($a_parent_obj->getObject()->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE);
        $this->pool_uses_preferences =
            ($a_parent_obj->getObject()->getScheduleType() === ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES);
        $this->pool_overall_limit = $this->pool_has_schedule
            ? null
            : $a_parent_obj->getObject()->getOverallLimit();

        $this->object_id = $this->book_request->getObjectId();
        $this->ref_id = $this->book_request->getRefId();
        $this->ctrl->saveParameter($this, "object_id");

        $this->rsv_ids = array_map('intval', $this->book_request->getReservationIdsFromString());
        $this->objects_manager = $DIC->bookingManager()->internal()->domain()->objects($this->pool->getId());

        $this->access->validateBookingObjId(
            $this->object_id,
            (int) $this->pool_gui->getObject()?->getId()
        );
    }

    public function activateManagement(bool $a_val): void
    {
        $this->management = $a_val;
    }

    /**
     * Is management activated?
     */
    public function isManagementActivated(): bool
    {
        return $this->management;
    }

    public function getPoolRefId(): int
    {
        return $this->pool_gui->getRefId();
    }

    public function getPoolObjId(): int
    {
        return $this->pool_gui->getObject()->getId();
    }

    /**
     * Has booking pool a schedule?
     */
    public function hasPoolSchedule(): bool
    {
        return ($this->pool_gui->getObject()->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE);
    }

    /**
     * Get booking pool overall limit
     */
    public function getPoolOverallLimit(): ?int
    {
        return $this->hasPoolSchedule()
            ? null
            : $this->pool_gui->getObject()->getOverallLimit();
    }

    public function getPool(): ilObjBookingPool
    {
        return $this->pool;
    }

    public function getBookingGuiService(): \ILIAS\BookingManager\InternalGUIService
    {
        return $this->gui;
    }

    public function getPoolUsesPreferences(): bool
    {
        return $this->pool_uses_preferences;
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass($this);

        switch ($next_class) {
            case "ilpropertyformgui":
                // only case is currently adv metadata internal link in info settings, see #24497
                $form = $this->initForm();
                $this->ctrl->forwardCommand($form);
                break;

            case "ilbookingprocesswithschedulegui":
                if (!$this->pool_uses_preferences) {
                    $ilCtrl->setReturn($this, "render");
                } else {
                    $ilCtrl->setReturn($this, "returnToPreferences");
                }
                /** @var ilObjBookingPool $pool */
                $pool = $this->pool_gui->getObject();
                $process_gui = $this->gui->process()->ilBookingProcessWithScheduleGUI(
                    $pool,
                    $this->object_id,
                    $this->context_obj_id,
                    $this->seed ?? $this->sseed
                );
                $this->ctrl->forwardCommand($process_gui);
                break;

            case "ilbookingprocesswithoutschedulegui":
                if (!$this->pool_uses_preferences) {
                    $ilCtrl->setReturn($this, "render");
                } else {
                    $ilCtrl->setReturn($this, "returnToPreferences");
                }
                /** @var ilObjBookingPool $pool */
                $pool = $this->pool_gui->getObject();
                $process_gui = $this->gui->process()->ilBookingProcessWithoutScheduleGUI(
                    $pool,
                    $this->object_id,
                    $this->context_obj_id
                );
                $this->ctrl->forwardCommand($process_gui);
                break;

            case strtolower(ilBookBulkCreationGUI::class):
                $this->ctrl->setReturn($this, "");
                $this->ctrl->forwardCommand($this->bulk_creation_gui);
                break;

            default:
                $cmd = $ilCtrl->getCmd("render");
                $this->$cmd();
                break;
        }
    }

    protected function showNoScheduleMessage(): void
    {
        $this->pool_gui->showNoScheduleMessage();
    }

    protected function returnToPreferences(): void
    {
        $this->ctrl->redirectByClass("ilBookingPreferencesGUI");
    }

    /**
     * Render list of booking objects
     */
    public function render(): void
    {
        $this->showNoScheduleMessage();
        if (\ilSession::has('book_bulk_flash')) {
            $this->tpl->setOnScreenMessage(
                (string) (\ilSession::get('book_bulk_flash_type') ?? 'info'),
                (string) \ilSession::get('book_bulk_flash'),
                true
            );
            \ilSession::clear('book_bulk_flash');
            \ilSession::clear('book_bulk_flash_type');
        }

        $tpl = $this->tpl;
        $lng = $this->lng;
        $bar = "";

        if ($this->isManagementActivated() && $this->access->canManageObjects($this->getPoolRefId())) {
            $bar = new ilToolbarGUI();
            $bar->addButton($lng->txt('book_add_object'), $this->ctrl->getLinkTarget($this, 'create'));

            // bulk creation
            $this->bulk_creation_gui->modifyToolbar($bar);
            $this->addTableWeekViewControlToToolbar($bar);
            $bar = $bar->getHTML();
        } elseif ($this->hasPoolSchedule() && $this->getAccessHandler()->checkAccess('read', '', $this->getPoolRefId())) {
            $bar = new ilToolbarGUI();
            $this->addTableWeekViewControlToToolbar($bar);
            $bar = $bar->getHTML();
        }

        $tpl->setPermanentLink('book', $this->getPoolRefId());
        $bookable = BookableItemTable::forObjectList($this);
        $tpl->setContent(
            $bar . $this->gui->ui()->renderer()->render(
                $bookable->getComponents($bookable->getActionUrlBuilderForExecuteTableAction())
            )
        );
    }

    public function executeTableAction(): void
    {
        $this->showNoScheduleMessage();
        $t = BookableItemTable::forObjectList($this);
        $t->execute($t->getActionUrlBuilderForExecuteTableAction());
        $this->ctrl->redirect($this, 'render');
    }

    protected function getAccessHandler(): \ilAccessHandler
    {
        global $DIC;
        return $DIC->access();
    }

    protected function addTableWeekViewControlToToolbar(ilToolbarGUI $bar): void
    {
        if (!$this->hasPoolSchedule()) {
            return;
        }
        if (!$this->getAccessHandler()->checkAccess('read', '', $this->getPoolRefId())) {
            return;
        }
        $bar->addSeparator();
        $table_link = $this->ctrl->getLinkTarget($this, "render");
        $week_link = $this->ctrl->getLinkTargetByClass("ilBookingProcessWithScheduleGUI", "week");
        $bar->addComponent(
            $this->gui->ui()->factory()->viewControl()->mode(
                [
                    $this->lng->txt("book_table") => $table_link,
                    $this->lng->txt("book_week") => $week_link
                ],
                $this->lng->txt("book_view")
            )->withActive($this->lng->txt("book_table"))
        );
    }

    public function applyFilter(): void
    {
        $this->render();
    }

    public function resetFilter(): void
    {
        $this->render();
    }

    /**
     * Render creation form
     */
    public function create(?ilPropertyFormGUI $a_form = null): void
    {
        if (!$this->access->canManageObjects($this->ref_id)) {
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
    public function edit(?ilPropertyFormGUI $a_form = null): void
    {
        if (!$this->access->canManageObjects($this->ref_id)) {
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

    protected function setHelpId(string $a_id): void
    {
        $this->help->setHelpId($a_id);
    }

    /**
     * Build property form
     */
    public function initForm(
        string $a_mode = "create",
        ?int $id = null
    ): ilPropertyFormGUI {
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
        $desc->setMaxNumOfChars(1000);
        $form_gui->addItem($desc);

        $file = new ilFileInputGUI($lng->txt("book_additional_info_file"), "file");
        $file->setAllowDeletion(true);
        $form_gui->addItem($file);

        $nr = new ilNumberInputGUI($lng->txt("booking_nr_of_items"), "items");
        $nr->setRequired(true);
        $nr->setSize(3);
        $nr->setMaxLength(3);
        $nr->setSuffix($lng->txt("book_booking_objects"));
        $form_gui->addItem($nr);

        if ($this->hasPoolSchedule()) {
            $options = array();
            foreach ($this->schedule_manager->getScheduleList() as $schedule_id => $schedule_title) {
                $options[$schedule_id] = $schedule_title;
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
        $pfile->setAllowDeletion(true);
        $form_gui->addItem($pfile);

        // #18214 - should also work for new objects
        $this->record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_EDITOR,
            "book",
            $this->getPoolObjId(),
            "bobj",
            (int) $id
        );
        $this->record_gui->setPropertyForm($form_gui);
        $this->record_gui->parse();

        if ($a_mode === "edit") {
            $form_gui->setTitle($lng->txt("book_edit_object"));

            $item = new ilHiddenInputGUI('object_id');
            $item->setValue($id);
            $form_gui->addItem($item);

            $obj = new ilBookingObject($id);
            $title->setValue($obj->getTitle());
            $desc->setValue($obj->getDescription());
            $nr->setValue($obj->getNrOfItems());
            $pdesc->setValue($obj->getPostText());
            $file->setValue($this->objects_manager->getObjectInfoFilename($id));
            $pfile->setValue($this->objects_manager->getBookingInfoFilename($id));

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

    public function save(): void
    {
        if (!$this->access->canManageObjects($this->ref_id)) {
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
                $obj = new ilBookingObject();
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
                    $this->objects_manager->importObjectInfoFromLegacyUpload($obj->getId(), $_FILES["file"]);
                } elseif ($file !== null && $file->getDeletionFlag()) {
                    $this->objects_manager->deleteObjectInfo($obj->getId());
                }

                $pfile = $form->getItemByPostVar("post_file");
                if ($_FILES["post_file"]["tmp_name"]) {
                    $this->objects_manager->importBookingInfoFromLegacyUpload($obj->getId(), $_FILES["post_file"]);
                } elseif ($pfile !== null && $pfile->getDeletionFlag()) {
                    $this->objects_manager->deleteBookingInfo($obj->getId());
                }

                $obj->update();

                if ($this->record_gui) {
                    $this->record_gui->writeEditForm(null, $obj->getId());
                }

                $this->tpl->setOnScreenMessage('success', $lng->txt("book_object_added"), true);
                $ilCtrl->redirect($this, "render");
            }
        }

        $form->setValuesByPost();
        $this->create($form);
    }

    public function update(): void
    {
        if (!$this->access->canManageObjects($this->ref_id)) {
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
                    $this->objects_manager->importObjectInfoFromLegacyUpload($obj->getId(), $_FILES["file"]);
                } elseif ($file !== null && $file->getDeletionFlag()) {
                    $this->objects_manager->deleteObjectInfo($obj->getId());
                }

                $pfile = $form->getItemByPostVar("post_file");
                if ($_FILES["post_file"]["tmp_name"]) {
                    $this->objects_manager->importBookingInfoFromLegacyUpload($obj->getId(), $_FILES["post_file"]);
                } elseif ($pfile !== null && $pfile->getDeletionFlag()) {
                    $this->objects_manager->deleteBookingInfo($obj->getId());
                }

                if ($this->hasPoolSchedule()) {
                    $obj->setScheduleId($form->getInput("schedule"));
                }

                $obj->update();

                if ($this->record_gui) {
                    $this->record_gui->writeEditForm();
                }

                $this->tpl->setOnScreenMessage('success', $lng->txt("book_object_updated"), true);
                $ilCtrl->redirect($this, "edit");
            }
        }

        $form->setValuesByPost();
        $this->edit($form);
    }

    public function confirmDelete(): void
    {
        if (!$this->access->canManageObjects($this->ref_id)) {
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

    public function delete(): void
    {
        if (!$this->access->canManageObjects($this->ref_id)) {
            return;
        }

        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $obj = new ilBookingObject($this->object_id);
        $obj->deleteReservationsAndCalEntries($this->object_id);
        $obj->delete();

        $this->tpl->setOnScreenMessage('success', $lng->txt('book_object_deleted'), true);
        $ilCtrl->setParameter($this, 'object_id', "");
        $ilCtrl->redirect($this, 'render');
    }


    public function deliverInfo(): void
    {
        $id = $this->object_id;
        if (!$id) {
            return;
        }

        $this->objects_manager->deliverObjectInfo($id);
    }

    /**
     * Legacy entry point: bulk selection now opens an async modal (see outputBulkBookModal()).
     */
    public function bulkBookForm(): void
    {
        $this->ctrl->redirect($this, 'render');
    }

    /**
     * Renders the bulk-booking modal (async response for data table action).
     *
     * @param list<string> $row_ids
     */
    public function outputBulkBookModal(array $row_ids): void
    {
        $this->lng->loadLanguageModule('book');
        $f = $this->gui->ui()->factory();
        if (!$this->access->canManageOwnReservations($this->getPoolRefId())) {
            $this->gui->send(
                $this->gui->ui()->renderer()->render(
                    $f->messageBox()->failure($this->lng->txt('no_permission'))
                )
            );
        }
        if ($row_ids === []) {
            $this->lng->loadLanguageModule('common');
            $this->gui->send(
                $this->gui->ui()->renderer()->render(
                    $f->messageBox()->info($this->lng->txt('no_checkbox'))
                )
            );
        }
        $selected = $row_ids;
        $available = $this->filterBulkRowIdsToBookable($row_ids);
        if ($available === []) {
            $this->gui->send(
                $this->gui->ui()->renderer()->render(
                    $f->messageBox()->info($this->lng->txt('book_bulk_all_unavailable'))
                )
            );
        }
        $skipped = count($selected) - count($available);
        $form = $this->buildBulkBookForm($available);
        $header = $skipped > 0
            ? $f->messageBox()->info(
                sprintf($this->lng->txt('book_bulk_omitted_unavailable'), (string) $skipped)
            )
            : null;
        $this->sendBulkBookModal($form, $header);
    }

    /**
     * Async bulk-booking modal: optional UI message box above the form (same as modal+form in Repository GUI).
     *
     * @param \ILIAS\UI\Component\Component|null $header e.g. messageBox()->info(…)
     */
    protected function sendBulkBookModal(
        \ILIAS\Repository\Form\FormAdapterGUI $form,
        ?\ILIAS\UI\Component\Component $header = null
    ): void {
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        if ($this->ctrl->isAsynch()) {
            $form = $form->asyncModal();
        } else {
            $form = $form->syncModal();
        }
        $this->lng->loadLanguageModule('common');
        $form_std = $form->getForm();
        $async = $form->isSentAsync() ? 'true' : 'false';
        $on_form_submit_click = "il.repository.ui.submitModalForm(event,$async); return false;";
        $button = $f->button()->standard(
            $form->getSubmitLabel(),
            '#'
        )->withOnLoadCode(function ($id) use ($on_form_submit_click) {
            return "$('#$id').click(function(event) {" . $on_form_submit_click . "});";
        });
        $modal = $f->modal()->roundtrip(
            $this->lng->txt('book_confirm_booking_schedule_number_of_objects'),
            $header,
            $form_std->getInputs(),
            $form_std->getPostURL()
        )
            ->withActionButtons([$button])
            ->withCancelButtonLabel($this->lng->txt('cancel'))
            ->withAdditionalOnLoadCode(function ($id) {
                return "il.repository.ui.initModal('$id');";
            });
        $this->gui->send($r->renderAsync($modal));
    }

    /**
     * Group bulk row IDs by object: each group is one bookable item, sorted by item title; within
     * a fixed-schedule object, time slots are sorted by start time.
     *
     * @param list<string> $row_ids
     * @return list<array{object_id: int, is_slot: bool, row_ids: list<string>}>
     */
    protected function groupBulkRowIdsByObject(array $row_ids): array
    {
        $schedule_by_obj = [];
        $nosc = [];
        foreach (array_values($row_ids) as $row_id) {
            $p = BookableItemTableData::parseRowIdForBulk((string) $row_id);
            if ($p === null) {
                continue;
            }
            if (!empty($p['is_slot']) && $p['from'] !== null && $p['to'] !== null) {
                $oid = (int) $p['object_id'];
                if (!isset($schedule_by_obj[$oid])) {
                    $schedule_by_obj[$oid] = [];
                }
                $schedule_by_obj[$oid][] = (string) $row_id;
            } else {
                $nosc[] = (string) $row_id;
            }
        }
        foreach ($schedule_by_obj as $oid => &$list) {
            usort(
                $list,
                static function (string $a, string $b): int {
                    $pa = BookableItemTableData::parseRowIdForBulk($a);
                    $pb = BookableItemTableData::parseRowIdForBulk($b);
                    if ($pa === null || $pb === null) {
                        return 0;
                    }
                    return ((int) ($pa['from'] ?? 0)) <=> ((int) ($pb['from'] ?? 0));
                }
            );
        }
        unset($list);

        $groups = [];
        foreach (array_keys($schedule_by_obj) as $oid) {
            $groups[] = [
                'object_id' => (int) $oid,
                'is_slot' => true,
                'row_ids' => $schedule_by_obj[$oid],
            ];
        }
        foreach ($nosc as $row_id) {
            $p = BookableItemTableData::parseRowIdForBulk($row_id);
            if ($p === null) {
                continue;
            }
            $groups[] = [
                'object_id' => (int) $p['object_id'],
                'is_slot' => false,
                'row_ids' => [(string) $row_id],
            ];
        }
        usort(
            $groups,
            function (array $a, array $b): int {
                $oa = new ilBookingObject($a['object_id']);
                $ob = new ilBookingObject($b['object_id']);
                $c = strcasecmp($oa->getTitle(), $ob->getTitle());
                if ($c !== 0) {
                    return $c;
                }
                return $a['object_id'] <=> $b['object_id'];
            }
        );
        return $groups;
    }

    /**
     * @param list<string> $row_ids
     * @return list<string>
     */
    protected function flattenBulkRowIdGroups(array $row_ids): array
    {
        $flat = [];
        foreach ($this->groupBulkRowIdsByObject($row_ids) as $g) {
            foreach ($g['row_ids'] as $rid) {
                $flat[] = (string) $rid;
            }
        }
        return $flat;
    }

    /**
     * @param list<string> $row_ids
     */
    protected function buildBulkBookForm(
        array $row_ids
    ): \ILIAS\Repository\Form\FormAdapterGUI {
        global $DIC;
        $this->lng->loadLanguageModule('book');
        $reservation = $DIC->bookingManager()->internal()->domain()->reservations();

        $ordered_ids = $this->flattenBulkRowIdGroups($row_ids);
        $ids_json = (string) json_encode($ordered_ids, JSON_UNESCAPED_SLASHES);
        $form = $this->gui
            ->form([self::class], 'bulkBookConfirmed', $this->lng->txt('save'))
            ->asyncModal()
            ->hidden('bulk_ids', $ids_json)
            ->hidden('origin_cmd', 'render');
        $msg_label = (string) $this->lng->txt('book_message');
        $msg_by = (string) $this->lng->txt('book_bulk_message_byline');

        foreach ($this->groupBulkRowIdsByObject($row_ids) as $g) {
            $oid = (int) $g['object_id'];
            $obj = new ilBookingObject($oid);
            $item_title = $obj->getTitle();
            if ($g['is_slot']) {
                $section_info = (string) $this->lng->txt('book_confirm_booking_schedule_number_of_objects_info');
                $form = $form->section('obj_' . $oid, (string) $item_title, $section_info);
                foreach ($g['row_ids'] as $i => $row_id) {
                    $p = BookableItemTableData::parseRowIdForBulk($row_id);
                    if ($p === null || empty($p['is_slot']) || $p['from'] === null || $p['to'] === null) {
                        continue;
                    }
                    $from = (int) $p['from'];
                    $to_disp = (int) $p['to'] - 1;
                    $counter = $reservation->getAvailableNr($oid, $from, $to_disp);
                    $period = ilDatePresentation::formatPeriod(
                        new ilDateTime($from, IL_CAL_UNIX),
                        new ilDateTime($to_disp, IL_CAL_UNIX)
                    );
                    $form = $form->number("nr_{$oid}_{$i}", (string) $period, '', 1, 0, $counter);
                }
                $form = $form->textarea(
                    'message_' . $oid,
                    $msg_label,
                    $msg_by
                );
            } else {
                $form = $form->section('nosc_' . $oid, (string) $item_title, '');
                $form = $form->textarea(
                    'message_' . $oid,
                    $msg_label,
                    $msg_by
                );
            }
        }

        return $form;
    }

    /**
     * @param list<string> $row_ids
     * @return list<string>
     */
    protected function filterBulkRowIdsToBookable(array $row_ids): array
    {
        $out = [];
        foreach ($row_ids as $row_id) {
            if (!$this->isBulkRowIdBookableNow((string) $row_id)) {
                continue;
            }
            $out[] = (string) $row_id;
        }
        return $out;
    }

    protected function isBulkRowIdBookableNow(string $row_id): bool
    {
        $p = BookableItemTableData::parseRowIdForBulk($row_id);
        if ($p === null) {
            return false;
        }
        if (!empty($p['is_slot']) && $p['from'] !== null && $p['to'] !== null) {
            $check = \ilBookingReservation::getAvailableObject(
                [(int) $p['object_id']],
                (int) $p['from'],
                (int) $p['to'] - 1,
                false,
                true
            );

            return array_sum($check) > 0;
        }
        return \ilBookingReservation::numAvailableFromObjectNoSchedule((int) $p['object_id']) >= 1;
    }

    /**
     * Hidden fields use {@see \ILIAS\Repository\Form\FormAdapterGUI::hidden} with dedicated names, so the
     * POST key is the literal string "form/bulk_ids" (UI groups use slash-separated paths, not form[bulk_ids]).
     * Without dedicated names, the first hidden was "form/input_0".
     */
    protected function getBulkRowIdsFromRequestBody(array $body): array
    {
        $candidates = [];
        foreach (
            [
                'bulk_ids',
                'form/bulk_ids',
                'form/input_0',
            ] as $k
        ) {
            if (isset($body[$k]) && $body[$k] !== '' && $body[$k] !== null) {
                $candidates[] = (string) $body[$k];
            }
        }
        if (isset($body['form']) && is_array($body['form']) && array_key_exists('bulk_ids', $body['form'])) {
            $candidates[] = (string) $body['form']['bulk_ids'];
        }
        foreach ($candidates as $raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && $this->isBulkBookingRowIdJsonList($decoded)) {
                return $decoded;
            }
        }

        return $this->findBulkBookingRowIdsJsonInRequestArray($body);
    }

    /**
     * @param list<mixed> $decoded
     */
    protected function isBulkBookingRowIdJsonList(array $decoded): bool
    {
        if ($decoded === []) {
            return false;
        }
        foreach ($decoded as $id) {
            if (!is_string($id) || !str_starts_with($id, 'bobj-')) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return list<string>
     */
    protected function findBulkBookingRowIdsJsonInRequestArray(array $body): array
    {
        foreach ($body as $v) {
            if (!is_string($v) || $v === '' || $v[0] !== '[') {
                continue;
            }
            $decoded = json_decode($v, true);
            if (is_array($decoded) && $this->isBulkBookingRowIdJsonList($decoded)) {
                return $decoded;
            }
        }
        foreach ($body as $v) {
            if (!is_array($v)) {
                continue;
            }
            foreach ($v as $vv) {
                if (!is_string($vv) || $vv === '' || $vv[0] !== '[') {
                    continue;
                }
                $decoded = json_decode($vv, true);
                if (is_array($decoded) && $this->isBulkBookingRowIdJsonList($decoded)) {
                    return $decoded;
                }
            }
        }
        return [];
    }

    public function bulkBookConfirmed(): void
    {
        global $DIC;
        $this->lng->loadLanguageModule('book');
        if (!$this->access->canManageOwnReservations($this->getPoolRefId())) {
            $this->ctrl->redirect($this, 'render');
        }
        $body = (array) $DIC->http()->request()->getParsedBody();
        $row_ids = $this->getBulkRowIdsFromRequestBody($body);
        if ($row_ids === []) {
            $this->gui->send(
                $this->gui->ui()->renderer()->render(
                    $this->gui->ui()->factory()->messageBox()->failure(
                        $this->lng->txt('book_reservation_failed')
                    )
                )
            );
        }
        $form = $this->buildBulkBookForm($row_ids);
        if (!$form->isValid()) {
            $this->sendBulkBookModal($form);
        }
        $data_ids = $form->getData('bulk_ids');
        $parsed_ids = is_string($data_ids) ? json_decode($data_ids, true) : null;
        if (!is_array($parsed_ids) || $parsed_ids === []) {
            $this->sendBulkBookModal($this->buildBulkBookForm($row_ids));
        }
        $process = $DIC->bookingManager()->internal()->domain()->process();
        $ok = 0;
        $skip = 0;
        $uid = $this->user->getId();
        foreach ($this->groupBulkRowIdsByObject($parsed_ids) as $g) {
            $oid = (int) $g['object_id'];
            $msg = (string) ($form->getData('message_' . $oid) ?? '');
            if ($g['is_slot']) {
                foreach ($g['row_ids'] as $i => $row_id) {
                    $row_id = (string) $row_id;
                    $p = BookableItemTableData::parseRowIdForBulk($row_id);
                    if ($p === null) {
                        $skip++;
                        continue;
                    }
                    if (!$this->access->canManageReservationForUser($this->getPoolRefId(), $uid)) {
                        $skip++;
                        continue;
                    }
                    if (empty($p['is_slot']) || $p['from'] === null || $p['to'] === null) {
                        $skip++;
                        continue;
                    }
                    $check = \ilBookingReservation::getAvailableObject(
                        [$p['object_id']],
                        (int) $p['from'],
                        (int) $p['to'] - 1,
                        false,
                        true
                    );
                    if (!array_sum($check)) {
                        $skip++;
                        continue;
                    }
                    $nr = (int) ($form->getData('nr_' . $oid . '_' . $i) ?? 0);
                    if ($nr < 0) {
                        $skip++;
                        continue;
                    }
                    if ($nr === 0) {
                        continue;
                    }
                    $booked = $process->bookAvailableObjects(
                        (int) $p['object_id'],
                        $uid,
                        $uid,
                        (int) $this->context_obj_id,
                        (int) $p['from'],
                        (int) $p['to'],
                        0,
                        $nr,
                        null,
                        $msg
                    );
                    if ($booked === []) {
                        $skip++;
                    } else {
                        $ok += count($booked);
                    }
                }
            } else {
                $row_id = (string) ($g['row_ids'][0] ?? '');
                $p = BookableItemTableData::parseRowIdForBulk($row_id);
                if ($p === null) {
                    $skip++;
                    continue;
                }
                if (!$this->access->canManageReservationForUser($this->getPoolRefId(), $uid)) {
                    $skip++;
                    continue;
                }
                if (\ilBookingReservation::numAvailableFromObjectNoSchedule($oid) < 1) {
                    $skip++;
                    continue;
                }
                $process->bookSingle(
                    $oid,
                    $uid,
                    $uid,
                    (int) $this->context_obj_id,
                    null,
                    null,
                    null,
                    $msg
                );
                $ok++;
            }
        }
        $message = sprintf($this->lng->txt('book_bulk_result'), (string) $ok, (string) $skip);
        \ilSession::set('book_bulk_flash', $message);
        \ilSession::set('book_bulk_flash_type', $ok > 0 ? 'success' : 'info');
        $back = $this->ctrl->getLinkTarget($this, 'render');
        $this->gui->send(
            "<script>window.location.href = " . json_encode($back, JSON_HEX_TAG | JSON_HEX_AMP) . ";</script>"
        );
    }
}
