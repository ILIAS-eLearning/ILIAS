<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents a block method of a block.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
abstract class ilBlockGUI
{
    const PRES_MAIN_LEG = 0;		// main legacy panel
    const PRES_SEC_LEG = 1;			// secondary legacy panel
    const PRES_SEC_LIST = 2;		// secondary list panel
    const PRES_MAIN_LIST = 3;		// main standard list panel

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @return string
     */
    abstract public function getBlockType() : string;

    /**
     * Returns whether block has a corresponding repository object
     *
     * @return bool
     */
    abstract protected function isRepositoryObject() : bool;

    protected $data = array();
    protected $enablenuminfo = true;
    protected $footer_links = array();
    protected $block_id = 0;
    protected $allow_moving = true;
    protected $move = array("left" => false, "right" => false, "up" => false, "down" => false);
    protected $block_commands = array();
    protected $max_count = false;
    protected $close_command = false;
    protected $image = false;
    protected $property = false;
    protected $nav_value = "";
    protected $css_row = "";

    /**
     * @var string
     */
    protected $title = "";


    /**
     * @var bool
     */
    protected $admincommands = false;

    protected $dropdown;

    /**
     * @var ilTemplate|null block template
     */
    protected $tpl;

    /**
     * @var ilTemplate|null main template
     */
    protected $main_tpl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var
     */
    protected $obj_def;

    /**
     * @var int
     */
    protected $presentation;

    /**
     * Constructor
     *
     * @param
     */
    public function __construct()
    {
        global $DIC;

        // default presentation
        $this->presentation = self::PRES_SEC_LEG;

        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        $this->main_tpl = $DIC["tpl"];
        $this->obj_def = $DIC["objDefinition"];
        $this->ui = $DIC->ui();

        include_once("./Services/YUI/classes/class.ilYuiUtil.php");
        ilYuiUtil::initConnection();
        $this->main_tpl->addJavaScript("./Services/Block/js/ilblockcallback.js");

        $this->setLimit($this->user->getPref("hits_per_page"));
    }


    /**
     * Set Data.
     *
     * @param    array $a_data Data
     */
    public function setData($a_data)
    {
        $this->data = $a_data;
    }

    /**
     * Get Data.
     *
     * @return    array    Data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set presentation
     *
     * @param int $type
     */
    public function setPresentation(int $type)
    {
        $this->presentation = $type;
    }

    /**
     * Get presentation type
     *
     * @return int
     */
    public function getPresentation() : int
    {
        return $this->presentation;
    }

    /**
     * Set Block Id
     *
     * @param    int $a_block_id Block ID
     */
    public function setBlockId($a_block_id = 0)
    {
        $this->block_id = $a_block_id;
    }

    /**
     * Get Block Id
     *
     * @return    int            Block Id
     */
    public function getBlockId()
    {
        return $this->block_id;
    }


    /**
     * Set GuiObject.
     * Only used for repository blocks, that are represented as
     * real repository objects (have a ref id and permissions)
     *
     * @param    object $a_gui_object GUI object
     */
    public function setGuiObject(&$a_gui_object)
    {
        $this->gui_object = $a_gui_object;
    }

    /**
     * Get GuiObject.
     *
     * @return    object    GUI object
     */
    public function getGuiObject()
    {
        return $this->gui_object;
    }


    /**
     * Set Title.
     *
     * @param    string $a_title Title
     */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    /**
     * Get Title.
     *
     * @return    string    Title
     */
    public function getTitle()
    {
        return (string) $this->title;
    }

    /**
     * Set Offset.
     *
     * @param    int $a_offset Offset
     */
    public function setOffset($a_offset)
    {
        $this->offset = $a_offset;
    }

    /**
     * Get Offset.
     *
     * @return    int    Offset
     */
    public function getOffset()
    {
        return $this->offset;
    }

    public function correctOffset()
    {
        if (!($this->offset < $this->max_count)) {
            $this->setOffset(0);
        }
    }

    /**
     * Set Limit.
     *
     * @param    int $a_limit Limit
     */
    public function setLimit($a_limit)
    {
        $this->limit = $a_limit;
    }

    /**
     * Get Limit.
     *
     * @return    int    Limit
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set EnableEdit.
     *
     * @param    boolean $a_enableedit EnableEdit
     */
    public function setEnableEdit($a_enableedit)
    {
        $this->enableedit = $a_enableedit;
    }

    /**
     * Get EnableEdit.
     *
     * @return    boolean    EnableEdit
     */
    public function getEnableEdit()
    {
        return $this->enableedit;
    }

