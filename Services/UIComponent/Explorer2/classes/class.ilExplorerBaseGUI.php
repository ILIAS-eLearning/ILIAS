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

/**
 * The class is supposed to work on a hierarchie of nodes that are identified
 * by IDs. Whether nodes are represented by associative arrays or objects
 * is not defined by this abstract class.
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @deprecated 11
 */
abstract class ilExplorerBaseGUI
{
    protected ilLogger $log;
    protected ilCtrl $ctrl;
    protected ?ilGlobalTemplateInterface $tpl;

    protected static string $js_tree_path = "./node_modules/jstree/dist/jstree.js";
    protected static string $js_tree_path_css = "./node_modules/jstree/dist/themes/default/style.min.css";

    protected static string $js_expl_path = "./Services/UIComponent/Explorer2/js/Explorer2.js";
    protected bool $skip_root_node = false;
    protected bool $ajax = false;
    protected array $custom_open_nodes = array();
    protected array $selected_nodes = array();
    protected string $select_postvar = "";
    protected bool $offline_mode = false;
    protected array $sec_highl_nodes = array();
    protected bool $enable_dnd = false;
    protected string $search_term = "";
    protected array $open_nodes = [];
    protected ilSessionIStorage $store;
    protected bool $select_multi = false;

    /**
     * @var string|object|array
     */
    protected $parent_obj;
    protected int $child_limit = 0;
    private bool $nodeOnclickEnabled;
    protected string $parent_cmd = '';

    protected string $requested_exp_cmd = "";
    protected string $requested_exp_cont = "";
    protected string $requested_searchterm = "";
    protected string $requested_node_id = "";
    protected string $id;

    public function __construct(
        string $a_expl_id,
        $a_parent_obj,
        string $a_parent_cmd
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;
        $this->log = $DIC["ilLog"];
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->id = $a_expl_id;
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;
        // get open nodes
        $this->store = new ilSessionIStorage("expl2");
        $open_nodes = $this->store->get("on_" . $this->id);
        $this->open_nodes = unserialize($open_nodes, ['allowed_classes' => false]) ?: [];
        if (!is_array($this->open_nodes)) {
            $this->open_nodes = array();
        }

        $params = $DIC->http()->request()->getQueryParams();
        $this->requested_node_id = ($params["node_id"] ?? "");
        $this->requested_exp_cmd = ($params["exp_cmd"] ?? "");
        $this->requested_exp_cont = ($params["exp_cont"] ?? "");
        $this->requested_searchterm = ($params["searchterm"] ?? "");

        $this->nodeOnclickEnabled = true;
        ilYuiUtil::initConnection();
    }

    public function setChildLimit(int $a_val): void
    {
        $this->child_limit = $a_val;
    }

    public function getChildLimit(): int
    {
        return $this->child_limit;
    }

    public function setSearchTerm(string $a_val): void
    {
        $this->search_term = $a_val;
    }

    public function getSearchTerm(): string
    {
        return $this->search_term;
    }

    public function setMainTemplate(ilGlobalTemplateInterface $a_main_tpl = null): void
    {
        $this->tpl = $a_main_tpl;
    }

    public static function getLocalExplorerJsPath(): string
    {
        return self::$js_expl_path;
    }

    public static function getLocalJsTreeJsPath(): string
    {
        return self::$js_tree_path;
    }

    public static function getLocalJsTreeCssPath(): string
    {
        return self::$js_tree_path_css;
    }

    public static function createHTMLExportDirs(string $a_target_dir): void
    {
        ilFileUtils::makeDirParents($a_target_dir . "/Services/UIComponent/Explorer2/lib/jstree-v.pre1.0");
        ilFileUtils::makeDirParents($a_target_dir . "/Services/UIComponent/Explorer2/js");
    }


    //
    // Abstract functions that need to be overwritten in derived classes
    //

    /**
     * Get root node.
     *
     * Please note that the class does not make any requirements how
     * nodes are represented (array or object)
     *
     * @return object|array|null
     */
    abstract public function getRootNode();

