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

const IL_FM_POSITIVE = 1;
const IL_FM_NEGATIVE = 2;

/**
 * class for explorer view in admin frame
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @deprecated 11
 */
class ilExplorer
{
    protected ilObjectDefinition $obj_definition;
    protected ilErrorHandling $error;
    protected ilRbacSystem $rbacsystem;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    public string $id = "";
    public string $output = "";
    public array $format_options = [];
    public ilTree $tree;
    public string $target = "";
    public string $target_get = "";
    public string $params_get = "";
    public array $expanded = [];
    public string $order_column = "";
    public string $order_direction = "asc";
    public ?string $expand_target = null;
    public bool $rbac_check = false;
    public bool $output_icons = false;
    public string $expand_variable = "";
    // array ($type => clickable (empty means true, "n" means false)
    public array $is_clickable = [];
    public bool $post_sort = false;
    public bool $filtered = false;
    protected $filter = [];
    public bool $filter_mode;
    // expand entire tree regardless of values in $expanded
    public bool $expand_all = false;
    public $root_id = null;
    public bool $use_standard_frame = false;
    protected string $highlighted = "";
    protected bool $show_minus = true;
    protected int $counter = 0;
    protected bool $asnch_expanding = false;
    protected int $textwidth = 0;
    protected string $title = "";
    protected string $up_frame = "";
    protected string $a_up_script = "";
    protected string $up_params = "";
    protected string $frame_target = "";
    protected string $up_script = "";
    protected string $tree_lead = "";
    protected array $iconList = [];

    protected \ILIAS\Refinery\Factory $refinery;
    protected \ILIAS\HTTP\Wrapper\WrapperFactory $wrapper;

    public function __construct(string $a_target)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->obj_definition = $DIC["objDefinition"];
        $this->error = $DIC["ilErr"];
        $this->rbacsystem = $DIC->rbac()->system();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $objDefinition = $DIC["objDefinition"];
        $ilErr = $DIC["ilErr"];

        if (!isset($a_target) or !is_string($a_target)) {
            $ilErr->raiseError(get_class($this) . "::Constructor(): No target given!", $ilErr->WARNING);
        }

        // autofilter object types in devmode
        $devtypes = $objDefinition->getDevModeAll();

        if (count($devtypes) > 0) {
            // activate filter if objects found in devmode
            $this->setFiltered(true);

            foreach ($devtypes as $type) {
                $this->addFilter($type);
            }
        }

