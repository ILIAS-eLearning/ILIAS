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

/**
 * Class ilForumTopicTableGUI
 * @author  Nadia Matuschek <nmatuschek@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ModulesForum
 */
class ilForumTopicTableGUI extends ilTable2GUI
{
    private ilForum $mapper;
    private bool $is_moderator = false;
    private int $ref_id = 0;
    private int $overview_setting = 0;
    private ForumDto $topicData;
    private ?ilForumTopic $merge_thread_obj = null;
    private int $position = 1;
    private bool $is_post_draft_allowed;
    private ilGlobalTemplateInterface $mainTemplate;
    private ilObjUser $user;
    private ilSetting $settings;

    public function __construct(
        ilObjForumGUI $a_parent_obj,
        string $a_parent_cmd,
        int $ref_id,
        ForumDto $topicData,
        bool $is_moderator = false,
        int $overview_setting = 0
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->mainTemplate = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->settings = $DIC->settings();

        $this->parent_cmd = $a_parent_cmd;
        $this->setIsModerator($is_moderator);
        $this->setOverviewSetting($overview_setting);
        $this->setRefId($ref_id);
        $this->setTopicData($topicData);

        $id = 'frm_tt_' . substr(md5($this->parent_cmd), 0, 3) . '_' . $this->getRefId();
        $this->setId($id);

        $this->setDefaultOrderDirection('DESC');
        $this->setDefaultOrderField('lp_date');
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->mainTemplate->addCss('./Modules/Forum/css/forum_table.css');
        $this->is_post_draft_allowed = ilForumPostDraft::isSavePostDraftAllowed();
    }

    public function init(): void
    {
        if ($this->parent_cmd === 'mergeThreads') {
            $this->initMergeThreadsTable();
        } else {
            $this->initTopicsOverviewTable();
        }
    }