    /**
     * Get children of node
     * @param string $a_parent_node_id
     * @return array
     */
    abstract public function getChildsOfNode($a_parent_node_id): array;

    /**
     * Get content of a node
     * @param object|array $a_node node array or object
     * @return string content of the node
     */
    abstract public function getNodeContent($a_node): string;

    /**
     * Get id of a node
     * @param object|array $a_node node array or object
     * @return string id of node
     */
    abstract public function getNodeId($a_node);


    //
    // Functions with standard implementations that may be overwritten
    //

    /**
     * Get href for node
     * @param object|array $a_node
     * @return string href attribute
     */
    public function getNodeHref($a_node): string
    {
        return "#";
    }

    /**
     * Node has children
     * Please note that this standard method may not
     * be optimal depending on what a derived class does in isNodeVisible.
     * @param object|array $a_node
     * @return bool
     */
    public function nodeHasVisibleChilds($a_node): bool
    {
        $childs = $this->getChildsOfNode($this->getNodeId($a_node));

        foreach ($childs as $child) {
            if ($this->isNodeVisible($child)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Sort childs
     * @param array $a_childs array of child nodes
     * @param string $a_parent_node_id parent node
     * @return array array of childs nodes
     */
    public function sortChilds(array $a_childs, $a_parent_node_id): array
    {
        return $a_childs;
    }

    /**
     * Get node icon path
     * @param object|array $a_node
     * @return string image file path
     */
    public function getNodeIcon($a_node): string
    {
        return "";
    }

    /**
     * Get node icon alt attribute
     * @param object|array $a_node
     * @return string image alt attribute
     */
    public function getNodeIconAlt($a_node): string
    {
        return "";
    }

    /**
     * Get node target (frame) attribute
     * @param object|array $a_node node
     * @return string target
     */
    public function getNodeTarget($a_node): string
    {
        return "";
    }

    /**
     * Get node onclick attribute
     * @param object|array $a_node node
     * @return string onclick value
     */
    public function getNodeOnClick($a_node): string
    {
        if ($this->select_postvar !== "") {
            return $this->getSelectOnClick($a_node);
        }
        return "";
    }

    /**
     * Is node visible?
     * @param object|array $a_node node
     * @return bool node visible true/false
     */
    public function isNodeVisible($a_node): bool
    {
        return true;
    }

    /**
     * Is node highlighted?
     * @param object|array $a_node node
     * @return bool node highlighted true/false
     */
    public function isNodeHighlighted($a_node): bool
    {
        return false;
    }

    /**
     * Is node clickable?
     * @param object|array $a_node node
     * @return bool node clickable true/false
     */
    public function isNodeClickable($a_node): bool
    {
        return true;
    }

    /**
     * Is node selectable?
     * @param object|array $a_node node
     * @return bool node selectable true/false
     */
    protected function isNodeSelectable($a_node): bool
    {
        return true;
    }


    //
    // Basic configuration / setter/getter
    //

    /**
     * Get id of explorer element
     * @return string id
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function setSkipRootNode(bool $a_val): void
    {
        $this->skip_root_node = $a_val;
    }

    public function getSkipRootNode(): bool
    {
        return $this->skip_root_node;
    }

    public function setAjax(bool $a_val): void
    {
        $this->ajax = $a_val;
    }

    public function getAjax(): bool
    {
        return $this->ajax;
    }

    /**
     * Set secondary (background) highlighted nodes
     */
    public function setSecondaryHighlightedNodes(array $a_val): void
    {
        $this->sec_highl_nodes = $a_val;
    }

    /**
     * Get secondary (background) highlighted nodes
     */
    public function getSecondaryHighlightedNodes(): array
    {
        return $this->sec_highl_nodes;
    }

    /**
     * Set node to be opened (additional custom opened node, not standard expand behaviour)
     * @param string $a_id
     */
    public function setNodeOpen($a_id): void
    {
        if (!in_array($a_id, $this->custom_open_nodes)) {
            $this->custom_open_nodes[] = $a_id;
        }
    }

    /**
     * Get onclick attribute for node toggling
     * @param object|array $a_node
     */
    final protected function getNodeToggleOnClick($a_node): string
    {
        return "$('#" . $this->getContainerId() . "').jstree('toggle_node' , '#" .
            $this->getDomNodeIdForNodeId($this->getNodeId($a_node)) . "'); return false;";
    }

    /**
     * Get onclick attribute for selecting radio/checkbox
     * @param object|array $a_node
     */
    final protected function getSelectOnClick($a_node): string
    {
        $dn_id = $this->getDomNodeIdForNodeId($this->getNodeId($a_node));
        $oc = "il.Explorer2.selectOnClick(event, '" . $dn_id . "'); return false;";
        return $oc;
    }

    /**
     * Set select mode (to deactivate, pass an empty string as postvar)
     * @param string  $a_postvar variable used for post, a "[]" is added automatically
     * @param bool $a_multi   multi select (checkboxes) or not (radio)
     */
    public function setSelectMode(string $a_postvar, bool $a_multi = false): void
    {
        $this->select_postvar = $a_postvar;
        $this->select_multi = $a_multi;
    }

    /**
     * Set node to be opened (additional custom opened node, not standard expand behaviour)
     * @param string $a_id
     */
    public function setNodeSelected($a_id): void
    {
        if (!in_array($a_id, $this->selected_nodes)) {
            $this->selected_nodes[] = $a_id;
        }
    }

    public function setOfflineMode(bool $a_val): void
    {
        $this->offline_mode = $a_val;
    }

    public function getOfflineMode(): bool
    {
        return $this->offline_mode;
    }

    //
    // Standard functions that usually are not overwritten / internal use
    //

    /**
     * Handle explorer internal command.
     */
    public function handleCommand(): bool
    {
        if ($this->requested_exp_cmd !== "" &&
            $this->requested_exp_cont === $this->getContainerId()) {
            $cmd = $this->requested_exp_cmd;
            if (in_array($cmd, array("openNode", "closeNode", "getNodeAsync"))) {
                $this->$cmd();
            }

            return true;
        }
        return false;
    }

    public function getContainerId(): string
    {
        return "il_expl2_jstree_cont_" . $this->getId();
    }

    /**
     * Open node
     */
    public function openNode(): void
    {
        $id = $this->getNodeIdForDomNodeId($this->requested_node_id);
        if (!in_array($id, $this->open_nodes)) {
            $this->open_nodes[] = $id;
        }
        $this->store->set("on_" . $this->id, serialize($this->open_nodes));
        exit;
    }

    /**
     * Close node
     */
    public function closeNode(): void
    {
        $id = $this->getNodeIdForDomNodeId($this->requested_node_id);
        if (in_array($id, $this->open_nodes)) {
            $k = array_search($id, $this->open_nodes);
            unset($this->open_nodes[$k]);
        }
        $this->store->set("on_" . $this->id, serialize($this->open_nodes));
        exit;
    }

    /**
     * Get node asynchronously
     */
    public function getNodeAsync(): string
    {
        $this->beforeRendering();

        $etpl = new ilTemplate("tpl.explorer2.html", true, true, "Services/UIComponent/Explorer2");

        $root = $this->getNodeId($this->getRootNode());
        if (!in_array($root, $this->open_nodes)) {
            $this->open_nodes[] = $root;
        }

        if ($this->requested_node_id !== "") {
            $id = $this->getNodeIdForDomNodeId($this->requested_node_id);
            $this->setSearchTerm(ilUtil::stripSlashes($this->requested_searchterm));
            $this->renderChilds($id, $etpl);
        } else {
            $this->getNodeId($this->getRootNode());
            $this->renderNode($this->getRootNode(), $etpl);
        }
        echo $etpl->get("tag");
        exit;
    }

    /**
     * Before rendering
     */
    public function beforeRendering(): void
    {
    }

    /**
     * Get all open nodes
     * @param string $node_id
     */
    protected function isNodeOpen($node_id): bool
    {
        return ($this->getNodeId($this->getRootNode()) == $node_id
            || in_array($node_id, $this->open_nodes)
            || in_array($node_id, $this->custom_open_nodes));
    }


    /**
     * Get on load code
     */
    public function getOnLoadCode(): string
    {
        $ilCtrl = $this->ctrl;

        $container_id = $this->getContainerId();
        $container_outer_id = "il_expl2_jstree_cont_out_" . $this->getId();

        // collect open nodes
        $open_nodes = array($this->getDomNodeIdForNodeId($this->getNodeId($this->getRootNode())));
        foreach ($this->open_nodes as $nid) {
            $open_nodes[] = $this->getDomNodeIdForNodeId($nid);
        }
        foreach ($this->custom_open_nodes as $nid) {
            $dnode = $this->getDomNodeIdForNodeId($nid);
            if (!in_array($dnode, $open_nodes)) {
                $open_nodes[] = $dnode;
            }
        }
        // ilias config options
        $url = "";
        if (!$this->getOfflineMode()) {
            if (is_object($this->parent_obj)) {
                $url = $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd, "", true);
            } else {
                $url = $ilCtrl->getLinkTargetByClass($this->parent_obj, $this->parent_cmd, "", true);
            }
        }

        // secondary highlighted nodes
        $shn = array();
        foreach ($this->sec_highl_nodes as $sh) {
            $shn[] = $this->getDomNodeIdForNodeId($sh);
        }
        $config = array(
            "container_id" => $container_id,
            "container_outer_id" => $container_outer_id,
            "url" => $url,
            "second_hnodes" => $shn,
            "ajax" => $this->getAjax(),
        );


        // jstree config options
        $js_tree_config = array(
            "core" => array(
                "animation" => 0,
                "initially_open" => $open_nodes,
                "open_parents" => false,
                "strings" => array("loading" => "Loading ...", "new_node" => "New node"),
                "themes" => array("dots" => false, "icons" => false, "theme" => "")
            ),
            "plugins" => $this->getJSTreePlugins(),
            "html_data" => array()
        );
        return (
            'il.Explorer2.init(' .
            json_encode($config, JSON_THROW_ON_ERROR) . ', ' .
            json_encode($js_tree_config, JSON_THROW_ON_ERROR) . ');'
        );
    }

    protected function getJSTreePlugins(): array
    {
        $plugins = array("html_data", "themes", "json_data");
        if ($this->isEnableDnd()) {
            $plugins[] = "dnd";
        }
        return $plugins;
    }


    // Init JS/CSS
    public static function init(ilGlobalTemplateInterface $a_main_tpl = null): void
    {
        global $DIC;

        $tpl = $a_main_tpl ?? $DIC["tpl"];

        iljQueryUtil::initjQuery($tpl);

        $tpl->addJavaScript(self::getLocalExplorerJsPath());
        $tpl->addJavaScript(self::getLocalJsTreeJsPath());
        $tpl->addCss(self::getLocalJsTreeCssPath());
    }


    public function getHTML(): string
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;

        $root = $this->getNodeId($this->getRootNode());
        if (!in_array($root, $this->open_nodes)) {
            $this->open_nodes[] = $root;
        }

        $this->beforeRendering();

        self::init($tpl);
        $container_id = $this->getContainerId();
        $container_outer_id = "il_expl2_jstree_cont_out_" . $this->getId();

        if (!$ilCtrl->isAsynch()) {
            $tpl->addOnLoadCode($this->getOnLoadCode());
        }

        $etpl = new ilTemplate("tpl.explorer2.html", true, true, "Services/UIComponent/Explorer2");

        if (!$this->ajax) {
            // render childs
            $root_node = $this->getRootNode();

            if (!$this->getSkipRootNode() &&
                $this->isNodeVisible($this->getRootNode())) {
                $this->listStart($etpl);
                $this->renderNode($this->getRootNode(), $etpl);
                $this->listEnd($etpl);
            } else {
                $childs = $this->getChildsOfNode($this->getNodeId($root_node));
                $childs = $this->sortChilds($childs, $this->getNodeId($root_node));
                $any = false;
                foreach ($childs as $child_node) {
                    if ($this->isNodeVisible($child_node)) {
                        if (!$any) {
                            $this->listStart($etpl);
                            $any = true;
                        }
                        $this->renderNode($child_node, $etpl);
                    }
                }
                if ($any) {
                    $this->listEnd($etpl);
                }
            }
        }

        $etpl->setVariable("CONTAINER_ID", $container_id);
        $etpl->setVariable("CONTAINER_OUTER_ID", $container_outer_id);

        $add = "";
        if ($ilCtrl->isAsynch()) {
            $add = "<script>" . $this->getOnLoadCode() . "</script>";
        }

        $content = $etpl->get();
        //echo $content.$add; exit;
        return $content . $add;
    }

    /**
     * Render node
     * @param object|array $a_node
     */
    public function renderNode($a_node, ilTemplate $tpl): void
    {
        $skip = ($this->getSkipRootNode()
            && $this->getNodeId($this->getRootNode()) == $this->getNodeId($a_node));
        if (!$skip) {
            $this->listItemStart($tpl, $a_node);

            // select mode?
            if ($this->select_postvar !== "" && $this->isNodeSelectable($a_node)) {
                if ($this->select_multi) {
                    $tpl->setCurrentBlock("cb");
                    if (in_array($this->getNodeId($a_node), $this->selected_nodes)) {
                        $tpl->setVariable("CHECKED", 'checked="checked"');
                    }
                    $tpl->setVariable("CB_VAL", $this->getNodeId($a_node));
                    $tpl->setVariable("CB_NAME", $this->select_postvar . "[]");
                } else {
                    $tpl->setCurrentBlock("rd");
                    if (in_array($this->getNodeId($a_node), $this->selected_nodes)) {
                        $tpl->setVariable("SELECTED", 'checked="checked"');
                    }
                    $tpl->setVariable("RD_VAL", $this->getNodeId($a_node));
                    $tpl->setVariable("RD_NAME", $this->select_postvar);
                }
                $tpl->parseCurrentBlock();
            }


            if ($this->isNodeHighlighted($a_node)) {
                $tpl->touchBlock("hl");
            }
            $tpl->setCurrentBlock("content");
            if ($this->getNodeIcon($a_node) !== "") {
                $tpl->setVariable("ICON", ilUtil::img($this->getNodeIcon($a_node), $this->getNodeIconAlt($a_node)) . " ");
            }
            $tpl->setVariable("CONTENT", $this->getNodeContent($a_node));
            if ($this->isNodeClickable($a_node)) {
                $tpl->setVariable("HREF", $this->getNodeHref($a_node));
            }
            $target = $this->getNodeTarget($a_node);
            if ($target !== "") {
                $targetRelatedParams = array(
                    'target="' . $target . '"'
                );

                if ('_blank' === $target) {
                    $targetRelatedParams[] = 'rel="noopener"';
                }

                $tpl->setVariable('TARGET', implode(' ', $targetRelatedParams));
            }
            if (!$this->isNodeOnclickEnabled() || !$this->isNodeClickable($a_node)) {
                $tpl->setVariable("ONCLICK", 'onclick="return false;"');
                $tpl->setVariable("A_CLASS", 'class="disabled"');
            } else {
                $onclick = $this->getNodeOnClick($a_node);
                if ($onclick !== "") {
                    $tpl->setVariable("ONCLICK", 'onclick="' . $onclick . '"');
                }
            }
            $tpl->parseCurrentBlock();

            $tpl->touchBlock("tag");
        }

        if (!$this->getAjax() || in_array($this->getNodeId($a_node), $this->open_nodes)
            || in_array($this->getNodeId($a_node), $this->custom_open_nodes)) {
            $this->renderChilds($this->getNodeId($a_node), $tpl);
        }

        if (!$skip) {
            $this->listItemEnd($tpl);
        }
    }

    /**
     * Render childs
     * @param string $a_node_id
     */
    final public function renderChilds($a_node_id, ilTemplate $tpl): void
    {
        $childs = $this->getChildsOfNode($a_node_id);
        $childs = $this->sortChilds($childs, $a_node_id);

        if (count($childs) > 0 || ($this->getSearchTerm() !== "" && $this->requested_node_id === $this->getDomNodeIdForNodeId($a_node_id))) {
            // collect visible childs

            $visible_childs = [];
            $cnt_child = 0;

            foreach ($childs as $child) {
                $cnt_child++;
                if ($this->getChildLimit() > 0 && $this->getChildLimit() < $cnt_child) {
                    continue;
                }

                if ($this->isNodeVisible($child)) {
                    $visible_childs[] = $child;
                }
            }

            // search field, if too many childs
            $any = false;
            if (($this->getChildLimit() > 0 && $this->getChildLimit() < $cnt_child) || $this->getSearchTerm() !== "") {
                if (!$any) {
                    $this->listStart($tpl);
                    $any = true;
                }
                $tpl->setCurrentBlock("list_search");
                $tpl->setVariable("SEARCH_CONTAINER_ID", $a_node_id);
                if ($this->requested_node_id === $this->getDomNodeIdForNodeId($a_node_id)) {
                    $tpl->setVariable("SEARCH_VAL", $this->getSearchTerm());
                }
                $tpl->parseCurrentBlock();
                $tpl->touchBlock("tag");
            }

            // render visible childs
            foreach ($visible_childs as $child) {
                // check child limit
                $cnt_child++;

                if ($this->isNodeVisible($child)) {
                    if (!$any) {
                        $this->listStart($tpl);
                        $any = true;
                    }
                    $this->renderNode($child, $tpl);
                }
            }
            if ($any) {
                $this->listEnd($tpl);
            }
        }
    }

    /**
     * Get DOM node id for node id
     * @param string $a_node_id
     */
    public function getDomNodeIdForNodeId($a_node_id): string
    {
        return "exp_node_" . $this->getId() . "_" . $a_node_id;
    }

    /**
     * Get node id for dom node id
     */
    public function getNodeIdForDomNodeId(string $a_dom_node_id): string
    {
        $i = strlen("exp_node_" . $this->getId() . "_");
        return substr($a_dom_node_id, $i);
    }

    /**
     * List item start
     * @param object|array $a_node
     */
    public function listItemStart(ilTemplate $tpl, $a_node): void
    {
        $tpl->setCurrentBlock("list_item_start");
        if ($this->getAjax() && $this->nodeHasVisibleChilds($a_node) && !$this->isNodeOpen($this->getNodeId($a_node))) {
            $tpl->touchBlock("li_closed");
        }
        if ($this->isNodeOpen($this->getNodeId($a_node))) {
            $tpl->touchBlock("li_opened");
        }

        $tpl->setVariable(
            "DOM_NODE_ID",
            $this->getDomNodeIdForNodeId($this->getNodeId($a_node))
        );
        $tpl->parseCurrentBlock();
        $tpl->touchBlock("tag");
    }

    public function listItemEnd(ilTemplate $tpl): void
    {
        $tpl->touchBlock("list_item_end");
        $tpl->touchBlock("tag");
    }

    public function listStart(ilTemplate $tpl): void
    {
        $tpl->touchBlock("list_start");
        $tpl->touchBlock("tag");
    }

    public function listEnd(ilTemplate $tpl): void
    {
        $tpl->touchBlock("list_end");
        $tpl->touchBlock("tag");
    }

    public function isNodeOnclickEnabled(): bool
    {
        return $this->nodeOnclickEnabled;
    }

    public function setNodeOnclickEnabled(bool $nodeOnclickEnabled): void
    {
        $this->nodeOnclickEnabled = $nodeOnclickEnabled;
    }

    public function isEnableDnd(): bool
    {
        return $this->enable_dnd;
    }

    // Enable Drag & Drop functionality
    public function setEnableDnd(bool $enable_dnd): void
    {
        $this->enable_dnd = $enable_dnd;
    }
}
