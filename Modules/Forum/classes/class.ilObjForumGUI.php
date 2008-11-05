<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
require_once './Modules/Forum/classes/class.ilForumProperties.php';
require_once './Services/Form/classes/class.ilPropertyFormGUI.php';
require_once './Modules/Forum/classes/class.ilForumPost.php';
require_once './Modules/Forum/classes/class.ilForum.php';
require_once './Modules/Forum/classes/class.ilForumTopic.php';

/**
* Class ilObjForumGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id$
*
* @ilCtrl_Calls ilObjForumGUI: ilPermissionGUI, ilForumExportGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjForumGUI: ilColumnGUI, ilPublicUserProfileGUI
*
* @ingroup ModulesForum
*/
class ilObjForumGUI extends ilObjectGUI
{
	private $objProperties = null;
	
	private $objCurrentTopic = null;	
	private $objCurrentPost = null;	
	private $display_confirm_post_deactivation = false;
	private $display_confirm_post_activation = false;
	
	private $is_moderator = false;
	
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
		$this->objCurrentTopic = new ilForumTopic(ilUtil::stripSlashes($_GET['thr_pk']), $ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']));

		// data of current topic/thread
		$this->objCurrentPost = new ilForumPost(ilUtil::stripSlashes($_GET['pos_pk']), $ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']));
	}

	/**
	* Execute Command.
	*/
	function &executeCommand()
	{
		global $ilNavigationHistory, $ilAccess;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		// workaround for new cancel button in edit/reply-post view, because form action does not "support" cmd=post
		if ($_POST['cmd']['cancelPost'] != '')
		{
			$cmd = key($_POST['cmd']);
		}	

		$exclude_cmds = array('showExplorer', 'viewThread',
							  'showThreadNotification',
					     	  'cancelPostActivation', 'cancelPostDeactivation',
					     	  'performPostActivation', 'performPostDeactivation', 'performPostAndChildPostsActivation',
					     	  'askForPostActivation', 'askForPostDeactivation',
					     	  'toggleThreadNotification', 'toggleThreadNotificationTab',
					     	  'toggleStickiness', 'cancelPost'
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
				require_once('./classes/class.ilPermissionGUI.php');
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilforumexportgui':
				require_once('./Modules/Forum/classes/class.ilForumExportGUI.php');
				$fex_gui =& new ilForumExportGUI($this);
				$ret =& $this->ctrl->forwardCommand($fex_gui);
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
				if (!$cmd)
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
			$this->objProperties->setAnonymisation(((int) $_POST['anonymized'] == 1) ? true : false);		
		}
		if ($ilSetting->get('enable_fora_statistics'))
		{
			$this->objProperties->setStatisticsStatus(((int) $_POST['statistics_enabled'] == 1) ? true : false);
		}
		$this->objProperties->setPostActivation(((int) $_POST['post_activation'] == 1) ? true : false);
				
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
		global $ilUser, $ilDB, $ilAccess, $lng;		

		if (!$ilAccess->checkAccess('read,visible', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		$frm =& $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->setForumRefId($this->object->getRefId());
		$frm->setWhereCondition('top_frm_fk = '.$ilDB->quote($frm->getForumId()));				

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_liste.html',	'Modules/Forum');
		
		$this->tpl->addBlockfile('BUTTONS', 'buttons', 'tpl.buttons.html');
			
		if ($ilAccess->checkAccess('add_post', '', $this->object->getRefId()) &&
			$ilAccess->checkAccess('add_thread', '', $this->object->getRefId()))
		{	
			// button: new topic
			$this->tpl->setCurrentBlock('btn_cell');
			$this->tpl->setVariable('BTN_LINK',	$this->ctrl->getLinkTarget($this, 'createThread'));
			$this->tpl->setVariable('BTN_TXT', $this->lng->txt('forums_new_thread'));
			$this->tpl->parseCurrentBlock();
		}		

		// button: enable/disable forum notification
		if ($this->ilias->getSetting('forum_notification') != 0)
		{
			$this->tpl->setCurrentBlock('btn_cell');			
			if ($frm->isForumNotificationEnabled($ilUser->getId()))
			{
				$this->tpl->setVariable('BTN_LINK', $this->ctrl->getLinkTarget($this, 'disableForumNotification'));
				$this->tpl->setVariable('BTN_TXT', $this->lng->txt('forums_disable_forum_notification'));
			}
			else
			{
				$this->tpl->setVariable('BTN_LINK', $this->ctrl->getLinkTarget($this,'enableForumNotification'));
				$this->tpl->setVariable('BTN_TXT', $this->lng->txt('forums_enable_forum_notification'));
			}
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable('TXT_HEADLINE', $this->lng->txt('forums_topics_overview'));

		$topicData = $frm->getOneTopic();
		if ($topicData)
		{
			// Visit-Counter
			$frm->setDbTable('frm_data');
			$frm->setWhereCondition('top_pk = '.$ilDB->quote($topicData['top_pk']));
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
				foreach ($threads as $thread)
				{				
					$rowCol = ilUtil::switchColor($z,'tblrow1','tblrow2');
					
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

						$this->tpl->setCurrentBlock('threads_row');
						$this->tpl->setVariable('ROWCOL', $rowCol);
				
						$thread->setCreateDate($frm->convertDate($thread->getCreateDate()));
						$this->tpl->setVariable('DATE', $thread->getCreateDate());
						$this->ctrl->setParameter($this, 'thr_pk', $thread->getId());
						$this->tpl->setVariable('TH_TITLE', $thread->getSubject());
						
						if ($thread->isSticky())
						{
							$this->tpl->setVariable('TXT_IS_STICKY', $this->lng->txt('sticky'));
						}
						
						if ($thread->isClosed())
						{
							$this->tpl->setVariable('TXT_IS_CLOSED', $this->lng->txt('topic_close'));
						}					
						
						if ($this->ilias->getSetting('forum_notification') != 0 &&
							$thread->isNotificationEnabled($ilUser->getId()))
						{
							$this->tpl->setVariable('NOTIFICATION_ENABLED', $this->lng->txt('forums_notification_enabled'));
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
							$this->tpl->setVariable('TH_HREF', $this->ctrl->getLinkTarget($this, 'showThreadFrameset'));
							$this->tpl->touchBlock('linked_title_b');
						}
						
						$this->tpl->setVariable('NUM_POSTS', $num_posts.' ('.$num_unread.')');						
						$this->tpl->setVariable('NEW_POSTS', $num_new);
						
						$this->tpl->setVariable('NUM_VISITS', $thread->getVisits());	
				
						// get author data
						if ($this->objProperties->isAnonymized())
						{
							if ($usr_data['login'] != '')
							{
								$this->tpl->setVariable('AUTHOR', $usr_data['login']);
							}
							else
							{
								$this->tpl->setVariable('AUTHOR', $this->lng->txt('forums_anonymous'));
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
									$this->tpl->setVariable('AUTHOR', $usr_data['login']);
								}
								else
								{
									$this->tpl->setVariable('AUTHOR',
										"<a href=\"".
										$this->ctrl->getLinkTarget($this, 'showUser').
										"\">".$usr_data['login']."</a>");
								}
								$this->ctrl->clearParameters($this);
							}
							else
							{
								$this->tpl->setVariable('AUTHOR', $usr_data['login']);
							}
						}				
						
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
								$this->tpl->setCurrentBlock('last_post');
								$this->tpl->setVariable('LP_DATE', $frm->convertDate($objLastPost->getCreateDate()));
								$this->tpl->setVariable('LP_FROM', $this->lng->txt('from'));
								$this->tpl->setVariable('LP_HREF',
								$this->ctrl->getLinkTarget($this, 'showThreadFrameset').'#'.$objLastPost->getId());
								if ($this->objProperties->isAnonymized())
								{
									if ($last_usr_data['login'] != '')
									{
										$this->tpl->setVariable('LP_TITLE', $last_usr_data['login']);
									}
									else
									{
										$this->tpl->setVariable('LP_TITLE', $this->lng->txt('forums_anonymous'));
									}
								}
								else
								{
									$this->tpl->setVariable('LP_TITLE', $last_usr_data['login']);
								}
								$this->tpl->parseCurrentBlock();
							}
							
							$this->tpl->setVariable('FORUM_ID', $thread->getId());
						}										
								
						$this->tpl->setVariable('THR_TOP_FK', $thread->getForumId());				
						$this->tpl->setVariable('TXT_PRINT', $this->lng->txt('print'));				
						$this->tpl->setVariable('THR_IMGPATH',$this->tpl->tplPath);				
						$this->tpl->setCurrentBlock('threads_row');
						$this->tpl->parseCurrentBlock();				
					} // if (($thrNum > $pageHits && $z >= $Start) || $thrNum <= $pageHits)
			
					$z++;
			
				} // foreach
		
				$this->tpl->setVariable('TXT_SELECT_ALL', $this->lng->txt('select_all'));		
				$this->tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this));
				
