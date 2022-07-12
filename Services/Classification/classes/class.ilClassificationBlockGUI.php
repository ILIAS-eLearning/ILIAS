<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\Classification\StandardGUIRequest;

/**
 * Classification block, displayed in different contexts, e.g. categories
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_IsCalledBy ilClassificationBlockGUI: ilColumnGUI
 */
class ilClassificationBlockGUI extends ilBlockGUI
{
    protected StandardGUIRequest $cl_request;
    protected ilObjectDefinition $obj_definition;
    protected ilTree $tree;
    protected string $parent_obj_type;
    protected int $parent_obj_id;
    protected int $parent_ref_id;
    protected array $providers;
    protected array $item_list_gui;
    protected static array $providers_cache;
    protected ilClassificationSessionRepository $repo;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->obj_definition = $DIC["objDefinition"];
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $lng = $DIC->language();
        
        parent::__construct();
                            
        $this->parent_ref_id = $this->requested_ref_id;
        $this->parent_obj_id = ilObject::_lookupObjId($this->parent_ref_id);
        $this->parent_obj_type = ilObject::_lookupType($this->parent_obj_id);
        
        $lng->loadLanguageModule("classification");
        $this->setTitle($lng->txt("clsfct_block_title"));
        // @todo: find another solution for this
        //$this->setFooterInfo($lng->txt("clsfct_block_info"));

