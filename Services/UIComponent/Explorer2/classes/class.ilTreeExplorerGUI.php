<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerBaseGUI.php");

/**
 * Explorer class that works on tree objects (Services/Tree)
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesUIComponent
 */
abstract class ilTreeExplorerGUI extends ilExplorerBaseGUI implements \ILIAS\UI\Component\Tree\TreeRecursion
{
    /** @var ilLanguage */
    protected $lng;

    /** @var \Psr\Http\Message\ServerRequestInterface */
    protected $httpRequest;

    protected $tree = null;
    protected $tree_label = "";
    protected $order_field = "";
    protected $order_field_numeric = false;
    protected $type_white_list = array();
    protected $type_black_list = array();
    protected $childs = array();			// preloaded childs
    protected $preloaded = false;
    protected $preload_childs = false;
    protected $root_node_data = null;
    protected $all_childs = array();

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;
    
    /**
     * Constructor
     */
    public function __construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $a_tree)
    {
        global $DIC;

        $this->httpRequest = $DIC->http()->request();
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd);
        $this->tree = $a_tree;
    }
    
    /**
     * Get tree
     *
     * @return object tree object
     */
    public function getTree()
    {
        return $this->tree;
    }
    
    /**
     * Set order field
     *
     * @param string $a_val order field key
     */
    public function setOrderField($a_val, $a_numeric = false)
    {
        $this->order_field = $a_val;
        $this->order_field_numeric = $a_numeric;
    }
    
    /**
     * Get order field
     *
     * @return string order field key
     */
    public function getOrderField()
    {
        return $this->order_field;
    }
    
    /**
     * Set type white list
     *
     * @param array $a_val array of strings of node types that should be retrieved
     */
    public function setTypeWhiteList($a_val)
    {
        $this->type_white_list = $a_val;
    }
    
    /**
     * Get type white list
     *
     * @return array array of strings of node types that should be retrieved
     */
    public function getTypeWhiteList()
    {
        return $this->type_white_list;
    }
    
    /**
     * Set type black list
     *
     * @param array $a_val array of strings of node types that should be filtered out
     */
    public function setTypeBlackList($a_val)
    {
        $this->type_black_list = $a_val;
    }
    
    /**
     * Get type black list
     *
     * @return array array of strings of node types that should be filtered out
     */
    public function getTypeBlackList()
    {
        return $this->type_black_list;
    }

    /**
     * Set preload childs
     *
     * @param boolean $a_val preload childs
     */
    public function setPreloadChilds($a_val)
    {
        $this->preload_childs = $a_val;
    }

    /**
     * Get preload childs
     *
     * @return boolean preload childs
     */
    public function getPreloadChilds()
    {
        return $this->preload_childs;
    }

    /**
     * Preload childs
     */
    protected function preloadChilds()
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

        if ($this->order_field != "") {
            foreach ($this->childs as $k => $childs) {
                $this->childs[$k] = ilUtil::sortArray($childs, $this->order_field, "asc", $this->order_field_numeric);
            }
        }

        // sort childs and store prev/next reference
        if ($this->order_field == "") {
            $this->all_childs =
                ilUtil::sortArray($this->all_childs, "lft", "asc", true, true);
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
     * @param integer $a_node_id node id
     * @param string $a_type node type
     * @return mixed node id or false
     */
    public function getSuccessorNode($a_node_id, $a_type = "")
    {
        if ($this->order_field != "") {
            die("ilTreeExplorerGUI::getSuccessorNode not implemented for order field " . $this->order_field);
        }

        if ($this->preloaded) {
            $next_id = $a_node_id;
            while (($next_id = $this->all_childs[$next_id]["next_node_id"]) && $a_type != "" &&
                $this->all_childs[$next_id]["type"] != $a_type);
            if ($next_id) {
                return $this->all_childs[$next_id];
            }
            return false;
        }
        return $this->getTree()->fetchSuccessorNode($a_node_id, $a_type);
    }



    /**
     * Get childs of node
     *
     * @param int $a_parent_node_id parent id
     * @return array childs
     */
    public function getChildsOfNode($a_parent_node_id)
    {
        if ($this->preloaded && $this->getSearchTerm() == "") {
            if (is_array($this->childs[$a_parent_node_id])) {
                return $this->childs[$a_parent_node_id];
            }
            return array();
        }

        $wl = $this->getTypeWhiteList();
        if (is_array($wl) && count($wl) > 0) {
            $childs = $this->tree->getChildsByTypeFilter($a_parent_node_id, $wl, $this->getOrderField());
        } else {
            $childs = $this->tree->getChilds($a_parent_node_id, $this->getOrderField());
        }
        
        // apply black list filter
        $bl = $this->getTypeBlackList();
        if (is_array($bl) && count($bl) > 0) {
            $bl_childs = array();
            foreach ($childs as $k => $c) {
                if (!in_array($c["type"], $bl) && ($this->matches($c) || $this->requested_node_id != $this->getDomNodeIdForNodeId($a_parent_node_id))) {
                    $bl_childs[$k] = $c;
                }
            }
            return $bl_childs;
        }

        $final_childs = [];
        foreach ($childs as $k => $c) {
            if ($this->matches($c) || $this->requested_node_id != $this->getDomNodeIdForNodeId($a_parent_node_id)) {
                $final_childs[$k] = $c;
            }
        }
        
        return $final_childs;
    }

    /**
     * Does a node match a search term (or is search term empty)
     *
     * @param array
     * @return bool
     */
    protected function matches($node) : bool
    {
        if ($this->getSearchTerm() == "" ||
            is_int(stripos($this->getNodeContent($node), $this->getSearchTerm()))) {
            return true;
        }
        return false;
    }

    
    /**
     * Get id for node
     *
     * @param mixed $a_node node object/array
     * @return string id
     */
    public function getNodeId($a_node)
    {
        return $a_node["child"];
    }

    /**
     * Get node icon alt attribute
     *
     * @param mixed $a_node node object/array
     * @return string image alt attribute
     */
    public function getNodeIconAlt($a_node)
    {
        $lng = $this->lng;
        
        return $lng->txt("icon") . " " . $lng->txt("obj_" . $a_node["type"]);
    }

    /**
     * Get root node
     *
     * @return mixed node object/array
     */
    public function getRootNode()
    {
        if (!isset($this->root_node_data)) {
            $this->root_node_data = $this->getTree()->getNodeData($this->getRootId());
        }
        return $this->root_node_data;
    }
    
    public function setRootId($a_root)
    {
        $this->root_id = $a_root;
    }
    
    protected function getRootId()
    {
        return $this->root_id
            ? $this->root_id
            : $this->getTree()->readRootId();
    }
    
    /**
     * Set node path to be opened
     *
     * @param string $a_id node id
     */
    public function setPathOpen($a_id)
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
    public function getHTML($new = false)
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

    /**
     * @inheritdoc
     */
    public function getChildren($node, $environment = null) : array
    {
        return $this->getChildsOfNode($node["child"]);
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
        if (is_string($nodeIconPath) && strlen($nodeIconPath) > 0) {
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

    /**
     * @inheritdoc
     */
    public function build(
        \ILIAS\UI\Component\Tree\Node\Factory $factory,
        $record,
        $environment = null
    ) : \ILIAS\UI\Component\Tree\Node\Node {
        $node = $this->createNode($factory, $record);

        $href = $this->getNodeHref($record);
        if (is_string($href) && strlen($href) > 0 && '#' !== $href) {
            $node = $node->withLink(new \ILIAS\Data\URI(ILIAS_HTTP_PATH . '/' . $href));
        }

        if ($this->isNodeOpen((int) $this->getNodeId($record))) {
            $node = $node->withExpanded(true);
        }

        $nodeStateToggleCmdClasses = $this->getNodeStateToggleCmdClasses($record);
        $cmdClass = end($nodeStateToggleCmdClasses);

        if (is_string($cmdClass) && strlen($cmdClass) > 0) {
            $node = $node->withAdditionalOnLoadCode(function ($id) use ($record, $nodeStateToggleCmdClasses, $cmdClass) {
                $serverNodeId = $this->getNodeId($record);

                $this->ctrl->setParameterByClass($cmdClass, 'node_id', $serverNodeId);
                $url = $this->ctrl->getLinkTargetByClass($nodeStateToggleCmdClasses, 'toggleExplorerNodeState', '', true, false);
                $this->ctrl->setParameterByClass($cmdClass, 'node_id', null);

                $javascript = "$('#$id').on('click', function(event) {
					let node = $(this);
	
					if (node.hasClass('expandable')) {
						il.UI.tree.toggleNodeState(event, '$url', 'prior_state', node.hasClass('expanded'));
						event.preventDefault();
						event.stopPropagation();
					}
				});";

                return $javascript;
            });
        }

        return $node;
    }

    /**
     * @return string
     */
    public function getTreeLabel()
    {
        return $this->tree_label;
    }

    /**
     * Get Tree UI
     *
     * @return \ILIAS\UI\Component\Tree\Tree|object
     */
    public function getTreeComponent()
    {
        $f = $this->ui->factory();
        $tree = $this->getTree();

        $data = array(
            $tree->getNodeData($tree->readRootId())
        );

        $label = $this->getTreeLabel();
        if ($this->getTreeLabel() == "" && $this->getNodeContent($this->getRootNode())) {
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

    /**
     * Render tree
     *
     * @return string
     */
    protected function render()
    {
        $r = $this->ui->renderer();

        return $r->render([
            $this->getTreeComponent()
        ]);
    }
}
