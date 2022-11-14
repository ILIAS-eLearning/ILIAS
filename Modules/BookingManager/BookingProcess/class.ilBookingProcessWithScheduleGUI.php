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

use \ILIAS\Filesystem\Stream\Streams;

/**
 * Booking process ui class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingProcessWithScheduleGUI implements \ILIAS\BookingManager\BookingProcess\BookingProcessGUI
{
    protected \ILIAS\BookingManager\BookingProcess\ObjectSelectionManager $object_selection;
    protected \ILIAS\BookingManager\Objects\ObjectsManager $object_manager;
    protected \ILIAS\BookingManager\Reservations\ReservationManager $reservation;
    protected \ILIAS\BookingManager\BookingProcess\ProcessUtilGUI $util_gui;
    protected \ILIAS\BookingManager\InternalRepoService $repo;
    protected \ILIAS\BookingManager\BookingProcess\BookingProcessManager $process;
    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\BookingManager\InternalGUIService $gui;
    protected array $raw_post_data;
    protected \ILIAS\BookingManager\StandardGUIRequest $book_request;
    protected ilObjBookingPool $pool;
    protected int $booking_object_id;
    protected int $user_id_to_book;
    protected int $user_id_assigner;
    protected string $seed;
    protected ilBookingHelpAdapter $help;
    protected int $context_obj_id;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilAccessHandler $access;
    protected ilTabsGUI $tabs_gui;
    protected ilObjUser $user;
    protected int $book_obj_id;
    protected array $rsv_ids = [];

    public function __construct(
        ilObjBookingPool $pool,
        int $booking_object_id,
        string $seed = "",
        int $context_obj_id = 0
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tabs_gui = $DIC->tabs();
        $this->user = $DIC->user();
        $this->help = $DIC->bookingManager()->internal()
            ->gui()->bookingHelp($pool);
        $this->http = $DIC->http();

        $this->context_obj_id = $context_obj_id;

        $this->book_obj_id = $booking_object_id;

        $this->pool = $pool;

        $this->seed = $seed;
        $this->book_request = $DIC->bookingManager()
            ->internal()
            ->gui()
            ->standardRequest();

        $this->gui = $DIC->bookingManager()
            ->internal()
            ->gui();

        $this->repo = $DIC->bookingManager()
                         ->internal()
                         ->repo();
        $this->object_manager = $DIC->bookingManager()->internal()
            ->domain()->objects($pool->getId());
        $this->object_selection = $DIC->bookingManager()->internal()
            ->domain()->objectSelection($pool->getId());

        $this->rsv_ids = $this->book_request->getReservationIdsFromString();

        $this->raw_post_data = $DIC->http()->request()->getParsedBody();

        $this->user_id_assigner = $this->user->getId();
        if ($this->book_request->getBookedUser() > 0) {
            $this->user_id_to_book = $this->book_request->getBookedUser();
        } else {
            $this->user_id_to_book = $this->user_id_assigner; // by default user books his own booking objects.
        }
        $this->ctrl->saveParameter($this, ["bkusr", "returnCmd"]);
        $this->ctrl->setParameter($this, "seed", $this->seed);

        $this->process = $DIC->bookingManager()->internal()->domain()->process();
        $this->reservation = $DIC->bookingManager()->internal()->domain()->reservations();
        $this->util_gui = $DIC->bookingManager()->internal()->gui()->process()->ProcessUtilGUI(
            $this->pool,
            $this
        );
    }

    public function executeCommand() : void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");
        switch ($next_class) {
            default:
                if (in_array($cmd, array("book", "back", "week",
                    "assignParticipants",
                    "bookMultipleParticipants",
                    "saveMultipleBookings",
                    "showNumberForm",
                    "processNumberForm",
                    "checkAvailability",
                    "displayPostInfo",
                    "bookAvailableItems",
                    "deliverPostFile",
                    "selectObjects",
                    "redirectToParticipantsList"
            ))) {
                    $this->$cmd();
                }
        }
    }


    //
    // Step 0 / week view
    //

    /**
     * First step in booking process
     */
    public function week() : void // ok
    {
        $tpl = $this->tpl;

        //$this->tabs_gui->clearTargets();
        //$this->tabs_gui->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'back'));

        $this->util_gui->setHelpId("week");
        $this->ctrl->setParameter($this, 'returnCmd', "week");

        if ($this->user_id_to_book !== $this->user_id_assigner) {
            $this->ctrl->setParameter($this, 'bkusr', $this->user_id_to_book);
        }
        $user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());

        $week_gui = new \ILIAS\BookingManager\BookingProcess\WeekGUI(
            $this,
            "week",
            $this->object_selection->getSelectedObjects(),
            $this->pool->getId(),
            $this->seed,
            $user_settings->getWeekStart()
        );
        $tpl->setContent($week_gui->getHTML());

        $bar = $this->gui->toolbar();
        $list_link = $this->ctrl->getLinkTargetByClass("ilObjBookingPoolGUI", "render");
        $week_link = $this->ctrl->getLinkTargetByClass("ilBookingProcessWithScheduleGUI", "week");
        $mode_control = $this->gui->ui()->factory()->viewControl()->mode([
            $this->lng->txt("book_list") => $list_link,
            $this->lng->txt("book_week") => $week_link
        ], $this->lng->txt("book_view"))->withActive($this->lng->txt("book_week"));
        $bar->addComponent($mode_control);

        $list_gui = new \ILIAS\BookingManager\BookingProcess\ObjectSelectionListGUI(
            $this->pool->getId(),
            $this->ctrl->getFormAction($this, "selectObjects")
        );
        $tpl->setRightContent($list_gui->render());
    }

    protected function selectObjects() : void
    {
        $obj_ids = $this->book_request->getObjectIds();
        $this->object_selection->setSelectedObjects($obj_ids);
        $this->ctrl->redirect($this, "week");
    }


    //
    // Step 1
    //


    /**
     * Triggered from object list
     * week view for booking a single object /
     * confirmation for
     */
    public function book() : void // ok
    {
        $tpl = $this->tpl;

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'back'));

        $this->util_gui->setHelpId("book");

        $obj = new ilBookingObject($this->book_obj_id);

        $this->lng->loadLanguageModule("dateplaner");
        $this->ctrl->setParameter($this, 'object_id', $obj->getId());
        $this->ctrl->setParameter($this, 'returnCmd', "book");

        if ($this->user_id_to_book !== $this->user_id_assigner) {
            $this->ctrl->setParameter($this, 'bkusr', $this->user_id_to_book);
        }

        $user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());

        $week_gui = new \ILIAS\BookingManager\BookingProcess\WeekGUI(
            $this,
            "book",
            [$obj->getId()],
            $this->pool->getId(),
            $this->seed,
            $user_settings->getWeekStart()
        );
        $tpl->setContent($week_gui->getHTML());
    }

    // Table to assign participants to an object.
    public function assignParticipants() : void
    {
        $this->util_gui->assignParticipants($this->book_obj_id);
    }

    public function showNumberForm() : void
    {
        $object_id = $this->book_obj_id;
        $from = $this->book_request->getSlotFrom();
        $to = $this->book_request->getSlotTo() - 1;
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('book_back_to_list'),
            $this->ctrl->getLinkTarget($this, 'back')
        );
        $form = $this->getNumberForm($from, $to);
        $this->gui->modal($this->getBookgingObjectTitle())
            ->form($form)
            ->send();
    }

    protected function getBookgingObjectTitle() : string
    {
        return (new ilBookingObject($this->book_obj_id))->getTitle();
    }


    /**
     * @throws ilCtrlException
     * @throws ilDateTimeException
     */
    protected function getNumberForm(
        int $from,
        int $to
    ) : \ILIAS\Repository\Form\FormAdapterGUI {
        $counter = $this->reservation->getAvailableNr($this->book_request->getObjectId(), $from, $to);
        $period = ilDatePresentation::formatPeriod(
            new ilDateTime($from, IL_CAL_UNIX),
            new ilDateTime($to, IL_CAL_UNIX)
        );
        $this->ctrl->setParameter($this, "slot", $from . "_" . $to);
        $form = $this->gui->form([self::class], "processNumberForm")
            ->asyncModal()
            ->section(
                "props",
                $this->lng->txt("book_confirm_booking_schedule_number_of_objects"),
                $this->lng->txt("book_confirm_booking_schedule_number_of_objects_info")
            )
            ->number("nr", $period, "", 1, 1, $counter)
            ->radio("recurrence", $this->lng->txt("book_recurrence"), "", "0")
            ->radioOption("0", $this->lng->txt("book_no_recurrence"))
            ->radioOption("1", $this->lng->txt("book_book_recurrence"));
        return $form;
    }

    public function processNumberForm() : void
    {
        //get the user who will get the booking.
        if ($this->book_request->getBookedUser() > 0) {
            $this->user_id_to_book = $this->book_request->getBookedUser();
        }
        $slot = $this->book_request->getSlot();
        $from = $this->book_request->getSlotFrom();
        $to = $this->book_request->getSlotTo();
        $obj_id = $this->book_request->getObjectId();

        if ($this->user_id_assigner !== $this->user_id_to_book) {
            $this->ctrl->setParameterByClass(self::class, "bkusr", $this->user_id_to_book);
        }
        $this->ctrl->setParameterByClass(self::class, "slot", $slot);

        // form not valid -> show again
        $form = $this->getNumberForm($from, $to);
        if (!$form->isValid()) {
            $this->gui->modal($this->getBookgingObjectTitle())
                      ->form($form)
                      ->send();
        }

        // recurrence? -> show recurrence form
        $recurrence = $form->getData("recurrence");
        if ($recurrence === "1") {
            $this->ctrl->setParameterByClass(self::class, "object_id", $this->book_request->getObjectId());
            $this->ctrl->setParameterByClass(self::class, "nr", (int) $form->getData("nr"));
            $form = $this->getRecurrenceForm();
            $this->gui->modal($this->getBookgingObjectTitle())
                      ->form($form)
                      ->send();
        }
        $this->checkAvailability(
            false,
            $form->getData("nr")
        );
    }


    protected function getRecurrenceForm() : \ILIAS\Repository\Form\FormAdapterGUI
    {
        $this->lng->loadLanguageModule("dateplaner");
        $today = new ilDate(time(), IL_CAL_UNIX);
        $form = $this->gui->form([self::class], "checkAvailability")
                          ->section(
                              "props",
                              $this->lng->txt("book_confirm_booking_schedule_number_of_objects"),
                              $this->lng->txt("book_confirm_booking_schedule_number_of_objects_info")
                          )
                          ->switch("recurrence", $this->lng->txt("cal_recurrences"), "", "1")
                          ->group("1", $this->lng->txt("cal_weekly"))
                          ->date("until1", $this->lng->txt("cal_repeat_until"), "", $today)
                          ->group("2", $this->lng->txt("r_14"))
                          ->date("until2", $this->lng->txt("cal_repeat_until"), "", $today)
                          ->group("4", $this->lng->txt("r_4_weeks"))
                          ->date("until4", $this->lng->txt("cal_repeat_until"), "", $today)
                          ->end();
        return $form;
    }

    public function checkAvailability(bool $incl_recurrence = true, int $nr = 0) : void
    {
        $obj_id = $this->book_request->getObjectId();
        $from = $this->book_request->getSlotFrom();
        $to = $this->book_request->getSlotTo();
        if ($nr === 0) {
            $nr = $this->book_request->getNr();
        }
        $recurrence = 0;
        $until_ts = 0;
        if ($incl_recurrence) {
            $form = $this->getRecurrenceForm();
            // recurrence form not valid -> show again
            if (!$form->isValid()) {
                $this->gui->modal($this->getBookgingObjectTitle())
                          ->form($form)
                          ->send();
            }

            $recurrence = (int) $form->getData("recurrence");   // 1, 2 or 4
            $until = $form->getData("until" . $recurrence);
            $until_ts = $until->get(IL_CAL_UNIX);
        }

        $this->ctrl->saveParameter($this, ["object_id", "slot", "nr"]);
        $this->ctrl->setParameter($this, "recurrence", $recurrence);
        $this->ctrl->setParameter($this, "until", $until_ts);
        $book_available_target = $this->getBookAvailableTarget(
            $obj_id,
            $this->book_request->getSlot(),
            $recurrence,
            $nr,
            $until_ts
        );

        if ($incl_recurrence) {

            $missing = $this->process->getRecurrenceMissingAvailability(
                $obj_id,
                $from,
                $to,
                $recurrence,
                $nr,
                $until
            );

            // anything missing? -> send missing message
            if (count($missing) > 0) {
                $html = $this->getMissingAvailabilityMessage($missing);
                $this->gui->modal($this->getBookgingObjectTitle())
                    ->legacy($html)
                    ->button(
                        $this->lng->txt("book_book_available"),
                        $book_available_target,
                        false
                    )
                    ->send();
            }
        }
        $this->gui->send("<script>window.location.href = '" . $book_available_target . "';</script>");
    }

    protected function getMissingAvailabilityMessage(array $missing) : string
    {
        $f = $this->gui->ui()->factory();
        $box = $f->messageBox()->failure($this->lng->txt("book_missing_availability"));
        $items = array_map(function ($i) {
            $from = ilDatePresentation::formatDate(new ilDateTime($i["from"], IL_CAL_UNIX));
            $to = ilDatePresentation::formatDate(new ilDateTime($i["to"], IL_CAL_UNIX));
            return $from . " - " . $to . " : " . str_replace("$1", $i["missing"], $this->lng->txt("book_missing_items"));
        }, $missing);

        $list = $f->listing()->unordered($items);
        return $this->gui->ui()->renderer()->render([$box, $list]);
    }

    protected function bookAvailableItems(?int $recurrence = null, ?ilDateTime $until = null) : void
    {
        $obj_id = $this->book_request->getObjectId();
        $from = $this->book_request->getSlotFrom();
        $to = $this->book_request->getSlotTo();
        $nr = $this->book_request->getNr();
        if (is_null($recurrence)) {
            $recurrence = (int) $this->book_request->getRecurrence();
        }
        if (is_null($until)) {
            if ($this->book_request->getUntil() > 0) {
                $until = new ilDateTime($this->book_request->getUntil(), IL_CAL_UNIX);
            }
        }

        $booked = $this->process->bookAvailableObjects(
            $obj_id,
            $this->user_id_to_book,
            $this->user_id_assigner,
            $this->context_obj_id,
            $from,
            $to,
            $recurrence,
            $nr,
            $until
        );
        if (count($booked) > 0) {
            $this->util_gui->handleBookingSuccess($obj_id, "displayPostInfo", $booked);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('book_reservation_failed'), true);
            $this->util_gui->back();
        }
    }

    protected function getBookAvailableTarget(
        int $obj_id,
        string $slot,
        int $recurrence,
        int $nr,
        int $until
    ) : string {
        $this->ctrl->setParameter($this, "obj_id", $obj_id);
        $this->ctrl->setParameter($this, "slot", $slot);
        $this->ctrl->setParameter($this, "recurrence", $recurrence);
        $this->ctrl->setParameter($this, "nr", $nr);
        $this->ctrl->setParameter($this, "until", $until);
        return $this->ctrl->getLinkTarget($this, "bookAvailableItems");
    }

    public function displayPostInfo() : void
    {
        $this->util_gui->displayPostInfo(
            $this->book_obj_id,
            $this->user_id_assigner,
            "deliverPostFile"
        );
    }

    public function deliverPostFile() : void
    {
        $this->util_gui->deliverPostFile(
            $this->book_obj_id,
            $this->user_id_assigner
        );
    }

    public function back() : void
    {
        $this->util_gui->back();
    }
}