        $this->cl_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->repo = new ilClassificationSessionRepository($this->parent_ref_id);
    }

    public function getBlockType() : string
    {
        return 'clsfct';
    }

    protected function isRepositoryObject() : bool
    {
        return false;
    }

    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;
        
        $cmd = $ilCtrl->getCmd();
        $next_class = $ilCtrl->getNextClass($this);

        switch ($next_class) {
            default:
                // explorer call
                if ($ilCtrl->isAsynch() && $cmd != "getAjax" && $cmd != "filterContainer") {
                    $this->getHTML();
                } else {
                    $this->$cmd();
                }
                break;
        }
    }
    
    public static function getScreenMode() : string
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        
        if ($ilCtrl->isAsynch()) {
            return "";
        }

        switch ($ilCtrl->getCmd()) {
            case "filterContainer":
                return IL_SCREEN_CENTER;
        }
        return "";
    }
    
    public function getHTML() : string
    {
        $tpl = $this->main_tpl;
        $ilCtrl = $this->ctrl;
        
        if (!$ilCtrl->isAsynch()) {
//            $this->repo->unsetAll();
        }
        
        $this->initProviders();
        
        if (!$this->validate()) {
            return "";
        }
        
        $tpl->addJavaScript("Services/Classification/js/ilClassification.js");
                
        return parent::getHTML();
    }
    
    public function getAjax() : void
    {
        $tpl = $this->main_tpl;
        
        $this->initProviders(true);
        
        echo $this->getHTML();
        echo $tpl->getOnLoadCodeForAsynch();

        exit();
    }

    protected function getLegacyContent() : string
    {
        $tpl = $this->main_tpl;

        $ilCtrl = $this->ctrl;
        
        $html = array();
        foreach ($this->providers as $provider) {
            $provider->render($html, $this);
        }
        
        //		$this->tpl->setVariable("BLOCK_ROW", "");
                    
        $ajax_block_id = "block_" . $this->getBlockType() . "_0";
        $ajax_block_url = $ilCtrl->getLinkTarget($this, "getAjax", "", true, false);
        $ajax_content_id = "il_center_col";
        $ajax_content_url = $ilCtrl->getLinkTarget($this, "filterContainer", "", true, false);

        $tabs = new ilTabsGUI();
        $tabs->setBackTarget($this->lng->txt("clsfct_back_to_cat"), (string) $ilCtrl->getParentReturn($this));
        $tabs->addTab("sel_objects", $this->lng->txt("clsfct_selected_objects"), "#");
        $tabs_html = $tabs->getHTML();


        // #15008 - always load regardless of content (because of redraw)
        $tpl->addOnLoadCode('il.Classification.setAjax("' . $ajax_block_id . '", "' .
            $ajax_block_url . '", "' . $ajax_content_id . '", "' . $ajax_content_url . '", ' . json_encode($tabs_html) . ');');

        $overall_html = "";
        if (sizeof($html)) {
            $btpl = new ilTemplate("tpl.classification_block.html", true, true, "Services/Classification");

            foreach ($html as $item) {
                $btpl->setCurrentBlock("provider_chunk_bl");
                $btpl->setVariable("TITLE", $item["title"]);
                $btpl->setVariable("CHUNK", $item["html"]);
                $btpl->parseCurrentBlock();
            }

            $overall_html .= $btpl->get();
            //$this->tpl->setVariable("DATA", $btpl->get());
        }
        return $overall_html;
    }
    
    protected function validate() : bool
    {
        return sizeof($this->providers);
    }
    
    protected function filterContainer() : void
    {
        $objDefinition = $this->obj_definition;
        $lng = $this->lng;
        $tree = $this->tree;
        $ilAccess = $this->access;
        $tpl = $this->main_tpl;
        
        $this->initProviders();
            
        // empty selection is invalid
        if ($this->repo->isEmpty()) {
            exit();
        }

        $all_matching_provider_object_ids = null;
        foreach ($this->providers as $provider) {
            $id = get_class($provider);
            $current = $this->repo->getValueForProvider($id);
            if ($current) {
                // combine providers AND
                $provider_object_ids = $provider->getFilteredObjects();
                if (is_array($all_matching_provider_object_ids)) {
                    $all_matching_provider_object_ids = array_intersect($all_matching_provider_object_ids, $provider_object_ids);
                } else {
                    $all_matching_provider_object_ids = $provider_object_ids;
                }
            }
        }
        $has_content = false;


        $ltpl = new ilTemplate("tpl.classification_object_list.html", true, true, "Services/Classification");
        
        if (is_array($all_matching_provider_object_ids) && sizeof($all_matching_provider_object_ids)) {
            $fields = array(
                "object_reference.ref_id"
                ,"object_data.obj_id"
                ,"object_data.type"
                ,"object_data.title"
                ,"object_data.description"
            );
            // see #28883 (tags + filter still work on current level only)
            // see also JF comment on https://docu.ilias.de/goto.php?target=wiki_1357_Tagging_in_Categories
            $matching = $tree->getSubTreeFilteredByObjIds(
                $this->parent_ref_id,
                $all_matching_provider_object_ids,
                $fields
            );
            //$matching = $this->getSubItemIds($all_matching_provider_object_ids);
            if (sizeof($matching)) {
                $valid_objects = array();
            
                // :TODO: not sure if this makes sense...
                include_once "Services/Object/classes/class.ilObjectListGUIPreloader.php";
                $preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_REPOSITORY);
                
                foreach ($matching as $item) {
                    if ($item["ref_id"] != $this->parent_ref_id &&
                        !$tree->isDeleted($item["ref_id"]) &&
                        $ilAccess->checkAccess("visible", "", $item["ref_id"])) {
                        // group all valid items in blocks
                        // by their parent group/course or category
                        $block_ref_id = 0;
                        $block_title = "";
                        foreach ($tree->getPathFull($item["ref_id"]) as $p) {
                            if (in_array($p["type"], array("root", "cat", "crs", "grp"))) {
                                $block_ref_id = $p["ref_id"];
                                $block_title = $p["title"];
                            }
                        }
                        if ($block_ref_id > 0) {
                            if (!is_array($valid_objects[$block_ref_id])) {
                                $valid_objects[$block_ref_id] = array(
                                    "title" => $block_title,
                                    "items" => array()
                                );
                            }
                            $valid_objects[$block_ref_id]["items"][] = $item;
                        }

                        $preloader->addItem($item["obj_id"], $item["type"], $item["ref_id"]);
                    }
                }
                $valid_objects = ilArrayUtil::sortArray($valid_objects, "title", "asc", false, true);
                if (sizeof($valid_objects)) {
                    $has_content = true;
                    
                    $preloader->preload();

                    $this->item_list_gui = array();
                    foreach ($valid_objects as $block) {
                        $items = ilArrayUtil::sortArray($block["items"], "title", "asc", false, true);
                        foreach ($items as $obj) {
                            $type = $obj["type"];

                            // get list gui class for each object type
                            if (empty($this->item_list_gui[$type])) {
                                $class = $objDefinition->getClassName($type);
                                $location = $objDefinition->getLocation($type);

                                $full_class = "ilObj" . $class . "ListGUI";

                                include_once($location . "/class." . $full_class . ".php");
                                $this->item_list_gui[$type] = new $full_class();
                                $this->item_list_gui[$type]->enableDelete(false);
                                $this->item_list_gui[$type]->enablePath(
                                    true,
                                    $this->parent_ref_id,
                                    new \ilSessionClassificationPathGUI()
                                );
                                $this->item_list_gui[$type]->enableLinkedPath(true);
                                $this->item_list_gui[$type]->enableCut(false);
                                $this->item_list_gui[$type]->enableCopy(false);
                                $this->item_list_gui[$type]->enableSubscribe(false);
                                $this->item_list_gui[$type]->enableLink(false);
                                $this->item_list_gui[$type]->enableIcon(true);

                                // :TOOD: for each item or just for each list?
                                foreach ($this->providers as $provider) {
                                    $provider->initListGUI($this->item_list_gui[$type]);
                                }
                            }

                            $html = $this->item_list_gui[$type]->getListItemHTML(
                                $obj["ref_id"],
                                $obj["obj_id"],
                                $obj["title"],
                                $obj["description"]
                            );

                            if ($html != "") {
                                $ltpl->setCurrentBlock("res_row");
                                $ltpl->setVariable("RESOURCE_HTML", $html);
                                $ltpl->parseCurrentBlock();
                            }
                        }
                        $ltpl->setCurrentBlock("block");
                        $ltpl->setVariable("BLOCK_TITLE", $block["title"]);
                        $ltpl->parseCurrentBlock();
                    }
                }
            }
        }

        if ($has_content) {
            echo $ltpl->get();
        } else {
            //$content_block->setContent($lng->txt("clsfct_content_no_match"));
            echo ilUtil::getSystemMessageHTML($lng->txt("clsfct_content_no_match"), "info");
        }

        exit();
    }
    
    protected function initProviders(bool $a_check_post = false) : void
    {
        if (!isset(self::$providers_cache[$this->parent_ref_id])) {
            include_once "Services/Classification/classes/class.ilClassificationProvider.php";
            self::$providers_cache[$this->parent_ref_id] = ilClassificationProvider::getValidProviders(
                $this->parent_ref_id,
                $this->parent_obj_id,
                $this->parent_obj_type
            );
        }
        $this->providers = self::$providers_cache[$this->parent_ref_id];
        if ($a_check_post && !$this->cl_request->getRedraw()) {
            foreach ($this->providers as $provider) {
                $id = get_class($provider);
                $current = $provider->importPostData($this->repo->getValueForProvider($id));
                if (is_array($current) || $current) {
                    $this->repo->setValueForProvider($id, $current);
                }
            }
        }
        
        foreach ($this->providers as $provider) {
            $id = get_class($provider);
            $current = $this->repo->getValueForProvider($id);
            if ($current) {
                $provider->setSelection($current);
            }
        }
    }

    protected function toggle() : void
    {
        $this->initProviders(true);
        $this->ctrl->returnToParent($this);
    }


    //
    // New rendering
    //

    protected bool $new_rendering = true;

    /**
     * Get sub item ids depending on container type that match the preselected
     * object ids
     * @param int[]
     */
    protected function getSubItemIds(array $obj_ids) : array
    {
        $tree = $this->tree;
        if (ilObject::_lookupType($this->parent_ref_id, true) == "cat") {
            $matching = array_filter($tree->getChilds($this->parent_ref_id), function ($item) use ($obj_ids) {
                return in_array($item["obj_id"], $obj_ids);
            });
        } else {
            $fields = array(
                "object_reference.ref_id"
            ,"object_data.obj_id"
            ,"object_data.type"
            ,"object_data.title"
            ,"object_data.description"
            );
            $matching = $tree->getSubTreeFilteredByObjIds($this->parent_ref_id, $obj_ids, $fields);
        }

        return $matching;
    }
}
