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

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\Sender\ResponseSendingException;

/**
 * This class represents a block method of a block.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilBlockGUI
{
    public const PRES_MAIN_LEG = 0;		// main legacy panel
    public const PRES_SEC_LEG = 1;			// secondary legacy panel
    public const PRES_SEC_LIST = 2;		// secondary list panel
    public const PRES_MAIN_LIST = 3;		// main standard list panel
    public const PRES_MAIN_TILE = 4;		// main standard list panel
    private int $offset;
    private int $limit;
    private bool $enableedit;
    private string $subtitle;
    private int $refid;
    private string $rowtemplatename;
    private string $rowtemplatedir;
    protected object $gui_object;
    protected \ILIAS\Block\StandardGUIRequest $request;
    protected \ILIAS\Block\BlockManager $block_manager;
    private \ILIAS\HTTP\GlobalHttpState $http;

    protected bool $repositorymode = false;
    protected \ILIAS\DI\UIServices $ui;
    protected array $data = array();
    protected bool $enablenuminfo = true;
    protected array $footer_links = array();
    protected string $block_id = "0";
    protected bool $allow_moving = true;
    protected array $move = array("left" => false, "right" => false, "up" => false, "down" => false);
    protected array $block_commands = array();
    protected int $max_count = 0;
    protected bool $close_command = false;
    protected bool $image = false;
    protected array $property = [];
    protected string $nav_value = "";
    protected string $css_row = "";
    protected string $title = "";
    protected bool $admincommands = false;
    protected array $dropdown;
    protected ?ilTemplate $tpl;
    protected ?ilGlobalTemplateInterface $main_tpl;
    protected ilObjUser $user;
    protected ilCtrl $ctrl;
    protected ilAccessHandler $access;
    protected ilLanguage $lng;
    protected ilObjectDefinition $obj_def;
    protected int $presentation;
    protected ?int $requested_ref_id;

    public function __construct()
    {
        global $DIC;


        $this->http = $DIC->http();
        $block_service = new ILIAS\Block\Service($DIC);
        $this->block_manager = $block_service->internal()
            ->domain()
            ->block();
        $this->request = $block_service->internal()
            ->gui()
            ->standardRequest();


        // default presentation
        $this->presentation = self::PRES_SEC_LEG;

        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        $this->main_tpl = $DIC["tpl"];
        $this->obj_def = $DIC["objDefinition"];
        $this->ui = $DIC->ui();

        ilYuiUtil::initConnection();
        $this->main_tpl->addJavaScript("./Services/Block/js/ilblockcallback.js");

        $this->setLimit((int) $this->user->getPref("hits_per_page"));

        $this->requested_ref_id = $this->request->getRefId();
    }

    abstract public function getBlockType(): string;

    /**
     * Returns whether block has a corresponding repository object
     */
    abstract protected function isRepositoryObject(): bool;

    public function setData(array $a_data): void
    {
        $this->data = $a_data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setPresentation(int $type): void
    {
        $this->presentation = $type;
    }

    public function getPresentation(): int
    {
        return $this->presentation;
    }

    public function setBlockId(string $a_block_id = "0"): void
    {
        $this->block_id = $a_block_id;
    }

    public function getBlockId(): string
    {
        return $this->block_id;
    }

    /**
     * Set GuiObject.
     * Only used for repository blocks, that are represented as
     * real repository objects (have a ref id and permissions)
     */
    public function setGuiObject(object $a_gui_object): void
    {
        $this->gui_object = $a_gui_object;
    }

    public function getGuiObject(): object
    {
        return $this->gui_object;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setOffset(int $a_offset): void
    {
        $this->offset = $a_offset;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function correctOffset(): void
    {
        if (!($this->offset < $this->max_count)) {
            $this->setOffset(0);
        }
    }

    public function setLimit(int $a_limit): void
    {
        $this->limit = $a_limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setEnableEdit(bool $a_enableedit): void
    {
        $this->enableedit = $a_enableedit;
    }

    public function getEnableEdit(): bool
    {
        return $this->enableedit;
    }

    public function setRepositoryMode(bool $a_repositorymode): void
    {
        $this->repositorymode = $a_repositorymode;
    }

    public function getRepositoryMode(): bool
    {
        return $this->repositorymode;
    }

    public function setSubtitle(string $a_subtitle): void
    {
        $this->subtitle = $a_subtitle;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    /**
     * Set Ref Id (only used if isRepositoryObject() is true).
     */
    public function setRefId(int $a_refid): void
    {
        $this->refid = $a_refid;
    }

    public function getRefId(): int
    {
        return $this->refid;
    }

    public function setAdminCommands(bool $a_admincommands): void
    {
        $this->admincommands = $a_admincommands;
    }

    public function getAdminCommands(): bool
    {
        return $this->admincommands;
    }

    public function setEnableNumInfo(bool $a_enablenuminfo): void
    {
        $this->enablenuminfo = $a_enablenuminfo;
    }

    public function getEnableNumInfo(): bool
    {
        return $this->enablenuminfo;
    }

    /**
     * This function is supposed to be used for block type specific
     * properties, that should be inherited through ilColumnGUI->setBlockProperties
     */
    public function setProperties(array $a_properties): void
    {
        $this->property = $a_properties;
    }

    public function getProperty(string $a_property): ?string
    {
        return $this->property[$a_property] ?? null;
    }

    public function setProperty(string $a_property, string $a_value): void
    {
        $this->property[$a_property] = $a_value;
    }

    /**
     * Set Row Template Name.
     */
    public function setRowTemplate(
        string $a_rowtemplatename,
        string $a_rowtemplatedir = ""
    ): void {
        $this->rowtemplatename = $a_rowtemplatename;
        $this->rowtemplatedir = $a_rowtemplatedir;
    }

    final public function getNavParameter(): string
    {
        return $this->getBlockType() . "_" . $this->getBlockId() . "_blnav";
    }

    final public function getConfigParameter(): string
    {
        return $this->getBlockType() . "_" . $this->getBlockId() . "_blconf";
    }

    final public function getMoveParameter(): string
    {
        return $this->getBlockType() . "_" . $this->getBlockId() . "_blmove";
    }

    public function getRowTemplateName(): string
    {
        return $this->rowtemplatename;
    }

    public function getRowTemplateDir(): string
    {
        return $this->rowtemplatedir;
    }

    public function addBlockCommand(string $a_href, string $a_text, string $a_onclick = ""): void
    {
        $this->block_commands[] = [
            "href" => $a_href,
            "text" => $a_text,
            "onclick" => $a_onclick
        ];
    }

    public function getBlockCommands(): array
    {
        return $this->block_commands;
    }

    public static function getScreenMode(): string
    {
        return IL_SCREEN_SIDE;
    }

    protected function initCommands(): void
    {
    }

    public function getHTML(): string
    {
        $this->initCommands();

        if ($this->new_rendering) {
            return $this->getHTMLNew();
        }

        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;
        $ilUser = $this->user;
        $objDefinition = $this->obj_def;

        if ($this->isRepositoryObject()) {
            if (!$ilAccess->checkAccess("read", "", $this->getRefId())) {
                return "";
            }
        }

        $this->tpl = new ilTemplate("tpl.block.html", true, true, "Services/Block");

        //		$this->handleConfigStatus();

        $this->fillDataSection();
        if ($this->getRepositoryMode() && $this->isRepositoryObject()) {
            // #10993
            // @todo: fix this in new presentation somehow
            if ($this->getAdminCommands()) {
                $this->tpl->setCurrentBlock("block_check");
                $this->tpl->setVariable("BL_REF_ID", $this->getRefId());
                $this->tpl->parseCurrentBlock();
            }

            if ($ilAccess->checkAccess("delete", "", $this->getRefId())) {
                $this->addBlockCommand(
                    "ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $this->requested_ref_id . "&cmd=delete" .
                    "&item_ref_id=" . $this->getRefId(),
                    $lng->txt("delete")
                );

                // see ilObjectListGUI::insertCutCommand();
                $this->addBlockCommand(
                    "ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $this->requested_ref_id . "&cmd=cut" .
                    "&item_ref_id=" . $this->getRefId(),
                    $lng->txt("move")
                );
            }

            // #14595 - see ilObjectListGUI::insertCopyCommand()
            if ($ilAccess->checkAccess("copy", "", $this->getRefId())) {
                $parent_type = ilObject::_lookupType($this->requested_ref_id, true);
                $parent_gui = "ilObj" . $objDefinition->getClassName($parent_type) . "GUI";

                $ilCtrl->setParameterByClass("ilobjectcopygui", "source_id", $this->getRefId());
                $copy_cmd = $ilCtrl->getLinkTargetByClass(
                    array("ilrepositorygui", $parent_gui, "ilobjectcopygui"),
                    "initTargetSelection"
                );

                // see ilObjectListGUI::insertCopyCommand();
                $this->addBlockCommand(
                    $copy_cmd,
                    $lng->txt("copy")
                );
            }
        }

        $this->dropdown = array();

        // commands
        if (count($this->getBlockCommands()) > 0) {
            foreach ($this->getBlockCommands() as $command) {
                if ($command["onclick"]) {
                    $command["onclick"] = "ilBlockJSHandler('" . "block_" . $this->getBlockType() . "_" . $this->block_id .
                        "','" . $command["onclick"] . "')";
                }
                $this->dropdown[] = $command;
            }
        }

        // fill previous next
        $this->fillPreviousNext();

        // fill footer
        $this->fillFooter();


        $this->fillHeaderCommands();
        $this->fillHeaderTitleBlock();

        if ($this->getPresentation() === self::PRES_MAIN_LEG) {
            $this->tpl->touchBlock("hclassb");
        } else {
            $this->tpl->touchBlock("hclass");
        }

        if ($ilCtrl->isAsynch()) {
            // return without div wrapper
            echo $this->tpl->get();
        //echo $this->tpl->getAsynch();
        } else {
            // return incl. wrapping div with id
            return '<div id="' . "block_" . $this->getBlockType() . "_" . $this->block_id . '">' .
                $this->tpl->get() . '</div>';
        }
        return "";
    }

    public function fillHeaderCommands(): void
    {
        // adv selection gui
        $dropdown = new ilAdvancedSelectionListGUI();
        $dropdown->setUseImages(true);
        $dropdown->setStyle(ilAdvancedSelectionListGUI::STYLE_LINK_BUTTON);
        $dropdown->setHeaderIcon(ilAdvancedSelectionListGUI::ICON_CONFIG);
        $dropdown->setId("block_dd_" . $this->getBlockType() . "_" . $this->block_id);
        foreach ($this->dropdown as $item) {
            if ($item["href"] || $item["onclick"]) {
                if (isset($item["checked"]) && $item["checked"]) {
                    $item["image"] = ilUtil::getImagePath("icon_checked.svg");
                }
                $dropdown->addItem(
                    $item["text"],
                    "",
                    $item["href"],
                    $item["image"] ?? "",
                    $item["text"],
                    "",
                    "",
                    false,
                    $item["onclick"]
                );
            }
        }
        $dropdown = $dropdown->getHTML();
        $this->tpl->setCurrentBlock("header_dropdown");
        $this->tpl->setVariable("ADV_DROPDOWN", $dropdown);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("hitem");
        $this->tpl->parseCurrentBlock();
    }

    public function fillHeaderTitleBlock(): void
    {
        $lng = $this->lng;


        // header title
        $this->tpl->setCurrentBlock("header_title");
        $this->tpl->setVariable(
            "BTID",
            "block_" . $this->getBlockType() . "_" . $this->block_id
        );
        $this->tpl->setVariable(
            "BLOCK_TITLE",
            $this->getTitle()
        );
        $this->tpl->setVariable(
            "TXT_BLOCK",
            $lng->txt("block")
        );
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("hitem");
        $this->tpl->parseCurrentBlock();
    }

    /**
     * Call this from overwritten fillDataSection(), if standard row based data is not used.
     */
    public function setDataSection(string $a_content): void
    {
        $this->tpl->setCurrentBlock("data_section");
        $this->tpl->setVariable("DATA", $a_content);
        $this->tpl->parseCurrentBlock();
        $this->tpl->setVariable("BLOCK_ROW", "");
    }

    /**
     * Standard implementation for row based data.
     * Overwrite this and call setContent for other data.
     */
    public function fillDataSection(): void
    {
        $req_nav_par = $this->request->getNavPar($this->getNavParameter());
        if ($req_nav_par != "") {
            $this->nav_value = $req_nav_par;
        }
        $this->nav_value = ($this->nav_value != "")
            ? $this->nav_value
            : $this->block_manager->getNavPar($this->getNavParameter());

        $this->block_manager->setNavPar(
            $this->getNavParameter(),
            $this->nav_value
        );

        $nav = explode(":", $this->nav_value);
        if (isset($nav[2])) {
            $this->setOffset((int) $nav[2]);
        } else {
            $this->setOffset(0);
        }

        // data
        $this->tpl->addBlockFile(
            "BLOCK_ROW",
            "block_row",
            $this->getRowTemplateName(),
            $this->getRowTemplateDir()
        );

        $data = $this->getData();
        $this->max_count = count($data);
        $this->correctOffset();
        $data = array_slice($data, $this->getOffset(), $this->getLimit());

        $this->preloadData($data);

        foreach ($data as $record) {
            $this->tpl->setCurrentBlock("block_row");
            $this->fillRowColor();
            $this->fillRow($record);
            $this->tpl->setCurrentBlock("block_row");
            $this->tpl->parseCurrentBlock();
        }
    }

    public function fillRow(array $a_set): void
    {
        foreach ($a_set as $key => $value) {
            $this->tpl->setVariable("VAL_" . strtoupper($key), $value);
        }
    }

    public function fillFooter(): void
    {
    }

    final protected function fillRowColor(string $a_placeholder = "CSS_ROW"): void
    {
        $this->css_row = ($this->css_row != "ilBlockRow1")
            ? "ilBlockRow1"
            : "ilBlockRow2";
        $this->tpl->setVariable($a_placeholder, $this->css_row);
    }

    public function fillPreviousNext(): void
    {
        $lng = $this->lng;

        // table pn numinfo
        $numinfo = "";
        if ($this->getEnableNumInfo() && $this->max_count > 0) {
            $start = $this->getOffset() + 1;                // compute num info
            $end = $this->getOffset() + $this->getLimit();

            if ($end > $this->max_count or $this->getLimit() == 0) {
                $end = $this->max_count;
            }

            $numinfo = "(" . $start . "-" . $end . " " . strtolower($lng->txt("of")) . " " . $this->max_count . ")";
        }

        $this->setPreviousNextLinks();
        $this->tpl->setVariable("NUMINFO", $numinfo);
    }

    public function setPreviousNextLinks(): void
    {
    }

    /**
     * Can be overwritten in subclasses. Only the visible part of the complete data was passed so a preload of the visible data is possible.
     * @param array $data
     */
    protected function preloadData(array $data): void
    {
    }

    /**
     * Use this for final get before sending asynchronous output (ajax)
     * per echo to output.
     */
    public function getAsynch(): string
    {
        header("Content-type: text/html; charset=UTF-8");
        return $this->tpl->get();
    }

    //
    // New rendering
    //

    // temporary flag
    protected bool $new_rendering = false;


    /**
     * Get legacy content
     *
     * @return string
     */
    protected function getLegacyContent(): string
    {
        return "";
    }

    /**
     * Get view controls
     *
     * @return array
     */
    protected function getViewControls(): array
    {
        if ($this->getPresentation() == self::PRES_SEC_LIST) {
            $pg_view_control = $this->getPaginationViewControl();
            if (!is_null($pg_view_control)) {
                return [$pg_view_control];
            }
        }
        return [];
    }

    /**
     * Get list item for data array
     *
     * @param array $data
     * @return null|\ILIAS\UI\Component\Item\Item
     */
    protected function getListItemForData(array $data): ?\ILIAS\UI\Component\Item\Item
    {
        return null;
    }


    /**
     * Handle navigation
     */
    protected function handleNavigation(): void
    {
        $reg_page = $this->request->getNavPage($this->getNavParameter());
        if ($reg_page !== "") {
            $this->nav_value = "::" . ($reg_page * $this->getLimit());
        }

        if ($this->nav_value == "") {
            $this->nav_value = $this->block_manager->getNavPar($this->getNavParameter());
        }

        $this->block_manager->setNavPar(
            $this->getNavParameter(),
            $this->nav_value
        );

        $nav = explode(":", $this->nav_value);
        if (isset($nav[2])) {
            $this->setOffset((int) $nav[2]);
        } else {
            $this->setOffset(0);
        }
    }

    /**
     * Load data for current page
     * @return array
     */
    protected function loadData(): array
    {
        $data = $this->getData();
        $this->max_count = count($data);
        $this->correctOffset();
        $data = array_slice($data, $this->getOffset(), $this->getLimit());
        $this->preloadData($data);
        return $data;
    }


    /**
     * Get items
     *
     * @return \ILIAS\UI\Component\Item\Group[]
     */
    protected function getListItemGroups(): array
    {
        global $DIC;
        $factory = $DIC->ui()->factory();

        $data = $this->loadData();

        $items = [];

        foreach ($data as $record) {
            $item = $this->getListItemForData($record);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        $item_group = $factory->item()->group("", $items);

        return [$item_group];
    }

    /**
     * Fill previous/next row
     */
    public function getPaginationViewControl(): ?\ILIAS\UI\Component\ViewControl\Pagination
    {
        global $DIC;
        $factory = $DIC->ui()->factory();

        $ilCtrl = $this->ctrl;


        //		$ilCtrl->setParameterByClass("ilcolumngui",
        //			$this->getNavParameter(), "::" . $prevoffset);

        // ajax link
        $ilCtrl->setParameterByClass(
            "ilcolumngui",
            "block_id",
            "block_" . $this->getBlockType() . "_" . $this->block_id
        );
        $block_id = "block_" . $this->getBlockType() . "_" . $this->block_id;
        $onclick = $ilCtrl->getLinkTargetByClass(
            "ilcolumngui",
            "updateBlock",
            "",
            true
        );
        $ilCtrl->setParameterByClass(
            "ilcolumngui",
            "block_id",
            ""
        );

        // normal link
        $href = $ilCtrl->getLinkTargetByClass("ilcolumngui", "", "", false, false);

        //$ilCtrl->setParameterByClass("ilcolumngui",
        //	$this->getNavParameter(), "");

        if ($this->max_count <= $this->getLimit()) {
            return null;
        }

        return $factory->viewControl()->pagination()
            ->withTargetURL($href, $this->getNavParameter() . "page")
            ->withTotalEntries($this->max_count)
            ->withPageSize($this->getLimit())
            ->withMaxPaginationButtons(5)
            ->withCurrentPage($this->getOffset() / $this->getLimit());
    }

    /**
     * Add repo commands
     */
    protected function addRepoCommands(): void
    {
        $access = $this->access;
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $obj_def = $this->obj_def;

        if ($this->getRepositoryMode() && $this->isRepositoryObject()) {
            // #10993
            // @todo: fix this in new presentation somehow
            /*
            if ($this->getAdminCommands()) {
                $this->tpl->setCurrentBlock("block_check");
                $this->tpl->setVariable("BL_REF_ID", $this->getRefId());
                $this->tpl->parseCurrentBlock();
            }*/

            if ($access->checkAccess("delete", "", $this->getRefId())) {
                $this->addBlockCommand(
                    "ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $this->requested_ref_id . "&cmd=delete" .
                    "&item_ref_id=" . $this->getRefId(),
                    $lng->txt("delete")
                );

                // see ilObjectListGUI::insertCutCommand();
                $this->addBlockCommand(
                    "ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $this->requested_ref_id . "&cmd=cut" .
                    "&item_ref_id=" . $this->getRefId(),
                    $lng->txt("move")
                );
            }

            // #14595 - see ilObjectListGUI::insertCopyCommand()
            if ($access->checkAccess("copy", "", $this->getRefId())) {
                $parent_type = ilObject::_lookupType($this->requested_ref_id, true);
                $parent_gui = "ilObj" . $obj_def->getClassName($parent_type) . "GUI";

                $ctrl->setParameterByClass("ilobjectcopygui", "source_id", $this->getRefId());
                $copy_cmd = $ctrl->getLinkTargetByClass(
                    array("ilrepositorygui", $parent_gui, "ilobjectcopygui"),
                    "initTargetSelection"
                );

                // see ilObjectListGUI::insertCopyCommand();
                $this->addBlockCommand(
                    $copy_cmd,
                    $lng->txt("copy")
                );
            }
        }
    }

    public function getHTMLNew(): string
    {
        global $DIC;
        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        $access = $this->access;
        $panel = null;

        $ctrl = $this->ctrl;

        if ($this->isRepositoryObject()) {
            if (!$access->checkAccess("read", "", $this->getRefId())) {
                return "";
            }
        }

        $this->addRepoCommands();

        switch ($this->getPresentation()) {
            case self::PRES_SEC_LEG:
                $panel = $factory->panel()->secondary()->legacy(
                    $this->getTitle(),
                    $factory->legacy($this->getLegacyContent())
                );
                break;

            case self::PRES_MAIN_LEG:
                $panel = $factory->panel()->standard(
                    $this->getTitle(),
                    $factory->legacy($this->getLegacyContent())
                );
                break;

            case self::PRES_SEC_LIST:
                $this->handleNavigation();
                $panel = $factory->panel()->secondary()->listing(
                    $this->getTitle(),
                    $this->getListItemGroups()
                );
                break;

            case self::PRES_MAIN_TILE:
            case self::PRES_MAIN_LIST:
                $this->handleNavigation();
                $panel = $factory->panel()->listing()->standard(
                    $this->getTitle(),
                    $this->getListItemGroups()
                );
                break;

        }

        // actions
        $actions = [];

        foreach ($this->getBlockCommands() as $command) {
            $href = ($command["onclick"] != "")
                ? ""
                : $command["href"];
            $button = $factory->button()->shy($command["text"], $href);
            if ($command["onclick"]) {
                $button = $button->withOnLoadCode(function ($id) use ($command) {
                    return
                        "$(\"#$id\").click(function() { ilBlockJSHandler('" . "block_" . $this->getBlockType() . "_" . $this->block_id .
                        "','" . $command["onclick"] . "');});";
                });
            }
            $actions[] = $button;
        }

        // check for empty list panel
        if (in_array($this->getPresentation(), [self::PRES_SEC_LIST, self::PRES_MAIN_LIST]) &&
            (count($panel->getItemGroups()) == 0 || (count($panel->getItemGroups()) == 1 && count($panel->getItemGroups()[0]->getItems()) == 0))) {
            if ($this->getPresentation() == self::PRES_SEC_LIST) {
                $panel = $factory->panel()->secondary()->legacy(
                    $this->getTitle(),
                    $factory->legacy($this->getNoItemFoundContent())
                );
            } else {
                $panel = $factory->panel()->standard(
                    $this->getTitle(),
                    $factory->legacy($this->getNoItemFoundContent())
                );
            }
        }


        if (count($actions) > 0) {
            $actions = $factory->dropdown()->standard($actions)
                ->withAriaLabel(sprintf(
                    $this->lng->txt('actions_for'),
                    htmlspecialchars($this->getTitle())
                ));
            $panel = $panel->withActions($actions);
        }

        // view controls
        if (count($this->getViewControls()) > 0) {
            $panel = $panel->withViewControls($this->getViewControls());
        }

        if ($ctrl->isAsynch()) {
            $html = $renderer->renderAsync($panel);
        } else {
            $html = $renderer->render($panel);
        }


        if ($ctrl->isAsynch()) {
            $this->send($html);
        } else {
            // return incl. wrapping div with id
            $html = '<div id="' . "block_" . $this->getBlockType() . "_" . $this->block_id . '">' .
                $html . '</div>';
        }

        return $html;
    }

    /**
     * Send
     * @throws ResponseSendingException
     */
    protected function send(string $output): void
    {
        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($output)
        ));
        $this->http->sendResponse();
        $this->http->close();
    }

    public function getNoItemFoundContent(): string
    {
        return $this->lng->txt("no_items");
    }
}
