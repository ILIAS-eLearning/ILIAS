<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Tree\Node\Node;
use ILIAS\UI\Component\Tree\Tree;
use ILIAS\UI\Component\Tree\TreeRecursion;

/**
 * Class ilForumExplorerGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumExplorerGUI implements TreeRecursion
{
    /** @var object */
    private $parent_obj;

    /** @var */
    private $id;

    /** @var \ILIAS\DI\UIServices */
    private $ui;

    /** @var  string */
    private $parent_cmd;

    /** @var ilForumTopic */
    protected $thread;

    /** @var int */
    protected $max_entries = PHP_INT_MAX;

    /** @var ilTemplate */
    protected $tpl;

    /** @var ilCtrl */
    protected $ctrl;

    /** @var \Psr\Http\Message\ServerRequestInterface */
    private $httpRequest;

    /** @var bool */
    protected $preloaded = false;

    /** @var array */
    protected $preloaded_children = [];

    /** @var array */
    protected $node_id_to_parent_node_id_map = [];

    /** @var ilForumPost|null */
    protected $root_node = null;

    /** @var \ilForumAuthorInformation[] */
    protected $authorInformation = [];

    /** @var array */
    private $custom_open_nodes = [];

    /** @var ilSessionIStorage */
    protected $store;

    /** @var array */
    private $open_nodes = [];

    /** @var int */
    protected $currentPostingId = 0;

    /**
     * ilForumExplorerGUI constructor.
     * @param $a_expl_id
     * @param $a_parent_obj
     * @param $a_parent_cmd
     * @param ilForumTopic $thread
     */
    public function __construct($a_expl_id, $a_parent_obj, $a_parent_cmd, ilForumTopic $thread)
    {
        global $DIC;

        $this->id = $a_expl_id;

        $this->ui = $DIC->ui();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->httpRequest = $DIC->http()->request();
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;

        $frm = new ilForum();
        $this->max_entries = (int) $frm->getPageHits();

        $this->thread = $thread;
        $this->root_node = $thread->getFirstPostNode();

        $this->ctrl->setParameter($this->parent_obj, 'thr_pk', $this->thread->getId());

        $this->setNodeOpen((int) $this->root_node->getId());

        $this->store = new ilSessionIStorage('expl2');
        $openNodes = $this->store->get($a_expl_id);
        $this->open_nodes = is_string($openNodes) ? unserialize($openNodes) : [];
        if (!is_array($this->open_nodes)) {
            $this->open_nodes = [];
        }

        $this->initPosting();
    }

    /**
     *
     */
    protected function initPosting() : void
    {
        $postingId = (int) ($this->httpRequest->getParsedBody()['pos_pk'] ?? 0);
        if (0 === $postingId) {
            $postingId = (int) ($this->httpRequest->getQueryParams()['pos_pk'] ?? 0);
        }

        $this->currentPostingId = (int) $postingId;
    }

    /**
     * @param int $parentNodeId
     * @return array
     */
    public function getChildrenOfNode($parentNodeId) : array
    {
        if ($this->preloaded) {
            if (isset($this->preloaded_children[$parentNodeId])) {
                return $this->preloaded_children[$parentNodeId];
            }
        }

        return $this->thread->getNestedSetPostChildren($parentNodeId, 1);
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
     * @inheritDoc
     */
    public function getChildren($record, $environment = null) : array
    {
        return $this->getChildrenOfNode((int) $record['pos_pk']);
    }

    /**
     * @return Tree
     */
    public function getTreeComponent() : Tree
    {
        $f = $this->ui->factory();

        $rootNode = array(
            'pos_pk' => $this->root_node->getId(),
            'pos_subject' => $this->root_node->getSubject(),
            'pos_author_id' => $this->root_node->getPosAuthorId(),
            'pos_display_user_id' => $this->root_node->getDisplayUserId(),
            'pos_usr_alias' => $this->root_node->getUserAlias(),
            'pos_date' => $this->root_node->getCreateDate(),
            'import_name' => $this->root_node->getImportName(),
            'post_read' => $this->root_node->isPostRead()
        );

        $endData = array($rootNode);

        $tree = $f->tree()
            ->expandable($this)
            ->withData($endData)
            ->withHighlightOnNodeClick(true);

        return $tree;
    }

    /**
     * @inheritDoc
     */
    public function build(
        \ILIAS\UI\Component\Tree\Node\Factory $factory,
        $record,
        $environment = null
    ) : Node {
        /** @var Node $node */
        $node = $this->createNode($factory, $record);

        if ($record['pos_pk'] != $this->root_node->getId()) {
            $href = $this->getNodeHref($record);
            if ($href) {
                $node = $node->withLink(new \ILIAS\Data\URI(ILIAS_HTTP_PATH . '/' . $href));
            }
        }

        if ($this->isNodeOpen((int) $record['pos_pk'])) {
            $node = $node->withExpanded(true);
        }

        $node = $node->withAdditionalOnLoadCode(function ($id) use ($record) {
            $serverNodeId = $record['pos_pk'];

            $this->ctrl->setParameter($this->parent_obj, 'node_id', $serverNodeId);
            $this->ctrl->setParameter($this->parent_obj, 'thr_pk', $this->thread->getId());
            $url = $this->ctrl->getLinkTarget($this->parent_obj, 'toggleExplorerNodeState', '', true, false);
            $this->ctrl->setParameter($this->parent_obj, 'node_id', null);
            $this->ctrl->setParameter($this->parent_obj, 'thr_pk', null);

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

        return $node->withHighlighted($this->currentPostingId === (int) $record['pos_pk']);
    }

    /**
     * @param int $nodeId
     * @return bool
     */
    private function isNodeOpen(int $nodeId) : bool
    {
        return (
            in_array($nodeId, $this->open_nodes) ||
            in_array($nodeId, $this->custom_open_nodes)
        );
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
     * @param int $nodeId
     */
    private function setNodeOpen(int $nodeId) : void
    {
        if (!in_array($nodeId, $this->custom_open_nodes)) {
            $this->custom_open_nodes[] = $nodeId;
        }
    }

    /**
     * @param $factory
     * @param $node
     * @return mixed
     * @throws ilDateTimeException
     */
    private function createNode(
        \ILIAS\UI\Component\Tree\Node\Factory $factory,
        $node
    ) : Node {
        global $DIC;

        $path = $this->getNodeIcon($node);

        $icon = $DIC->ui()
            ->factory()
            ->symbol()
            ->icon()
            ->custom($path, 'forum');

        if ($node['pos_pk'] == $this->root_node->getId()) {
            $treeNode = $factory->simple($node['pos_subject'], $icon);
        } else {
            $authorInfo = $this->getAuthorInformationByNode($node);
            $creationDate = ilDatePresentation::formatDate(new ilDateTime($node['pos_date'], IL_CAL_DATETIME));
            $bylineString = $authorInfo->getAuthorShortName() . ', ' . $creationDate;

            $treeNode = $factory->bylined($node['pos_subject'], $bylineString, $icon);
        }

        return $treeNode;
    }

    /**
     * @param array $node
     * @return \ilForumAuthorInformation
     */
    private function getAuthorInformationByNode(array $node) : \ilForumAuthorInformation
    {
        if (isset($this->authorInformation[(int) $node['pos_pk']])) {
            return $this->authorInformation[(int) $node['pos_pk']];
        }

        return $this->authorInformation[(int) $node['pos_pk']] = new ilForumAuthorInformation(
            $node['pos_author_id'],
            $node['pos_display_user_id'],
            $node['pos_usr_alias'],
            $node['import_name']
        );
    }

    /**
     * @param $node
     * @return string
     */
    private function getNodeIcon(array $node) : string
    {
        if ($this->root_node->getId() == $node['pos_pk']) {
            return ilObject::_getIcon(0, 'tiny', 'frm');
        }

        return $this->getAuthorInformationByNode($node)->getProfilePicture();
    }

    /**
     * 
     */
    private function preloadChildren() : void
    {
        $this->preloaded_children = [];
        $this->node_id_to_parent_node_id_map = [];

        $children = $this->thread->getNestedSetPostChildren($this->root_node->getId());

        array_walk($children, function (&$node, $key) {
            $this->node_id_to_parent_node_id_map[(int) $node['pos_pk']] = (int) $node['parent_pos'];
            $this->preloaded_children[(int) $node['parent_pos']][$node['pos_pk']] = $node;
        });

        $this->preloaded = true;
    }

    /**
     * @param array $node
     * @return string
     */
    private function getNodeHref(array $node) : string
    {
        $this->ctrl->setParameter($this->parent_obj, 'backurl', null);
        $this->ctrl->setParameter($this->parent_obj, 'pos_pk', $node['pos_pk']);

        if (isset($node['counter']) && $node['counter'] > 0) {
            $this->ctrl->setParameter(
                $this->parent_obj,
                'page',
                floor(($node['counter'] - 1) / $this->max_entries)
            );
        }

        if (isset($node['post_read']) && $node['post_read']) {
            return $this->ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd, $node['pos_pk'], false, false);
        }

        return $this->ctrl->getLinkTarget($this->parent_obj, 'markPostRead', $node['pos_pk'], false, false);
    }
}
