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

use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\BookableItem\Table\BookableItemTable;
use ILIAS\BookingManager\BookableItem\Table\BookableItemWithoutScheduleTable;
use ILIAS\BookingManager\BookableItem\Table\BookableItemWithScheduleTable;
use ILIAS\BookingManager\BookingProcess\BookingProcessManager;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\InternalGUIService;
use ILIAS\BookingManager\Objects\ObjectsManager;
use ILIAS\BookingManager\Settings\Settings;
use ILIAS\BookingManager\Schedule\ScheduleManager;
use ILIAS\BookingManager\StandardGUIRequest;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Component\Modal\Modal;

/**
 * @ilCtrl_Calls ilBookingObjectGUI: ilPropertyFormGUI, ilBookingProcessWithScheduleGUI, ilBookingProcessWithoutScheduleGUI
 * @ilCtrl_Calls ilBookingObjectGUI: ilBookBulkCreationGUI
 */
class ilBookingObjectGUI
{
    protected ObjectsManager $objects_manager;
    protected ScheduleManager $schedule_manager;
    protected ilBookBulkCreationGUI $bulk_creation_gui;
    protected ilObjBookingPool $pool;
    protected InternalGUIService $gui;
    protected AccessManager $access;
    protected StandardGUIRequest $book_request;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilObjectDataCache $obj_data_cache;
    protected ilObjUser $user;
    protected Factory $ui_factory;
    protected Renderer $ui_renderer;
    protected ilUIService $ui_service;
    protected Refinery $refinery;
    protected HttpService $http;
    protected BookingProcessManager $process_manager;
    protected DataFactory $data_factory;
    protected Settings $settings;
    protected bool $pool_has_schedule;
    protected ?int $pool_overall_limit;
    protected bool $pool_uses_preferences = false;
    // Is management of objects (create/edit/delete) activated?
    protected bool $management = true;
    protected int $object_id;
    protected array $rsv_ids = [];
    protected ?ilAdvancedMDRecordGUI $record_gui = null;
    protected int $ref_id;

    public function __construct(
        protected ilObjBookingPoolGUI $a_parent_obj,
        protected string $seed,
        protected string $sseed,
        protected ilBookingHelpAdapter $help,
        // Context object id (e.g. course with booking service activated)
        protected int $context_obj_id = 0
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->access = $DIC
            ->bookingManager()
            ->internal()
            ->domain()
            ->access();
        $this->tabs = $DIC->tabs();
        $this->obj_data_cache = $DIC['ilObjDataCache'];
        $this->user = $DIC->user();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ui_service = $DIC->uiService();
        $this->refinery = $DIC->refinery();
        $this->http = new HttpService($DIC->http(), $this->refinery);
        $this->process_manager = $DIC->bookingManager()->internal()->domain()->process();
        $this->data_factory = new DataFactory();

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
        $this->settings = $DIC
            ->bookingManager()
            ->internal()
            ->domain()
            ->bookingSettings()
            ->getByObjId($this->pool->getId());

        $this->bulk_creation_gui = $this->gui->objects()->ilBookBulkCreationGUI($this->pool);

        $schedule_type = $a_parent_obj->getObject()->getScheduleType();
        $this->pool_has_schedule = $schedule_type === ilObjBookingPool::TYPE_FIX_SCHEDULE;
        $this->pool_uses_preferences = $schedule_type === ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES;
        $this->pool_overall_limit = !$this->pool_has_schedule ? $a_parent_obj->getObject()->getOverallLimit() : null;

        $this->object_id = $this->book_request->getObjectId();
        $this->ref_id = $this->book_request->getRefId();
        $this->ctrl->saveParameter($this, 'object_id');

        $this->rsv_ids = array_map('intval', $this->book_request->getReservationIdsFromString());
        $this->objects_manager = $DIC
            ->bookingManager()
            ->internal()
            ->domain()
            ->objects($this->pool->getId());

        $this->access->validateBookingObjId($this->object_id, (int) $a_parent_obj->getObject()?->getId());
    }

