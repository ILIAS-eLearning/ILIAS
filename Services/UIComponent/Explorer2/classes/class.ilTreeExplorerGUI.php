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

use ILIAS\UI\Component\Tree\Tree;

/**
 * Explorer class that works on tree objects (Services/Tree)
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @deprecated 10
 */
abstract class ilTreeExplorerGUI extends ilExplorerBaseGUI implements \ILIAS\UI\Component\Tree\TreeRecursion
{
    protected ilLanguage $lng;
    protected \Psr\Http\Message\ServerRequestInterface $httpRequest;
    protected ?ilTree $tree = null;
    protected string $tree_label = "";
    protected string $order_field = "";
    protected bool $order_field_numeric = false;
    protected array $type_white_list = array();
    protected array $type_black_list = array();
    protected array $childs = array();			// preloaded childs
    protected bool $preloaded = false;
    protected bool $preload_childs = false;
    protected ?array $root_node_data = null;
    protected array $all_childs = array();
    protected $root_id = 0;
    protected \ILIAS\DI\UIServices $ui;
    
    public function __construct(
        string $a_expl_id,
        $a_parent_obj,
        string $a_parent_cmd,
        ilTree $a_tree
    ) {
        global $DIC;

        $this->httpRequest = $DIC->http()->request();
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd);
        $this->tree = $a_tree;
    }
    
    public function getTree() : ilTree
    {
        return $this->tree;
    }
    
    public function setOrderField(
        string $a_val,
        bool $a_numeric = false
    ) : void {
        $this->order_field = $a_val;
        $this->order_field_numeric = $a_numeric;
    }
    
    public function getOrderField() : string
    {
        return $this->order_field;
    }
    
    /**
     * Set type white list
     * @param array $a_val array of strings of node types that should be retrieved
     */
    public function setTypeWhiteList(array $a_val) : void
    {
        $this->type_white_list = $a_val;
    }
    
    /**
     * Get type white list
     * @return array array of strings of node types that should be retrieved
     */
    public function getTypeWhiteList() : array
    {
        return $this->type_white_list;
    }
    
    /**
     * Set type black list
     * @param array $a_val array of strings of node types that should be filtered out
     */
    public function setTypeBlackList(array $a_val) : void
    {
        $this->type_black_list = $a_val;
    }
    
    /**
     * Get type black list
     * @return array array of strings of node types that should be filtered out
     */
    public function getTypeBlackList() : array
    {
        return $this->type_black_list;
    }

    public function setPreloadChilds(bool $a_val) : void
    {
        $this->preload_childs = $a_val;
    }

    public function getPreloadChilds() : bool
    {
        return $this->preload_childs;
    }

    protected function preloadChilds() : void
    {
        $subtree = $this->tree->getSubTree($this->getRootNode());
        foreach ($subtree as $s) {
            $wl = $this->getTypeWhiteList();
            if (is_array($wl) && count($wl) > 0 && !in_array($s["type"], $wl)) {
                continue;
            }
            $bl = $this->getTypeBlackList();
            if (is_array($bl) && count($bl) > 0 && in_array($s["type"], $bl)) {
                continue;
            }
            $this->childs[$s["parent"]][] = $s;
            $this->all_childs[$s["child"]] = $s;
        }

        if ($this->order_field !== "") {
            foreach ($this->childs as $k => $childs) {
                $this->childs[$k] = ilArrayUtil::sortArray(
                    $childs,
                    $this->order_field,
                    "asc",
                    $this->order_field_numeric
                );
            }
        }

        // sort childs and store prev/next reference
        if ($this->order_field === "") {
            $this->all_childs =
                ilArrayUtil::sortArray($this->all_childs, "lft", "asc", true, true);
            $prev = false;
            foreach ($this->all_childs as $k => $c) {
                if ($prev) {
                    $this->all_childs[$prev]["next_node_id"] = $k;
                }
                $this->all_childs[$k]["prev_node_id"] = $prev;
                $this->all_childs[$k]["next_node_id"] = false;
                $prev = $k;
            }
        }

        $this->preloaded = true;
    }



    /**
     * Get successor node (currently only(!) based on lft/rgt tree values)
     *
     * @param int|string $a_node_id node id
     * @return mixed node id or false
     */
    public function getSuccessorNode(
        $a_node_id,
        string $a_type = ""
    ) {
        if ($this->order_field !== "") {
            die("ilTreeExplorerGUI::getSuccessorNode not implemented for order field " . $this->order_field);
        }

        if ($this->preloaded) {
            $next_id = $a_node_id;
            while (($next_id = $this->all_childs[$next_id]["next_node_id"]) && $a_type !== "" &&
                $this->all_childs[$next_id]["type"] !== $a_type) {
                // do nothing
            }
            if ($next_id) {
                return $this->all_childs[$next_id];
            }
            return false;
        }
        return $this->getTree()->fetchSuccessorNode($a_node_id, $a_type);
    }



    /**
     * Get childs of node
     * @param int|string $a_parent_node_id parent id
     * @return array childs
     */
    public function getChildsOfNode($a_parent_node_id) : array
    {
        if ($this->preloaded && $this->getSearchTerm() === "") {
            if (isset($this->childs[$a_parent_node_id]) && is_array($this->childs[$a_parent_node_id])) {
                return $this->childs[$a_parent_node_id];
            }
            return array();
        }

        $wl = $this->getTypeWhiteList();
        if (count($wl) > 0) {
            $childs = $this->tree->getChildsByTypeFilter($a_parent_node_id, $wl, $this->getOrderField());
        } else {
            $childs = $this->tree->getChilds($a_parent_node_id, $this->getOrderField());
        }
        
        // apply black list filter
        $bl = $this->getTypeBlackList();
        if (is_array($bl) && count($bl) > 0) {
            $bl_childs = array();
            foreach ($childs as $k => $c) {
                if (!in_array($c["type"], $bl, true) && ($this->matches($c) || $this->requested_node_id !== $this->getDomNodeIdForNodeId($a_parent_node_id))) {
                    $bl_childs[$k] = $c;
                }
            }
            return $bl_childs;
        }

        $final_childs = [];
        foreach ($childs as $k => $c) {
            if ($this->matches($c) || $this->requested_node_id !== $this->getDomNodeIdForNodeId($a_parent_node_id)) {
                $final_childs[$k] = $c;
            }
        }
        return $final_childs;
    }

    /**
     * Does a node match a search term (or is search term empty)
     * @param object|array $node
     * @return bool
     */
    protected function matches($node) : bool
    {
        return (
            $this->getSearchTerm() === "" ||
            is_int(ilStr::striPos($this->getNodeContent($node), $this->getSearchTerm()))
        );
    }

    
    /**
     * Get id for node
     *
     * @param object|array $a_node
     * @return string
     */
    public function getNodeId($a_node)
    {
        return $a_node["child"];
    }

    /**
     * Get node icon alt attribute
     * @param object|array $a_node node
     * @return string image alt attribute
     */
    public function getNodeIconAlt($a_node) : string
    {
        $lng = $this->lng;
        
        return $lng->txt("icon") . " " . $lng->txt("obj_" . ($a_node["type"] ?? ''));
    }

    /**
     * Get root node
     *
     * @return object|array node
     */
    public function getRootNode()
    {
        if (!isset($this->root_node_data)) {
            $this->root_node_data = $this->getTree()->getNodeData($this->getRootId());
        }
        return $this->root_node_data;
    }

    /**
     * @param int|string $a_root
     */
    public function setRootId($a_root) : void
    {
        $this->root_id = $a_root;
    }

    protected function getRootId() : int
    {
        return $this->root_id
            ?: $this->getTree()->readRootId();
    }
    
    /**
     * Set node path to be opened
     *
     * @param string $a_id node id
     */
    public function setPathOpen($a_id) : void
    {
        $path = $this->getTree()->getPathId($a_id);
        foreach ($path as $id) {
            $this->setNodeOpen($id);
        }
    }

    /**
     * Get HTML
     *
     * @return string html
     */
    public function getHTML($new = false) : string
    {
        if ($this->getPreloadChilds()) {
            $this->preloadChilds();
        }
        if (!$new) {
            return parent::getHTML();
        }
        return $this->render();
    }

    // New implementation

    public function getChildren($record, $environment = null) : array
    {
        return $this->getChildsOfNode($record["child"]);
    }

    /**
     * Creates at tree node, can be overwritten in derivatives if another node type should be used
     */
    protected function createNode(
        \ILIAS\UI\Component\Tree\Node\Factory $factory,
        $record
    ) : \ILIAS\UI\Component\Tree\Node\Node {
        $nodeIconPath = $this->getNodeIcon($record);

        $icon = null;
        if ($nodeIconPath !== '') {
            $icon = $this->ui
                ->factory()
                ->symbol()
                ->icon()
                ->custom($nodeIconPath, $this->getNodeIconAlt($record));
        }

        return $factory->simple($this->getNodeContent($record), $icon);
    }

    /**
     * Should return an array of ilCtrl-enabled command classes which should be used to build the URL for
     * the expand/collapse actions applied on a tree node
     * @param $record
     * @return array
     */
    protected function getNodeStateToggleCmdClasses($record) : array
    {
        return [];
    }

    public function build(
        \ILIAS\UI\Component\Tree\Node\Factory $factory,
        $record,
        $environment = null
    ) : \ILIAS\UI\Component\Tree\Node\Node {
        $node = $this->createNode($factory, $record);

        $href = $this->getNodeHref($record);
        if ($href !== '' && '#' !== $href) {
            $node = $node->withLink(new \ILIAS\Data\URI(ILIAS_HTTP_PATH . '/' . $href));
        }

        if ($this->isNodeOpen((int) $this->getNodeId($record))) {
            $node = $node->withExpanded(true);
        }

        $nodeStateToggleCmdClasses = $this->getNodeStateToggleCmdClasses($record);
        $cmdClass = end($nodeStateToggleCmdClasses);

        if (is_string($cmdClass) && $cmdClass !== '') {
            $node = $node->withAdditionalOnLoadCode(function ($id) use ($record, $nodeStateToggleCmdClasses, $cmdClass) : string {
                $serverNodeId = $this->getNodeId($record);

                $this->ctrl->setParameterByClass($cmdClass, 'node_id', $serverNodeId);
                $url = $this->ctrl->getLinkTargetByClass($nodeStateToggleCmdClasses, 'toggleExplorerNodeState', '', true, false);
                $this->ctrl->setParameterByClass($cmdClass, 'node_id', null);

                $javascript = "il.UI.tree.registerToggleNodeAsyncAction('$id', '$url', 'prior_state');";

                return $javascript;
            });
        }

        return $node;
    }

    public function getTreeLabel() : string
    {
        return $this->tree_label;
    }

    public function getTreeComponent() : Tree
    {
        $f = $this->ui->factory();
        $tree = $this->getTree();

        $data = array(
            $tree->getNodeData($tree->readRootId())
        );

        $label = $this->getTreeLabel();
        if ($this->getTreeLabel() === "" && $this->getNodeContent($this->getRootNode())) {
            $label = $this->getNodeContent($this->getRootNode());
        }

        $tree = $f->tree()->expandable($label, $this)
            ->withData($data)
            ->withHighlightOnNodeClick(true);

        return $tree;
    }

    /**
     * Should be called by an ilCtrl-enabled command class if a tree node toggle action should be processed
     */
    public function toggleExplorerNodeState() : void
    {
        $nodeId = (int) ($this->httpRequest->getQueryParams()['node_id'] ?? 0);
        $priorState = (int) ($this->httpRequest->getQueryParams()['prior_state'] ?? 0);

        if ($nodeId > 0) {
            if (0 === $priorState && !in_array($nodeId, $this->open_nodes)) {
                $this->open_nodes[] = $nodeId;
            } elseif (1 === $priorState && in_array($nodeId, $this->open_nodes)) {
                $key = array_search($nodeId, $this->open_nodes);
                unset($this->open_nodes[$key]);
            }

            $this->store->set('on_' . $this->id, serialize($this->open_nodes));
        }
        exit();
    }

    protected function render() : string
    {
        $r = $this->ui->renderer();

        return $r->render([
            $this->getTreeComponent()
        ]);
    }
}
