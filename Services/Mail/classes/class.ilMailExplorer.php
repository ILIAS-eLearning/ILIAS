<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Tree\Node\Factory;
use ILIAS\UI\Component\Tree\Node\Node;
use ILIAS\UI\Component\Tree\Tree;
use ILIAS\UI\Component\Tree\TreeRecursion;

/**
 * Class Mail Explorer
 * class for explorer view for mailboxes
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 */
class ilMailExplorer implements TreeRecursion
{
    /** @var ilMailGUI */
    private $parentObject;

    /** @var \ILIAS\DI\UIServices */
    private $ui;

    /** @var ilTree */
    private $tree;

    /** @var  */
    private $root_id;

    /** @var array */
    private $open_nodes = array();

    /** @var array */
    private $custom_open_nodes = array();

    /** @var \ilLanguage */
    private $lng;

    /** @var \ilCtrl */
    private $ctrl;

    /** @var \Psr\Http\Message\ServerRequestInterface */
    private $httpRequest;

    private $order_field = '';

    private $search_term = '';

    private $childs = array();

    private $all_childs = array();

    private $preloaded = false;

    private $orderFieldNumeric = false;


    /**
     * ilMailExplorer constructor.
     * @param $parentObject
     * @param $userId
     */
    public function __construct($parentObject, $userId)
    {
        global $DIC;

        $this->lng          = $DIC->language();
        $this->ctrl         = $DIC->ctrl();
        $this->httpRequest  = $DIC->http()->request();
        $this->parentObject = $parentObject;
        $this->ui           = $DIC->ui();

        $this->tree = new ilTree($userId);
        $this->tree->setTableNames('mail_tree', 'mail_obj_data');
    }

    /**
     * Get Tree UI
     *
     * @return Tree|object
     */
    public function getTreeComponent()
    {
        $f = $this->ui->factory();
        /** @var ilTree $tree */
        $tree = $this->tree;

        $subtree  = $tree->getChilds($tree->readRootId());
        $data     = $subtree;

        $tree = $f->tree()
                  ->expandable($this)
                  ->withData($data)
                  ->withHighlightOnNodeClick(true);

        return $tree;
    }

    /**
     * Get a list of records (that list can also be empty).
     * Each record will be relayed to $this->build to retrieve a Node.
     * Also, each record will be asked for Sub-Nodes using this function.
     * @param      $record
     * @param null $environment
     * @return array
     */
    public function getChildren($record, $environment = null) : array
    {
        return $this->getChildsOfNode($record['child']);
    }

