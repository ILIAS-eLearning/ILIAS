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

use ILIAS\BookingManager;

/**
 * This class is used for integration of the booking manager as a service
 * into other repository objects, e.g. courses.
 * @ilCtrl_Calls ilBookingGatewayGUI: ilPropertyFormGUI, ilBookingObjectServiceGUI, ilBookingReservationsGUI
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingGatewayGUI
{
    protected BookingManager\InternalDomainService $domain;
    protected BookingManager\StandardGUIRequest $book_request;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected \ilGlobalTemplateInterface $main_tpl;
    protected ilObjectGUI $parent_gui;
    protected ilTabsGUI $tabs;
    protected int $obj_id;
    protected int $ref_id;
    protected ilObjBookingServiceSettings $current_settings;
    protected int $current_pool_ref_id;
    protected ?ilObjBookingPool $pool = null;
    protected ilToolbarGUI $toolbar;
    protected int $main_host_ref_id = 0;
    // Have any pools been already selected?
    protected bool $pools_selected = false;
    protected string $seed;
    protected string $sseed;
    protected ilObjUseBookDBRepository $use_book_repo;
    protected string $return_to = "";
    protected ilBookingHelpAdapter $help;

    public function __construct(
        ilObjectGUI $parent_gui,
        int $main_host_ref_id = 0
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->parent_gui = $parent_gui;
        $this->tabs = $DIC->tabs();
        $this->book_request = $DIC->bookingManager()
                                  ->internal()
                                  ->gui()
                                  ->standardRequest();
        $this->domain = $DIC
            ->bookingManager()
            ->internal()
            ->domain();

        $this->lng->loadLanguageModule("book");

        // current parent context (e.g. session in course)
        $this->obj_id = $parent_gui->getObject()->getId();
        $this->ref_id = $parent_gui->getObject()->getRefId();

        $this->main_host_ref_id = ($main_host_ref_id === 0)
            ? $this->ref_id
            : $main_host_ref_id;


        $this->seed = $this->book_request->getSeed();
        $this->sseed = $this->book_request->getSSeed();

        $this->toolbar = $DIC->toolbar();

        $this->use_book_repo = new ilObjUseBookDBRepository($DIC->database());

        $req_return_to = $this->book_request->getReturnTo();
        if (in_array($req_return_to, ["ilbookingobjectservicegui", "ilbookingreservationsgui"])) {
            $this->return_to = $req_return_to;
        }

        // get current settings
        $handler = new BookingManager\getObjectSettingsCommandHandler(
            new BookingManager\getObjectSettingsCommand($this->obj_id),
            $this->use_book_repo
        );
        $this->current_settings = $handler->handle()->getSettings();

        $this->initPool();

        if (is_object($this->pool)) {
            $this->help = new ilBookingHelpAdapter($this->pool, $DIC["ilHelp"]);
            $DIC["ilHelp"]->setScreenIdComponent("book");
        }
    }

    /**
     * Init pool. Determine the current pool in $this->current_pool_ref_id.
     * Host objects (e.g. courses) may use multiple booking pools.
     * This method determines the current selected
     * pool (stored in request parameter "pool_ref_id") within the host object user interface.
     * If no pool has been selected yet, the first one attached to the host object is choosen.
     * If no pools are attached to the host object at all we get a 0 ID.
     */
    protected function initPool(): void
    {
        $ctrl = $this->ctrl;

        $ctrl->saveParameter($this, "pool_ref_id");
        $pool_ref_id = $this->book_request->getPoolRefId();

        $book_ref_ids = $this->use_book_repo->getUsedBookingPools(ilObject::_lookupObjId($this->main_host_ref_id));

        $this->pools_selected = (count($book_ref_ids) > 0);

        if (!in_array($pool_ref_id, $book_ref_ids)) {
            if (count($book_ref_ids) > 0) {
                $pool_ref_id = current($book_ref_ids);
            } else {
                $pool_ref_id = 0;
            }
        }
        $this->current_pool_ref_id = $pool_ref_id;
        if ($this->current_pool_ref_id > 0) {
            $this->pool = new ilObjBookingPool($this->current_pool_ref_id);
            $ctrl->setParameter($this, "pool_ref_id", $this->current_pool_ref_id);
        }
    }

    /**
     * @throws ilCtrlException
     * @throws ilException
     */
    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        switch ($next_class) {
            case "ilpropertyformgui":
                $form = $this->initSettingsForm();
                $ctrl->setReturn($this, 'settings');
                $ctrl->forwardCommand($form);
                break;

            case "ilbookingobjectservicegui":
                $this->setSubTabs("book_obj");
                $this->showPoolSelector("ilbookingobjectservicegui");
                $book_ser_gui = new ilBookingObjectServiceGUI(
                    $this->ref_id,
                    $this->current_pool_ref_id,
                    $this->use_book_repo,
                    $this->seed,
                    $this->sseed,
                    $this->help
                );
                $ctrl->forwardCommand($book_ser_gui);
                break;

            case "ilbookingreservationsgui":
                $this->showPoolSelector("ilbookingreservationsgui");
                $this->setSubTabs("reservations");
                $res_gui = new ilBookingReservationsGUI($this->pool, $this->help, $this->obj_id);
                $this->ctrl->forwardCommand($res_gui);
                break;


            default:
                if (in_array($cmd, array("show", "settings", "saveSettings", "selectPool"))) {
                    $this->$cmd();
                }
        }
    }

    protected function showPoolSelector(
        string $return_to
    ): void {
        //
        $options = [];
        foreach ($this->use_book_repo->getUsedBookingPools(ilObject::_lookupObjectId($this->main_host_ref_id)) as $ref_id) {
            $options[$ref_id] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
        }

        $this->ctrl->setParameter($this, "return_to", $return_to);
        if (count($options) > 0) {
            $si = new ilSelectInputGUI("", "pool_ref_id");
            $si->setOptions($options);
            $si->setValue($this->current_pool_ref_id);
            $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
            $this->toolbar->addInputItem($si, false);
            $this->toolbar->addFormButton($this->lng->txt("book_select_pool"), "selectPool");
        }
    }

    protected function selectPool(): void
    {
        if ($this->return_to !== "") {
            $this->ctrl->redirectByClass($this->return_to);
        }
    }

    protected function setSubTabs(
        string $active
    ): void {
        $tabs = $this->tabs;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        if ($this->pools_selected) {
            $tabs->addSubTab(
                "book_obj",
                $lng->txt("book_booking_objects"),
                $ctrl->getLinkTargetByClass("ilbookingobjectservicegui", "")
            );
            $tabs->addSubTab(
                "reservations",
                $lng->txt("book_log"),
                $ctrl->getLinkTargetByClass("ilbookingreservationsgui", "")
            );
        }
        if ($this->ref_id === $this->main_host_ref_id) {
            $tabs->addSubTab(
                "settings",
                $lng->txt("settings"),
                $ctrl->getLinkTarget($this, "settings")
            );
        }

        $tabs->activateSubTab($active);
    }

    protected function show(): void
    {
        $ctrl = $this->ctrl;
        if ($this->pools_selected) {
            $ctrl->redirectByClass("ilbookingobjectservicegui");
        } elseif ($this->ref_id === $this->main_host_ref_id) {
            $ctrl->redirect($this, "settings");
        }

        $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("book_no_pools_selected"));
    }

    //
    // Settings
    //

    protected function settings(): void
    {
        $this->setSubTabs("settings");
        $main_tpl = $this->main_tpl;
        $form = $this->initSettingsForm();
        $main_tpl->setContent($form->getHTML());
    }

    public function initSettingsForm(): ilPropertyFormGUI
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $form = new ilPropertyFormGUI();

        // booking tools
        $repo = new ilRepositorySelector2InputGUI($this->lng->txt("objs_book"), "booking_obj_ids", true);
        $repo->getExplorerGUI()->setSelectableTypes(["book"]);
        $repo->getExplorerGUI()->setTypeWhiteList(
            ["book", "root", "cat", "grp", "fold", "crs"]
        );
        $form->addItem($repo);
        $repo->setValue($this->current_settings->getUsedBookingObjectIds());

        $form->addCommandButton("saveSettings", $lng->txt("save"));

        $form->setTitle($lng->txt("book_pool_selection"));
        $form->setFormAction($ctrl->getFormAction($this));

        return $form;
    }

    public function saveSettings(): void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $main_tpl = $this->main_tpl;

        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $b_ids = $form->getInput("booking_obj_ids");
            $b_ids = is_array($b_ids)
                ? array_map(static function ($i) {
                    return (int) $i;
                }, $b_ids)
                : [];

            if (!$this->checkBookingPoolsForSchedules($b_ids)) {
                $this->main_tpl->setOnScreenMessage('failure', $lng->txt("book_all_pools_need_schedules"));
                $form->setValuesByPost();
                $main_tpl->setContent($form->getHTML());
                return;
            }

            $cmd = new BookingManager\saveObjectSettingsCommand(new ilObjBookingServiceSettings(
                $this->obj_id,
                $b_ids
            ));

            $repo = $this->use_book_repo;
            $handler = new BookingManager\saveObjectSettingsCommandHandler($cmd, $repo);
            $handler->handle();

            $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ctrl->redirect($this, "");
        } else {
            $form->setValuesByPost();
            $main_tpl->setContent($form->getHTML());
        }
    }

    /**
     * Check if all pools have schedules
     * @param int[] $ids pool ref ids
     */
    protected function checkBookingPoolsForSchedules(array $ids): bool
    {
        foreach ($ids as $pool_ref_id) {
            $schedule_manager = $this->domain->schedules(ilObject::_lookupObjectId($pool_ref_id));
            if (!$schedule_manager->hasSchedules()) {
                return false;
            }
        }
        return true;
    }
}
