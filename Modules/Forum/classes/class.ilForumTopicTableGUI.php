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
		$this->ctrl->setParameter($this->getParentObject(), 'backurl', urlencode('repository.php?ref_id=' . $_GET['ref_id']));
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
	
		function fillFooter()
	{
		global $lng, $ilCtrl, $ilUser;

		$footer = false;
		
		// select all checkbox
		if ((strlen($this->getFormName())) && (strlen($this->getSelectAllCheckbox())) && $this->dataExists())
		{
			$this->tpl->setCurrentBlock("select_all_checkbox");
			$this->tpl->setVariable("SELECT_ALL_TXT_SELECT_ALL", $lng->txt("select_all"));
			$this->tpl->setVariable("SELECT_ALL_CHECKBOX_NAME", $this->getSelectAllCheckbox());
			$this->tpl->setVariable("SELECT_ALL_FORM_NAME", $this->getFormName());
			$this->tpl->setVariable("CHECKBOXNAME", "chb_select_all_" . $this->unique_id);
			$this->tpl->parseCurrentBlock();
		}
		
		// table footer numinfo
		if ($this->enabled["numinfo"] && $this->enabled["footer"])
		{
			$start = $this->offset + 1;				// compute num info
			if (!$this->dataExists())
			{
				$start = 0;
			}
			$end = $this->offset + $this->limit;
			
			if ($end > $this->max_count or $this->limit == 0)
			{
				$end = $this->max_count;
			}
			
			if ($this->max_count > 0)
			{
				if ($this->lang_support)
				{
					$numinfo = "(".$start." - ".$end." ".strtolower($this->lng->txt("of"))." ".$this->max_count.")";
				}
				else
				{
					$numinfo = "(".$start." - ".$end." of ".$this->max_count.")";
				}
			}
			if ($this->max_count > 0)
			{
				if ($this->getEnableNumInfo())
				{
					$this->tpl->setCurrentBlock("tbl_footer_numinfo");
					$this->tpl->setVariable("NUMINFO", $numinfo);
					$this->tpl->parseCurrentBlock();
				}
			}
			$footer = true;
		}

		// table footer linkbar
		if ($this->enabled["linkbar"] && $this->enabled["footer"] && $this->limit  != 0
			 && $this->max_count > 0)
		{
			$layout = array(
							"link"	=> $this->footer_style,
							"prev"	=> $this->footer_previous,
							"next"	=> $this->footer_next,
							);
			//if (!$this->getDisplayAsBlock())
			//{
				$linkbar = $this->getLinkbar("1");
				$this->tpl->setCurrentBlock("tbl_footer_linkbar");
				$this->tpl->setVariable("LINKBAR", $linkbar);
				$this->tpl->parseCurrentBlock();
				$linkbar = true;
			//}
			$footer = true;
		}
		
		// column selector
		if (count($this->getSelectableColumns()) > 0)
		{
			$items = array();
			foreach ($this->getSelectableColumns() as $k => $c)
			{
				$items[$k] = array("txt" => $c["txt"],
					"selected" => $this->isColumnSelected($k));
			}
			include_once("./Services/UIComponent/CheckboxListOverlay/classes/class.ilCheckboxListOverlayGUI.php");
			$cb_over = new ilCheckboxListOverlayGUI("tbl_".$this->getId());
			$cb_over->setLinkTitle($lng->txt("columns"));
			$cb_over->setItems($items);
			//$cb_over->setUrl("./ilias.php?baseClass=ilTablePropertiesStorage&table_id=".
			//		$this->getId()."&cmd=saveSelectedFields&user_id=".$ilUser->getId());
			$cb_over->setFormCmd($this->getParentCmd());
			$cb_over->setFieldVar("tblfs".$this->getId());
			$cb_over->setHiddenVar("tblfsh".$this->getId());
			$cb_over->setSelectionHeaderClass("ilTableMenuItem");
			$column_selector = $cb_over->getHTML();
			$footer = true;
		}

		if($this->getShowTemplates() && is_object($ilUser))
		{
			// template handling
			if(isset($_REQUEST["tbltplcrt"]) && $_REQUEST["tbltplcrt"])
			{
				if($this->saveTemplate($_REQUEST["tbltplcrt"]))
				{
					ilUtil::sendSuccess($lng->txt("tbl_template_created"));
				}
			}
			else if(isset($_REQUEST["tbltpldel"]) && $_REQUEST["tbltpldel"])
			{
				if($this->deleteTemplate($_REQUEST["tbltpldel"]))
				{
					ilUtil::sendSuccess($lng->txt("tbl_template_deleted"));
				}
			}

			$create_id = "template_create_overlay_".$this->getId();
			$delete_id = "template_delete_overlay_".$this->getId();
			$list_id = "template_stg_".$this->getId();

			include_once("./Services/Table/classes/class.ilTableTemplatesStorage.php");
			$storage = new ilTableTemplatesStorage();
			$templates = $storage->getNames($this->getContext(), $ilUser->getId());
			
			include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");

			// form to delete template
			if(sizeof($templates))
			{
				$overlay = new ilOverlayGUI($delete_id);
				$overlay->setTrigger($list_id."_delete");
				$overlay->setAnchor("ilAdvSelListAnchorElement_".$list_id);
				$overlay->setAutoHide(false);
				$overlay->add();

				$this->tpl->setCurrentBlock("template_editor_delete_item");
				$this->tpl->setVariable("TEMPLATE_DELETE_OPTION", "");
				$this->tpl->parseCurrentBlock();
				foreach($templates as $name)
				{
					$this->tpl->setVariable("TEMPLATE_DELETE_OPTION", $name);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("template_editor_delete");
				$this->tpl->setVariable("TEMPLATE_DELETE_ID", $delete_id);
				$this->tpl->setVariable("TXT_TEMPLATE_DELETE", $lng->txt("tbl_template_delete"));
				$this->tpl->setVariable("TXT_TEMPLATE_DELETE_SUBMIT", $lng->txt("delete"));
				$this->tpl->setVariable("TEMPLATE_DELETE_CMD", $this->parent_cmd);
				$this->tpl->parseCurrentBlock();
			}


			// form to save new template
			$overlay = new ilOverlayGUI($create_id);
			$overlay->setTrigger($list_id."_create");
			$overlay->setAnchor("ilAdvSelListAnchorElement_".$list_id);
			$overlay->setAutoHide(false);
			$overlay->add();

			$this->tpl->setCurrentBlock("template_editor");
			$this->tpl->setVariable("TEMPLATE_CREATE_ID", $create_id);
			$this->tpl->setVariable("TXT_TEMPLATE_CREATE", $lng->txt("tbl_template_create"));
			$this->tpl->setVariable("TXT_TEMPLATE_CREATE_SUBMIT", $lng->txt("save"));
			$this->tpl->setVariable("TEMPLATE_CREATE_CMD", $this->parent_cmd);
			$this->tpl->parseCurrentBlock();

			// load saved template
			include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
			$alist = new ilAdvancedSelectionListGUI();
			$alist->setId($list_id);
			$alist->addItem($lng->txt("tbl_template_create"), "create", "#");
			if(sizeof($templates))
			{
				$alist->addItem($lng->txt("tbl_template_delete"), "delete", "#");
				foreach($templates as $name)
				{
					$ilCtrl->setParameter($this->parent_obj, $this->prefix."_tpl", urlencode($name));
					$alist->addItem($name, $name, $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
					$ilCtrl->setParameter($this->parent_obj, $this->prefix."_tpl", "");
				}
			}
			$alist->setListTitle($lng->txt("tbl_templates"));
			$this->tpl->setVariable("TEMPLATE_SELECTOR", "&nbsp;".$alist->getHTML());
		}

		if ($footer)
		{
			$this->tpl->setCurrentBlock("tbl_footer");
			$this->tpl->setVariable("COLUMN_COUNT", $this->getColumnCount());
			if ($this->getDisplayAsBlock())
			{
				$this->tpl->setVariable("BLK_CLASS", "Block");
			}
			$this->tpl->parseCurrentBlock();
			
			// top navigation, if number info or linkbar given
			if ($numinfo != "" || $linkbar != "" || $column_selector != "" ||
				count($this->filters) > 0 || count($this->optional_filters) > 0)
			{
				if (is_object($ilUser) && (count($this->filters) || count($this->optional_filters)))
				{
					$this->tpl->setCurrentBlock("filter_activation");
					$this->tpl->setVariable("TXT_ACTIVATE_FILTER", $lng->txt("show_filter"));
					$this->tpl->setVariable("FILA_ID", $this->getId());
					if ($this->getId() != "")
					{
						$this->tpl->setVariable("SAVE_URLA", "./ilias.php?baseClass=ilTablePropertiesStorage&table_id=".
							$this->getId()."&cmd=showFilter&user_id=".$ilUser->getId());
					}
					$this->tpl->parseCurrentBlock();

					
					if (!$this->getDisableFilterHiding())
					{
						$this->tpl->setCurrentBlock("filter_deactivation");
						$this->tpl->setVariable("TXT_HIDE", $lng->txt("hide_filter"));
						if ($this->getId() != "")
						{
							$this->tpl->setVariable("SAVE_URL", "./ilias.php?baseClass=ilTablePropertiesStorage&table_id=".
								$this->getId()."&cmd=hideFilter&user_id=".$ilUser->getId());
							$this->tpl->setVariable("FILD_ID", $this->getId());
						}
						$this->tpl->parseCurrentBlock();
					}
					
				}
				
				if ($numinfo != "" && $this->getEnableNumInfo())
				{
					$this->tpl->setCurrentBlock("top_numinfo");
					$this->tpl->setVariable("NUMINFO", $numinfo);
					$this->tpl->parseCurrentBlock();
				}
				if ($linkbar != "" && !$this->getDisplayAsBlock())
				{
					$linkbar = $this->getLinkbar("2");
					$this->tpl->setCurrentBlock("top_linkbar");
					$this->tpl->setVariable("LINKBAR", $linkbar);
					$this->tpl->parseCurrentBlock();
				}
				
				// column selector
				$this->tpl->setVariable("COLUMN_SELECTOR", $column_selector);
				
				// row selector
				if ($this->getShowRowsSelector() && is_object($ilUser))
				{
					include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
					$alist = new ilAdvancedSelectionListGUI();
					$alist->setId("sellst_rows");
					$hpp = ($ilUser->getPref("hits_per_page") != 9999)
						? $ilUser->getPref("hits_per_page")
						: $lng->txt("unlimited");
	
					$options = array(0 => $lng->txt("default")." (".$hpp.")",5 => 5, 10 => 10, 15 => 15, 20 => 20,
									 30 => 30, 40 => 40, 50 => 50,
									 100 => 100, 200 => 200, 400 => 400, 800 => 800);
					foreach ($options as $k => $v)
					{
						$ilCtrl->setParameter($this->parent_obj, $this->prefix."_trows", $k);
						$alist->addItem($v, $k, $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
						$ilCtrl->setParameter($this->parent_obj, $this->prefix."_trows", "");
					}
					$alist->setListTitle($lng->txt("number_of_threads"));
					$this->tpl->setVariable("ROW_SELECTOR", $alist->getHTML());
				}

				// export
				if(sizeof($this->export_formats) && $this->dataExists())
				{
					$map = array(self::EXPORT_EXCEL => "tbl_export_excel",
						self::EXPORT_CSV => "tbl_export_csv");
					include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
					$alist = new ilAdvancedSelectionListGUI();
					$alist->setId("sellst_xpt");
					foreach($this->export_formats as $format)
					{
						$ilCtrl->setParameter($this->parent_obj, $this->prefix."_xpt", $format);
						$url = $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd);
						$ilCtrl->setParameter($this->parent_obj, $this->prefix."_xpt", "");
						$alist->addItem($lng->txt($map[$format]), $format, $url);
					}
					$alist->setListTitle($lng->txt("export"));
					$this->tpl->setVariable("EXPORT_SELECTOR", "&nbsp;".$alist->getHTML());
				}
				
				$this->tpl->setCurrentBlock("top_navigation");
				$this->tpl->setVariable("COLUMN_COUNT", $this->getColumnCount());
				if ($this->getDisplayAsBlock())
				{
					$this->tpl->setVariable("BLK_CLASS", "Block");
				}
				$this->tpl->parseCurrentBlock();
			}
		}
	}
}