				$this->tpl->setVariable('TXT_OK', $this->lng->txt('ok'));
				
				// options: please choose
				$this->tpl->setVariable('TXT_PLEASE_CHOOSE', $this->lng->txt('please_choose'));
				
				// options: export html							
				$this->tpl->setVariable('TXT_EXPORT_HTML', $this->lng->txt('export_html'));
				
				// options: enable/disable notification
				if ($this->ilias->getSetting('forum_notification') != 0)
				{
					$this->tpl->setVariable('TXT_DISABLE_NOTIFICATION', $this->lng->txt('forums_disable_notification'));
					$this->tpl->setVariable('TXT_ENABLE_NOTIFICATION', $this->lng->txt('forums_enable_notification'));
				}
				
				// options: sticky
				if ($ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
				{
					$this->tpl->setVariable('TXT_MAKE_STICKY', $this->lng->txt('make_topics_sticky'));
					$this->tpl->setVariable('TXT_UNMAKE_STICKY', $this->lng->txt('make_topics_non_sticky'));
				}
				
				// options: close/reopen
				if ($ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
				{					
					$this->tpl->setVariable('TXT_CLOSE_THREADS', $this->lng->txt('close_topics'));
					$this->tpl->setVariable('TXT_REOPEN_THREADS', $this->lng->txt('reopen_topics'));
				}
				
				// options: move
				if ($ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
				{
					$this->tpl->setVariable('TXT_MOVE_THREADS', $this->lng->txt('move'));
				}
				
				
				$this->tpl->setVariable('IMGPATH', $this->tpl->tplPath);
				
				// button: mark all read
				$this->tpl->setCurrentBlock('btn_cell');
				$this->tpl->setVariable('BTN_LINK', $this->ctrl->getLinkTarget($this, 'markAllRead'));
				$this->tpl->setVariable('BTN_TXT', $this->lng->txt('forums_mark_read'));
				$this->tpl->parseCurrentBlock();
				
				$this->tpl->setVariable('TXT_DATE', $this->lng->txt('date'));
				$this->tpl->setVariable('TXT_TITLE', $this->lng->txt('title'));
				$this->tpl->setVariable('TXT_TOPIC', $this->lng->txt('forums_thread'));
				$this->tpl->setVariable('TXT_AUTHOR', $this->lng->txt('forums_created_by'));
				$this->tpl->setVariable('TXT_NUM_POSTS', $this->lng->txt('forums_articles').' ('.$this->lng->txt('unread').')');
				$this->tpl->setVariable('TXT_NEW_POSTS',$this->lng->txt('forums_new_articles'));
				$this->tpl->setVariable('TXT_NUM_VISITS', $this->lng->txt('visits'));
				$this->tpl->setVariable('TXT_LAST_POST', $this->lng->txt('forums_last_post'));		
			} // if ($thrNum > 0)
			else
			{
				$this->tpl->setVariable('TXT_NO_THREADS', $this->lng->txt('forums_threads_not_available'));
			}
		} // if (is_array($topicData = $frm->getOneTopic()))		
		
		$this->tpl->setCurrentBlock('perma_link');
		$this->tpl->setVariable('PERMA_LINK', ILIAS_HTTP_PATH.'/goto.php?target='.$this->object->getType().'_'.$this->object->getRefId().'&client_id='.CLIENT_ID);
		$this->tpl->setVariable('TXT_PERMA_LINK', $this->lng->txt('perma_link'));
		$this->tpl->setVariable('PERMA_TARGET', '_top');
		$this->tpl->parseCurrentBlock();
		
		return true;
	}

	/**
	* creation form
	*/
	function createObject()
	{
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.frm_create.html", "Modules/Forum");

		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_frm.gif'));
		$this->tpl->setVariable("ALT_IMG", $this->lng->txt('edit_properties'));

		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
		$this->tpl->setVariable("TITLE",$_POST['title']);
		$this->tpl->setVariable("DESC",$_POST['description']);

		$this->ctrl->setParameter($this, "new_type", $new_type);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		$this->tpl->setVariable("TARGET", ' target="'.
			ilFrameTargetInfo::_getFrame("MainContent").'" ');

		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt('frm_new'));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

		// DEFAULT ORDER
		$this->tpl->setVariable("TXT_VIEW",$this->lng->txt('frm_default_view'));
		$this->tpl->setVariable("TXT_ANSWER",$this->lng->txt('order_by').' '.$this->lng->txt('answers'));
		$this->tpl->setVariable("TXT_DATE",$this->lng->txt('order_by').' '.$this->lng->txt('date'));

		$default_sort = $_POST['sort'] ? $_POST['sort'] : 1;

		$this->tpl->setVariable("CHECK_ANSWER",ilUtil::formRadioButton($default_sort == 1 ? 1 : 0,'sort',1));
		$this->tpl->setVariable("CHECK_DATE",ilUtil::formRadioButton($default_sort == 2 ? 1 : 0,'sort',2));

		// ANONYMIZED OR NOT
		$this->tpl->setVariable("TXT_ANONYMIZED",$this->lng->txt('frm_anonymous_posting'));
		$this->tpl->setVariable("TXT_ANONYMIZED_DESC",$this->lng->txt('frm_anonymous_posting_desc'));

		$anonymized = $_POST['anonymized'] ? $_POST['anonymized'] : 0;

		$this->tpl->setVariable("CHECK_ANONYMIZED",
				ilUtil::formCheckbox(
				$anonymized == 1 && !$this->ilias->getSetting('disable_anonymous_fora',true) ? 1 : 0,
				'anonymized',1,
				!$this->ilias->getSetting("disable_anonymous_fora", true)?false:true));
	

		// Statistics enabled or not
		