    /**
     * Set RepositoryMode.
     *
     * @param    boolean $a_repositorymode RepositoryMode
     */
    public function setRepositoryMode($a_repositorymode)
    {
        $this->repositorymode = $a_repositorymode;
    }

    /**
     * Get RepositoryMode.
     *
     * @return    boolean    RepositoryMode
     */
    public function getRepositoryMode()
    {
        return $this->repositorymode;
    }


    /**
     * Set Subtitle.
     *
     * @param    string $a_subtitle Subtitle
     */
    public function setSubtitle($a_subtitle)
    {
        $this->subtitle = $a_subtitle;
    }

    /**
     * Get Subtitle.
     *
     * @return    string    Subtitle
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Set Ref Id (only used if isRepositoryObject() is true).
     *
     * @param    int $a_refid Ref Id
     */
    public function setRefId($a_refid)
    {
        $this->refid = $a_refid;
    }

    /**
     * Get Ref Id (only used if isRepositoryObject() is true).
     *
     * @return    int        Ref Id
     */
    public function getRefId()
    {
        return $this->refid;
    }

    /**
     * Set Administration Commmands.
     *
     * @param    boolean $a_admincommands Administration Commmands
     */
    public function setAdminCommands(bool $a_admincommands)
    {
        $this->admincommands = $a_admincommands;
    }

    /**
     * Get Administration Commmands.
     *
     * @return    boolean    Administration Commmands
     */
    public function getAdminCommands() : bool
    {
        return $this->admincommands;
    }

    /**
     * Set Enable Item Number Info.
     *
     * @param    boolean $a_enablenuminfo Enable Item Number Info
     */
    public function setEnableNumInfo($a_enablenuminfo)
    {
        $this->enablenuminfo = $a_enablenuminfo;
    }

    /**
     * Get Enable Item Number Info.
     *
     * @return    boolean    Enable Item Number Info
     */
    public function getEnableNumInfo()
    {
        return $this->enablenuminfo;
    }

    /**
     * This function is supposed to be used for block type specific
     * properties, that should be inherited through ilColumnGUI->setBlockProperties
     *
     * @param    string $a_properties properties array (key => value)
     */
    public function setProperties($a_properties)
    {
        $this->property = $a_properties;
    }

    public function getProperty($a_property)
    {
        return $this->property[$a_property];
    }

    public function setProperty($a_property, $a_value)
    {
        $this->property[$a_property] = $a_value;
    }

    /**
     * Set Row Template Name.
     *
     * @param    string $a_rowtemplatename Row Template Name
     */
    public function setRowTemplate($a_rowtemplatename, $a_rowtemplatedir = "")
    {
        $this->rowtemplatename = $a_rowtemplatename;
        $this->rowtemplatedir = $a_rowtemplatedir;
    }

    final public function getNavParameter()
    {
        return $this->getBlockType() . "_" . $this->getBlockId() . "_blnav";
    }

    final public function getConfigParameter()
    {
        return $this->getBlockType() . "_" . $this->getBlockId() . "_blconf";
    }

    final public function getMoveParameter()
    {
        return $this->getBlockType() . "_" . $this->getBlockId() . "_blmove";
    }

    /**
     * Get Row Template Name.
     *
     * @return    string    Row Template Name
     */
    public function getRowTemplateName()
    {
        return $this->rowtemplatename;
    }

    /**
     * Get Row Template Directory.
     *
     * @return    string    Row Template Directory
     */
    public function getRowTemplateDir()
    {
        return $this->rowtemplatedir;
    }

    /**
     * Add Block Command.
     *
     * @param string $a_href
     * @param string $a_text
     * @param string $a_onclick
     */
    public function addBlockCommand(string $a_href, string $a_text, string $a_onclick = "") : void
    {
        $this->block_commands[] = [
            "href" => $a_href,
            "text" => $a_text,
            "onclick" => $a_onclick
        ];
    }

    /**
     * Get Block commands.
     *
     * @return array
     */
    public function getBlockCommands() : array
    {
        return $this->block_commands;
    }



    /**
     * Get Screen Mode for current command.
     */
    public static function getScreenMode()
    {
        return IL_SCREEN_SIDE;
    }

    /**
     * Init commands
     */
    protected function initCommands()
    {
    }


