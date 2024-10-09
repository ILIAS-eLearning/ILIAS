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

declare(strict_types=1);

class ilForumThreadObjectTableGUI extends ilTable2GUI
{
    private ilForum $mapper;
    private bool $is_moderator = false;
    private int $ref_id = 0;
    private ForumDto $topicData;
    private ?ilForumTopic $merge_thread_obj = null;
    private readonly bool $is_post_draft_allowed;
    private readonly ilGlobalTemplateInterface $mainTemplate;
    private readonly ilObjUser $user;
    private readonly ilSetting $settings;

    public function __construct(
        ilObjForumGUI $a_parent_obj,
        string $a_parent_cmd,
        int $ref_id,
        ForumDto $topicData,
        bool $is_moderator = false
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->mainTemplate = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->settings = $DIC->settings();

        $this->parent_cmd = $a_parent_cmd;
        $this->setIsModerator($is_moderator);
        $this->setRefId($ref_id);
        $this->setTopicData($topicData);

        $id = 'frm_tt_' . substr(md5($this->parent_cmd), 0, 3) . '_' . $this->getRefId();
        $this->setId($id);

        $this->setDefaultOrderDirection('DESC');
        $this->setDefaultOrderField('lp_date');
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->mainTemplate->addCss('./components/ILIAS/Forum/css/forum_table.css');
        $this->is_post_draft_allowed = ilForumPostDraft::isSavePostDraftAllowed();
    }

    public function fetchDataAnReturnObject(): array
    {
        $excluded_ids = [];
        if ($this->parent_cmd === 'mergeThreads' &&
            $this->getSelectedThread() instanceof ilForumTopic) {
            $excluded_ids[] = $this->getSelectedThread()->getId();
        }

        $params = [
            'is_moderator' => $this->getIsModerator(),
            'excluded_ids' => $excluded_ids,
            'order_column' => $this->getOrderField(),
            'order_direction' => $this->getOrderDirection()
        ];

        $data = $this->getMapper()->getAllThreads(
            $this->topicData->getTopPk(),
            $params,
            $this->getLimit(),
            $this->getOffset()
        );
        if ($data['items'] === [] && $this->getOffset() > 0) {
            $this->resetOffset();
            $data = $this->getMapper()->getAllThreads(
                $this->topicData->getTopPk(),
                $params,
                $this->getLimit(),
                $this->getOffset()
            );
        }

        $this->setMaxCount($data['cnt']);
        $temp_data = (array_map(static function (ilForumTopic $thread): array {
            return ['thread' => $thread];
        }, $data['items']));

        $thread_ids = [];
        $user_ids = [];
        foreach ($data['items'] as $thread) {
            /** @var ilForumTopic $thread */
            $thread_ids[] = $thread->getId();
            if ($thread->getDisplayUserId() > 0) {
                $user_ids[$thread->getDisplayUserId()] = $thread->getDisplayUserId();
            }
        }

        $user_ids = array_merge(
            ilObjForum::getUserIdsOfLastPostsByRefIdAndThreadIds($this->getRefId(), $thread_ids),
            $user_ids
        );

        ilForumAuthorInformationCache::preloadUserObjects(array_unique($user_ids));

        return $temp_data;
    }

    public function setMapper(ilForum $mapper): self
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper(): ilForum
    {
        return $this->mapper;
    }

    public function setRefId(int $ref_id): self
    {
        $this->ref_id = $ref_id;
        return $this;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function setIsModerator(bool $is_moderator): self
    {
        $this->is_moderator = $is_moderator;
        return $this;
    }

    public function getIsModerator(): bool
    {
        return $this->is_moderator;
    }

    public function setTopicData(ForumDto $topicData): self
    {
        $this->topicData = $topicData;
        return $this;
    }

    public function getSelectedThread(): ?ilForumTopic
    {
        return $this->merge_thread_obj;
    }
}
