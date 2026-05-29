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
use ILIAS\BookingManager\StandardGUIRequest;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Schedule\ScheduleManager;
use ILIAS\BookingManager\Schedule\Table\ScheduleTable;
use ILIAS\BookingManager\Service as BookingManager;
use ILIAS\Data\Factory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;

/**
 * @ilCtrl_Calls ilBookingScheduleGUI:
 */
class ilBookingScheduleGUI
{
    protected AccessManager $access;
    protected StandardGUIRequest $book_request;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilHelpGUI $help;
    protected ilObjectDataCache $obj_data_cache;
    protected int $schedule_id;
    protected int $ref_id;
    private readonly Refinery $refinery;
    private readonly UIFactory $ui_factory;
    private readonly UIRenderer $ui_renderer;
    private readonly HttpService $http;
    private readonly BookingManager $booking_manager;
    private readonly ilToolbarGUI $toolbar;
    private readonly Factory $data_factory;

    public function __construct(ilObjBookingPoolGUI $a_parent_obj)
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->bookingManager()->internal()->domain()->access();
        $this->help = $DIC['ilHelp'];
        $this->obj_data_cache = $DIC['ilObjDataCache'];
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->http = new HttpService($DIC->http(), $this->refinery);
        $this->booking_manager = $DIC->bookingManager();
        $this->toolbar = $DIC->toolbar();
        $this->data_factory = new Factory();

        $this->ref_id = $a_parent_obj->getRefId();
        $this->book_request = $DIC->bookingManager()->internal()->gui()->standardRequest();
        $this->schedule_id = $this->book_request->getScheduleId();