    public function activateManagement(bool $a_val): void
    {
        $this->management = $a_val;
    }

    public function isManagementActivated(): bool
    {
        return $this->management;
    }

    protected function getPoolRefId(): int
    {
        return $this->a_parent_obj->getRefId();
    }

    protected function getPoolObjId(): int
    {
        return $this->pool->getId();
    }

    protected function hasPoolSchedule(): bool
    {
        return $this->pool_has_schedule;
    }

    protected function getPoolOverallLimit(): ?int
    {
        return $this->pool_overall_limit;
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        switch (strtolower($this->ctrl->getNextClass($this))) {
            case strtolower(ilPropertyFormGUI::class):
                // only case is currently adv metadata internal link in info settings, see #24497
                $this->ctrl->forwardCommand($this->initForm());
                break;

            case strtolower(ilBookingProcessWithScheduleGUI::class):
                $this->ctrl->setReturn($this, $this->pool_uses_preferences ? 'returnToPreferences' : 'render');
                $process_gui = $this->gui->process()->ilBookingProcessWithScheduleGUI(
                    $this->pool,
                    $this->object_id,
                    $this->context_obj_id,
                    $this->seed ?? $this->sseed
                );
                $this->ctrl->forwardCommand($process_gui);
                break;

            case strtolower(ilBookingProcessWithoutScheduleGUI::class):
                $this->ctrl->setReturn($this, $this->pool_uses_preferences ? 'returnToPreferences' : 'render');
                $process_gui = $this->gui->process()->ilBookingProcessWithoutScheduleGUI(
                    $this->pool,
                    $this->object_id,
                    $this->context_obj_id
                );
                $this->ctrl->forwardCommand($process_gui);
                break;

            case strtolower(ilBookBulkCreationGUI::class):
                $this->ctrl->setReturn($this, '');
                $this->ctrl->forwardCommand($this->bulk_creation_gui);
                break;

            default:
                $cmd = $this->ctrl->getCmd('render');
                $this->$cmd();
                break;
        }
    }

    protected function returnToPreferences(): void
    {
        $this->ctrl->redirectByClass(ilBookingPreferencesGUI::class);
    }

    public function render(?Modal $modal = null): void
    {
        $this->a_parent_obj->showNoScheduleMessage();

        $bar = '';

        if ($this->isManagementActivated()) {
            $bar = new ilToolbarGUI();
            if ($this->access->canManageObjects($this->getPoolRefId())) {
                $bar->addButton($this->lng->txt('book_add_object'), $this->ctrl->getLinkTarget($this, 'create'));

                // bulk creation
                $this->bulk_creation_gui->modifyToolbar($bar);
            }

            if ($bar->getItems() !== []) {
                $bar->addSeparator();
            }

            if ($this->hasPoolSchedule()) {
                $mode_control = $this->gui->ui()->factory()->viewControl()->mode(
                    [
                       $this->lng->txt('book_table') => $this->ctrl->getLinkTarget($this, ''),
                       $this->lng->txt('book_week') => $this->ctrl->getLinkTargetByClass(ilBookingProcessWithScheduleGUI::class, 'week')
                    ],
                    $this->lng->txt('book_view')
                );
                $bar->addComponent($mode_control);
            }

            $bar = $bar->getHTML();
        }

        $components = $this->getTableComponents();
        if ($modal !== null) {
            $components[] = $modal;
        }

        $this->tpl->setPermanentLink('book', $this->getPoolRefId());
        $this->tpl->setContent($bar . $this->ui_renderer->render($components));
    }

