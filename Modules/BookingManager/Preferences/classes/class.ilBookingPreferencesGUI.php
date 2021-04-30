<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Booking preferences ui class
 *
 * @author killing@leifos.de
 */
class ilBookingPreferencesGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $main_tpl;

    /**
     * @var ilBookingManagerInternalService
     */
    protected $service;

    /**
     * @var ilObjBookingPool
     */
    protected $pool;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilBookingPreferencesDBRepository
     */
    protected $repo;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * Constructor
     */
    public function __construct(ilObjBookingPool $pool)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->request = $DIC->http()->request();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->service = $DIC->bookingManager()->internal();
        $this->pool = $pool;
        $this->repo = $this->service->repo()->getPreferencesRepo();
        $this->access = $DIC->access();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");
        if ($cmd == "render") {
            $cmd = "show";
        }
        switch ($next_class) {
            default:
                if (in_array($cmd, ["show", "savePreferences"])) {
                    $this->$cmd();
                }
        }
    }
    
    /**
     * Show
     */
    protected function show()
    {
        $preferences = $this->service->domain()->preferences($this->pool);

        if ($preferences->isGivingPreferencesPossible()) {
            $this->listPreferenceOptions();
        } else {
            $this->listBookingResults();
        }
    }
    
    /**
     * List preference options
     */
    protected function listPreferenceOptions($form = null)
    {
        $ui = $this->ui;
        if (count(ilBookingObject::getList($this->pool->getId())) > 0) {
            if (is_null($form)) {
                $form = $this->initPreferenceForm();
            }
            $this->main_tpl->setContent($ui->renderer()->render($form));
        } else {
            ilUtil::sendInfo($this->lng->txt("book_type_warning"));
        }
    }

    /**
     * Init preferences form.
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    public function initPreferenceForm()
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $repo = $this->repo;

        $preferences = $repo->getPreferencesOfUser($this->pool->getId(), $this->user->getId());
        $preferences = $preferences->getPreferences();

        $this->renderBookingInfo();

        $fields = [];
        foreach (ilBookingObject::getList($this->pool->getId()) as $book_obj) {
            $checked = (is_array($preferences[$this->user->getId()]) &&
                in_array($book_obj["booking_object_id"], $preferences[$this->user->getId()]))
                ? true
                : false;

            $fields["cb_" . $book_obj["booking_object_id"]] =
                $f->input()->field()->checkbox($book_obj["title"], $book_obj["description"])->withValue($checked);
        }

        // section
        $section1 = $f->input()->field()->section($fields, $lng->txt("book_preferences"));

        $form_action = $ctrl->getLinkTarget($this, "savePreferences");
        return $f->input()->container()->form()->standard($form_action, ["sec" => $section1]);
    }

    /**
     * Save preferences
     */
    public function savePreferences()
    {
        $preferences = $this->service->domain()->preferences($this->pool);

        if (!$preferences->isGivingPreferencesPossible()) {
            return;
        }

        $request = $this->request;
        $form = $this->initPreferenceForm();
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $repo = $this->repo;

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();

            if (is_array($data["sec"])) {
                $obj_ids = [];
                foreach ($data["sec"] as $k => $v) {
                    if ($v === true) {
                        $id = explode("_", $k);
                        $obj_ids[] = (int) $id[1];
                    }
                }

                if (count($obj_ids) > $this->pool->getPreferenceNumber()) {
                    ilUtil::sendFailure($lng->txt("book_too_many_preferences"), true);
                    $this->listPreferenceOptions($form);
                    return;
                }

                if (count($obj_ids) < $this->pool->getPreferenceNumber()) {
                    ilUtil::sendFailure($lng->txt("book_not_enough_preferences"), true);
                    $this->listPreferenceOptions($form);
                    return;
                }

                $preferences = $this->service->data()->preferencesFactory()->preferences(
                    [$this->user->getId() => $obj_ids]
                );

                $repo->savePreferencesOfUser($this->pool->getId(), $this->user->getId(), $preferences);
                $part = new ilBookingParticipant($this->user->getId(), $this->pool->getId());

                $titles = implode(", ", array_map(function ($id) {
                    return ilBookingObject::lookupTitle($id);
                }, $obj_ids));

                ilUtil::sendSuccess($lng->txt("book_preferences_saved") . " (" . $titles . ")", true);
            }
        }
        $ctrl->redirect($this, "show");
    }



    /**
     * Render booking info
     */
    protected function renderBookingInfo()
    {
        $lng = $this->lng;
        $info = $lng->txt("book_preference_info");
        $info = str_replace("%1", $this->pool->getPreferenceNumber(), $info);
        $info = str_replace("%2", ilDatePresentation::formatDate(
            new ilDateTime($this->pool->getPreferenceDeadline(), IL_CAL_UNIX)
        ), $info);
        ilUtil::sendInfo($info);
    }

    
    /**
     * List booking results
     */
    protected function listBookingResults()
    {
        $main_tpl = $this->main_tpl;
        $lng = $this->lng;
        $repo = $this->repo;
        $ui = $this->ui;
        $ctrl = $this->ctrl;

        $info_gui = new ilInfoScreenGUI($this);

        // preferences
        $info_gui->addSection($lng->txt("book_your_preferences"));
        $preferences = $repo->getPreferencesOfUser($this->pool->getId(), $this->user->getId());
        $preferences = $preferences->getPreferences();
        $cnt = 1;
        if (is_array($preferences[$this->user->getId()])) {
            foreach ($preferences[$this->user->getId()] as $book_obj_id) {
                $book_obj = new ilBookingObject($book_obj_id);
                $info_gui->addProperty((string) $cnt++, $book_obj->getTitle());
            }
        } else {
            $info_gui->addProperty("", $lng->txt("book_no_preferences_for_you"));
        }

        // bookings
        $this->service->domain()->preferences($this->pool)->storeBookings(
            $this->repo->getPreferences($this->pool->getId())
        );
        $bookings = $this->service->domain()->preferences($this->pool)->readBookings();
        $info_gui->addSection($lng->txt("book_your_bookings"));
        $cnt = 1;
        if (is_array($bookings[$this->user->getId()])) {
            foreach ($bookings[$this->user->getId()] as $book_obj_id) {
                $book_obj = new ilBookingObject($book_obj_id);

                // post info button
                $post_info_button = "";
                if ($book_obj->getPostFile() || $book_obj->getPostText()) {
                    $ctrl->setParameterByClass("ilBookingObjectGUI", "object_id", $book_obj_id);
                    $b = $ui->factory()->button()->shy(
                        $lng->txt("book_post_booking_information"),
                        $ctrl->getLinkTargetByClass(["ilBookingObjectGUI", "ilBookingProcessGUI"], "displayPostInfo")
                    );
                    $post_info_button = "<br>" . $ui->renderer()->render($b);
                }
                $info_gui->addProperty((string) $cnt++, $book_obj->getTitle() . $post_info_button);
            }
        } else {
            $info_gui->addProperty("", $lng->txt("book_no_bookings_for_you"));
        }

        // all users
        if ($this->access->checkAccess("write", "", $this->pool->getRefId())) {
            $info_gui->addSection($lng->txt("book_all_users"));
            $preferences = $repo->getPreferences($this->pool->getId());
            $preferences = $preferences->getPreferences();
            foreach ($preferences as $user_id => $obj_ids) {
                $booking_str = "<br>" . $lng->txt("book_log") . ": -";
                if (is_array($bookings[$user_id])) {
                    $booking_str = "<br>" . $lng->txt("book_log") . ": " . implode(", ", array_map(function ($obj_id) {
                        $book_obj = new ilBookingObject($obj_id);
                        return $book_obj->getTitle();
                    }, $bookings[$user_id]));
                }

                $info_gui->addProperty(
                    ilUserUtil::getNamePresentation($user_id, false, false, "", true),
                    $lng->txt("book_preferences") . ": " . implode(", ", array_map(function ($obj_id) {
                        $book_obj = new ilBookingObject($obj_id);
                        return $book_obj->getTitle();
                    }, $obj_ids)) . $booking_str
                );
            }
        }

        $main_tpl->setContent($info_gui->getHTML());
    }
}
