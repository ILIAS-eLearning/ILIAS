<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './classes/class.ilObjectGUI.php';
require_once "Services/Table/classes/class.ilTable2GUI.php";
require_once './Modules/Forum/classes/class.ilForumProperties.php';
require_once './Services/Form/classes/class.ilPropertyFormGUI.php';
require_once './Modules/Forum/classes/class.ilForumPost.php';
require_once './Modules/Forum/classes/class.ilForum.php';
require_once './Modules/Forum/classes/class.ilForumTopic.php';
require_once 'Services/RTE/classes/class.ilRTE.php';


/**
* Class ilObjForumGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* $Id$
*
* @ilCtrl_Calls ilObjForumGUI: ilPermissionGUI, ilForumExportGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjForumGUI: ilColumnGUI, ilPublicUserProfileGUI, ilForumModeratorsGUI, ilObjectCopyGUI, ilExportGUI
*
* @ingroup ModulesForum
*/
class ilObjForumGUI extends ilObjectGUI
{
	public $objProperties = null;
	
	private $objCurrentTopic = null;	
	private $objCurrentPost = null;	
	private $display_confirm_post_deactivation = 0;
	private $display_confirm_post_activation = 0;
	
	private $is_moderator = false;
	private $action = null;
	
	private $create_form_gui = null;
	private $create_import_gui = null;
	private $create_topic_form_gui = null;

	private $hideToolbar = false;
	private $forum_overview_setting = null;
	
	public function ilObjForumGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		global $ilCtrl, $ilUser, $ilAccess;

