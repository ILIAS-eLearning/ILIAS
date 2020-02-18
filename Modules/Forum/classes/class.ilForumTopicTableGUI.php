<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumTopicTableGUI
 * @author  Nadia Matuschek <nmatuschek@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ModulesForum
 */
class ilForumTopicTableGUI extends ilTable2GUI
{
    /**
     * @var ilForum
     */
    protected $mapper;

    /**
     * @var bool
     */
    protected $is_moderator = false;

    /**
     * @var int
     */
    protected $ref_id = 0;

    /**
     * @var string
     */
    protected $overview_setting = '';

    /**
     * @var array
     */
    protected $topicData = array();

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilForumTopic
     */
    protected $merge_thread_obj = null;

    /**
     * @var int for displaying thread_sorting position
     */
    public $position = 1;
    
    /**
     * @var bool
     */
    public $is_post_draft_allowed = false;

    /**
     * @var \ilTemplate
     */
    protected $mainTemplate;
    
    private $user;
    private $settings;

    /**
     * @param        $a_parent_obj
     * @param string $a_parent_cmd
     * @param string $template_context
     * @param int    $ref_id
     * @param bool   $is_moderator
     * @param string $overview_setting
     */
    public function __construct($a_parent_obj, $a_parent_cmd = '', $template_context = '', $ref_id = 0, $topicData = array(), $is_moderator = false, $overview_setting = '')
    {
        global $DIC;

        $this->lng  = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->mainTemplate = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->settings = $DIC->settings();
        
        $this->parent_cmd = $a_parent_cmd;
        $this->setIsModerator($is_moderator);
        $this->setOverviewSetting($overview_setting);
        $this->setRefId($ref_id);
        $this->setTopicData($topicData);

        // Call this immediately in constructor
        $id = 'frm_tt_' . substr(md5($this->parent_cmd), 0, 3) . '_' . $this->getRefId();
        $this->setId($id);

        // Let the database do the work
        $this->setDefaultOrderDirection('DESC');
        $this->setDefaultOrderField('lp_date');
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);

        parent::__construct($a_parent_obj, $a_parent_cmd, $template_context);

        // Add global css for table styles
        $this->mainTemplate->addCss('./Modules/Forum/css/forum_table.css');
        