    private function getTable(): BookableItemTable
    {
        if ($this->hasPoolSchedule()) {
            return new BookableItemWithScheduleTable(
                $this->ui_factory,
                $this->ui_renderer,
                $this->lng,
                $this->http,
                $this->ui_service,
                $this->ctrl,
                $this->tpl,
                $this->refinery,
                $this->access,
                $this->pool,
                $this->process_manager,
                $this->settings,
                $this->user,
                $this->getPoolRefId(),
                $this->isManagementActivated(),
                $this->context_obj_id,
            );
        }

        return new BookableItemWithoutScheduleTable(
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->http,
            $this->ui_service,
            $this->ctrl,
            $this->tpl,
            $this->refinery,
            $this->access,
            $this->pool,
            $this->process_manager,
            $this->settings,
            $this->user,
            $this->getPoolRefId(),
            $this->isManagementActivated(),
            $this->context_obj_id,
        );
    }

    private function getTableComponents(): array
    {
        return $this->getTable()->getComponents($this->getURLBuilder());
    }

    public function executeTableAction(): void
    {
        $modal = $this->getTable()->execute($this->getURLBuilder());
        if ($modal !== null) {
            $this->render($modal);
            return;
        }
        $this->ctrl->redirectByClass(self::class, 'render');
    }

    private function getURLBuilder(): URLBuilder
    {
        return new URLBuilder(
            $this->data_factory->uri(ILIAS_HTTP_PATH . "/{$this->ctrl->getLinkTarget($this, 'executeTableAction')}")
        );
    }

