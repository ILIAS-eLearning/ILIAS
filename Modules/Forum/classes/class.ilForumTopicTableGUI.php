<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/Calendar/classes/class.ilDatePresentation.php';
require_once 'Modules/Forum/classes/class.ilForumAuthorInformation.php';
require_once 'Modules/Forum/classes/class.ilForumAuthorInformationCache.php';

/**
 * Class ilForumTopicTableGUI
 * @author  Nadia Ahmad <nahmad@databay.de>
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
	 * @param        $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param string $template_context
	 * @param int    $ref_id
	 * @param bool   $is_moderator
	 * @param string $overview_setting
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = '', $template_context = '', $ref_id = 0, $topicData = array(), $is_moderator = false, $overview_setting = '')
	{
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $lng    ilLanguage
		 * @var $tpl    ilTemplate
		 */
		global $ilCtrl, $lng, $tpl;

		$this->lng  = $lng;
		$this->ctrl = $ilCtrl;

		$this->setIsModerator($is_moderator);
		$this->setOverviewSetting($overview_setting);
		$this->setRefId($ref_id);
		$this->setTopicData($topicData);

		// Call this immediately in constructor
		$this->setId('il_frm_thread_table_' . $this->getRefId());
		$this->setPrefix('frm_threads');

		// Let the database do the work
		$this->setDefaultOrderDirection('DESC');
		$this->setDefaultOrderField('is_sticky');
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);

		parent::__construct($a_parent_obj, $a_parent_cmd, $template_context);

		// Add global css for table styles
		$tpl->addCss('./Modules/Forum/css/forum_table.css');

		// Columns
		$this->addColumn('', 'check', '1px', true);
		$this->addColumn($this->lng->txt('forums_thread'), 'th_title');
		$this->addColumn($this->lng->txt('forums_created_by'), 'author');
		$this->addColumn($this->lng->txt('forums_articles'), 'num_posts');
		$this->addColumn($this->lng->txt('visits'), 'num_visit');
		$this->addColumn($this->lng->txt('forums_last_post'), 'lp_date');

		// Disable sorting
		$this->disable('sort');
		$this->setSelectAllCheckbox('thread_ids');

		// Default Form Action
		$this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), 'showThreads'));

		// Row template
		$this->setRowTemplate('tpl.forums_threads_table.html', 'Modules/Forum');

		// Multi commands
		$this->addMultiCommand('', $this->lng->txt('please_choose'));
		if($this->ilias->getSetting('forum_notification') == 1)
		{
			$this->addMultiCommand('enable_notifications', $this->lng->txt('forums_enable_notification'));
			$this->addMultiCommand('disable_notifications', $this->lng->txt('forums_disable_notification'));
		}
		if($this->getIsModerator())
		{
			$this->addMultiCommand('makesticky', $this->lng->txt('make_topics_sticky'));
			$this->addMultiCommand('unmakesticky', $this->lng->txt('make_topics_non_sticky'));
			$this->addMultiCommand('editThread', $this->lng->txt('frm_edit_title'));
			$this->addMultiCommand('close', $this->lng->txt('close_topics'));
			$this->addMultiCommand('reopen', $this->lng->txt('reopen_topics'));
			$this->addMultiCommand('move', $this->lng->txt('move'));
		}
		$this->addMultiCommand('html', $this->lng->txt('export_html'));
		if($this->getIsModerator())
		{
			$this->addMultiCommand('confirmDeleteThreads', $this->lng->txt('delete'));
		}

		$this->setShowRowsSelector(true);
		$this->setRowSelectorLabel($this->lng->txt('number_of_threads'));
	}

	/**
	 * @param ilForumTopic $thread
	 */
	public function fillRow(ilForumTopic $thread)
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		$this->ctrl->setParameter($this->getParentObject(), 'thr_pk', $thread->getId());

		$this->tpl->setVariable('VAL_CHECK', ilUtil::formCheckbox(
			(isset($_POST['thread_ids']) && in_array($thread->getId(), $_POST['thread_ids']) ? true : false), 'thread_ids[]', $thread->getId()
		));

		$subject = '';

		if($thread->isSticky())
		{
			$subject .= '<span class="light">[' . $this->lng->txt('sticky') . ']</span> ';
		}
		if($thread->isClosed())
		{
			$subject .= '<span class="light">[' . $this->lng->txt('topic_close') . ']</span> ';
		}

		if($ilUser->getId() != ANONYMOUS_USER_ID &&
			$this->ilias->getSetting('forum_notification') != 0 &&
			$thread->getUserNotificationEnabled()
		)
		{
			$subject .= '<span class="light">[' . $this->lng->txt('forums_notification_enabled') . ']</span> ';
		}

		$num_posts  = $thread->getNumPosts();
		$num_unread = $thread->getNumUnreadPosts();
		$num_new    = $thread->getNumNewPosts();

		if($num_posts > 0)
		{
			$subject = '<div><a href="' . $this->ctrl->getLinkTarget($this->getParentObject(), 'viewThread') . '">' . $thread->getSubject() . '</a></div>' . $subject;
		}
		else
		{
			$subject = $thread->getSubject() . $subject;
		}
		$this->tpl->setVariable('VAL_SUBJECT', $subject);

		// Author
		$this->ctrl->setParameter($this->getParentObject(), 'backurl',
			urlencode($this->ctrl->getLinkTargetByClass("ilrepositorygui", "")));
		$this->ctrl->setParameter($this->getParentObject(), 'user', $thread->getUserId());

		$authorinfo = new ilForumAuthorInformation(
			$thread->getUserId(),
			$thread->getUserAlias(),
			$thread->getImportName(),
			array(
				 'class' => 'il_ItemProperty',
				 'href'  => $this->ctrl->getLinkTarget($this->getParentObject(), 'showUser')
			)
		);
		$this->tpl->setVariable('VAL_AUTHOR', $authorinfo->getLinkedAuthorName());

		$topicStats = $num_posts;
		if($ilUser->getId() != ANONYMOUS_USER_ID)
		{
			if($num_unread > 0)
			{
				$topicStats .= '<br /><span class="alert" style="white-space:nowrap">' . $this->lng->txt('unread') . ': ' . $num_unread . '</span>';
			}
			if($num_new > 0 && $this->getOverviewSetting() == 0)
			{
				$topicStats .= '<br /><span class="alert" style="white-space:nowrap">' . $this->lng->txt('new') . ': ' . $num_new . '</span>';
			}
		}

		$this->tpl->setVariable('VAL_ARTICLE_STATS', $topicStats);
		$this->tpl->setVariable('VAL_NUM_VISIT', $thread->getVisits());

		$this->ctrl->clearParameters($this->getParentObject());

		// Last posting
		if($num_posts > 0)
		{
			if($this->getIsModerator())
			{
				$objLastPost = $thread->getLastPost();
			}
			else
			{
				$objLastPost = $thread->getLastActivePost();
			}

			if(is_object($objLastPost))
			{
				$this->ctrl->setParameter($this->getParentObject(), 'thr_pk', $thread->getId());
				$this->ctrl->setParameter($this->getParentObject(), 'thr_pk', $objLastPost->getThreadId());

				$authorinfo = new ilForumAuthorInformation(
					$objLastPost->getUserId(),
					$objLastPost->getUserAlias(),
					$objLastPost->getImportName(),
					array(
						 'href' => $this->ctrl->getLinkTarget($this->getParentObject(), 'viewThread') . '#' . $objLastPost->getId()
					)
				);

				$this->tpl->setVariable('VAL_LP_DATE', '<div style="white-space:nowrap">' . ilDatePresentation::formatDate(new ilDateTime($objLastPost->getCreateDate(), IL_CAL_DATETIME)) . '</div>' .
					'<div style="white-space:nowrap">' . $this->lng->txt('from') . ' ' . $authorinfo->getLinkedAuthorName() . '</div>'
				);

				$this->ctrl->clearParameters($this->getParentObject());
			}
		}

		// Row style
		$css_row = $this->css_row;
		if($thread->isSticky())
		{
			$css_row = $css_row == 'tblrow1' ? 'tblstickyrow1' : 'tblstickyrow2';
		}
		$this->tpl->setVariable('CSS_ROW', $css_row);
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
		global $ilAccess;

		$this->determineOffsetAndOrder();

		$data = $this->getMapper()->getAllThreads($this->topicData['top_pk'], $this->getIsModerator(), (int)$this->getLimit(), (int)$this->getOffset());
		if(!count($data['items']) && $this->getOffset() > 0)
		{
			$this->resetOffset();
			$data = $this->getMapper()->getAllThreads($this->topicData['top_pk'], $this->getIsModerator(), (int)$this->getLimit(), (int)$this->getOffset());
		}

		$this->setMaxCount($data['cnt']);
		$this->setData($data['items']);

		// Collect user ids for preloading user objects
		$thread_ids = array();
		$user_ids   = array();
		foreach($data['items'] as $thread)
		{
			/**
			 * @var $thread ilForumTopic
			 */
			$thread_ids[] = (int)$thread->getId();
			if($thread->getUserId() > 0)
			{
				$user_ids[$thread->getUserId()] = (int)$thread->getUserId();
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
}