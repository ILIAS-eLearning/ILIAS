<?php declare(strict_types = 1);

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

use ILIAS\HTTP\Agent\AgentDetermination;

define("IL_COL_LEFT", "left");
define("IL_COL_RIGHT", "right");
define("IL_COL_CENTER", "center");

define("IL_SCREEN_SIDE", "");
define("IL_SCREEN_CENTER", "center");
define("IL_SCREEN_FULL", "full");

/**
 * Column user interface class. This class is used on the personal desktop,
 * the info screen class and witin container classes.
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_IsCalledBy ilColumnGUI: ilCalendarGUI
 * @ilCtrl_Calls ilColumnGUI:
 */
class ilColumnGUI
{
    protected string $coltype;
    protected ilDashboardSidePanelSettingsRepository $dash_side_panel_settings;
    protected \ILIAS\Block\StandardGUIRequest $request;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilTemplate $tpl;
    protected AgentDetermination $browser;
    protected ilSetting $settings;

    protected string $side = IL_COL_RIGHT;
    protected string $type;
    protected bool $enableedit = false;
    protected bool $repositorymode = false;
    /** @var array[] */
    protected array $repositoryitems = array();
    /** @var array<string,array[]> */
    protected array $blocks = [];
    // all blocks that are repository objects
    /** @var string[] */
    protected array $rep_block_types = array("feed","poll");
    /** @var array<string,array<string,string>> */
    protected array $block_property = array();
    protected bool $admincommands = false;
    protected ?ilAdvancedSelectionListGUI $action_menu = null;
    
    //
    // This two arrays may be replaced by some
    // xml or other magic in the future...
    //
    
    protected static array $locations = array(
        "ilNewsForContextBlockGUI" => "Services/News/",
        "ilCalendarBlockGUI" => "Services/Calendar/",
        "ilPDCalendarBlockGUI" => "Services/Calendar/",
        "ilPDTasksBlockGUI" => "Services/Tasks/",
        "ilPDMailBlockGUI" => "Services/Mail/",
        "ilPDSelectedItemsBlockGUI" => "Services/Dashboard/ItemsBlock/",
        "ilPDNewsBlockGUI" => "Services/News/",
        'ilPollBlockGUI' => 'Modules/Poll/',
        'ilClassificationBlockGUI' => 'Services/Classification/',
        "ilPDStudyProgrammeSimpleListGUI" => "Modules/StudyProgramme/",
        "ilPDStudyProgrammeExpandableListGUI" => "Modules/StudyProgramme/",
    );
    
    protected static array $block_types = array(
        "ilPDMailBlockGUI" => "pdmail",
        "ilPDTasksBlockGUI" => "pdtasks",
        "ilPDNewsBlockGUI" => "pdnews",
        "ilNewsForContextBlockGUI" => "news",
        "ilCalendarBlockGUI" => "cal",
        "ilPDCalendarBlockGUI" => "pdcal",
        "ilPDSelectedItemsBlockGUI" => "pditems",
        'ilPollBlockGUI' => 'poll',
        'ilClassificationBlockGUI' => 'clsfct',
        "ilPDStudyProgrammeSimpleListGUI" => "prgsimplelist",
        "ilPDStudyProgrammeExpandableListGUI" => "prgexpandablelist",
    );
    
        
    protected array $default_blocks = array(
        "cat" => array(
            "ilNewsForContextBlockGUI" => IL_COL_RIGHT,
            "ilClassificationBlockGUI" => IL_COL_RIGHT
            ),
        "crs" => array(
            "ilNewsForContextBlockGUI" => IL_COL_RIGHT,
            "ilCalendarBlockGUI" => IL_COL_RIGHT,
            "ilClassificationBlockGUI" => IL_COL_RIGHT
            ),
        "grp" => array(
            "ilNewsForContextBlockGUI" => IL_COL_RIGHT,
            "ilCalendarBlockGUI" => IL_COL_RIGHT,
            "ilClassificationBlockGUI" => IL_COL_RIGHT
            ),
        "frm" => array("ilNewsForContextBlockGUI" => IL_COL_RIGHT),
        "root" => array(),
        "info" => array(
            "ilNewsForContextBlockGUI" => IL_COL_RIGHT),
        "pd" => array(
            "ilPDTasksBlockGUI" => IL_COL_RIGHT,
            "ilPDCalendarBlockGUI" => IL_COL_RIGHT,
            "ilPDNewsBlockGUI" => IL_COL_RIGHT,
            "ilPDStudyProgrammeSimpleListGUI" => IL_COL_CENTER,
            "ilPDStudyProgrammeExpandableListGUI" => IL_COL_CENTER,
            "ilPDSelectedItemsBlockGUI" => IL_COL_CENTER,
            "ilPDMailBlockGUI" => IL_COL_RIGHT
            )
        );