        if ($this->schedule_id > 0) {
            $this->access->validateScheduleId($this->schedule_id, ilObject::_lookupObjId($this->ref_id));
        }
    }

    public function executeCommand(): void
    {
        switch ($this->ctrl->getNextClass($this)) {
            default:
                $cmd = $this->ctrl->getCmd('render');
                if (method_exists($this, $cmd)) {
                    $this->$cmd();
                }
                break;
        }
    }

    public function executeTableAction(): void
    {
        $pool_id = $this->obj_data_cache->lookupObjId($this->ref_id);
        $schedule_manager = $this->booking_manager->internal()->domain()->schedules($pool_id);

        $this->configureScheduleTable($schedule_manager)->execute($this->getTableActionUrlBuilder());
        $this->render();
    }

    public function render(): void
    {
        $pool_id = $this->obj_data_cache->lookupObjId($this->ref_id);
        $schedule_manager = $this->booking_manager->internal()->domain()->schedules($pool_id);

        $this->checkForInfoMessageAboutMissingBookableItems($schedule_manager, $pool_id);

        if ($this->access->canManageSettings($this->ref_id)) {
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('book_add_schedule'),
                    $this->ctrl->getLinkTarget($this, 'create')
                )
            );
        }

        $this->tpl->setContent(
            $this->ui_renderer->render(
                $this->configureScheduleTable($schedule_manager)->getComponents($this->getTableActionUrlBuilder())
            )
        );
    }

    public function create(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'render'));
        $this->help->setScreenIdComponent('book');
        $this->help->setScreenId('schedules');
        $this->help->setSubScreenId('create');
        $this->tpl->setContent($this->initForm()->getHTML());
    }

    public function edit(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'render'));
        $this->help->setScreenIdComponent('book');
        $this->help->setScreenId('schedules');
        $this->help->setSubScreenId('edit');
        $this->tpl->setContent($this->initForm('edit', $this->schedule_id)->getHTML());
    }

    public function initForm(string $a_mode = 'create', ?int $id = null): ilPropertyFormGUI
    {
        $this->lng->loadLanguageModule('dateplaner');

        $form_gui = new ilPropertyFormGUI();

        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setRequired(true);
        $title->setSize(40);
        $title->setMaxLength(120);
        $form_gui->addItem($title);

        $definition = new ilScheduleInputGUI($this->lng->txt('book_schedule_days'), 'days');
        $definition->setInfo($this->lng->txt('book_schedule_days_info'));
        $definition->setRequired(true);
        $form_gui->addItem($definition);

        $deadline_opts = new ilRadioGroupInputGUI($this->lng->txt('book_deadline_options'), 'deadline_opts');
        $deadline_opts->setRequired(true);
        $form_gui->addItem($deadline_opts);

        $deadline_time = new ilRadioOption($this->lng->txt('book_deadline_hours'), 'hours');
        $deadline_opts->addOption($deadline_time);

        $deadline = new ilNumberInputGUI($this->lng->txt('book_deadline'), 'deadline');
        $deadline->setInfo($this->lng->txt('book_deadline_info'));
        $deadline->setSuffix($this->lng->txt('book_hours'));
        $deadline->setMinValue(1);
        $deadline->setSize(3);
        $deadline->setMaxLength(3);
        $deadline->setRequired(true);
        $deadline_time->addSubItem($deadline);

        $deadline_start = new ilRadioOption($this->lng->txt('book_deadline_slot_start'), 'slot_start');
        $deadline_opts->addOption($deadline_start);

        $deadline_slot = new ilRadioOption($this->lng->txt('book_deadline_slot_end'), 'slot_end');
        $deadline_opts->addOption($deadline_slot);

        if ($a_mode === 'edit') {
            $schedule = new ilBookingSchedule($id);
        }

        $av = new ilFormSectionHeaderGUI();
        $av->setTitle($this->lng->txt('obj_activation_list_gui'));
        $form_gui->addItem($av);

        // #18221
        $this->lng->loadLanguageModule('rep');

        $from = new ilDateTimeInputGUI($this->lng->txt('rep_activation_limited_start'), 'from');
        $from->setShowTime(true);
        $form_gui->addItem($from);

        $to = new ilDateTimeInputGUI($this->lng->txt('rep_activation_limited_end'), 'to');
        $to->setShowTime(true);
        $form_gui->addItem($to);

        if ($a_mode === 'edit') {
            $form_gui->setTitle($this->lng->txt('book_edit_schedule'));

            $item = new ilHiddenInputGUI('schedule_id');
            $item->setValue($id);
            $form_gui->addItem($item);

            $schedule = new ilBookingSchedule($id);
            $title->setValue($schedule->getTitle());
            $from->setDate($schedule->getAvailabilityFrom());
            $to->setDate($schedule->getAvailabilityTo());

            if ($schedule->getDeadline() === 0) {
                $deadline_opts->setValue('slot_start');
            } elseif ($schedule->getDeadline() > 0) {
                $deadline->setValue($schedule->getDeadline());
                $deadline_opts->setValue('hours');
            } else {
                $deadline->setValue(0);
                $deadline_opts->setValue('slot_end');
            }

            $definition->setValue($schedule->getDefinitionBySlots());

            $form_gui->addCommandButton('update', $this->lng->txt('save'));
        } else {
            $form_gui->setTitle($this->lng->txt('book_add_schedule'));
            $form_gui->addCommandButton('save', $this->lng->txt('save'));
            $form_gui->addCommandButton('render', $this->lng->txt('cancel'));
        }
        $form_gui->setFormAction($this->ctrl->getFormAction($this));

        return $form_gui;
    }

    public function save(): void
    {
        $form = $this->initForm();

        if ($form->checkInput()) {
            $obj = new ilBookingSchedule();
            $this->formToObject($form, $obj);
            $obj->save();

            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                $this->lng->txt('book_schedule_added'),
                true
            );
            $this->ctrl->redirect($this, 'render');
            return;
        }

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    public function update(): void
    {
        $form = $this->initForm('edit', $this->schedule_id);

        if ($form->checkInput()) {
            $obj = new ilBookingSchedule($this->schedule_id);
            $this->formToObject($form, $obj);
            $obj->update();

            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                $this->lng->txt('book_schedule_updated'),
                true
            );
            $this->ctrl->redirect($this, 'render');
            return;
        }

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    protected function formToObject(ilPropertyFormGUI $form, ilBookingSchedule $schedule): void
    {
        $schedule->setTitle($form->getInput('title'));
        $schedule->setPoolId($this->obj_data_cache->lookupObjId($this->ref_id));

        $from = $form->getItemByPostVar('from');
        if ($from !== null) {
            $schedule->setAvailabilityFrom($from->getDate());
        }

        $to = $form->getItemByPostVar('to');
        if ($to !== null) {
            $schedule->setAvailabilityTo($to->getDate());
        }

        match ($form->getInput('deadline_opts')) {
            'slot_start' => $schedule->setDeadline(0),
            'hours' => $schedule->setDeadline($form->getInput('deadline')),
            'slot_end' => $schedule->setDeadline(-1),
        };

        $schedule->setDefinitionBySlots($form->getInput('days'));
    }

    private function configureScheduleTable(ScheduleManager $schedule_manager): ScheduleTable
    {
        return new ScheduleTable(
            $this->ctrl,
            $this->lng,
            $this->tpl,
            $this->ui_factory,
            $this->ui_renderer,
            $this->refinery,
            $this->access,
            $this->http,
            $schedule_manager,
            $this->ref_id
        );
    }

    private function checkForInfoMessageAboutMissingBookableItems(ScheduleManager $schedule_manager, int $pool_id): void
    {
        $schedule_data = $schedule_manager->getScheduleData();

        if ($schedule_data === [] || ilBookingObject::getList($pool_id) !== []) {
            return;
        }

        $this->tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_INFO, $this->lng->txt('book_type_warning'));
    }

    private function getTableActionUrlBuilder(): URLBuilder
    {
        return new URLBuilder($this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(self::class, 'executeTableAction')
        ));
    }
}
