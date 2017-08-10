<?php 
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumSettingsGUI
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumSettingsGUI
{
	private $ctrl;
	private $tpl;
	private $lng;
	private $settings;
	private $tabs;
	private $access;
	private $tree;
	private $parent_obj;
	
	/**
	 * @var ilPropertyFormGUI
	 */
	protected  $form;
	
	/**
	 * ilForumSettingsGUI constructor.
	 * @param $parent_obj
	 */
	public function __construct($parent_obj)
	{
		global $DIC;
		
		$this->parent_obj = $parent_obj;
		$this->ctrl = $DIC->ctrl();
		$this->tpl = $DIC->ui()->mainTemplate();
		$this->lng = $DIC->language();
		$this->settings = $DIC->settings();
		$this->tabs = $DIC->tabs();
		$this->access = $DIC->access();
		$this->tree = $DIC->repositoryTree();
	}
	
	/**
	 * @param ilPropertyFormGUI $a_form
	 */
	public function getCustomForm(&$a_form)
	{
		$this->settingsTabs();
		
		//sorting for sticky threads
		$cb_sort = new ilCheckboxInputGUI($this->lng->txt('sorting_manual_sticky'),	'thread_sorting');
		$cb_sort->setValue('1');
		$cb_sort->setInfo($this->lng->txt('sticky_threads_always_on_top'));
		$a_form->addItem($cb_sort);
		
		// sorting for postings
		$rg_pro = new ilRadioGroupInputGUI($this->lng->txt('frm_default_view'), 'default_view');
		
		$rg_pro->addOption(new ilRadioOption($this->lng->txt('sort_by_posts'), ilForumProperties::VIEW_TREE));
		$rg_sort_by_date = new ilRadioOption($this->lng->txt('sort_by_date'), ilForumProperties::VIEW_DATE);
		$rg_pro->addOption($rg_sort_by_date);
		
		$view_direction_group_gui = new ilRadioGroupInputGUI('', 'default_view_sort_dir');
		$view_desc = new ilRadioOption($this->lng->txt('descending_order'), ilForumProperties::VIEW_DATE_DESC);
		$view_asc = new ilRadioOption($this->lng->txt('ascending_order'), ilForumProperties::VIEW_DATE_ASC);
		$view_direction_group_gui->addOption($view_desc);
		$view_direction_group_gui->addOption($view_asc);
		
		$rg_sort_by_date->addSubItem($view_direction_group_gui);
		$a_form->addItem($rg_pro);
		
		if($this->settings->get('enable_anonymous_fora') || $this->parent_obj->objProperties->isAnonymized())
		{
			$cb_prop = new ilCheckboxInputGUI($this->lng->txt('frm_anonymous_posting'),	'anonymized');
			$cb_prop->setValue('1');
			$cb_prop->setInfo($this->lng->txt('frm_anonymous_posting_desc'));
			$a_form->addItem($cb_prop);
		}
		
		$cb_prop = new ilCheckboxInputGUI($this->lng->txt('mark_moderator_posts'), 'mark_mod_posts');
		$cb_prop->setValue('1');
		$cb_prop->setInfo($this->lng->txt('mark_moderator_posts_desc'));
		$a_form->addItem($cb_prop);
		
		if($this->settings->get('enable_fora_statistics', false))
		{
			$cb_prop = new ilCheckboxInputGUI($this->lng->txt('frm_statistics_enabled'), 'statistics_enabled');
			$cb_prop->setValue('1');
			$cb_prop->setInfo($this->lng->txt('frm_statistics_enabled_desc'));
			$a_form->addItem($cb_prop);
		}
		
		$cb_prop = new ilCheckboxInputGUI($this->lng->txt('activate_new_posts'), 'post_activation');
		$cb_prop->setValue('1');
		$cb_prop->setInfo($this->lng->txt('post_activation_desc'));
		$a_form->addItem($cb_prop);
		
		$frm_subject = new ilRadioGroupInputGUI($this->lng->txt('frm_subject_setting'), 'subject_setting');
		$frm_subject->addOption(new ilRadioOption($this->lng->txt('preset_subject'), 'preset_subject'));
		$frm_subject->addOption(new ilRadioOption($this->lng->txt('add_re_to_subject'), 'add_re_to_subject'));
		$frm_subject->addOption(new ilRadioOption($this->lng->txt('empty_subject'), 'empty_subject'));
		$a_form->addItem($frm_subject);
		
		$cb_prop = new ilCheckboxInputGUI($this->lng->txt('enable_thread_ratings'), 'thread_rating');
		$cb_prop->setValue(1);
		$cb_prop->setInfo($this->lng->txt('enable_thread_ratings_info'));
		$a_form->addItem($cb_prop);
		
		if(!ilForumProperties::isFileUploadGloballyAllowed())
		{
			$frm_upload = new ilCheckboxInputGUI($this->lng->txt('file_upload_allowed'), 'file_upload_allowed');
			$frm_upload->setValue(1);
			$frm_upload->setInfo($this->lng->txt('allow_file_upload_desc'));
			$a_form->addItem($frm_upload);
		}
	}
	
	/**
	 * @return bool
	 */
	public function settingsTabs()
	{
		$this->tabs->activateTab('settings');
		$this->tabs->addSubTabTarget('basic_settings', $this->ctrl->getLinkTarget($this, 'edit'), 'edit', get_class($this), '', $_GET['cmd']=='edit'? true : false );
		
		// notification tab
		if($this->settings->get('forum_notification') > 0)
		{
			// check if there a parent-node is a grp or crs
			$grp_ref_id = $this->tree->checkForParentType($this->parent_obj->ref_id, 'grp');
			$crs_ref_id = $this->tree->checkForParentType($this->parent_obj->ref_id, 'crs');
			
			if((int)$grp_ref_id > 0 || (int)$crs_ref_id > 0 )
			{
				#show member-tab for notification if forum-notification is enabled in administration
				if($this->access->checkAccess('write', '', $this->parent_obj->ref_id))
				{
					$mem_active = array('showMembers', 'forums_notification_settings');
					(in_array($_GET['cmd'],$mem_active)) ? $force_mem_active = true : $force_mem_active = false;
					
					$this->tabs->addSubTabTarget('notifications', $this->ctrl->getLinkTarget($this, 'showMembers'), $_GET['cmd'], get_class($this), '', $force_mem_active);
				}
			}
		}
		return true;
	}
	
	/**
	 * @param array $a_values
	 */
	public function getCustomValues(Array &$a_values)
	{
		$a_values['default_view'] = $this->parent_obj->objProperties->getDefaultView();
		$a_values['anonymized'] = $this->parent_obj->objProperties->isAnonymized();
		$a_values['statistics_enabled'] = $this->parent_obj->objProperties->isStatisticEnabled();
		$a_values['post_activation'] = $this->parent_obj->objProperties->isPostActivationEnabled();
		$a_values['subject_setting'] = $this->parent_obj->objProperties->getSubjectSetting();
		$a_values['mark_mod_posts'] = $this->parent_obj->objProperties->getMarkModeratorPosts();
		$a_values['thread_sorting'] = $this->parent_obj->objProperties->getThreadSorting();
		$a_values['thread_rating'] = $this->parent_obj->objProperties->isIsThreadRatingEnabled();
		
		$default_view = (int)$this->parent_obj->objProperties->getDefaultView() > ilForumProperties::VIEW_TREE 
			? ilForumProperties::VIEW_DATE 
			: ilForumProperties::VIEW_TREE;
		
		$default_view_sort_dir = (int)$this->parent_obj->objProperties->getDefaultView() > ilForumProperties::VIEW_DATE_ASC
			? ilForumProperties::VIEW_DATE_DESC
			: ilForumProperties::VIEW_DATE_ASC;

		$a_values['default_view'] = $default_view;
		$a_values['default_view_sort_dir'] = $default_view_sort_dir;
		$a_values['file_upload_allowed']   = (bool)$this->parent_obj->objProperties->getFileUploadAllowed();
	}
	
	/**
	 * @param ilPropertyFormGUI $a_form
	 */
	public function updateCustomValues(ilPropertyFormGUI $a_form)
	{
		$default_view = (int)$a_form->getInput('default_view') > ilForumProperties::VIEW_TREE
			? ilForumProperties::VIEW_DATE
			: ilForumProperties::VIEW_TREE;
		
		if($default_view == ilForumProperties::VIEW_DATE)
		{
			(int)$a_form->getInput('default_view_sort_dir') > ilForumProperties::VIEW_DATE_ASC
				? $default_view = ilForumProperties::VIEW_DATE_DESC
				: $default_view = ilForumProperties::VIEW_DATE_ASC;
		}
		
		$this->parent_obj->objProperties->setDefaultView($default_view);
		
		// BUGFIX FOR 11271
		if(isset($_SESSION['viewmode']))
		{
			$_SESSION['viewmode'] = $default_view;
		}
		
		if($this->settings->get('enable_anonymous_fora') || $this->parent_obj->objProperties->isAnonymized())
		{
			$this->parent_obj->objProperties->setAnonymisation((int)$a_form->getInput('anonymized'));
		}
		if($this->settings->get('enable_fora_statistics', false))
		{
			$this->parent_obj->objProperties->setStatisticsStatus((int)$a_form->getInput('statistics_enabled'));
		}
		$this->parent_obj->objProperties->setPostActivation((int)$a_form->getInput('post_activation'));
		$this->parent_obj->objProperties->setSubjectSetting($a_form->getInput('subject_setting'));
		$this->parent_obj->objProperties->setMarkModeratorPosts((int)$a_form->getInput('mark_mod_posts'));
		$this->parent_obj->objProperties->setThreadSorting((int)$a_form->getInput('thread_sorting'));
		$this->parent_obj->objProperties->setIsThreadRatingEnabled((bool)$a_form->getInput('thread_rating'));
		if(!ilForumProperties::isFileUploadGloballyAllowed())
		{
			$this->parent_obj->objProperties->setFileUploadAllowed((bool)$a_form->getInput('file_upload_allowed'));
		}
		$this->parent_obj->objProperties->update();
	}
}