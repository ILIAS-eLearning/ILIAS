<?php

declare(strict_types=1);

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

use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Item\Item;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HttpServices;

/**
 * @author            Jesús López Reyes <lopez@leifos.com>
 * @version           $Id$
 * @ilCtrl_IsCalledBy ilAppointmentPresentationGUI: ilCalendarAppointmentPresentationGUI
 * @ingroup           ServicesCalendar
 */
class ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{
    protected static self $instance;

    protected array $appointment;

    protected ?ilToolbarGUI $toolbar;
    protected ?ilInfoScreenGUI $infoscreen;
    protected ilLanguage $lng;
    protected ilTree $tree;
    protected UIServices $ui;
    protected ilCtrlInterface $ctrl;
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected ilObjUser $user;
    protected RefineryFactory $refinery;
    protected HttpServices $http;


    protected ?Item $list_item = null;

    protected array $info_items = [];
    protected array $list_properties = [];
    protected array $actions = [];

    /**
     * @var int[] readable ref ids for an object id
     */
    protected array $readable_ref_ids;

    protected bool $has_files = false;
    protected int $obj_id = 0;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        array $a_appointment,
        ?ilInfoScreenGUI $a_info_screen,
        ?ilToolbarGUI $a_toolbar,
        ?Item $a_list_item
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->appointment = $a_appointment;
        $this->infoscreen = $a_info_screen;
        $this->info_items = [];
        $this->toolbar = $a_toolbar;
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("dateplaner");
        $this->tree = $DIC->repositoryTree();
        $this->ui = $DIC->ui();
        $this->list_item = $a_list_item;
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();
        $this->readObjIdForAppointment();
    }

    public function getObjIdForAppointment(): int
    {
        return $this->obj_id;
    }

    /**
     * read obj_id for appointment
     */
    protected function readObjIdForAppointment(): void
    {
        $cat_id = $this->getCatId($this->appointment['event']->getEntryId());
        $category = ilCalendarCategory::getInstanceByCategoryId($cat_id);
        $this->obj_id = $category->getObjId();
    }

    public static function getInstance(
        array $a_appointment,
        ?ilInfoScreenGUI $a_info_screen,
        ?ilToolbarGUI $a_toolbar,
        ?Item $a_list_item
    ): ilCalendarAppointmentPresentation {
        return new static($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
    }

    public function getToolbar(): ?ilToolbarGUI
    {
        return $this->toolbar;
    }

    /**
     * Get list item
     * @return \ILIAS\UI\Component\Item\Item
     */
    public function getListItem(): ?Item
    {
        return $this->list_item;
    }

    /**
     * @return ilInfoScreenGUI
     */
    public function getInfoScreen(): ?ilInfoScreenGUI
    {
        return $this->infoscreen;
    }

    public function getCatId(int $a_entry_id): int
    {
        return ilCalendarCategoryAssignments::_lookupCategory($a_entry_id);
    }

    public function getCatInfo(): array
    {
        $cat_id = $this->getCatId($this->appointment['event']->getEntryId());
        return ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd("getHTML");
        switch ($next_class) {
            default:
                $this->$cmd();
        }
    }

    public function getHTML(): string
    {
        $this->collectStandardPropertiesAndActions();
        $this->collectPropertiesAndActions();
        $ui = $this->ui;

        $infoscreen = $this->getInfoScreen();
        if ($infoscreen instanceof ilInfoScreenGUI) {
            foreach ($this->info_items as $i) {
                switch ($i["type"]) {
                    case "section":
                        $infoscreen->addSection($i["txt"]);
                        break;
                    case "property":
                        $infoscreen->addProperty($i["txt"], $i["val"]);
                        break;
                }
            }
        }

        $toolbar = $this->getToolbar();
        if ($toolbar instanceof ilToolbarGUI) {
            //todo: duplicated from ilcalendarviewgui.
            $settings = ilCalendarSettings::_getInstance();
            if ($settings->isBatchFileDownloadsEnabled() && $this->has_files) {
                // file download
                $this->ctrl->setParameter($this, "app_id", $this->appointment['event']->getEntryId());

                $download_btn = ilLinkButton::getInstance();
                $download_btn->setCaption($this->lng->txt("cal_download_files"), false);
                $download_btn->setUrl(
                    $this->ctrl->getLinkTarget($this, 'downloadFiles')
                );
                $this->ctrl->setParameter($this, "app_id", '');
                $toolbar->addButtonInstance($download_btn);
                $toolbar->addSeparator();
            }

            foreach ($this->actions as $a) {
                $btn = ilLinkButton::getInstance();
                $btn->setCaption($a["txt"], false);
                $btn->setUrl($a["link"]);
                // all buttons are sticky
                $toolbar->addStickyItem($btn);
            }
        }

        $list_item = $this->getListItem();
        if ($list_item instanceof \ILIAS\UI\Component\Item\Standard) {
            $dd = $list_item->getActions();
            if ($dd === null) {
                $actions = array();
                $label = "";
            } else {
                $actions = $dd->getItems();
                $label = $dd->getLabel();
            }
            $properties = $list_item->getProperties();

            foreach ($this->actions as $a) {
                $actions[] = $ui->factory()->button()->shy($a["txt"], $a["link"]);
            }
            foreach ($this->list_properties as $lp) {
                $properties[$lp["txt"]] = $lp["val"];
            }

            $new_dd = $ui->factory()->dropdown()
                         ->standard($actions)
                         ->withLabel($label);
            $this->list_item = $list_item
                ->withActions($new_dd)
                ->withProperties($properties);
        }
        return '';
    }

    /**
     * Add course/group container info
     */
    public function addContainerInfo(int $a_obj_id): void
    {
        $refs = $this->getReadableRefIds($a_obj_id);
        $ref_id = current($refs);
        if (count($refs) == 1 && $ref_id > 0) {
            $tree = $this->tree;
            $f = $this->ui->factory();
            $r = $this->ui->renderer();

            //parent course or group title
            $cont_ref_id = $tree->checkForParentType($ref_id, 'grp');
            if ($cont_ref_id == 0) {
                $cont_ref_id = $tree->checkForParentType($ref_id, 'crs');
            }

            if ($cont_ref_id > 0) {
                $type = ilObject::_lookupType($cont_ref_id, true);
                $href = ilLink::_getStaticLink($cont_ref_id);
                $parent_title = ilObject::_lookupTitle(ilObject::_lookupObjectId($cont_ref_id));
                $this->addInfoProperty(
                    $this->lng->txt("obj_" . $type),
                    $r->render($f->button()->shy($parent_title, $href))
                );
                $this->addListItemProperty(
                    $this->lng->txt("obj_" . $type),
                    $r->render($f->button()->shy($parent_title, $href))
                );
            }
        }
    }

    /**
     * Add info section
     */
    public function addInfoSection(string $a_txt): void
    {
        $this->info_items[] = array("type" => "section", "txt" => $a_txt);
    }

    /**
     * Add info property
     */
    public function addInfoProperty(string $a_txt, string $a_val): void
    {
        $this->info_items[] = array("type" => "property", "txt" => $a_txt, "val" => $a_val);
    }

    /**
     * Add list item property
     */
    public function addListItemProperty(string $a_txt, string $a_val): void
    {
        #22638
        $this->list_properties[] = array("txt" => $a_txt, "val" => $a_val);
    }

    /**
     * Add action
     */
    public function addAction(string $a_txt, string $a_link): void
    {
        $this->actions[] = array("txt" => $a_txt, "link" => $a_link);
    }

    /**
     * Collect properties and actions
     */
    public function collectPropertiesAndActions(): void
    {
    }

    /**
     * Collect standard properties and actions
     */
    public function collectStandardPropertiesAndActions(): void
    {
        $cat_info = $this->getCatInfo();

        //we can move this to the factory.
        if ($cat_info['editable'] && !$this->appointment['event']->isAutoGenerated()) {
            $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
            //			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed', $this->getSeed()->get(IL_CAL_DATE));
            $this->ctrl->setParameterByClass(
                'ilcalendarappointmentgui',
                'app_id',
                $this->appointment['event']->getEntryId()
            );
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'dt', $this->appointment['dstart']);

            $this->addAction(
                $this->lng->txt("edit"),
                $this->ctrl->getLinkTargetByClass(array('ilcalendarappointmentgui'), 'askEdit')
            );

            $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
            //			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$this->getSeed()->get(IL_CAL_DATE));
            $this->ctrl->setParameterByClass(
                'ilcalendarappointmentgui',
                'app_id',
                $this->appointment['event']->getEntryId()
            );
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'dt', $this->appointment['dstart']);

            $this->addAction(
                $this->lng->txt("delete"),
                $this->ctrl->getLinkTargetByClass(array('ilcalendarappointmentgui'), 'askDelete')
            );
        }
    }

    /**
     * Add object link
     */
    public function addObjectLinks(int $obj_id, ?array $a_appointment = null): void
    {
        $refs = $this->getReadableRefIds($obj_id);
        reset($refs);
        $title = ilObject::_lookupTitle($obj_id);
        $buttons = array();
        foreach ($refs as $ref_id) {
            $link_title = $title;
            if (count($refs) > 1) {
                $par_ref = $this->tree->getParentId($ref_id);
                $link_title .= " (" . ilObject::_lookupTitle(ilObject::_lookupObjId($par_ref)) . ")";
            }

            $link = $this->buildDirectLinkForAppointment($ref_id, $a_appointment);

            $buttons[] = $this->ui->renderer()->render(
                $this->ui->factory()->button()->shy($link_title, $link)
            );
        }
        if ($refs == 0) {
            $prop_value = $title;
        } else {
            $prop_value = implode("<br>", $buttons);
        }
        if ($prop_value != '') {
            $this->addInfoProperty($this->lng->txt("obj_" . ilObject::_lookupType($obj_id)), $prop_value);
            $this->addListItemProperty($this->lng->txt("obj_" . ilObject::_lookupType($obj_id)), $prop_value);
        }
    }

    /**
     * Build direct link for appointment
     */
    protected function buildDirectLinkForAppointment(int $a_ref_id, ?array $a_appointment = null): string
    {
        return ilLink::_getStaticLink($a_ref_id);
    }

    /**
     * @param int $a_obj_id
     * @return int[]
     */
    public function getReadableRefIds(int $a_obj_id): array
    {
        if (!isset($this->readable_ref_ids[$a_obj_id])) {
            $ref_ids = array();
            foreach (ilObject::_getAllReferences($a_obj_id) as $ref_id) {
                if ($this->access->checkAccess("read", "", $ref_id)) {
                    $ref_ids[] = $ref_id;
                }
            }
            $this->readable_ref_ids[$a_obj_id] = $ref_ids;
        }
        return $this->readable_ref_ids[$a_obj_id];
    }

    /**
     * Add event description
     */
    public function addEventDescription(array $a_app): void
    {
        if ($a_app['event']->getDescription()) {
            $this->addInfoProperty(
                $this->lng->txt("description"),
                ilUtil::makeClickable(nl2br($a_app['event']->getDescription()))
            );
        }
    }

    /**
     * Add event location
     */
    public function addEventLocation(array $a_app): void
    {
        if ($a_app['event']->getLocation()) {
            $this->addInfoProperty($this->lng->txt("cal_where"), $a_app['event']->getLocation());
            $this->addListItemProperty($this->lng->txt("location"), $a_app['event']->getLocation());
        }
    }

    /**
     * Add last update
     */
    public function addLastUpdate(array $a_app): void
    {
        $update = new ilDateTime(
            $a_app["event"]->getLastUpdate()->get(IL_CAL_UNIX),
            IL_CAL_UNIX,
            $this->user->getTimeZone()
        );
        $this->addListItemProperty($this->lng->txt('last_update'), ilDatePresentation::formatDate($update));
    }

    public function addCalendarInfo(array $cat_info): void
    {
        $this->ctrl->setParameterByClass("ilCalendarPresentationGUI", "category_id", $cat_info["cat_id"]);

        $link = $this->ui->renderer()->render(
            $this->ui->factory()->button()->shy(
                $cat_info["title"],
                $this->ctrl->getLinkTargetByClass(array("ilDashboardGUI", "ilCalendarPresentationGUI"), "")
            )
        );

        $this->ctrl->setParameterByClass("ilCalendarPresentationGUI", "category_id", '');
        $this->addInfoProperty($this->lng->txt("calendar"), $link);
        $this->addListItemProperty($this->lng->txt("calendar"), $link);
    }

    public function addCommonSection(
        array $a_app,
        int $a_obj_id = 0,
        ?array $cat_info = null,
        bool $a_container_info = false
    ): void {
        // event title
        $this->addInfoSection($a_app["event"]->getPresentationTitle(false));

        // event description
        $this->addEventDescription($a_app);

        // course title (linked of accessible)
        if ($a_obj_id > 0) {
            $this->addObjectLinks($a_obj_id, $a_app);
        }

        // container info (course groups)
        if ($a_container_info) {
            $this->addContainerInfo($a_obj_id);
        }

        // event location
        $this->addEventLocation($a_app);

        // calendar info
        if ($cat_info != null) {
            $this->addCalendarInfo($cat_info);
        }
    }

    public function addMetaData(
        string $a_obj_type,
        int $a_obj_id,
        ?string $a_sub_obj_type = null,
        ?int $a_sub_obj_id = null
    ): void {
        //TODO: Remove the hack in ilADTActiveRecordByType.php.
        $record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_APP_PRESENTATION,
            $a_obj_type,
            $a_obj_id,
            (string) $a_sub_obj_type,
            (int) $a_sub_obj_id
        );
        $md_items = $record_gui->parse();
        if (count($md_items)) {
            foreach ($md_items as $md_item) {
                $this->addInfoProperty($md_item['title'], $md_item['value']);
                $this->addListItemProperty($md_item['title'], $md_item['value']);
            }
        }
    }

    /**
     * Get (linked if possible) user name
     */
    public function getUserName(int $a_user_id, bool $a_force_name = false): string
    {
        $ref_id = 0;
        if ($this->http->wrapper()->query()->has('ref_id')) {
            $ref_id = $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $type = ilObject::_lookupType($ref_id, true);
        $ctrl_path = array();
        if ($type == "crs") {
            $ctrl_path[] = "ilobjcoursegui";
        }
        if ($type == "grp") {
            $ctrl_path[] = "ilobjgroupgui";
        }
        $baseClass = '';
        if ($this->http->wrapper()->query()->has('baseClass')) {
            $baseClass = $this->http->wrapper()->query()->retrieve(
                'baseClass',
                $this->refinery->kindlyTo()->string()
            );
        }
        if (strtolower($baseClass) == "ildashboardgui") {
            $ctrl_path[] = "ildashboardgui";
        }
        $ctrl_path[] = "ilCalendarPresentationGUI";
        $ctrl_path[] = "ilpublicuserprofilegui";

        return ilUserUtil::getNamePresentation(
            $a_user_id,
            false,
            true,
            $this->ctrl->getParentReturn($this),
            $a_force_name,
            false,
            true,
            false,
            $ctrl_path
        );
    }

    /**
     * Download files from an appointment ( Modals )
     */
    public function downloadFiles(): void
    {
        //calendar in the sidebar (marginal calendar)
        if (empty($this->appointment)) {
            $entry_id = 0;
            if ($this->http->wrapper()->query()->has('app_id')) {
                $entry_id = $this->http->wrapper()->query()->retrieve(
                    'app_id',
                    $this->refinery->kindlyTo()->int()
                );
            }
            $entry = new ilCalendarEntry($entry_id);
            //if the entry exists
            if ($entry->getStart()) {
                $this->appointment = array(
                    "event" => $entry,
                    "dstart" => $entry->getStart(),
                    "dend" => $entry->getEnd(),
                    "fullday" => $entry->isFullday()
                );
            } else {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("obj_not_found"), true);
                $this->ctrl->returnToParent($this);
            }
        }
        $download_job = new ilDownloadFilesBackgroundTask($this->user->getId());

        $download_job->setBucketTitle($this->lng->txt("cal_calendar_download") . " " . $this->appointment['event']->getTitle());
        $download_job->setEvents(array($this->appointment));
        if ($download_job->run()) {
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('cal_download_files_started'), true);
        }
        $this->ctrl->returnToParent($this);
    }
}
