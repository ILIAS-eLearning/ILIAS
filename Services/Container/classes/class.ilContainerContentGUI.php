<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Parent class of all container content GUIs.
*
* These classes are responsible for displaying the content, i.e. the
* side column and main column and its subitems in container objects.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
abstract class ilContainerContentGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    const DETAILS_DEACTIVATED = 0;
    const DETAILS_TITLE = 1;
    const DETAILS_ALL = 2;

    const VIEW_MODE_LIST = 0;
    const VIEW_MODE_TILE = 1;

    protected $details_level = self::DETAILS_DEACTIVATED;

    /**
     * @var ilContainerRenderer
     */
    protected $renderer;
    
    public $container_gui;
    public $container_obj;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * @var int
     */
    protected $view_mode;

    /**
    * Constructor
    *
    */
    public function __construct(&$container_gui_obj)
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->db = $DIC->database();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->settings = $DIC->settings();
        $this->obj_definition = $DIC["objDefinition"];
        $tpl = $DIC["tpl"];

        $this->container_gui = $container_gui_obj;
        $this->container_obj = $this->container_gui->object;

        $tpl->addJavaScript("./Services/Container/js/Container.js");

        $this->log = ilLoggerFactory::getLogger('cont');

        $this->view_mode = (ilContainer::_lookupContainerSetting($this->container_obj->getId(), "list_presentation") == "tile" &&
            !$this->container_gui->isActiveAdministrationPanel() &&
            !$this->container_gui->isActiveOrdering())
            ? self::VIEW_MODE_TILE
            : self::VIEW_MODE_LIST;
    }
    
    /**
     * Get view mode
     */
    protected function getViewMode()
    {
        return $this->view_mode;
    }
    
    
    /**
     * get details level
     *
     * @access protected
     * @param
     * @return
     */
    protected function getDetailsLevel($a_item_id)
    {
        return $this->details_level;
    }

    /**
    * Get container object.
    *
    * @return 	object		container object instance
    */
    public function getContainerObject()
    {
        return $this->container_obj;
    }
    
    /**
    * Get container GUI object
    *
    * @return 	object		container GUI instance
    */
    public function getContainerGUI()
    {
        return $this->container_gui;
    }

    /**
    * Sets view output into column layout
    *
    * This method sets the output of the right and main column
    * in the global standard template.
    */
    public function setOutput()
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;

        // note: we do not want to get the center html in case of
        // asynchronous calls to blocks in the right column (e.g. news)
        // see #13012
        if ($ilCtrl->getNextClass() == "ilcolumngui" &&
            $ilCtrl->isAsynch()) {
            $tpl->setRightContent($this->getRightColumnHTML());
        }
        
        // BEGIN ChangeEvent: record read event.
        require_once('Services/Tracking/classes/class.ilChangeEvent.php');
        $ilUser = $this->user;

        $obj_id = ilObject::_lookupObjId($this->getContainerObject()->getRefId());
        ilChangeEvent::_recordReadEvent(
            $this->getContainerObject()->getType(),
            $this->getContainerObject()->getRefId(),
            $obj_id,
            $ilUser->getId()
        );
        // END ChangeEvent: record read event.
        

        $tpl->setContent($this->getCenterColumnHTML());

        // see above, all other cases (this was the old position of setRightContent,
        // maybe the position above is ok and all ifs can be removed)
        if ($ilCtrl->getNextClass() != "ilcolumngui" ||
            !$ilCtrl->isAsynch()) {
            $tpl->setRightContent($this->getRightColumnHTML());
        }
    }

    /**
    * Get HTML for right column
    */
    protected function getRightColumnHTML()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $ilPluginAdmin = $this->plugin_admin;

        $ilCtrl->saveParameterByClass("ilcolumngui", "col_return");

        $obj_id = ilObject::_lookupObjId($this->getContainerObject()->getRefId());
        $obj_type = ilObject::_lookupType($obj_id);

        include_once("Services/Block/classes/class.ilColumnGUI.php");
        $column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);

        if ($column_gui->getScreenMode() == IL_SCREEN_FULL) {
            return "";
        }

        $this->getContainerGUI()->setColumnSettings($column_gui);
        
        if ($ilCtrl->getNextClass() == "ilcolumngui" &&
            $column_gui->getCmdSide() == IL_COL_RIGHT &&
            $column_gui->getScreenMode() == IL_SCREEN_SIDE) {
            $html = $ilCtrl->forwardCommand($column_gui);
        } else {
            if (!$ilCtrl->isAsynch()) {
                $html = "";
                
                // user interface plugin slot + default rendering
                include_once("./Services/UIComponent/classes/class.ilUIHookProcessor.php");
                $uip = new ilUIHookProcessor(
                    "Services/Container",
                    "right_column",
                    array("container_content_gui" => $this)
                );
                if (!$uip->replaced()) {
                    $html = $ilCtrl->getHTML($column_gui);
                }
                $html = $uip->getHTML($html);
            }
        }
        
        return $html;
    }

    /**
    * Get HTML for center column
    */
    protected function getCenterColumnHTML()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilDB = $this->db;
        
        $ilCtrl->saveParameterByClass("ilcolumngui", "col_return");
        
        $tpl->addOnLoadCode("il.Object.setRedrawListItemUrl('" .
            $ilCtrl->getLinkTarget($this->container_gui, "redrawListItem", "", true) . "');");

        $tpl->addOnLoadCode("il.Object.setRatingUrl('" .
            $ilCtrl->getLinkTargetByClass(
                array(get_class($this->container_gui), "ilcommonactiondispatchergui", "ilratinggui"),
                "saveRating",
                "",
                true,
                false
            ) . "');");
        
        switch ($ilCtrl->getNextClass()) {
            case "ilcolumngui":
                $this->container_gui->setSideColumnReturn();
                $html = $this->__forwardToColumnGUI();
                break;
                
            default:
                $ilDB->useSlave(true);
                $html = $this->getMainContent();
                $ilDB->useSlave(false);
                break;
        }
        
        return $html;
    }

    /**
    * Get content HTML for main column, this one must be
    * overwritten in derived classes.
    */
    abstract public function getMainContent();
    
    /**
     * Init container renderer
     */
    protected function initRenderer()
    {
        include_once('./Services/Container/classes/class.ilContainerSorting.php');
        $sorting = ilContainerSorting::_getInstance($this->getContainerObject()->getId());
        
        include_once "Services/Container/classes/class.ilContainerRenderer.php";
        $this->renderer = new ilContainerRenderer(
            ($this->getContainerGUI()->isActiveAdministrationPanel() && !$_SESSION["clipboard"]),
            $this->getContainerGUI()->isMultiDownloadEnabled(),
            $this->getContainerGUI()->isActiveOrdering() && (get_class($this) != "ilContainerObjectiveGUI") // no block sorting in objective view
            ,
            $sorting->getBlockPositions(),
            $this->container_gui,
            $this->getViewMode()
        );
    }
    
    /**
    * Get columngui output
    */
    final private function __forwardToColumnGUI()
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        
        include_once("Services/Block/classes/class.ilColumnGUI.php");

        // this gets us the subitems we need in setColumnSettings()
        // todo: this should be done in ilCourseGUI->getSubItems
        
        $obj_id = ilObject::_lookupObjId($this->getContainerObject()->getRefId());
        $obj_type = ilObject::_lookupType($obj_id);

        if (!$ilCtrl->isAsynch()) {
            //if ($column_gui->getScreenMode() != IL_SCREEN_SIDE)
            if (ilColumnGUI::getScreenMode() != IL_SCREEN_SIDE) {
                // right column wants center
                if (ilColumnGUI::getCmdSide() == IL_COL_RIGHT) {
                    $column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);
                    $this->getContainerGUI()->setColumnSettings($column_gui);
                    $html = $ilCtrl->forwardCommand($column_gui);
                }
                // left column wants center
                if (ilColumnGUI::getCmdSide() == IL_COL_LEFT) {
                    $column_gui = new ilColumnGUI($obj_type, IL_COL_LEFT);
                    $this->getContainerGUI()->setColumnSettings($column_gui);
                    $html = $ilCtrl->forwardCommand($column_gui);
                }
            } else {
                $html = $this->getMainContent();
            }
        }

        return $html;
    }

    /**
    * cleaer administration commands determination
    */
    protected function clearAdminCommandsDetermination()
    {
        $this->adminCommands = false;
    }
    
    /**
    * determin admin commands
    */
    protected function determineAdminCommands($a_ref_id, $a_admin_com_included_in_list = false)
    {
        $rbacsystem = $this->rbacsystem;
        
        //echo "-".$a_admin_com_included_in_list."-";
        
        if (!$this->adminCommands) {
            if (!$this->getContainerGUI()->isActiveAdministrationPanel()) {
                if ($rbacsystem->checkAccess("delete", $a_ref_id)) {
                    $this->adminCommands = true;
                }
            } else {
                $this->adminCommands = $a_admin_com_included_in_list;
            }
        }
    }

    /**
    * Get ListGUI object for item
    */
    protected function getItemGUI($item_data, $a_show_path = false)
    {
        include_once 'Services/Object/classes/class.ilObjectListGUIFactory.php';

        // get item list gui object
        if (!is_object($this->list_gui[$item_data["type"]])) {
            $item_list_gui = &ilObjectListGUIFactory::_getListGUIByType($item_data["type"]);
            $item_list_gui->setContainerObject($this->getContainerGUI());
            $this->list_gui[$item_data["type"]] = &$item_list_gui;
        } else {
            $item_list_gui = &$this->list_gui[$item_data["type"]];
        }

        // unique js-ids
        $item_list_gui->setParentRefId($item_data["parent"]);
        
        $item_list_gui->setDefaultCommandParameters(array());
        $item_list_gui->disableTitleLink(false);
        $item_list_gui->resetConditionTarget();

        // show administration command buttons (or not)
        if (!$this->getContainerGUI()->isActiveAdministrationPanel()) {
            //			$item_list_gui->enableDelete(false);
//			$item_list_gui->enableLink(false);
//			$item_list_gui->enableCut(false);
        }
        
        // activate common social commands
        $item_list_gui->enableComments(true);
        $item_list_gui->enableNotes(true);
        $item_list_gui->enableTags(true);
        $item_list_gui->enableRating(true);
        
        // reset
        $item_list_gui->forceVisibleOnly(false);

        // container specific modifications
        $this->getContainerGUI()->modifyItemGUI($item_list_gui, $item_data, $a_show_path);

        return $item_list_gui;
    }

    /**
    * Determine all blocks that are embedded in the container page
    */
    public function determinePageEmbeddedBlocks($a_container_page_html)
    {
        $type_grps = $this->getGroupedObjTypes();
        
        // iterate all types
        foreach ($type_grps as $type => $v) {
            // set template (overall or type specific)
            if (is_int(strpos($a_container_page_html, "[list-" . $type . "]"))) {
                $this->addEmbeddedBlock("type", $type);
            }
        }
        
        // determine item groups
        while (preg_match('~\[(item-group-([0-9]*))\]~i', $a_container_page_html, $found)) {
            $this->addEmbeddedBlock("itgr", (int) $found[2]);
            
            $a_container_page_html = preg_replace('~\[' . $found[1] . '\]~i', $html, $a_container_page_html);
        }
    }
    
    /**
    * Add embedded block
    *
    * @param
    */
    public function addEmbeddedBlock($block_type, $block_parameter)
    {
        $this->embedded_block[$block_type][] = $block_parameter;
    }
    
    /**
    * Get page embedded blocks
    */
    public function getEmbeddedBlocks()
    {
        return $this->embedded_block;
    }
    
    /**
    * Render Page Embedded Blocks
    */
    public function renderPageEmbeddedBlocks()
    {
        $lng = $this->lng;
                
        // item groups
        if (is_array($this->embedded_block["itgr"])) {
            $item_groups = array();
            if (is_array($this->items["itgr"])) {
                foreach ($this->items["itgr"] as $ig) {
                    $item_groups[$ig["ref_id"]] = $ig;
                }
            }

            foreach ($this->embedded_block["itgr"] as $ref_id) {
                if (isset($item_groups[$ref_id])) {
                    $this->renderItemGroup($item_groups[$ref_id]);
                }
            }
        }
        
        // type specific blocks
        if (is_array($this->embedded_block["type"])) {
            foreach ($this->embedded_block["type"] as $k => $type) {
                if (is_array($this->items[$type]) &&
                    $this->renderer->addTypeBlock($type)) {
                    // :TODO: obsolete?
                    if ($type == 'sess') {
                        $this->items['sess'] = ilUtil::sortArray($this->items['sess'], 'start', 'ASC', true, true);
                    }
                    
                    $position = 1;

                    foreach ($this->items[$type] as $k => $item_data) {
                        if (!$this->renderer->hasItem($item_data["child"])) {
                            $html = $this->renderItem($item_data, $position++);
                            if ($html != "") {
                                $this->renderer->addItemToBlock($type, $item_data["type"], $item_data["child"], $html);
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Render an item
     * @param $a_item_data
     * @param int $a_position
     * @param bool $a_force_icon
     * @param string $a_pos_prefix
     * @return string
     */
    public function renderItem($a_item_data, $a_position = 0, $a_force_icon = false, $a_pos_prefix = "")
    {
        $ilSetting = $this->settings;
        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;
        
        // Pass type, obj_id and tree to checkAccess method to improve performance
        if (!$ilAccess->checkAccess('visible', '', $a_item_data['ref_id'], $a_item_data['type'], $a_item_data['obj_id'], $a_item_data['tree'])) {
            return '';
        }

        if ($this->getViewMode() == self::VIEW_MODE_TILE) {
            return $this->renderCard($a_item_data, $a_position, $a_force_icon, $a_pos_prefix);
        }

        $item_list_gui = $this->getItemGUI($a_item_data);
        if ($ilSetting->get("icon_position_in_lists") == "item_rows" ||
            $a_item_data["type"] == "sess" || $a_force_icon) {
            $item_list_gui->enableIcon(true);
        }
        
        if ($this->getContainerGUI()->isActiveAdministrationPanel() && !$_SESSION["clipboard"]) {
            $item_list_gui->enableCheckbox(true);
        } elseif ($this->getContainerGUI()->isMultiDownloadEnabled()) {
            // display multi download checkboxes
            $item_list_gui->enableDownloadCheckbox($a_item_data["ref_id"], true);
        }
        
        if ($this->getContainerGUI()->isActiveItemOrdering() && ($a_item_data['type'] != 'sess' || get_class($this) != 'ilContainerSessionsContentGUI')) {
            $item_list_gui->setPositionInputField(
                $a_pos_prefix . "[" . $a_item_data["ref_id"] . "]",
                sprintf('%d', (int) $a_position * 10)
            );
        }
        
        if ($a_item_data['type'] == 'sess' and get_class($this) != 'ilContainerObjectiveGUI') {
            switch ($this->getDetailsLevel($a_item_data['obj_id'])) {
                case self::DETAILS_TITLE:
                    $item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_MINIMAL);
                    $item_list_gui->enableExpand(true);
                    $item_list_gui->setExpanded(false);
                    $item_list_gui->enableDescription(false);
                    $item_list_gui->enableProperties(true);
                    break;
                    
                case self::DETAILS_ALL:
                    $item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_ALL);
                    $item_list_gui->enableExpand(true);
                    $item_list_gui->setExpanded(true);
                    $item_list_gui->enableDescription(true);
                    $item_list_gui->enableProperties(true);
                    break;
                                        
                default:
                    $item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_ALL);
                    $item_list_gui->enableExpand(true);
                    $item_list_gui->enableDescription(true);
                    $item_list_gui->enableProperties(true);
                    break;
            }
        }
        
        if (method_exists($this, "addItemDetails")) {
            $this->addItemDetails($item_list_gui, $a_item_data);
        }

        // show subitems
        if ($a_item_data['type'] == 'sess' and (
                                            $this->getDetailsLevel($a_item_data['obj_id']) != self::DETAILS_TITLE or
                                            $this->getContainerGUI()->isActiveAdministrationPanel() or
                                            $this->getContainerGUI()->isActiveItemOrdering()
                                            )
        ) {
            $pos = 1;
                        
            include_once('./Services/Container/classes/class.ilContainerSorting.php');
            include_once('./Services/Object/classes/class.ilObjectActivation.php');
            $items = ilObjectActivation::getItemsByEvent($a_item_data['obj_id']);
            $items = ilContainerSorting::_getInstance($this->getContainerObject()->getId())->sortSubItems('sess', $a_item_data['obj_id'], $items);
            $items = ilContainer::getCompleteDescriptions($items);
            
            $item_readable = $ilAccess->checkAccess('read', '', $a_item_data['ref_id']);
            
            foreach ($items as $item) {
                // TODO: this should be removed and be handled by if(strlen($sub_item_html))
                // 	see mantis: 0003944
                if (!$ilAccess->checkAccess('visible', '', $item['ref_id'])) {
                    continue;
                }
                
                $item_list_gui2 = $this->getItemGUI($item);
                $item_list_gui2->enableIcon(true);
                $item_list_gui2->enableItemDetailLinks(false);
                
                // unique js-ids
                $item_list_gui2->setParentRefId($a_item_data['ref_id']);
                
                // @see mantis 10488
                if (!$item_readable and !$ilAccess->checkAccess('write', '', $item['ref_id'])) {
                    $item_list_gui2->forceVisibleOnly(true);
                }
                
                if ($this->getContainerGUI()->isActiveAdministrationPanel() && !$_SESSION["clipboard"]) {
                    $item_list_gui2->enableCheckbox(true);
                } elseif ($this->getContainerGUI()->isMultiDownloadEnabled()) {
                    // display multi download checkbox
                    $item_list_gui2->enableDownloadCheckbox($item['ref_id'], true);
                }

                if ($this->getContainerGUI()->isActiveItemOrdering()) {
                    $item_list_gui2->setPositionInputField(
                        "[sess][" . $a_item_data['obj_id'] . "][" . $item["ref_id"] . "]",
                        sprintf('%d', (int) $pos * 10)
                    );
                    $pos++;
                }
                
                // #10611
                ilObjectActivation::addListGUIActivationProperty($item_list_gui2, $item);
                        
                $sub_item_html = $item_list_gui2->getListItemHTML(
                    $item['ref_id'],
                    $item['obj_id'],
                    $item['title'],
                    $item['description']
                );
                                        
                $this->determineAdminCommands($item["ref_id"], $item_list_gui2->adminCommandsIncluded());
                if (strlen($sub_item_html)) {
                    $item_list_gui->addSubItemHTML($sub_item_html);
                }
            }
        }


        if ($ilSetting->get("item_cmd_asynch")) {
            $asynch = true;
            $ilCtrl->setParameter($this->container_gui, "cmdrefid", $a_item_data['ref_id']);
            $asynch_url = $ilCtrl->getLinkTarget(
                $this->container_gui,
                "getAsynchItemList",
                "",
                true,
                false
            );
            $ilCtrl->setParameter($this->container_gui, "cmdrefid", "");

            //#0020343
            $fold_set = new ilSetting('fold');
            if ($a_item_data['type'] == 'fold' && $fold_set->get("bgtask_download") && $fold_set->get("enable_download_folder")) {
                include_once "Services/BackgroundTask/classes/class.ilFolderDownloadBackgroundTaskHandler.php";
                ilFolderDownloadBackgroundTaskHandler::initObjectListAction();
            }
        }
                    
        include_once "Services/Object/classes/class.ilObjectActivation.php";
        ilObjectActivation::addListGUIActivationProperty($item_list_gui, $a_item_data);
        
        $html = $item_list_gui->getListItemHTML(
            $a_item_data['ref_id'],
            $a_item_data['obj_id'],
            $a_item_data['title'],
            $a_item_data['description'],
            $asynch,
            false,
            $asynch_url
        );
        $this->determineAdminCommands(
            $a_item_data["ref_id"],
            $item_list_gui->adminCommandsIncluded()
        );
            
            
        return $html;
    }

    /**
     * Render card
     * @param $a_item_data
     * @param int $a_position
     * @param bool $a_force_icon
     * @param string $a_pos_prefix
     * @return string
     */
    public function renderCard($a_item_data, $a_position = 0, $a_force_icon = false, $a_pos_prefix = "")
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();
        $user = $DIC->user();

        $item_list_gui = $this->getItemGUI($a_item_data);
        $item_list_gui->setAjaxHash(ilCommonActionDispatcherGUI::buildAjaxHash(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            $a_item_data['ref_id'],
            $a_item_data['type'],
            $a_item_data['obj_id']
        ));
        $item_list_gui->initItem(
            $a_item_data['ref_id'],
            $a_item_data['obj_id'],
            $a_item_data['title'],
            $a_item_data['description']
        );

        // actions
        $item_list_gui->insertCommands();
        $actions = [];
        foreach ($item_list_gui->current_selection_list->getItems() as $item) {
            if (!isset($item["onclick"]) || $item["onclick"] == "") {
                $button =
                    $f->button()->shy($item["title"], $item["link"]);
            } else {
                $button =
                    $f->button()->shy($item["title"], "")->withAdditionalOnLoadCode(function ($id) use ($item) {
                        return
                            "$('#$id').click(function(e) { " . $item["onclick"] . "});";
                    });
            }
            $actions[] = $button;
        }


        $def_command = $item_list_gui->getDefaultCommand();
        $dropdown = $f->dropdown()->standard($actions);

        $img = $DIC->object()->commonSettings()->tileImage()->getByObjId($a_item_data['obj_id']);

        if ($img->exists()) {
            $path = $img->getFullPath();
        } else {
            $path = ilUtil::getImagePath("cont_tile/cont_tile_default_" . $a_item_data['type'] . ".svg");
            if (!is_file($path)) {
                $path = ilUtil::getImagePath("cont_tile/cont_tile_default.svg");
            }
        }

        $sections = [];
        $title = $a_item_data["title"];

        // workaround for scorm
        $modified_link =
            $item_list_gui->modifySAHSlaunch($def_command["link"], $def_command["frame"]);

        // workaround for #26205
        // we should get rid of _top links completely and gifure our how
        // to manage scorm links better
        if ($def_command["frame"] == "_top") {
            $def_command["frame"] = "";
        }

        $image = $f->image()->responsive($path, "");
        if ($def_command["link"] != "") {	// #24256
            if ($def_command["frame"] != "" && ($modified_link == $def_command["link"])) {
                $image = $image->withAdditionalOnLoadCode(function ($id) use ($def_command) {
                    return
                        "$('#$id').click(function(e) { window.open('" . str_replace("&amp;", "&", $def_command["link"]) . "', '" . $def_command["frame"] . "');});";
                });

                $button =
                    $f->button()->shy($title, "")->withAdditionalOnLoadCode(function ($id) use ($def_command) {
                        return
                            "$('#$id').click(function(e) { window.open('" . str_replace("&amp;", "&", $def_command["link"]) . "', '" . $def_command["frame"] . "');});";
                    });
                $title = $r->render($button);
            } else {
                $image = $image->withAction($modified_link);
            }
        }

        // description, @todo: move to new ks element
        if ($a_item_data["description"] != "") {
            $sections[] = $f->legacy("<div class='il_info il-multi-line-cap-3'>" . $a_item_data["description"] . "</div>");
        }

        if ($a_item_data["type"] == "sess") {
            $app_info = ilSessionAppointment::_lookupAppointment($a_item_data['obj_id']);
            if ($title != "") {
                $title = ": " . $title;
            }
            $title = ilSessionAppointment::_appointmentToString($app_info['start'], $app_info['end'], $app_info['fullday']) .
                $title;
        }

        $icon = $f->icon()->standard($a_item_data["type"], $this->lng->txt("obj_" . $a_item_data["type"]))
            ->withIsOutlined(true);
        $card = $f->card()->repositoryObject(
            $title . "<span data-list-item-id='" . $item_list_gui->getUniqueItemId(true) . "'></span>",
            $image
        )->withObjectIcon(
            $icon
        )->withActions(
            $dropdown
        );

        if ($def_command["link"] != "" && ($def_command["frame"] == "" || $modified_link != $def_command["link"])) {	// #24256
            $card = $card->withTitleAction($modified_link);
        }

        // properties
        $l = [];
        foreach ($item_list_gui->determineProperties() as $p) {
            if ($p["property"] != $this->lng->txt("learning_progress")) {
                $l[(string) $p["property"]] = (string) $p["value"];
            }
        }

        if (count($l) > 0) {
            $prop_list = $f->listing()->descriptive($l);
            $sections[] = $prop_list;
        }
        if (count($sections) > 0) {
            $card = $card->withSections($sections);
        }
        // learning progress
        include_once "Services/Tracking/classes/class.ilLPStatus.php";
        $lp = ilLPStatus::getListGUIStatus($a_item_data["obj_id"], false);
        if ($lp) {
            $percentage = (int) ilLPStatus::_lookupPercentage($a_item_data["obj_id"], $user->getId());
            if ($lp["status"] == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                $percentage = 100;
            }
            //var_dump(ilLPStatus::_lookupPercentage($a_item_data["obj_id"], $user->getId())); exit;
            $progressmeter = $f->chart()->progressMeter()->mini(100, $percentage);
            $card = $card->withProgress($progressmeter);
        }

        return $card;
    }

    /**
    * Insert blocks into container page
    */
    public function insertPageEmbeddedBlocks($a_output_html)
    {
        $this->determinePageEmbeddedBlocks($a_output_html);
        $this->renderPageEmbeddedBlocks($this->items);
        
        // iterate all types
        foreach ($this->getGroupedObjTypes() as $type => $v) {
            // set template (overall or type specific)
            if (is_int(strpos($a_output_html, "[list-" . $type . "]"))) {
                $a_output_html = preg_replace(
                    '~\[list-' . $type . '\]~i',
                    $this->renderer->renderSingleTypeBlock($type),
                    $a_output_html
                );
            }
        }
        
        // insert all item groups
        while (preg_match('~\[(item-group-([0-9]*))\]~i', $a_output_html, $found)) {
            $itgr_ref_id = (int) $found[2];
            
            $a_output_html = preg_replace(
                '~\[' . $found[1] . '\]~i',
                $this->renderer->renderSingleCustomBlock($itgr_ref_id),
                $a_output_html
            );
        }

        return $a_output_html;
    }

    /**
     * Render single block
     *
     * @param string block id
     * @return string
     */
    public function getSingleTypeBlockAsynch($type)
    {
        $this->initRenderer();
        // get all sub items
        $this->items = $this->getContainerObject()->getSubItems(
            $this->getContainerGUI()->isActiveAdministrationPanel()
        );


        $ref_ids = array_map(function ($i) {
            $parts = explode("_", $i);
            return $parts[2];
        }, $_POST["ids"]);

        // iterate all types
        if (is_array($this->items[$type]) &&
            $this->renderer->addTypeBlock($type)) {
            //$this->renderer->setBlockPosition($type, ++$pos);

            $position = 1;
            foreach ($this->items[$type] as $item_data) {
                $item_ref_id = $item_data["child"];

                if (in_array($item_ref_id, $ref_ids)) {
                    continue;
                }

                if ($this->block_limit > 0 && $position == $this->block_limit + 1) {
                    if ($position == $this->block_limit + 1) {
                        // render more button
                        $this->renderer->addShowMoreButton($type);
                    }
                    continue;
                }

                if (!$this->renderer->hasItem($item_ref_id)) {
                    $html = $this->renderItem($item_data, $position++);
                    if ($html != "") {
                        $this->renderer->addItemToBlock($type, $item_data["type"], $item_ref_id, $html);
                    }
                }
            }
        }

        return $this->renderer->renderSingleTypeBlock($type);
    }
    
    /**
    * Get grouped repository object types.
    *
    * @return	array	array of object types
    */
    public function getGroupedObjTypes()
    {
        $objDefinition = $this->obj_definition;
        
        if (empty($this->type_grps)) {
            $this->type_grps =
                $objDefinition->getGroupedRepositoryObjectTypes($this->getContainerObject()->getType());
        }
        return $this->type_grps;
    }

    /**
    * Get introduction.
    */
    public function getIntroduction()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $lng->loadLanguageModule("rep");
        
        $tpl = new ilTemplate("tpl.rep_intro.html", true, true, "Services/Repository");
        $tpl->setVariable("IMG_REP_LARGE", ilObject::_getIcon("", "big", "root"));
        $tpl->setVariable("TXT_WELCOME", $lng->txt("rep_intro"));
        $tpl->setVariable("TXT_INTRO_1", $lng->txt("rep_intro1"));
        $tpl->setVariable("TXT_INTRO_2", $lng->txt("rep_intro2"));
        $tpl->setVariable("TXT_INTRO_3", sprintf($lng->txt("rep_intro3"), $lng->txt("add")));
        $tpl->setVariable("TXT_INTRO_4", sprintf($lng->txt("rep_intro4"), $lng->txt("cat_add")));
        $tpl->setVariable("TXT_INTRO_5", $lng->txt("rep_intro5"));
        $tpl->setVariable("TXT_INTRO_6", $lng->txt("rep_intro6"));
        
        return $tpl->get();
    }

    /**
     * Get item groups HTML
     *
     * @param
     * @return
     */
    public function getItemGroupsHTML($a_pos = 0)
    {
        if (is_array($this->items["itgr"])) {
            foreach ($this->items["itgr"] as $itgr) {
                if (!$this->renderer->hasCustomBlock($itgr["child"])) {
                    $this->renderItemGroup($itgr);
                                        
                    $this->renderer->setBlockPosition($itgr["ref_id"], ++$a_pos);
                }
            }
        }
        return $a_pos;
    }
    
    /**
     * Render item group
     *
     * @param
     * @return
     */
    public function renderItemGroup($a_itgr)
    {
        $ilAccess = $this->access;
        $ilUser = $this->user;
        
        // #16493
        $perm_ok = ($ilAccess->checkAccess("visible", "", $a_itgr['ref_id']) &&
             $ilAccess->checkAccess("read", "", $a_itgr['ref_id']));

        include_once('./Services/Container/classes/class.ilContainerSorting.php');
        include_once('./Services/Object/classes/class.ilObjectActivation.php');
        $items = ilObjectActivation::getItemsByItemGroup($a_itgr['ref_id']);
        
        // if no permission is given, set the items to "rendered" but
        // do not display the whole block
        if (!$perm_ok) {
            foreach ($items as $item) {
                $this->renderer->hideItem($item["child"]);
            }
            return;
        }
        
        $item_list_gui = $this->getItemGUI($a_itgr);
        $item_list_gui->enableNotes(false);
        $item_list_gui->enableTags(false);
        $item_list_gui->enableComments(false);
        $item_list_gui->enableTimings(false);
        $item_list_gui->getListItemHTML(
            $a_itgr["ref_id"],
            $a_itgr["obj_id"],
            $a_itgr["title"],
            $a_itgr["description"]
        );
        $commands_html = $item_list_gui->getCommandsHTML();

        // determine behaviour
        include_once("./Modules/ItemGroup/classes/class.ilObjItemGroup.php");
        include_once("./Modules/ItemGroup/classes/class.ilItemGroupBehaviour.php");
        $beh = ilObjItemGroup::lookupBehaviour($a_itgr["obj_id"]);
        include_once("./Services/Container/classes/class.ilContainerBlockPropertiesStorage.php");
        $stored_val = ilContainerBlockPropertiesStorage::getProperty("itgr_" . $a_itgr["ref_id"], $ilUser->getId(), "opened");
        if ($stored_val !== false && $beh != ilItemGroupBehaviour::ALWAYS_OPEN) {
            $beh = ($stored_val == "1")
                ? ilItemGroupBehaviour::EXPANDABLE_OPEN
                : ilItemGroupBehaviour::EXPANDABLE_CLOSED;
        }

        $data = array(
            "behaviour" => $beh,
            "store-url" => "./ilias.php?baseClass=ilcontainerblockpropertiesstorage&cmd=store" .
                "&cont_block_id=itgr_" . $a_itgr['ref_id']
        );
        if (ilObjItemGroup::lookupHideTitle($a_itgr["obj_id"]) &&
            !$this->getContainerGUI()->isActiveAdministrationPanel()) {
            $this->renderer->addCustomBlock($a_itgr["ref_id"], "", $commands_html, $data);
        } else {
            $this->renderer->addCustomBlock($a_itgr["ref_id"], $a_itgr["title"], $commands_html, $data);
        }
    
        
        // render item group sub items
        
        $items = ilContainerSorting::_getInstance(
            $this->getContainerObject()->getId()
        )->sortSubItems('itgr', $a_itgr['obj_id'], $items);
        
        // #18285
        $items = ilContainer::getCompleteDescriptions($items);
        
        $position = 1;
        foreach ($items as $item) {
            // we are NOT using hasItem() here, because item might be in multiple item groups
            $html2 = $this->renderItem($item, $position++, false, "[itgr][" . $a_itgr['obj_id'] . "]");
            if ($html2 != "") {
                // :TODO: show it multiple times?
                $this->renderer->addItemToBlock($a_itgr["ref_id"], $item["type"], $item["child"], $html2, true);
            }
        }
    }
}