    /**
     * Get HTML.
     */
    public function getHTML()
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
                    "ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $_GET["ref_id"] . "&cmd=delete" .
                    "&item_ref_id=" . $this->getRefId(),
                    $lng->txt("delete")
                );

                // see ilObjectListGUI::insertCutCommand();
                $this->addBlockCommand(
                    "ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $_GET["ref_id"] . "&cmd=cut" .
                    "&item_ref_id=" . $this->getRefId(),
                    $lng->txt("move")
                );
            }

            // #14595 - see ilObjectListGUI::insertCopyCommand()
            if ($ilAccess->checkAccess("copy", "", $this->getRefId())) {
                $parent_type = ilObject::_lookupType($_GET["ref_id"], true);
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


        // for screen readers we first output the title and the commands
        // (e.g. close icon afterwards), otherwise we first output the
        // header commands, since we want to have the close icon top right
        // and not floated after the title
        if (is_object($ilUser) && $ilUser->getPref("screen_reader_optimization")) {
            $this->fillHeaderTitleBlock();
            $this->fillHeaderCommands();
        } else {
            $this->fillHeaderCommands();
            $this->fillHeaderTitleBlock();
        }

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
    }

    /**
     * Fill header commands block
     */
    public function fillHeaderCommands()
    {
        // adv selection gui
        include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
        $dropdown = new ilAdvancedSelectionListGUI();
        $dropdown->setUseImages(true);
        $dropdown->setStyle(ilAdvancedSelectionListGUI::STYLE_LINK_BUTTON);
        $dropdown->setHeaderIcon(ilAdvancedSelectionListGUI::ICON_CONFIG);
        $dropdown->setId("block_dd_" . $this->getBlockType() . "_" . $this->block_id);
        foreach ($this->dropdown as $item) {
            if ($item["href"] || $item["onclick"]) {
                if ($item["checked"]) {
                    $item["image"] = ilUtil::getImagePath("icon_checked.svg");
                }
                $dropdown->addItem(
                    $item["text"],
                    "",
                    $item["href"],
                    $item["image"],
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


    /**
     * Fill header title block (title and
     */
    public function fillHeaderTitleBlock()
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
    public function setDataSection($a_content)
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
    public function fillDataSection()
    {
        $this->nav_value = (isset($_POST[$this->getNavParameter()]) && $_POST[$this->getNavParameter()] != "")
            ? $_POST[$this->getNavParameter()]
            : (isset($_GET[$this->getNavParameter()]) ? $_GET[$this->getNavParameter()] : $this->nav_value);
        $this->nav_value = ($this->nav_value == "" && isset($_SESSION[$this->getNavParameter()]))
            ? $_SESSION[$this->getNavParameter()]
            : $this->nav_value;

        $_SESSION[$this->getNavParameter()] = $this->nav_value;

        $nav = explode(":", $this->nav_value);
        if (isset($nav[2])) {
            $this->setOffset($nav[2]);
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

    public function fillRow($a_set)
    {
        foreach ($a_set as $key => $value) {
            $this->tpl->setVariable("VAL_" . strtoupper($key), $value);
        }
    }

    public function fillFooter()
    {
    }

    final protected function fillRowColor($a_placeholder = "CSS_ROW")
    {
        $this->css_row = ($this->css_row != "ilBlockRow1")
            ? "ilBlockRow1"
            : "ilBlockRow2";
        $this->tpl->setVariable($a_placeholder, $this->css_row);
    }

    /**
     * Fill previous/next row
     */
    public function fillPreviousNext()
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

    /**
     * Get previous/next linkbar.
     *
     * @author Sascha Hofmann <shofmann@databay.de>
     *
     * @return    array    linkbar or false on error
     */
    public function setPreviousNextLinks()
    {
        // @todo: fix this
        return false;


        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        // if more entries then entries per page -> show link bar
        if ($this->max_count > $this->getLimit() && ($this->getLimit() != 0)) {
            // previous link
            if ($this->getOffset() >= 1) {
                $prevoffset = $this->getOffset() - $this->getLimit();

                $ilCtrl->setParameterByClass(
                    "ilcolumngui",
                    $this->getNavParameter(),
                    "::" . $prevoffset
                );

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
                $href = $ilCtrl->getLinkTargetByClass("ilcolumngui", "");
                $text = $lng->txt("previous");

                //				$this->addFooterLink($text, $href, $onclick, $block_id, true);
            }

            // calculate number of pages
            $pages = intval($this->max_count / $this->getLimit());

            // add a page if a rest remains
            if (($this->max_count % $this->getLimit())) {
                $pages++;
            }

            // show next link (if not last page)
            if (!(($this->getOffset() / $this->getLimit()) == ($pages - 1)) && ($pages != 1)) {
                $newoffset = $this->getOffset() + $this->getLimit();

                $ilCtrl->setParameterByClass(
                    "ilcolumngui",
                    $this->getNavParameter(),
                    "::" . $newoffset
                );

                // ajax link
                $ilCtrl->setParameterByClass(
                    "ilcolumngui",
                    "block_id",
                    "block_" . $this->getBlockType() . "_" . $this->block_id
                );
                //$this->tpl->setCurrentBlock("pnonclick");
                $block_id = "block_" . $this->getBlockType() . "_" . $this->block_id;
                $onclick = $ilCtrl->getLinkTargetByClass(
                    "ilcolumngui",
                    "updateBlock",
                    "",
                    true
                );
                //echo "-".$onclick."-";
                //$this->tpl->parseCurrentBlock();
                $ilCtrl->setParameterByClass(
                    "ilcolumngui",
                    "block_id",
                    ""
                );

                // normal link
                $href = $ilCtrl->getLinkTargetByClass("ilcolumngui", "");
                $text = $lng->txt("next");

                //				$this->addFooterLink($text, $href, $onclick, $block_id, true);
            }
            $ilCtrl->setParameterByClass(
                "ilcolumngui",
                $this->getNavParameter(),
                ""
            );
            return true;
        } else {
            return false;
        }
    }

    /**
     * Can be overwritten in subclasses. Only the visible part of the complete data was passed so a preload of the visible data is possible.
     * @param array $data
     */
    protected function preloadData(array $data)
    {
    }

    /**
     * Use this for final get before sending asynchronous output (ajax)
     * per echo to output.
     */
    public function getAsynch()
    {
        header("Content-type: text/html; charset=UTF-8");
        return $this->tpl->get();
    }

    //
    // New rendering
    //

    // temporary flag
    protected $new_rendering = false;


    /**
     * Get legacy content
     *
     * @return string
     */
    protected function getLegacyContent() : string
    {
        return "";
    }

    /**
     * Get view controls
     *
     * @return array
     */
    protected function getViewControls() : array
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
    protected function getListItemForData(array $data) : \ILIAS\UI\Component\Item\Item
    {
        return null;
    }


    /**
     * Handle navigation
     */
    protected function handleNavigation()
    {
        $reg_page = $_REQUEST[$this->getNavParameter() . "page"];
        if ($reg_page !== "") {
            $this->nav_value = "::" . ($reg_page * $this->getLimit());
        }

        if ($this->nav_value == "" && isset($_SESSION[$this->getNavParameter()])) {
            $this->nav_value = $_SESSION[$this->getNavParameter()];
        }

        $_SESSION[$this->getNavParameter()] = $this->nav_value;

        $nav = explode(":", $this->nav_value);
        if (isset($nav[2])) {
            $this->setOffset($nav[2]);
        } else {
            $this->setOffset(0);
        }
    }

    /**
     * Load data for current page
     *
     * @return array
     */
    protected function loadData()
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
    protected function getListItemGroups() : array
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
    public function getPaginationViewControl()
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
            ->withCurrentPage((int) $this->getOffset() / $this->getLimit());
    }

    /**
     * Add repo commands
     */
    protected function addRepoCommands()
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
                    "ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $_GET["ref_id"] . "&cmd=delete" .
                    "&item_ref_id=" . $this->getRefId(),
                    $lng->txt("delete")
                );

                // see ilObjectListGUI::insertCutCommand();
                $this->addBlockCommand(
                    "ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $_GET["ref_id"] . "&cmd=cut" .
                    "&item_ref_id=" . $this->getRefId(),
                    $lng->txt("move")
                );
            }

            // #14595 - see ilObjectListGUI::insertCopyCommand()
            if ($access->checkAccess("copy", "", $this->getRefId())) {
                $parent_type = ilObject::_lookupType($_GET["ref_id"], true);
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

    /**
     * Get HTML.
     */
    public function getHTMLNew()
    {
        global $DIC;
        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        $access = $this->access;

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

            case self::PRES_MAIN_LIST:
                $this->handleNavigation();
                $panel = $factory->panel()->listing()->standard(
                    $this->getTitle(),
                    $this->getListItemGroups()
                );
                break;

            case self::PRES_MAIN_TILE:
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
                    $this->getTitle()
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
            echo $html;
            exit;
        } else {
            // return incl. wrapping div with id
            $html = '<div id="' . "block_" . $this->getBlockType() . "_" . $this->block_id . '">' .
                $html . '</div>';
        }

        //$this->new_rendering = false;
        //$html.= $this->getHTML();

        return $html;
    }

    /**
     * No item entry
     *
     * @return string
     */
    protected function getNoItemFoundContent() : string
    {
        return $this->lng->txt("no_items");
    }
}
