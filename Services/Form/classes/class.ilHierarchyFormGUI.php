<?php declare(strict_types=1);

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

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * This class represents a hierarchical form. These forms are used for
 * quick editing, where each node is represented by it's title.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHierarchyFormGUI extends ilFormGUI
{
    protected string $exp_target_script = "";
    protected string $icon = "";
    protected string $exp_id = "";
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected string $expand_variable = "";
    protected ?array $white_list = null;
    protected array $highlighted_nodes = [];
    protected string $focus_id = "";
    protected string $exp_frame = "";
    protected string $triggered_update_command = "";
    protected array $drag_target = [];
    protected array $drag_content = [];
    protected object $parent_obj;
    protected string $parent_cmd = "";
    protected ilTree $tree;
    protected int $currenttopnodeid = 0;
    protected string $title = "";
    protected string $checkboxname = "";
    protected string $dragicon = "";
    protected int $maxdepth = 0;
    protected array $help_items = [];
    protected array $diss_menues = [];
    protected array $multi_commands = [];
    protected array $commands = [];
    protected array $expanded = [];
    protected HTTP\Services $http;
    protected Refinery\Factory $refinery;


    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        
        $this->maxdepth = -1;
        $this->multi_commands = array();
        $this->commands = array();
        $this->drag_target[] = array();
        $this->drag_content[] = array();
        $lng->loadLanguageModule("form");
        $this->setCheckboxName("cbox");
        $this->help_items = array();
        
        ilYuiUtil::initDragDrop();
        $tpl->addJavascript("./Services/Form/js/ServiceFormHierarchyForm.js");

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    public function setParentCommand(
        object $a_parent_obj,
        string $a_parent_cmd
    ) : void {
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;
    }
    
    public function getParentObject() : object
    {
        return $this->parent_obj;
    }
    
    public function getParentCommand() : string
    {
        return $this->parent_cmd;
    }

    /**
     * @throws ilException
     */
    public function setId(string $a_id) : void
    {
        throw new ilException("ilHierarchyFormGUI does currently not support multiple forms (multiple IDs). ID is always hform.");
    }

    public function getId() : string
    {
        return "hform";
    }

    public function setTree(ilTree $a_tree) : void
    {
        $this->tree = $a_tree;
    }

    public function getTree() : ilTree
    {
        return $this->tree;
    }

    public function setCurrentTopNodeId(int $a_currenttopnodeid) : void
    {
        $this->currenttopnodeid = $a_currenttopnodeid;
    }

    public function getCurrentTopNodeId() : int
    {
        return $this->currenttopnodeid;
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setIcon(string $a_icon) : void
    {
        $this->icon = $a_icon;
    }

    public function getIcon() : string
    {
        return $this->icon;
    }

    public function setCheckboxName(string $a_checkboxname) : void
    {
        $this->checkboxname = $a_checkboxname;
    }

    public function getCheckboxName() : string
    {
        return $this->checkboxname;
    }

    public function setDragIcon(string $a_dragicon) : void
    {
        $this->dragicon = $a_dragicon;
    }

    public function getDragIcon() : string
    {
        return $this->dragicon;
    }

    public function setMaxDepth(int $a_maxdepth) : void
    {
        $this->maxdepth = $a_maxdepth;
    }

    public function getMaxDepth() : int
    {
        return $this->maxdepth;
    }

    public function setExplorerUpdater(
        string $a_exp_frame,
        string $a_exp_id,
        string $a_exp_target_script
    ) : void {
        $this->exp_frame = $a_exp_frame;
        $this->exp_id = $a_exp_id;
        $this->exp_target_script = $a_exp_target_script;
    }
    
    public function setTriggeredUpdateCommand(
        string $a_triggered_update_command
    ) : void {
        $this->triggered_update_command = $a_triggered_update_command;
    }

    public function addHelpItem(
        string $a_text,
        string $a_image = ""
    ) : void {
        $this->help_items[] = array("text" => $a_text,
            "image" => $a_image);
    }

    public function getHelpItems() : array
    {
        return $this->help_items;
    }
    
    // Makes a nodes (following droparea) a drag target
    public function makeDragTarget(
        string $a_id,
        string $a_group,
        bool $a_first_child_drop_area = false,
        bool $a_as_subitem = false,
        string $a_diss_text = ""
    ) : void {
        if ($a_first_child_drop_area == true) {		// first child drop areas only insert as subitems
            $a_as_subitem = true;
        }
        
        if ($a_id != "") {
            if ($a_first_child_drop_area) {
                $a_id .= "fc";
            }
            
            $this->drag_target[] = array("id" => $a_id, "group" => $a_group);
            $this->diss_menues[$a_id][$a_group][] = array("subitem" => $a_as_subitem, "text" => $a_diss_text);
        }
    }
    
    // Makes a node a drag content
    public function makeDragContent(
        string $a_id,
        string $a_group
    ) : void {
        if ($a_id != "") {
            $this->drag_content[] = array("id" => $a_id, "group" => $a_group);
        }
    }

    // Add a multi command (for selection of items)
    public function addMultiCommand(
        string $a_txt,
        string $a_cmd
    ) : void {
        $this->multi_commands[] = array("text" => $a_txt, "cmd" => $a_cmd);
    }

    public function addCommand(
        string $a_txt,
        string $a_cmd
    ) : void {
        $this->commands[] = array("text" => $a_txt, "cmd" => $a_cmd);
    }
    
    public function setHighlightedNodes(
        array $a_val
    ) : void {
        $this->highlighted_nodes = $a_val;
    }
    
    public function getHighlightedNodes() : array
    {
        return $this->highlighted_nodes;
    }

    public function setFocusId(string $a_val) : void
    {
        $this->focus_id = $a_val;
    }
    
    public function getFocusId() : string
    {
        return $this->focus_id;
    }

    public function setExpandVariable(string $a_val) : void
    {
        $this->expand_variable = $a_val;
    }

    public function getExpandVariable() : string
    {
        return $this->expand_variable;
    }
    
    public function setExpanded(array $a_val) : void
    {
        $this->expanded = $a_val;
    }
    
    public function getExpanded() : array
    {
        return $this->expanded;
    }

    protected function str($key) : string
    {
        return self::_str($key);
    }

    protected static function _str($key) : string
    {
        global $DIC;

        $w = $DIC->http()->wrapper();
        $t = $DIC->refinery()->kindlyTo()->string();

        if (!$w->post()->has($key) && !$w->query()->has($key)) {
            return "";
        }

        $val = (string) ($w->post()->retrieve($key, $t) ?? "");

        if ($val == "") {
            $val = (string) ($w->query()->retrieve($key, $t) ?? "");
        }
        return ilUtil::stripSlashes($val);
    }


    public function updateExpanded() : void
    {
        $ev = $this->getExpandVariable();
        $node_id = 0;
        if ($ev == "") {
            return;
        }
        
        // init empty session
        if (!is_array(ilSession::get($ev))) {
            ilSession::set($ev, array($this->getTree()->getRootId()));
        }

        if ($this->str("il_hform_expand") != "") {
            $node_id = $this->str("il_hform_expand");
        }
        if ($this->str($ev) != "") {
            $node_id = $this->str($ev);
        }
        
        // if positive => expand this node
        if ($node_id > 0 && !in_array($node_id, ilSession::get($ev))) {
            $nodes = ilSession::get($ev);
            $nodes[] = $node_id;
            ilSession::set($ev, $nodes);
        }
        // if negative => compress this node
        if ($node_id < 0) {
            $key = array_keys(ilSession::get($ev), -(int) $node_id);
            $nodes = ilSession::get($ev);
            unset($nodes[$ev][$key[0]]);
            ilSession::set($ev, $nodes);
        }
        $this->setExpanded(ilSession::get($ev));
    }

    public function setTypeWhiteList(array $a_val) : void
    {
        $this->white_list = $a_val;
    }
    
    public function getTypeWhiteList() : array
    {
        return $this->white_list;
    }

    /**
     * Get all childs of current node. Standard implementation uses
     * tree object.
     */
    public function getChilds(?int $a_node_id = null) : array
    {
        if ($a_node_id == null) {
            $a_node_id = $this->getCurrentTopNodeId();
        }
        
        $tree_childs = $this->getTree()->getChilds($a_node_id);
        $childs = array();
        foreach ($tree_childs as $tree_child) {
            if (!isset($this->white_list) || !is_array($this->white_list) || in_array($tree_child["type"], $this->white_list)) {
                $childs[] = array("node_id" => $tree_child["child"],
                    "title" => $tree_child["title"],
                    "type" => $tree_child["type"],
                    "depth" => $tree_child["depth"]
                    );
            }
        }
        
        return $childs;
    }
    
    public function getContent() : string
    {
        $lng = $this->lng;
        $single = false;
        $multi = false;

        if ($this->getExpandVariable() != "") {
            $this->updateExpanded();
        }
        
        $ttpl = new ilTemplate("tpl.hierarchy_form.html", true, true, "Services/Form");
        $ttpl->setVariable("TXT_SAVING", $lng->txt("saving"));
        $top_node_data = $this->getTree()->getNodeData($this->getCurrentTopNodeId());
        $top_node = array("node_id" => $top_node_data["child"] ?? 0,
                "title" => $top_node_data["title"] ?? "",
                "type" => $top_node_data["type"] ?? "");

        $childs = [];
        $nodes_html = $this->getLevelHTML($top_node, 0, $childs);


        // commands
        $secs = array("1", "2");
        foreach ($secs as $sec) {
            reset($this->commands);
            reset($this->multi_commands);
            if (count($this->multi_commands) > 0 || count($this->commands) > 0) {
                if (count($childs) > 0) {
                    $single = false;
                    foreach ($this->commands as $cmd) {
                        $ttpl->setCurrentBlock("cmd" . $sec);
                        $ttpl->setVariable("CMD", $cmd["cmd"]);
                        $ttpl->setVariable("CMD_TXT", $cmd["text"]);
                        $ttpl->parseCurrentBlock();
                        $single = true;
                    }
    
                    $multi = false;
                    foreach ($this->multi_commands as $cmd) {
                        $ttpl->setCurrentBlock("multi_cmd" . $sec);
                        $ttpl->setVariable("MULTI_CMD", $cmd["cmd"]);
                        $ttpl->setVariable("MULTI_CMD_TXT", $cmd["text"]);
                        $ttpl->parseCurrentBlock();
                        $multi = true;
                    }
                    if ($multi) {
                        $ttpl->setCurrentBlock("multi_cmds" . $sec);
                        $ttpl->setVariable("MCMD_ALT", $lng->txt("commands"));
                        if ($sec == "1") {
                            $ttpl->setVariable("MCMD_IMG", ilUtil::getImagePath("arrow_downright.svg"));
                        } else {
                            $ttpl->setVariable("MCMD_IMG", ilUtil::getImagePath("arrow_upright.svg"));
                        }
                        $ttpl->parseCurrentBlock();
                    }
                }
                
                if ($single || $multi) {
                    $ttpl->setCurrentBlock("commands" . $sec);
                    $ttpl->parseCurrentBlock();
                }
                $single = true;
            }
        }

        // explorer updater
        if ($this->exp_frame != "") {
            $ttpl->setCurrentBlock("updater");
            $ttpl->setVariable("UPDATER_FRAME", $this->exp_frame);
            $ttpl->setVariable("EXP_ID_UPDATER", $this->exp_id);
            $ttpl->setVariable("HREF_UPDATER", $this->exp_target_script);
            $ttpl->parseCurrentBlock();
        }

        // drag and drop initialisation
        foreach ($this->drag_target as $drag_target) {
            $ttpl->setCurrentBlock("dragtarget");
            $ttpl->setVariable("EL_ID", $drag_target["id"] ?? "");
            $ttpl->setVariable("GROUP", $drag_target["group"] ?? "");
            $ttpl->parseCurrentBlock();
        }
        foreach ($this->drag_content as $drag_content) {
            $ttpl->setCurrentBlock("dragcontent");
            $ttpl->setVariable("EL_ID", $drag_content["id"] ?? "");
            $ttpl->setVariable("GROUP", $drag_content["group"] ?? "");
            $ttpl->parseCurrentBlock();
        }
        
        // disambiguation menues and "insert as first child" flags
        if (is_array($this->diss_menues)) {
            foreach ($this->diss_menues as $node_id => $d_menu) {
                foreach ($d_menu as $group => $menu) {
                    if (count($menu) > 1) {
                        foreach ($menu as $menu_item) {
                            $ttpl->setCurrentBlock("dmenu_cmd");
                            $ttpl->setVariable("SUBITEM", (int) $menu_item["subitem"]);
                            $ttpl->setVariable("TXT_MENU_CMD", $menu_item["text"]);
                            $ttpl->parseCurrentBlock();
                        }
                        
                        $ttpl->setCurrentBlock("disambiguation_menu");
                        $ttpl->setVariable("DNODE_ID", $node_id);
                        $ttpl->setVariable("GRP", $group);
                        $ttpl->parseCurrentBlock();
                    } elseif (count($menu) == 1) {
                        // set first child flag
                        $ttpl->setCurrentBlock("as_subitem_flag");
                        $ttpl->setVariable("SI_NODE_ID", $node_id);
                        $ttpl->setVariable("SI_GRP", $group);
                        $ttpl->setVariable("SI_SI", (int) $menu[0]["subitem"]);
                        $ttpl->parseCurrentBlock();
                    }
                }
            }
        }
//        $this->diss_menues[$a_id][$a_group][] = array("type" => $a_type, "text" => $a_diss_text);


        if ($this->triggered_update_command != "") {
            $ttpl->setCurrentBlock("tr_update");
            $ttpl->setVariable("UPDATE_CMD", $this->triggered_update_command);
            $ttpl->parseCurrentBlock();
        }

        // disambiguation menues and "insert as first child" flags
        if (is_array($this->diss_menues)) {
            foreach ($this->diss_menues as $node_id => $d_menu) {
                foreach ($d_menu as $group => $menu) {
                    if (count($menu) > 1) {
                        foreach ($menu as $menu_item) {
                            $ttpl->setCurrentBlock("dmenu_cmd");
                            $ttpl->setVariable("SUBITEM", (int) $menu_item["subitem"]);
                            $ttpl->setVariable("TXT_MENU_CMD", $menu_item["text"]);
                            $ttpl->parseCurrentBlock();
                        }
                        
                        $ttpl->setCurrentBlock("disambiguation_menu");
                        $ttpl->setVariable("DNODE_ID", $node_id);
                        $ttpl->setVariable("GRP", $group);
                        $ttpl->parseCurrentBlock();
                    } elseif (count($menu) == 1) {
                        // set first child flag
                        $ttpl->setCurrentBlock("as_subitem_flag");
                        $ttpl->setVariable("SI_NODE_ID", $node_id);
                        $ttpl->setVariable("SI_GRP", $group);
                        $ttpl->setVariable("SI_SI", (int) $menu[0]["subitem"]);
                        $ttpl->parseCurrentBlock();
                    }
                }
            }
        }
        //$this->diss_menues[$a_id][$a_group][] = array("type" => $a_type, "text" => $a_diss_text);
        
        // nodes
        $ttpl->setVariable("NODES", $nodes_html);
        
        // title
        //echo "<br>".htmlentities($this->getTitle())." --- ".htmlentities(ilUtil::prepareFormOutput($this->getTitle()));
        $ttpl->setVariable("TITLE", $this->getTitle());
        
        
        return $ttpl->get();
    }

    public function getLegend() : string
    {
        $lng = $this->lng;

        $ttpl = new ilTemplate("tpl.hierarchy_form_legend.html", true, true, "Services/Form");
        if ($this->getDragIcon() != "") {
            $ttpl->setCurrentBlock("help_drag");
            $ttpl->setVariable("IMG_DRAG", $this->getDragIcon());
            $ttpl->setVariable(
                "DRAG_ARROW",
                ilGlyphGUI::get(ilGlyphGUI::DRAG)
            );
            $ttpl->setVariable(
                "TXT_DRAG",
                $lng->txt("form_hierarchy_drag_drop_help")
            );
            $ttpl->setVariable("PLUS", ilGlyphGUI::get(ilGlyphGUI::ADD));
            $ttpl->parseCurrentBlock();
        }

        // additional help items
        foreach ($this->getHelpItems() as $help) {
            if ($help["image"] != "") {
                $ttpl->setCurrentBlock("help_img");
                $ttpl->setVariable("IMG_HELP", $help["image"]);
                $ttpl->parseCurrentBlock();
            }
            $ttpl->setCurrentBlock("help_item");
            $ttpl->setVariable("TXT_HELP", $help["text"]);
            $ttpl->parseCurrentBlock();
        }

        $ttpl->setVariable(
            "TXT_ADD_EL",
            $lng->txt("form_hierarchy_add_elements")
        );
        $ttpl->setVariable("PLUS2", ilGlyphGUI::get(ilGlyphGUI::ADD));

        return $ttpl->get();
    }

    public function getLevelHTML(
        array $a_par_node,
        int $a_depth,
        array &$a_childs
    ) : string {
        $lng = $this->lng;
        
        if ($this->getMaxDepth() > -1 && $this->getMaxDepth() < $a_depth) {
            return "";
        }

        $childs = $this->getChilds((int) $a_par_node["node_id"]);
        $a_childs = $childs;
        $ttpl = new ilTemplate("tpl.hierarchy_form_nodes.html", true, true, "Services/Form");

        // prepended drop area
        if ($this->nodeAllowsChilds($a_par_node) && (count($childs) > 0 || $a_depth == 0)) {
            $ttpl->setCurrentBlock("drop_area");
            $ttpl->setVariable("DNODE_ID", $a_par_node["node_id"] . "fc");		// fc means "first child"
            $ttpl->setVariable("IMG_BLANK", ilUtil::getImagePath("spacer.png"));
            if (count($childs) == 0) {
                $ttpl->setVariable("NO_CONTENT_CLASS", "ilCOPGNoPageContent");
                $ttpl->setVariable("NO_CONTENT_TXT", " &nbsp;" . $lng->txt("form_hier_click_to_add"));
            }
            $ttpl->parseCurrentBlock();
    
            $this->manageDragAndDrop($a_par_node, $a_depth, true, null, $childs);
            $menu_items = $this->getMenuItems($a_par_node, $a_depth, true, null, $childs);
            //var_dump($menu_items);
            if (count($menu_items) > 0) {
                // determine maximum of multi add numbers
                $max = 1;
                foreach ($menu_items as $menu_item) {
                    if ($menu_item["multi"] > $max) {
                        $max = $menu_item["multi"];
                    }
                }
                
                reset($menu_items);
                $mcnt = 1;
                foreach ($menu_items as $menu_item) {
                    if ($menu_item["multi"] > 1) {
                        for ($i = 1; $i <= $menu_item["multi"]; $i++) {
                            $ttpl->setCurrentBlock("multi_add");
                            $ttpl->setVariable("MA_NUM", $i);
                            $ttpl->setVariable("MENU_CMD", $menu_item["cmd"]);
                            $ttpl->setVariable("FC", "1");
                            $ttpl->setVariable("CMD_NODE", $a_par_node["node_id"]);
                            $ttpl->setVariable("MCNT", $mcnt . "fc");
                            $ttpl->parseCurrentBlock();
                        }
                    }
                    
                    // buffer td for lower multis
                    if ($max > $menu_item["multi"]) {
                        $ttpl->setCurrentBlock("multi_buffer");
                        $ttpl->setVariable("BUF_SPAN", $max - $menu_item["multi"]);
                        $ttpl->parseCurrentBlock();
                    }
                    $ttpl->setCurrentBlock("menu_cmd");
                    $ttpl->setVariable("TXT_MENU_CMD", $menu_item["text"]);
                    $ttpl->setVariable("MENU_CMD", $menu_item["cmd"]);
                    $ttpl->setVariable("CMD_NODE", $a_par_node["node_id"]);
                    $ttpl->setVariable("FC", "1");
                    $ttpl->setVariable("MCNT", $mcnt . "fc");
                    $ttpl->parseCurrentBlock();
                    $mcnt++;
                }
                $ttpl->setCurrentBlock("drop_area_menu");
                $ttpl->setVariable("MNODE_ID", $a_par_node["node_id"] . "fc");
                $ttpl->parseCurrentBlock();
    
                $ttpl->setCurrentBlock("element");
                $ttpl->parseCurrentBlock();
            }
        }
        
        // insert childs
        if (count($childs) > 0) {
            for ($i = 0, $iMax = count($childs); $i < $iMax; $i++) {
                $next_sibling = ($i < (count($childs) - 1))
                    ? $childs[$i + 1]
                    : null;

                $this->renderChild($ttpl, $childs[$i], $a_depth, $next_sibling);
            }
        }

        $html = $ttpl->get();
        unset($ttpl);
        
        return $html;
    }
    
    /**
     * Render a single child (including grandchilds)
     */
    public function renderChild(
        ilTemplate $a_tpl,
        array $a_child,
        int $a_depth,
        ?array $next_sibling = null
    ) {
        $ilCtrl = $this->ctrl;
        
        // image
        $a_tpl->setCurrentBlock("img");
        $a_tpl->setVariable("IMGPATH", $this->getChildIcon($a_child));
        $a_tpl->setVariable("IMGALT", $this->getChildIconAlt($a_child));
        $a_tpl->setVariable("IMG_NODE", $a_child["node_id"]);
        $a_tpl->setVariable("NODE_ID", $a_child["node_id"]);
        $a_tpl->setVariable("TYPE", $a_child["type"]);
        $a_tpl->parseCurrentBlock();
        
        // checkbox
        $a_tpl->setCurrentBlock("cbox");
        $a_tpl->setVariable("CNODE_ID", $a_child["node_id"]);
        $a_tpl->setVariable("CBOX_NAME", $this->getCheckboxName());
        $a_tpl->parseCurrentBlock();
        
        // node info
        if (($info = $this->getChildInfo($a_child)) != "") {
            $a_tpl->setCurrentBlock("node_info");
            $a_tpl->setVariable("NODE_INFO", $info);
            $a_tpl->parseCurrentBlock();
        }
        
        // commands of child node
        $child_commands = $this->getChildCommands($a_child);
        if (is_array($child_commands)) {
            foreach ($child_commands as $command) {
                $a_tpl->setCurrentBlock("node_cmd");
                $a_tpl->setVariable("HREF_NODE_CMD", $command["link"]);
                $a_tpl->setVariable("TXT_NODE_CMD", $command["text"]);
                $a_tpl->parseCurrentBlock();
            }
        }
        
        // title
        $a_tpl->setCurrentBlock("text");
        $hl = $this->getHighlightedNodes();
        if (is_array($hl) && in_array($a_child["node_id"], $hl)) {
            $a_tpl->setVariable("CLASS", ' class="ilHFormHighlighted" ');
        }
        $a_tpl->setVariable("VAL_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($this->getChildTitle($a_child)));
        $a_tpl->setVariable("TNODE_ID", $a_child["node_id"]);
        $a_tpl->parseCurrentBlock();
        $grandchilds = [];
        $grandchilds_html = $this->getLevelHTML($a_child, $a_depth + 1, $grandchilds);
        
        // focus
        if ($this->getFocusId() == $a_child["node_id"]) {
            $a_tpl->setCurrentBlock("focus");
            $a_tpl->setVariable("FNODE_ID", $a_child["node_id"]);
            $a_tpl->parseCurrentBlock();
        }
        
        // expander
        if ($this->getExpandVariable() != "") {
            $a_tpl->setCurrentBlock("expand_icon");
            if (!is_null($grandchilds) && count($grandchilds) > 0) {
                if (!in_array($a_child["node_id"], $this->getExpanded())) {
                    $ilCtrl->setParameter($this->getParentObject(), $this->getExpandVariable(), $a_child["node_id"]);
                    $a_tpl->setVariable("IMG_EXPAND", ilUtil::getImagePath("browser/plus.png"));
                    $a_tpl->setVariable("HREF_NAME", "n" . $a_child["node_id"]);
                    $a_tpl->setVariable(
                        "HREF_EXPAND",
                        $ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCommand(), "n" . $a_child["node_id"])
                    );
                    $grandchilds_html = "";
                } else {
                    $ilCtrl->setParameter($this->getParentObject(), $this->getExpandVariable(), -$a_child["node_id"]);
                    $a_tpl->setVariable("IMG_EXPAND", ilUtil::getImagePath("browser/minus.png"));
                    $a_tpl->setVariable("HREF_NAME", "n" . $a_child["node_id"]);
                    $a_tpl->setVariable(
                        "HREF_EXPAND",
                        $ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCommand(), "n" . $a_child["node_id"])
                    );
                }
                $ilCtrl->setParameter($this->getParentObject(), $this->getExpandVariable(), "");
            } else {
                $a_tpl->setVariable("IMG_EXPAND", ilUtil::getImagePath("spacer.png"));
            }
            $a_tpl->parseCurrentBlock();
        }
        
        // childs
        $a_tpl->setCurrentBlock("list_item");
        $a_tpl->setVariable("CHILDS", $grandchilds_html);
        $a_tpl->parseCurrentBlock();
        
        $a_tpl->setCurrentBlock("element");
        $a_tpl->parseCurrentBlock();
        
        // drop area after child
        $a_tpl->setCurrentBlock("drop_area");
        $a_tpl->setVariable("DNODE_ID", $a_child["node_id"]);
        $a_tpl->setVariable("IMG_BLANK", ilUtil::getImagePath("spacer.png"));
        $a_tpl->parseCurrentBlock();

        // manage drag and drop areas
        $this->manageDragAndDrop($a_child, $a_depth, false, $next_sibling, $grandchilds);
        
        // drop area menu
        $menu_items = $this->getMenuItems($a_child, $a_depth, false, $next_sibling, $grandchilds);
        if (count($menu_items) > 0) {
            // determine maximum of multi add numbers
            $max = 1;
            foreach ($menu_items as $menu_item) {
                if ($menu_item["multi"] > $max) {
                    $max = $menu_item["multi"];
                }
            }
            
            reset($menu_items);
            $mcnt = 1;
            foreach ($menu_items as $menu_item) {
                if ($menu_item["multi"] > 1) {
                    for ($i = 1; $i <= $menu_item["multi"]; $i++) {
                        $a_tpl->setCurrentBlock("multi_add");
                        $a_tpl->setVariable("MA_NUM", $i);
                        $a_tpl->setVariable("MENU_CMD", $menu_item["cmd"]);
                        if ($menu_item["as_subitem"] ?? false) {
                            $a_tpl->setVariable("FC", "1");
                            $a_tpl->setVariable("MCNT", $mcnt . "fc");
                        } else {
                            $a_tpl->setVariable("FC", "0");
                            $a_tpl->setVariable("MCNT", $mcnt);
                        }
                        $a_tpl->setVariable("CMD_NODE", $a_child["node_id"]);
                        $a_tpl->parseCurrentBlock();
                    }
                }
                
                // buffer td for lower multis
                if ($max > $menu_item["multi"]) {
                    $a_tpl->setCurrentBlock("multi_buffer");
                    $a_tpl->setVariable("BUF_SPAN", $max - $menu_item["multi"]);
                    $a_tpl->parseCurrentBlock();
                }
                
                $a_tpl->setCurrentBlock("menu_cmd");
                $a_tpl->setVariable("TXT_MENU_CMD", $menu_item["text"]);
                $a_tpl->setVariable("MENU_CMD", $menu_item["cmd"]);
                if ($menu_item["as_subitem"] ?? false) {
                    $a_tpl->setVariable("FC", "1");
                    $a_tpl->setVariable("MCNT", $mcnt . "fc");
                } else {
                    $a_tpl->setVariable("FC", "0");
                    $a_tpl->setVariable("MCNT", $mcnt);
                }
                $a_tpl->setVariable("CMD_NODE", $a_child["node_id"]);
                $a_tpl->parseCurrentBlock();
                $mcnt++;
            }
            $a_tpl->setCurrentBlock("drop_area_menu");
            $a_tpl->setVariable("MNODE_ID", $a_child["node_id"]);
            $a_tpl->parseCurrentBlock();
        }
        
        $a_tpl->setCurrentBlock("element");
        $a_tpl->parseCurrentBlock();
    }
    
    public function getChildIcon(array $a_item) : string
    {
        return ilUtil::getImagePath("icon_" . $a_item["type"] . ".svg");
    }
    
    public function getChildIconAlt(array $a_item) : string
    {
        $lng = $this->lng;
        
        return $lng->txt($a_item["type"]);
    }

    public function getChildCommands(array $a_item) : array
    {
        return [];
    }

    public function getChildTitle(array $a_child) : string
    {
        return $a_child["title"];
    }
    
    public function getChildInfo(array $a_child) : string
    {
        return "";
    }
    
    /**
     * Get menu items for drop area of node.
     * This function will be most likely overwritten by sub class
     * @param	array $a_node            node array ("title", "node_id", "type")
     * @param bool     $a_first_child     if false, the menu of the drop area
     *									right after the node (same level) is set
     *									if true, the menu of the drop area before
     *									the first child (if nodes are allowed)
     *									of the node is set
     */
    public function getMenuItems(
        array $a_node,
        int $a_depth,
        bool $a_first_child = false,
        ?array $a_next_sibling = null,
        ?array $a_childs = null
    ) : array {
        return array();
    }
    
    /**
     * Checks, whether current nodes allows childs at all.
     * Should be overwritten.
     */
    public function nodeAllowsChilds(array $a_node) : bool
    {
        return true;
    }
    
    /**
    * Makes nodes drag and drop content and targets.
    * Must be overwritten to support drag and drop.
    * @param	array $a_node node array
    */
    public function manageDragAndDrop(
        array $a_node,
        int $a_depth,
        bool $a_first_child = false,
        ?array $a_next_sibling = null,
        ?array $a_childs = null
    ) : void {
        //$this->makeDragTarget($a_node["id"], $a_group);
        //$this->makeDragTarget($a_node["id"], $a_group);
    }

    /**
     * Get multi number of _POST input
     */
    public static function getPostMulti() : int
    {
        return max(1, (int) self::_str("il_hform_multi"));
    }
    
    /**
     * Get node ID of _POST input
     */
    public static function getPostNodeId() : string
    {
        return self::_str("il_hform_node");
    }

    /**
     * Should node be inserted as first child of target node (true) or as successor (false)
     */
    public static function getPostFirstChild() : bool
    {
        return ((int) self::_str("il_hform_fc") == 1);
    }

    public function getHTML() : string
    {
        return parent::getHTML() . $this->getLegend();
    }

    public static function getPostFields() : array
    {
        return array(
            "il_hform_node" => self::_str("il_hform_node"),
            "il_hform_fc" => self::_str("il_hform_fc"),
            "il_hform_as_subitem" => self::_str("il_hform_as_subitem"),
            "il_hform_multi" => self::_str("il_hform_multi"),
            "il_hform_source_id" => self::_str("il_hform_source_id"),
            "il_hform_target_id" => self::_str("il_hform_target_id")
        );
    }
}