    // these are only for pd blocks
    // other blocks are rep objects now
    protected array $custom_blocks = array(
        "cat" => array(),
        "crs" => array(),
        "grp" => array(),
        "frm" => array(),
        "root" => array(),
        "info" => array(),
        "fold" => array(),
        "pd" => array()
    );

    // check global activation for these block types
    // @todo: add calendar
    protected array $check_global_activation =
        array("news" => true,
            "cal" => true,
            "pdcal" => true,
            "pdnews" => true,
            "pdtag" => true,
            "pdmail" => true,
            "pdtasks" => true,
            "tagcld" => true,
            "clsfct" => true);
            
    protected array $check_nr_limit =
        array("pdfeed" => true);

    public function __construct(
        string $a_col_type = "",
        string $a_side = "",
        bool $use_std_context = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->browser = $DIC->http()->agent();
        $this->settings = $DIC->settings();
        $this->setColType($a_col_type);
        $this->setSide($a_side);

        $block_service = new ILIAS\Block\Service($DIC);
        $this->request = $block_service->internal()
           ->gui()
           ->standardRequest();

        $this->dash_side_panel_settings = new ilDashboardSidePanelSettingsRepository();
    }

    /**
     * Adds location information of the custom block gui
     */
    public static function addCustomBlockLocation(
        string $className,
        string $path
    ) : void {
        self::$locations[$className] = $path;
    }

    /**
     * Adds the block type of the custom block gui
     * @param string $className The name of the custom block gui class
     * @param string $identifier The identifier (block type) of the custom block gui
     */
    public static function addCustomBlockType(
        string $className,
        string $identifier
    ) : void {
        self::$block_types[$className] = $identifier;
    }

    /**
     * Get Column Side of Current Command
     */
    public static function getCmdSide() : ?string
    {
        global $DIC;

        $block_service = new ILIAS\Block\Service($DIC);
        $request = $block_service->internal()
            ->gui()
            ->standardRequest();
        return $request->getColSide();
    }

    /**
     * @param	string	$a_coltype	Column Type
     */
    public function setColType(string $a_coltype) : void
    {
        $this->coltype = $a_coltype;
    }

    public function getColType() : string
    {
        return $this->coltype;
    }

    /**
    * @param string $a_side Side IL_COL_LEFT | IL_COL_RIGHT
    */
    public function setSide(string $a_side) : void
    {
        $this->side = $a_side;
    }

    public function getSide() : string
    {
        return $this->side;
    }

    public function setEnableEdit(bool $a_enableedit) : void
    {
        $this->enableedit = $a_enableedit;
    }

    public function getEnableEdit() : bool
    {
        return $this->enableedit;
    }

    public function setRepositoryMode(
        bool $a_repositorymode
    ) : void {
        $this->repositorymode = $a_repositorymode;
    }

    public function getRepositoryMode() : bool
    {
        return $this->repositorymode;
    }

    public function setAdminCommands(bool $a_admincommands) : void
    {
        $this->admincommands = $a_admincommands;
    }

    public function getAdminCommands() : bool
    {
        return $this->admincommands;
    }

    public static function getScreenMode() : string
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();

        $block_service = new ILIAS\Block\Service($DIC);
        $request = $block_service->internal()
                                 ->gui()
                                 ->standardRequest();