        $this->is_post_draft_allowed = ilForumPostDraft::isSavePostDraftAllowed();
    }
    
    public function init()
    {
        if ($this->parent_cmd == 'mergeThreads') {
            $this->initMergeThreadsTable();
        } else {
            $this->initTopicsOverviewTable();
        }
    }

    /**
     *
     */
    public function initTopicsOverviewTable()
    {
        if ($this->parent_cmd  == "showThreads") {
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
        if ('showThreads' == $this->parent_cmd && $this->parent_obj->objProperties->isIsThreadRatingEnabled()) {
            $this->addColumn($this->lng->txt('frm_rating'), 'rating');
        }

        // Default Form Action
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), 'showThreads'));

        // Row template
        $this->setRowTemplate('tpl.forums_threads_table.html', 'Modules/Forum');

        if ($this->parent_cmd == 'sortThreads') {
            $this->addCommandButton('saveThreadSorting', $this->lng->txt('save'));
        } else {
            // Multi commands
            $this->addMultiCommand('', $this->lng->txt('please_choose'));
            if ($this->settings->get('forum_notification') > 0  && !$this->user->isAnonymous()) {
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
                $this->addMultiCommand('merge', $this->lng->txt('merge_posts_into_thread'));
            }
        }
        $this->setShowRowsSelector(true);
        $this->setRowSelectorLabel($this->lng->txt('number_of_threads'));
    }
    
    public function initMergeThreadsTable()
    {
        // Columns
        $this->addColumn('', 'check', '1px', true);
        $this->addColumn($this->lng->txt('forums_thread'), 'th_title');
        $this->addColumn($this->lng->txt('forums_created_by'), 'author');
        $this->addColumn($this->lng->txt('forums_articles'), 'num_posts');
        $this->addColumn($this->lng->txt('visits'), 'num_visit');
        if ($this->is_post_draft_allowed) {
            $this->addColumn($this->lng->txt('drafts', ''));
        }
        $this->addColumn($this->lng->txt('forums_last_post'), 'lp_date');
    
        // Disable sorting
        $this->disable('sort');

        // Default Form Action
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), 'confirmMergeThreads'));

        // Row template
        $this->setRowTemplate('tpl.forums_threads_table.html', 'Modules/Forum');

        ilUtil::sendInfo($this->lng->txt('please_choose_target'));
        
        $this->setTitle(sprintf($this->lng->txt('frm_selected_merge_src'), $this->getSelectedThread()->getSubject()));
        
        $this->addCommandButton('confirmMergeThreads', $this->lng->txt('merge'));
        $this->addCommandButton('showThreads', $this->lng->txt('cancel'));
        $this->setShowRowsSelector(true);
        $this->setRowSelectorLabel($this->lng->txt('number_of_threads'));
    }

    /**
     * @param ilForumTopic $thread
     */
    public function fillRow($thread)
    {
        $this->ctrl->setParameter($this->getParentObject(), 'thr_pk', $thread->getId());
        if ('mergeThreads' == $this->parent_cmd) {
            $checked = $this->max_count == 1 || (isset($_POST['thread_ids']) && in_array($thread->getId(), $_POST['thread_ids']));
            $this->tpl->setVariable('VAL_CHECK', ilUtil::formRadioButton(
                $checked,
                'thread_ids[]',
                $thread->getId()
            ));
        } elseif ('showThreads' == $this->parent_cmd) {
            $this->tpl->setVariable('VAL_CHECK', ilUtil::formCheckbox(
                (isset($_POST['thread_ids']) && in_array($thread->getId(), $_POST['thread_ids']) ? true : false),
                'thread_ids[]',
                $thread->getId()
            ));

            if ($this->parent_obj->objProperties->isIsThreadRatingEnabled()) {
                $rating = new ilRatingGUI();
                $rating->setObject($this->parent_obj->object->getId(), $this->parent_obj->object->getType(), $thread->getId(), 'thread');
                $rating->setUserId($this->user->getId());
                $this->tpl->setVariable('VAL_RATING', $rating->getHTML());
            }
        } else {
            if ($thread->isSticky()) {
                $this->tpl->setVariable('VAL_SORTING_NAME', 'thread_sorting[' . $thread->getId() . ']');
                $this->tpl->setVariable('VAL_SORTING', (int) $this->position * 10);
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
            $this->settings->get('forum_notification') != 0 &&
            $thread->getUserNotificationEnabled()
        ) {
            $subject .= '<span class="light">[' . $this->lng->txt('forums_notification_enabled') . ']</span> ';
        }

        $num_posts  = $thread->getNumPosts();
        $num_unread = $thread->getNumUnreadPosts();
        $num_new    = $thread->getNumNewPosts();

        $subject = '<div><a href="' . $this->ctrl->getLinkTarget($this->getParentObject(), 'viewThread') . '">' . $thread->getSubject() . '</a></div>' . $subject;
        $this->tpl->setVariable('VAL_SUBJECT', $subject);

        // Author
        $this->ctrl->setParameter($this->getParentObject(), 'backurl', urlencode($this->ctrl->getLinkTargetByClass("ilrepositorygui", "")));
        $this->ctrl->setParameter($this->getParentObject(), 'user', $thread->getDisplayUserId());

        $authorinfo = new ilForumAuthorInformation(
            $thread->getThrAuthorId(),
            $thread->getDisplayUserId(),
            $thread->getUserAlias(),
            $thread->getImportName(),
            array(
                 'class' => 'il_ItemProperty',
                 'href'  => $this->ctrl->getLinkTarget($this->getParentObject(), 'showUser')
            )
        );
        $this->tpl->setVariable('VAL_AUTHOR', $authorinfo->getLinkedAuthorName());

        $topicStats = $num_posts;
        if (!$this->user->isAnonymous()) {
            if ($num_unread > 0) {
                $topicStats .= '<br /><span class="ilAlert ilWhiteSpaceNowrap">' . $this->lng->txt('unread') . ': ' . $num_unread . '</span>';
            }
            if ($num_new > 0 && $this->getOverviewSetting() == 0) {
                $topicStats .= '<br /><span class="ilAlert ilWhiteSpaceNowrap">' . $this->lng->txt('new') . ': ' . $num_new . '</span>';
            }
        }

        $this->tpl->setVariable('VAL_ARTICLE_STATS', $topicStats);
        $this->tpl->setVariable('VAL_NUM_VISIT', $thread->getVisits());
        if ($this->is_post_draft_allowed) {
            $draft_statistics = ilForumPostDraft::getDraftsStatisticsByRefId($this->getRefId());
            $this->tpl->setVariable('VAL_DRAFTS', (int) $draft_statistics[$thread->getId()]);
        }
        // Last posting
        if ($num_posts > 0) {
            if ($thread->getLastPostForThreadOverview() instanceof ilForumPost) {
                $objLastPost = $thread->getLastPostForThreadOverview();
                $authorinfo = new ilForumAuthorInformation(
                    $objLastPost->getPosAuthorId(),
                    $objLastPost->getDisplayUserId(),
                    $objLastPost->getUserAlias(),
                    $objLastPost->getImportName(),
                    array(
                         'href' => $this->ctrl->getLinkTarget($this->getParentObject(), 'viewThread') . '#' . $objLastPost->getId()
                    )
                );

                $this->tpl->setVariable(
                    'VAL_LP_DATE',
                    '<div class="ilWhiteSpaceNowrap">' . ilDatePresentation::formatDate(new ilDateTime($objLastPost->getCreateDate(), IL_CAL_DATETIME)) . '</div>' .
                    '<div class="ilWhiteSpaceNowrap">' . $this->lng->txt('from') . ' ' . $authorinfo->getLinkedAuthorName() . '</div>'
                );
            }
        }

        // Row style
        $css_row = $this->css_row;
        if ($thread->isSticky()) {
            $css_row = $css_row == 'tblrow1' ? 'tblstickyrow1' : 'tblstickyrow2';
        }
        $this->tpl->setVariable('CSS_ROW', $css_row);

        $this->ctrl->setParameter($this->getParentObject(), 'thr_pk', '');
        $this->ctrl->setParameter($this->getParentObject(), 'user', '');
        $this->ctrl->setParameter($this->getParentObject(), 'backurl', '');
    }

    /**
     * * Currently not used because of external segmentation and sorting and formatting in fillRow
     * @param string $cell
     * @param mixed  $value
     * @return mixed
     */
    protected function formatCellValue($cell, $value)
    {
        return $value;
    }

    /**
     * Currently not used because of external segmentation and sorting
     * @param string $column
     * @return bool
     */
    public function numericOrdering($column)
    {
        return false;
    }

    /**
     * @return ilForumTopicTableGUI
     */
    public function fetchData()
    {
        $this->determineOffsetAndOrder();

        $excluded_ids = array();
        if ($this->parent_cmd == 'mergeThreads' &&
           $this->getSelectedThread() instanceof ilForumTopic) {
            $excluded_ids[] = $this->getSelectedThread()->getId();
        }
        
        $params = array(
            'is_moderator'    => $this->getIsModerator(),
            'excluded_ids'    => $excluded_ids,
            'order_column'    => $this->getOrderField(),
            'order_direction' => $this->getOrderDirection()
        );

        $data = $this->getMapper()->getAllThreads($this->topicData['top_pk'], $params, (int) $this->getLimit(), (int) $this->getOffset());
        if (!count($data['items']) && $this->getOffset() > 0) {
            $this->resetOffset();
            $data = $this->getMapper()->getAllThreads($this->topicData['top_pk'], $params, (int) $this->getLimit(), (int) $this->getOffset());
        }

        $this->setMaxCount($data['cnt']);
        $this->setData($data['items']);

        // Collect user ids for preloading user objects
        $thread_ids = array();
        $user_ids   = array();
        foreach ($data['items'] as $thread) {
            /**
             * @var $thread ilForumTopic
             */
            $thread_ids[] = (int) $thread->getId();
            if ($thread->getDisplayUserId() > 0) {
                $user_ids[$thread->getDisplayUserId()] = (int) $thread->getDisplayUserId();
            }
        }

        $user_ids = array_merge(
            ilObjForum::getUserIdsOfLastPostsByRefIdAndThreadIds($this->getRefId(), $thread_ids),
            $user_ids
        );

        ilForumAuthorInformationCache::preloadUserObjects(array_unique($user_ids));

        return $this;
    }

    /**
     * @param ilForum $mapper
     * @return ilForumTopicTableGUI
     */
    public function setMapper(ilForum $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return ilForum
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @param int $ref_id
     * @return ilForumTopicTableGUI
     */
    public function setRefId($ref_id)
    {
        $this->ref_id = $ref_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getRefId()
    {
        return $this->ref_id;
    }

    /**
     * @param string $overview_setting
     * @return ilForumTopicTableGUI
     */
    public function setOverviewSetting($overview_setting)
    {
        $this->overview_setting = $overview_setting;
        return $this;
    }

    /**
     * @return string
     */
    public function getOverviewSetting()
    {
        return $this->overview_setting;
    }

    /**
     * @param bool $is_moderator
     * @return ilForumTopicTableGUI
     */
    public function setIsModerator($is_moderator)
    {
        $this->is_moderator = $is_moderator;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsModerator()
    {
        return $this->is_moderator;
    }

    /**
     * @param array $topicData
     * @return ilForumTopicTableGUI
     */
    public function setTopicData($topicData)
    {
        $this->topicData = $topicData;
        return $this;
    }

    /**
     * @return array
     */
    public function getTopicData()
    {
        return $this->topicData;
    }

    /**
     * @param ilForumTopic $thread_obj
     */
    public function setSelectedThread(ilForumTopic $thread_obj)
    {
        $this->merge_thread_obj = $thread_obj;
    }

    /**
     * @return ilForumTopic
     */
    public function getSelectedThread()
    {
        return $this->merge_thread_obj;
    }
}
