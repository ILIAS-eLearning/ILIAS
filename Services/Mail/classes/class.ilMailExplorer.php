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

    /** @var */
    private $root_id;

    /** @var array */
    private $open_nodes = [];

    /** @var array */
    private $custom_open_nodes = [];

    /** @var \ilLanguage */
    private $lng;

    /** @var \ilCtrl */
    private $ctrl;

    /** @var \Psr\Http\Message\ServerRequestInterface */
    private $httpRequest;

    private $order_field = '';

    private $search_term = '';

    private $children = [];

    private $all_children = [];

    private $preloaded = false;

    private $orderFieldNumeric = false;

    /** @var ilSessionIStorage */
    protected $store;

    /** @var int  */
    protected $currentFolderId = 0;

    /**
     * ilMailExplorer constructor.
     * @param $parentObject
     * @param $userId
     */
    public function __construct($parentObject, $userId)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->httpRequest = $DIC->http()->request();
        $this->parentObject = $parentObject;
        $this->ui = $DIC->ui();

        $this->tree = new ilTree($userId);
        $this->tree->setTableNames('mail_tree', 'mail_obj_data');

        $this->store = new ilSessionIStorage('expl2');
        $openNodes = $this->store->get('mail_tree');
        $this->open_nodes = $this->open_nodes = is_string($openNodes) ? unserialize($openNodes) : [];
        if (!is_array($this->open_nodes)) {
            $this->open_nodes = [];
        }

        $this->initFolder();
    }

    /**
     *
     */
    protected function initFolder() : void
    {
        $folderId = (int) ($this->httpRequest->getParsedBody()['mobj_id'] ?? 0);
        if (0 === $folderId) {
            $folderId = (int) ($this->httpRequest->getQueryParams()['mobj_id'] ?? 0);
        }

        $this->currentFolderId = (int) $folderId;
    }

    /**
     * @return Tree
     */
    public function getTreeComponent() : Tree
    {
        $f = $this->ui->factory();

        /** @var ilTree $tree */
        $tree = $this->tree;

        $subtree = $tree->getChilds((int) $tree->readRootId());
        $data = $subtree;

        $tree = $f->tree()
            ->expandable($this)
            ->withData($data)
            ->withHighlightOnNodeClick(false);

        return $tree;
    }

    /**
     * @inheritDoc
     */
    public function getChildren($record, $environment = null) : array
    {
        return $this->getChildrenByParentId($record['child']);
    }

    /**
     * @inheritDoc
     */
    public function build(
        Factory $factory,
        $record,
        $environment = null
    ) : Node {
        /** @var Node $node */
        $node = $this->createNode($factory, $record);

        $href = $this->getNodeHref($record);

        if ($href) {
            $node = $node->withLink(new \ILIAS\Data\URI(ILIAS_HTTP_PATH . '/' . $href));
        }

        if ($this->isNodeOpen((int) $record['child'])) {
            $node = $node->withExpanded(true);
        }

        $node = $node->withAdditionalOnLoadCode(function ($id) use ($record) {
            $serverNodeId = $record['child'];

            $this->ctrl->setParameterByClass('ilMailGUI', 'node_id', $serverNodeId);
            $url = $this->ctrl->getLinkTargetByClass(['ilMailGUI'], 'toggleExplorerNodeState', '', true, false);
            $this->ctrl->setParameterByClass('ilMailGUI', 'node_id', null);

            $code = "$('#$id').on('click', function(event) {
                let node = $(this);

                if (node.hasClass('expandable')) {
                    il.UI.tree.toggleNodeState(event, '$url', 'prior_state', node.hasClass('expanded'));
                    event.preventDefault();
                    event.stopPropagation();
                }
            });";

            return $code;
        });

        return $node->withHighlighted($this->currentFolderId === (int) $record['child']);
    }

    /**
     * @return string
     */
    public function getHTML() : string
    {
        $this->preloadChildren();

        return $this->render();
    }

    /**
     * @param Factory $factory
     * @param array $node
     * @return Node
     */
    private function createNode(
        Factory $factory,
        array $node
    ) : Node {
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
     * @param int $parentNodeId
     * @return array
     */
    private function getChildrenByParentId(int $parentNodeId) : array
    {
        if ($this->preloaded && $this->search_term == '') {
            if (is_array($this->children[$parentNodeId])) {
                return $this->children[$parentNodeId];
            }

            return [];
        }

        $children = $this->tree->getChilds($parentNodeId, $this->order_field);

        $finalChildren = [];
        foreach ($children as $key => $child) {
            if ($this->matches($child)) {
                $finalChildren[$key] = $child;
            }
        }

        return $finalChildren;
    }

    /**
     * @param int $nodeId
     * @return bool
     */
    private function isNodeOpen(int $nodeId) : bool
    {
        return (
            $this->getNodeId($this->getRootNode()) == $nodeId ||
            in_array($nodeId, $this->open_nodes) ||
            in_array($nodeId, $this->custom_open_nodes)
        );
    }

    /**
     * Preload childs
     */
    private function preloadChildren() : void
    {
        $subtree = $this->tree->getSubTree($this->getRootNode());
        foreach ($subtree as $subNode) {
            $this->children[$subNode['parent']][] = $subNode;
            $this->all_children[$subNode['child']] = $subNode;
        }

        if ($this->order_field != '') {
            foreach ($this->children as $key => $children) {
                $this->children[$key] = ilUtil::sortArray(
                    $children,
                    $this->order_field,
                    'asc',
                    $this->orderFieldNumeric
                );
            }
        }

        // sort children and store prev/next reference
        if ($this->order_field == '') {
            $this->all_children = ilUtil::sortArray(
                $this->all_children,
                'lft',
                'asc',
                true,
                true
            );

            $prev = false;
            foreach ($this->all_children as $key => $children) {
                if ($prev) {
                    $this->all_children[$prev]['next_node_id'] = $key;
                }
                $this->all_children[$key]['prev_node_id'] = $prev;
                $this->all_children[$key]['next_node_id'] = false;
                $prev = $key;
            }
        }

        $this->preloaded = true;
    }

    /**
     * @return string
     */
    private function render() : string
    {
        $renderer = $this->ui->renderer();

        return $renderer->render([
            $this->getTreeComponent()
        ]);
    }

    /**
     * @param array $node
     * @return int
     */
    private function getNodeId(array $node)
    {
        return (int) $node['child'];
    }

    /**
     * @return array
     */
    private function getRootNode() : array
    {
        if (!isset($this->root_node_data)) {
            $this->root_node_data = $this->tree->getNodeData($this->getRootId());
        }

        return $this->root_node_data;
    }

    /**
     * @return int
     */
    private function getRootId() : int
    {
        return (int) ($this->root_id ? $this->root_id : $this->tree->readRootId());
    }

    /**
     * Does a node match a search term (or is search term empty)
     * @param array
     * @return bool
     */
    private function matches(array $node) : bool
    {
        if (
            $this->search_term == '' ||
            is_int(stripos($this->getNodeContent($node), $this->search_term))
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param array $node
     * @return string
     */
    private function getNodeContent(array $node) : string
    {
        $content = $node['title'];

        if ($node['child'] == $this->getNodeId($this->getRootNode())) {
            $content = $this->lng->txt('mail_folders');
        } elseif ($node['depth'] < 3) {
            $content = $this->lng->txt('mail_' . $node['title']);
        }

        return $content;
    }

    /**
     * @param array $node
     * @return string
     */
    private function getNodeIcon(array $node) : string
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

    /**
     * @param array $node
     * @return string
     */
    private function getNodeHref(array $node) : string
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
