<?php

declare(strict_types=1);

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
 * Class ilForumExplorerGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumExplorerGUI extends ilTreeExplorerGUI
{
    private ilForumTopic $thread;
    private ilForumPost $root_node;
    private int $max_entries;
    /** @var array<int, array<int, array<string, mixed>>> */
    private array $preloaded_children = [];

    /** @var array<int, ilForumAuthorInformation> */
    private array $authorInformation = [];

    public function __construct(
        string $a_expl_id,
        object $a_parent_obj,
        string $a_parent_cmd,
        ilForumTopic $thread,
        ilForumPost $root
    ) {
        global $DIC;

        parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $DIC->repositoryTree());

        $this->setSkipRootNode(false);
        $this->setAjax(false);
        $this->setPreloadChilds(true);

        $this->thread = $thread;
        $this->root_node = $root;

        $this->ctrl->setParameter($this->parent_obj, 'thr_pk', $this->thread->getId());

        $frm = new ilForum();
        $this->max_entries = $frm->getPageHits();

        $this->setNodeOpen($this->root_node->getId());
    }

    private function getRootNodeId(): int
    {
        return $this->root_node->getId();
    }

    private function getAuthorInformationByNode(array $node): ilForumAuthorInformation
    {
        return $this->authorInformation[(int) $node['pos_pk']] ?? ($this->authorInformation[(int) $node['pos_pk']] = new ilForumAuthorInformation(
            (int) ($node['pos_author_id'] ?? 0),
            (int) $node['pos_display_user_id'],
            (string) $node['pos_usr_alias'],
            (string) $node['import_name']
        ));
    }

    public function getChildsOfNode($a_parent_node_id): array
    {
        if ($this->preloaded) {
            return $this->preloaded_children[$a_parent_node_id] ?? [];
        }

        return $this->thread->getNestedSetPostChildren($a_parent_node_id, 1);
    }

    protected function preloadChilds(): void
    {
        $this->preloaded_children = [];

        $children = $this->thread->getNestedSetPostChildren($this->root_node->getId());

        array_walk($children, function ($node, $key): void {
            if (!array_key_exists((int) $node['pos_pk'], $this->preloaded_children)) {
                $this->preloaded_children[(int) $node['pos_pk']] = [];
            }

            $this->preloaded_children[(int) $node['parent_pos']][$node['pos_pk']] = $node;
        });

        $this->preloaded = true;
    }

    public function getChildren($record, $environment = null): array
    {
        return $this->getChildsOfNode((int) $record['pos_pk']);
    }

    public function getTreeLabel(): string
    {
        return $this->lng->txt("frm_posts");
    }

    public function getTreeComponent(): Tree
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

        return $this->ui->factory()->tree()
            ->expandable($this->getTreeLabel(), $this)
            ->withData($rootNode)
            ->withHighlightOnNodeClick(false);
    }

    protected function createNode(
        \ILIAS\UI\Component\Tree\Node\Factory $factory,
        $record
    ): \ILIAS\UI\Component\Tree\Node\Node {
        $nodeIconPath = $this->getNodeIcon($record);

        $icon = null;
        if ($nodeIconPath !== '') {
            $icon = $this->ui
                ->factory()
                ->symbol()
                ->icon()
                ->custom($nodeIconPath, $this->getNodeIconAlt($record));
        }

        if ((int) $record['pos_pk'] === $this->root_node->getId()) {
            $node = $factory->simple($this->getNodeContent($record), $icon);
        } else {
            $authorInfo = $this->getAuthorInformationByNode($record);
            $creationDate = ilDatePresentation::formatDate(new ilDateTime($record['pos_date'], IL_CAL_DATETIME));
            $bylineString = $authorInfo->getAuthorShortName() . ', ' . $creationDate;

            $node = $factory->bylined($this->getNodeContent($record), $bylineString, $icon);
        }

        return $node;
    }

    protected function getNodeStateToggleCmdClasses($record): array
    {
        return [
            ilRepositoryGUI::class,
            ilObjForumGUI::class,
        ];
    }

    public function getNodeId($a_node): int
    {
        return (isset($a_node['pos_pk']) ? (int) $a_node['pos_pk'] : 0);
    }

    public function getNodeIcon($a_node): string
    {
        if ($this->getRootNodeId() === (int) $a_node['pos_pk']) {
            return ilObject::_getIcon(0, 'tiny', 'frm');
        }

        return $this->getAuthorInformationByNode($a_node)->getProfilePicture();
    }

    public function getNodeHref($a_node): string
    {
        if ($this->getRootNodeId() === (int) $a_node['pos_pk']) {
            return '';
        }

        $this->ctrl->setParameter($this->parent_obj, 'backurl', null);

        if (isset($a_node['counter']) && $a_node['counter'] > 0) {
            $page = (int) floor(($a_node['counter'] - 1) / $this->max_entries);
            $this->ctrl->setParameter($this->parent_obj, 'page', $page);
        }

        if (isset($a_node['post_read']) && $a_node['post_read']) {
            $this->ctrl->setParameter($this->parent_obj, 'pos_pk', null);
            $url = $this->ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd, (string) $a_node['pos_pk']);
        } else {
            $this->ctrl->setParameter($this->parent_obj, 'pos_pk', $a_node['pos_pk']);
            $url = $this->ctrl->getLinkTarget($this->parent_obj, 'markPostRead', (string) $a_node['pos_pk']);
            $this->ctrl->setParameter($this->parent_obj, 'pos_pk', null);
        }

        $this->ctrl->setParameter($this->parent_obj, 'page', null);

        return $url;
    }

    public function getNodeContent($a_node): string
    {
        return $a_node['pos_subject'];
    }
}
