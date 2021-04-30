<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Tree\Node\Node;
use ILIAS\UI\Component\Tree\Tree;

/**
 * Class ilForumExplorerGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumExplorerGUI extends ilTreeExplorerGUI
{
    /** @var ilForumTopic */
    protected $thread;

    /** @var ilForumPost */
    protected $root_node;

    /** @var array */
    protected $node_id_to_parent_node_id_map = [];

    /** @var int */
    protected $max_entries = PHP_INT_MAX;

    /** @var array */
    protected $preloaded_children = [];

    /** @var ilForumAuthorInformation[] */
    protected $authorInformation = [];

    /** @var int */
    protected $currentPostingId = 0;
    
    /** @var int  */
    private $currentPage = 0;

    /**
     * ilForumExplorerGUI constructor.
     * @param $a_expl_id
     * @param $a_parent_obj
     * @param $a_parent_cmd
     * @param ilForumTopic $thread
     * @param ilForumPost $root
     */
    public function __construct(string $a_expl_id, object $a_parent_obj, string $a_parent_cmd, ilForumTopic $thread, ilForumPost $root)
    {
        global $DIC;

        parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $DIC->repositoryTree());
        
        $this->setSkipRootNode(false);
        $this->setAjax(false);
        $this->setPreloadChilds(true);

        $this->thread = $thread;
        $this->root_node = $root;

        $this->ctrl->setParameter($this->parent_obj, 'thr_pk', $this->thread->getId());

        $frm = new ilForum();
        $this->max_entries = (int) $frm->getPageHits();

        $this->initPosting();

        $this->setNodeOpen($this->root_node->getId());
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
     * @inheritDoc
     */
    public function getChildsOfNode($parentNodeId)
    {
        if ($this->preloaded) {
            if (isset($this->preloaded_children[$parentNodeId])) {
                return $this->preloaded_children[$parentNodeId];
            }

            return [];
        }

        return $this->thread->getNestedSetPostChildren($parentNodeId, 1);
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage(int $currentPage) : void
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @inheritDoc
     */
    protected function preloadChilds()
    {
        $this->preloaded_children = [];
        $this->node_id_to_parent_node_id_map = [];

        $children = $this->thread->getNestedSetPostChildren($this->root_node->getId());

        array_walk($children, function (&$node, $key) {
            $this->node_id_to_parent_node_id_map[(int) $node['pos_pk']] = (int) $node['parent_pos'];

            if (!array_key_exists((int) $node['pos_pk'], $this->preloaded_children)) {
                $this->preloaded_children[(int) $node['pos_pk']] = [];
            }

            $this->preloaded_children[(int) $node['parent_pos']][$node['pos_pk']] = $node;
        });

        $this->preloaded = true;
    }

    /**
     * @inheritDoc
     */
    public function getChildren($record, $environment = null) : array
    {
        return $this->getChildsOfNode((int) $record['pos_pk']);
    }

    /**
     * @return string
     */
    public function getTreeLabel()
    {
        return $this->lng->txt("frm_posts");
    }

    /**
     * @inheritDoc
     */
    public function getTreeComponent() : Tree
    {
        $rootNode = [
            [
                'pos_pk' => $this->root_node->getId(),
                'pos_subject' => $this->root_node->getSubject(),
                'pos_author_id' => $this->root_node->getPosAuthorId(),
                'pos_display_user_id' => $this->root_node->getDisplayUserId(),
                'pos_usr_alias' => $this->root_node->getUserAlias(),
                'pos_date' => $this->root_node->getCreateDate(),
                'import_name' => $this->root_node->getImportName(),
                'post_read' => $this->root_node->isPostRead()
            ]
        ];

        $tree = $this->ui->factory()->tree()
            ->expandable($this->getTreeLabel(), $this)
            ->withData($rootNode)
            ->withHighlightOnNodeClick(false);

        return $tree;
    }

    /**
     * @inheritDoc
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

        if ((int) $record['pos_pk'] === (int) $this->root_node->getId()) {
            $node = $factory->simple($this->getNodeContent($record), $icon);
        } else {
            $authorInfo = $this->getAuthorInformationByNode($record);
            $creationDate = ilDatePresentation::formatDate(new ilDateTime($record['pos_date'], IL_CAL_DATETIME));
            $bylineString = $authorInfo->getAuthorShortName() . ', ' . $creationDate;

            $node = $factory->bylined($this->getNodeContent($record), $bylineString, $icon);
        }

        return $node;
    }

    /**
     * @inheritDoc
     */
    protected function getNodeStateToggleCmdClasses($record) : array
    {
        return [
            'ilRepositoryGUI',
            'ilObjForumGUI',
        ];
    }

    /**
     * @param array $node
     * @return ilForumAuthorInformation
     */
    private function getAuthorInformationByNode(array $node) : ilForumAuthorInformation
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
     * @inheritDoc
     */
    public function getNodeId($a_node)
    {
        return $a_node['pos_pk'];
    }

    /**
     * @inheritDoc
     */
    public function getNodeIcon($node)
    {
        if ((int) $this->root_node->getId() === (int) $node['pos_pk']) {
            return ilObject::_getIcon(0, 'tiny', 'frm');
        }

        return $this->getAuthorInformationByNode($node)->getProfilePicture();
    }

    /**
     * @inheritDoc
     */
    public function getNodeHref($node)
    {
        if ((int) $this->root_node->getId() === (int) $node['pos_pk']) {
            return '';
        }

        $this->ctrl->setParameter($this->parent_obj, 'backurl', null);

        if (isset($node['counter']) && $node['counter'] > 0) {
            $page = (int) floor(($node['counter'] - 1) / $this->max_entries);
            $this->ctrl->setParameter($this->parent_obj, 'page', $page);
        }

        if (isset($node['post_read']) && $node['post_read']) {
            $this->ctrl->setParameter($this->parent_obj, 'pos_pk', null);
            $url = $this->ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd, $node['pos_pk'], false, false);
        } else {
            $this->ctrl->setParameter($this->parent_obj, 'pos_pk', $node['pos_pk']);
            $url = $this->ctrl->getLinkTarget($this->parent_obj, 'markPostRead', $node['pos_pk'], false, false);
            $this->ctrl->setParameter($this->parent_obj, 'pos_pk', null);
        }

        $this->ctrl->setParameter($this->parent_obj, 'page', null);
        
        return $url;
    }

    /**
     * @inheritDoc
     */
    public function getNodeContent($a_node)
    {
        return $a_node['pos_subject'];
    }
}