		$statisticsEnabled  = $_POST['statistics_enabled'] ? $_POST['statistics_enabled'] : 1;
		
		$this->tpl->setVariable("TXT_STATISTICS_ENABLED", $this->lng->txt("frm_statistics_enabled"));	
		$this->tpl->setVariable("TXT_STATISTICS_ENABLED_DESC", $this->lng->txt("frm_statistics_enabled_desc"));
		
		
		$this->tpl->setVariable("CHECK_STATISTICS_ENABLED", 
			ilUtil::formCheckbox(
				$statisticsEnabled == 1 && $this->ilias->getSetting("enable_fora_statistics", true)? 1 : 0,
				'statistics_enabled', 1,
				$this->ilias->getSetting("enable_fora_statistics", true)?false:true));

		// show ilias 2 forum import for administrators only
		include_once("classes/class.ilMainMenuGUI.php");
		if(ilMainMenuGUI::_checkAdministrationPermission())
		{
			$this->tpl->setCurrentBlock("forum_import");
			$this->tpl->setVariable("FORMACTION_IMPORT",
				$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_IMPORT_FORUM", $this->lng->txt("forum_import")." (ILIAS 2)");
			$this->tpl->setVariable("TXT_IMPORT_FILE", $this->lng->txt("forum_import_file"));
			$this->tpl->setVariable("BTN2_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("BTN_IMPORT", $this->lng->txt("import"));
			$this->tpl->setVariable("TYPE_IMG2",ilUtil::getImagePath('icon_frm.gif'));
			$this->tpl->setVariable("ALT_IMG2", $this->lng->txt("forum_import"));
			$this->tpl->parseCurrentBlock();
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
		$this->__initFileObject();

		if(!$this->file_obj->storeUploadedFile($_FILES["importFile"]))	// STEP 1 save file in ...import/mail
		{
			$this->message = $this->lng->txt("import_file_not_valid"); 
			$this->file_obj->unlinkLast();
		}
		else if(!$this->file_obj->unzip())
		{
			$this->message = $this->lng->txt("cannot_unzip_file");			// STEP 2 unzip uplaoded file
			$this->file_obj->unlinkLast();
		}
		else if(!$this->file_obj->findXMLFile())						// STEP 3 getXMLFile
		{
			$this->message = $this->lng->txt("cannot_find_xml");
			$this->file_obj->unlinkLast();
		}
		else if(!$this->__initParserObject($this->file_obj->getXMLFile()) or !$this->parser_obj->startParsing())
		{
			$this->message = $this->lng->txt("import_parse_error").":<br/>"; // STEP 5 start parsing
		}

		// FINALLY CHECK ERROR
		if(!$this->message)
		{
			ilUtil::sendInfo($this->lng->txt("import_forum_finished"),true);
			$ref_id = $this->parser_obj->getRefId();
			if ($ref_id > 0)
			{
				$this->ctrl->setParameter($this, "ref_id", $ref_id);
				ilUtil::redirect($this->getReturnLocation("save",
					$this->ctrl->getLinkTarget($this, "showThreads")));
			}
			else
			{
				ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$_GET["ref_id"]);
			}
		}
		else
		{
			ilUtil::sendInfo($this->message);
			$this->createObject();
		}
	}


	/**
	* save object
	* @access	public
	*/
	function saveObject($a_prevent_redirect = false)
	{
		global $rbacadmin, $ilDB, $ilUser;

		$_POST['Fobject']['title'] = $_POST['title'];
		$_POST['Fobject']['desc'] = $_POST['desc'];

		// create and insert forum in objecttree
		$forumObj = parent::saveObject();		
		
		// save settings
		$this->objProperties->setObjId($forumObj->getId());
		$this->objProperties->setDefaultView(((int) $_POST['sort']));
		if (!$this->ilias->getSetting('disable_anonymous_fora') || $this->objProperties->isAnonymized())
		{
			$this->objProperties->setAnonymisation(((int) $_POST['anonymized'] == 1) ? true : false);
		}
		$this->objProperties->setStatisticsStatus(((int) $_POST['statistics_enabled'] == 1) ? true : false);
		$this->objProperties->insert();		
			
		$forumObj->createSettings();

		// setup rolefolder & default local roles (moderator)
		$roles = $forumObj->initDefaultRoles();

		// ...finally assign moderator role to creator of forum object
		$rbacadmin->assignUser($roles[0], $forumObj->getOwner(), "n");
			
		// insert new forum as new topic into frm_data
		$top_data = array(
            'top_frm_fk'   		=> $forumObj->getId(),
			'top_name'   		=> $forumObj->getTitle(),
            'top_description' 	=> $forumObj->getDescription(),
            'top_num_posts'     => 0,
            'top_num_threads'   => 0,
            'top_last_post'     => '',
			'top_mods'      	=> $roles[0],
			'top_usr_id'      	=> $ilUser->getId(),
            'top_date' 			=> date("Y-m-d H:i:s")
        );
	
		$q = "INSERT INTO frm_data ";
		$q .= "(top_frm_fk,top_name,top_description,top_num_posts,top_num_threads,top_last_post,top_mods,top_date,top_usr_id) ";
		$q .= "VALUES ";
		$q .= "(".$ilDB->quote($top_data["top_frm_fk"]).",".$ilDB->quote($top_data["top_name"]).",".$ilDB->quote($top_data["top_description"]).",".
			$ilDB->quote($top_data["top_num_posts"]).",".$ilDB->quote($top_data["top_num_threads"]).",".$ilDB->quote($top_data["top_last_post"]).",".
			$ilDB->quote($top_data["top_mods"]).",'".$top_data["top_date"]."',".$ilDB->quote($top_data["top_usr_id"]).")";
		$this->ilias->db->query($q);

		$this->object = $forumObj;
		
		// always send a message
		ilUtil::sendInfo($this->lng->txt('frm_added'), true);
		
		$this->ctrl->setParameter($this, 'ref_id', $forumObj->getRefId());

		if (!$a_prevent_redirect)
		{
			ilUtil::redirect($this->ctrl->getLinkTarget($this, 'createThread'));
		}
	}

	function getTabs(&$tabs_gui)
	{
		global $ilAccess;

		$this->ctrl->setParameter($this, 'ref_id', $this->ref_id);
		
		#if ($ilAccess->checkAccess('write', '', $this->ref_id))
		#{
		include_once './classes/class.ilRepositoryExplorer.php';
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

		$header_params = array('ref_id' => $this->ref_id, 'cmd' => 'statistic');
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
			$frm = new ilForum();
		
			$frm->setForumId($forumObj->getId());
			$frm->setForumRefId($forumObj->getRefId());
		
			$dead_thr = $frm->deletePost($this->objCurrentPost->getId());
				
			// if complete thread was deleted ...
			if ($dead_thr == $this->objCurrentTopic->getId())
			{
				$frm->setWhereCondition('top_frm_fk = '.$ilDB->quote($forumObj->getId()));
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
			$this->objCurrentPost->activatePostAndChildPosts();
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
	public function setDisplayConfirmPostActivation($status = false)
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
	
	
	/**
	 * View single thread
	 */
	public function viewThreadObject()
	{
		global $tpl, $lng, $ilUser, $ilAccess, $ilTabs, $rbacsystem,
			   $rbacreview, $ilDB, $ilNavigationHistory, $ilCtrl, $frm;
			
		if(!$ilAccess->checkAccess('read,visible', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		require_once './Modules/Forum/classes/class.ilObjForum.php';
		require_once './Modules/Forum/classes/class.ilFileDataForum.php';
		
		$lng->loadLanguageModule('forum');
		
		if (!empty($_POST['addQuote']))
		{
			$_GET['action'] = 'showreply';
		}

		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			$ilCtrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
			$ilNavigationHistory->addItem($this->object->getRefId(), $ilCtrl->getLinkTarget($this, 'showThreadFrameset'), 'frm');
		}
		
		$forumObj = new ilObjForum($_GET['ref_id']);
		$frm = $forumObj->Forum;		
		
		// save last access
		$forumObj->updateLastAccess($ilUser->getId(), (int) $this->objCurrentTopic->getId());
		
		// mark post read if explorer link was clicked
		if ($this->objCurrentTopic->getId() && $this->objCurrentPost->getId())
		{
			$forumObj->markPostRead($ilUser->getId(), (int) $this->objCurrentTopic->getId(), (int) $this->objCurrentPost->getId());
		}
		
		$file_obj = new ilFileDataForum($forumObj->getId(), $this->objCurrentPost->getId());

		$frm->setForumId($forumObj->getId());
		$frm->setForumRefId($forumObj->getRefId());		
		
		
		$this->prepareThreadScreen($forumObj);
		
		$tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_view.html', 'Modules/Forum');

		$formData = $_POST['formData'];
		// form processing (edit & reply)
		if ($_GET['action'] == 'ready_showreply' || $_GET['action'] == 'ready_showedit')
		{
			// check form-dates
			$errors = '';

			if (trim($formData['message']) == '') $errors .= $lng->txt('forums_the_post').', ';
			if ($errors != '') $errors = substr($errors, 0, strlen($errors)-2);

			if ($errors != '')
			{
				ilUtil::sendInfo($lng->txt('form_empty_fields').' '.$errors);
				$_GET['action'] = substr($_GET['action'], 6);
				$_GET['show_post'] = 1;
			}
		}

		// delete file
		if (isset($_POST['cmd']['delete_file']))
		{
			$file_obj->unlinkFiles($_POST['del_file']);
			ilUtil::sendInfo('File deleted');
		}
		// download file
		if ($_GET['file'])
		{
			if(!$path = $file_obj->getAbsolutePath(urldecode($_GET['file'])))
			{
				ilUtil::sendInfo('Error reading file!');
			}
			else
			{
				ilUtil::deliverFile($path, urldecode($_GET['file']));
			}
		}		

		$session_name = 'viewmode_'.$forumObj->getId();
		if ($_SESSION[$session_name] == 'flat')
		{
			$new_order = 'answers';
			$orderField = 'frm_posts_tree.date';
		}
		else
		{
			$new_order = 'date';
			$orderField = 'frm_posts_tree.rgt';
		}
				
		// get forum- and thread-data
		$frm->setWhereCondition('top_frm_fk = '.$ilDB->quote($frm->getForumId()));		
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
			// menu template (contains linkbar, new topic and print thread button)
			$menutpl = new ilTemplate('tpl.forums_threads_menu.html', true, true, 'Modules/Forum');
			
			// make/unmake sticky			
			/*if ($ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
			{
				$menutpl->setCurrentBlock('btn_cell');
				$this->ctrl->setParameter($this, 'thr_pk',  $this->objCurrentTopic->getId());
				$menutpl->setVariable('BTN_LINK', $this->ctrl->getLinkTarget($this, 'toggleStickiness'));
				$this->ctrl->clearParameters($this);
				if ($this->objCurrentTopic->isSticky())
				{
					$menutpl->setVariable('BTN_TXT', $lng->txt('make_topic_non_sticky'));	
				}
				else
				{
					$menutpl->setVariable('BTN_TXT', $lng->txt('make_topic_sticky'));
				}
				
				$menutpl->parseCurrentBlock();
			}*/
		
			// mark read
			if($forumObj->getCountUnread($ilUser->getId(), (int) $this->objCurrentTopic->getId()))
			{
				$menutpl->setCurrentBlock('btn_cell');
				$this->ctrl->setParameter($this, 'mark_read', '1');
				$this->ctrl->setParameter($this, 'thr_pk',  $this->objCurrentTopic->getId());
				$menutpl->setVariable('BTN_LINK', $this->ctrl->getLinkTarget($this, 'showThreadFrameset'));
				$this->ctrl->clearParameters($this);
				$t_frame = ilFrameTargetInfo::_getFrame('MainContent');
				$menutpl->setVariable('BTN_TARGET', ' target="'.$t_frame.'"');
				$menutpl->setVariable('BTN_TXT', $lng->txt('forums_mark_read'));
				$menutpl->parseCurrentBlock();
			}

			// print thread
			$menutpl->setCurrentBlock('btn_cell');
			$this->ctrl->setParameterByClass('ilforumexportgui', 'print_thread', $this->objCurrentTopic->getId());
			$this->ctrl->setParameterByClass('ilforumexportgui', 'thr_top_fk', $this->objCurrentTopic->getForumId());
			$menutpl->setVariable('BTN_LINK', $this->ctrl->getLinkTargetByClass('ilforumexportgui', 'printThread'));
			$menutpl->setVariable('BTN_TARGET', ' target="'.$t_frame.'"');
			$menutpl->setVariable('BTN_TXT', $lng->txt('forums_print_thread'));
			$menutpl->parseCurrentBlock();
		
			// enable/disable notification
			if($this->ilias->getSetting('forum_notification') != 0)
			{
				$menutpl->setCurrentBlock('btn_cell');
				if ($this->objCurrentTopic->isNotificationEnabled($ilUser->getId()))
				{
					$menutpl->setVariable('BTN_TXT', $lng->txt('forums_disable_notification'));
				}
				else
				{
					$menutpl->setVariable('BTN_TXT', $lng->txt('forums_enable_notification'));
				}
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
				$menutpl->setVariable('BTN_LINK', $this->ctrl->getLinkTarget($this, 'toggleThreadNotification'));
				$this->ctrl->clearParameters($this);
				$menutpl->setVariable('BTN_TARGET', ' target="'.$t_frame.'"');
				$menutpl->parseCurrentBlock();
			}
		
			// ********************************************************************************
		
			// form processing (edit & reply)
			if (!$this->objCurrentTopic->isClosed() && ($_GET['action'] == 'ready_showreply' || $_GET['action'] == 'ready_showedit' || $_GET['action'] == 'ready_censor'))
			{
				if ($_GET['action'] != 'ready_censor')
				{
					$_GET['show_post'] = 0;
						
					// Generating new posting
					if ($_GET['action'] == 'ready_showreply')
					{
						// reply: new post						
						
						$status = 1;
						$send_activation_mail = false;
						
						if ($this->objProperties->isPostActivationEnabled())
						{
							if (!$ilAccess->checkAccess('moderate_frm', '', (int) $this->object->getRefId()))								
							{
								$status = 0;
								$send_activation_mail = true;								
							}
							else if ($this->objCurrentPost->isAnyParentDeactivated())
							{
								$status = 0;
							}
						}						

						$newPost = $frm->generatePost($topicData['top_pk'], $this->objCurrentTopic->getId(),
													  ($this->objProperties->isAnonymized() ? 0 : $ilUser->getId()), $this->handleFormInput($formData['message']),
													  $_GET['pos_pk'], $_POST['notify'],
													  $formData['subject']
														? $this->handleFormInput($formData['subject'])
														:  $this->objCurrentTopic->getSubject(),
													  ilUtil::stripSlashes($formData['alias']),
													  '',
													  $status,
													  $send_activation_mail);
							
						
						if (!$ilAccess->checkAccess('moderate_frm', '', (int) $this->object->getRefId())
							&& $status == 0)								
						{
							ilUtil::sendInfo($lng->txt('forums_post_needs_to_be_activated'));
						}
						else
						{
							ilUtil::sendInfo($lng->txt('forums_post_new_entry'));
						}
						
						if (isset($_FILES['userfile']))
						{
							$tmp_file_obj =& new ilFileDataForum($forumObj->getId(), $newPost);
							$tmp_file_obj->storeUploadedFile($_FILES['userfile']);
						}		
					}
					else
					{
						$this->objCurrentPost->setSubject($formData['subject'] ? $this->handleFormInput($formData['subject']) :  $this->objCurrentPost->getSubject());
						$this->objCurrentPost->setMessage($this->handleFormInput($formData['message']));
						$this->objCurrentPost->setNotification($_POST['notify'] ? 1 : 0);
						$this->objCurrentPost->setChangeDate(date("Y-m-d H:i:s"));
						$this->objCurrentPost->setUpdateUserId($ilUser->getId());
						
						// edit: update post
						if ($this->objCurrentPost->update())
						{
							$this->objCurrentPost->reload();
							
							// Change news item accordingly
							include_once("./Services/News/classes/class.ilNewsItem.php");
							// note: $this->objCurrentPost->getForumId() does not give us the forum ID here (why?)
							$news_id = ilNewsItem::getFirstNewsIdForContext($forumObj->getId(),
								"frm", $this->objCurrentPost->getId(), "pos");
							if ($news_id > 0)
							{
								$news_item = new ilNewsItem($news_id);
								$news_item->setTitle($this->objCurrentPost->getSubject());
								$news_item->setContent($frm->prepareText(
									$this->objCurrentPost->getMessage(), 0));
								$news_item->update();
							}
							
							ilUtil::sendInfo($lng->txt('forums_post_modified'));
						}
						if (isset($_FILES['userfile']))
						{
							$file_obj->storeUploadedFile($_FILES['userfile']);
						}
					}
		
					if($_SESSION['viewmode_'.$forumObj->getId()] == 'tree')
					{
						$tpl->setCurrentBlock('javascript_block');
						$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
						$tpl->setVariable('JAVASCRIPT',	$this->ctrl->getLinkTarget($this, 'showExplorer'));
						$this->ctrl->clearParameters($this);
						$tpl->parseCurrentBlock();						
					}

				} // if ($_GET["cmd"] != "ready_censor")
				// insert censorship
				elseif ($_POST['confirm'] != '' && $_GET['action'] == 'ready_censor')
				{
					$frm->postCensorship($this->handleFormInput($formData['cens_message']), $this->objCurrentPost->getId(), 1);
				}
				elseif ($_POST['cancel'] != '' && $_GET['action'] == 'ready_censor')
				{
					$frm->postCensorship($this->handleFormInput($formData['cens_message']), $this->objCurrentPost->getId());
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
								// edit attachments
								if(count($file_obj->getFilesOfPost()) && $_GET['action'] == 'showedit')
								{
									foreach($file_obj->getFilesOfPost() as $file)
									{
										$tpl->setCurrentBlock('attachment_edit_row');
										$tpl->setVariable('FILENAME', $file['name']);
										$tpl->setVariable('CHECK_FILE', ilUtil::formCheckbox(0, 'del_file[]', $file['name']));
										$tpl->parseCurrentBlock();
									}
			
									$tpl->setCurrentBlock('reply_attachment_edit');
									$tpl->setVariable('TXT_ATTACHMENTS_EDIT', $lng->txt('forums_attachments_edit'));
									$tpl->setVariable('ATTACHMENT_EDIT_DELETE', $lng->txt('forums_delete_file'));
									$tpl->parseCurrentBlock();
								}
			
								// add attachments
								$tpl->setCurrentBlock('reply_attachment');
								$tpl->setVariable('TXT_ATTACHMENTS_ADD', $lng->txt('forums_attachments_add'));
								#$tpl->setVariable('BUTTON_UPLOAD', $lng->txt('upload'));
								$tpl->parseCurrentBlock();
								
								$tpl->setCurrentBlock('reply_post');
								$tpl->setVariable('REPLY_ANKER', $node->getId());
			
								if($this->objProperties->isAnonymized() && $_GET['action'] == 'showreply')
								{
									$tpl->setCurrentBlock('alias');
									$tpl->setVariable('TXT_FORM_ALIAS', $lng->txt('forums_your_name'));
									$tpl->setVariable('TXT_ALIAS_INFO', $lng->txt('forums_use_alias'));								
									$tpl->setVariable('ALIAS_VALUE',  $_GET['show_post'] == 1 ?
													  ilUtil::prepareFormOutput($_POST['formData']['alias'], true) :
													  ''
									);
									$tpl->parseCurrentBlock();
								}
	
								$tpl->setVariable('TXT_FORM_SUBJECT', $lng->txt('forums_subject'));
								if($_GET['action'] == 'showreply')
								{
									$tpl->setVariable('TXT_FORM_MESSAGE', $lng->txt('forums_your_reply'));
								}
								else
								{
									$tpl->setVariable('TXT_FORM_MESSAGE', $lng->txt('forums_edit_post'));
								}
	
								if($_GET['action'] == 'showreply')
								{
									$tpl->setVariable('SUBJECT_VALUE',
										($_GET['show_post'] == 1 ?
											$this->forwardInputToOutput($_POST['formData']['subject']) :
											$this->prepareFormOutput($this->objCurrentTopic->getSubject())));
	
									if (!empty($_POST['addQuote']))
									{
										$tpl->setVariable('MESSAGE_VALUE',
											($_GET['show_post'] == 1 ?
												ilUtil::prepareFormOutput($_POST['formData']['message'], true) :
												$frm->prepareText($node->getMessage(), 1, $node->getLoginName())."\n".
												ilUtil::prepareFormOutput($_POST['formData']['message'], true)
												));
									}
									else
									{
										$tpl->setVariable('MESSAGE_VALUE',
											($_GET['show_post'] == 1 ?
												ilUtil::prepareFormOutput($_POST['formData']['message'], true) :
												''));
									}
								}
								else
								{
									$tpl->setVariable('SUBJECT_VALUE',
										($_GET['show_post'] == 1 ?
											$this->forwardInputToOutput($_POST['formData']['subject']) :
											$this->prepareFormOutput($node->getSubject())));
									$tpl->setVariable('MESSAGE_VALUE',
										($_GET['show_post'] == 1 ?
											ilUtil::prepareFormOutput($_POST['formData']['message'], true) :
											$frm->prepareText($node->getMessage(), 2)));
								}
								
								// NOTIFY
								include_once 'Services/Mail/classes/class.ilMail.php';
								$umail = new ilMail($_SESSION['AccountId']);
	
								if($rbacsystem->checkAccess('mail_visible', $umail->getMailObjectReferenceId()))
								{
									global $ilUser;
									
									// only if gen. notification is disabled and forum isn't anonymous
									if (!$frm->isThreadNotificationEnabled($ilUser->getId(), $node->getThreadId()) &&
										!$this->objProperties->isAnonymized())
									{
										$tpl->setCurrentBlock('notify');
										$tpl->setVariable('NOTIFY', $lng->txt('forum_notify_me'));
										
										if($_GET['action'] == 'showreply')
										{
											$tpl->setVariable('NOTIFY_CHECKED', $_POST['notify'] ? ' checked="checked"' : '');
										}
										else if($_GET['action'] == 'showedit')
										{
											if(isset($_POST['SUB']))
											{
												$tpl->setVariable('NOTIFY_CHECKED', $_POST['notify'] ? ' checked="checked"' : '');	
											}
											else
											{
												$tpl->setVariable('NOTIFY_CHECKED', $node->isNotificationEnabled() ? "checked=\"checked\"" : '');	
											}								
										}
										
										$tpl->parseCurrentBlock();
									}
								}
								
								if ($_GET['action'] == 'showreply' || !empty($_POST['addQuote']))
								{
									$tpl->setCurrentBlock('quotation');
									$tpl->setVariable('TXT_ADD_QUOTE', $lng->txt('forum_add_quote'));
									$tpl->parseCurrentBlock();
								}
	
								$tpl->setVariable('SUBMIT', $lng->txt('submit'));							
								$tpl->setVariable('CANCEL_FORM_TXT', $lng->txt('cancel'));														
								#$tpl->setVariable('RESET', $lng->txt('reset'));
								
								$this->ctrl->setParameter($this, 'action', 'ready_'.$_GET['action']);
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
								$tpl->setVariable('FORMACTION',	$this->ctrl->getLinkTarget($this, 'viewThread', $node->getId()));
								$this->ctrl->clearParameters($this);
								$tpl->parseCurrentBlock();
			
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
							if (!$node->isRead($ilUser->getId()))
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
								$this->ctrl->setParameter($this, 'file', urlencode($file['name']));
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
		
						$node->setChangeDate($frm->convertDate($node->getChangeDate()));

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

							if($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']) && $usr_data['public_profile'] != 'n')
							{
								$tpl->setVariable('USR_NAME', $usr_data['firstname'].' '.$usr_data['lastname']);
							}
							
							if($ilAccess->checkAccessOfUser($user_obj->getId(), 'moderate_frm', '', $_GET['ref_id']))
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

					// make links in post usable
					$node->setMessage(ilUtil::makeClickable($node->getMessage()));
		
					// prepare post
					$node->setMessage($frm->prepareText($node->getMessage()));
		
					$tpl->setVariable('TXT_CREATE_DATE', $lng->txt('forums_thread_create_date'));
		
					if ($node->isRead($ilUser->getId()))
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
							$tpl->setVariable('POST', "<span class=\"".$spanClass."\">".nl2br($node->getMessage())."</span>");
						}
						else
						{
							$tpl->setVariable('POST', nl2br($node->getMessage()));
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
		
		$tpl->setCurrentBlock('perma_link_bottom_block');
		$tpl->setVariable('PERMA_LINK_BOTTOM', ILIAS_HTTP_PATH.'/goto.php?target='.'frm'.'_'.$_GET['ref_id'].'_'.$this->objCurrentTopic->getId().'&client_id='.CLIENT_ID);
		$tpl->setVariable('TXT_PERMA_LINK_BOTTOM', $lng->txt('perma_link'));
		$tpl->setVariable('PERMA_TARGET_BOTTOM', '_top');
		$tpl->parseCurrentBlock();
		
		$tpl->setVariable('COUNT_POST', $lng->txt('forums_count_art').': '.$posNum);
		$tpl->setVariable('TXT_AUTHOR', $lng->txt('author'));
		$tpl->setVariable('TXT_POST', $lng->txt('forums_thread').': '.$this->objCurrentTopic->getSubject());
		
		$tpl->setVariable('TPLPATH', $tpl->vars['TPLPATH']);
		
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
			$tpl->addBlockFile('BUTTONS', 'buttons', 'tpl.buttons.html');
			$tpl->setCurrentBlock('btn_cell');
			$tpl->setVariable('BTN_LINK', urldecode($_GET['backurl']));
			$tpl->setVariable('BTN_TXT', $lng->txt('back'));
			$tpl->parseCurrentBlock();
		}
				
		$tpl->setVariable('TPLPATH', $tpl->vars['TPLPATH']);
		
		return true;
	}
	
	/**
	* Perform form action in threads list.
	*/
	function performThreadsActionObject()
	{
		global $ilUser, $ilAccess;
		
		unset($_SESSION['threads2move']);
		unset($_SESSION['forums_search_submitted']);
		
		if (is_array($_POST['forum_id']))
		{
			if ($_POST['action'] == 'move')
			{
				if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
				{
					$_SESSION['threads2move'] = $_POST['forum_id'];
					$this->moveThreadsObject();
				}
			}
			else if ($_POST['action'] == 'enable_notifications' && $this->ilias->getSetting('forum_notification') != 0)
			{
				for ($i = 0; $i < count($_POST['forum_id']); $i++)
				{
					$tmp_obj = new ilForumTopic($_POST['forum_id'][$i]);
					$tmp_obj->enableNotification($ilUser->getId());
					unset($tmp_obj);
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}
			else if ($_POST['action'] == 'disable_notifications' && $this->ilias->getSetting('forum_notification') != 0)
			{
				for ($i = 0; $i < count($_POST['forum_id']); $i++)
				{
					$tmp_obj = new ilForumTopic($_POST['forum_id'][$i]);
					$tmp_obj->disableNotification($ilUser->getId());
					unset($tmp_obj);
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}
			else if ($_POST['action'] == 'close')
			{
				if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
				{
					for ($i = 0; $i < count($_POST['forum_id']); $i++)
					{
						$tmp_obj = new ilForumTopic($_POST['forum_id'][$i]);
						$tmp_obj->close();
						unset($tmp_obj);
					}
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}
			else if ($_POST['action'] == 'reopen')
			{
				if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
				{
					for ($i = 0; $i < count($_POST['forum_id']); $i++)
					{
						$tmp_obj = new ilForumTopic($_POST['forum_id'][$i]);
						$tmp_obj->reopen();
						unset($tmp_obj);
					}
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}
			else if ($_POST['action'] == 'makesticky')
			{
				if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
				{
					for ($i = 0; $i < count($_POST['forum_id']); $i++)
					{
						$tmp_obj = new ilForumTopic($_POST['forum_id'][$i]);
						$tmp_obj->makeSticky();
						unset($tmp_obj);
					}
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}
			else if ($_POST['action'] == 'unmakesticky')
			{
				if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
				{
					for ($i = 0; $i < count($_POST['forum_id']); $i++)
					{
						$tmp_obj = new ilForumTopic($_POST['forum_id'][$i]);
						$tmp_obj->unmakeSticky();
						unset($tmp_obj);
					}
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}
			else if ($_POST['action'] == 'html')
			{
				$this->ctrl->setCmd('exportHTML');
				$this->ctrl->setCmdClass('ilForumExportGUI');
				$this->executeCommand();
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
		global $ilAccess, $lng, $ilDB, $tree, $ilObjDataCache;
		
		if (!$ilAccess->checkAccess('moderate_frm', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
				
		$threads2move = $_SESSION['threads2move'];
		
		if (empty($threads2move))
		{
			ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'), true);
			$this->ctrl->redirect($this, 'showThreads');
		}		
		
		require_once 'Services/Table/classes/class.ilTable2GUI.php';
		
		$this->tpl->addBlockfile('BUTTONS', 'buttons', 'tpl.buttons.html');			
	
		// button: back
		$this->tpl->setCurrentBlock('btn_cell');
		$this->tpl->setVariable('BTN_LINK',	$this->ctrl->getLinkTarget($this));
		$this->tpl->setVariable('BTN_TXT', $this->lng->txt('back'));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_move.html', 'Modules/Forum');

		if ($confirm)
		{
			include_once('Services/Utilities/classes/class.ilConfirmationGUI.php');
			$c_gui = new ilConfirmationGUI();
			
			$c_gui->setFormAction($this->ctrl->getFormAction($this, 'performMoveThreads'));
			$c_gui->setHeaderText($this->lng->txt('sure_move_threads'));
			$c_gui->setCancel($this->lng->txt('cancel'), 'cancelMoveThreads');
			$c_gui->setConfirm($this->lng->txt('confirm'), 'performMoveThreads');	
			
			foreach($threads2move as $thr_pk)
			{			
				$c_gui->addHiddenItem('thr_id[]', $thr_pk);
			}			
			
			$c_gui->addHiddenItem('frm_ref_id', $_POST['frm_ref_id']);
						
			$this->tpl->setVariable('CONFIRM_TABLE', $c_gui->getHTML());
		}	

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

			$result[$counter]['thr_subject'] = $objCurrentTopic->getSubject();
			$result[$counter]['thr_pk'] = $thr_pk;
			
			unset($objCurrentTopic);
			
			++$counter;			
		}			 	
		$tblThr->setData($result);		
		$this->tpl->setVariable('THREADS_TABLE', $tblThr->getHTML());			
		
		
		if (!$_SESSION['forums_search_submitted'])
		{
			$forums_ref_ids = ilUtil::_getObjectsByOperations('frm', 'moderate_frm', 0, -1);
		}
		else
		{
			$this->lng->loadLanguageModule('search');
			include_once './Services/Search/classes/class.ilQueryParser.php';
			$query_parser = new ilQueryParser(ilUtil::stripSlashes($_POST['title']));
			$query_parser->setMinWordLength(1);
			$query_parser->setCombination(QP_COMBINATION_AND);
			$query_parser->parse();
			if (!$query_parser->validate())
			{
				ilUtil::sendInfo($query_parser->getMessage());
			}
			else
			{			
				include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
				$object_search = new ilLikeObjectSearch($query_parser);
		
				$object_search->setFilter(array('frm'));
				$res = $object_search->performSearch();
				$res->setRequiredPermission('moderate_frm');	
				
				$res->filter(ROOT_FOLDER_ID, true);
				
				if (!count($forums = $res->getResults()))
				{
					ilUtil::sendInfo($this->lng->txt('search_no_match'));
				}
			}
		}
		
		if ($_SESSION['forums_search_submitted'] || 
			count($forums_ref_ids) >= $this->ilias->getSetting('search_max_hits', 100)) 
		{
			$this->tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this, 'searchForums'));
			$this->tpl->setVariable('SEARCH_COMMAND', 'searchForums');
			
			$this->tpl->setVariable('TXT_SEARCH_TITLE', $this->lng->txt('search'));
			$this->tpl->setVariable('TXT_TITLE', $this->lng->txt('title'));
			$this->tpl->setVariable('TXT_SEARCH_COMMAND', $this->lng->txt('search'));
			$this->tpl->setVariable('VAL_TITLE', ilUtil::prepareFormOutput($_POST['title'], true));
		}

		if (($_SESSION['forums_search_submitted'] && is_object($query_parser) && $query_parser->validate()) || 
			(!$_SESSION['forums_search_submitted'] && count($forums_ref_ids) < $this->ilias->getSetting('search_max_hits', 100)))
		{
			$this->tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this, 'confirmMoveThreads'));
			
			$tbl = new ilTable2GUI($this);
			$tbl->setTitle($this->lng->txt('to_forum'));
			$tbl->setLimit(0);		
			$tbl->setRowTemplate('tpl.forums_threads_move_frm_row.html', 'Modules/Forum');
			$tbl->addColumn('', 'radio', '1%');
		 	$tbl->addColumn($this->lng->txt('title'), 'top_name', '10%');
		 	$tbl->addColumn($this->lng->txt('path'), 'path', '89%');
			$tbl->disable('footer');
			$tbl->disable('sort');
			$tbl->disable('linkbar');
			
			$tbl->setColumnWidth(2);	 	
			$tbl->setDefaultOrderField('top_name');
			
			$result = array();	
			
			$counter = 0;
			if (is_array($forums_ref_ids))
			{		
				foreach ($forums_ref_ids as $ref_id)
				{
					if ($ilObjDataCache->lookupObjId($_GET['ref_id']) != $ilObjDataCache->lookupObjId($ref_id))
					{
						$this->object->Forum->setWhereCondition(" top_frm_fk = '".$ilObjDataCache->lookupObjId($ref_id)."' ");
							
						if(!is_null($frmData = $this->object->Forum->getOneTopic()))
						{
							$check = 0;			
							if (isset($_POST['frm_ref_id']) && $_POST['frm_ref_id'] == $ref_id) $check = 1;  
										
							$result[$counter]['radio'] = ilUtil::formRadioButton($check, 'frm_ref_id', $ref_id);
							$result[$counter]['top_name'] = $frmData['top_name'];
							
							$path_arr = $tree->getPathFull($ref_id, ROOT_FOLDER_ID);
							$path_counter = 0;
							$path = '';
							foreach($path_arr as $data)
							{
								if($path_counter++)
								{
									$path .= " -> ";
								}
								$path .= $data['title'];
							}
							$result[$counter]['path'] = $this->lng->txt('path').': '.$path;
							
							++$counter;
						}
					}
				}	 	
			}
			if (is_array($forums))
			{
				foreach ($forums as $obj_id => $val)
				{
					if ($ilObjDataCache->lookupObjId($_GET['ref_id']) != $val['obj_id'])
					{	
						$this->object->Forum->setWhereCondition(" top_frm_fk = '".$val['obj_id']."' ");		
						if(!is_null($frmData = $this->object->Forum->getOneTopic()))				
						{
							$check = 0;			
							if (isset($_POST['frm_ref_id']) && $_POST['frm_ref_id'] == $val['ref_id']) $check = 1;  
										
							$result[$counter]['radio'] = ilUtil::formRadioButton($check, 'frm_ref_id', $val['ref_id']);
							$result[$counter]['top_name'] = $frmData['top_name'];
							$path_arr = $tree->getPathFull($val['ref_id'], ROOT_FOLDER_ID);
							$path_counter = 0;
							$path = '';
							foreach($path_arr as $data)
							{
								if($path_counter++)
								{
									$path .= " -> ";
								}
								$path .= $data['title'];
							}
							$result[$counter]['path'] = $this->lng->txt('path').': '.$path;
							
							++$counter;
						}
					}
				}
			}
			
			$tbl->setData($result);
			
			if (empty($result))
			{
				$tbl->setNoEntriesText($lng->txt('no_forum_available_for_moving_threads'));
			}
			else
			{		
				$tbl->addCommandButton('confirmMoveThreads', $this->lng->txt('move'));
			}
			
			$this->tpl->setVariable('FORUMS_TABLE', $tbl->getHTML());
		}				

		return true;
	}
	
	/**
	* New Thread form.
	*/
	function createThreadObject($errors = '')
	{
		global $lng, $tpl, $rbacsystem, $ilias, $ilDB, $ilAccess;
		
		require_once './Modules/Forum/classes/class.ilObjForum.php';
		
		$lng->loadLanguageModule('forum');
		
		$forumObj = new ilObjForum($_GET['ref_id']);
		$frm =& $forumObj->Forum;
		
		$frm->setForumId($forumObj->getId());
		$frm->setForumRefId($forumObj->getRefId());
		
		$frm->setWhereCondition('top_frm_fk = '.$ilDB->quote($frm->getForumId()));
		$topicData = $frm->getOneTopic();		
		
		$tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_new.html',	'Modules/Forum');
				
		if ($errors != '')
		{
			ilUtil::sendInfo($lng->txt('form_empty_fields').' '.$errors);
		}

		if (!$ilAccess->checkAccess('add_thread,add_post', '', $forumObj->getRefId()))
		{
			$ilias->raiseError($lng->txt('permission_denied'), $ilias->error_obj->MESSAGE);
		}		
		
		$tpl->setCurrentBlock('new_thread');
		$tpl->setVariable('TXT_REQUIRED_FIELDS', $lng->txt('required_field'));
		$tpl->setVariable('TXT_SUBJECT', $lng->txt('forums_thread'));
		$tpl->setVariable('SUBJECT_VALUE', $this->forwardInputToOutput($_POST['formData']['subject']));
		$tpl->setVariable('TXT_MESSAGE', $lng->txt('forums_the_post'));
		$tpl->setVariable('MESSAGE_VALUE', $this->forwardInputToOutput($_POST['formData']['message']));
		if ($this->objProperties->isAnonymized())
		{
			$tpl->setVariable('TXT_ALIAS', $lng->txt('forums_your_name'));
			$tpl->setVariable('ALIAS_VALUE', $_POST['formData']['alias']);
			$tpl->setVariable('TXT_ALIAS_INFO', $lng->txt('forums_use_alias'));
		}		
		
		include_once 'Services/Mail/classes/class.ilMail.php';
		$umail = new ilMail($_SESSION['AccountId']);
		// catch hack attempts
		if ($rbacsystem->checkAccess('mail_visible', $umail->getMailObjectReferenceId()) &&
			!$this->objProperties->isAnonymized())
		{
			$tpl->setCurrentBlock('notify');
			$tpl->setVariable('TXT_NOTIFY', $lng->txt('forum_direct_notification'));
			$tpl->setVariable('NOTIFY', $lng->txt('forum_notify_me_directly'));
			if ($_POST['formData']['notify'] == 1) $tpl->setVariable('NOTIFY_CHECKED', 'checked');
			$tpl->parseCurrentBlock();
			if ($ilias->getSetting('forum_notification') != 0)
			{
				$tpl->setCurrentBlock('notify_posts');
				$tpl->setVariable('TXT_NOTIFY_POSTS', $lng->txt('forum_general_notification'));
				$tpl->setVariable('NOTIFY_POSTS', $lng->txt('forum_notify_me_generally'));
				if ($_POST['formData']['notify_posts'] == 1) $tpl->setVariable('NOTIFY_POSTS_CHECKED', "checked=\"checked\"");
				$tpl->parseCurrentBlock();
			}
		}

		$tpl->setVariable('SUBMIT', $lng->txt('submit'));
		$tpl->setVariable('CANCEL', $lng->txt('cancel'));
		$tpl->setVariable('FORMACTION',	$this->ctrl->getFormAction($this, 'addThread'));
		$tpl->setVariable('TXT_NEW_TOPIC', $lng->txt('forums_new_thread'));
		
		$tpl->setCurrentBlock('attachment');
		$tpl->setVariable('TXT_ATTACHMENTS_ADD', $lng->txt('forums_attachments_add'));
		$tpl->parseCurrentBlock('attachment');
		
		$tpl->parseCurrentBlock('new_thread');
		
		$tpl->setVariable('TPLPATH', $tpl->vars['TPLPATH']);
		
		return true;
	}	
	
	/**
	* Add New Thread.
	*/
	function addThreadObject($a_prevent_redirect = false)
	{
		global $lng, $tpl, $ilDB, $ilUser;
		
		$forumObj = new ilObjForum($_GET['ref_id']);
		$frm =& $forumObj->Forum;
		$frm->setForumId($forumObj->getId());
		$frm->setForumRefId($forumObj->getRefId());

		$frm->setWhereCondition('top_frm_fk = '.$ilDB->quote($frm->getForumId()));
		$topicData = $frm->getOneTopic();

		$formData = $_POST['formData'];
	
		// check form-dates
		$errors = '';

		if (trim($formData['subject']) == '') $errors .= $lng->txt('forums_thread').', ';
		if (trim($formData['message']) == '') $errors .= $lng->txt('forums_the_post').', ';
		if ($errors != '') $errors = substr($errors, 0, strlen($errors) - 2);
		if ($errors != '')
		{
			$this->createThreadObject($errors);
		}
		else
		{			
			// build new thread
			if ($this->objProperties->isAnonymized())
			{			
				$newPost = $frm->generateThread(
								$topicData['top_pk'],
								0,
								$this->handleFormInput($formData['subject']),
								$this->handleFormInput($formData['message']),
								$formData['notify'],
								$formData['notify_posts'],
								ilUtil::stripSlashes($formData['alias'])
				);
			}
			else
			{
				$newPost = $frm->generateThread(
								$topicData['top_pk'],
								$ilUser->getId(),
								$this->handleFormInput($formData['subject']),
								$this->handleFormInput($formData['message']),
								$formData['notify'],
								$formData['notify_posts']
				);
			}
			
			// file upload
			if (isset($_FILES['userfile']))
			{
				$tmp_file_obj =& new ilFileDataForum($forumObj->getId(), $newPost);
				$tmp_file_obj->storeUploadedFile($_FILES['userfile']);
			}
			// Visit-Counter
			$frm->setDbTable('frm_data');
			$frm->setWhereCondition('top_pk = '.$ilDB->quote($topicData['top_pk']));
			$frm->updateVisits($topicData['top_pk']);
			// on success: change location
			$frm->setWhereCondition('thr_top_fk = '.$ilDB->quote($topicData['top_pk']).' AND thr_subject = '.
									$ilDB->quote(ilUtil::stripSlashes($formData['subject'])).' AND thr_num_posts = 1');		
	
			if (!$a_prevent_redirect)
			{
				ilUtil::redirect('repository.php?ref_id='.$forumObj->getRefId());
			}
			else
			{
				return $newPost;
			}
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
	function handleFormInput($a_text)
	{
		$a_text = str_replace("<", "&lt;", $a_text);
		$a_text = str_replace(">", "&gt;", $a_text);
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
	
	function forwardInputToOutput($a_text)
	{
		$a_text = $this->handleFormInput($a_text);
		$a_text = $this->prepareFormOutput($a_text);
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