    public function create(?ilPropertyFormGUI $a_form = null): void
    {
        if (!$this->access->canManageObjects($this->ref_id)) {
            return;
        }

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'render'));

        $this->setHelpId('create');

        $a_form ??= $this->initForm();
        $this->tpl->setContent($a_form->getHTML());
    }

    public function edit(?ilPropertyFormGUI $a_form = null): void
    {
        if (!$this->access->canManageObjects($this->ref_id)) {
            return;
        }

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'render'));

        $this->setHelpId('edit');

        $a_form ??= $this->initForm('edit', $this->object_id);
        $this->tpl->setContent($a_form->getHTML());
    }

    protected function setHelpId(string $a_id): void
    {
        $this->help->setHelpId($a_id);
    }

    public function initForm(string $a_mode = 'create', ?int $id = null): ilPropertyFormGUI
    {
        $form_gui = new ilPropertyFormGUI();

        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setRequired(true);
        $title->setSize(40);
        $title->setMaxLength(120);
        $form_gui->addItem($title);

        $desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');
        $desc->setCols(70);
        $desc->setRows(15);
        $desc->setMaxNumOfChars(1000);
        $form_gui->addItem($desc);

        $file = new ilFileInputGUI($this->lng->txt('book_additional_info_file'), 'file');
        $file->setAllowDeletion(true);
        $form_gui->addItem($file);

        $nr = new ilNumberInputGUI($this->lng->txt('booking_nr_of_items'), 'items');
        $nr->setRequired(true);
        $nr->setSize(3);
        $nr->setMaxLength(3);
        $nr->setSuffix($this->lng->txt('book_booking_objects'));
        $form_gui->addItem($nr);

        $schedule = null;
        if ($this->hasPoolSchedule()) {
            $options = array_map(
                static fn(string $schedule_title): string => $schedule_title,
                $this->schedule_manager->getScheduleList()
            );
            $schedule = new ilSelectInputGUI($this->lng->txt('book_schedule'), 'schedule');
            $schedule->setRequired(true);
            $schedule->setOptions($options);
            $form_gui->addItem($schedule);
        }

        $post = new ilFormSectionHeaderGUI();
        $post->setTitle($this->lng->txt('book_post_booking_information'));
        $form_gui->addItem($post);

        $pdesc = new ilTextAreaInputGUI($this->lng->txt('book_post_booking_text'), 'post_text');
        $pdesc->setCols(70);
        $pdesc->setRows(15);
        $pdesc->setInfo($this->lng->txt('book_post_booking_text_info'));
        $form_gui->addItem($pdesc);

        $pfile = new ilFileInputGUI($this->lng->txt('book_post_booking_file'), 'post_file');
        $pfile->setAllowDeletion(true);
        $form_gui->addItem($pfile);

        // #18214 - should also work for new objects
        $this->record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_EDITOR,
            'book',
            $this->getPoolObjId(),
            'bobj',
            (int) $id
        );
        $this->record_gui->setPropertyForm($form_gui);
        $this->record_gui->parse();

        if ($a_mode === 'edit') {
            $form_gui->setTitle($this->lng->txt('book_edit_object'));

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

            $schedule?->setValue($obj->getScheduleId());

            $form_gui->addCommandButton('update', $this->lng->txt('save'));
        } else {
            $form_gui->setTitle($this->lng->txt('book_add_object'));
            $form_gui->addCommandButton('save', $this->lng->txt('save'));
            $form_gui->addCommandButton('render', $this->lng->txt('cancel'));
        }

        $form_gui->setFormAction($this->ctrl->getFormAction($this));
        return $form_gui;
    }

    public function save(): void
    {
        $this->handleForm(true);
    }

    public function update(): void
    {
        $this->handleForm(false);
    }

    private function handleForm(bool $create): void
    {
        if (!$this->access->canManageObjects($this->ref_id)) {
            return;
        }

        $form = $create ? $this->initForm() : $this->initForm('edit', $this->object_id);

        if (
            $form->checkInput()
            && (
                !$this->record_gui instanceof ilAdvancedMDRecordGUI
                || $this->record_gui->importEditFormPostValues()
            )
        ) {
            $obj = new ilBookingObject($this->object_id);
            $obj->setTitle($form->getInput('title'));
            $obj->setDescription($form->getInput('desc'));
            $obj->setNrOfItems($form->getInput('items'));
            $obj->setPostText($form->getInput('post_text'));

            if ($this->hasPoolSchedule()) {
                $obj->setScheduleId($form->getInput('schedule'));
            }

            if ($create) {
                $obj->setPoolId($this->getPoolObjId());
                $obj->save();
            } else {
                $obj->update();
            }

            if ($_FILES['file']['tmp_name']) {
                $this->objects_manager->importObjectInfoFromLegacyUpload($obj->getId(), $_FILES['file']);
            } elseif ($form->getItemByPostVar('file')?->getDeletionFlag()) {
                $this->objects_manager->deleteObjectInfo($obj->getId());
            }

            if ($_FILES['post_file']['tmp_name']) {
                $this->objects_manager->importBookingInfoFromLegacyUpload($obj->getId(), $_FILES['post_file']);
            } elseif ($form->getItemByPostVar('post_file')?->getDeletionFlag()) {
                $this->objects_manager->deleteBookingInfo($obj->getId());
            }

            $obj->update();

            $create
                ? $this->record_gui?->writeEditForm(null, $obj->getId())
                : $this->record_gui?->writeEditForm();

            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt($create ? 'book_object_added' : 'book_object_updated'),
                true
            );
            $this->ctrl->redirect($this, $create ? 'render' : 'edit');
        }

        $form->setValuesByPost();
        $create ? $this->create($form) : $this->edit($form);
    }

    public function confirmDelete(): void
    {
        if (!$this->access->canManageObjects($this->ref_id)) {
            return;
        }

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'render'));

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('book_confirm_delete'));

        $type = new ilBookingObject($this->object_id);
        $conf->addItem('object_id', $this->object_id, $type->getTitle());
        $conf->setConfirm($this->lng->txt('delete'), 'delete');
        $conf->setCancel($this->lng->txt('cancel'), 'render');

        $this->tpl->setContent($conf->getHTML());
    }

    public function delete(): void
    {
        if (!$this->access->canManageObjects($this->ref_id)) {
            return;
        }

        $obj = new ilBookingObject($this->object_id);
        $obj->deleteReservationsAndCalEntries($this->object_id);
        $obj->delete();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('book_object_deleted'), true);
        $this->ctrl->setParameter($this, 'object_id', '');
        $this->ctrl->redirect($this, 'render');
    }

    public function deliverInfo(): void
    {
        if (!$this->object_id) {
            return;
        }

        $this->objects_manager->deliverObjectInfo($this->object_id);
    }
}