		// CONTROL OPTIONS
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array('ref_id', 'cmdClass'));

		$this->type = 'frm';
		$this->ilObjectGUI($a_data, $a_id, $a_call_by_reference, false);

		$this->lng->loadLanguageModule('forum');
		
		$properties_obj_id = is_object($this->object) ? $this->object->getId() : ilObject::_lookupObjId($_GET['ref_id']);

		// forum properties
		$this->objProperties = ilForumProperties::getInstance($properties_obj_id);

		// data of current post
		$this->objCurrentTopic = new ilForumTopic(ilUtil::stripSlashes((int)$_GET['thr_pk']), $ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']));

		// data of current topic/thread
		$this->objCurrentPost = new ilForumPost(ilUtil::stripSlashes((int)$_GET['pos_pk']), $ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']));

		$frma_set = new ilSetting("frma");
		$this->forum_overview_setting = $frma_set->get('forum_overview');
	}

	/**
	* Execute Command.
	*/
	function &executeCommand()
	{
		global $ilNavigationHistory, $ilAccess, $ilCtrl, $ilObjDataCache, $ilTabs;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$exclude_cmds = array('showExplorer', 'viewThread', 'markPostUnread','markPostRead',#'editThread',
							  'showThreadNotification',
					     	  'cancelPostActivation', 'cancelPostDeactivation',
					     	  'performPostActivation', 'performPostDeactivation', 'performPostAndChildPostsActivation',
					     	  'askForPostActivation', 'askForPostDeactivation',
					     	  'toggleThreadNotification', 'toggleThreadNotificationTab',
					     	  'toggleStickiness', 'cancelPost', 'savePost', 'quotePost', 'getQuotationHTMLAsynch',
							  'rememberTreeStateAsynch'
					     	  );

		if (!is_array($exclude_cmds) || !in_array($cmd, $exclude_cmds))
		{
			$this->prepareOutput();
		}

		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess('read', '', $_GET['ref_id']))
		{
			$ilNavigationHistory->addItem($_GET['ref_id'], 'repository.php?cmd=showThreads&ref_id='.$_GET['ref_id'], 'frm');
		}
		
		switch ($next_class)
		{
			case 'ilpermissiongui':
				require_once('Services/AccessControl/classes/class.ilPermissionGUI.php');
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilforumexportgui':
				require_once('./Modules/Forum/classes/class.ilForumExportGUI.php');
				$fex_gui =& new ilForumExportGUI($this);
				$ret =& $this->ctrl->forwardCommand($fex_gui);
				exit();
				break;
			
			case 'ilforummoderatorsgui':
				require_once 'Modules/Forum/classes/class.ilForumModeratorsGUI.php';
				$fm_gui = new ilForumModeratorsGUI($this);
				$ret = $this->ctrl->forwardCommand($fm_gui);
				break;
				
			case 'ilinfoscreengui':
				//$this->prepareOutput();
				$this->infoScreen();
				break;

			case 'ilcolumngui':
				$this->showThreadsObject();
				break;

			case 'ilpublicuserprofilegui':				
				include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$add = $this->getUserProfileAdditional($_GET["ref_id"], $_GET["user"]);
				$profile_gui->setAdditional($add);
				$ret = $this->ctrl->forwardCommand($profile_gui);
				$this->tpl->setContent($ret);
				break;
				
			case 'ilobjectcopygui':
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('frm');
				$this->ctrl->forwardCommand($cp);
				break;

			case "ilexportgui":
				$this->tabs_gui->setTabActive("export");
				include_once './Services/Export/classes/class.ilExportGUI.php';
				$exp = new ilExportGUI($this);
				$exp->addFormat('xml');
				$this->ctrl->forwardCommand($exp);
				break;

			default:

				// alex, 11 Jan 2011:
				// I inserted this workaround due to bug report 6971.
				// In general the command handling is quite obscure here.
				// The form action of the table should be filled
				// with $ilCtrl->getFormAction(..) not with $ilCtrl->getLinkTarget(..)
				// Commands should be determined with $ilCtrl->getCmd() not
				// with accessing $_POST['selected_cmd'], since this is internal
				// of ilTable2GUI/ilCtrl and may change.
				if (isset($_POST["select_cmd2"]))
				{
					$_POST['selected_cmd'] = $_POST["selected_cmd2"];
				}


				if($_POST['selected_cmd'] != null)
				{
						$member_cmd = array('enableAdminForceNoti','disableAdminForceNoti','enableHideUserToggleNoti','disableHideUserToggleNoti');

					in_array($_POST['selected_cmd'], $member_cmd)
					? $cmd = $_POST['selected_cmd']
					: $cmd = 'performThreadsAction';
				}
				//if (!$cmd )
				else if (!$cmd && !$_POST['selected_cmd'] )
				{
					$cmd = 'showThreads';
				}

				$cmd .= 'Object';
				$this->$cmd();

				break;
		}

		return true;
	}

	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{
		global $ilSetting;


		$this->settingsTabs();

		$rg_pro = new ilRadioGroupInputGUI($this->lng->txt('frm_default_view'), 'default_view');
		$rg_pro->addOption(new ilRadioOption($this->lng->txt('order_by').' '.$this->lng->txt('answers'), '1'));
		$rg_pro->addOption(new ilRadioOption($this->lng->txt('order_by').' '.$this->lng->txt('date'), '2'));
		$a_form->addItem($rg_pro);

		if ($ilSetting->get('enable_anonymous_fora') || $this->objProperties->isAnonymized())
		{
			$cb_prop = new ilCheckboxInputGUI($this->lng->txt('frm_anonymous_posting'),	'anonymized');
			$cb_prop->setValue('1');
			$cb_prop->setInfo($this->lng->txt('frm_anonymous_posting_desc'));
			$a_form->addItem($cb_prop);
		}

		if ($ilSetting->get('enable_fora_statistics', false))
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

		//_force_new_post_titles
		$frm_subject = new ilRadioGroupInputGUI($this->lng->txt('frm_subject_setting'), 'subject_setting');
		$frm_subject->addOption(new ilRadioOption($this->lng->txt('preset_subject'), 'preset_subject'));
		$frm_subject->addOption(new ilRadioOption($this->lng->txt('add_re_to_subject'), 'add_re_to_subject'));
		$frm_subject->addOption(new ilRadioOption($this->lng->txt('empty_subject'), 'empty_subject'));

		$a_form->addItem($frm_subject);

	}

	protected function getEditFormCustomValues(array &$a_values)
	{
		$a_values['default_view'] = $this->objProperties->getDefaultView();
		$a_values['anonymized'] = $this->objProperties->isAnonymized();
		$a_values['statistics_enabled'] = $this->objProperties->isStatisticEnabled();
		$a_values['post_activation'] = $this->objProperties->isPostActivationEnabled();
		$a_values['subject_setting'] = $this->objProperties->getSubjectSetting();
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{
		global $ilSetting;
		
		$this->objProperties->setDefaultView((int)$a_form->getInput('default_view'));
		if ($this->ilias->getSetting('enable_anonymous_fora') || $this->objProperties->isAnonymized())
		{
			$this->objProperties->setAnonymisation((int)$a_form->getInput('anonymized'));
		}
		if ($ilSetting->get('enable_fora_statistics', false))
		{
			$this->objProperties->setStatisticsStatus((int)$a_form->getInput('statistics_enabled'));
		}
		$this->objProperties->setPostActivation((int)$a_form->getInput('post_activation'));
		$this->objProperties->setSubjectSetting($a_form->getInput('subject_setting'));

		$this->objProperties->update();
	}
	
	public function editThreadObject($a_thread_id)
	{
		$this->ctrl->setParameter($this,'thr_pk', $a_thread_id);
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html', 'Modules/Forum');
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'updateThread'));

		$ti_prop = new ilTextInputGUI($this->lng->txt('title'), 'title');
		$ti_prop->setValue(ilForumTopic::_lookupTitle($a_thread_id));
		$form->addItem($ti_prop);

		$form->addCommandButton('updateThread', $this->lng->txt('save'));
		$form->addCommandButton('showThreads', $this->lng->txt('cancel'));

		$this->tpl->setVariable('FORM1', $form->getHTML());
		return true;
	}

	public function updateThreadObject()
	{
		if(isset($_POST['title']))
		{
			$this->objCurrentTopic->setSubject($_POST['title']);
			//$this->objCurrentTopic->setId($_GET['thr_pk']);

			$this->objCurrentTopic->updateThreadTitle();
		}
		$this->showThreadsObject();
		return true;
	}

	function markAllReadObject()
	{
		global $ilUser;

		$this->object->markAllThreadsRead($ilUser->getId());

		ilUtil::sendInfo($this->lng->txt('forums_all_threads_marked_read'));

		$this->showThreadsObject();
		return true;
	}

	/**
	* list threads of forum
	*/
	function showThreadsObject()
	{
		$this->tpl->setRightContent($this->getRightColumnHTML());
		$this->getCenterColumnHTML();
	}	

	function getContent()
	{
		global $ilUser, $ilAccess, $lng, $ilToolbar;

		if (!$ilAccess->checkAccess('read,visible', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		$frm =& $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->setForumRefId($this->object->getRefId());

		$frm->setMDB2Wherecondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_liste.html',	'Modules/Forum');
		
		if((int)strlen($this->confirmation_gui_html))
		{
			 $this->tpl->setVariable('CONFIRMATION_GUI', $this->confirmation_gui_html);
		}	

		if ($ilAccess->checkAccess('add_post', '', $this->object->getRefId()) &&
			$ilAccess->checkAccess('add_thread', '', $this->object->getRefId()) &&
			!$this->hideToolbar())
		{	
			// button: new topic
			$ilToolbar->addButton($this->lng->txt('forums_new_thread'), $this->ctrl->getLinkTarget($this, 'createThread'));
		}
		
		// mark all read
		include_once("./Services/Accessibility/classes/class.ilAccessKeyGUI.php");
		if($ilUser->getId() != ANONYMOUS_USER_ID && !(int)strlen($this->confirmation_gui_html))
		{
			$ilToolbar->addButton($this->lng->txt('forums_mark_read'),
				$this->ctrl->getLinkTarget($this, 'markAllRead'),
				ilAccessKeyGUI::getAttribute(ilAccessKey::MARK_ALL_READ)
			);
			$this->ctrl->clearParameters($this);
		}
		// button: enable/disable forum notification
		include_once './Modules/Forum/classes/class.ilForumNotification.php';

		$frm_noti = new ilForumNotification($_GET['ref_id']);
		$frm_noti->setUserId($ilUser->getId());
		$is_button_enabled = $frm_noti->isUserToggleNotification();

		if($ilUser->getId() != ANONYMOUS_USER_ID &&
		   $this->ilias->getSetting('forum_notification') != 0 &&
		   !$this->hideToolbar() &&
		   $is_button_enabled != 1)
		{		
			if($frm->isForumNotificationEnabled($ilUser->getId()))
			{
				$ilToolbar->addButton($this->lng->txt('forums_disable_forum_notification'), $this->ctrl->getLinkTarget($this, 'disableForumNotification'));
			}
			else
			{
				$ilToolbar->addButton($this->lng->txt('forums_enable_forum_notification'), $this->ctrl->getLinkTarget($this, 'enableForumNotification'));
			}
		}

		$topicData = $frm->getOneTopic();

		if ($topicData)
		{
			// Visit-Counter
			$frm->setDbTable('frm_data');

		  	$frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array($topicData['top_pk'])); 
			
			$frm->updateVisits($topicData['top_pk']);
						
			// get list of threads
			$threads = $frm->getAllThreads($topicData['top_pk'], $ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']));
			$thrNum = count($threads);		
			
			// check number of threads
			if ($thrNum != $topicData['top_num_threads'])
			{
				#$frm->fixThreadNumber($topicData['top_pk'], $thrNum);
			}
			
			$pageHits = $frm->getPageHits();
			$pageHits = $ilUser->getPref('hits_per_page');

			if ($thrNum > 0)
			{
				$z = 0;
		
				// navigation to browse
				if ($thrNum > $pageHits)
				{
					if (!$_GET['offset'])
					{
						$Start = 0;
					}
					else
					{
						$Start = $_GET['offset'];
					}
			
					$linkbar = ilUtil::Linkbar($this->ctrl->getLinkTarget($this), $thrNum, $pageHits, $Start, array());
			
					if ($linkbar != '')
					{
						$this->tpl->setVariable('LINKBAR', $linkbar);
					}
				}
				
				// display thread list								
				$counter = 0;
				$this->ctrl->setParameter($this, 'cmd', 'post');
				
				$this->tpl->addCss('./Modules/Forum/css/forum_table.css');
				include_once './Modules/Forum/classes/class.ilForumTableGUI.php';
				$tbl = new ilForumTableGUI($this);
				$tbl->setId('il_frm_thread_table_'.(int)$_GET['ref_id']);
				$tbl->setFormAction($this->ctrl->getLinkTarget($this, 'showThreads'));
  				$tbl->setRowTemplate('tpl.forums_threads_table.html', 'Modules/Forum');				
		
				$tbl->addColumn('','check', '1');
				$tbl->addColumn($this->lng->txt('forums_thread'),'th_title');
			  	$tbl->addColumn($this->lng->txt('forums_created_by'), 'author');
			  	$tbl->addColumn($this->lng->txt('forums_articles'), 'num_posts');
			  	$tbl->addColumn($this->lng->txt('visits'),'num_visit');
			  	$tbl->addColumn($this->lng->txt('forums_last_post'),'lp_date');


				foreach ($threads as $thread)
				{				
					if ($thrNum > $pageHits && $z >= ($Start + $pageHits))
					{
						break;
					}
	
					if (($thrNum > $pageHits && $z >= $Start) || $thrNum <= $pageHits)
					{
						$this->ctrl->setParameter($this, 'thr_pk', $thread->getId());
						
						$result[$counter]['check'] = ilUtil::formCheckbox(
							(isset($_POST['thread_ids']) && in_array($thread->getId(), $_POST['thread_ids']) ? true : false), 'thread_ids[]',  $thread->getId()
						);						
						
						$thread->setCreateDate($frm->convertDate($thread->getCreateDate()));					
						
						if ($thread->isSticky())
						{
							$result[$counter]['th_sticky'] = true;
							$result[$counter]['th_title'] .= '<span class="light">['.$this->lng->txt('sticky').']</span> ';							
						}
						else $result[$counter]['th_sticky'] = false;
						
						if ($thread->isClosed())
						{
							$result[$counter]['th_title'] .= '<span class="light">['.$this->lng->txt('topic_close').']</span> ';								
						}	
												
						if($ilUser->getId() != ANONYMOUS_USER_ID && 
						   $this->ilias->getSetting('forum_notification') != 0 &&
						   $thread->isNotificationEnabled($ilUser->getId()))
						{
							$result[$counter]['th_title'] .= '<span class="light">['.$this->lng->txt('forums_notification_enabled').']</span> ';								
						}
						
						if ($ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
						{
							$num_posts =  $thread->countPosts();				
							$num_unread = $num_posts - $thread->countReadPosts($ilUser->getId());
							$num_new = $thread->countNewPosts($ilUser->getId());	
						}
						else
						{							
							$num_posts = $thread->countActivePosts();
							$num_unread = $num_posts - $thread->countReadActivePosts($ilUser->getId());
							$num_new = $thread->countNewActivePosts($ilUser->getId());
						}						
						
						if ($num_posts > 0)
						{
							$result[$counter]['th_title'] = "<div><a href=\"".
								$this->ctrl->getLinkTarget($this, 'viewThread').
								"\">".$thread->getSubject()."</a></div>".$result[$counter]['th_title'];
						}						
					
						// get author data
						if($thread->getUserId())
						{										
							$usr_data = $frm->getUserData($thread->getUserId(), $thread->getImportName());
						}					
						else
						{
							$usr_data = array(
								'usr_id' => 0,
								'login' => $thread->getUserAlias(),
								'firstname' => '',
								'lastname' => '',
								'public_profile' => 'n'
							);
						}						
						
						if($thread->getUserId() && 
						   $thread->getUserId() != ANONYMOUS_USER_ID)
						{						
							$this->ctrl->setParameter($this, 'backurl', urlencode('repository.php?ref_id='.$_GET['ref_id'].'&offset='.$Start));
							$this->ctrl->setParameter($this, 'user', $thread->getUserId());
							if ($usr_data['public_profile'] == 'n')
							{
								$result[$counter]['author'] = $usr_data['login'];												
							}
							else
							{
					
								$result[$counter]['author'] = "<a href=\"".
									$this->ctrl->getLinkTarget($this, 'showUser').
									"\">".$usr_data['login']."</a>";													
							}
							$this->ctrl->clearParameters($this);
						}
						else
						{
							if(strlen($thread->getUserAlias()))
							{
								$result[$counter]['author'] = $thread->getUserAlias();
								if($thread->getUserId() != ANONYMOUS_USER_ID)
								{
									$result[$counter]['author'] .= ' ('.$this->lng->txt('frm_pseudonym').')';
								}						
							}
							else
							{
								$result[$counter]['author'] = $this->lng->txt('forums_anonymous');
							}						
						}					

						$result[$counter]['num_posts'] = $num_posts;
						if($ilUser->getId() != ANONYMOUS_USER_ID)
						{
							if ($num_unread > 0)
							{
								$result[$counter]['num_posts'].= "<br><span class='alert' style='white-space:nowrap;'>".
									$lng->txt("unread").": ".$num_unread."</span>";
							}
							if ($num_new > 0 && $this->forum_overview_setting == 0)
							{
								$result[$counter]['num_posts'].= "<br><span class='alert' style='white-space:nowrap;'>".
									$lng->txt("new").": ".$num_new."</span>";
							}
						}						
				
						$result[$counter]['num_visit'] = $thread->getVisits();				
						
						if ($num_posts > 0)
						{
							if ($ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
							{
								$objLastPost = $thread->getLastPost();
							}
							else
							{
								$objLastPost = $thread->getLastActivePost();
							}
							
							if (is_object($objLastPost))
							{
								$lastPostAuthor = '';								
								if ($objLastPost->getUserId())
								{
									$last_usr_data = $frm->getUserData($objLastPost->getUserId(), $objLastPost->getImportName());
									$lastPostAuthor = $last_usr_data['login'];
								}
								else
								{									
									if(strlen($objLastPost->getUserAlias()))
									{
										$lastPostAuthor = $objLastPost->getUserAlias().' ('.$this->lng->txt('frm_pseudonym').')';						
									}
									else
									{
										$lastPostAuthor = $this->lng->txt('forums_anonymous');
									}
								}
										
								$this->ctrl->setParameter($this, 'thr_pk', $objLastPost->getThreadId());
								
								$result[$counter]['lp_date'] = '<div style="white-space:nowrap">'.
										$frm->convertDate($objLastPost->getCreateDate())."</div>".
										'<div style="white-space:nowrap">'.$this->lng->txt('from').' '."<a href=\"".
										$this->ctrl->getLinkTarget($this, 'viewThread').'#'.$objLastPost->getId().
										"\">".$lastPostAuthor."</a></div>";
							}
						}										
					}
					$counter++;
				}
				
				$tbl->disable('sort');
				$tbl->setSelectAllCheckbox('thread_ids');
				$tbl->setPrefix('frm_threads');
				$tbl->setData($result);
				
				$tbl->addMultiCommand('', $this->lng->txt('please_choose'));							
				if($this->ilias->getSetting('forum_notification') == 1)
				{
					$tbl->addMultiCommand('enable_notifications', $this->lng->txt('forums_enable_notification'));
					$tbl->addMultiCommand('disable_notifications', $this->lng->txt('forums_disable_notification'));					
				}
				if($ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
				{
					$tbl->addMultiCommand('makesticky', $this->lng->txt('make_topics_sticky'));
					$tbl->addMultiCommand('unmakesticky', $this->lng->txt('make_topics_non_sticky'));
					$tbl->addMultiCommand('editThread', $this->lng->txt('frm_edit_title'));
					$tbl->addMultiCommand('close', $this->lng->txt('close_topics'));
					$tbl->addMultiCommand('reopen', $this->lng->txt('reopen_topics'));
					$tbl->addMultiCommand('move', $this->lng->txt('move'));							
				}	

				$tbl->addMultiCommand('html', $this->lng->txt('export_html'));	
				
				if($ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
				{
					$tbl->addMultiCommand('confirmDeleteThreads', $this->lng->txt('delete'));
				}
		
				$this->tpl->setVariable('THREADS_TABLE', $tbl->getHTML());
			}
		} 	
		
		// permanent link
		include_once 'Services/PermanentLink/classes/class.ilPermanentLinkGUI.php';
		$permalink = new ilPermanentLinkGUI('frm', $this->object->getRefId());		
		$this->tpl->setVariable('PRMLINK', $permalink->getHTML());
		
		return true;
	}
	
	/**
	* @access private
	*/
	private function initForumCreateForm($object_type)
	{
		$this->create_form_gui = new ilPropertyFormGUI();
		$this->create_form_gui->setTableWidth('600px');
		
		$this->create_form_gui->setTitle($this->lng->txt('frm_new'));
		$this->create_form_gui->setTitleIcon(ilUtil::getImagePath('icon_frm.gif'));
		
		// form action
		$this->ctrl->setParameter($this, 'new_type', $object_type);
		$this->create_form_gui->setFormAction($this->ctrl->getFormAction($this, 'save'));		
		
		// title
		$title_gui = new ilTextInputGUI($this->lng->txt('title'), 'title');
		$title_gui->setMaxLength(128);
		$this->create_form_gui->addItem($title_gui);
		
		// description
		$description_gui = new ilTextAreaInputGUI($this->lng->txt('desc'), 'desc');
		$description_gui->setCols(40);
		$description_gui->setRows(2);
		$this->create_form_gui->addItem($description_gui);		
		
		// view
		$view_group_gui = new ilRadioGroupInputGUI($this->lng->txt('frm_default_view'), 'sort');
			$view_hir = new ilRadioOption($this->lng->txt('order_by').' '.$this->lng->txt('answers'), 1);							
		$view_group_gui->addOption($view_hir);
			$view_dat = new ilRadioOption($this->lng->txt('order_by').' '.$this->lng->txt('date'), 2);
		$view_group_gui->addOption($view_dat);				
		$this->create_form_gui->addItem($view_group_gui);		
		
		// anonymized or not
		$anonymize_gui = new ilCheckboxInputGUI($this->lng->txt('frm_anonymous_posting'), 'anonymized');
		$anonymize_gui->setInfo($this->lng->txt('frm_anonymous_posting_desc'));
		$anonymize_gui->setValue(1);

		if($this->ilias->getSetting('enable_anonymous_fora', false))
			$anonymize_gui->setDisabled(true);
		$this->create_form_gui->addItem($anonymize_gui);		
		
		// statistics enabled or not
		$statistics_gui = new ilCheckboxInputGUI($this->lng->txt('frm_statistics_enabled'), 'statistics_enabled');
		$statistics_gui->setInfo($this->lng->txt('frm_statistics_enabled_desc'));
		$statistics_gui->setValue(1);
		if(!$this->ilias->getSetting('enable_fora_statistics', false))
			$statistics_gui->setDisabled(true);
		$this->create_form_gui->addItem($statistics_gui);
		
		$cb_prop = new ilCheckboxInputGUI($this->lng->txt('activate_new_posts'), 'post_activation');
		$cb_prop->setValue('1');
		$cb_prop->setInfo($this->lng->txt('post_activation_desc'));
		$this->create_form_gui->addItem($cb_prop);
		
		$this->create_form_gui->addCommandButton('save', $this->lng->txt('save'));
		$this->create_form_gui->addCommandButton('cancel', $this->lng->txt('cancel'));
	}
	
	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	function cancelObject($in_rep = false)
	{
		ilUtil::sendInfo($this->lng->txt('msg_cancel'), true);
		// TODO: check this
		ilUtil::redirect('repository.php?cmd=frameset&ref_id='.$_GET['ref_id']);
	}

	/**
	* save object
	* @access	public
	*/
	protected function afterSave(ilObject $forumObj)
	{
		global $rbacadmin;
		
		// save settings
		$this->objProperties->setObjId($forumObj->getId());
		$this->objProperties->setDefaultView(1);
		$this->objProperties->setAnonymisation(0);
		$this->objProperties->setStatisticsStatus(0);
		$this->objProperties->setPostActivation(0);
		$this->objProperties->insert();

		$forumObj->createSettings();

		// ...finally assign moderator role to creator of forum object
		$roles = array();
		$roles[0] = ilObjForum::_lookupModeratorRole($forumObj->getRefId());

		$rbacadmin->assignUser($roles[0], $forumObj->getOwner(), 'n');

		// insert new forum as new topic into frm_data
		$forumObj->saveData($roles);

		// always send a message
		ilUtil::sendSuccess($this->lng->txt('frm_added'), true);

		$this->ctrl->setParameter($this, 'ref_id', $forumObj->getRefId());
		ilUtil::redirect($this->ctrl->getLinkTarget($this, 'createThread', '', false, false));
	}

	function getTabs(&$tabs_gui)
	{
		global $ilAccess, $ilUser, $tree;

		$this->ctrl->setParameter($this, 'ref_id', $this->ref_id);

		include_once './Services/Repository/classes/class.ilRepositoryExplorer.php';
		$active = array('',
						'showThreads', 
						'view', 
						'markAllRead', 
						'enableForumNotification',
						'disableForumNotification',
						'moveThreads',
						'performMoveThreads',
						'confirmMoveThreads',
						'cancelMoveThreads',
						'performThreadsAction',
						'searchForums',
						'createThread',
						'addThread',
						'showUser'
						);

		(in_array($_GET['cmd'],$active)) ? $force_active = true : $force_active = false;
		$tabs_gui->addTarget('forums_threads', $this->ctrl->getLinkTarget($this,'showThreads'), $_GET['cmd'], get_class($this), '', $force_active);

		// info tab
		if ($ilAccess->checkAccess('visible', '', $this->ref_id))
		{
			$force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui"
				|| strtolower($_GET["cmdClass"]) == "ilnotegui")
				? true
				: false;
	//echo "-$force_active-";
			$tabs_gui->addTarget("info_short",
				 $this->ctrl->getLinkTargetByClass(
				 array("ilobjforumgui", "ilinfoscreengui"), "showSummary"),
				 array("showSummary", "infoScreen"),
				 "", "", $force_active);
		}
		
		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$force_active = ($_GET['cmd'] == 'edit') ? true	: false;
			$tabs_gui->addTarget('settings', $this->ctrl->getLinkTarget($this, 'edit'), 'edit', get_class($this), '', $force_active);
		}
		
		if($ilAccess->checkAccess('edit_permission', '', $this->ref_id))
		{
			$tabs_gui->addTarget('frm_moderators', $this->ctrl->getLinkTargetByClass('ilForumModeratorsGUI', 'showModerators'), 'showModerators', get_class($this));			
		}

		if ($this->ilias->getSetting('enable_fora_statistics', false) &&
			($this->objProperties->isStatisticEnabled() || $ilAccess->checkAccess('write', '', $this->ref_id))) 
		{
			$force_active = ($_GET['cmd'] == 'showStatistics') ? true	: false;
			$tabs_gui->addTarget('frm_statistics', $this->ctrl->getLinkTarget($this, 'showStatistics'), 'showStatistics', get_class($this), '', $force_active); //false
		}

		if ($ilAccess->checkAccess("write", '', $this->object->getRefId()))
		{
			$tabs_gui->addTarget("export",
				$this->ctrl->getLinkTargetByClass("ilexportgui", ""), "", "ilexportgui");
		}

		if ($ilAccess->checkAccess('edit_permission', '', $this->ref_id))
		{
			$tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');							
		}
		
		return true;
	}

	public function settingsTabs()
	{
		global $ilTabs,$ilAccess, $ilUser, $tree;

		$ilTabs->setTabActive('settings');
		$ilTabs->addSubTabTarget('basic_settings', $this->ctrl->getLinkTarget($this, 'edit'), 'edit', get_class($this), '', $_GET['cmd']=='edit'? true : false );
// member tab
		$parent_ref_id = $tree->getParentId($this->object->getRefId());
		$parent_obj = ilObjectFactory::getInstanceByRefId($parent_ref_id);

		$parent_type = $parent_obj->getType();

		if($parent_type == 'grp' || $parent_type == 'crs')
		{
			#show member-tab for notification
			if ($ilAccess->checkAccess('edit_permission', '', $this->ref_id))
			{
				$mem_active = array('showMembers', 'forums_notification_settings');
				(in_array($_GET['cmd'],$mem_active)) ? $force_mem_active = true : $force_mem_active = false;

					$ilTabs->addSubTabTarget('notifications', $this->ctrl->getLinkTarget($this, 'showMembers'), $_GET['cmd'], get_class($this), '', $force_mem_active);
			}
		}
		return true;
	}
	/**
	 * called from GUI
	 */
	function showStatisticsObject() 
	{
		global $ilUser, $ilAccess;
		
		/// if globally deactivated, skip!!! intrusion detected
		if (!$this->ilias->getSetting('enable_fora_statistics', false))
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		// if no read access -> intrusion detected
		if (!$ilAccess->checkAccess('read', '', $_GET['ref_id']))
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		// if read access and statistics disabled -> intrusion detected 		
		if (!$ilAccess->checkAccess('read', '',  $_GET['ref_id']) && !$this->objProperties->isStatisticEnabled())
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}				
    			
		// if write access and statistics disabled -> ok, for forum admin 		
		if ($ilAccess->checkAccess('write', '', $_GET['ref_id']) && 
			!$this->objProperties->isStatisticEnabled())
		{
			ilUtil::sendInfo($this->lng->txt('frm_statistics_disabled_for_participants'));
		}
		
		$this->object->Forum->setForumId($this->object->getId());
		
		require_once 'Modules/Forum/classes/class.ilForumStatisticsTableGUI.php';		
		
		$tbl = new ilForumStatisticsTableGUI($this, 'showStatistics');
		$tbl->setId('il_frm_statistic_table_'.(int)$_GET['ref_id']);
		$tbl->setTitle($this->lng->txt('statistic'), 'icon_usr_b.gif', $this->lng->txt('obj_'.$this->object->getType()));		
		
		$data = $this->object->Forum->getUserStatistic($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']));		
		$result = array();
		$counter = 0;
		foreach($data as $row)
		{
			$result[$counter]['ranking'] = $row[0];
			$result[$counter]['login'] = $row[1];
			$result[$counter]['lastname'] = $row[2];
			$result[$counter]['firstname'] = $row[3];
			
			++$counter;
		}
		$tbl->setData($result);
				
		$this->tpl->setContent($tbl->getHTML());
	}
	
	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	function _goto($a_target, $a_thread = 0, $a_posting = 0)
	{
		global $ilAccess, $ilErr, $lng, $ilCtrl;

		if ($ilAccess->checkAccess('read', '', $a_target))
		{
			if ($a_thread != 0)
			{				
				$objTopic = new ilForumTopic($a_thread);
				if ($objTopic->getFrmObjId() && 
					$objTopic->getFrmObjId() != ilObject::_lookupObjectId($a_target))
				{					
					$ref_ids = ilObject::_getAllReferences($objTopic->getFrmObjId());
					foreach ($ref_ids as $ref_id)
					{
						if ($ilAccess->checkAccess('read,visible', '', $ref_id))
						{
							$new_ref_id = $ref_id;							
							break;
						}
					}
					
					if (isset($new_ref_id) && $new_ref_id != $a_target)
					{
						ilUtil::redirect(ILIAS_HTTP_PATH."/goto.php?target=frm_".$new_ref_id."_".$a_thread."_".$a_posting);
					}
				}			
	
				$_GET['ref_id'] = $a_target;
				$_GET['pos_pk'] = $a_posting;
				$_GET['thr_pk'] = $a_thread;				
				$_GET['cmdClass'] = 'ilObjForumGUI';
				$_GET['cmd'] = 'viewThread';

				include_once('repository.php');
				exit();
			}
			else
			{
			
				$_GET['ref_id'] = $a_target;
				include_once('repository.php');
				exit();
			}
		}
		else if ($ilAccess->checkAccess('read', '', ROOT_FOLDER_ID))
		{
			$_GET['target'] = '';
			$_GET['ref_id'] = ROOT_FOLDER_ID;
			ilUtil::sendInfo(sprintf($lng->txt('msg_no_perm_read_item'),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			include('repository.php');
			exit();
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
	}
	
	public function performDeleteThreadsObject()
	{
		global $lng, $ilAccess, $ilCtrl;
		
		if(!$ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
		{		
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
			return $this->showThreadsObject();
		}
		
		if(!is_array($_POST['thread_ids']))
	 	{
	 		ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'));
	 		return $this->showThreadsObject();
	 	}	

		require_once './Modules/Forum/classes/class.ilForum.php';
		require_once './Modules/Forum/classes/class.ilObjForum.php';

		$forumObj = new ilObjForum($_GET['ref_id']);
		
		$this->objProperties->setObjId($forumObj->getId());

		foreach($_POST['thread_ids'] as $topic_id)
		{
			$frm = new ilForum();
			$frm->setForumId($forumObj->getId());
			$frm->setForumRefId($forumObj->getRefId());

			$first_node = $frm->getFirstPostNode($topic_id);
			if((int)$first_node['pos_pk'])
			{
				$frm->deletePost($first_node['pos_pk']);
				ilUtil::sendInfo($lng->txt('forums_post_deleted'));
			}						
		}
		
		$this->ctrl->redirect($this, 'showThreads');
	}
	
	public function confirmDeleteThreads()
	{
		global $ilAccess;

		if(!is_array($_POST['thread_ids']))
	 	{
	 		ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'));
	 		return $this->showThreadsObject();
	 	}
	 	
	 	if(!$ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
			return $this->showThreadsObject();
		}
	 	
	 	include_once('Services/Utilities/classes/class.ilConfirmationGUI.php');
		$c_gui = new ilConfirmationGUI();
		
		$c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDeleteThreads'));
		$c_gui->setHeaderText($this->lng->txt('frm_sure_delete_threads'));
		$c_gui->setCancel($this->lng->txt('cancel'), 'showThreads');
		$c_gui->setConfirm($this->lng->txt('confirm'), 'performDeleteThreads');

		foreach((array)$_POST['thread_ids'] as $thread_id)
		{
			$c_gui->addItem('thread_ids[]', $thread_id, ilForumTopic::_lookupTitle($thread_id));
		}
		
		$this->confirmation_gui_html = $c_gui->getHTML();
		
		$this->hideToolbar(true);
		
		return $this->showThreadsObject();
	}

	/**
	* Show Forum Explorer.
	*/
	public function showExplorerObject()
	{
		global $tpl, $lng;

		require_once './Modules/Forum/classes/class.ilForumExplorer.php';

		$tpl->addBlockFile('CONTENT', 'content', 'tpl.explorer.html');
		$tpl->setVariable('IMG_SPACE', ilUtil::getImagePath('spacer.gif', false));

		$exp = new ilForumExplorer("./repository.php?cmd=viewThread&cmdClass=ilobjforumgui&thr_pk=".$this->objCurrentTopic->getId()."&ref_id=".$_GET['ref_id'],
				$this->objCurrentTopic, (int) $_GET['ref_id']);

		//build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();
	}

	function prepareThreadScreen($a_forum_obj)
	{
		global $tpl, $lng, $ilTabs, $ilias, $ilUser, $ilAccess;
		
		$session_name = 'viewmode_'.$a_forum_obj->getId();

		$tpl->getStandardTemplate();
		ilUtil::sendInfo();	
		ilUtil::infoPanel();
		
		$tpl->setTitleIcon(ilUtil::getImagePath('icon_frm_b.gif'));

        $ilTabs->setBackTarget($lng->txt('all_topics'), 'repository.php?ref_id='.$_GET['ref_id'], $t_frame);
	
		// by answer view
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'viewmode', 'answers');
		$ilTabs->addTarget('order_by_answers', $this->ctrl->getLinkTarget($this, 'viewThread'));
	
		// by date view
		$this->ctrl->setParameter($this, 'viewmode', 'date');
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$ilTabs->addTarget('order_by_date',	$this->ctrl->getLinkTarget($this, 'viewThread'));
		$this->ctrl->clearParameters($this);

		if($_SESSION['viewmode']== 'date')
		{
			$ilTabs->setTabActive('order_by_date');
		}
		else
		{
			$ilTabs->setTabActive('order_by_answers');
		}

		$frm =& $a_forum_obj->Forum;
		$frm->setForumId($a_forum_obj->getId());
	}
	
	public function performPostAndChildPostsActivationObject()
	{
		global $ilAccess;
		
		if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
		{
			$activate = $this->objCurrentPost->activatePostAndChildPosts();
			ilUtil::sendInfo($this->lng->txt('forums_post_and_children_were_activated'), true);
		}
		
		$this->viewThreadObject();
		
		return true;
	}
	
	public function performPostActivationObject()
	{
		global $ilAccess;
		
		if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
		{
			$this->objCurrentPost->activatePost();
			ilUtil::sendInfo($this->lng->txt('forums_post_was_activated'), true);
		}
		
		$this->viewThreadObject();
		
		return true;
	}

	public function cancelPostActivationObject()		
	{
		$this->viewThreadObject();
		
		return true;
	}
	
	public function askForPostActivationObject()
	{
		global $ilAccess;		

		if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
		{		
			$this->setDisplayConfirmPostActivation(true);		
		}
		
		$this->viewThreadObject();
		
		return true;
	}
	
	public function setDisplayConfirmPostActivation($status = 0)
	{
		$this->display_confirm_post_activation = $status;
	}	
	public function displayConfirmPostActivation()
	{
		return $this->display_confirm_post_activation;
	}

	/**
	 * Toggle thread notification for current user
	 */
	public function toggleThreadNotificationObject()
	{
		global $ilUser;

		if ($this->objCurrentTopic->isNotificationEnabled($ilUser->getId()))
		{
			$this->objCurrentTopic->disableNotification($ilUser->getId());
			ilUtil::sendInfo($this->lng->txt('forums_notification_disabled'), true);
		}
		else
		{
			$this->objCurrentTopic->enableNotification($ilUser->getId());
			ilUtil::sendInfo($this->lng->txt('forums_notification_enabled'), true);
		}
		
		$this->viewThreadObject();
		
		return true;
	}
	
	/**
	 * Toggle sticky attribute of a thread
	 */
	public function toggleStickinessObject()
	{
		global $ilAccess;
		
		if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
		{
			if ($this->objCurrentTopic->isSticky())
			{
				$this->objCurrentTopic->unmakeSticky();	
			}
			else
			{
				$this->objCurrentTopic->makeSticky();
			}
		}
		
		$this->viewThreadObject();
		
		return true;
	}
	
	public function cancelPostObject()
	{
		$_GET['action'] = '';

		$this->viewThreadObject();
		
		return true;
	}
	
	public function getDeleteFormHTML()
	{
		global $lng;
		
		$form_tpl = new ilTemplate('tpl.frm_delete_post_form.html', true, true, 'Modules/Forum');

		$form_tpl->setVariable('ANKER', $this->objCurrentPost->getId());
		$form_tpl->setVariable('SPACER', '<hr noshade="noshade" width="100%" size="1" align="center" />');
		$form_tpl->setVariable('TXT_DELETE', $lng->txt('forums_info_delete_post'));
		$this->ctrl->setParameter($this, 'action', 'ready_delete');
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
		$form_tpl->setVariable('FORM_ACTION', $this->ctrl->getLinkTarget($this, 'viewThread'));
		$this->ctrl->clearParameters($this);
		$form_tpl->setVariable('CANCEL_BUTTON', $lng->txt('cancel'));
		$form_tpl->setVariable('CONFIRM_BUTTON', $lng->txt('confirm'));

  		return $form_tpl->get(); 
	}
	
	public function getActivationFormHTML()
	{
		global $lng;
		
		$form_tpl = new ilTemplate('tpl.frm_activation_post_form.html', true, true, 'Modules/Forum');

		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
		$form_tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'performPostActivation'));
		$form_tpl->setVariable('SPACER', '<hr noshade="noshade" width="100%" size="1" align="center" />');
		$form_tpl->setVariable('ANCHOR', $this->objCurrentPost->getId());
		$form_tpl->setVariable('TXT_ACT', $lng->txt('activate_post_txt'));								
		$form_tpl->setVariable('CONFIRM_BUTTON', $lng->txt('activate_only_current'));
		$form_tpl->setVariable('CMD_CONFIRM', 'performPostActivation');
		$form_tpl->setVariable('CONFIRM_BRANCH_BUTTON', $lng->txt('activate_current_and_childs'));
		$form_tpl->setVariable('CMD_CONFIRM_BRANCH', 'performPostAndChildPostsActivation');
		$form_tpl->setVariable('CANCEL_BUTTON',$lng->txt('cancel'));
		$form_tpl->setVariable('CMD_CANCEL', 'cancelPostActivation');
		$this->ctrl->clearParameters($this);

  		return $form_tpl->get(); 
	}
	
	public function getDeactivationFormHTML()
	{
		global $lng;
		
		$form_tpl = new ilTemplate('tpl.frm_deactivation_post_form.html', true, true, 'Modules/Forum');

		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
		$form_tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'performPostDeactivation'));
		$form_tpl->setVariable('SPACER', '<hr noshade="noshade" width="100%" size="1" align="center" />');
		$form_tpl->setVariable('ANCHOR', $this->objCurrentPost->getId());
		$form_tpl->setVariable('TXT_DEACT', $lng->txt('deactivate_post_txt'));
		$form_tpl->setVariable('CONFIRM_BUTTON', $lng->txt('deactivate_current_and_childs'));
		$form_tpl->setVariable('CMD_CONFIRM', 'performPostDeactivation');
		$form_tpl->setVariable('CANCEL_BUTTON',$lng->txt('cancel'));
		$form_tpl->setVariable('CMD_CANCEL', 'cancelPostDeactivation');
		$this->ctrl->clearParameters($this);

  		return $form_tpl->get(); 
	}
	
	public function getCensorshipFormHTML()
	{
		global $lng, $frm;
		
		$form_tpl = new ilTemplate('tpl.frm_censorship_post_form.html', true, true, 'Modules/Forum');

		$form_tpl->setVariable('ANCHOR', $this->objCurrentPost->getId());
		$form_tpl->setVariable('SPACER', '<hr noshade="noshade" width="100%" size="1" align="center" />');
		$this->ctrl->setParameter($this, 'action', 'ready_censor');
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
		$form_tpl->setVariable('FORM_ACTION', $this->ctrl->getLinkTarget($this, 'viewThread'));
		$this->ctrl->clearParameters($this);
		$form_tpl->setVariable('TXT_CENS_MESSAGE', $lng->txt('forums_the_post'));
		$form_tpl->setVariable('TXT_CENS_COMMENT', $lng->txt('forums_censor_comment').':');
		$form_tpl->setVariable('CENS_MESSAGE', $frm->prepareText($this->objCurrentPost->getCensorshipComment(), 2));
		$form_tpl->setVariable('CANCEL_BUTTON', $lng->txt('cancel'));
		$form_tpl->setVariable('CONFIRM_BUTTON', $lng->txt('confirm'));

		if ($this->objCurrentPost->isCensored())
		{
			$form_tpl->setVariable('TXT_CENS', $lng->txt('forums_info_censor2_post'));
			$form_tpl->setVariable('CANCEL_BUTTON', $lng->txt('yes'));
			$form_tpl->setVariable('CONFIRM_BUTTON', $lng->txt('no'));
		}
		else
		{
			$form_tpl->setVariable('TXT_CENS', $lng->txt('forums_info_censor_post'));
		}
		
  		return $form_tpl->get(); 
	}	
	
	private function initReplyEditForm()
	{
		global $ilUser, $rbacsystem;
		
		// init objects
		$oForumObjects = $this->getForumObjects();		
		$forumObj = $oForumObjects['forumObj'];
		$frm = $oForumObjects['frm'];
		$oFDForum = $oForumObjects['file_obj'];
		
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$this->replyEditForm = new ilPropertyFormGUI();
		$this->replyEditForm->setTableWidth('100%');
		
		// titel
		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply')
		{
			$this->ctrl->setParameter($this, 'action', 'ready_showreply');
		}
		else
		{
			$this->ctrl->setParameter($this, 'action', 'ready_showedit');
		}
		
		// form action
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->setParameter($this, 'offset', (int)$_GET['offset']);
		$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
		$this->replyEditForm->setFormAction($this->ctrl->getFormAction($this, 'savePost', $this->objCurrentPost->getId()));
		$this->ctrl->clearParameters($this);
		
		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply')
		{
			$this->replyEditForm->setTitle($this->lng->txt('forums_your_reply'));
		}
		else
		{
			$this->replyEditForm->setTitle($this->lng->txt('forums_edit_post'));
		}
		
		// alias
		if($this->objProperties->isAnonymized() && 
		   in_array($_GET['action'], array('showreply', 'ready_showreply')))
		{
			$oAnonymousNameGUI = new ilTextInputGUI($this->lng->txt('forums_your_name'), 'alias');
			$oAnonymousNameGUI->setMaxLength(64);
			$oAnonymousNameGUI->setInfo($this->lng->txt('forums_use_alias'));			
			
			$this->replyEditForm->addItem($oAnonymousNameGUI);
		}
		
		// subject
		$oSubjectGUI = new ilTextInputGUI($this->lng->txt('forums_subject'), 'subject');
		$oSubjectGUI->setMaxLength(64);
		$oSubjectGUI->setRequired(true);
	
		if($this->objProperties->getSubjectSetting() == 'empty_subject')
		$oSubjectGUI->setInfo($this->lng->txt('enter_new_subject'));
		
		$this->replyEditForm->addItem($oSubjectGUI);
		
		// post
		$oPostGUI = new ilTextAreaInputGUI(
			$_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply' ? $this->lng->txt('forums_your_reply') : $this->lng->txt('forums_edit_post'), 
			'message'
		);
		$oPostGUI->setRequired(true);
		$oPostGUI->setCols(50);
		$oPostGUI->setRows(15);
		$oPostGUI->setUseRte(true);
		$oPostGUI->addPlugin('latex');
		$oPostGUI->addButton('latex');
		$oPostGUI->addButton('pastelatex');
		$oPostGUI->addPlugin('ilfrmquote');
		$oPostGUI->addPlugin('ilimgupload');
        $oPostGUI->addButton('ilimgupload');
		$oPostGUI->addPlugin('code'); 
		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply')
		{
			$oPostGUI->addButton('ilFrmQuoteAjaxCall');
		}
		$oPostGUI->removePlugin('advlink');
		$oPostGUI->removePlugin('ibrowser');
		$oPostGUI->removePlugin('image');
		$oPostGUI->setRTERootBlockElement('');
		$oPostGUI->usePurifier(true);
		$oPostGUI->disableButtons(array(
			'charmap',
			'undo',
			'redo',
			'justifyleft',
			'justifycenter',
			'justifyright',
			'justifyfull',
			'anchor',
			'fullscreen',
			'cut',
			'copy',
			'paste',
			'pastetext',
			'formatselect',
			'ibrowser',
			'image'
		));
		
		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply')
		{
			$oPostGUI->setRTESupport($ilUser->getId(), 'frm~', 'frm_post', 'tpl.tinymce_frm_post.html');
		}
		else
		{
			$oPostGUI->setRTESupport($this->objCurrentPost->getId(), 'frm', 'frm_post', 'tpl.tinymce_frm_post.html');
		}
		// purifier
		require_once 'Services/Html/classes/class.ilHtmlPurifierFactory.php';
		$oPostGUI->setPurifier(ilHtmlPurifierFactory::_getInstanceByType('frm_post'));		
			
		$this->replyEditForm->addItem($oPostGUI);
		
		// notification only if gen. notification is disabled and forum isn't anonymous
		include_once 'Services/Mail/classes/class.ilMail.php';
		$umail = new ilMail($ilUser->getId());
		if($rbacsystem->checkAccess('mail_visible', $umail->getMailObjectReferenceId()) &&
		   !$frm->isThreadNotificationEnabled($ilUser->getId(), $this->objCurrentPost->getThreadId()) &&
		   !$this->objProperties->isAnonymized())
		{
			$oNotificationGUI = new ilCheckboxInputGUI('', 'notify');
			$oNotificationGUI->setInfo($this->lng->txt('forum_notify_me'));
			
			$this->replyEditForm->addItem($oNotificationGUI);
		}
		
		// attachments
		$oFileUploadGUI = new ilFileWizardInputGUI($this->lng->txt('forums_attachments_add'), 'userfile');
		$oFileUploadGUI->setFilenames(array(0 => ''));
		$this->replyEditForm->addItem($oFileUploadGUI);
		
		// edit attachments
		if(count($oFDForum->getFilesOfPost()) && ($_GET['action'] == 'showedit' || $_GET['action'] == 'ready_showedit'))
		{
			$oExistingAttachmentsGUI = new ilCheckboxGroupInputGUI($this->lng->txt('forums_delete_file'), 'del_file');
						
			foreach($oFDForum->getFilesOfPost() as $file)
			{
				$oAttachmentGUI = new ilCheckboxInputGUI($file['name'], 'del_file');
				$oAttachmentGUI->setValue($file['md5']);
				$oExistingAttachmentsGUI->addOption($oAttachmentGUI);
			}
			$this->replyEditForm->addItem($oExistingAttachmentsGUI);
		}
				
		// buttons
		$this->replyEditForm->addCommandButton('savePost', $this->lng->txt('submit'));				
		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply')
		{
			include_once 'Services/RTE/classes/class.ilRTE.php';
			$rtestring = ilRTE::_getRTEClassname();
			
			if (array_key_exists('show_rte', $_POST))
			{
				ilObjAdvancedEditing::_setRichTextEditorUserState($_POST['show_rte']);
			}			

			if(strtolower($rtestring) != 'iltinymce' || !ilObjAdvancedEditing::_getRichTextEditorUserState())
			{
				$this->replyEditForm->addCommandButton('quotePost', $this->lng->txt('forum_add_quote'));
			}	
		}
		$this->replyEditForm->addCommandButton('cancelPost', $this->lng->txt('cancel'));
	}
	
	private function getReplyEditForm()
	{
		if(null === $this->replyEditForm)
		{
			$this->initReplyEditForm();
		}
		
		return $this->replyEditForm;
	}
	
	public function savePostObject()	
	{
		global $ilUser, $ilAccess, $lng;

		$_SESSION['frm'][(int)$_GET['thr_pk']]['openTreeNodes'] = 0;
		if(!is_array($_POST['del_file'])) $_POST['del_file'] = array();
		
		if($this->objCurrentTopic->isClosed())
		{
			$_GET['action'] = '';
			return $this->viewThreadObject();
		}
			
		$oReplyEditForm = $this->getReplyEditForm();		
		if($oReplyEditForm->checkInput())
		{	
			// init objects
			$oForumObjects = $this->getForumObjects();		
			$forumObj = $oForumObjects['forumObj'];
			$frm = $oForumObjects['frm'];
			$frm->setMDB2WhereCondition(' top_frm_fk = %s ', array('integer'), array($frm->getForumId()));		
			$topicData = $frm->getOneTopic();
						
			// Generating new posting
			if($_GET['action'] == 'ready_showreply')
			{
				// reply: new post					
				$status = 1;
				$send_activation_mail = 0;
				
				if($this->objProperties->isPostActivationEnabled())
				{
					if(!$ilAccess->checkAccess('moderate_frm', '', (int)$this->object->getRefId()))								
					{
						$status = 0;
						$send_activation_mail = 1;								
					}
					else if($this->objCurrentPost->isAnyParentDeactivated())
					{
						$status = 0;
					}
				}

				if($this->objProperties->isAnonymized())
				{
					if(!strlen($oReplyEditForm->getInput('alias')))
					{
						$user_alias = $this->lng->txt('forums_anonymous');
					}
					else
					{
						$user_alias = $oReplyEditForm->getInput('alias');
					}
				}
				else
				{
					$user_alias = $ilUser->getLogin();	
				}
		
				$newPost = $frm->generatePost(
					$topicData['top_pk'], 
					$this->objCurrentTopic->getId(),
					($this->objProperties->isAnonymized() ? 0 : $ilUser->getId()), 
					ilRTE::_replaceMediaObjectImageSrc($oReplyEditForm->getInput('message'), 0),
					$this->objCurrentPost->getId(),
					(int)$oReplyEditForm->getInput('notify'),
					$this->handleFormInput($oReplyEditForm->getInput('subject'), false),
				    $user_alias,
					'',
					$status,
					$send_activation_mail
				);
				
				// copy temporary media objects (frm~)
				include_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
				$mediaObjects = ilRTE::_getMediaObjects($oReplyEditForm->getInput('message'), 0);				
				$myMediaObjects = ilObjMediaObject::_getMobsOfObject('frm~:html', $ilUser->getId());
				foreach($mediaObjects as $mob)
				{
					foreach($myMediaObjects as $myMob)
					{
						if($mob == $myMob)
						{
							// change usage
							ilObjMediaObject::_removeUsage($mob, 'frm~:html', $ilUser->getId());
							break;													
						}
					}
					ilObjMediaObject::_saveUsage($mob, 'frm:html', $newPost);
				}
				
				$oFDForum = new ilFileDataForum($forumObj->getId(), $newPost);
				$file = $_FILES['userfile'];
				if(is_array($file) && !empty($file))
				{
					$oFDForum->storeUploadedFile($file);
				}
				
				$message = '';				
				if(!$ilAccess->checkAccess('moderate_frm', '', (int)$this->object->getRefId()) &&
				   !$status)								
				{
					$message = $lng->txt('forums_post_needs_to_be_activated');
				}
				else
				{
					$message = $lng->txt('forums_post_new_entry');
				}
				
				ilUtil::sendSuccess($message, true);
				$this->ctrl->setParameter($this, 'pos_pk', $newPost);
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());				
			}
			else
			{
				if((!$ilAccess->checkAccess('moderate_frm', '', (int)$_GET['ref_id']) &&
				   !$this->objCurrentPost->isOwner($ilUser->getId())) || $this->objCurrentPost->isCensored())
				{
				   	$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
				}				
				
				// remove usage of deleted media objects
				include_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
				$oldMediaObjects = ilObjMediaObject::_getMobsOfObject('frm:html', $this->objCurrentPost->getId());
				$curMediaObjects = ilRTE::_getMediaObjects($oReplyEditForm->getInput('message'), 0);
				foreach($oldMediaObjects as $oldMob)
				{
					$found = false;
					foreach($curMediaObjects as $curMob)
					{
						if($oldMob == $curMob)
						{
							$found = true;
							break;																					
						}
					}
					if(!$found)
					{						
						if(ilObjMediaObject::_exists($oldMob))
						{
							ilObjMediaObject::_removeUsage($oldMob, 'frm:html', $this->objCurrentPost->getId());
							$mob_obj = new ilObjMediaObject($oldMob);
							$mob_obj->delete();
						}
					}
				}
					
				// if post has been edited posting mus be activated again by moderator
				$status = 1;
				$send_activation_mail = 0;

				if($this->objProperties->isPostActivationEnabled())
				{
					if(!$ilAccess->checkAccess('moderate_frm', '', (int)$this->object->getRefId()))
					{
						$status = 0;
						$send_activation_mail = 1;
					}
					else if($this->objCurrentPost->isAnyParentDeactivated())
					{
						$status = 0;
					}
				}
				$this->objCurrentPost->setStatus($status);

				$this->objCurrentPost->setSubject($this->handleFormInput($oReplyEditForm->getInput('subject'), false));
				$this->objCurrentPost->setMessage(ilRTE::_replaceMediaObjectImageSrc($oReplyEditForm->getInput('message'), 0));
				$this->objCurrentPost->setNotification((int)$oReplyEditForm->getInput('notify'));
				$this->objCurrentPost->setChangeDate(date('Y-m-d H:i:s'));
				$this->objCurrentPost->setUpdateUserId($ilUser->getId());
				
				// edit: update post
				if($this->objCurrentPost->update())
				{
					$this->objCurrentPost->reload();
					
					// Change news item accordingly
					include_once 'Services/News/classes/class.ilNewsItem.php';
					// note: $this->objCurrentPost->getForumId() does not give us the forum ID here (why?)
					$news_id = ilNewsItem::getFirstNewsIdForContext($forumObj->getId(),
						'frm', $this->objCurrentPost->getId(), 'pos');
					if($news_id > 0)
					{
						$news_item = new ilNewsItem($news_id);
						$news_item->setTitle($this->objCurrentPost->getSubject());
						$news_item->setContent(ilRTE::_replaceMediaObjectImageSrc($frm->prepareText(
							$this->objCurrentPost->getMessage(), 0), 1)
						);
						$news_item->update();
					}
					
					// attachments					
					$oFDForum = $oForumObjects['file_obj'];
					
					$file = $_FILES['userfile'];
					if(is_array($file) && !empty($file))
					{
						$oFDForum->storeUploadedFile($file);
					}
					
					$file2delete = $oReplyEditForm->getInput('del_file');
					if(is_array($file2delete) && count($file2delete))
					{
						$oFDForum->unlinkFilesByMD5Filenames($file2delete);
					}				
				}

				if (!$status && $send_activation_mail)
				{
					$pos_data = $this->objCurrentPost->getDataAsArray();
					$pos_data["top_name"] = $this->object->getTitle();
					$frm->sendPostActivationNotification($pos_data);
				}

				ilUtil::sendSuccess($lng->txt('forums_post_modified'), true);
				$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
				$this->ctrl->setParameter($this, 'viewmode', $_SESSION['viewmode']);
			}	
		}
		else
		{
			$_GET['action'] = substr($_GET['action'], 6);
		}		
		return $this->viewThreadObject();
	}

	private function hideToolbar($a_flag = null)
	{
		if(null === $a_flag)
		{
			return $this->hideToolbar;
		}
		
		$this->hideToolbar = $a_flag;
		return $this;
	}
	
	public function quotePostObject()		
	{
		if(!is_array($_POST['del_file'])) $_POST['del_file'] = array();
		
		if($this->objCurrentTopic->isClosed())
		{
			$_GET['action'] = '';
			return $this->viewThreadObject();
		}
		
		$oReplyEditForm = $this->getReplyEditForm();		
		
		// remove mandatory fields
		$oReplyEditForm->getItemByPostVar('subject')->setRequired(false);
		$oReplyEditForm->getItemByPostVar('message')->setRequired(false);
		
		$oReplyEditForm->checkInput();
		
		// add mandatory fields
		$oReplyEditForm->getItemByPostVar('subject')->setRequired(true);
		$oReplyEditForm->getItemByPostVar('message')->setRequired(true);
		
		$_GET['action'] = 'showreply';		
		
		return $this->viewThreadObject();
	}
	
	public function getQuotationHTMLAsynchObject()
	{
		$oForumObjects = $this->getForumObjects();	
		$frm = $oForumObjects['frm'];
		
		$html = ilRTE::_replaceMediaObjectImageSrc($frm->prepareText($this->objCurrentPost->getMessage(), 1, $this->objCurrentPost->getLoginName()), 1);
		echo $html;
		exit();	
	}
	
	private function getForumObjects()
	{
		if(null === $this->forumObjects)
		{
			$forumObj = new ilObjForum($_GET['ref_id']);
			$file_obj = new ilFileDataForum($forumObj->getId(), $this->objCurrentPost->getId());		
			$frm = $forumObj->Forum;
			$frm->setForumId($forumObj->getId());
			$frm->setForumRefId($forumObj->getRefId());
			
			$this->forumObjects['forumObj'] = $forumObj;
			$this->forumObjects['frm'] = $frm;
			$this->forumObjects['file_obj'] = $file_obj;
		}
		
		return $this->forumObjects;
	}
	
	public function getForumExplorer()
	{
		global $tpl;

		include_once 'Services/JSON/classes/class.ilJsonUtil.php';
		include_once 'Modules/Forum/classes/class.ilForumExplorer.php';
		include_once 'Services/YUI/classes/class.ilYuiUtil.php';

		ilYuiUtil::initConnection();
		$tpl->addJavaScript(ilYuiUtil::getLocalPath().'/yahoo/yahoo-min.js');
		$tpl->addJavaScript(ilYuiUtil::getLocalPath().'/event/event-min.js');
		$tpl->addJavaScript('./Modules/Forum/js/treeview.js');
		$tpl->addCss('./Modules/Forum/css/forum_tree.css');

		if(!is_array($_SESSION['frm'][(int)$_GET['thr_pk']]['openTreeNodes']))
		{		
			$_SESSION['frm'][(int)$_GET['thr_pk']]['openTreeNodes'] = array();
		}

		$tplTree = new ilTemplate('tpl.frm_tree.html', true, true, 'Modules/Forum');
		$tplTree->setVariable('THR_OPEN_NODES',
			ilJsonUtil::encode($_SESSION['frm'][(int)$_GET['thr_pk']]['openTreeNodes']));

		$this->ctrl->setParameter($this, 'thr_pk', (int)$_GET['thr_pk']);
		$tplTree->setVariable('THR_TREE_STATE_URL',
			$this->ctrl->getFormAction($this, 'rememberTreeStateAsynch', '', true, false));

		$obj_frm_exp = new ilForumExplorer(
			$tplTree,
			$this->ctrl->getLinkTarget($this, 'viewThread'),
			#$this->ctrl->getLinkTarget($this, 'markPostRead'),
			$this->objCurrentTopic, (int) $_GET['ref_id']
		);

		$obj_frm_exp->renderTree();
		return $tplTree->get();
	}

	public function rememberTreeStateAsynchObject()
	{
		include_once 'Services/JSON/classes/class.ilJsonUtil.php';

		$response = new stdClass();
		$response->success = true;

		$_SESSION['frm'][(int)$_GET['thr_pk']]['openTreeNodes'] =
			explode(',', $_POST['openNodes']);

		echo ilJsonUtil::encode($response);
		exit();
	}

	/**
	 * View single thread
	 */
	public function viewThreadObject()
	{
		global $tpl, $lng, $ilUser, $ilAccess, $ilTabs, $rbacsystem,
			   $rbacreview, $ilNavigationHistory, $ilCtrl, $frm, $ilToolbar;

		$tpl->addCss('./Modules/Forum/css/forum_tree.css');

		// quick and dirty: check for treeview
		if(!isset($_SESSION['thread_control']['old']))
		{
			$_SESSION['thread_control']['old'] = $_GET['thr_pk'];
			$_SESSION['thread_control']['new'] = $_GET['thr_pk'];
		}
		else
		if(isset($_SESSION['thread_control']['old']) && $_GET['thr_pk'] != $_SESSION['thread_control']['old'])
		{
			$_SESSION['thread_control']['new'] = $_GET['thr_pk'];
			$_SESSION['viewmode'] = 'answers';
			$_SESSION['frm'][(int)$_GET['thr_pk']]['openTreeNodes'] = 0;
		}

		if(isset($_GET['viewmode']) && $_GET['viewmode'] != $_SESSION['viewmode'])
		{
			$_SESSION['viewmode'] = $_GET['viewmode'];
		}

		if( (isset($_GET['action']) &&  $_SESSION['viewmode'] != 'date')
			||($_SESSION['viewmode'] == 'answers')
			|| !isset($_SESSION['viewmode']))
		{
			$_SESSION['viewmode'] = 'answers';
		}
		else
		{
			$_SESSION['viewmode'] = 'date';
		}

		if(!$ilAccess->checkAccess('read,visible', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		// Set context for login
		$append = '_'.$this->objCurrentTopic->getId().
			($this->objCurrentPost->getId() ? '_'.$this->objCurrentPost->getId() : '');
		$tpl->setLoginTargetPar('frm_'.$_GET['ref_id'].$append);

		// delete temporary media object (not in case a user adds media objects and wants to save an invalid form)
		if($_GET['action'] != 'showreply' && $_GET['action'] != 'showedit')
		{
			try
			{
				include_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
				$mobs = ilObjMediaObject::_getMobsOfObject('frm~:html', $ilUser->getId());
				foreach($mobs as $mob)
				{					
					if(ilObjMediaObject::_exists($mob))
					{
						ilObjMediaObject::_removeUsage($mob, 'frm~:html', $ilUser->getId());
						$mob_obj = new ilObjMediaObject($mob);
						$mob_obj->delete();
					}
				}
			}
			catch(Exception $e)
			{
			}
		}
		
		require_once './Modules/Forum/classes/class.ilObjForum.php';
		require_once './Modules/Forum/classes/class.ilFileDataForum.php';		
		
		$lng->loadLanguageModule('forum');

		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			$ilCtrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
			$ilNavigationHistory->addItem($this->object->getRefId(), $ilCtrl->getLinkTarget($this, 'showThreads'), 'frm');
		}
		
		// init objects
		$oForumObjects = $this->getForumObjects();		
		$forumObj = $oForumObjects['forumObj'];
		$frm = $oForumObjects['frm'];
		$file_obj = $oForumObjects['file_obj'];
		
		// save last access
		$forumObj->updateLastAccess($ilUser->getId(), (int) $this->objCurrentTopic->getId());
		
		$this->prepareThreadScreen($forumObj);
		
		$tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_view.html', 'Modules/Forum');
		
		// download file
		if($_GET['file'])
		{
			if(!$path = $file_obj->getFileDataByMD5Filename($_GET['file']))
			{
				ilUtil::sendInfo('Error reading file!');
			}
			else
			{
				ilUtil::deliverFile($path['path'], $path['clean_filename']);
			}
		}		

		$session_name = 'viewmode_'.$forumObj->getId();

		if ($_SESSION['viewmode'] == 'date')
		{
			$new_order = 'answers';
			$orderField = 'frm_posts_tree.fpt_date';
		}
		else
		{
			$new_order = 'date';
			$orderField = 'frm_posts_tree.rgt';
		}
				
		// get forum- and thread-data
		$frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));
		
		if(is_array($topicData = $frm->getOneTopic()))
		{
			// Visit-Counter for topic
			$this->objCurrentTopic->updateVisits();
			
			$tpl->setTitle($lng->txt('forums_thread')." \"".$this->objCurrentTopic->getSubject()."\"");			
		
			// ********************************************************************************
			// build location-links
			include_once('./Modules/Forum/classes/class.ilForumLocatorGUI.php');
			$frm_loc = new ilForumLocatorGUI();
			$frm_loc->setRefId($_GET['ref_id']);
			$frm_loc->setForum($frm);
			$frm_loc->setThread($this->objCurrentTopic->getId(), $this->objCurrentTopic->getSubject());
			$frm_loc->display();
																		 
			// set tabs					
			// menu template (contains linkbar)
			$menutpl = new ilTemplate('tpl.forums_threads_menu.html', true, true, 'Modules/Forum');

			include_once("./Services/Accessibility/classes/class.ilAccessKeyGUI.php");
			
			// mark all as read
			if($ilUser->getId() != ANONYMOUS_USER_ID &&
			   $forumObj->getCountUnread($ilUser->getId(), (int) $this->objCurrentTopic->getId()))
			{
				$this->ctrl->setParameter($this, 'mark_read', '1');
				$this->ctrl->setParameter($this, 'thr_pk',  $this->objCurrentTopic->getId());
				$ilToolbar->addButton($this->lng->txt('forums_mark_read'),
					$this->ctrl->getLinkTarget($this, 'viewThread'),
					ilAccessKeyGUI::getAttribute(ilAccessKey::MARK_ALL_READ)
				);
				$this->ctrl->clearParameters($this);
			}

			// print thread
			$this->ctrl->setParameterByClass('ilforumexportgui', 'print_thread', $this->objCurrentTopic->getId());
			$this->ctrl->setParameterByClass('ilforumexportgui', 'thr_top_fk', $this->objCurrentTopic->getForumId());
			$ilToolbar->addButton($this->lng->txt('forums_print_thread'),
				$this->ctrl->getLinkTargetByClass('ilforumexportgui', 'printThread')
			);
			$this->ctrl->clearParametersByClass('ilforumexportgui');
			
			// enable/disable notification
			if($ilUser->getId() != ANONYMOUS_USER_ID &&
			   $this->ilias->getSetting('forum_notification') != 0)
			{
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());

				// checks if notification is forced by moderator and if user is allowed to disable notification
				include_once 'Modules/Forum/classes/class.ilForumNotification.php';
				$frm_noti = new ilForumNotification($this->object->getRefId());
				$frm_noti->setUserId($ilUser->getId());
				$user_toggle = $frm_noti->isUserToggleNotification();

				if(!$user_toggle)
				{
					if($this->objCurrentTopic->isNotificationEnabled($ilUser->getId()))
					{
						$ilToolbar->addButton(
							$this->lng->txt('forums_disable_notification'),
							$this->ctrl->getLinkTarget($this, 'toggleThreadNotification')
						);
					}
					else
					{
						$ilToolbar->addButton(
							$this->lng->txt('forums_enable_notification'),
							$this->ctrl->getLinkTarget($this, 'toggleThreadNotification')
						);
					}
				}
				$this->ctrl->clearParameters($this);
			}

		if ($_GET['mark_read'])
		{
		$forumObj->markThreadRead($ilUser->getId(),(int) $this->objCurrentTopic->getId());
			ilUtil::sendInfo($lng->txt('forums_thread_marked'), true);
		}

		// delete post and its sub-posts
		require_once './Modules/Forum/classes/class.ilForum.php';

		if ($_GET['action'] == 'ready_delete' && $_POST['confirm'] != '')
		{
			if(!$this->objCurrentTopic->isClosed() &&
			   ($ilAccess->checkAccess('moderate_frm', '', (int) $_GET['ref_id']) ||
			    ($this->objCurrentPost->isOwner($ilUser->getId()) && !$this->objCurrentPost->hasReplies())))
			{
				$frm = new ilForum();

				$frm->setForumId($forumObj->getId());
				$frm->setForumRefId($forumObj->getRefId());

				$dead_thr = $frm->deletePost($this->objCurrentPost->getId());

				// if complete thread was deleted ...
				if ($dead_thr == $this->objCurrentTopic->getId())
				{

					$frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($forumObj->getId()));

					$topicData = $frm->getOneTopic();

					ilUtil::sendInfo($lng->txt('forums_post_deleted'), true);

					if ($topicData['top_num_threads'] > 0)
					{
						$this->ctrl->redirect($this, 'showThreads');
					}
					else
					{
						$this->ctrl->redirect($this, 'createThread');
					}
				}
				ilUtil::sendInfo($lng->txt('forums_post_deleted'));
			}
		}
			// form processing (censor)			
			if(!$this->objCurrentTopic->isClosed() && $_GET['action'] == 'ready_censor')
			{
				if($_POST['confirm'] != '' && $_GET['action'] == 'ready_censor')
				{
					$frm->postCensorship($this->handleFormInput($_POST['formData']['cens_message']), $this->objCurrentPost->getId(), 1);
				}
				else if($_POST['cancel'] != '' && $_GET['action'] == 'ready_censor')
				{
					$frm->postCensorship($this->handleFormInput($_POST['formData']['cens_message']), $this->objCurrentPost->getId());
				}
			}

			// get complete tree of thread	
			$first_node = $this->objCurrentTopic->getFirstPostNode();
			$this->objCurrentTopic->setOrderField($orderField);
			$subtree_nodes = $this->objCurrentTopic->getPostTree($first_node);

			// no posts
			if (!$posNum = count($subtree_nodes))
			{
				ilUtil::sendInfo($this->lng->txt('forums_no_posts_available'));	
			}			
					
			$pageHits = $frm->getPageHits();

			$z = 0;
		
			// navigation to browse
			if ($posNum > $pageHits)
			{
				$params = array(
					'ref_id'		=> $_GET['ref_id'],
					'thr_pk'		=> $this->objCurrentTopic->getId(),
					'orderby'		=> $_GET['orderby']
				);
		
				if (!$_GET['offset'])
				{
					$Start = 0;
				}
				else
				{
					$Start = $_GET['offset'];
				}
		
				$linkbar = ilUtil::Linkbar($ilCtrl->getLinkTarget($this, 'viewThread'), $posNum, $pageHits, $Start, $params);

				if($linkbar != '')
				{
					$menutpl->setCurrentBlock('linkbar');
					$menutpl->setVariable('LINKBAR', $linkbar);
					$menutpl->parseCurrentBlock();
				}
			}		

			$tpl->setVariable('THREAD_MENU', $menutpl->get());
		
			// assistance val for anchor-links
			$jump = 0;

			// generate post-dates
			foreach($subtree_nodes as $node)
			{
				$this->ctrl->clearParameters($this);
				
				if($this->objCurrentPost->getId() && $this->objCurrentPost->getId() == $node->getId())
				{
					$jump++;
				}
		
				if ($posNum > $pageHits && $z >= ($Start + $pageHits))
				{
					// if anchor-link was not found ...
					if ($this->objCurrentPost->getId() && $jump < 1)
					{
						$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
						$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
						$this->ctrl->setParameter($this, 'offset', ($Start + $pageHits));
						$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
						$this->ctrl->redirect($this, 'viewThread', $this->objCurrentPost->getId());
						exit();
					}
					else
					{
						break;
					}
				}
		
				if(($posNum > $pageHits && $z >= $Start) || $posNum <= $pageHits)
				{	
					if($this->objCurrentPost->getId() == $node->getId())
					{
						# actions for "active" post
						if($ilAccess->checkAccess('moderate_frm', '', (int) $_GET['ref_id']) ||
						   (!$ilAccess->checkAccess('moderate_frm', '', (int) $_GET['ref_id']) && $node->isActivated()))
						{
							// reply/edit
							if(!$this->objCurrentTopic->isClosed() && ($_GET['action'] == 'showreply' || $_GET['action'] == 'showedit'))
							{
								if($_GET['action'] == 'showedit' &&
								  ((!$ilAccess->checkAccess('moderate_frm', '', (int) $_GET['ref_id']) &&
								   !$node->isOwner($ilUser->getId())) || $node->isCensored()))
								{
								   	$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
								}
													   
								$tpl->setVariable('REPLY_ANKER', $this->objCurrentPost->getId());
								$oEditReplyForm = $this->getReplyEditForm();

								switch($this->objProperties->getSubjectSetting())
								{
									case 'add_re_to_subject':
										$subject = $this->lng->txt('post_reply').' '. $this->objCurrentPost->getSubject();
										break;

									case 'preset_subject':
										$subject = $this->objCurrentPost->getSubject();
										break;

									case 'empty_subject':
									default:
										$subject = NULL;
										break;
								}

								switch($_GET['action'])
								{
									case 'showreply':
										if($this->ctrl->getCmd() == 'savePost')
										{
											$oEditReplyForm->setValuesByPost();
										}										
										else if($this->ctrl->getCmd() == 'quotePost')
										{
											$oEditReplyForm->setValuesByPost();
											$oEditReplyForm->getItemByPostVar('message')->setValue(
												ilRTE::_replaceMediaObjectImageSrc(
                                                    $frm->prepareText($node->getMessage(), 1, $node->getLoginName())."\n".$oEditReplyForm->getInput('message'),    1
												)
											);
										}
										else
										{
											$oEditReplyForm->setValuesByArray(array(
												'alias' => '',
												'subject' => $subject,
												'message' => '',
												'notify' => 0,
												'userfile' => '',
												'del_file' => array()
											));
										}
										
										$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
										$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());

										$jsTpl = new ilTemplate('ilFrmPostAjaxHandler.js', true, true, 'Modules/Forum/');
										$jsTpl->setVariable('IL_FRM_QUOTE_CALLBACK_SRC',
											$this->ctrl->getLinkTarget($this, 'getQuotationHTMLAsynch', '', true));
										$this->ctrl->clearParameters($this);
										$this->tpl->setVariable('FRM_POST_JS', $jsTpl->get());
										break;
									case 'showedit':
										if($this->ctrl->getCmd() == 'savePost')
										{
											$oEditReplyForm->setValuesByPost();
										}
										else
										{
											$oEditReplyForm->setValuesByArray(array(
												'alias' => '',
												'subject' => $this->objCurrentPost->getSubject(),
												'message' => ilRTE::_replaceMediaObjectImageSrc($frm->prepareText($this->objCurrentPost->getMessage(), 2), 1),
												'notify' => $this->objCurrentPost->isNotificationEnabled() ? true : false,
												'userfile' => '',
												'del_file' => array()
											));
										}
										break;
								}
								$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
								$this->ctrl->setParameter($this, 'offset', (int)$_GET['offset']);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
								$this->ctrl->setParameter($this, 'action', $_GET['action']);
								$tpl->setVariable('FORM', $oEditReplyForm->getHTML());
								$this->ctrl->clearParameters($this);
			
							} // if ($_GET['action'] == 'showreply' || $_GET['action'] == 'showedit')
							else if(!$this->objCurrentTopic->isClosed() && $_GET['action'] == 'delete')
							{
								if($ilAccess->checkAccess('moderate_frm', '', (int) $_GET['ref_id']) ||
								  ($node->isOwner($ilUser->getId()) && !$node->hasReplies()))
								{
									// confirmation: delete
									$tpl->setVariable('FORM', $this->getDeleteFormHTML());							
								}
							} // else if ($_GET['action'] == 'delete')
							else if(!$this->objCurrentTopic->isClosed() && $_GET['action'] == 'censor')
							{
								if($ilAccess->checkAccess('moderate_frm', '', (int) $_GET['ref_id']))
								{
									// confirmation: censor / remove censorship
									$tpl->setVariable('FORM', $this->getCensorshipFormHTML());							
								}
							}
							else if (!$this->objCurrentTopic->isClosed() && $this->displayConfirmPostActivation())
							{
								if ($ilAccess->checkAccess('moderate_frm', '', (int) $_GET['ref_id']))
								{
									// confirmation: activate
									$tpl->setVariable('FORM', $this->getActivationFormHTML());							
								}
							} 
						}
					} // if ($this->objCurrentPost->getId() == $node->getId())				
					
					if ($this->objCurrentPost->getId() != $node->getId() ||
						($_GET['action'] != 'showreply' &&
						 $_GET['action'] != 'showedit' &&
						 $_GET['action'] != 'censor' &&
						 $_GET['action'] != 'delete' &&
						 #!$this->displayConfirmPostDeactivation() &&
						 !$this->displayConfirmPostActivation()
						))
					{
						if($ilAccess->checkAccess('moderate_frm', '', (int) $_GET['ref_id']) ||
						   (!$ilAccess->checkAccess('moderate_frm', '', (int) $_GET['ref_id']) && $node->isActivated()))
						{		
							// button: reply
							if (!$this->objCurrentTopic->isClosed() &&
								$ilAccess->checkAccess('add_post', '', (int) $_GET['ref_id']) && 
								!$node->isCensored())
							{						
								$tpl->setCurrentBlock('commands');
								$this->ctrl->setParameter($this, 'action', 'showreply');
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$tpl->setVariable('COMMANDS_COMMAND',	$this->ctrl->getLinkTarget($this, 'viewThread', $node->getId()));
								$tpl->setVariable('COMMANDS_TXT', $lng->txt('reply'));
								$this->ctrl->clearParameters($this);
								$tpl->parseCurrentBlock();				
							}
							
							// button: edit article
							if (!$this->objCurrentTopic->isClosed() &&
								($node->isOwner($ilUser->getId()) ||
								 $ilAccess->checkAccess('moderate_frm', '', (int) $_GET['ref_id'])) &&									
								 !$node->isCensored())
							{
								$tpl->setCurrentBlock('commands');
								$this->ctrl->setParameter($this, 'action', 'showedit');
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
								$tpl->setVariable('COMMANDS_COMMAND', $this->ctrl->getLinkTarget($this, 'viewThread', $node->getId()));
								$tpl->setVariable('COMMANDS_TXT', $lng->txt('edit'));
								$this->ctrl->clearParameters($this);
								$tpl->parseCurrentBlock();
							}		
							
							// button: print
							if (!$node->isCensored())
							{							
								$tpl->setCurrentBlock('commands');
								$this->ctrl->setParameterByClass('ilforumexportgui', 'print_post', $node->getId());
								$this->ctrl->setParameterByClass('ilforumexportgui', 'top_pk', $node->getForumId());
								$this->ctrl->setParameterByClass('ilforumexportgui', 'thr_pk', $node->getThreadId());
								$tpl->setVariable('COMMANDS_COMMAND',	$this->ctrl->getLinkTargetByClass('ilforumexportgui', 'printPost'));
								$tpl->setVariable('COMMANDS_TXT', $lng->txt('print'));
								$this->ctrl->clearParameters($this);
								$tpl->parseCurrentBlock();									
							}					
											
							# buttons for every post except the "active"						
							if (!$this->objCurrentTopic->isClosed() &&
							   ($ilAccess->checkAccess('moderate_frm', '', (int) $_GET['ref_id']) ||
							   ($node->isOwner($ilUser->getId()) && !$node->hasReplies())))
							{
								// button: delete							
								$tpl->setCurrentBlock('commands');							
								$this->ctrl->setParameter($this, 'action', 'delete');
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
								$tpl->setVariable('COMMANDS_COMMAND', $this->ctrl->getLinkTarget($this, 'viewThread', $node->getId()));							
								$tpl->setVariable('COMMANDS_TXT', $lng->txt('delete'));
								$this->ctrl->clearParameters($this);
								$tpl->parseCurrentBlock();
							}
							
							if (!$this->objCurrentTopic->isClosed() &&
								$ilAccess->checkAccess('moderate_frm', '', (int) $_GET['ref_id']))
							{	
								// button: censor							
								$tpl->setCurrentBlock('commands');
								$this->ctrl->setParameter($this, 'action', 'censor');
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);									
								$tpl->setVariable('COMMANDS_COMMAND', $this->ctrl->getLinkTarget($this, 'viewThread', $node->getId()));							
								$tpl->setVariable('COMMANDS_TXT', $lng->txt('censorship'));
								$this->ctrl->clearParameters($this);
								$tpl->parseCurrentBlock();
								
								// button: activation/deactivation							
								$tpl->setCurrentBlock('commands');
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);

								if (!$node->isActivated())
								{								
									$tpl->setVariable('COMMANDS_COMMAND', $this->ctrl->getLinkTarget($this, 'askForPostActivation', $node->getId()));
									$tpl->setVariable('COMMANDS_TXT', $lng->txt('activate_post'));
								}
								$this->ctrl->clearParameters($this);			
								$tpl->parseCurrentBlock();
							}												
							
							// button: mark read
							if ($ilUser->getId() != ANONYMOUS_USER_ID &&
							    !$node->isRead($ilUser->getId()))
							{
								$tpl->setCurrentBlock('commands');
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
								$this->ctrl->setParameter($this, 'viewmode', $_SESSION['viewmode']);
								$tpl->setVariable('COMMANDS_COMMAND', $this->ctrl->getLinkTarget($this, 'markPostRead', $node->getId()));
								$tpl->setVariable('COMMANDS_TXT', $lng->txt('is_read'));
								$this->ctrl->clearParameters($this);
								$tpl->parseCurrentBlock();
							}

							// button: mark unread
							if ($ilUser->getId() != ANONYMOUS_USER_ID &&
							    $node->isRead($ilUser->getId()))
							{
								$tpl->setCurrentBlock('commands');
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
								$this->ctrl->setParameter($this, 'viewmode', $_SESSION['viewmode']);
								$tpl->setVariable('COMMANDS_COMMAND', $this->ctrl->getLinkTarget($this, 'markPostUnread', $node->getId()));
								$tpl->setVariable('COMMANDS_TXT', $lng->txt('unread'));
								$this->ctrl->clearParameters($this);
								$tpl->parseCurrentBlock();
							}
						}
					} // if ($this->objCurrentPost->getId() != $node->getId())										
										
					// download post attachments
					$tmp_file_obj =& new ilFileDataForum($forumObj->getId(), $node->getId());
					if (count($tmp_file_obj->getFilesOfPost()))
					{
						if ($node->getId() != $this->objCurrentPost->getId() || $_GET['action'] != 'showedit')
						{
							foreach ($tmp_file_obj->getFilesOfPost() as $file)
							{
								$tpl->setCurrentBlock('attachment_download_row');
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());								
								$this->ctrl->setParameter($this, 'file', $file['md5']);
								$tpl->setVariable('HREF_DOWNLOAD', $this->ctrl->getLinkTarget($this, 'viewThread'));
								$tpl->setVariable('TXT_FILENAME', $file['name']);
								$this->ctrl->clearParameters($this);
								$tpl->parseCurrentBlock();
							}
							$tpl->setCurrentBlock('attachments');
							$tpl->setVariable('TXT_ATTACHMENTS_DOWNLOAD',$lng->txt('forums_attachments'));
							$tpl->setVariable('DOWNLOAD_IMG', ilUtil::getImagePath('icon_attachment.gif'));
							$tpl->setVariable('TXT_DOWNLOAD_ATTACHMENT', $lng->txt('forums_download_attachment'));
							$tpl->parseCurrentBlock();
						}
					}
		
					$tpl->setCurrentBlock('posts_row');
					
					// anker for every post					
					$tpl->setVariable('POST_ANKER', $node->getId());					
					
					//permanent link for every post																
				//	$tpl->setVariable('PERMA_LINK', ILIAS_HTTP_PATH."/goto.php?target="."frm"."_".$this->object->getRefId()."_".$node->getThreadId()."_".$node->getId()."&client_id=".CLIENT_ID);
					$tpl->setVariable('TXT_PERMA_LINK', $lng->txt('perma_link'));
					$tpl->setVariable('PERMA_TARGET', '_top');
					$tpl->setVariable('IMG_POSTING', ilUtil::getImagePath('icon_posting_s.gif'));
					
					$rowCol = ilUtil::switchColor($z, 'tblrow1', 'tblrow2');
					if ((  $_GET['action'] != 'delete' && $_GET['action'] != 'censor' && 
						   #!$this->displayConfirmPostDeactivation() &&						    
						   !$this->displayConfirmPostActivation()
						) 
						|| $this->objCurrentPost->getId() != $node->getId())
					{
						$tpl->setVariable('ROWCOL', $rowCol);
					}
					else
					{
						// highlight censored posts
						$rowCol = 'tblrowmarked';
					}
					
					// post is censored
					if ($node->isCensored())
					{
						// display censorship advice
						if ($_GET['action'] != 'censor')
						{
							$tpl->setVariable('TXT_CENSORSHIP_ADVICE', $this->lng->txt('post_censored_comment_by_moderator'));
						}
						
						// highlight censored posts
						$rowCol = 'tblrowmarked';
					}				
					
					// set row color
					$tpl->setVariable('ROWCOL', $rowCol);
		
					// get author data
					unset($author);
					if($node->getUserId())
					{
						$author = $this->getUserInstance($node->getUserId());
						if(null === $author)
						{
							unset($author);
							$node->setUserId(0);
						}	
					}					
		
					if($node->getUserId())
					{
						// GET USER DATA, USED FOR IMPORTED USERS											
						$usr_data = $frm->getUserData($node->getUserId(), $node->getImportName());						
					}					
					else
					{
						$usr_data = array(
							'usr_id' => 0,
							'login' => $node->getUserAlias(),
							'public_profile' => 'n'
						);	
					}
		
					$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
					$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
					$backurl = urlencode($this->ctrl->getLinkTarget($this, 'viewThread', $node->getId()));

					// get create- and update-dates
					if ($node->getUpdateUserId() > 0)
					{
						$span_class = '';
		
						// last update from moderator?
						$posMod = $frm->getModeratorFromPost($node->getId());
		
						if (is_array($posMod) && $posMod['top_mods'] > 0)
						{
							$MODS = $rbacreview->assignedUsers($posMod['top_mods']);
							
							if (is_array($MODS))
							{
								if (in_array($node->getUpdateUserId(), $MODS))
									$span_class = 'moderator_small';
							}
						}
		
						$node->setChangeDate($node->getChangeDate());

						$last_user_data = $frm->getUserData($node->getUpdateUserId());
						if ($span_class == '')
							$span_class = 'small';
		
		
						if($last_user_data['usr_id'])
						{
							if ($last_user_data['public_profile'] == 'n')
							{
								$edited_author = $last_user_data['login'];
							}
							else
							{
								$this->ctrl->setParameter($this, 'backurl', $backurl);
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'user', $last_user_data['usr_id']);
								$edited_author =  $this->ctrl->getLinkTarget($this, 'showUser');
								$edited_author = "<a href=\"".$edited_author."\">".$last_user_data['login']."</a>";
								$this->ctrl->clearParameters($this);
							}
						}
						else
						{
							$edited_author = $last_user_data['login'];
						}

						$tpl->setVariable('POST_UPDATE', $lng->txt('edited_at').': '.
							$frm->convertDate($node->getChangeDate()).' - '.strtolower($lng->txt('by')).' '.$edited_author);

					} // if ($node->getUpdateUserId() > 0)					

					// if post is not activated display message for the owner
					if(!$node->isActivated() && $node->isOwner($ilUser->getId()))
					{
						$tpl->setVariable('POST_NOT_ACTIVATED_YET', $this->lng->txt('frm_post_not_activated_yet'));
					}
					
					if($node->getUserId() && 
					   $node->getUserId() != ANONYMOUS_USER_ID)
					{
						$user_obj = $this->getUserInstance($usr_data['usr_id']);
						// user image
						$webspace_dir = ilUtil::getWebspaceDir();
						$image_dir = $webspace_dir.'/usr_images';
						$xthumb_file = $image_dir.'/usr_'.$user_obj->getID().'_xsmall.jpg';

						if ($user_obj->getPref('public_upload') == 'y' &&
							($user_obj->getPref('public_profile') == 'y' || 
							 $user_obj->getPref('public_profile') == 'g') &&
							@is_file($xthumb_file))
						{
							#$tpl->setCurrentBlock('usr_image');
							$tpl->setVariable('USR_IMAGE', $xthumb_file.'?t='.rand(1, 99999));
							#$tpl->parseCurrentBlock();
							//$tpl->setCurrentBlock('posts_row');
						}							

						if ($usr_data['public_profile'] == 'n')
						{
							$tpl->setVariable('AUTHOR',	$usr_data['login']);
						}
						else
						{
							$tpl->setVariable('TXT_REGISTERED', $lng->txt('registered_since').':');
							$tpl->setVariable('REGISTERED_SINCE',$frm->convertDate($author->getCreateDate()));		
							$this->ctrl->setParameter($this, 'backurl', $backurl);
							$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
							$this->ctrl->setParameter($this, 'user', $usr_data['usr_id']);
							$href = $this->ctrl->getLinkTarget($this, 'showUser');
							$tpl->setVariable('AUTHOR',	"<a href=\"".$href."\">".$usr_data['login']."</a>");
						}			
						
						if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
						{
							$numPosts = $frm->countUserArticles($author->id);
						}
						else
						{
							$numPosts = $frm->countActiveUserArticles($author->id);	
						}
						
						$tpl->setVariable('TXT_NUM_POSTS', $lng->txt('forums_posts').':');
						$tpl->setVariable('NUM_POSTS', $numPosts);

						if($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']) || $usr_data['public_profile'] != 'n')
						{
							$tpl->setVariable('USR_NAME', $usr_data['firstname'].' '.$usr_data['lastname']);
						}
						
						if(ilForum::_isModerator($_GET['ref_id'], $node->getUserId()))
						{						
							if($user_obj->getGender() == 'f')
							{
								$tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_f'));
							}
							else if($user_obj->getGender() == 'm')
							{
								$tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_m'));
							}
						}
					}
					else
					{
						if(strlen($usr_data['login']))
						{
							$tpl->setVariable('AUTHOR', $usr_data['login']);
							if($node->getUserId() != ANONYMOUS_USER_ID)
							{
								$tpl->setVariable('PSEUDONYM', $this->lng->txt('frm_pseudonym'));
							}							
						}
						else
						{
							$tpl->setVariable('AUTHOR', $lng->txt('forums_anonymous'));
						}						
					}
		
					// prepare post
					$node->setMessage($frm->prepareText($node->getMessage()));
		
					$tpl->setVariable('TXT_CREATE_DATE', $lng->txt('forums_thread_create_date'));
		
					if($ilUser->getId() == ANONYMOUS_USER_ID ||
					   $node->isRead($ilUser->getId()))
					{
						$tpl->setVariable('SUBJECT', $node->getSubject());
					}
					else
					{
						$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
						$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
						$this->ctrl->setParameter($this, 'offset', $Start);
						$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
						$this->ctrl->setParameter($this, 'viewmode', $_SESSION['viewmode']);
						$mark_post_target = $this->ctrl->getLinkTarget($this, 'markPostRead', $node->getId());

						$tpl->setVariable('SUBJECT',"<a href=\"".$mark_post_target."\"><b>".$node->getSubject()."</b></a>");

					}
		
					$tpl->setVariable('POST_DATE', $frm->convertDate($node->getCreateDate()));
					$tpl->setVariable('SPACER', "<hr noshade width=100% size=1 align='center' />");
						
					if (!$node->isCensored() ||
						($this->objCurrentPost->getId() == $node->getId() && $_GET['action'] == 'censor'))
					{
						// post from moderator?
						$modAuthor = $frm->getModeratorFromPost($node->getId());
		
						$spanClass = "";
		
						if (is_array($modAuthor) && $modAuthor['top_mods'] > 0)
						{
							unset($MODS);
		
							$MODS = $rbacreview->assignedUsers($modAuthor['top_mods']);
		
							if (is_array($MODS))
							{
								if (in_array($node->getUserId(), $MODS))
									$spanClass = 'moderator';
							}
						}
						
						// Add target="_top"
/*
						$node->setMessage(nl2br(
							preg_replace(
								'/<a((?![^>]+target="[^>]*")[^>]*)>/ims',
							 	'<a target="_top"$1>',
							 	$node->getMessage())
							)
						);
	## */
						if ($spanClass != "")
						{
							$tpl->setVariable('POST', "<span class=\"".$spanClass."\">".ilRTE::_replaceMediaObjectImageSrc($node->getMessage(), 1)."</span>");
						}
						else
						{
							$tpl->setVariable('POST', ilRTE::_replaceMediaObjectImageSrc($node->getMessage(), 1));
						}
					}
					else
					{
						$tpl->setVariable('POST', "<span class=\"moderator\">".nl2br($node->getCensorshipComment())."</span>");
					}
		
					$tpl->parseCurrentBlock();
		
				} // if (($posNum > $pageHits && $z >= $Start) || $posNum <= $pageHits)
		
				$z++;
		
			} // foreach($subtree_nodes as $node)
		}
		else
		{
			$tpl->setCurrentBlock('posts_no');
			$tpl->setVAriable('TXT_MSG_NO_POSTS_AVAILABLE', $lng->txt('forums_posts_not_available'));
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable('COUNT_POST', $lng->txt('forums_count_art').': '.$posNum);
		$tpl->setVariable('TXT_AUTHOR', $lng->txt('author'));
		$tpl->setVariable('TXT_POST', $lng->txt('forums_thread').': '.$this->objCurrentTopic->getSubject());

		$oThreadToolbar = clone $ilToolbar;
		$tpl->setVariable('THREAD_TOOLBAR', $oThreadToolbar->getHTML());
		
		$tpl->setVariable('TPLPATH', $tpl->vars['TPLPATH']);
		
		// permanent link
		include_once 'Services/PermanentLink/classes/class.ilPermanentLinkGUI.php';
		$permalink = new ilPermanentLinkGUI('frm', $this->object->getRefId(), '_'.$this->objCurrentTopic->getId());		
		$this->tpl->setVariable('PRMLINK', $permalink->getHTML());
		
		// Render tree
		if ($_SESSION['viewmode'] == 'answers') {
			$tpl->setLeftContent($this->getForumExplorer());
		}

		return true;
	}
	
	/**
	* Show user profile.
	*/
	function showUserObject()
	{		
		global $tpl;
	
		// we could actually call ilpublicuserprofilegui directly, this method
		// is not needed - but sadly used throughout the forum code
		// see above in execute command
						
		include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
		$profile_gui = new ilPublicUserProfileGUI($_GET['user']);
		$add = $this->getUserProfileAdditional($_GET["ref_id"], $_GET["user"]);
		$profile_gui->setAdditional($add);
		$profile_gui->setBackUrl($_GET['backurl']);
		$tpl->setContent($this->ctrl->getHTML($profile_gui));
	}
	
	/**
	 * Additional data for public profile
	 * 
	 * Used in showUserObject() and executeCommand()
	 * 
	 * @param int $a_forum_ref_id
	 * @param int $a_user_id
	 * @return array 
	 */
	protected function getUserProfileAdditional($a_forum_ref_id, $a_user_id)
	{
		global $lng, $ilAccess;
		
		if (!$ilAccess->checkAccess('read', '', $a_forum_ref_id))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		require_once './Modules/Forum/classes/class.ilForum.php';
		
		$lng->loadLanguageModule('forum');
		
		$ref_obj =& ilObjectFactory::getInstanceByRefId($a_forum_ref_id);
		if ($ref_obj->getType() == 'frm')
		{
			$forumObj = new ilObjForum($a_forum_ref_id);
			$frm =& $forumObj->Forum;
			$frm->setForumId($forumObj->getId());
			$frm->setForumRefId($forumObj->getRefId());
		}
		else
		{
			$frm =& new ilForum();
		}
		
		// count articles of user
		if ($ilAccess->checkAccess('moderate_frm', '', $a_forum_ref_id))
		{
			$numPosts = $frm->countUserArticles(addslashes($a_user_id));
		}
		else
		{
			$numPosts = $frm->countActiveUserArticles(addslashes($a_user_id));	
		}
		
		return array($lng->txt('forums_posts') => $numPosts);		
	}
	
	/**
	* Perform form action in threads list.
	*/
	function performThreadsActionObject()
	{
		global $lng, $ilUser, $ilAccess;

		unset($_SESSION['threads2move']);
		unset($_SESSION['forums_search_submitted']);
		unset($_SESSION['frm_topic_paste_expand']);	

		if (is_array($_POST['thread_ids']))
		{
			if ($_POST['selected_cmd'] == 'move')
			{
				if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
				{
					$_SESSION['threads2move'] = $_POST['thread_ids'];
					$this->moveThreadsObject();
				}
			}
			else if ($_POST['selected_cmd'] == 'enable_notifications' && $this->ilias->getSetting('forum_notification') != 0)
			{
				for ($i = 0; $i < count($_POST['thread_ids']); $i++)
				{
					$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
					$tmp_obj->enableNotification($ilUser->getId());
					unset($tmp_obj);
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}

			else if ($_POST['selected_cmd'] == 'disable_notifications' && $this->ilias->getSetting('forum_notification') != 0)
			{

				for ($i = 0; $i < count($_POST['thread_ids']); $i++)
				{

					$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
					$tmp_obj->disableNotification($ilUser->getId());
					unset($tmp_obj);
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}

			else if ($_POST['selected_cmd'] == 'close')
			{
				
				if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
				{

					for ($i = 0; $i < count($_POST['thread_ids']); $i++)
					{

						$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
						$tmp_obj->close();
						unset($tmp_obj);
					}
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}

			else if ($_POST['selected_cmd'] == 'reopen')
			{
				if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
				{

					for ($i = 0; $i < count($_POST['thread_ids']); $i++)
					{

						$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
						$tmp_obj->reopen();
						unset($tmp_obj);
					}
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}

			else if ($_POST['selected_cmd'] == 'makesticky')
			{
				if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
				{

					for ($i = 0; $i < count($_POST['thread_ids']); $i++)
					{
						$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
						$tmp_obj->makeSticky();
						unset($tmp_obj);
					}
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}

			else if ($_POST['selected_cmd'] == 'unmakesticky')
			{
				if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
				{

					for ($i = 0; $i < count($_POST['thread_ids']); $i++)
					{

						$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
						$tmp_obj->unmakeSticky();
						unset($tmp_obj);
					}
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}

			else if($_POST['selected_cmd'] == 'editThread')
			{
				if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
				{
					$count = count($_POST['thread_ids']);
					if($count != 1)
					{
						ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'), true);
						$this->ctrl->redirect($this, 'showThreads');
					}
					else
					{	foreach($_POST['thread_ids'] as $thread_id);
						{
							return $this->editThreadObject($thread_id);
						}
					}
				}

				$this->ctrl->redirect($this, 'showThreads');
			}
			else if ($_POST['selected_cmd'] == 'html')
			{
				$this->ctrl->setCmd('exportHTML');
				$this->ctrl->setCmdClass('ilForumExportGUI');
				$this->executeCommand();
			}

			else if ($_POST['selected_cmd'] == 'confirmDeleteThreads')
			{
				return $this->confirmDeleteThreads();
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt('topics_please_select_one_action'), true);
				$this->ctrl->redirect($this, 'showThreads');
			}

		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'), true);
			$this->ctrl->redirect($this, 'showThreads');
		}
	}
	
	public function performMoveThreadsObject()
	{
		global $lng, $ilAccess, $ilObjDataCache;
	
		if (!$ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		if (is_numeric($_POST['frm_ref_id']) && $_POST['frm_ref_id'] > 0)
		{	
			$this->object->Forum->moveThreads($_SESSION['threads2move'], $_GET['ref_id'], $ilObjDataCache->lookupObjId($_POST['frm_ref_id']));
						
			unset($_SESSION['threads2move']);
			unset($_SESSION['forums_search_submitted']);
			unset($_SESSION['frm_topic_paste_expand']);
			ilUtil::sendInfo($lng->txt('threads_moved_successfully'), true);			
			$this->ctrl->redirect($this, 'showThreads');		
			
			return true;
		}
		else
		{
			ilUtil::sendInfo($lng->txt('no_forum_selected'));		
			$this->moveThreadsObject();
		}
		
		return true;	
	}
	
	public function cancelMoveThreadsObject()
	{
		global $ilAccess;
		
		if (!$ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		unset($_SESSION['threads2move']);
		unset($_SESSION['forums_search_submitted']);
		unset($_SESSION['frm_topic_paste_expand']);
		$this->ctrl->redirect($this, 'showThreads');
		
		return true;
	}
	
	public function confirmMoveThreadsObject()
	{
		global $lng, $ilAccess;
	
		if (!$ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		if (! (is_numeric($_POST['frm_ref_id']) && $_POST['frm_ref_id'] > 0))
		{ 
			ilUtil::sendInfo($lng->txt('no_forum_selected'));		
			$this->moveThreadsObject();
			return true;
		}
				
		$this->hideToolbar(true);
		$this->moveThreadsObject(true);
	
		return true;	
	}
	
	public function searchForumsObject()
	{
		global $ilAccess;
		
		if (!$ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		$this->search_forums_for_moving_threads = true;
		$_SESSION['forums_search_submitted'] = true;
		
		$this->moveThreadsObject();

		return true;
	}
	
	public function moveThreadsObject($confirm = false)
	{
		global $ilAccess, $lng, $tree, $ilObjDataCache, $ilToolbar;
		
		if (!$ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
				
		$threads2move = $_SESSION['threads2move'];
		
		if(empty($threads2move))
		{
			ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'), true);
			$this->ctrl->redirect($this, 'showThreads');
		}		
		
		require_once 'Services/Table/classes/class.ilTable2GUI.php';		
				
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_move.html', 'Modules/Forum');

		if($confirm)
		{
			include_once('Services/Utilities/classes/class.ilConfirmationGUI.php');
			$c_gui = new ilConfirmationGUI();
			
			$c_gui->setFormAction($this->ctrl->getFormAction($this, 'performMoveThreads'));
			$c_gui->setHeaderText($this->lng->txt('sure_move_threads'));
			$c_gui->setCancel($this->lng->txt('cancel'), 'cancelMoveThreads');
			$c_gui->setConfirm($this->lng->txt('confirm'), 'performMoveThreads');	
			
			foreach($threads2move as $thr_pk)
			{			
				$c_gui->addItem('thread_ids[]', $thr_pk, ilForumTopic::_lookupTitle($thr_pk));
			}			
			
			$c_gui->addHiddenItem('frm_ref_id', $_POST['frm_ref_id']);
						
			$this->tpl->setVariable('CONFIRM_TABLE', $c_gui->getHTML());
		}	
		if(!$this->hideToolbar())
			$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this));

		$tblThr = new ilTable2GUI($this);
		$tblThr->setId('il_frm_thread_move_table_'.(int)$_GET['frm_ref_id']);
		$tblThr->setTitle($this->lng->txt('move_chosen_topics'));
		$tblThr->addColumn($this->lng->txt('subject'), 'top_name', '100%');
		$tblThr->disable('header');
		$tblThr->disable('footer');
		$tblThr->disable('linkbar');
		$tblThr->disable('sort');
		$tblThr->disable('linkbar');		
		$tblThr->setLimit(0);
		$tblThr->setRowTemplate('tpl.forums_threads_move_thr_row.html', 'Modules/Forum');	 		 	
		$tblThr->setDefaultOrderField('is_sticky');	
		$counter = 0;
		foreach ($threads2move as $thr_pk)
		{
			$objCurrentTopic = new ilForumTopic($thr_pk, $ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']));

			$result[$counter]['num'] = $counter + 1;
			$result[$counter]['thr_subject'] = $objCurrentTopic->getSubject();
			
			unset($objCurrentTopic);
			
			++$counter;			
		}			 	
		$tblThr->setData($result);		
		$this->tpl->setVariable('THREADS_TABLE', $tblThr->getHTML());
		
		
		
		// selection tree
		require_once 'Modules/Forum/classes/class.ilForumMoveTopicsExplorer.php';
		$exp = new ilForumMoveTopicsExplorer($this->ctrl->getLinkTarget($this, 'moveThreads'), 'frm_topic_paste_expand');	
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'moveThreads'));
		$exp->setTargetGet('ref_id');				
		$exp->setPostVar('frm_ref_id');
		$exp->excludeObjIdFromSelection($ilObjDataCache->lookupObjId($_GET['ref_id']));
		$exp->setCheckedItem($_POST['frm_ref_id']);
		
		// open current position in tree
		if(!is_array($_SESSION['frm_topic_paste_expand']))
		{
			global $tree;
			
			$_SESSION['frm_topic_paste_expand'] = array();
			
			$path = $tree->getPathId((int)$_GET['ref_id']);
			foreach((array)$path as $node_id)
			{
				if(!in_array($node_id, $_SESSION['frm_topic_paste_expand']))
					$_SESSION['frm_topic_paste_expand'][] = $node_id;
			}
		}

		if($_GET['frm_topic_paste_expand'] == '')
		{
			$expanded = $this->tree->readRootId();
		}
		else
		{
			$expanded = $_GET['frm_topic_paste_expand'];
		}

		$exp->setExpand($expanded);
		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();
		$this->tpl->setVariable('FRM_SELECTION_TREE', $output);		
		$this->tpl->setVariable('CMD_SUBMIT', 'confirmMoveThreads');
		$this->tpl->setVariable('TXT_SUBMIT', $this->lng->txt('paste'));
		$this->tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this, 'confirmMoveThreads'));


		return true;
	}
	
	private function initTopicCreateForm()
	{
		global $ilUser, $rbacsystem, $ilias;
		
		$this->create_topic_form_gui = new ilPropertyFormGUI();
		
		$this->create_topic_form_gui->setTitle($this->lng->txt('forums_new_thread'));
		$this->create_topic_form_gui->setTitleIcon(ilUtil::getImagePath('icon_frm.gif'));		
		$this->create_topic_form_gui->setTableWidth('100%');	
		
		// form action
		$this->create_topic_form_gui->setFormAction($this->ctrl->getFormAction($this, 'addThread'));

		if($this->objProperties->isAnonymized() == 1)
		{			
			$alias_gui = new ilTextInputGUI($this->lng->txt('forums_your_name'), 'alias');
			$alias_gui->setInfo($this->lng->txt('forums_use_alias'));
			$alias_gui->setMaxLength(255);
			$alias_gui->setSize(50);
			$this->create_topic_form_gui->addItem($alias_gui);
		}
		else
		{
			$alias_gui = new ilNonEditableValueGUI($this->lng->txt('forums_your_name'), 'alias');
			$alias_gui->setValue($ilUser->getLogin());
			$this->create_topic_form_gui->addItem($alias_gui);
		}
		
		// topic
		$subject_gui = new ilTextInputGUI($this->lng->txt('forums_thread'), 'subject');
		$subject_gui->setMaxLength(255);
		$subject_gui->setSize(50);
		$subject_gui->setRequired(true);
		$this->create_topic_form_gui->addItem($subject_gui);
		
		// message
		$post_gui = new ilTextAreaInputGUI($this->lng->txt('forums_the_post'), 'message');
		$post_gui->setCols(50);
		$post_gui->setRows(15);
		$post_gui->setRequired(true);
		$post_gui->setUseRte(true);
		$post_gui->addPlugin('latex');
		$post_gui->addButton('latex');
		$post_gui->addButton('pastelatex');
		$post_gui->addPlugin('ilfrmquote');
		$post_gui->addPlugin('code'); 
		$post_gui->addPlugin('ilimgupload');
		$post_gui->addButton('ilimgupload');
		$post_gui->removePlugin('advlink');
		$post_gui->removePlugin('ibrowser');
		$post_gui->removePlugin('image');
		$post_gui->usePurifier(true);	
		$post_gui->setRTERootBlockElement('');	
		$post_gui->setRTESupport($ilUser->getId(), 'frm~', 'frm_post', 'tpl.tinymce_frm_post.html');
		$post_gui->disableButtons(array(
			'charmap',
			'undo',
			'redo',
			'justifyleft',
			'justifycenter',
			'justifyright',
			'justifyfull',
			'anchor',
			'fullscreen',
			'cut',
			'copy',
			'paste',
			'pastetext',
			'formatselect',
			'image',
			'ibrowser'
		));		
				
		// purifier
		require_once 'Services/Html/classes/class.ilHtmlPurifierFactory.php';
		$post_gui->setPurifier(ilHtmlPurifierFactory::_getInstanceByType('frm_post'));
		$this->create_topic_form_gui->addItem($post_gui);		
		
		// file
		$fi = new ilFileWizardInputGUI($this->lng->txt('forums_attachments_add'), 'userfile');
		$fi->setFilenames(array(0 => ''));
		$this->create_topic_form_gui->addItem($fi);
		
		include_once 'Services/Mail/classes/class.ilMail.php';
		$umail = new ilMail($ilUser->getId());
		// catch hack attempts
		if ($rbacsystem->checkAccess('mail_visible', $umail->getMailObjectReferenceId()) &&
			!$this->objProperties->isAnonymized())
		{
			// direct notification
			$dir_notification_gui = new ilCheckboxInputGUI($this->lng->txt('forum_direct_notification'), 'notify');
			$dir_notification_gui->setInfo($this->lng->txt('forum_notify_me_directly'));
			$dir_notification_gui->setValue(1);			
			$this->create_topic_form_gui->addItem($dir_notification_gui);		
			
			if($ilias->getSetting('forum_notification') != 0)
			{
				// gen. notification
				$gen_notification_gui = new ilCheckboxInputGUI($this->lng->txt('forum_general_notification'), 'notify_posts');
				$gen_notification_gui->setInfo($this->lng->txt('forum_notify_me_generally'));
				$gen_notification_gui->setValue(1);
				$this->create_topic_form_gui->addItem($gen_notification_gui);
			}
		}		
		
		$this->create_topic_form_gui->addCommandButton('addThread', $this->lng->txt('submit'));
		$this->create_topic_form_gui->addCommandButton('showThreads', $this->lng->txt('cancel'));
	}
	
	/**
	* @access private
	*/
	private function setTopicCreateDefaultValues()
	{
		global $ilUser;

		$this->create_topic_form_gui->setValuesByArray(array(
			'subject' => '',
			'message' => '',
			'userfile' => '',
			'notify' => 0,
			'notify_posts' => 0
		));
	}	
	
	/**
	* New Thread form.
	*/
	function createThreadObject()
	{
		global $rbacsystem, $ilAccess, $lng;
		
		if(!$ilAccess->checkAccess('add_thread,add_post', '', (int)$_GET['ref_id']))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		$this->initTopicCreateForm();
		$this->setTopicCreateDefaultValues();
		
		$this->tpl->setContent($this->create_topic_form_gui->getHTML());
	}	


	/**
	* Add New Thread.
	*/
	function addThreadObject($a_prevent_redirect = false)
	{
		global $ilUser, $ilAccess;		
		
		$forumObj = new ilObjForum((int)$_GET['ref_id']);
		$frm = $forumObj->Forum;
		$frm->setForumId($forumObj->getId());
		$frm->setForumRefId($forumObj->getRefId());
		
		if(!$ilAccess->checkAccess('add_thread,add_post', '', $forumObj->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		$frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));
		
		$topicData = $frm->getOneTopic();

		$this->initTopicCreateForm();
		if($this->create_topic_form_gui->checkInput())
		{
			if($this->objProperties->isAnonymized())
			{
				if(!strlen($this->create_topic_form_gui->getInput('alias')))
				{
					$user_alias = $this->lng->txt('forums_anonymous');
				}
				else
				{
					$user_alias = $this->create_topic_form_gui->getInput('alias');
				}
			}
			else
			{
				$user_alias = $ilUser->getLogin();	
			}			
			
			// build new thread
			$newPost = $frm->generateThread(
				$topicData['top_pk'],
				($this->objProperties->isAnonymized() ? 0 : $ilUser->getId()),
				$this->handleFormInput($this->create_topic_form_gui->getInput('subject'), false),
				ilRTE::_replaceMediaObjectImageSrc($this->create_topic_form_gui->getInput('message'), 0),
				$this->create_topic_form_gui->getItemByPostVar('notify') ? (int)$this->create_topic_form_gui->getInput('notify') : 0,
				$this->create_topic_form_gui->getItemByPostVar('notify_posts') ? (int)$this->create_topic_form_gui->getInput('notify_posts') : 0,
				$user_alias
			);
			
			$file = $_FILES['userfile'];
			
			// file upload
			if(is_array($file) && !empty($file))
			{
				$tmp_file_obj =& new ilFileDataForum($forumObj->getId(), $newPost);
				$tmp_file_obj->storeUploadedFile($file);
			}
			
			// Visit-Counter
			$frm->setDbTable('frm_data');
			$frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array($topicData['top_pk']));
			$frm->updateVisits($topicData['top_pk']);			

			$frm->setMDB2WhereCondition('thr_top_fk = %s AND thr_subject = %s AND thr_num_posts = 1 ', 
										array('integer', 'text'), array($topicData['top_pk'], $this->create_topic_form_gui->getInput('subject')));			
			
			// copy temporary media objects (frm~)
			include_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
			$mediaObjects = ilRTE::_getMediaObjects($this->create_topic_form_gui->getInput('message'), 0);
			foreach($mediaObjects as $mob)
			{
				if(ilObjMediaObject::_exists($mob))
				{
					ilObjMediaObject::_removeUsage($mob, 'frm~:html', $ilUser->getId());
					ilObjMediaObject::_saveUsage($mob, 'frm:html', $newPost);
				}
			}
			
			if(!$a_prevent_redirect)
			{
				ilUtil::sendSuccess($this->lng->txt('forums_thread_new_entry'), true);
				$this->ctrl->redirect($this);
			}
			else
			{
				return $newPost;
			}
		}
		else
		{
			$this->create_topic_form_gui->setValuesByPost();

			if(!$this->objProperties->isAnonymized())
			{
				$this->create_topic_form_gui->getItemByPostVar('alias')->setValue($ilUser->getLogin());
			}

			return $this->tpl->setContent($this->create_topic_form_gui->getHTML());
		}
	}	
	
	/**
	* Show Notification Tab
	*/
	/*
	public function showThreadNotificationObject()
	{
		global $lng, $tpl, $ilias, $ilUser, $ilTabs, $ilDB, $ilAccess;
		
		require_once './Modules/Forum/classes/class.ilObjForum.php';
		require_once './Modules/Forum/classes/class.ilFileDataForum.php';
		
		if (!$ilAccess->checkAccess('read,visible', '', $_GET['ref_id']))
		{
			$ilias->raiseError($lng->txt('permission_denied'), $ilias->error_obj->MESSAGE);
		}

		$lng->loadLanguageModule('forum');
		
		$forumObj = new ilObjForum($_GET['ref_id']);
		$frm =& $forumObj->Forum;
		
		$frm->setForumId($forumObj->getId());
		$frm->setForumRefId($forumObj->getRefId());
		
		$this->prepareThreadScreen($forumObj);
		$tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_notification.html', 'Modules/Forum');
		$ilTabs->setTabActive('forums_notification');		
		
		// get forum- and thread-data 
		// use setMDB2WhereCondition ..
		$frm->setWhereCondition('top_frm_fk = '.$ilDB->quote($frm->getForumId()));		
		if (is_array($topicData = $frm->getOneTopic()))
		{
			$tpl->setTitle($lng->txt('forums_thread')." \"".$this->objCurrentTopic->getSubject()."\"");
			
			// build location-links
			include_once('./Modules/Forum/classes/class.ilForumLocatorGUI.php');
			$frm_loc =& new ilForumLocatorGUI();
			$frm_loc->setRefId($_GET['ref_id']);
			$frm_loc->setForum($frm);
			$frm_loc->setThread($this->objCurrentTopic->getId(), $this->objCurrentTopic->getSubject());
			$frm_loc->display();
		
			// set tabs
			// display different buttons depending on viewmode		
			$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
			if ($this->objCurrentTopic->isNotificationEnabled($ilUser->getId()))
			{				
				ilUtil::sendInfo($lng->txt('forums_notification_is_enabled'));
				$tpl->setVariable('TXT_SUBMIT', $lng->txt('forums_disable_notification'));				
			}
			else
			{
				ilUtil::sendInfo($lng->txt('forums_notification_is_disabled'));
				$tpl->setVariable('TXT_SUBMIT', $lng->txt('forums_enable_notification'));
			}
			
			$tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this, 'toggleThreadNotificationTab'));
			$tpl->setVariable('CMD', 'toggleThreadNotificationTab');
			
			$this->ctrl->clearParameters($this);
		}
		
		return true;
	}
	*/
	
	/**
	* Enable forum notification.
	*/
	public function enableForumNotificationObject()
	{
		global $ilUser;
 
		$forumObj = new ilObjForum($_GET['ref_id']);
		$frm =& $forumObj->Forum;
		$frm->setForumId($forumObj->getId());

		$frm->enableForumNotification($ilUser->getId());
		
		ilUtil::sendInfo($this->lng->txt('forums_forum_notification_enabled'));
		
		$this->showThreadsObject();
		
		return true;
	}

	/**
	* Disable forum notification.
	*/
	public function disableForumNotificationObject()
	{
		global $ilUser;
		
		$forumObj = new ilObjForum($_GET['ref_id']);
		$frm =& $forumObj->Forum;
		$frm->setForumId($forumObj->getId());

		$frm->disableForumNotification($ilUser->getId());
		
		$this->showThreadsObject();
		
		return true;
	}	

	/**
	* No editing allowd in forums. Notifications only.
	*/
	public function checkEnableColumnEdit()
	{
		return false;
	}
	
	/**
	* Set column settings.
	*/
	public function setColumnSettings($column_gui)
	{
		global $lng, $ilAccess;
		
		$lng->loadLanguageModule('frm');
		$column_gui->setBlockProperty('news', 'title', $lng->txt('frm_latest_postings'));
		$column_gui->setBlockProperty("news", "prevent_aggregation", true);
		
		$column_gui->setRepositoryMode(true);
		if ($ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$news_set = new ilSetting('news');
			$enable_internal_rss = $news_set->get('enable_rss_for_internal');
			
			if ($enable_internal_rss)
			{
				$column_gui->setBlockProperty('news', 'settings', true);
				$column_gui->setBlockProperty('news', 'public_notifications_option', true);
			}
		}
	}
	
	// Copy wizard
	/**
	 * 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function copyWizardHasOptions($a_mode)
	{
	 	switch($a_mode)
	 	{
	 		case self::COPY_WIZARD_NEEDS_PAGE:
	 			return true;
	 		
	 		default:
	 			return false;
	 	}
	}
	
	/**
	 * Show selection of starting threads
	 *
	 * @access public
	 */
	public function cloneWizardPageObject()
	{
		global $ilObjDataCache;
		
	 	if (!$_POST['clone_source'])
	 	{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			if (isset($_SESSION['wizard_search_title']))
			{
				$this->searchCloneSourceObject();
			}
			else
			{
				$this->createObject();
			}
			return false;
	 	}
		$source_id = $_POST['clone_source'];
		$this->lng->loadLanguageModule('frm');

	 	$new_type = $_REQUEST['new_type'];
	 	$this->ctrl->setParameter($this, 'clone_source', (int) $_POST['clone_source']);
	 	$this->ctrl->setParameter($this, 'new_type', $new_type);
	 	
	 	$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.frm_wizard_page.html', 'Modules/Forum');
	 	$this->tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this));
	 	$this->tpl->setVariable('TYPE_IMG', ilUtil::getImagePath('icon_'.$new_type.'.gif'));
	 	$this->tpl->setVariable('ALT_IMG', $this->lng->txt('obj_'.$new_type));
	 	$this->tpl->setVariable('TXT_DUPLICATE', $this->lng->txt('frm_wizard_page'));
	 	$this->tpl->setVariable('INFO_THREADS', $this->lng->txt('fmr_copy_threads_info'));
	 	$this->tpl->setVariable('THREADS', $this->lng->txt('forums_threads'));
	 	
	 	$forum_id = $ilObjDataCache->lookupObjId((int) $_POST['clone_source']);
	 	include_once('Modules/Forum/classes/class.ilForum.php');
	 	$threads = ilForum::_getThreads($forum_id, ilForum::SORT_TITLE);
	 	foreach ($threads as $thread_id => $title)
	 	{
	 		$this->tpl->setCurrentBlock('thread_row');
	 		$this->tpl->setVariable('CHECK_THREAD', ilUtil::formCheckbox(0, 'cp_options['.$source_id.'][threads][]', $thread_id));
	 		$this->tpl->setVariable('NAME_THREAD', $title);
	 		$this->tpl->parseCurrentBlock();
	 	}
	 	$this->tpl->setVariable('SELECT_ALL', $this->lng->txt('select_all'));
	 	$this->tpl->setVariable('JS_FIELD', 'cp_options['.$source_id.'][threads]');
	 	$this->tpl->setVariable('BTN_COPY', $this->lng->txt('obj_'.$new_type.'_duplicate'));
	 	if (isset($_SESSION['wizard_search_title']))
	 	{
	 		$this->tpl->setVariable('BACK_CMD', 'searchCloneSource');
	 	}
	 	else
	 	{
	 		$this->tpl->setVariable('BACK_CMD', 'create');
	 	}
 		$this->tpl->setVariable('BTN_BACK', $this->lng->txt('btn_back'));
	}
	
	public function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ''), '', $_GET['ref_id']);
		}
	}

	/**
	* Handle subject and message text input. < and > are escaped
	* because HTML is not allowed.
	*
	* @param	string		$a_text		input text
	*/
	function handleFormInput($a_text, $a_stripslashes = true)
	{
		$a_text = str_replace("<", "&lt;", $a_text);
		$a_text = str_replace(">", "&gt;", $a_text);
		if($a_stripslashes)
			$a_text = ilUtil::stripSlashes($a_text);
		
		return $a_text;
	}
	
	/**
	*
	*/
	function prepareFormOutput($a_text)
	{
		$a_text = str_replace("&lt;", "<", $a_text);
		$a_text = str_replace("&gt;", ">", $a_text);
		$a_text = ilUtil::prepareFormOutput($a_text);
		return $a_text;
	}

	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}

	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess;

		if (!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$info->enablePrivateNotes();
		
		if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			//$info->enableNews();
		}

		// no news editing for files, just notifications