        $this->expanded = array();
        $this->target = $a_target;
        $this->target_get = 'ref_id';
        $this->frame_target = "content";
        $this->order_column = "title";
        $this->tree = new ilTree(ROOT_FOLDER_ID);
        $this->tree->initLangCode();
        $this->expand_target = $_SERVER["PATH_INFO"] ?? "";
        $this->rbac_check = true;
        $this->output_icons = true;
        $this->expand_variable = "expand";
        $this->setTitleLength(50);
        $this->post_sort = true;
        $this->setFilterMode(IL_FM_NEGATIVE);
        $this->highlighted = "";
        $this->show_minus = true;
        $this->counter = 0;
        $this->asnch_expanding = false;
        $this->refinery = $DIC->refinery();
        $this->wrapper = $DIC->http()->wrapper();
    }

    protected function requestStr(string $key) : string
    {
        $str = $this->refinery->kindlyTo()->string();
        if ($this->wrapper->post()->has($key)) {
            return $this->wrapper->post()->retrieve($key, $str);
        }
        if ($this->wrapper->query()->has($key)) {
            return $this->wrapper->query()->retrieve($key, $str);
        }
        return "";
    }

    public function setId(string $a_val) : void
    {
        $this->id = $a_val;
    }
    
    public function getId() : string
    {
        return $this->id;
    }
    
    public function setAsynchExpanding(bool $a_val) : void
    {
        $this->asnch_expanding = $a_val;
    }

    public function getAsynchExpanding() : bool
    {
        return $this->asnch_expanding;
    }

    public function initItemCounter(int $a_number) : void
    {
        $this->counter = $a_number;
    }
    
    public function setTitle(string $a_val) : void
    {
        $this->title = $a_val;
    }
    
    public function setTitleLength(int $a_length) : void
    {
        $this->textwidth = $a_length;
    }
    
    public function getTitleLength() : int
    {
        return $this->textwidth;
    }
    
    public function getTitle() : string
    {
        return $this->title;
    }
    
    public function setRoot($a_root_id) : void
    {
        #$this->tree = new ilTree(ROOT_FOLDER_ID,$a_root_id);
        $this->root_id = $a_root_id;
    }
    
    public function getRoot() : ?int
    {
        return $this->root_id ?? $this->tree->getRootId();
    }

    public function setOrderColumn(string $a_column) : void
    {
        $this->order_column = $a_column;
    }

    public function setOrderDirection(string $a_direction) : void
    {
        if ($a_direction === "desc") {
            $this->order_direction = $a_direction;
        } else {
            $this->order_direction = "asc";
        }
    }

    /**
     * @param string $a_target_get varname containing Ids to be used in GET-string
     */
    public function setTargetGet(string $a_target_get) : void
    {
        $ilErr = $this->error;

        if (!isset($a_target_get) or !is_string($a_target_get)) {
            $ilErr->raiseError(get_class($this) . "::setTargetGet(): No target given!", $ilErr->WARNING);
        }

        $this->target_get = $a_target_get;
    }

    public function setParamsGet(array $a_params_get) : void
    {
        $ilErr = $this->error;

        if (!isset($a_params_get) or !is_array($a_params_get)) {
            $ilErr->raiseError(get_class($this) . "::setTargetGet(): No target given!", $ilErr->WARNING);
        }
        $str = "";
        foreach ($a_params_get as $key => $val) {
            $str .= "&" . $key . "=" . $val;
        }

        $this->params_get = $str;
    }


    /**
     * target script for expand icons
     * @param	string		$a_exp_target	script name of target script(may include parameters)
     *										initially set to $_SERVER["PATH_INFO"]
     */
    public function setExpandTarget(string $a_exp_target) : void
    {
        $this->expand_target = $a_exp_target;
    }
    
    public function setFrameUpdater(
        string $a_up_frame,
        string $a_up_script,
        string $a_params = ""
    ) : void {
        $this->up_frame = $a_up_frame;
        $this->up_script = $a_up_script;
        $this->up_params = $a_params;
    }

    
    public function highlightNode(string $a_id) : void
    {
        $this->highlighted = $a_id;
    }

    public function checkPermissions(bool $a_check) : void
    {
        $this->rbac_check = $a_check;
    }

    public function setSessionExpandVariable(string $a_var_name = "expand") : void
    {
        $this->expand_variable = $a_var_name;
    }

    public function outputIcons(bool $a_icons) : void
    {
        $this->output_icons = $a_icons;
    }

    public function setClickable(string $a_type, bool $a_clickable) : void
    {
        if ($a_clickable) {
            $this->is_clickable[$a_type] = "";
        } else {
            $this->is_clickable[$a_type] = "n";
        }
    }

    public function isVisible(
        $a_ref_id,
        string $a_type
    ) : bool {
        $rbacsystem = $this->rbacsystem;
        
        if (!$this->rbac_check) {
            return true;
        }
        
        $visible = $rbacsystem->checkAccess('visible', $a_ref_id);

        return $visible;
    }

    // Set tree leading content
    public function setTreeLead(string $a_val) : void
    {
        $this->tree_lead = $a_val;
    }

    public function getTreeLead() : string
    {
        return $this->tree_lead;
    }

    //  check if links for certain object type are activated
    public function isClickable(
        string $type,
        int $ref_id = 0
    ) : bool {
        // in this standard implementation
        // only the type determines, wether an object should be clickable or not
        // but this method can be overwritten and make $exp->setFilterMode(IL_FM_NEGATIVE);use of the ref id
        // (this happens e.g. in class ilRepositoryExplorerGUI)
        return $this->is_clickable[$type] !== "n";
    }

    public function setPostSort(bool $a_sort) : void
    {
        $this->post_sort = $a_sort;
    }

    public function setFilterMode(int $a_mode = IL_FM_NEGATIVE) : void
    {
        $this->filter_mode = $a_mode;
    }

    /**
     * @return	int		filter mode IL_FM_NEGATIVE | IL_FM_NEGATIVE
     */
    public function getFilterMode() : int
    {
        return $this->filter_mode;
    }

    /**
     * Set use standard frame. If true, the standard
     * explorer frame (like in the repository) is put around the tree.
     */
    public function setUseStandardFrame(bool $a_val) : void
    {
        $this->use_standard_frame = $a_val;
    }
    
    public function getUseStandardFrame() : bool
    {
        return $this->use_standard_frame;
    }
    
    public function getChildsOfNode($a_parent_id) : array
    {
        return $this->tree->getChilds($a_parent_id, $this->order_column);
    }
    
    
    /**
     * Creates output for explorer view in admin menue
     * recursive method
     */
    public function setOutput(
        $a_parent_id,
        int $a_depth = 1,
        int $a_obj_id = 0,
        bool $a_highlighted_subtree = false
    ) : void {
        $ilErr = $this->error;

        $parent_index = 0;

        if (!isset($a_parent_id)) {
            $ilErr->raiseError(get_class($this) . "::setOutput(): No node_id given!", $ilErr->WARNING);
        }

        if ($this->showChilds($a_parent_id)) {
            $objects = $this->getChildsOfNode($a_parent_id);
        } else {
            $objects = array();
        }

        $objects = $this->modifyChilds($a_parent_id, $objects);

        // force expansion (of single nodes)
        if ($this->forceExpanded($a_parent_id) && !in_array($a_parent_id, $this->expanded)) {
            $this->expanded[] = $a_parent_id;
        }

        if (count($objects) > 0) {
            // Maybe call a lexical sort function for the child objects
            $tab = ++$a_depth - 2;
            if ($this->post_sort) {
                $objects = $this->sortNodes($objects, $a_obj_id);
            }
            $skip_rest = false;
            foreach ($objects as $key => $object) {
                // skip childs, if parent is not expanded
                if (!$this->forceExpanded($object["child"]) && $skip_rest) {
                    continue;
                }
                //echo "<br>-".$object["child"]."-".$this->forceExpanded($object["child"])."-";
                //ask for FILTER
                if ($this->filtered === false || $this->checkFilter($object["type"]) === false) {
                    if ($this->isVisible($object['child'], $object['type'])) {
                        #echo 'CHILD getIndex() '.$object['child'].' parent: '.$this->getRoot();
                        if ($object["child"] != $this->getRoot()) {
                            $parent_index = $this->getIndex($object);
                        }
                        $this->format_options[(string) $this->counter]["parent"] = $object["parent"];
                        $this->format_options[(string) $this->counter]["child"] = $object["child"];
                        $this->format_options[(string) $this->counter]["title"] = $object["title"];
                        $this->format_options[(string) $this->counter]["type"] = $object["type"];
                        $this->format_options[(string) $this->counter]["obj_id"] = $object["obj_id"];
                        $this->format_options[(string) $this->counter]["desc"] = "obj_" . $object["type"];
                        $this->format_options[(string) $this->counter]["depth"] = $tab;
                        $this->format_options[(string) $this->counter]["container"] = false;
                        $this->format_options[(string) $this->counter]["visible"] = true;
                        $this->format_options[(string) $this->counter]["highlighted_subtree"] = $a_highlighted_subtree;

                        // Create prefix array
                        for ($i = 0; $i < $tab; ++$i) {
                            $this->format_options[(string) $this->counter]["tab"][] = 'blank';
                        }

                        // fix explorer (sometimes explorer disappears)
                        if ($parent_index === 0) {
                            if (!$this->expand_all && !in_array($object["parent"], $this->expanded)) {
                                $this->expanded[] = $object["parent"];
                            }
                        }

                        // only if parent is expanded and visible, object is visible
                        if ($object["child"] != $this->getRoot() && ((!$this->expand_all && !in_array($object["parent"], $this->expanded))
                           or !$this->format_options[(string) $parent_index]["visible"])) {
                            if (!$this->forceExpanded($object["child"])) {
                                // if parent is not expanded, and one child is
                                // visible we don't need more information and
                                // can skip the rest of the childs
                                if ($this->format_options[(string) $this->counter]["visible"]) {
                                    //echo "-setSkipping";
                                    $skip_rest = true;
                                }
                                $this->format_options[(string) $this->counter]["visible"] = false;
                            }
                        }

                        // if object exists parent is container
                        if ($object["child"] != $this->getRoot()) {
                            $this->format_options[(string) $parent_index]["container"] = true;

                            if ($this->expand_all || in_array($object["parent"], $this->expanded)) {
                                //echo "<br>-".$object["child"]."-".$this->forceExpanded($object["child"])."-";
                                if ($this->forceExpanded($object["parent"])) {
                                    $this->format_options[(string) $parent_index]["tab"][($tab - 2)] = 'forceexp';
                                } else {
                                    $this->format_options[(string) $parent_index]["tab"][($tab - 2)] = 'minus';
                                }
                            } else {
                                $this->format_options[(string) $parent_index]["tab"][($tab - 2)] = 'plus';
                            }
                        }
                        //echo "-"."$parent_index"."-";
                        //var_dump($this->format_options["$parent_index"]);
                        ++$this->counter;

                        // stop recursion if 2. level beyond expanded nodes is reached
                        if ($this->expand_all || in_array($object["parent"], $this->expanded) or ($object["parent"] == 0)
                            or $this->forceExpanded($object["child"])) {
                            $highlighted_subtree = $a_highlighted_subtree ||
                                ($object["child"] == $this->highlighted);

                            // recursive
                            $this->setOutput($object["child"], $a_depth, $object['obj_id'], $highlighted_subtree);
                        }
                    } //if
                } //if FILTER
            } //foreach
        } //if
    } //function

    public function modifyChilds(
        $a_parent_id,
        array $a_objects
    ) : array {
        return $a_objects;
    }

    /**
     * determines wether the childs of an object should be shown or not
     * note: this standard implementation always returns true
     * but it could be overwritten by derived classes (e.g. ilRepositoryExplorerGUI)
     */
    public function showChilds($a_parent_id) : bool
    {
        return true;
    }

    /**
     * force expansion of node
     */
    public function forceExpanded($a_obj_id) : bool
    {
        return false;
    }

    public function getMaximumTreeDepth() : int
    {
        $this->tree->getMaximumDepth();
        return 0;   // seems to not return the value...
    }
    
    
    public function getOutput() : string
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        $this->format_options[0]["tab"] = array();

        $depth = $this->getMaximumTreeDepth();

        for ($i = 0;$i < $depth;++$i) {
            $this->createLines($i);
        }

        ilYuiUtil::initConnection();
        $tpl->addJavaScript("./Services/UIComponent/Explorer/js/ilExplorer.js");

        //echo "hh";
        // set global body class
        //		$tpl->setBodyClass("il_Explorer");
        
        $tpl_tree = new ilTemplate("tpl.tree.html", true, true, "Services/UIComponent/Explorer");
        
        // updater
        if (($this->requestStr("ict") !== "" ||
            $this->requestStr("collapseAll") !== "" ||
            $this->requestStr("expandAll") !== "") && $this->up_frame !== "") {
            $tpl_tree->setCurrentBlock("updater");
            $tpl_tree->setVariable("UPDATE_FRAME", $this->up_frame);
            $tpl_tree->setVariable("UPDATE_SCRIPT", $this->up_script);
            if (is_array($this->up_params)) {
                $up_str = $lim = "";
                foreach ($this->up_params as $p) {
                    $up_str .= $lim . "'" . $p . "'";
                    $lim = ",";
                }
                $tpl_tree->setVariable("UPDATE_PARAMS", $up_str);
            }
            $tpl_tree->parseCurrentBlock();
        }
        
        $cur_depth = -1;
        foreach ($this->format_options as $key => $options) {
            //echo "-".$options["depth"]."-";
            if (!$options["visible"]) {
                continue;
            }
            
            // end tags
            $this->handleListEndTags($tpl_tree, $cur_depth, $options["depth"]);
            
            // start tags
            $this->handleListStartTags($tpl_tree, $cur_depth, $options["depth"]);
            
            $cur_depth = $options["depth"];
            
            if ($options["visible"] and $key != 0) {
                $this->formatObject($tpl_tree, $options["child"], $options, $options['obj_id']);
            }
            if ($key == 0) {
                $this->formatHeader($tpl_tree, $options["child"], $options);
            }
        }

        $this->handleListEndTags($tpl_tree, $cur_depth, -1);
        
        $tpl_tree->setVariable("TREE_LEAD", "");
        if ($this->tree_lead !== "") {
            $tpl_tree->setCurrentBlock("tree_lead");
            $tpl_tree->setVariable("TREE_LEAD", $this->tree_lead);
            $tpl_tree->parseCurrentBlock();
        }
        if ($this->getId() !== "") {
            $tpl_tree->setVariable("TREE_ID", 'id="' . $this->getId() . '_tree"');
        }

        $html = $tpl_tree->get();
        
        if ($this->getUseStandardFrame()) {
            $mtpl = new ilGlobalTemplate("tpl.main.html", true, true);
            $mtpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
            $mtpl->setVariable("BODY_CLASS", "il_Explorer");
            $mtpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
            if ($this->getTitle() !== "") {
                $mtpl->setVariable("TXT_EXPLORER_HEADER", $this->getTitle());
            }
            if ($this->getId() !== "") {
                $mtpl->setVariable("ID", 'id="' . $this->getId() . '"');
            }
            $mtpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.png", false));
            $mtpl->setCurrentBlock("content");
            $mtpl->setVariable("EXPLORER", $html);
            $mtpl->setVariable("EXP_REFRESH", $lng->txt("refresh"));
            $mtpl->parseCurrentBlock();
            $html = $mtpl->get();
        }
        
        return $html;
    }
    
    
    
    /**
     * handle list end tags (</li> and </ul>)
     */
    public function handleListEndTags(
        ilTemplate $a_tpl_tree,
        int $a_cur_depth,
        int $a_item_depth
    ) : void {
        if ($a_item_depth < $a_cur_depth) {
            // </li></ul> for ending lists
            for ($i = 0; $i < ($a_cur_depth - $a_item_depth); $i++) {
                $a_tpl_tree->touchBlock("end_list_item");
                $a_tpl_tree->touchBlock("element");

                $a_tpl_tree->touchBlock("end_list");
                $a_tpl_tree->touchBlock("element");
            }
        } elseif ($a_item_depth == $a_cur_depth) {
            // </li> for ending list items
            $a_tpl_tree->touchBlock("end_list_item");
            $a_tpl_tree->touchBlock("element");
        }
    }
    
    /**
     * handle list start tags (<ul> and <li>)
     */
    public function handleListStartTags(
        ilTemplate $a_tpl_tree,
        int $a_cur_depth,
        int $a_item_depth
    ) : void {
        // start tags
        if ($a_item_depth > $a_cur_depth) {
            // <ul><li> for new lists
            if ($a_item_depth > 1) {
                $a_tpl_tree->touchBlock("start_list");
            } else {
                $a_tpl_tree->touchBlock("start_list_no_indent");
            }
            $a_tpl_tree->touchBlock("element");
        }
        $a_tpl_tree->touchBlock("start_list_item");
        $a_tpl_tree->touchBlock("element");
    }

    public function formatHeader(
        ilTemplate $tpl,
        $a_obj_id,
        array $a_option
    ) : void {
    }

    public function formatObject(
        ilTemplate $tpl,
        $a_node_id,
        array $a_option,
        $a_obj_id = 0
    ) : void {
        $lng = $this->lng;
        $ilErr = $this->error;

        if (!isset($a_node_id) or !is_array($a_option)) {
            $ilErr->raiseError(get_class($this) . "::formatObject(): Missing parameter or wrong datatype! " .
                                    "node_id: " . $a_node_id . " options:" . var_export($a_option, true), $ilErr->WARNING);
        }

        $pic = false;
        foreach ((array) $a_option["tab"] as $picture) {
            if ($picture === 'plus') {
                $tpl->setCurrentBlock("expander");
                $tpl->setVariable("EXP_DESC", $lng->txt("collapsed"));
                $tpl->setVariable("LINK_NAME", $a_node_id);
                if (!$this->getAsynchExpanding()) {
                    $target = $this->createTarget('+', $a_node_id, $a_option["highlighted_subtree"]);
                    $tpl->setVariable("LINK_TARGET_EXPANDER", $target);
                } else {
                    $target = $this->createTarget('+', $a_node_id, $a_option["highlighted_subtree"], false);
                    $tpl->setVariable("ONCLICK_TARGET_EXPANDER", " onclick=\"return il.Explorer.refresh('tree_div', '" . $target . "');\"");
                    $tpl->setVariable("LINK_TARGET_EXPANDER", "#");
                }
                $tpl->setVariable("IMGPATH", $this->getImage("browser/plus.png"));
                $tpl->parseCurrentBlock();
                $pic = true;
            }

            if ($picture === 'forceexp') {
                $tpl->setCurrentBlock("expander");
                $tpl->setVariable("EXP_DESC", $lng->txt("expanded"));
                $target = $this->createTarget('+', $a_node_id);
                $tpl->setVariable("LINK_NAME", $a_node_id);
                $tpl->setVariable("LINK_TARGET_EXPANDER", $target);
                $tpl->setVariable("IMGPATH", $this->getImage("browser/forceexp.png"));
                $tpl->parseCurrentBlock();
                $pic = true;
            }

            if ($picture === 'minus' && $this->show_minus) {
                $tpl->setCurrentBlock("expander");
                $tpl->setVariable("EXP_DESC", $lng->txt("expanded"));
                $tpl->setVariable("LINK_NAME", $a_node_id);
                if (!$this->getAsynchExpanding()) {
                    $target = $this->createTarget('-', $a_node_id, $a_option["highlighted_subtree"]);
                    $tpl->setVariable("LINK_TARGET_EXPANDER", $target);
                } else {
                    $target = $this->createTarget('-', $a_node_id, $a_option["highlighted_subtree"], false);
                    $tpl->setVariable("ONCLICK_TARGET_EXPANDER", " onclick=\"return il.Explorer.refresh('tree_div', '" . $target . "');\"");
                    $tpl->setVariable("LINK_TARGET_EXPANDER", "#");
                }
                $tpl->setVariable("IMGPATH", $this->getImage("browser/minus.png"));
                $tpl->parseCurrentBlock();
                $pic = true;
            }
        }
        
        if (!$pic) {
            $tpl->setCurrentBlock("blank");
            $tpl->setVariable("BLANK_PATH", $this->getImage("browser/blank.png"));
            $tpl->parseCurrentBlock();
        }

        if ($this->output_icons) {
            $tpl->setCurrentBlock("icon");
            $tpl->setVariable("ICON_IMAGE", $this->getImage("icon_" . $a_option["type"] . ".svg", $a_option["type"], $a_obj_id));
            
            $tpl->setVariable("TARGET_ID", "iconid_" . $a_node_id);
            $this->iconList[] = "iconid_" . $a_node_id;
            $tpl->setVariable(
                "TXT_ALT_IMG",
                $this->getImageAlt($lng->txt("icon") . " " . $lng->txt($a_option["desc"]), $a_option["type"], $a_obj_id)
            );
            $tpl->parseCurrentBlock();
        }
        
        if (($sel = $this->buildSelect($a_node_id, $a_option['type'])) !== '') {
            $tpl->setCurrentBlock('select');
            $tpl->setVariable('OBJ_SEL', $sel);
            $tpl->parseCurrentBlock();
        }

        if ($this->isClickable($a_option["type"], $a_node_id)) {	// output link
            $tpl->setCurrentBlock("link");
            //$target = (strpos($this->target, "?") === false) ?
            //	$this->target."?" : $this->target."&";
            //$tpl->setVariable("LINK_TARGET", $target.$this->target_get."=".$a_node_id.$this->params_get);
            $tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($a_node_id, $a_option["type"]));
                
            $style_class = $this->getNodeStyleClass($a_node_id, $a_option["type"]);
            
            if ($style_class !== "") {
                $tpl->setVariable("A_CLASS", ' class="' . $style_class . '" ');
            }

            if (($onclick = $this->buildOnClick($a_node_id, $a_option["type"], $a_option["title"])) != "") {
                $tpl->setVariable("ONCLICK", "onClick=\"$onclick\"");
            }

            //$tpl->setVariable("LINK_NAME", $a_node_id);
            $tpl->setVariable(
                "TITLE",
                ilStr::shortenTextExtended(
                    $this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]),
                    $this->textwidth,
                    true
                )
            );
            $tpl->setVariable(
                "DESC",
                ilStr::shortenTextExtended(
                    $this->buildDescription($a_option["description"] ?? "", $a_node_id, $a_option["type"]),
                    $this->textwidth,
                    true
                )
            );
            $frame_target = $this->buildFrameTarget($a_option["type"], $a_node_id, $a_option["obj_id"]);
            if ($frame_target !== "") {
                $tpl->setVariable("TARGET", " target=\"" . $frame_target . "\"");
            }
        } else {			// output text only
            $tpl->setCurrentBlock("text");
            $tpl->setVariable(
                "OBJ_TITLE",
                ilStr::shortenTextExtended(
                    $this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]),
                    $this->textwidth,
                    true
                )
            );
            $tpl->setVariable(
                "OBJ_DESC",
                ilStr::shortenTextExtended(
                    $this->buildDescription($a_option["desc"], $a_node_id, $a_option["type"]),
                    $this->textwidth,
                    true
                )
            );
        }
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("list_item");
        $tpl->parseCurrentBlock();
        $tpl->touchBlock("element");
    }

    public function getImage(
        string $a_name,
        string $a_type = "",
        $a_obj_id = ""
    ) : string {
        return ilUtil::getImagePath($a_name);
    }
    
    public function getImageAlt(
        string $a_default_text,
        string $a_type = "",
        $a_obj_id = ""
    ) : string {
        return $a_default_text;
    }
    
    public function getNodeStyleClass(
        $a_id,
        string $a_type
    ) : string {
        if ($a_id == $this->highlighted) {
            return "il_HighlightedNode";
        }
        return "";
    }

    public function buildLinkTarget(
        $a_node_id,
        string $a_type
    ) : string {
        $target = (strpos($this->target, "?") === false)
            ? $this->target . "?"
            : $this->target . "&";
        return $target . $this->target_get . "=" . $a_node_id . $this->params_get;
    }

    public function buildOnClick(
        $a_node_id,
        string $a_type,
        string $a_title
    ) : string {
        return "";
    }

    public function buildTitle(
        string $a_title,
        $a_id,
        string $a_type
    ) : string {
        return $a_title;
    }

    public function buildDescription(
        string $a_desc,
        $a_id,
        string $a_type
    ) : string {
        return "";
    }
    
    /**
     * standard implementation for adding an option select box between image and title
     */
    public function buildSelect($a_node_id, string $a_type) : string
    {
        return "";
    }
    
    public function buildFrameTarget(
        string $a_type,
        $a_child = 0,
        $a_obj_id = 0
    ) : string {
        return $this->frame_target;
    }


    public function createTarget(
        string $a_type,
        $a_node_id,
        bool $a_highlighted_subtree = false,
        bool $a_append_anch = true
    ) : string {
        $ilErr = $this->error;

        if (!isset($a_type) or !is_string($a_type) or !isset($a_node_id)) {
            $ilErr->raiseError(get_class($this) . "::createTarget(): Missing parameter or wrong datatype! " .
                                    "type: " . $a_type . " node_id:" . $a_node_id, $ilErr->WARNING);
        }

        // SET expand parameter:
        //     positive if object is expanded
        //     negative if object is compressed
        $a_node_id = $a_type === '+' ? $a_node_id : -(int) $a_node_id;

        $sep = (is_int(strpos($this->expand_target, "?")))
            ? "&"
            : "?";
        
        // in current tree flag
        $ict_str = ($a_highlighted_subtree || $this->highlighted === "")
            ? "&ict=1"
            : "";
        if ($this->getAsynchExpanding()) {
            $ict_str .= "&cmdMode=asynch";
        }
        if ($a_append_anch) {
            return $this->expand_target . $sep . $this->expand_variable . "=" . $a_node_id . $this->params_get . $ict_str . "#" . abs($a_node_id);
        } else {
            return $this->expand_target . $sep . $this->expand_variable . "=" . $a_node_id . $this->params_get . $ict_str;
        }
    }

    public function setFrameTarget(string $a_target) : void
    {
        $this->frame_target = $a_target;
    }

    public function createLines(int $a_depth) : void
    {
        for ($i = 0, $iMax = count($this->format_options); $i < $iMax; ++$i) {
            if ($this->format_options[$i]["depth"] == $a_depth + 1
               and !$this->format_options[$i]["container"]
                and $this->format_options[$i]["depth"] != 1) {
                $this->format_options[$i]["tab"][(string) $a_depth] = "quer";
            }

            if ($this->format_options[$i]["depth"] == $a_depth + 2) {
                if ($this->is_in_array($i + 1, $this->format_options[$i]["depth"])) {
                    $this->format_options[$i]["tab"][(string) $a_depth] = "winkel";
                } else {
                    $this->format_options[$i]["tab"][(string) $a_depth] = "ecke";
                }
            }

            if ($this->format_options[$i]["depth"] > $a_depth + 2) {
                if ($this->is_in_array($i + 1, $a_depth + 2)) {
                    $this->format_options[$i]["tab"][(string) $a_depth] = "hoch";
                }
            }
        }
    }

    public function is_in_array(
        int $a_start,
        int $a_depth
    ) : bool {
        for ($i = $a_start, $iMax = count($this->format_options); $i < $iMax; ++$i) {
            if ($this->format_options[$i]["depth"] < $a_depth) {
                break;
            }

            if ($this->format_options[$i]["depth"] == $a_depth) {
                return true;
            }
        }
        return false;
    }

    // get index of format_options array from specific ref_id,parent_id
    public function getIndex(array $a_data) : int
    {
        if (!is_array($this->format_options)) {
            return -1;
        }
        
        foreach ($this->format_options as $key => $value) {
            if (($value["child"] == $a_data["parent"])) {
                return $key;
            }
        }
        
        return -1;
    }

    public function addFilter(string $a_item) : bool
    {
        if (is_array($this->filter)) {
            //run through filter
            foreach ($this->filter as $item) {
                if ($item === $a_item) {
                    return false;
                }
            }
        } else {
            $this->filter = array();
        }
        $this->filter[] = $a_item;

        return true;
    }

    public function delFilter(string $a_item) : bool
    {
        $deleted = 0;
        //check if a filter exists
        if (is_array($this->filter)) {
            //build copy of the existing filter without the given item
            $tmp = array();

            foreach ($this->filter as $item) {
                if ($item !== $a_item) {
                    $tmp[] = $item;
                } else {
                    $deleted = 1;
                }
            }

            $this->filter = $tmp;
        } else {
            return false;
        }

        return $deleted === 1;
    }

    /**
     * set the expand option
     * this value is stored in a SESSION variable to save it different view (lo view, frm view,...)
     */
    public function setExpand($a_node_id) : void
    {
        // IF ISN'T SET CREATE SESSION VARIABLE
        if (!is_array(ilSession::get($this->expand_variable))) {
            ilSession::set($this->expand_variable, [$this->getRoot()]);
        }
        // IF $_GET["expand"] is positive => expand this node
        if ($a_node_id > 0 && !in_array($a_node_id, ilSession::get($this->expand_variable))) {
            $exp = ilSession::get($this->expand_variable);
            $exp[] = $a_node_id;
            ilSession::set($this->expand_variable, $exp);
        }
        // IF $_GET["expand"] is negative => compress this node
        if ($a_node_id < 0) {
            $key = array_keys(ilSession::get($this->expand_variable), -(int) $a_node_id);
            $exp = ilSession::get($this->expand_variable);
            unset($exp[$key[0]]);
            ilSession::set($this->expand_variable, $exp);
        }
        $this->expanded = (array) ilSession::get($this->expand_variable);
    }

    /**
     * force expandAll. if true all nodes are expanded regardless of the values
     * in $expanded (default: false)
     */
    public function forceExpandAll(
        bool $a_mode,
        bool $a_show_minus = true
    ) : void {
        $this->expand_all = $a_mode;
        $this->show_minus = $a_show_minus;
    }

    public function setFiltered(bool $a_bool) : bool
    {
        $this->filtered = $a_bool;
        return true;
    }

    public function checkFilter(string $a_item) : bool
    {
        if (is_array($this->filter)) {
            if (in_array($a_item, $this->filter)) {
                $ret = true;
            } else {
                $ret = false;
            }
        } else {
            $ret = false;
        }

        if ($this->getFilterMode() === IL_FM_NEGATIVE) {
            return $ret;
        } else {
            return !$ret;
        }
    }

    /**
     * sort nodes and put adm object to the end of sorted array
     */
    public function sortNodes(array $a_nodes, $a_parent_obj_id) : array
    {
        $adm_node = null;
        foreach ($a_nodes as $key => $node) {
            if ($node["type"] === "adm") {
                $match = $key;
                $adm_node = $node;
                break;
            }
        }

        // cut off adm node
        if (isset($match)) {
            array_splice($a_nodes, $match, 1);
        }

        $a_nodes = ilArrayUtil::sortArray($a_nodes, $this->order_column, $this->order_direction);

        // append adm node to end of list
        if (isset($match)) {
            $a_nodes[] = $adm_node;
        }

        return $a_nodes;
    }
}
