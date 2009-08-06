<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

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
* @author Stefan Meyer <smeyer@databay.de>
* $Id$
*
* @ilCtrl_Calls ilObjForumGUI: ilPermissionGUI, ilForumExportGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjForumGUI: ilColumnGUI, ilPublicUserProfileGUI, ilForumModeratorsGUI
*
* @ingroup ModulesForum
*/
class ilObjForumGUI extends ilObjectGUI
{
	private $objProperties = null;
	
	private $objCurrentTopic = null;	
	private $objCurrentPost = null;	
	private $display_confirm_post_deactivation = 0;
	private $display_confirm_post_activation = 0;
	
	private $is_moderator = false;
	private $action = null;
	
	private $create_form_gui = null;
	private $create_import_gui = null;
	private $create_topic_form_gui = null;
	
	private $reloadTree = false;
	private $hideToolbar = false;
	
	public function ilObjForumGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		global $ilCtrl, $ilUser, $ilAccess;

		// CONTROL OPTIONS
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array('ref_id', 'cmdClass'));

		$this->type = 'frm';
		$this->ilObjectGUI($a_data, $a_id, $a_call_by_reference, false);

		$this->lng->loadLanguageModule('forum');
		
		$properties_obj_id = is_object($this->object) ? $this->object->getId() : 0;

		// forum properties
		$this->objProperties = ilForumProperties::getInstance($properties_obj_id);

		// data of current post
		$this->objCurrentTopic = new ilForumTopic(ilUtil::stripSlashes((int)$_GET['thr_pk']), $ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']));

		// data of current topic/thread
		$this->objCurrentPost = new ilForumPost(ilUtil::stripSlashes((int)$_GET['pos_pk']), $ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']));
	}

	/**
	* Execute Command.
	*/
	function &executeCommand()
	{
		global $ilNavigationHistory, $ilAccess;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$exclude_cmds = array('showExplorer', 'viewThread',
							  'showThreadNotification',
					     	  'cancelPostActivation', 'cancelPostDeactivation',
					     	  'performPostActivation', 'performPostDeactivation', 'performPostAndChildPostsActivation',
					     	  'askForPostActivation', 'askForPostDeactivation',
					     	  'toggleThreadNotification', 'toggleThreadNotificationTab',
					     	  'toggleStickiness', 'cancelPost', 'savePost', 'quotePost', 'getQuotationHTMLAsynch',
							  'doReloadTree'
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
				exit();
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
				$ret = $this->ctrl->forwardCommand($profile_gui);
				break;

			default:
				if($_POST['selected_cmd'] != null)
				{
					$cmd = 'performThreadsAction';
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
	
	public function updateObject()
	{
		global $ilAccess, $ilSetting;

		if (!$ilAccess->checkAccess('write', '', $_GET['ref_id']))
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		$this->object->setTitle(ilUtil::stripSlashes(trim($_POST["title"])));
		$this->object->setDescription(ilUtil::stripSlashes(trim($_POST["desc"])));

		$this->objProperties->setDefaultView(((int) $_POST['default_view']));
		if (!$this->ilias->getSetting('disable_anonymous_fora') || $this->objProperties->isAnonymized())
		{
			//$this->objProperties->setAnonymisation(((int) $_POST['anonymized'] == 1) ? true : false);
			$this->objProperties->setAnonymisation((int) $_POST['anonymized']);		
		}
		if ($ilSetting->get('enable_fora_statistics'))
		{
			//$this->objProperties->setStatisticsStatus(((int) $_POST['statistics_enabled'] == 1) ? true : false);
			$this->objProperties->setStatisticsStatus((int) $_POST['statistics_enabled']);
		}
		//$this->objProperties->setPostActivation(((int) $_POST['post_activation'] == 1) ? true : false);
		$this->objProperties->setPostActivation((int) $_POST['post_activation']);
				
		if (strlen(trim($_POST['title'])))
		{			
			$this->objProperties->update();
			$this->object->update();

			ilUtil::sendInfo($this->lng->txt('msg_obj_modified'), true);
			ilUtil::redirect($this->ctrl->getLinkTarget($this, 'edit'));
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('frm_title_required'));
		}
		
		$this->showForumProperties();		
	
		return true;
	}
	
	public function editObject()
	{
		global $ilAccess, $ilSetting;

		if (!$ilAccess->checkAccess('write', '', $_GET['ref_id']))
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}		

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'update'));		
		$form->setTitle($this->lng->txt('edit_properties'));
		
		$ti_prop = new ilTextInputGUI($this->lng->txt('title'), 'title');
		$ti_prop->setValue($this->object->getTitle());
		$form->addItem($ti_prop);
		
		$tai_prop = new ilTextAreaInputGUI($this->lng->txt('desc'), 'desc');
		$tai_prop->setValue($this->object->getLongDescription());
		$tai_prop->setRows(5);
		$tai_prop->setCols(50);
		$form->addItem($tai_prop);

		$rg_pro = new ilRadioGroupInputGUI($this->lng->txt('frm_default_view'), 'default_view');
		$rg_pro->addOption(new ilRadioOption($this->lng->txt('order_by').' '.$this->lng->txt('answers'), '1'));
		$rg_pro->addOption(new ilRadioOption($this->lng->txt('order_by').' '.$this->lng->txt('date'), '2'));
		$rg_pro->setValue($this->objProperties->getDefaultView());		
		$form->addItem($rg_pro);	

		if (!$ilSetting->get('disable_anonymous_fora') || $this->objProperties->isAnonymized())
		{	
			$cb_prop = new ilCheckboxInputGUI($this->lng->txt('frm_anonymous_posting'),	'anonymized');
			$cb_prop->setValue('1');
			$cb_prop->setInfo($this->lng->txt('frm_anonymous_posting_desc'));
			$cb_prop->setChecked($this->objProperties->isAnonymized() ? 1 : 0);
			$form->addItem($cb_prop);
		}

		if ($ilSetting->get('enable_fora_statistics'))
		{
			$cb_prop = new ilCheckboxInputGUI($this->lng->txt('frm_statistics_enabled'), 'statistics_enabled');
			$cb_prop->setValue('1');
			$cb_prop->setInfo($this->lng->txt('frm_statistics_enabled_desc'));
			$cb_prop->setChecked($this->objProperties->isStatisticEnabled() ? 1 : 0);
			$form->addItem($cb_prop);
		}	
		
		$cb_prop = new ilCheckboxInputGUI($this->lng->txt('activate_new_posts'), 'post_activation');
		$cb_prop->setValue('1');
		$cb_prop->setInfo($this->lng->txt('post_activation_desc'));
		$cb_prop->setChecked($this->objProperties->isPostActivationEnabled() ? 1 : 0);
		$form->addItem($cb_prop);		
		
		$form->addCommandButton('update', $this->lng->txt('save'));
		
		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML());
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
		global $ilUser, $ilDB, $ilAccess, $lng, $ilToolbar;		

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

		// button: enable/disable forum notification
		if($ilUser->getId() != ANONYMOUS_USER_ID &&
		   $this->ilias->getSetting('forum_notification') != 0 &&
		   !$this->hideToolbar())
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
				
				$tbl = new ilTable2GUI($this);
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
					$result[$counter]['check'] = ilUtil::formCheckbox(
						(isset($_POST['thread_ids']) && in_array($thread->getId(), $_POST['thread_ids']) ? true : false), 'thread_ids[]',  $thread->getId()
					);
				
					if ($thrNum > $pageHits && $z >= ($Start + $pageHits))
					{
						break;
					}
	
					if (($thrNum > $pageHits && $z >= $Start) || $thrNum <= $pageHits)
					{
						if ($this->objProperties->isAnonymized())
						{
							$usr_data = array(
								'usr_id' => 0,
								'login' => $thread->getUserAlias(),
								'firstname' => '',
								'lastname' => '',
								'public_profile' => 'n'
							);
						}						
						else
						{
							// get user data, used for imported users
							$usr_data = $frm->getUserData($thread->getUserId(), $thread->getImportName());
						}

						$thread->setCreateDate($frm->convertDate($thread->getCreateDate()));
		
						$this->ctrl->setParameter($this, 'thr_pk', $thread->getId());
						
						if ($thread->isSticky())
						{
							$result[$counter]['th_title'] .= '<span class="light">['.$this->lng->txt('sticky').']</span> ';							
						}
						
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
										$this->ctrl->getLinkTarget($this, 'showThreadFrameset').
										"\">".$thread->getSubject()."</a></div>".$result[$counter]['th_title'];								
						}
						// get author data
						if ($this->objProperties->isAnonymized())
						{
							if ($usr_data['login'] != '')
							{
								$result[$counter]['author'] = $usr_data['login'];									
							}
							else
							{ 
								$result[$counter]['author'] = $this->lng->txt('forums_anonymous');									
							}							
						}
						else
						{
							if ($thread->getUserId() && $usr_data['usr_id'] != 0)
							{
								$this->ctrl->setParameter($this, 'backurl', urlencode('repository.php?ref_id='.$_GET['ref_id'].'&offset='.$Start));
								$this->ctrl->setParameter($this, 'user', $usr_data['usr_id']);
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
								$result[$counter]['author'] = $usr_data['login'];										
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
							if ($num_new > 0)
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
								if ($this->objProperties->isAnonymized())
								{
									$last_usr_data = array(
										'usr_id' => 0,
										'login' => $objLastPost->getUserAlias(),
										'firstname' => '',
										'lastname' => '',
										'public_profile' => 'n'
									);
								}
								else
								{
									$last_usr_data = $frm->getUserData($objLastPost->getUserId(), $objLastPost->getImportName());
								}					
										
								$this->ctrl->setParameter($this, 'thr_pk', $objLastPost->getThreadId());
								
								$result[$counter]['lp_date'] = '<div style="white-space:nowrap">'.
										$frm->convertDate($objLastPost->getCreateDate())."</div>".
										'<div style="white-space:nowrap">'.$this->lng->txt('from').' '."<a href=\"".
										$this->ctrl->getLinkTarget($this, 'showThreadFrameset').'#'.$objLastPost->getId().
										"\">".$usr_data['login']."</a></div>";
												
								
								if($this->objProperties->isAnonymized())
								{
									if($last_usr_data['login'] != '')
									{
										$result[$counter]['lp_title'] = $last_usr_data['login'];											
									}
									else
									{
										$result[$counter]['lp_title'] = $this->lng->txt('forums_anonymous');																					
									}
								}								
							}
						}										

					} 
					$counter++;
				}
				
				$tbl->disable('sort');
				$tbl->setSelectAllCheckbox('thread_ids');
				$tbl->setPrefix('frm_threads');
				$tbl->setData($result);
				
				$tbl->addMultiCommand('please_choose', $this->lng->txt('please_choose'));
				$tbl->addMultiCommand('html', $this->lng->txt('export_html'));				
				if($this->ilias->getSetting('forum_notification') == 1)
				{
					$tbl->addMultiCommand('disable_notifications', $this->lng->txt('forums_disable_notification'));
					$tbl->addMultiCommand('enable_notifications', $this->lng->txt('forums_enable_notification'));
				}
				if($ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
				{
					$tbl->addMultiCommand('makesticky', $this->lng->txt('make_topics_sticky'));
					$tbl->addMultiCommand('unmakesticky', $this->lng->txt('make_topics_non_sticky'));
					$tbl->addMultiCommand('close', $this->lng->txt('close_topics'));
					$tbl->addMultiCommand('reopen', $this->lng->txt('reopen_topics'));
					$tbl->addMultiCommand('move', $this->lng->txt('move'));
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
		if($this->ilias->getSetting('disable_anonymous_fora', true))
			$anonymize_gui->setDisabled(true);
		$this->create_form_gui->addItem($anonymize_gui);		
		
		// statistics enabled or not
		$statistics_gui = new ilCheckboxInputGUI($this->lng->txt('frm_statistics_enabled'), 'statistics_enabled');
		$statistics_gui->setInfo($this->lng->txt('frm_statistics_enabled_desc'));
		$statistics_gui->setValue(1);
		if(!$this->ilias->getSetting('enable_fora_statistics', false))
			$statistics_gui->setDisabled(true);
		$this->create_form_gui->addItem($statistics_gui);
		
		$this->create_form_gui->addCommandButton('save', $this->lng->txt('save'));
		$this->create_form_gui->addCommandButton('cancel', $this->lng->txt('cancel'));
	}
	
	/**
	* @access private
	*/
	private function setForumCreateDefaultValues()
	{
		$this->create_form_gui->setValuesByArray(array(
			'title' => '',
			'desc' => '',
			'sort' => 1,
			'anonymized' => false,
			'statistics_enabled' => false
		));
	}
	
	/**
	* @access private
	*/
	private function initForumImportForm($object_type)
	{
		$this->import_form_gui = new ilPropertyFormGUI();
		
		$this->import_form_gui->setTitle($this->lng->txt('forum_import').' (ILIAS 2)');
		$this->import_form_gui->setTitleIcon(ilUtil::getImagePath('icon_frm.gif'));		
		
		// form action
		$this->ctrl->setParameter($this, 'new_type', $object_type);
		$this->import_form_gui->setFormAction($this->ctrl->getFormAction($this, 'performImport'));		
		
		// file
		$in_file = new ilFileInputGUI($this->lng->txt('forum_import_file'), 'importFile');
		$in_file->setRequired(true);
		$this->import_form_gui->addItem($in_file);
		
		$this->import_form_gui->addCommandButton('performImport', $this->lng->txt('import'));
		$this->import_form_gui->addCommandButton('cancel', $this->lng->txt('cancel'));
	}
	
	/**
	* creation form
	*/
	function createObject($subbmitted_form = '')
	{
		global $rbacsystem;		

		$new_type = $_POST['new_type'] ? $_POST['new_type'] : $_GET['new_type'];
		if(!$rbacsystem->checkAccess('create', $_GET['ref_id'], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.frm_create.html', 'Modules/Forum');
		
		// create form
		if($this->create_form_gui === null)
			$this->initForumCreateForm($new_type);
		if($subbmitted_form == 'create')
			$this->create_form_gui->setValuesByPost();
		else
			$this->setForumCreateDefaultValues();		
		$this->tpl->setVariable('CREATE_FORM', $this->create_form_gui->getHTML());

		// show ilias 2 forum import for administrators only
		include_once 'Services/MainMenu/classes/class.ilMainMenuGUI.php';
		if(ilMainMenuGUI::_checkAdministrationPermission())
		{
			if($this->import_form_gui === null)
				$this->initForumImportForm($new_type);
			$this->tpl->setVariable('IMPORT_FORM', $this->import_form_gui->getHTML());			
		}
		
		$this->fillCloneTemplate('DUPLICATE','frm');
		return true;
	}

	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	function cancelObject($in_rep = false)
	{
		ilUtil::sendInfo($this->lng->txt('msg_cancel'), true);

		ilUtil::redirect('repository.php?cmd=frameset&ref_id='.$_GET['ref_id']);

	}

	function performImportObject()
	{
		$new_type = $_POST['new_type'] ? $_POST['new_type'] : $_GET['new_type'];
		$this->initForumImportForm($new_type);
		if($this->import_form_gui->checkInput())
		{
			$file = $this->import_form_gui->getInput('importFile');
			
			$error = false;			
			
			$this->__initFileObject();

			if(!$this->file_obj->storeUploadedFile($file))	// STEP 1 save file in ...import/mail
			{
				$this->import_form_gui->getItemByPostVar('importFile')
					 ->setAlert($this->lng->txt('import_file_not_valid'));
				$error = true; 
				$this->file_obj->unlinkLast();
			}
			else if(!$this->file_obj->unzip()) // STEP 2 unzip uploaded file
			{
				$this->import_form_gui->getItemByPostVar('importFile')
					 ->setAlert($this->lng->txt('cannot_unzip_file'));
				
				$error = true;
				$this->file_obj->unlinkLast();
			}
			else if(!$this->file_obj->findXMLFile())// STEP 3 getXMLFile
			{
				$this->import_form_gui->getItemByPostVar('importFile')
					 ->setAlert($this->lng->txt('cannot_find_xml'));
				
				$error = true;
				$this->file_obj->unlinkLast();
			}
			else if(!$this->__initParserObject($this->file_obj->getXMLFile()) ||
			        !$this->parser_obj->startParsing()) // STEP 5 start parsing
			{
				$this->import_form_gui->getItemByPostVar('importFile')
					 ->setAlert($this->lng->txt('import_parse_error'));
				
				$error = true;
				$this->message = $this->lng->txt("import_parse_error").":<br/>";
			}
			
			// FINALLY CHECK ERROR
			if(!$error)
			{
				ilUtil::sendSuccess($this->lng->txt('import_forum_finished'), true);
				$ref_id = $this->parser_obj->getRefId();
				if($ref_id > 0)
				{
					$this->ctrl->setParameter($this, 'ref_id', $ref_id);
					ilUtil::redirect($this->getReturnLocation('save',
						$this->ctrl->getLinkTarget($this, 'showThreads')));
				}
				else
				{
					ilUtil::redirect('repository.php?cmd=frameset&ref_id='.$_GET['ref_id']);
				}
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
				$this->createObject('import');
			}
		}
		else
		{
			return $this->createObject('import');
		}	
	}


	/**
	* save object
	* @access	public
	*/
	function saveObject($a_prevent_redirect = false)
	{
		global $rbacadmin, $rbacsystem;

		$new_type = $_POST['new_type'] ? $_POST['new_type'] : $_GET['new_type'];		
		if(!$rbacsystem->checkAccess('create', $_GET['ref_id'], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}		
		
		$this->initForumCreateForm($new_type);
		if($this->create_form_gui->checkInput())
		{			
			$_POST['Fobject']['title'] = $this->create_form_gui->getInput('title');
			$_POST['Fobject']['desc'] =  $this->create_form_gui->getInput('desc');
			
			// create and insert forum in objecttree
			$forumObj = parent::saveObject();
			
			// save settings
			$this->objProperties->setObjId($forumObj->getId());
			$this->objProperties->setDefaultView(((int)$this->create_form_gui->getInput('sort')));
			if(!$this->ilias->getSetting('disable_anonymous_fora', true))
			{
				$this->objProperties->setAnonymisation((int)$this->create_form_gui->getInput('anonymized'));
			}
			else
			{
				$this->objProperties->setAnonymisation(0);
			}			
			if($this->ilias->getSetting('enable_fora_statistics', false))
			{
				$this->objProperties->setStatisticsStatus((int)$this->create_form_gui->getInput('statistics_enabled'));	
			}	
			else
			{
				$this->objProperties->setStatisticsStatus(0);				
			}		
			$this->objProperties->insert();		
				
			$forumObj->createSettings();
	
			// setup rolefolder & default local roles (moderator)
			$roles = $forumObj->initDefaultRoles();
	
			// ...finally assign moderator role to creator of forum object
			$rbacadmin->assignUser($roles[0], $forumObj->getOwner(), 'n');
			
			// insert new forum as new topic into frm_data
			$forumObj->saveData($roles);        
			
			// always send a message
			ilUtil::sendSuccess($this->lng->txt('frm_added'), true);
						
			$this->ctrl->setParameter($this, 'ref_id', $forumObj->getRefId());	
			if(!$a_prevent_redirect)
			{
				ilUtil::redirect($this->ctrl->getLinkTarget($this, 'createThread'));
			}
		}
		else
		{
			return $this->createObject('create');
		}		
	}

	function getTabs(&$tabs_gui)
	{
		global $ilAccess, $ilUser;

		$this->ctrl->setParameter($this, 'ref_id', $this->ref_id);
		
		#if ($ilAccess->checkAccess('write', '', $this->ref_id))
		#{
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
						'ancelMoveThreads',
						'performThreadsAction',
						'searchForums',
						'createThread',
						'addThread',
						'showUser'
						);
		$tabs_gui->addTarget('forums_threads', ilRepositoryExplorer::buildLinkTarget($this->ref_id, 'frm'),	$active, '');
		#}
		
		if($ilAccess->checkAccess('edit_permission', '', $this->ref_id))
		{
			$tabs_gui->addTarget('frm_moderators', $this->ctrl->getLinkTargetByClass('ilForumModeratorsGUI', 'showModerators'), 'showModerators', get_class($this), '', $force_active);			
		}
		
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
			$tabs_gui->addTarget('edit_properties', $this->ctrl->getLinkTarget($this, 'edit'), 'edit', get_class($this), '', $force_active);
		}

		if ($this->ilias->getSetting('enable_fora_statistics', true) &&
			($this->objProperties->isStatisticEnabled() || $ilAccess->checkAccess('write', '', $this->ref_id))) 
		{
			$tabs_gui->addTarget('frm_statistics', $this->ctrl->getLinkTarget($this, 'showStatistics'), 'showStatistics', get_class($this), '', false);				
		}

		if ($ilAccess->checkAccess('edit_permission', '', $this->ref_id))
		{
			$tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');							
		}
		
		return true;
	}
	
	/**
	 * called from GUI
	 */
	function showStatisticsObject() 
	{
		global $ilUser, $ilAccess, $ilDB;
		
		/// if globally deactivated, skip!!! intrusion detected
		if (!$this->ilias->getSetting('enable_fora_statistics', true))
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

		$tbl = new ilTableGUI();
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_statistics_view.html', 'Modules/Forum');		
    	$this->tpl->addBlockfile('TBL_CONTENT', 'tbl_content', 'tpl.table.html');		
		
		// if write access and statistics disabled -> ok, for forum admin 		
		if ($ilAccess->checkAccess('write', '', $_GET['ref_id']) && 
			!$this->objProperties->isStatisticEnabled())
		{
			ilUtil::sendInfo($this->lng->txt('frm_statistics_disabled_for_participants'));
		}
		
		// get sort variables from get vars
		$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order']: 'desc';
		$sort_by  = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'ranking';

		if ($sort_by == 'title') $sort_by = 'ranking';
		
		$this->object->Forum->setForumId($this->object->getId());		
		$data = $this->object->Forum->getUserStatistic($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']));		

		// title & header columns
		$tbl->setTitle($this->lng->txt('statistic'), 'icon_usr_b.gif', $this->lng->txt('obj_'.$this->object->getType()));
				
		$header_names = array ($this->lng->txt('frm_statistics_ranking'), $this->lng->txt('login'), $this->lng->txt('lastname'), $this->lng->txt('firstname'));
			 
		$tbl->setHeaderNames($header_names);

		$header_params = array('ref_id' => $this->ref_id, 'cmd' => 'showStatistics');
		$header_fields = array('ranking', 'login', 'lastname', 'firstname');		

		$tbl->setHeaderVars($header_fields,$header_params);
		$tbl->setColumnWidth(array('', '25%', '25%', '25%'));

		// table properties
    	$tbl->enable('hits');
    	$tbl->enable('sort');
		$tbl->setOrderColumn($sort_by);
		$tbl->setOrderDirection($sort_order);
		$tbl->setLimit(0);
		$tbl->setOffset(0);
		$tbl->setData($data);

		$tbl->render();
				
		$this->tpl->parseCurrentBlock();			
	}

	private function __initFileObject()
	{
		include_once './Modules/Forum/classes/class.ilFileDataImportForum.php';

		$this->file_obj =& new ilFileDataImportForum();

		return true;
	}

	private function __initParserObject($a_xml_file)
	{
		include_once './Modules/Forum/classes/class.ilForumImportParser.php';

		$this->parser_obj =& new ilForumImportParser($a_xml_file, $this->ref_id);

		return true;
	}
	
	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	function _goto($a_target, $a_thread = 0, $a_posting = 0)
	{
		global $ilAccess, $ilErr, $lng;

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
				$_GET['cmd'] = 'showThreadFrameset';
				//include_once('forums_frameset.php');
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
			$_GET['cmd'] = 'frameset';
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
		global $lng, $ilDB, $ilAccess, $ilCtrl;
		
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
	* Output forum frameset.
	*/
	function showThreadFramesetObject()
	{
		global $ilUser, $lng, $ilDB, $ilAccess, $ilNavigationHistory, $ilCtrl;
		
		require_once './Modules/Forum/classes/class.ilForum.php';
		require_once './Modules/Forum/classes/class.ilObjForum.php';
		
		$lng->loadLanguageModule('forum');
		
		$forumObj = new ilObjForum($_GET['ref_id']);
		$this->objProperties->setObjId($forumObj->getId());

		if ($_GET['mark_read'])
		{
			$forumObj->markThreadRead($ilUser->getId(),(int) $this->objCurrentTopic->getId());
			ilUtil::sendInfo($lng->txt('forums_thread_marked'), true);
		}		
		
		// delete post and its sub-posts
		

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
		
		
		$session_name = 'viewmode_'.$forumObj->getId();
		
		if (isset($_GET['viewmode']))
		{
			$_SESSION[$session_name] = $_GET['viewmode'];
		}
		if (!$_SESSION[$session_name])
		{
			$_SESSION[$session_name] = $this->objProperties->getDefaultView() == 1 ? 'tree' : 'flat';
		}
		
		if ($_SESSION[$session_name] == 'tree')
		{
			include_once('Services/Frameset/classes/class.ilFramesetGUI.php');
			$fs_gui = new ilFramesetGUI();
			$fs_gui->setMainFrameName('content');
			$fs_gui->setSideFrameName('tree');
			$fs_gui->setFramesetTitle($forumObj->getTitle());
		
			if (isset($_GET['target']))
			{
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
				$fs_gui->setSideFrameSource($this->ctrl->getLinkTarget($this, 'showExplorer'));
				$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
				$fs_gui->setMainFrameSource($this->ctrl->getLinkTarget($this, 'viewThread').'#'.$this->objCurrentPost->getId());
			}
			else
			{
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
				$fs_gui->setSideFrameSource($this->ctrl->getLinkTarget($this, 'showExplorer'));
				$fs_gui->setMainFrameSource($this->ctrl->getLinkTarget($this, 'viewThread'));
			}
			$fs_gui->show();
			exit();
		}
		else
		{			
			if (isset($_GET['target']))
			{
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
				$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
				$this->ctrl->redirect($this, 'viewThread', $this->objCurrentPost->getId());
			}
			else
			{
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
				$this->ctrl->redirect($this, 'viewThread');
			}
		}
	}

	/**
	* Show Forum Explorer.
	*/
	function showExplorerObject()
	{
		global $tpl, $lng;
		
		require_once './Modules/Forum/classes/class.ilForumExplorer.php';

		$tpl->addBlockFile('CONTENT', 'content', 'tpl.explorer.html');
		$tpl->setVariable('IMG_SPACE', ilUtil::getImagePath('spacer.gif', false));
	
		$exp = new ilForumExplorer("./repository.php?cmd=viewThread&cmdClass=ilobjforumgui&thr_pk=".$this->objCurrentTopic->getId()."&ref_id=".$_GET['ref_id'], $this->objCurrentTopic, (int) $_GET['ref_id']);
		$exp->setTargetGet('pos_pk');
		
		if ($_GET['fexpand'] == '')
		{
			$expanded = $this->objCurrentTopic->getFirstPostNode()->getId();
		}
		else
		{
			$expanded = $_GET['fexpand'];
		}
			
		$exp->setExpand($expanded);
		
		//build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();
		
		$tpl->setCurrentBlock('content');
		//$tpl->setVariable('TXT_EXPLORER_HEADER', $lng->txt('forums_posts'));
		$tpl->setVariable('EXP_REFRESH', $lng->txt('refresh'));
		$tpl->setVariable('EXPLORER', $output);
		$this->ctrl->setParameter($this, 'fexpand', $_GET['fexpand']);
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
		$tpl->setVariable('ACTION', $this->ctrl->getLinkTarget($this, 'showExplorer'));
		$tpl->parseCurrentBlock();
		
		$tpl->show(false);
		exit();
	}	
	
	function prepareThreadScreen($a_forum_obj)
	{
		global $tpl, $lng, $ilTabs, $ilias, $ilUser;
		
		$session_name = 'viewmode_'.$a_forum_obj->getId();
		$t_frame = ilFrameTargetInfo::_getFrame('MainContent');

		$tpl->getStandardTemplate();
		ilUtil::sendInfo();	
		ilUtil::infoPanel();
		
		$tpl->setTitleIcon(ilUtil::getImagePath('icon_frm_b.gif'));

		$ilTabs->setBackTarget($lng->txt('all_topics'), 'repository.php?ref_id='.$_GET['ref_id'], $t_frame);
	
		// by answer view
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
		$this->ctrl->setParameter($this, 'viewmode', 'tree');
		$ilTabs->addTarget('order_by_answers', $this->ctrl->getLinkTarget($this, 'showThreadFrameset'), '', '', $t_frame);
	
		// by date view
		$this->ctrl->setParameter($this, 'viewmode', 'flat');
		$ilTabs->addTarget('order_by_date',	$this->ctrl->getLinkTarget($this, 'showThreadFrameset'), '', '', $t_frame);
		$this->ctrl->clearParameters($this);
	
		if (!isset($_SESSION[$session_name]) or $_SESSION[$session_name] == 'flat')
		{
			$ilTabs->setTabActive('order_by_date');
		}
		else
		{
			$ilTabs->setTabActive('order_by_answers');
		}
	
		$frm =& $a_forum_obj->Forum;
		$frm->setForumId($a_forum_obj->getId());
		
		/*
		if ($ilias->getSetting('forum_notification') != 0 &&
			!$frm->isForumNotificationEnabled($ilUser->getId()))
		{
			$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
			$ilTabs->addTarget('forums_notification', $this->ctrl->getLinkTarget($this, 'showThreadNotification'), '','');
			$this->ctrl->clearParameters($this);
		}
		*/
	}
	
	/*
	public function performPostDeactivationObject()
	{
		global $ilAccess;
		
		if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
		{
			$this->objCurrentPost->deactivatePostAndChildPosts();
			ilUtil::sendInfo($this->lng->txt('forums_post_and_children_were_deactivated'), true);
		}		
		
		$this->viewThreadObject();
		
		return true;
	}
	*/
	
	/*
	public function cancelPostDeactivationObject()		
	{
		$this->viewThreadObject();
		
		return true;
	}
	*/
	
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
	
	/*
	public function askForPostDeactivationObject()
	{
		global $ilAccess;		

		if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
		{		
			$this->setDisplayConfirmPostDeactivation(true);			
		}
		
		$this->viewThreadObject();
		
		return true;
	}	
	*/
	public function setDisplayConfirmPostActivation($status = 0)
	{
		$this->display_confirm_post_activation = $status;
	}	
	/*
	public function setDisplayConfirmPostDeactivation($status = false)
	{
		$this->display_confirm_post_deactivation = $status;
	}
	*/
	public function displayConfirmPostActivation()
	{
		return $this->display_confirm_post_activation;
	}
	/*	
	public function displayConfirmPostDeactivation()
	{
		return $this->display_confirm_post_deactivation;
	}
	*/
	
	/**
	 * Toggle thread notification for current user in notification tab view
	 *
	/*
	
	/*
	public function toggleThreadNotificationTabObject()
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
		
		$this->showThreadNotificationObject();
		
		return true;
	}
	*/
	
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
		$form_tpl->setVariable('FORM_ACTION', $this->ctrl->getLinkTarget($this, 'showThreadFrameset'));
		$this->ctrl->clearParameters($this);
		$t_frame = ilFrameTargetInfo::_getFrame('MainContent');
		$form_tpl->setVariable('FORM_TARGET', $t_frame);
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
		if($this->objProperties->isAnonymized() && $_GET['action'] == 'showreply')
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
		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply')
		{
			$oPostGUI->addButton('ilFrmQuoteAjaxCall');
		}
		$oPostGUI->removePlugin('advlink');
		$oPostGUI->setRTERootBlockElement('');
		$oPostGUI->usePurifier(true);
		$oPostGUI->disableButtons(array(
			'removeformat',
			'charmap',
			'undo',
			'redo',
			'justifyleft',
			'justifycenter',
			'justifyright',
			'justifyfull',
			'anchor',
			'code',
			'fullscreen',
			'cut',
			'copy',
			'paste',
			'pastetext',
			'pasteword',
			'formatselect'
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
		$oFileUploadGUI = new ilFileInputGUI($this->lng->txt('forums_attachments_add'), 'userfile');
		
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
		global $ilUser, $ilAccess, $ilDB, $lng;
		
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
				$file = $oReplyEditForm->getInput('userfile');
				if(strlen($file['tmp_name']))
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
				$this->ctrl->redirect($this, 'doReloadTree', $newPost);								
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
					
					$file = $oReplyEditForm->getInput('userfile');
					if(strlen($file['tmp_name']))
					{
						$oFDForum->storeUploadedFile($file);
					}
					
					$file2delete = $oReplyEditForm->getInput('del_file');
					if(is_array($file2delete) && count($file2delete))
					{
						$oFDForum->unlinkFilesByMD5Filenames($file2delete);
					}				
				}
				ilUtil::sendSuccess($lng->txt('forums_post_modified'), true);
				$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());				
				$this->ctrl->redirect($this, 'doReloadTree', $this->objCurrentPost->getId());
			}	
		}
		else
		{
			$_GET['action'] = substr($_GET['action'], 6);
		}		
		
		return $this->viewThreadObject();
	}
	
	private function reloadTree($a_flag = null)
	{
		if(null === $a_flag)
		{
			return $this->reloadTree;
		}
		
		$this->reloadTree = $a_flag;
		return $this;
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
	
	public function doReloadTreeObject()
	{
		$this->reloadTree(true);

		$this->viewThreadObject();	
	}
	
	/**
	 * View single thread
	 */
	public function viewThreadObject()
	{
		global $tpl, $lng, $ilUser, $ilAccess, $ilTabs, $rbacsystem,
			   $rbacreview, $ilDB, $ilNavigationHistory, $ilCtrl, $frm, $ilToolbar;
			
		if(!$ilAccess->checkAccess('read,visible', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
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
			$ilNavigationHistory->addItem($this->object->getRefId(), $ilCtrl->getLinkTarget($this, 'showThreadFrameset'), 'frm');
		}
		
		// init objects
		$oForumObjects = $this->getForumObjects();		
		$forumObj = $oForumObjects['forumObj'];
		$frm = $oForumObjects['frm'];
		$file_obj = $oForumObjects['file_obj'];
		
		// save last access
		$forumObj->updateLastAccess($ilUser->getId(), (int) $this->objCurrentTopic->getId());
		
		// mark post read if explorer link was clicked
		if ($this->objCurrentTopic->getId() && $this->objCurrentPost->getId())
		{
			$forumObj->markPostRead($ilUser->getId(), (int) $this->objCurrentTopic->getId(), (int) $this->objCurrentPost->getId());
		}		
		
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
		if ($_SESSION[$session_name] == 'flat')
		{
			$new_order = 'answers';
			$orderField = 'frm_posts_tree.fpt_date';
		}
		else
		{
			if($this->reloadTree())
			{
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
				$tpl->setVariable('JAVASCRIPT',	$this->ctrl->getLinkTarget($this, 'showExplorer'));
			}
			
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
			
			// mark read
			if($ilUser->getId() != ANONYMOUS_USER_ID &&
			   $forumObj->getCountUnread($ilUser->getId(), (int) $this->objCurrentTopic->getId()))
			{
				$this->ctrl->setParameter($this, 'mark_read', '1');
				$this->ctrl->setParameter($this, 'thr_pk',  $this->objCurrentTopic->getId());
				$ilToolbar->addButton(
					$this->lng->txt('forums_mark_read'),
					$this->ctrl->getLinkTarget($this, 'showThreadFrameset'),
					ilFrameTargetInfo::_getFrame('MainContent'),
					ilAccessKeyGUI::getAttribute(ilAccessKey::MARK_ALL_READ)
				);
				$this->ctrl->clearParameters($this);
			}

			// print thread
			$this->ctrl->setParameterByClass('ilforumexportgui', 'print_thread', $this->objCurrentTopic->getId());
			$this->ctrl->setParameterByClass('ilforumexportgui', 'thr_top_fk', $this->objCurrentTopic->getForumId());
			$ilToolbar->addButton(
				$this->lng->txt('forums_print_thread'),
				$this->ctrl->getLinkTargetByClass('ilforumexportgui', 'printThread'),
				ilFrameTargetInfo::_getFrame('MainContent')
			);
			$this->ctrl->clearParametersByClass('ilforumexportgui');
			
			// enable/disable notification
			if($ilUser->getId() != ANONYMOUS_USER_ID &&
			   $this->ilias->getSetting('forum_notification') != 0)
			{
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
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
				$this->ctrl->clearParameters($this);
			}
		
			// ********************************************************************************	
			
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
													$frm->prepareText($node->getMessage(), 1, $node->getLoginName())."\n".$oEditReplyForm->getInput('message'),	1
												)
											);
										}
										else
										{
											$oEditReplyForm->setValuesByArray(array(
												'alias' => '',
												'subject' => $this->objCurrentPost->getSubject(),
												'message' => '',
												'notify' => 0,
												'userfile' => '',
												'del_file' => array()
											));
										}
										
										$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
										$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());										
										$jsTpl = new ilTemplate('ilFrmPostAjaxHandler.js', true, true, 'Modules/Forum');
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
							} // else if ($this->displayConfirmPostActivation())
							/*else if ($this->displayConfirmPostDeactivation())
							{
								if ($ilAccess->checkAccess('moderate_frm', '', (int) $_GET['ref_id']))
								{
									// confirmation: deactivate
									$tpl->setVariable('FORM', $this->getDeactivationFormHTML()));								
								}
							} // else if ($this->displayConfirmPostDeactivation())
							*/
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
								/*if ($node->isActivated())
								{								
									$tpl->setVariable('ACTIVATE_DEACTIVATE_LINK', $this->ctrl->getLinkTarget($this, 'askForPostDeactivation', $node->getId()));
									$tpl->setVariable('ACTIVATE_DEACTIVATE_BUTTON', $lng->txt('deactivate_post'));
								}
								else
								*/
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
								$tpl->setVariable('COMMANDS_COMMAND', $this->ctrl->getLinkTarget($this, 'viewThread', $node->getId()));
								$tpl->setVariable('COMMANDS_TXT', $lng->txt('is_read'));
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
					$tpl->setVariable('PERMA_LINK', ILIAS_HTTP_PATH."/goto.php?target="."frm"."_".$this->object->getRefId()."_".$node->getThreadId()."_".$node->getId()."&client_id=".CLIENT_ID);
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
					if (ilObject::_exists($node->getUserId()))
					{
						$author = $frm->getUser($node->getUserId());
					}
					else
					{
						$node->setUserId(0);
					}
		
					if ($this->objProperties->isAnonymized())
					{
						$usr_data = array(
							'usr_id' => 0,
							'login' => $node->getUserAlias(),
							'public_profile' => 'n'
						);
					}					
					else
					{
						// GET USER DATA, USED FOR IMPORTED USERS											
						$usr_data = $frm->getUserData($node->getUserId(), $node->getImportName());
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
							$node->getChangeDate().' - '.strtolower($lng->txt('by')).' '.$edited_author);

					} // if ($node->getUpdateUserId() > 0)					

					// if post is not activated display message for the owner
					if(!$node->isActivated() && $node->isOwner($ilUser->getId()))
					{
						$tpl->setVariable('POST_NOT_ACTIVATED_YET', $this->lng->txt('frm_post_not_activated_yet'));
					}
			
					if($this->objProperties->isAnonymized())
					{
						if ($usr_data['login'] != '') $tpl->setVariable('AUTHOR', $usr_data['login']);
						else $tpl->setVariable('AUTHOR', $lng->txt('forums_anonymous'));
					}
					else
					{
						if($node->getUserId())
						{
							$user_obj = new ilObjUser($usr_data['usr_id']);
							// user image
							$webspace_dir = ilUtil::getWebspaceDir();
							$image_dir = $webspace_dir.'/usr_images';
							$xthumb_file = $image_dir.'/usr_'.$user_obj->getID().'_xsmall.jpg';
							if ($user_obj->getPref('public_upload') == 'y' &&
								$user_obj->getPref('public_profile') == 'y' &&
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
							$tpl->setVariable('AUTHOR', $usr_data['login']);
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
						if ($forumObj->isNew($ilUser->getId(), $node->getThreadId(), $node->getId()))
						{
							$tpl->setVariable('SUBJECT', '<i><b>'.$node->getSubject().'</b></i>');
							$tpl->setVariable('TXT_MARK_ICON', $this->lng->txt('new'));
							$tpl->setVariable('IMG_MARK_ICON', ilUtil::getImagePath('icon_new.gif'));
						}
						else
						{
							
							$tpl->setVariable('SUBJECT', '<b>'.$node->getSubject().'</b>');
							$tpl->setVariable('TXT_MARK_ICON', $this->lng->txt('unread'));
							$tpl->setVariable('IMG_MARK_ICON', ilUtil::getImagePath('icon_unread.gif'));
						}
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
		
		return true;
	}
	
	/**
	* Show user profile.
	*/
	function showUserObject()
	{
		global $lng, $tpl, $ilAccess, $ilDB;

		if (!$ilAccess->checkAccess('read', '', $_GET['ref_id']))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		require_once './Modules/Forum/classes/class.ilForum.php';
		
		$lng->loadLanguageModule('forum');
		
		$ref_obj =& ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
		if ($ref_obj->getType() == 'frm')
		{
			$forumObj = new ilObjForum($_GET['ref_id']);
			$frm =& $forumObj->Forum;
			$frm->setForumId($forumObj->getId());
			$frm->setForumRefId($forumObj->getRefId());
		}
		else
		{
			$frm =& new ilForum();
		}
		
		$tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_user_view.html',	'Modules/Forum');		
		
		$_GET['obj_id'] = $_GET['user'];
		
		// count articles of user
		if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
		{
			$numPosts = $frm->countUserArticles(addslashes($_GET['user']));
		}
		else
		{
			$numPosts = $frm->countActiveUserArticles(addslashes($_GET['user']));	
		}
		$add = array($lng->txt('forums_posts') => $numPosts);
		
		//$user_gui = new ilObjUserGUI('', $_GET['user'], false, false);
		//$user_gui->insertPublicProfile('USR_PROFILE', 'usr_profile', $add);
		include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
		$profile_gui = new ilPublicUserProfileGUI($_GET['user']);
		$profile_gui->setAdditional($add);
		$tpl->setVariable("USR_PROFILE", $profile_gui->getHTML());
		
		if ($_GET['backurl'])
		{
			global $ilToolbar;
			$ilToolbar->addButton($this->lng->txt('back'), urldecode($_GET['backurl']));
		}
				
		$tpl->setVariable('TPLPATH', $tpl->vars['TPLPATH']);
		
		return true;
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
		global $ilAccess, $lng, $ilDB, $tree, $ilObjDataCache, $ilToolbar;
		
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
		
		// form action
		$this->create_topic_form_gui->setFormAction($this->ctrl->getFormAction($this, 'addThread'));
		
		if($this->objProperties->isAnonymized())
		{			
			$alias_gui = new ilTextInputGUI($this->lng->txt('forums_your_name'), 'alias');
			$alias_gui->setInfo($this->lng->txt('forums_use_alias'));
			$alias_gui->setMaxLength(255);
			$alias_gui->setSize(50);
			$this->create_topic_form_gui->addItem($alias_gui);
		}
		else
		{
			$alias_gui = new ilNonEditableValueGUI($this->lng->txt('forums_your_name', 'alias'));
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
		$post_gui->removePlugin('advlink');
		$post_gui->usePurifier(true);	
		$post_gui->setRTERootBlockElement('');	
		$post_gui->setRTESupport($ilUser->getId(), 'frm~', 'frm_post', 'tpl.tinymce_frm_post.html');
		$post_gui->disableButtons(array(
			'removeformat',
			'charmap',
			'undo',
			'redo',
			'justifyleft',
			'justifycenter',
			'justifyright',
			'justifyfull',
			'anchor',
			'code',
			'fullscreen',
			'cut',
			'copy',
			'paste',
			'pastetext',
			'pasteword',
			'formatselect'
		));		
				
		// purifier
		require_once 'Services/Html/classes/class.ilHtmlPurifierFactory.php';
		$post_gui->setPurifier(ilHtmlPurifierFactory::_getInstanceByType('frm_post'));
		$this->create_topic_form_gui->addItem($post_gui);		
		
		// file
		$file_gui = new ilFileInputGUI($this->lng->txt('forums_attachments_add'), 'userfile');
		$this->create_topic_form_gui->addItem($file_gui);
		
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
		
		$this->create_topic_form_gui->addCommandButton('addThread', $this->lng->txt('save'));
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
		global $rbacsystem, $ilAccess;
		
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
			// build new thread
			if($this->objProperties->isAnonymized())
			{			
				$newPost = $frm->generateThread(
					$topicData['top_pk'],
					0,
					$this->handleFormInput($this->create_topic_form_gui->getInput('subject'), false),
					ilRTE::_replaceMediaObjectImageSrc($this->create_topic_form_gui->getInput('message'), 0),
					$this->create_topic_form_gui->getItemByPostVar('notify') ? (int)$this->create_topic_form_gui->getInput('notify') : 0,
					$this->create_topic_form_gui->getItemByPostVar('notify_posts') ? (int)$this->create_topic_form_gui->getInput('notify_posts') : 0,
					$this->create_topic_form_gui->getInput('alias')
				);
			}
			else
			{
				$newPost = $frm->generateThread(
					$topicData['top_pk'],
					$ilUser->getId(),
					$this->handleFormInput($this->create_topic_form_gui->getInput('subject'), false),
					ilRTE::_replaceMediaObjectImageSrc($this->create_topic_form_gui->getInput('message'), 0),
					$this->create_topic_form_gui->getItemByPostVar('notify') ? (int)$this->create_topic_form_gui->getInput('notify') : 0,
					$this->create_topic_form_gui->getItemByPostVar('notify_posts') ? (int)$this->create_topic_form_gui->getInput('notify_posts') : 0,
					$ilUser->getLogin()
				);
			}
			
			$file = $this->create_topic_form_gui->getInput('userfile');
			
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
} // END class.ilObjForumGUI
?>