    /**
     * Build and return a Node.
     * The renderer will provide the $factory-parameter which is the UI-factory
     * for nodes, as well as the (unspecified) $environment as configured at the Tree.
     * $record is the data the node should be build for.
     *
     * @param Factory $factory
     * @param         $record
     * @param null    $environment
     * @return Node
     */
    public function build(
        Factory $factory,
        $record,
        $environment = null
    ) : Node {
        $node = $this->createNode($factory, $record);

        $href = $this->getNodeHref($record);

        if ($href) {
            $node = $node->withAdditionalOnLoadCode( function($id) use ($href) {
                $js = "$('#$id').find('.node-label').on('click', function(event) {
                            window.location = '{$href}';
                            return false;
                        });";
                return $js;
            });
        }

        if ($this->isNodeOpen($record['child'])) {
            $node = $node->withExpanded(true);
        }

        return $node;
    }

    /**
     * Get HTML
     *
     * @return string html
     */
    public function getHTML()
    {
        $this->preloadChilds();

        return $this->render();
    }

    /**
     * @param $factory
     * @param $node
     * @return mixed
     */
    private function createNode(
        Factory $factory,
        $node
    ) {
        global $DIC;

        $path = $this->getNodeIcon($node);

        $icon = $DIC->ui()
                    ->factory()
                    ->symbol()
                    ->icon()
                    ->custom($path, 'a');

        $simple = $factory->simple($this->getNodeContent($node), $icon);

        return $simple;
    }


    /**
     * Get childs of node
     * @param int $parentNodeId parent id
     * @return array childs
     */
    private function getChildsOfNode($parentNodeId)
    {
        if ($this->preloaded && $this->search_term == '') {
            if (is_array($this->childs[$parentNodeId])) {
                return $this->childs[$parentNodeId];
            }
            return array();
        }

        $childs = $this->tree->getChilds($parentNodeId, $this->order_field);

        $finalChildren = [];
        foreach($childs as $key => $child) {
            if ($this->matches($child)) {
                $finalChildren[$key] = $child;
            }
        }

        return $finalChildren;
    }

    /**
     * Get all open nodes
     * @param
     * @return bool
     */
    private function isNodeOpen($nodeId)
    {
        return ($this->getNodeId($this->getRootNode()) == $nodeId
            || in_array($nodeId, $this->open_nodes)
            || in_array($nodeId, $this->custom_open_nodes));
    }

    /**
     * Preload childs
     */
    private function preloadChilds()
    {
        $subtree = $this->tree->getSubTree($this->getRootNode());
        foreach ($subtree as $subNode) {
            $this->childs[$subNode['parent']][] = $subNode;
            $this->all_childs[$subNode['child']] = $subNode;
        }

        if ($this->order_field != '') {
            foreach ($this->childs as $key => $childs) {
                $this->childs[$key] = ilUtil::sortArray(
                    $childs,
                    $this->order_field,
                    'asc',
                    $this->orderFieldNumeric
                );
            }
        }

        // sort childs and store prev/next reference
        if ($this->order_field == '') {
            $this->all_childs = ilUtil::sortArray(
                $this->all_childs,
                'lft',
                'asc',
                true,
                true
            );

            $prev = false;
            foreach ($this->all_childs as $key => $children) {
                if ($prev) {
                    $this->all_childs[$prev]['next_node_id'] = $key;
                }
                $this->all_childs[$key]['prev_node_id'] = $prev;
                $this->all_childs[$key]['next_node_id'] = false;
                $prev = $key;
            }
        }

        $this->preloaded = true;
    }

    /**
     * Render tree
     *
     * @return string
     */
    private function render()
    {
        $renderer = $this->ui->renderer();

        return $renderer->render([
            $this->getTreeComponent()
        ]);
    }

    /**
     * Get id for node
     * @param array $node node object/array
     * @return string id
     */
    private function getNodeId(array $node)
    {
        return $node['child'];
    }

    /**
     * Get root node
     *
     * @return mixed node object/array
     */
    private function getRootNode()
    {
        if (!isset($this->root_node_data)) {
            $this->root_node_data =  $this->tree->getNodeData($this->getRootId());
        }
        return $this->root_node_data;
    }


    private function getRootId()
    {
        return $this->root_id
            ? $this->root_id
            : $this->tree->readRootId();
    }

    /**
     * Does a node match a search term (or is search term empty)
     *
     * @param array
     * @return bool
     */
    private function matches($node): bool
    {
        if ($this->search_term == '' ||
            is_int(stripos($this->getNodeContent($node), $this->search_term))
        ) {
            return true;
        }
        return false;
    }

    private function getNodeContent(array $node)
    {
        $content = $node['title'];

        if ($node['child'] == $this->getNodeId($this->getRootNode())) {
            $content = $this->lng->txt('mail_folders');
        } elseif ($node['depth'] < 3) {
            $content = $this->lng->txt('mail_' . $node['title']);
        }

        return $content;
    }

    private function getNodeIcon(array $node)
    {
        if ($node['child'] == $this->getNodeId($this->getRootNode())) {
            $icon = ilUtil::getImagePath('icon_mail.svg');
        } else {
            $iconType = $node['m_type'];
            if ($node['m_type'] === 'user_folder') {
                $iconType = 'local';
            }

            $icon = ilUtil::getImagePath('icon_' . $iconType . '.svg');
        }

        return $icon;
    }

    private function getNodeHref(array $node)
    {
        if ($node['child'] == $this->getNodeId($this->getRootNode())) {
            $node['child'] = 0;
        }

        $this->ctrl->setParameterByClass('ilMailFolderGUI', 'mobj_id', $node['child']);
        $href = $this->ctrl->getLinkTargetByClass('ilMailFolderGUI', '', '', false, false);
        $this->ctrl->clearParametersByClass('ilMailFolderGUI');

        return $href;
    }
}
