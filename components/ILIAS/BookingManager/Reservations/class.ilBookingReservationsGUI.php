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
use ILIAS\BookingManager\BookingProcess\ProcessUtilGUI;
use ILIAS\BookingManager\Bookings\Table\BookingsTable;
use ILIAS\BookingManager\Bookings\Table\BookingsWithoutScheduleTable;
use ILIAS\BookingManager\Bookings\Table\BookingsWithScheduleTable;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\InternalService;
use ILIAS\BookingManager\StandardGUIRequest;
use ILIAS\Data\Factory;
use ILIAS\DI\UIServices;
use ILIAS\UI\URLBuilder;

class ilBookingReservationsGUI
{
    public const string DEFAULT_CMD = 'log';

    protected ProcessUtilGUI $util_gui;
    protected AccessManager $access;
    protected ilToolbarGUI $toolbar;
    protected UIServices $ui;
    protected InternalService $service;
    protected array $raw_post_data;
    protected StandardGUIRequest $book_request;
    protected ilBookingHelpAdapter $help;
    protected ?int $context_obj_id;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs_gui;
    protected ilObjUser $user;
    protected ilObjBookingPool $pool;
    protected int $ref_id;
    protected int $book_obj_id;
    protected string $reservation_id;  // see BookingReservationDBRepo, obj_user_(slot)_context
    protected int $booked_user;
    protected ilUIService $ui_service;

    public function __construct(ilObjBookingPool $pool, ilBookingHelpAdapter $help, ?int $context_obj_id = null)
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->pool = $pool;
        $this->ctrl = $DIC->ctrl();
        $this->ref_id = $pool->getRefId();
        $this->lng = $DIC->language();
        $this->access = $DIC->bookingManager()->internal()->domain()->access();
        $this->tabs_gui = $DIC->tabs();
        $this->help = $help;
        $this->user = $DIC->user();
        $this->book_request = $DIC->bookingManager()->internal()->gui()->standardRequest();
        $this->service = $DIC->bookingManager()->internal();
        $this->ui = $DIC->ui();
        $this->toolbar = $DIC->toolbar();
        $this->ui_service = $DIC->uiservice();

        $this->book_obj_id = $this->book_request->getObjectId();

        $this->context_obj_id = $context_obj_id;

        $this->booked_user = $this->book_request->getBookedUser();
        if ($this->booked_user === 0) {
            $this->booked_user = $DIC->user()->getId();
        }
        $this->reservation_id = $this->book_request->getReservationId();

        $this->ctrl->saveParameter($this, ['object_id', 'bkusr']);

        if ($this->book_request->getObjectId() > 0 && ilBookingObject::lookupPoolId($this->book_request->getObjectId()) !== $this->pool->getId()) {
            throw new ilException('Booking Object ID does not match Booking Pool.');
        }