        if ($ilCtrl->getCmdClass() == "ilcolumngui") {
            switch ($ilCtrl->getCmd()) {
                case "addBlock":
                    return IL_SCREEN_CENTER;
            }
        }

        $cur_block_type = $request->getBlockType();

        if ($class = array_search($cur_block_type, self::$block_types)) {
            return call_user_func(array($class, 'getScreenMode'));
        }

        return IL_SCREEN_SIDE;
    }
    
    /**
     * This function is supposed to be used for block type specific
     * properties, that should be passed to ilBlockGUI->setProperty
     */
    public function setBlockProperty(
        string $a_block_type,
        string $a_property,
        string $a_value
    ) : void {
        $this->block_property[$a_block_type][$a_property] = $a_value;
    }
    
    public function getBlockProperties(
        string $a_block_type
    ) : array {
        return $this->block_property[$a_block_type];
    }

    public function setAllBlockProperties(
        array $a_block_properties
    ) : void {
        $this->block_property = $a_block_properties;
    }

    public function setRepositoryItems(
        array $a_repositoryitems
    ) : void {
        $this->repositoryitems = $a_repositoryitems;
    }

    public function getRepositoryItems() : array
    {
        return $this->repositoryitems;
    }

    public function executeCommand() : string
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setParameter($this, "col_side", $this->getSide());

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");

        $cur_block_type = $this->request->getBlockType();

        if ($next_class != "") {
            // forward to block
            if ($gui_class = array_search($cur_block_type, self::$block_types)) {
                $ilCtrl->setParameter($this, "block_type", $cur_block_type);
                $block_gui = new $gui_class();
                $block_gui->setProperties($this->block_property[$cur_block_type] ?? []);
                $block_gui->setRepositoryMode($this->getRepositoryMode());
                $block_gui->setEnableEdit($this->getEnableEdit());
                $block_gui->setAdminCommands($this->getAdminCommands());

                if (in_array($gui_class, $this->custom_blocks[$this->getColType()]) ||
                    in_array($cur_block_type, $this->rep_block_types)) {
                    $block_class = substr($gui_class, 0, strlen($gui_class) - 3);
                    $app_block = new $block_class($this->request->getBlockId());
                    $block_gui->setBlock($app_block);
                }
                $html = $ilCtrl->forwardCommand($block_gui);
                $ilCtrl->setParameter($this, "block_type", "");
                
                return $html;
            }
        } else {
            return (string) $this->$cmd();
        }
        return "";
    }

    public function getHTML() : string
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setParameter($this, "col_side", $this->getSide());
        
        $this->tpl = new ilTemplate("tpl.column.html", true, true, "Services/Block");
        $this->determineBlocks();
        $this->showBlocks();
        return $this->tpl->get();
    }
    
    public function showBlocks() : void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $i = 1;
        $sum_moveable = count($this->blocks[$this->getSide()]);

        foreach ($this->blocks[$this->getSide()] as $block) {
            $gui_class = $block["class"] ?? null;
            if (!is_string($gui_class)) {
                continue;
            }
            $block_class = substr($gui_class, 0, strlen($gui_class) - 3);

            // get block gui class
            $block_gui = new $gui_class();
            if (isset($this->block_property[$block["type"]])) {
                $block_gui->setProperties($this->block_property[$block["type"]]);
            }
            $block_gui->setRepositoryMode($this->getRepositoryMode());
            $block_gui->setEnableEdit($this->getEnableEdit());
            $block_gui->setAdminCommands($this->getAdminCommands());

            // get block for custom blocks
            if ($block["custom"]) {
                $path = "./" . self::$locations[$gui_class] . "classes/" .
                    "class." . $block_class . ".php";
                if (file_exists($path)) {
                    $app_block = new $block_class((int) $block["id"]);
                } else {
                    // we only need generic block
                    $app_block = new ilCustomBlock((int) $block["id"]);
                }
                $block_gui->setBlock($app_block);
                if (isset($block["ref_id"])) {
                    $block_gui->setRefId((int) $block["ref_id"]);
                }
            }

            $ilCtrl->setParameter($this, "block_type", $block_gui->getBlockType());
            $this->tpl->setCurrentBlock("col_block");

            $html = $ilCtrl->getHTML($block_gui);

            // don't render a block if it's empty
            if ($html != "") {
                $this->tpl->setVariable("BLOCK", $html);
                $this->tpl->parseCurrentBlock();
                $ilCtrl->setParameter($this, "block_type", "");
            }
        }
    }
    

    /**
     * Update Block (asynchronous)
     */
    public function updateBlock() : void
    {
        $ilCtrl = $this->ctrl;
        
        $this->determineBlocks();
        $i = 1;
        $sum_moveable = count($this->blocks[$this->getSide()]);

        foreach ($this->blocks[$this->getSide()] as $block) {

            // set block id to context obj id,
            // if block is not a custom block and context is not personal desktop
            if (!$block["custom"] && $ilCtrl->getContextObjType() != "" && $ilCtrl->getContextObjType() != "user") {
                $block["id"] = $ilCtrl->getContextObjId();
            }
                
            if ($this->request->getBlockId() == "block_" . $block["type"] . "_" . $block["id"]) {
                $gui_class = $block["class"];
                $block_class = substr($block["class"], 0, strlen($block["class"]) - 3);
                
                $block_gui = new $gui_class();
                $block_gui->setProperties($this->block_property[$block["type"]]);
                $block_gui->setRepositoryMode($this->getRepositoryMode());
                $block_gui->setEnableEdit($this->getEnableEdit());
                $block_gui->setAdminCommands($this->getAdminCommands());
                
                // get block for custom blocks
                if ($block["custom"]) {
                    $app_block = new $block_class($block["id"]);
                    $block_gui->setBlock($app_block);
                    $block_gui->setRefId($block["ref_id"]);
                }

                $ilCtrl->setParameter($this, "block_type", $block["type"]);
                echo $ilCtrl->getHTML($block_gui);
                exit;
            }
            
            // count (moveable) blocks
            if ($block["type"] != "pdfeedb"
                && $block["type"] != "news") {
                $i++;
            } else {
                $sum_moveable--;
            }
        }
        echo "Error: ilColumnGUI::updateBlock: Block '" .
            $this->request->getBlockId() . "' unknown.";
        exit;
    }

    /**
     * Activate hidden block
     */
    public function activateBlock() : void
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;

        if ($this->request->getBlock() != "") {
            $block = explode("_", $this->request->getBlock());
            ilBlockSetting::_writeDetailLevel($block[0], 2, $ilUser->getId(), $block[1]);
        }

        $ilCtrl->returnToParent($this);
    }

    /**
     * Add a block
     */
    public function addBlock() : string
    {
        $ilCtrl = $this->ctrl;
        
        $class = array_search($this->request->getBlockType(), self::$block_types);

        $ilCtrl->setCmdClass($class);
        $ilCtrl->setCmd("create");
        $block_gui = new $class();
        $block_gui->setProperties($this->block_property[$this->request->getBlockType()]);
        $block_gui->setRepositoryMode($this->getRepositoryMode());
        $block_gui->setEnableEdit($this->getEnableEdit());
        $block_gui->setAdminCommands($this->getAdminCommands());
        
        $ilCtrl->setParameter($this, "block_type", $this->request->getBlockType());
        $html = $ilCtrl->forwardCommand($block_gui);
        $ilCtrl->setParameter($this, "block_type", "");
        return $html;
    }
    
    /**
     * Determine which blocks to show.
     */
    public function determineBlocks() : void
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;

        $this->blocks[IL_COL_LEFT] = array();
        $this->blocks[IL_COL_RIGHT] = array();
        $this->blocks[IL_COL_CENTER] = array();
        
        $user_id = ($this->getColType() === "pd")
            ? $ilUser->getId()
            : 0;

        $def_nr = 1000;
        if (isset($this->default_blocks[$this->getColType()])) {
            foreach ($this->default_blocks[$this->getColType()] as $class => $def_side) {
                $type = self::$block_types[$class];
                if ($this->isGloballyActivated($type)) {
                    $nr = $def_nr++;
                    
                    // extra handling for system messages, feedback block and news
                    if ($type == "news") {		// always show news first
                        $nr = -15;
                    }
                    if ($type == "cal") {
                        $nr = -8;
                    }
                    if ($type == "pdfeedb") {		// always show feedback request second
                        $nr = -10;
                    }
                    if ($type == "clsfct") {		// mkunkel wants to have this on top
                        $nr = -16;
                    }
                    $side = ilBlockSetting::_lookupSide($type, $user_id);
                    if (is_null($side)) {
                        $side = $def_side;
                    }
                    if ($side == IL_COL_LEFT) {
                        $side = IL_COL_RIGHT;
                    }
                    
                    $this->blocks[$side][] = array(
                        "nr" => $nr,
                        "class" => $class,
                        "type" => $type,
                        "id" => 0,
                        "custom" => false);
                }
            }
        }
        
        if (!$this->getRepositoryMode()) {
            $custom_block = new ilCustomBlock();
            $custom_block->setContextObjId($ilCtrl->getContextObjId());
            $custom_block->setContextObjType($ilCtrl->getContextObjType());
            $c_blocks = $custom_block->queryBlocksForContext();
    
            foreach ($c_blocks as $c_block) {
                $type = $c_block["type"];
                
                if ($this->isGloballyActivated($type)) {
                    $class = array_search($type, self::$block_types);
                    $nr = $def_nr++;
                    $side = ilBlockSetting::_lookupSide($type, $user_id, $c_block["id"]);
                    if (is_null($side)) {
                        $side = IL_COL_RIGHT;
                    }
    
                    $this->blocks[$side][] = array(
                        "nr" => $nr,
                        "class" => $class,
                        "type" => $type,
                        "id" => $c_block["id"],
                        "custom" => true);
                }
            }
        } else {	// get all subitems
            $rep_items = $this->getRepositoryItems();
            foreach ($this->rep_block_types as $block_type) {
                if ($this->isGloballyActivated($block_type)) {
                    if (!isset($rep_items[$block_type]) || !is_array($rep_items[$block_type])) {
                        continue;
                    }
                    foreach ($rep_items[$block_type] as $item) {
                        $costum_block = new ilCustomBlock();
                        $costum_block->setContextObjId((int) $item["obj_id"]);
                        $costum_block->setContextObjType($block_type);
                        $c_blocks = $costum_block->queryBlocksForContext();
                        $c_block = $c_blocks[0];
                        
                        $type = $block_type;
                        $class = array_search($type, self::$block_types);
                        $nr = $def_nr++;
                        $side = ilBlockSetting::_lookupSide($type, $user_id, (int) $c_block["id"]);
                        if ($side == false) {
                            $side = IL_COL_RIGHT;
                        }
            
                        $this->blocks[$side][] = array(
                            "nr" => $nr,
                            "class" => $class,
                            "type" => $type,
                            "id" => $c_block["id"],
                            "custom" => true,
                            "ref_id" => $item["ref_id"]);
                    }
                }
            }
                                        
            // repository object custom blocks
            $custom_block = new ilCustomBlock();
            $custom_block->setContextObjId($ilCtrl->getContextObjId());
            $custom_block->setContextObjType($ilCtrl->getContextObjType());
            $c_blocks = $custom_block->queryBlocksForContext(false); // get all sub-object types
            foreach ($c_blocks as $c_block) {
                $type = $c_block["type"];
                $class = array_search($type, self::$block_types);
                
                if ($class) {
                    $nr = $def_nr++;
                    $side = IL_COL_RIGHT;
                        
                    $this->blocks[$side][] = array(
                        "nr" => $nr,
                        "class" => $class,
                        "type" => $type,
                        "id" => $c_block["id"],
                        "custom" => true);
                }
            }
        }
        
        
        $this->blocks[IL_COL_LEFT] =
            ilArrayUtil::sortArray($this->blocks[IL_COL_LEFT], "nr", "asc", true);
        $this->blocks[IL_COL_RIGHT] =
            ilArrayUtil::sortArray($this->blocks[IL_COL_RIGHT], "nr", "asc", true);
        $this->blocks[IL_COL_CENTER] =
            ilArrayUtil::sortArray($this->blocks[IL_COL_CENTER], "nr", "asc", true);
    }

    /**
     * Check whether a block type is globally activated
     */
    protected function isGloballyActivated(
        string $a_type
    ) : bool {
        $ilSetting = $this->settings;
        $ilCtrl = $this->ctrl;

        if ($a_type == 'pdfeed') {
            return false;
        }

        if (isset($this->check_global_activation[$a_type]) && $this->check_global_activation[$a_type]) {
            if ($a_type == 'pdnews') {
                return ($this->dash_side_panel_settings->isEnabled($this->dash_side_panel_settings::NEWS) &&
                    $ilSetting->get('block_activated_news'));
            } elseif ($a_type == 'pdmail') {
                return $this->dash_side_panel_settings->isEnabled($this->dash_side_panel_settings::MAIL);
            } elseif ($a_type == 'pdtasks') {
                return $this->dash_side_panel_settings->isEnabled($this->dash_side_panel_settings::TASKS);
            } elseif ($a_type == 'news') {
                return
                    $ilSetting->get('block_activated_news') &&

                    (!in_array($ilCtrl->getContextObjType(), ["grp", "crs"]) ||
                        ilContainer::_lookupContainerSetting(
                            $GLOBALS['ilCtrl']->getContextObjId(),
                            ilObjectServiceSettingsGUI::USE_NEWS,
                            "1"
                        )) &&
                    ilContainer::_lookupContainerSetting(
                        $GLOBALS['ilCtrl']->getContextObjId(),
                        'cont_show_news',
                        "1"
                    );
            } elseif ($ilSetting->get("block_activated_" . $a_type)) {
                return true;
            } elseif ($a_type == 'cal') {
                return ilCalendarSettings::lookupCalendarContentPresentationEnabled($ilCtrl->getContextObjId());
            } elseif ($a_type == 'pdcal') {
                if (!$this->dash_side_panel_settings->isEnabled($this->dash_side_panel_settings::CALENDAR)) {
                    return false;
                }
                return ilCalendarSettings::_getInstance()->isEnabled();
            } elseif ($a_type == "tagcld") {
                $tags_active = new ilSetting("tags");
                return (bool) $tags_active->get("enable", "0");
            } elseif ($a_type == "clsfct") {
                if ($ilCtrl->getContextObjType() == "cat") {	// taxonomy presentation in classification block
                    return true;
                }
                $tags_active = new ilSetting("tags");		// tags presentation in classification block
                return (bool) $tags_active->get("enable", "0");
            }
            return false;
        }
        return true;
    }

    /**
     * Check whether limit is not exceeded
     */
    protected function exceededLimit(
        string $a_type
    ) : bool {
        $ilSetting = $this->settings;
        $ilCtrl = $this->ctrl;

        if ($this->check_nr_limit[$a_type]) {
            if (!$this->getRepositoryMode()) {
                $costum_block = new ilCustomBlock();
                $costum_block->setContextObjId($ilCtrl->getContextObjId());
                $costum_block->setContextObjType($ilCtrl->getContextObjType());
                $costum_block->setType($a_type);
                $res = $costum_block->queryCntBlockForContext();
                $cnt = (int) $res[0]["cnt"];
            } else {
                return false;		// not implemented for repository yet
            }
            
            
            if ($ilSetting->get("block_limit_" . $a_type) > $cnt) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    public function setActionMenu(
        ilAdvancedSelectionListGUI $action_menu
    ) : void {
        $this->action_menu = $action_menu;
    }

    public function getActionMenu() : ilAdvancedSelectionListGUI
    {
        return $this->action_menu;
    }
}