//		$info->enableNewsEditing(false);
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
//			$news_set = new ilSetting("news");
//			$enable_internal_rss = $news_set->get("enable_rss_for_internal");
			
//			if ($enable_internal_rss)
//			{
//				$info->setBlockProperty("news", "settings", true);
//				$info->setBlockProperty("news", "public_notifications_option", true);
//			}
		}

		
		// standard meta data
		$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
		
		// forward the command
		$this->ctrl->forwardCommand($info);
	}
	
	/**
	 * 
	 * Caching method for user instances
	 *  
	 * @access	private
	 * @param	integer		$a_usr_id	Id of a user instance
	 * @return	ilObjUser	or null
	 * 
	 */
	private function getUserInstance($a_usr_id)
	{		
		static $userObjectCache = array();
		
		if(isset($userObjectCache[$a_usr_id])) return $userObjectCache[$a_usr_id];
		
		$oUser = ilObjectFactory::getInstanceByObjId($a_usr_id, false);
		if(!is_object($oUser) || $oUser->getType() != 'usr')
		{
			$userObjectCache[$a_usr_id] = null;
		}
		else
		{
			$userObjectCache[$a_usr_id] = $oUser;
		}		 
		
		return $userObjectCache[$a_usr_id];
	}

	/**
	 *
	 * Saves the notifcation settings
	 *
	 * @access	public
	 *
	 */
	public function updateNotificationSettingsObject()
	{
		// instantiate the property form
		$this->initNotificationSettingsForm();

		// check input
		if($this->notificationSettingsForm->checkInput())
		{
			if($_POST['notification_type']== 'all_users')
			{
				// set values and call update
				$this->objProperties->setAdminForceNoti(1);
				$this->objProperties->setUserToggleNoti((int)$this->notificationSettingsForm->getInput('usr_toggle'));
				$this->objProperties->setNotificationType('all_users');
			}
			else if($_POST['notification_type']== 'per_user')
			{
				$this->objProperties->setNotificationType('per_user');
				$this->objProperties->setAdminForceNoti(1);
			}
			else //  if($_POST['notification_type'] == 'default')
			{
				$this->objProperties->setNotificationType('default');
				$this->objProperties->setAdminForceNoti(0);
				$this->objProperties->setUserToggleNoti(0);
			}

			$this->objProperties->update();

			// print success message
			ilUtil::sendInfo($this->lng->txt('saved_successfully'));
		}
		$this->notificationSettingsForm->setValuesByPost();

		return $this->showMembersObject();
	}

	/**
	 *
	 * Initializes a new form for notifcation settings
	 *
	 * @access	private
	 * @param	boolean	True in case the form did not exist before calling this method, otherwise false
	 *
	 */
	private function initNotificationSettingsForm()
	{
		if(null === $this->notificationSettingsForm)
		{
			$form = new ilPropertyFormGUI();
			$form->setFormAction($this->ctrl->getFormAction($this, 'updateNotificationSettings'));
			$form->setTitle($this->lng->txt('forums_notification_settings'));

			$radio_grp = new ilRadioGroupInputGUI('','notification_type');
			$radio_grp->setValue('default');

			$opt_default  = new ilRadioOption($this->lng->txt("user_decides_notification"), 'default');
			$opt_0 = new ilRadioOption($this->lng->txt("settings_for_all_members"), 'all_users');
			$opt_1 = new ilRadioOption($this->lng->txt("settings_per_members"), 'per_user');

			$radio_grp->addOption($opt_default, 'default');
			$radio_grp->addOption($opt_0, 'all_users');
			$radio_grp->addOption($opt_1, 'per_user');

			$chb_2 = new ilCheckboxInputGUI($this->lng->txt('user_toggle_noti'), 'usr_toggle');
			$chb_2->setValue(1);

			$opt_0->addSubItem($chb_2);
			$form->addItem($radio_grp);

			$form->addCommandButton('updateNotificationSettings', $this->lng->txt('save'));

			$this->notificationSettingsForm = $form;

			return false;
		}

		return true;
	}
	public function getIcon($user_toggle_noti)
	{
		$icon = $user_toggle_noti
		? "<img src=\"".ilUtil::getImagePath("icon_ok.gif")."\" alt=\"".$this->lng->txt("enabled")."\" title=\"".$this->lng->txt("enabled")."\" border=\"0\" vspace=\"0\"/>"
		: "<img src=\"".ilUtil::getImagePath("icon_not_ok.gif")."\" alt=\"".$this->lng->txt("disabled")."\" title=\"".$this->lng->txt("disabled")."\" border=\"0\" vspace=\"0\"/>";
		return $icon;
	}
	/**
	 *
	 * Shows different user sections and their notifcation status
	 *
	 * @access	public
	 *
	 */
	public function showMembersObject()
	{
		global $tree, $ilObjDataCache, $tpl, $ilTabs;

		$tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_members_list.html', 'Modules/Forum');

		$ilTabs->setTabActive('settings');
		$this->settingsTabs();
		// instantiate the property form
		if(!$this->initNotificationSettingsForm())
		{
			// if the form was just created set the values fetched from database
			$this->notificationSettingsForm->setValuesByArray(array(
				'notification_type' => $this->objProperties->getNotificationType(),
				'adm_force' => (bool)$this->objProperties->isAdminForceNoti(),
				'usr_toggle' => (bool)$this->objProperties->isUserToggleNoti()
			));
		}

		// set form html into template
		$tpl->setVariable('NOTIFICATIONS_SETTINGS_FORM', $this->notificationSettingsForm->getHTML());

		include_once 'Modules/Forum/classes/class.ilForumNotification.php';
		include_once 'Modules/Forum/classes/class.ilObjForum.php';

		$frm_noti = new ilForumNotification($_GET['ref_id']);

		$parent_ref_id = $tree->getParentId((int)$_GET['ref_id']);
		$parent_obj = ilObjectFactory::getInstanceByRefId($parent_ref_id);
		//$parent_type = ilForumNotification::_isParentNodeGrpCrs($_GET['ref_id']);

	//	if($parent_type == 'crs')
		if($parent_obj->getType() == 'crs')
		{
			include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
			$oParticipants = ilCourseParticipants::_getInstanceByObjId(
			#$frm_noti->getForumId());#
			$parent_obj->getId());

		}
		else //if($parent_type == 'grp')
		if($parent_obj->getType() == 'grp')
		{
			include_once 'Modules/Group/classes/class.ilGroupParticipants.php';
			$oParticipants = ilGroupParticipants::_getInstanceByObjId(
			#$frm_noti->getForumId());#
			$parent_obj->getId());
		}

		$moderator_ids = $frm_noti->_getModerators($_GET['ref_id']);

		$admin_ids = $oParticipants->getAdmins();
		$member_ids = $oParticipants->getMembers();
		$tutor_ids = $oParticipants->getTutors();

		$moderators = array();
		$admins = array();
		$members = array();
		$tutors = array();

		if($this->objProperties->getNotificationType() == 'default')
		{
			// update forum_notification table
			include_once './Modules/Forum/classes/class.ilForumNotification.php';
			$forum_noti = new ilForumNotification($this->ref_id);
			$forum_noti->setAdminForce($this->objProperties->isAdminForceNoti());
			$forum_noti->setUserToggle($this->objProperties->isUserToggleNoti());
			$forum_noti->setForumId($this->objProperties->getObjId());

			if($_POST['notification_type'] == 'default')
			{
				// delete all notifications set by admin
				$forum_noti->deleteNotificationAllUsers();
			}
		}
		else if($this->objProperties->getNotificationType() == 'per_user')
		{
			$counter = 0;
			foreach($moderator_ids as $user_id)
			{
				$frm_noti->setUserId($user_id);
				$admin_force_noti = $frm_noti->isAdminForceNotification();
				$user_toggle_noti = $frm_noti->isUserToggleNotification();
				$icon_ok = $this->getIcon(!$user_toggle_noti);

				$moderators[$counter]['user_id'] = ilUtil::formCheckbox(0, 'user_id[]', $user_id);
				$moderators[$counter]['login'] = ilObjUser::_lookupLogin($user_id);
				$name = ilObjUser::_lookupName($user_id);
				$moderators[$counter]['firstname'] = $name['firstname'];
				$moderators[$counter]['lastname'] = $name['lastname'];
				$moderators[$counter]['user_toggle_noti'] = $icon_ok;
				$counter++;
			}

			$counter = 0;
			foreach($admin_ids as $user_id)
			{
				$frm_noti->setUserId($user_id);
				$admin_force_noti = $frm_noti->isAdminForceNotification();
				$user_toggle_noti = $frm_noti->isUserToggleNotification();
				$icon_ok = $this->getIcon(!$user_toggle_noti);

				$admins[$counter]['user_id'] = ilUtil::formCheckbox(0, 'user_id[]', $user_id);
				$admins[$counter]['login'] = ilObjUser::_lookupLogin($user_id);
				$name = ilObjUser::_lookupName($user_id);
				$admins[$counter]['firstname'] = $name['firstname'];
				$admins[$counter]['lastname'] = $name['lastname'];
				$admins[$counter]['user_toggle_noti'] =  $icon_ok;
				$counter++;
			}

			$counter = 0;
			foreach($member_ids as $user_id)
			{
				$frm_noti->setUserId($user_id);
				$admin_force_noti = $frm_noti->isAdminForceNotification();
				$user_toggle_noti = $frm_noti->isUserToggleNotification();
				$icon_ok = $this->getIcon(!$user_toggle_noti);

				$members[$counter]['user_id'] = ilUtil::formCheckbox(0, 'user_id[]', $user_id);
				$members[$counter]['login'] = ilObjUser::_lookupLogin($user_id);
				$name = ilObjUser::_lookupName($user_id);
				$members[$counter]['firstname'] = $name['firstname'];
				$members[$counter]['lastname'] = $name['lastname'];
				$members[$counter]['user_toggle_noti'] = $icon_ok;
				$counter++;
			}

			$counter = 0;
			foreach($tutor_ids as $user_id)
			{

				$frm_noti->setUserId($user_id);
				$admin_force_noti = $frm_noti->isAdminForceNotification();
				$user_toggle_noti = $frm_noti->isUserToggleNotification();
				$icon_ok = $this->getIcon(!$user_toggle_noti);

				$tutors[$counter]['user_id'] = ilUtil::formCheckbox(0, 'user_id[]', $user_id);
				$tutors[$counter]['login'] = ilObjUser::_lookupLogin($user_id);
				$name = ilObjUser::_lookupName($user_id);
				$tutors[$counter]['firstname'] = $name['firstname'];
				$tutors[$counter]['lastname'] = $name['lastname'];
				$tutors[$counter]['user_toggle_noti'] = $icon_ok;
				$counter++;
			}

			$this->__showMembersTable($moderators, $admins, $members, $tutors);
		}
		else
		{
			$frm_noti = new ilForumNotification($_GET['ref_id']);
			$all_notis = $frm_noti->read();

			foreach($member_ids as $user_id)
			{
				$frm_noti->setUserId($user_id);

				$frm_noti->setAdminForce(1);
				$frm_noti->setUserToggle($this->objProperties->isUserToggleNoti());

				if(array_key_exists($user_id, $all_notis))
				{
					$res = $frm_noti->update();
				}
				else
				{
					$res = $frm_noti->insertAdminForce();
				}
			}
		}
	}

	function __showMembersTable($moderators,$admins,$members,$tutors)
	{
		global $lng, $tpl, $ilTabs, $ilCtrl;

		include_once 'Services/Table/classes/class.ilTable2GUI.php';

		if($moderators)
		{
			$tbl_mod = new ilTable2GUI($this);
			$tbl_mod->setId('tbl_id_mod');
			$tbl_mod->setFormAction($ilCtrl->getFormAction($this, "showMembers"));
			$tbl_mod->setTitle($lng->txt('moderators'));

			$tbl_mod->addColumn('', '', '1%');
			$tbl_mod->addColumn($lng->txt('login'), '', '10%');
			$tbl_mod->addColumn($lng->txt('firstname'), '', '10%');
			$tbl_mod->addColumn($lng->txt('lastname'), '', '10%');
#			$tbl_mod->addColumn($lng->txt('admin_force_noti'), '', '10%');
			$tbl_mod->addColumn($lng->txt('user_toggle_noti'), '', '10%');
			$tbl_mod->setSelectAllCheckbox('user_id');

			$tbl_mod->setRowTemplate('tpl.forums_members_row.html', 'Modules/Forum');
			$tbl_mod->setData($moderators);

#			$tbl_mod->addMultiCommand('enableAdminForceNoti',$lng->txt('enable_admin_force'));
#			$tbl_mod->addMultiCommand('disableAdminForceNoti',$lng->txt('disable_admin_force'));
			$tbl_mod->addMultiCommand('enableHideUserToggleNoti',$lng->txt('enable_hide_user_toggle'));
			$tbl_mod->addMultiCommand('disableHideUserToggleNoti',$lng->txt('disable_hide_user_toggle'));

			$tpl->setCurrentBlock('moderators_table');
			$tpl->setVariable('MODERATORS',$tbl_mod->getHTML());
		}

		if($admins)
		{
			$tbl_adm = new ilTable2GUI($this);
			$tbl_adm->setId('tbl_id_adm');
			$tbl_adm->setFormAction($ilCtrl->getFormAction($this, "showMembers"));
			$tbl_adm->setTitle($lng->txt('administrator'));

			$tbl_adm->addColumn('', '', '1%');
			$tbl_adm->addColumn($lng->txt('login'), '', '10%');
			$tbl_adm->addColumn($lng->txt('firstname'), '', '10%');
			$tbl_adm->addColumn($lng->txt('lastname'), '', '10%');
	#		$tbl_adm->addColumn($lng->txt('admin_force_noti'), '', '10%');
			$tbl_adm->addColumn($lng->txt('user_toggle_noti'), '', '10%');
			$tbl_adm->setSelectAllCheckbox('user_id');
			$tbl_adm->setRowTemplate('tpl.forums_members_row.html', 'Modules/Forum');

			$tbl_adm->setData($admins);
	#		$tbl_adm->addMultiCommand('enableAdminForceNoti',$lng->txt('enable_admin_force'));
#			$tbl_adm->addMultiCommand('disableAdminForceNoti',$lng->txt('disable_admin_force'));
			$tbl_adm->addMultiCommand('enableHideUserToggleNoti',$lng->txt('enable_hide_user_toggle'));
			$tbl_adm->addMultiCommand('disableHideUserToggleNoti',$lng->txt('disable_hide_user_toggle'));

			$tpl->setCurrentBlock('admins_table');
			$tpl->setVariable('ADMINS',$tbl_adm->getHTML());

		}


		if($members)
		{
			$tbl_mem = new ilTable2GUI($this);
			$tbl_mem->setId('tbl_id_mem');
			$tbl_mem->setFormAction($ilCtrl->getFormAction($this, "showMembers"));
			$tbl_mem->setTitle($lng->txt('members'));

			$tbl_mem->addColumn('', '', '1%');
			$tbl_mem->addColumn($lng->txt('login'), '', '10%');
			$tbl_mem->addColumn($lng->txt('firstname'), '', '10%');
			$tbl_mem->addColumn($lng->txt('lastname'), '', '10%');
	#		$tbl_mem->addColumn($lng->txt('admin_force_noti'), '', '10%');
			$tbl_mem->addColumn($lng->txt('user_toggle_noti'), '', '10%');
			$tbl_mem->setSelectAllCheckbox('user_id');
			$tbl_mem->setRowTemplate('tpl.forums_members_row.html', 'Modules/Forum');
			$tbl_mem->setData($members);

	#		$tbl_mem->addMultiCommand('enableAdminForceNoti',$lng->txt('enable_admin_force'));
	#		$tbl_mem->addMultiCommand('disableAdminForceNoti',$lng->txt('disable_admin_force'));
			$tbl_mem->addMultiCommand('enableHideUserToggleNoti',$lng->txt('enable_hide_user_toggle'));
			$tbl_mem->addMultiCommand('disableHideUserToggleNoti',$lng->txt('disable_hide_user_toggle'));

			$tpl->setCurrentBlock('members_table');
			$tpl->setVariable('MEMBERS',$tbl_mem->getHTML());

		}
		if($tutors)
		{
			$tbl_tut = new ilTable2GUI($this);
			$tbl_tut->setId('tbl_id_tut');
			$tbl_tut->setFormAction($ilCtrl->getFormAction($this, "showMembers"));
			$tbl_tut->setTitle($lng->txt('tutors'));

			$tbl_tut->addColumn('', '', '1%');
			$tbl_tut->addColumn($lng->txt('login'), '', '10%');
			$tbl_tut->addColumn($lng->txt('firstname'), '', '10%');
			$tbl_tut->addColumn($lng->txt('lastname'), '', '10%');
		#	$tbl_tut->addColumn($lng->txt('admin_force_noti'), '', '10%');
			$tbl_tut->addColumn($lng->txt('user_toggle_noti'), '', '10%');
			$tbl_tut->setSelectAllCheckbox('user_id');
			$tbl_tut->setRowTemplate('tpl.forums_members_row.html', 'Modules/Forum');
			$tbl_tut->setData($tutors);

		#	$tbl_tut->addMultiCommand('enableAdminForceNoti',$lng->txt('enable_admin_force'));
		#	$tbl_tut->addMultiCommand('disableAdminForceNoti',$lng->txt('disable_admin_force'));
			$tbl_tut->addMultiCommand('enableHideUserToggleNoti',$lng->txt('enable_hide_user_toggle'));
			$tbl_tut->addMultiCommand('disableHideUserToggleNoti',$lng->txt('disable_hide_user_toggle'));

			$tpl->setCurrentBlock('tutors_table');
			$tpl->setVariable('TUTORS',$tbl_tut->getHTML());
		}
	}

	/**
	 *
	 * enableAdminForceNotiObject
	 *
	 * if the Moderator or Admin activates the HideUserToggle Checkbox
	 * the AdminForceNoti Checkbox will be automatically activated too
	 *
	 * if Mod/Admin disable the AdminForceNoti Checkbox
	 * the HideUserToggle Checkbox will be disabled too
	 *
	 * @access	public
	 *
	 */
	public function enableAdminForceNotiObject()
	{
		include_once 'Modules/Forum/classes/class.ilForumNotification.php';

		$frm_noti = new ilForumNotification($_GET['ref_id']);
		if(!$_POST['user_id'])
		{
			ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'), true);
		}
		else
		{
			foreach($_POST['user_id'] as $user_id)
			{
				$frm_noti->setUserId($user_id);
				$is_enabled = $frm_noti->isAdminForceNotification();

				$frm_noti->setUserToggle(0);
				if(!$is_enabled)
				{
					$frm_noti->setAdminForce(1);
					$frm_noti->insertAdminForce();
				}
			}

			// print success message
			ilUtil::sendInfo($this->lng->txt('saved_successfully'));
		}

		return $this->showMembersObject();
	}

	/**
	 *
	 * disableAdminForceNotiObject
	 *
	 * @access	public
	 *
	 */
	public function disableAdminForceNotiObject()
	{
		include_once 'Modules/Forum/classes/class.ilForumNotification.php';

		$frm_noti = new ilForumNotification($_GET['ref_id']);

		if(!$_POST['user_id'])
		{
			ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'), true);
		}
		else
		{
			foreach($_POST['user_id'] as $user_id)
			{
				$frm_noti->setUserId($user_id);
				$is_enabled = $frm_noti->isAdminForceNotification();

				if($is_enabled)
				{
					$frm_noti->deleteAdminForce();
				}
			}

			// print success message
			ilUtil::sendInfo($this->lng->txt('saved_successfully'));
		}

		return $this->showMembersObject();
	}

	/**
	 *
	 * enableHideUserToggleNotiObject
	 *
	 * @access	public
	 *
	 */
	public function enableHideUserToggleNotiObject()
	{
		include_once 'Modules/Forum/classes/class.ilForumNotification.php';

		$frm_noti = new ilForumNotification($_GET['ref_id']);
		if(!$_POST['user_id'])
		{
			ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'), true);
		}
		else
		{
			foreach($_POST['user_id'] as $user_id)
			{
				$frm_noti->setUserId($user_id);
				$is_enabled = $frm_noti->isAdminForceNotification();
				$frm_noti->setUserToggle(1);

				if(!$is_enabled)
				{
					$frm_noti->setAdminForce(1);
					$frm_noti->insertAdminForce();
				}
				else
				{
					$frm_noti->updateUserToggle();
				}
			}

			// print success message
			ilUtil::sendInfo($this->lng->txt('saved_successfully'));
		}

		return $this->showMembersObject();
	}

	/**
	 *
	 * disableHideUserToggleNotiObject
	 *
	 * @access	public
	 *
	 */
	public function disableHideUserToggleNotiObject()
	{
		include_once 'Modules/Forum/classes/class.ilForumNotification.php';

		$frm_noti = new ilForumNotification($_GET['ref_id']);
		if(!$_POST['user_id'])
		{
			ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'), true);
		}
		else
		{
			foreach($_POST['user_id'] as $user_id)
			{
				$frm_noti->setUserId($user_id);
				$is_enabled = $frm_noti->isAdminForceNotification();
				$frm_noti->setUserToggle(0);
				if($is_enabled)
				{
					$frm_noti->updateUserToggle();
				}
				else
				{
					$frm_noti->setAdminForce(1);
					$frm_noti->insertAdminForce();
				}
			}

			// print success message
			ilUtil::sendInfo($this->lng->txt('saved_successfully'));
		}

		return $this->showMembersObject();
	}

	public function markPostUnreadObject()
	{
		global $ilUser;
		
		$this->object->markPostUnread((int)$ilUser->getId(), (int)$_GET['pos_pk']);
		$this->viewThreadObject();
	}

	public function markPostReadObject()
	{
		global $ilUser;

		$this->object->markPostRead($ilUser->getId(), (int) $this->objCurrentTopic->getId(), (int) $this->objCurrentPost->getId());
		$this->viewThreadObject();
	}

	#$forumObj->markPostRead($ilUser->getId(), (int) $this->objCurrentTopic->getId(), (int) $this->objCurrentPost->getId());
} // END class.ilObjForumGUI
?>