        $this->raw_post_data = $DIC->http()->request()->getParsedBody();
        $this->util_gui = $DIC
            ->bookingManager()
            ->internal()
            ->gui()
            ->process()
            ->ProcessUtilGUI($this->pool, $this);
    }

    protected function getLogReservationIds(): array
    {
        $reservation_ids = $this->book_request->getReservationIds();
        if ($reservation_ids !== []) {
            return $reservation_ids;
        }

        return $this->reservation_id > 0 ? [$this->reservation_id] : [];
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd(self::DEFAULT_CMD);
        if ($cmd === '') {
            $cmd = self::DEFAULT_CMD;
        }

        $cmds = [
            self::DEFAULT_CMD,
            'executeTableAction',
            'changeStatusObject',
            'back',
            'confirmResetRun',
            'resetRun',
            'displayPostInfo',
            'deliverPostFile'
        ];
        if (in_array($cmd, $cmds, true)) {
            $this->$cmd();
        }
    }

    protected function setHelpId(string $a_id): void
    {
        $this->help->setHelpId($a_id);
    }

    private function getBookingsTable(): BookingsTable
    {
        global $DIC;

        $this->showRerunPreferenceAssignment();
        $booking_table_class = $this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE
            ? BookingsWithScheduleTable::class
            : BookingsWithoutScheduleTable::class;

        return new $booking_table_class(
            $this->ui->factory(),
            $this->ui->renderer(),
            $this->access,
            $this->tpl,
            $DIC->refinery(),
            $this->lng,
            new HttpService($DIC->http(), $DIC->refinery()),
            $this->user,
            $DIC->bookingManager()->internal()->repo()->reservation(),
            $this->ctrl,
            $DIC->uiService(),
            $this->service->domain()->bookingSettings()->getByObjId($this->pool->getId()),
            $this->pool
        );
    }

    private function getTableActionUrlBuilder(): URLBuilder
    {
        return new URLBuilder((new Factory())->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(self::class, 'executeTableAction')
        ));
    }

    private function presetBookingsFilterFromRequest(): void
    {
        $object_id = $this->book_request->getObjectId();
        if ($object_id <= 0) {
            return;
        }

        $filter_id = $this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE
            ? BookingsWithScheduleTable::ID . '_filter'
            : BookingsWithoutScheduleTable::ID . '_filter';

        $gateway = new ilUIFilterServiceSessionGateway();
        $gateway->writeValue($filter_id, 'object', (string) $object_id);
        $gateway->writeActivated($filter_id, true);
    }

    public function log(): void
    {
        $this->presetBookingsFilterFromRequest();

        $bookings_table = $this->getBookingsTable();
        $this->tpl->setContent(
            $this->ui->renderer()->render(
                $bookings_table->getComponents($this->getTableActionUrlBuilder())
            )
        );

        $reservations_table = new ilBookingReservationsTableGUI(
            $this,
            'log',
            $this->ref_id,
            $this->pool,
            $this->access->canManageAllReservations($this->ref_id) || $this->pool->hasPublicLog(),
            $this->ui_service->filter()->getData($bookings_table->getFilter()) ?? [],
            null,
            $this->context_obj_id !== null ? [$this->context_obj_id] : null
        );
        $reservations_table->getExportMode() > 0 && $reservations_table->exportData($reservations_table->getExportMode(), true);
    }

    public function executeTableAction(): void
    {
        $this->getBookingsTable()->execute($this->getTableActionUrlBuilder());
        $this->ctrl->redirectByClass(self::class, 'log');
    }

    public function changeStatusObject(): void
    {
        $rsv_ids = $this->book_request->getReservationIds();
        if ($rsv_ids === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->log();
        }

        if ($this->access->canManageAllReservations($this->ref_id)) {
            ilBookingReservation::changeStatus($rsv_ids, $this->book_request->getStatus());
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->log();
    }

    protected function back(): void
    {
        $this->ctrl->redirect($this, 'log');
    }

    protected function showRerunPreferenceAssignment(): void
    {
        if (
            !$this->access->canManageAllReservations($this->ref_id)
            || $this->pool->getScheduleType() !== ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES
        ) {
            return;
        }

        if ($this->service->domain()->preferences($this->pool)->hasRun()) {
            $this->toolbar->addComponent($this->ui->factory()->button()->standard(
                $this->lng->txt('book_rerun_assignments'),
                $this->ctrl->getLinkTarget($this, 'confirmResetRun')
            ));
        }
    }

    protected function confirmResetRun(): void
    {
        if (!$this->access->canManageAllReservations($this->ref_id)) {
            return;
        }

        $this->tabs_gui->activateTab('log');
        $mess = $this->ui->factory()->messageBox()->confirmation($this->lng->txt('book_rerun_confirmation'))->withButtons(
            [
                $this->ui->factory()->button()->standard($this->lng->txt('book_rerun_assignments'), $this->ctrl->getLinkTarget($this, 'resetRun')),
                $this->ui->factory()->button()->standard($this->lng->txt('cancel'), $this->ctrl->getLinkTarget($this, 'log'))
            ]
        );
        $this->tpl->setContent($this->ui->renderer()->render($mess));
    }

    protected function resetRun(): void
    {
        if (!$this->access->canManageAllReservations($this->ref_id)) {
            return;
        }

        if (
            $this->pool->getScheduleType() === ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES
            && $this->access->canManageAllReservations($this->pool->getRefId())
        ) {
            $pref_manager = $this->service->domain()->preferences($this->pool);
            $repo = $this->service->repo()->preferences();
            $pref_manager->resetRun();
            $pref_manager->storeBookings($repo->getPreferences($this->pool->getId()));
        }
        $this->ctrl->redirect($this, 'log');
    }

    public function displayPostInfo(): void
    {
        $this->ctrl->saveParameter($this, 'object_id');
        $this->util_gui->displayPostInfo($this->book_obj_id, 0, 'deliverPostFile');
    }

    public function deliverPostFile(): void
    {
        $this->util_gui->deliverPostFile($this->book_obj_id, $this->user->getId());
    }
}