    public function initTopicsOverviewTable(): void
    {
        if ($this->parent_cmd === "showThreads") {
            $this->setSelectAllCheckbox('thread_ids');
            $this->addColumn('', 'check', '1px', true);
        } else {
            $this->addColumn('', 'check', '10px', true);
        }

        $this->addColumn($this->lng->txt('forums_thread'), 'thr_subject');
        $this->addColumn($this->lng->txt('forums_created_by'), '');
        $this->addColumn($this->lng->txt('forums_articles'), 'num_posts');
        $this->addColumn($this->lng->txt('visits'), 'num_visit');

        if ($this->is_post_draft_allowed) {
            $this->addColumn($this->lng->txt('drafts', ''));
        }

        $this->addColumn($this->lng->txt('forums_last_post'), 'post_date');
        if ('showThreads' === $this->parent_cmd && $this->parent_obj->objProperties->isIsThreadRatingEnabled()) {
            $this->addColumn($this->lng->txt('frm_rating'), 'rating');
        }

        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), 'showThreads'));
        $this->setRowTemplate('tpl.forums_threads_table.html', 'Modules/Forum');

        if ($this->parent_cmd === 'sortThreads') {
            $this->addCommandButton('saveThreadSorting', $this->lng->txt('save'));
        } else {
            $this->addMultiCommand('', $this->lng->txt('please_choose'));
            if ($this->settings->get('forum_notification') > 0 && !$this->user->isAnonymous()) {
                $this->addMultiCommand('enable_notifications', $this->lng->txt('forums_enable_notification'));
                $this->addMultiCommand('disable_notifications', $this->lng->txt('forums_disable_notification'));
            }
            if ($this->getIsModerator()) {
                $this->addMultiCommand('makesticky', $this->lng->txt('make_topics_sticky'));
                $this->addMultiCommand('unmakesticky', $this->lng->txt('make_topics_non_sticky'));
                $this->addMultiCommand('editThread', $this->lng->txt('frm_edit_title'));
                $this->addMultiCommand('close', $this->lng->txt('close_topics'));
                $this->addMultiCommand('reopen', $this->lng->txt('reopen_topics'));
                $this->addMultiCommand('move', $this->lng->txt('move_thread_to_forum'));
            }
            $this->addMultiCommand('html', $this->lng->txt('export_html'));
            if ($this->getIsModerator()) {
                $this->addMultiCommand('confirmDeleteThreads', $this->lng->txt('delete'));
                $this->addMultiCommand('mergeThreads', $this->lng->txt('merge_posts_into_thread'));
            }
        }
        $this->setShowRowsSelector(true);
        $this->setRowSelectorLabel($this->lng->txt('number_of_threads'));
    }

    public function initMergeThreadsTable(): void
    {
        $this->addColumn('', 'check', '1px', true);
        $this->addColumn($this->lng->txt('forums_thread'), 'th_title');
        $this->addColumn($this->lng->txt('forums_created_by'), 'author');
        $this->addColumn($this->lng->txt('forums_articles'), 'num_posts');
        $this->addColumn($this->lng->txt('visits'), 'num_visit');
        if ($this->is_post_draft_allowed) {
            $this->addColumn($this->lng->txt('drafts', ''));
        }
        $this->addColumn($this->lng->txt('forums_last_post'), 'lp_date');

        $this->disable('sort');
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), 'confirmMergeThreads'));
        $this->setRowTemplate('tpl.forums_threads_table.html', 'Modules/Forum');

        $this->mainTemplate->setOnScreenMessage('info', $this->lng->txt('please_choose_target'));

        $this->setTitle(sprintf($this->lng->txt('frm_selected_merge_src'), $this->getSelectedThread()->getSubject()));

        $this->addCommandButton('confirmMergeThreads', $this->lng->txt('merge'));
        $this->addCommandButton('showThreads', $this->lng->txt('cancel'));
        $this->setShowRowsSelector(true);
        $this->setRowSelectorLabel($this->lng->txt('number_of_threads'));
    }

    protected function fillRow(array $a_set): void
    {
        /** @var ilForumTopic $thread */
        $thread = $a_set['thread'];

        $this->ctrl->setParameter($this->getParentObject(), 'thr_pk', $thread->getId());
        global $DIC;
        $thread_ids = [];
        if ($DIC->http()->wrapper()->post()->has('thread_ids')) {
            $thread_ids = $DIC->http()->wrapper()->post()->retrieve(
                'thread_ids',
                $DIC->refinery()->kindlyTo()->listOf($DIC->refinery()->kindlyTo()->int())
            );
        }

        if ('mergeThreads' === $this->parent_cmd) {
            $checked = $this->max_count === 1 || (isset($thread_ids) && in_array($thread->getId(), $thread_ids, true));
            $this->tpl->setVariable(
                'VAL_CHECK',
                ilLegacyFormElementsUtil::formRadioButton(
                    $checked,
                    'thread_ids[]',
                    (string) $thread->getId()
                )
            );
        } elseif ('showThreads' === $this->parent_cmd) {
            $this->tpl->setVariable(
                'VAL_CHECK',
                ilLegacyFormElementsUtil::formCheckbox(
                    (isset($thread_ids) && in_array($thread->getId(), $thread_ids, true)),
                    'thread_ids[]',
                    (string) $thread->getId()
                )
            );

            if ($this->parent_obj->objProperties->isIsThreadRatingEnabled()) {
                $rating = new ilRatingGUI();
                $rating->setObject(
                    $this->parent_obj->getObject()->getId(),
                    $this->parent_obj->getObject()->getType(),
                    $thread->getId(),
                    'thread'
                );
                $rating->setUserId($this->user->getId());
                $this->tpl->setVariable('VAL_RATING', $rating->getHTML());
            }
        } else {
            if ($thread->isSticky()) {
                $this->tpl->setVariable('VAL_SORTING_NAME', 'thread_sorting[' . $thread->getId() . ']');
                $this->tpl->setVariable('VAL_SORTING', $this->position * 10);
            } else {
                $this->tpl->setVariable('VAL_CHECK', '');
            }
            $this->position++;
        }
        $subject = '';

        if ($thread->isSticky()) {
            $subject .= '<span class="light">[' . $this->lng->txt('sticky') . ']</span> ';
        }
        if ($thread->isClosed()) {
            $subject .= '<span class="light">[' . $this->lng->txt('topic_close') . ']</span> ';
        }

        if (!$this->user->isAnonymous() &&
            (int) $this->settings->get('forum_notification', '0') !== 0 &&
            $thread->isUserNotificationEnabled()
        ) {
            $subject .= '<span class="light">[' . $this->lng->txt('forums_notification_enabled') . ']</span> ';
        }

        $num_posts = $thread->getNumPosts();
        $num_unread = $thread->getNumUnreadPosts();
        $num_new = $thread->getNumNewPosts();

        $this->ctrl->setParameter($this->getParentObject(), 'page', 0);
        $subject = '<div><a href="' . $this->ctrl->getLinkTarget(
            $this->getParentObject(),
            'viewThread'
        ) . '">' . $thread->getSubject() . '</a></div>' . $subject;
        $this->ctrl->setParameter($this->getParentObject(), 'page', null);
        $this->tpl->setVariable('VAL_SUBJECT', $subject);

        $this->ctrl->setParameter(
            $this->getParentObject(),
            'backurl',
            urlencode($this->ctrl->getLinkTargetByClass("ilrepositorygui", ""))
        );
        $this->ctrl->setParameter($this->getParentObject(), 'user', $thread->getDisplayUserId());

        $authorinfo = new ilForumAuthorInformation(
            $thread->getThrAuthorId(),
            $thread->getDisplayUserId(),
            (string) $thread->getUserAlias(),
            (string) $thread->getImportName(),
            [
                'class' => 'il_ItemProperty',
                'href' => $this->ctrl->getLinkTarget($this->getParentObject(), 'showUser')
            ]
        );
        $this->tpl->setVariable('VAL_AUTHOR', $authorinfo->getLinkedAuthorName());

        $topicStats = $num_posts;
        if (!$this->user->isAnonymous()) {
            if ($num_unread > 0) {
                $topicStats .= '<br /><span class="ilAlert ilWhiteSpaceNowrap">' . $this->lng->txt('unread') . ': ' . $num_unread . '</span>';
            }
            if ($num_new > 0 && $this->getOverviewSetting() === 0) {
                $topicStats .= '<br /><span class="ilAlert ilWhiteSpaceNowrap">' . $this->lng->txt('new') . ': ' . $num_new . '</span>';
            }
        }

        $this->tpl->setVariable('VAL_ARTICLE_STATS', $topicStats);
        $this->tpl->setVariable('VAL_NUM_VISIT', $thread->getVisits());
        if ($this->is_post_draft_allowed) {
            $draft_statistics = ilForumPostDraft::getDraftsStatisticsByRefId($this->getRefId());
            $this->tpl->setVariable(
                'VAL_DRAFTS',
                (int) isset($draft_statistics[$thread->getId()]) ? $draft_statistics[$thread->getId()] : 0
            );
        }

        if ($num_posts > 0 && $thread->getLastPostForThreadOverview() instanceof ilForumPost) {
            $objLastPost = $thread->getLastPostForThreadOverview();

            $this->ctrl->setParameter($this->getParentObject(), 'user', $objLastPost->getDisplayUserId());
            $authorinfo = new ilForumAuthorInformation(
                $objLastPost->getPosAuthorId(),
                $objLastPost->getDisplayUserId(),
                (string) $objLastPost->getUserAlias(),
                (string) $objLastPost->getImportName(),
                [
                    'href' => $this->ctrl->getLinkTarget($this->getParentObject(), 'showUser')
                ]
            );

            $this->tpl->setVariable(
                'VAL_LP_DATE',
                '<div class="ilWhiteSpaceNowrap">' . ilDatePresentation::formatDate(new ilDateTime(
                    $objLastPost->getCreateDate(),
                    IL_CAL_DATETIME
                )) . '</div>' .
                '<div class="ilWhiteSpaceNowrap">' . $this->lng->txt('from') . ' ' . $authorinfo->getLinkedAuthorName() . '</div>'
            );
        }

        $css_row = $this->css_row;
        if ($thread->isSticky()) {
            $css_row = $css_row === 'tblrow1' ? 'tblstickyrow1' : 'tblstickyrow2';
        }
        $this->tpl->setVariable('CSS_ROW', $css_row);

        $this->ctrl->setParameter($this->getParentObject(), 'thr_pk', '');
        $this->ctrl->setParameter($this->getParentObject(), 'user', '');
        $this->ctrl->setParameter($this->getParentObject(), 'backurl', '');
    }

    public function fetchData(): ilForumTopicTableGUI
    {
        $this->determineOffsetAndOrder();

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
        if (!count($data['items']) && $this->getOffset() > 0) {
            $this->resetOffset();
            $data = $this->getMapper()->getAllThreads(
                $this->topicData->getTopPk(),
                $params,
                $this->getLimit(),
                $this->getOffset()
            );
        }

        $this->setMaxCount($data['cnt']);
        $this->setData(array_map(static function (ilForumTopic $thread): array {
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

        return $this;
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

    public function setOverviewSetting(int $overview_setting): self
    {
        $this->overview_setting = $overview_setting;
        return $this;
    }

    public function getOverviewSetting(): int
    {
        return $this->overview_setting;
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

    public function getTopicData(): ForumDto
    {
        return $this->topicData;
    }

    public function setSelectedThread(ilForumTopic $thread_obj): self
    {
        $this->merge_thread_obj = $thread_obj;
        return $this;
    }

    public function getSelectedThread(): ?ilForumTopic
    {
        return $this->merge_thread_obj;
    }
}
