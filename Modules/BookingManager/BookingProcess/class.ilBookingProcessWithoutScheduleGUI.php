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
class ilBookingProcessWithoutScheduleGUI implements \ILIAS\BookingManager\BookingProcess\BookingProcessGUI
{
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

        $this->rsv_ids = $this->book_request->getReservationIdsFromString();

        $this->raw_post_data = $DIC->http()->request()->getParsedBody();

        $this->user_id_assigner = $this->user->getId();
        if ($this->book_request->getBookedUser() > 0) {
            $this->user_id_to_book = $this->book_request->getBookedUser();
        } else {
            $this->user_id_to_book = $this->user_id_assigner; // by default user books his own booking objects.
        }
        $this->ctrl->saveParameter($this, ["bkusr", "returnCmd"]);

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
                if (in_array($cmd, array(
                    "book",
                    "back",
                    "assignParticipants",
                    "bookMultipleParticipants",
                    "saveMultipleBookings",
                    "confirmedBooking",
                    "displayPostInfo",
                    "deliverPostFile",
                    "redirectToParticipantsList"
            ))) {
                    $this->$cmd();
                }
        }
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

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("book_confirm_booking_no_schedule"));

        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt("cancel"), "back");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedBooking");

        $cgui->addItem("object_id", $obj->getId(), $obj->getTitle());

        $tpl->setContent($cgui->getHTML());
    }

    // Table to assign participants to an object.
    public function assignParticipants() : void
    {
        $this->util_gui->assignParticipants($this->book_obj_id);
    }

    /**
     * Create reservations for a bunch of booking pool participants.
     */
    public function bookMultipleParticipants() : void
    {
        $participants = $this->book_request->getParticipants();
        if (count($participants) === 0) {
            $this->util_gui->back();
            return;
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, 'assignparticipants'));

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));

        //add user list as items.
        foreach ($participants as $id) {
            $name = ilObjUser::_lookupFullname($id);
            $conf->addItem("participants[]", $id, $name);
        }

        $available = ilBookingReservation::numAvailableFromObjectNoSchedule($this->book_obj_id);
        if (count($participants) > $available) {
            $obj = new ilBookingObject($this->book_obj_id);
            $this->tpl->setOnScreenMessage("failure", sprintf(
                $this->lng->txt('book_limit_objects_available'),
                count($participants),
                $obj->getTitle(),
                $available
            ), true);
            $this->ctrl->redirect($this, "redirectToParticipantsList");
        } else {
            $conf->setHeaderText($this->lng->txt('book_confirm_booking_no_schedule'));
            $conf->addHiddenItem("object_id", $this->book_obj_id);
            $conf->setConfirm($this->lng->txt("assign"), "saveMultipleBookings");
        }

        $conf->setCancel($this->lng->txt("cancel"), 'redirectToParticipantsList');
        $this->tpl->setContent($conf->getHTML());
    }

    public function redirectToParticipantsList() : void
    {
        $this->ctrl->redirect($this, 'assignParticipants');
    }

    /**
     * Save multiple users reservations for one booking pool object.
     * @todo check if object/user exist in the DB,
     */
    public function saveMultipleBookings() : void
    {
        $participants = $this->book_request->getParticipants();
        $object_id = $this->book_request->getObjectId();
        if (count($participants) > 0 && $object_id > 0) {
            $this->book_obj_id = $object_id;
        } else {
            $this->util_gui->back();
        }
        $rsv_ids = array();
        foreach ($participants as $id) {
            $this->user_id_to_book = $id;
            $rsv_ids[] = $this->process->bookSingle(
                $this->book_obj_id,
                $this->user_id_to_book,
                $this->user_id_assigner,
                $this->context_obj_id
            );
        }

        if (count($rsv_ids)) {
            $this->tpl->setOnScreenMessage('success', "booking_multiple_succesfully");
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('book_reservation_failed_overbooked'), true);
        }
        $this->util_gui->back();
    }


    //
    // Step 2: Confirmation
    //

    // Book object - either of type or specific - for given dates
    public function confirmedBooking() : bool
    {
        $success = false;
        $rsv_ids = array();

        if ($this->book_obj_id > 0) {
            $object_id = $this->book_obj_id;
            if ($object_id) {
                if (ilBookingReservation::isObjectAvailableNoSchedule($object_id) &&
                    count(ilBookingReservation::getObjectReservationForUser($object_id, $this->user_id_to_book)) === 0) { // #18304
                    $rsv_ids[] = $this->process->bookSingle(
                        $object_id,
                        $this->user_id_to_book,
                        $this->user_id_assigner,
                        $this->context_obj_id
                    );
                    $success = $object_id;
                } else {
                    // #11852
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('book_reservation_failed_overbooked'), true);
                    $this->ctrl->redirect($this, 'back');
                }
            }
        }

        if ($success) {
            $this->util_gui->handleBookingSuccess($success, "displayPostInfo", $rsv_ids);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('book_reservation_failed'), true);
            $this->ctrl->redirect($this, 'book');
        }
        return true;
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
