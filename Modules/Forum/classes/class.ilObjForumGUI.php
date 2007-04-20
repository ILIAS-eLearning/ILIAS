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

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjForumGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id$
*
* @ilCtrl_Calls ilObjForumGUI: ilPermissionGUI, ilForumExportGUI
* @ilCtrl_Calls ilObjForumGUI: ilColumnGUI
*
* @ingroup ModulesForum
*/
class ilObjForumGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjForumGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		global $ilCtrl;

		// CONTROL OPTIONS
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id","cmdClass"));

		$this->type = "frm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);

		$this->lng->loadLanguageModule('forum');
	}

	/**
	* Execute Command.
	*/
	function &executeCommand()
	{
		global $ilNavigationHistory, $ilAccess;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		if ($cmd != "showExplorer" && $cmd != "viewThread" && $cmd != "showUser"
			&& $cmd != "addThread" && $cmd != "createThread" && $cmd != "showThreadNotification"
			&& $cmd != "enableThreadNotification" && $cmd != "disableThreadNotification")
		{
			$this->prepareOutput();
		}

		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"repository.php?cmd=showThreads&ref_id=".$_GET["ref_id"], "frm");
		}

		switch($next_class)
		{
			case 'ilpermissiongui':
				require_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilforumexportgui':
				require_once("./Modules/Forum/classes/class.ilForumExportGUI.php");
				$fex_gui =& new ilForumExportGUI($this);
				$ret =& $this->ctrl->forwardCommand($fex_gui);
				exit;
				break;

			case "ilcolumngui":
				$this->showThreadsObject();
				break;

			default:
				if(!$cmd)
				{
					$cmd = "showThreads";
				}
				$cmd .= "Object";

				$this->$cmd();
					
				break;
		}

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
		global $rbacsystem,$ilUser, $ilDB;

		$frm =& $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->setForumRefId($this->object->getRefId());
		$frm->setWhereCondition("top_frm_fk = ".$ilDB->quote($frm->getForumId()));

		$topicData = $frm->getOneTopic();
		if(!$topicData['top_num_threads'])
		{
			$this->ctrl->redirect($this, "createThread");
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.forums_threads_liste.html",
			"Modules/Forum");
		if($rbacsystem->checkAccess('edit_post',$this->object->getRefId()))
		{
			$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
			// display button
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",
				$this->ctrl->getLinkTarget($this, "createThread"));
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt('forums_new_thread'));
			$this->tpl->parseCurrentBlock();
		}

		// Button mark all read
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'markAllRead'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('forums_mark_read'));
		$this->tpl->parseCurrentBlock();

		// Enable/Disable forum notification
		if ($this->ilias->getSetting("forum_notification") != 0)
		{
			$this->tpl->setCurrentBlock("btn_cell");
			if ($frm->isForumNotificationEnabled($ilUser->getId()))
			{
				$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'disableForumNotification'));
				$this->tpl->setVariable("BTN_TXT",$this->lng->txt('forums_disable_forum_notification'));
			}
			else
			{
				$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'enableForumNotification'));
				$this->tpl->setVariable("BTN_TXT",$this->lng->txt('forums_enable_forum_notification'));
			}
			$this->tpl->parseCurrentBlock();

			if ($frm->isForumNotificationEnabled($ilUser->getId()))
			{
				$this->tpl->setVariable("TXT_FORUM_NOTIFICATION_ENABLED",$this->lng->txt('forums_forum_notification_enabled'));
			}
		}

		if($topicData)
		{
			// Visit-Counter
			$frm->setDbTable("frm_data");
			$frm->setWhereCondition("top_pk = ".$ilDB->quote($topicData["top_pk"]));
			$frm->updateVisits($topicData["top_pk"]);
	
			// get list of threads
			$frm->setOrderField("thr_date DESC");
			$resThreads = $frm->getThreadList($topicData["top_pk"]);
			$thrNum = $resThreads->numRows();
			
			// check number of threads
			if ($thrNum != $topicData['top_num_threads'])
			{
				$frm->fixThreadNumber($topicData['top_pk'], $thrNum);
			}
			
			$pageHits = $frm->getPageHits();
			$pageHits = $ilUser->getPref('hits_per_page');

			if ($thrNum > 0)
			{
				$z = 0;
		
				// navigation to browse
				if ($thrNum > $pageHits)
				{
					$params = array(
						"ref_id"		=> $_GET["ref_id"]	
						);
			
					if (!$_GET["offset"])
					{
						$Start = 0;
					}
					else
					{
						$Start = $_GET["offset"];
					}
			
					$linkbar = ilUtil::Linkbar(basename($_SERVER["PHP_SELF"]),$thrNum,$pageHits,$Start,$params);
			
					if ($linkbar != "")
					{
						$this->tpl->setVariable("LINKBAR", $linkbar);
					}
				}
		
				// get threads dates
				while ($thrData = $resThreads->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$rowCol = ilUtil::switchColor($z,"tblrow1","tblrow2");
					
					if ($thrNum > $pageHits && $z >= ($Start+$pageHits))
					{
						break;
					}
		
					if (($thrNum > $pageHits && $z >= $Start) || $thrNum <= $pageHits)
					{
						if ($frm->isAnonymized())
						{
							$usr_data = array(
								"usr_id" => 0,
								"login" => $thrData["thr_usr_alias"],
								"firstname" => "",
								"lastname" => "",
								"public_profile" => "n"
							);
						}

						// GET USER DATA, USED FOR IMPORTED USERS
						else
						{
							$usr_data = $frm->getUserData($thrData["thr_usr_id"],$thrData["import_name"]);
						}


						$this->tpl->setCurrentBlock("threads_row");
						$this->tpl->setVariable("ROWCOL", $rowCol);
				
						$thrData["thr_date"] = $frm->convertDate($thrData["thr_date"]);
						$this->tpl->setVariable("DATE",$thrData["thr_date"]);
						//$this->tpl->setVariable("TITLE","<a href=\"forums_frameset.php?thr_pk=".
						//						$thrData["thr_pk"]."&ref_id=".$this->object->getRefId()."\">".
						//						$thrData["thr_subject"]."</a>");
						$this->ctrl->setParameter($this, "thr_pk", $thrData["thr_pk"]);
						$this->tpl->setVariable("TH_TITLE", $thrData["thr_subject"]);
						$this->tpl->setVariable("TH_HREF",
							$this->ctrl->getLinkTarget($this, "showThreadFrameset"));
						if ($this->ilias->getSetting("forum_notification") != 0 &&
							$frm->isThreadNotificationEnabled($ilUser->getId(), $thrData["thr_pk"]))
						{
							$this->tpl->setVariable("NOTIFICATION_ENABLED", $this->lng->txt("forums_notification_enabled"));
						}

						$num_unread = $this->object->getCountUnread($ilUser->getId(),$thrData['thr_pk']);
						$this->tpl->setVariable("NUM_POSTS",$thrData["thr_num_posts"].' ('.$num_unread.')');
						$this->tpl->setVariable("NEW_POSTS",$this->object->getCountNew($ilUser->getId(),$thrData['thr_pk']));
						$this->tpl->setVariable("NUM_VISITS",$thrData["visits"]);	
				
						// get author data

						if ($frm->isAnonymized())
						{
							if ($usr_data["login"] != "")
							{
								$this->tpl->setVariable("AUTHOR",$usr_data["login"]);
							}
							else
							{
								$this->tpl->setVariable("AUTHOR",$this->lng->txt("forums_anonymous"));
							}
							
						}
						else
						{
							if($thrData["thr_usr_id"] && $usr_data["usr_id"] != 0)
							{
								$this->ctrl->setParameter($this, "backurl",
									urlencode("repository.php?ref_id=".$_GET["ref_id"]."&offset=".$Start));
								$this->ctrl->setParameter($this, "user", $usr_data['usr_id']);
								if ($usr_data["public_profile"] == "n")
								{
									$this->tpl->setVariable("AUTHOR",$usr_data["login"]);
								}
								else
								{
									$this->tpl->setVariable("AUTHOR",
										"<a href=\"".
										$this->ctrl->getLinkTarget($this, "showUser").
										"\">".$usr_data["login"]."</a>");
								}
								$this->ctrl->clearParameters($this);
							}
							else
							{
								$this->tpl->setVariable("AUTHOR",$usr_data["login"]);
							}
						}
					
				
						// get last-post data
						$lpCont = "";				
						if ($thrData["thr_last_post"] != "")
						{
							$lastPost = $frm->getLastPost($thrData["thr_last_post"]);
						}
						// TODOOOOOOOOOOOOOOOOOOO
						if ($frm->isAnonymized())
						{
							$last_usr_data = array(
								"usr_id" => 0,
								"login" => $lastPost["pos_usr_alias"],
								"firstname" => "",
								"lastname" => "",
								"public_profile" => "n"
							);
						}
						else
						{
							$last_usr_data = $frm->getUserData($lastPost["pos_usr_id"],$lastPost["import_name"]);
						}
						if (is_array($lastPost))
						{				
							$lastPost["pos_message"] = $frm->prepareText($lastPost["pos_message"]);
							//$lpCont = $lastPost["pos_date"]."<br/>".strtolower($this->lng->txt("from"))."&nbsp;";
							//$lpCont .= "<a href=\"forums_frameset.php?pos_pk=".
							//	$lastPost["pos_pk"]."&thr_pk=".$lastPost["pos_thr_fk"]."&ref_id=".
							//	$this->object->getRefId()."#".$lastPost["pos_pk"]."\">".$last_usr_data["login"]."</a>";
							$this->ctrl->setParameter($this, "thr_pk", $lastPost["pos_thr_fk"]);
							$this->tpl->setCurrentBlock("last_post");
							$this->tpl->setVariable("LP_DATE", $lastPost["pos_date"]);
							$this->tpl->setVariable("LP_FROM", $this->lng->txt("from"));
							$this->tpl->setVariable("LP_HREF",
								$this->ctrl->getLinkTarget($this, "showThreadFrameset")."#".$lastPost["pos_pk"]);
							if ($frm->isAnonymized())
							{
								if ($last_usr_data["login"] != "")
								{
									$this->tpl->setVariable("LP_TITLE",$last_usr_data["login"]);
								}
								else
								{
									$this->tpl->setVariable("LP_TITLE",$this->lng->txt("forums_anonymous"));
								}
							}
							else
							{
								$this->tpl->setVariable("LP_TITLE", $last_usr_data["login"]);
							}
							$this->tpl->parseCurrentBlock();
						}	
				
						$this->tpl->setVariable("FORUM_ID", $thrData["thr_pk"]);		
						$this->tpl->setVariable("THR_TOP_FK", $thrData["thr_top_fk"]);		
				
						$this->tpl->setVariable("TXT_PRINT", $this->lng->txt("print"));
				
						$this->tpl->setVariable("THR_IMGPATH",$this->tpl->tplPath);
				
						$this->tpl->setCurrentBlock("threads_row");
						$this->tpl->parseCurrentBlock();
				
					} // if (($thrNum > $pageHits && $z >= $Start) || $thrNum <= $pageHits)
			
					$z ++;
			
				} // while ($thrData = $resThreads->fetchRow(DB_FETCHMODE_ASSOC))
		
				$this->tpl->setVariable("TXT_SELECT_ALL", $this->lng->txt("select_all"));		
				$this->tpl->setVariable("FORMACTION",
					$this->ctrl->getFormAction($this));
				$this->tpl->setVariable("TXT_OK",$this->lng->txt("ok"));			
				$this->tpl->setVariable("TXT_EXPORT_HTML", $this->lng->txt("export_html"));
				$this->tpl->setVariable("TXT_EXPORT_XML", $this->lng->txt("export_xml"));
				if ($this->ilias->getSetting("forum_notification") != 0 &&
					!$frm->isForumNotificationEnabled($ilUser->getId()))
				{
					$this->tpl->setVariable("TXT_DISABLE_NOTIFICATION", $this->lng->txt("forums_disable_notification"));
					$this->tpl->setVariable("TXT_ENABLE_NOTIFICATION", $this->lng->txt("forums_enable_notification"));
				}
				$this->tpl->setVariable("IMGPATH",$this->tpl->tplPath);
		
			} // if ($thrNum > 0)	
		
		} // if (is_array($topicData = $frm->getOneTopic()))

		$this->tpl->setCurrentBlock("threadtable");
		$this->tpl->setVariable("COUNT_THREAD", $this->lng->txt("forums_count_thr").": ".$thrNum);
		$this->tpl->setVariable("TXT_DATE", $this->lng->txt("date"));
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_TOPIC", $this->lng->txt("forums_thread"));
		$this->tpl->setVariable("TXT_AUTHOR", $this->lng->txt("forums_created_by"));
		$this->tpl->setVariable("TXT_NUM_POSTS", $this->lng->txt("forums_articles").' ('.$this->lng->txt('unread').')');
		$this->tpl->setVariable("TXT_NEW_POSTS",$this->lng->txt('forums_new_articles'));
		$this->tpl->setVariable("TXT_NUM_VISITS", $this->lng->txt("visits"));
		$this->tpl->setVariable("TXT_LAST_POST", $this->lng->txt("forums_last_post"));
		$this->tpl->parseCurrentBlock("threadtable");
		
		$this->tpl->setCurrentBlock("perma_link");
		$this->tpl->setVariable("PERMA_LINK", ILIAS_HTTP_PATH.
			"/goto.php?target=".
			$this->object->getType().
			"_".$this->object->getRefId()."&client_id=".CLIENT_ID);
		$this->tpl->setVariable("TXT_PERMA_LINK", $this->lng->txt("perma_link"));
		$this->tpl->setVariable("PERMA_TARGET", "_top");
		$this->tpl->parseCurrentBlock();
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

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.frm_create.html",
			"Modules/Forum");

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

		$this->tpl->setVariable("CHECK_ANONYMIZED",ilUtil::formCheckbox($anonymized == 1 ? 1 : 0,'anonymized',1));

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
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);

		//$this->ctrl->redirectByClass("ilrepositorygui", "frameset");
		ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$_GET["ref_id"]);

	}

	function updateObject()
	{
		if(strlen($_POST['title']))
		{
			$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$this->object->setDescription(ilUtil::stripSlashes($_POST["desc"]));
			$this->object->setDefaultView((int) $_POST['sort']);
			$this->object->setAnonymized((int) $_POST['anonymized']);
			$this->object->setStatisticsEnabled((int) $_POST['statistics_enabled']);
			$this->object->update();

			ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);

			// REDIRECT (UPDATE TITLE)
			$this->ctrl->redirect($this,'edit');
		}
		// ERROR
		ilUtil::sendInfo($this->lng->txt('frm_title_required'));

		return $this->editObject();
	}

	/**
	* display edit form (usually called by editObject)
	*
	* @access	private
	* @param	array	$fields		key/value pairs of input fields
	*/
	function editObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"],"frm"))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$default_sort = $_POST['sort']
			? $_POST['sort'] 
			: $this->object->getDefaultView();
		$anonymized = $_POST['anonymized']
			? $_POST['anonymized'] 
			: $this->object->isAnonymized();
		$statisticsEnabled  = $_POST['statistics_enabled'] ? 
			$_POST['statistics_enabled'] 
			: $this->object->isStatisticsEnabled();

		
			
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.forum_properties.html",
			"Modules/Forum");

		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_frm.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('edit_properties'));


		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
		$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput(stripslashes($this->object->getTitle())));
		$this->tpl->setVariable("DESC", ilUtil::stripSlashes(stripslashes($this->object->getDescription())));


		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt('edit_properties'));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

		// DEFAULT ORDER
		$this->tpl->setVariable("TXT_VIEW",$this->lng->txt('frm_default_view'));
		$this->tpl->setVariable("TXT_ANSWER",$this->lng->txt('order_by').' '.$this->lng->txt('answers'));
		$this->tpl->setVariable("TXT_DATE",$this->lng->txt('order_by').' '.$this->lng->txt('date'));

		$this->tpl->setVariable("CHECK_ANSWER",ilUtil::formRadioButton($default_sort == 1 ? 1 : 0,'sort',1));
		$this->tpl->setVariable("CHECK_DATE",ilUtil::formRadioButton($default_sort == 2 ? 1 : 0,'sort',2));

		// ANONYMIZED OR NOT
		$this->tpl->setVariable("TXT_ANONYMIZED",$this->lng->txt('frm_anonymous_posting'));
		$this->tpl->setVariable("TXT_ANONYMIZED_DESC",$this->lng->txt('frm_anonymous_posting_desc'));

		$this->tpl->setVariable("CHECK_ANONYMIZED",ilUtil::formCheckbox($anonymized == 1 ? 1 : 0,'anonymized',1));

		// Statistics enabled or not
		$this->tpl->setVariable("TXT_STATISTICS_ENABLED", $this->lng->txt("frm_statistics_enabled"));	
		$this->tpl->setVariable("TXT_STATISTICS_ENABLED_DESC", $this->lng->txt("frm_statistics_enabled_desc"));
		
		$this->tpl->setVariable("CHECK_STATISTICS_ENABLED", 
			ilUtil::formCheckbox(
				$statisticsEnabled == 1 && $this->ilias->getSetting("enable_fora_statistics", true)? 1 : 0,
				'statistics_enabled', 1,
				$this->ilias->getSetting("enable_fora_statistics", true)?false:true));
				

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
	function saveObject()
	{
		global $rbacadmin, $ilDB;


		$_POST['Fobject']['title'] = $_POST['title'];
		$_POST['Fobject']['desc'] = $_POST['desc'];

		// create and insert forum in objecttree
		$forumObj = parent::saveObject();

		// Create settings
		$forumObj->setDefaultView((int) $_POST['sort']);
		$forumObj->setAnonymized((int) $_POST['anonymized']);
		$forumObj->setStatisticsEnabled((int) $_POST['statistics_enabled']);
		$forumObj->createSettings();

		// setup rolefolder & default local roles (moderator)
		$roles = $forumObj->initDefaultRoles();

		// ...finally assign moderator role to creator of forum object
		$rbacadmin->assignUser($roles[0], $forumObj->getOwner(), "n");
		ilObjUser::updateActiveRoles($forumObj->getOwner());
			
		// insert new forum as new topic into frm_data
		$top_data = array(
            "top_frm_fk"   		=> $forumObj->getId(),
			"top_name"   		=> $forumObj->getTitle(),
            "top_description" 	=> $forumObj->getDescription(),
            "top_num_posts"     => 0,
            "top_num_threads"   => 0,
            "top_last_post"     => "",
			"top_mods"      	=> $roles[0],
			"top_usr_id"      	=> $_SESSION["AccountId"],
            "top_date" 			=> date("Y-m-d H:i:s")
        );
	
		$q = "INSERT INTO frm_data ";
		$q .= "(top_frm_fk,top_name,top_description,top_num_posts,top_num_threads,top_last_post,top_mods,top_date,top_usr_id) ";
		$q .= "VALUES ";
		$q .= "(".$ilDB->quote($top_data["top_frm_fk"]).",".$ilDB->quote($top_data["top_name"]).",".$ilDB->quote($top_data["top_description"]).",".
			$ilDB->quote($top_data["top_num_posts"]).",".$ilDB->quote($top_data["top_num_threads"]).",".$ilDB->quote($top_data["top_last_post"]).",".
			$ilDB->quote($top_data["top_mods"]).",'".$top_data["top_date"]."',".$ilDB->quote($top_data["top_usr_id"]).")";
		$this->ilias->db->query($q);

		// always send a message
		ilUtil::sendInfo($this->lng->txt("frm_added"),true);
		
		$this->ctrl->setParameter($this, "ref_id", $forumObj->getRefId());

		ilUtil::redirect($this->ctrl->getLinkTarget($this,'showThreads'));
	}

	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if($rbacsystem->checkAccess('write',$this->ref_id))
		{
			include_once './classes/class.ilRepositoryExplorer.php';

			$tabs_gui->addTarget("forums_threads",ilRepositoryExplorer::buildLinkTarget($this->ref_id,'frm'),
				array("", "showThreads", "view", "markAllRead"), "");
		}
		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$force_active = ($_GET["cmd"] == "edit")
				? true
				: false;
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this),
				"", $force_active);
		}
	
		if ($this->ilias->getSetting("enable_fora_statistics", true) &&
			($this->object->isStatisticsEnabled() || $rbacsystem->checkAccess('write',$this->ref_id))) 
		{
			$tabs_gui->addTarget("statistic", 
				$this->ctrl->getLinkTarget($this, "showStatistics"), "showStatistics", get_class($this),"",false);							
			
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');							
		}
		
		return true;
	}
	
	/**
	 * called from GUI
	 */
	function showStatisticsObject() 
	{
		global $rbacsystem, $ilUser, $ilAccess, $ilDB;
		
		/// if globally deactivated, skip!!! intrusion detected
		if (!$this->ilias->getSetting("enable_fora_statistics", true))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		// if no read access -> intrusion detected
		if (!$rbacsystem->checkAccess("read", $_GET["ref_id"],"frm"))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// if read access and statistics disabled -> intrusion detected 		
		if (!$rbacsystem->checkAccess("read", $_GET["ref_id"],"frm") && !$this->object->isStatisticsEnabled())
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		
		
		$tbl = new ilTableGUI();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.forums_statistics_view.html",
			"Modules/Forum");		
    	$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.table.html");		
		
		
		
		// if write access and statistics disabled -> ok, for forum admin 		
		if ($rbacsystem->checkAccess("write", $_GET["ref_id"],"frm") && !$this->object->isStatisticsEnabled())
		{
			//todo: show message
			$this->tpl->setVariable ("STATUSLINE",$this->lng->txt("frm_statistics_disabled_for_participants")); 
		}
		
		// get sort variables from get vars
		$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order']:"DESC";
		$sort_by  = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'ranking';

		if ($sort_by == "title")
			$sort_by = "ranking";

		
		// create query
		$query = "SELECT COUNT(f.pos_usr_id) as ranking, u.login, u.lastname, u.firstname
                                            FROM frm_posts f, frm_posts_tree t, frm_threads th, usr_data u, frm_data d
                                            WHERE f.pos_pk = t.pos_fk AND t.thr_fk = th.thr_pk AND u.usr_id = f.pos_usr_id AND d.top_pk = f.pos_top_fk AND d.top_frm_fk = ".$ilDB->quote($this->object->getId())."
                                            GROUP BY pos_usr_id ORDER BY $sort_by $sort_order"; 
		                                           
		// get resultset
		$resultset = $this->ilias->db->query ($query);
		
		while ($row = $resultset->fetchRow(DB_FETCHMODE_ASSOC)) {
		    $data [] = $row;
		}
		
	
		// title & header columns
		$tbl->setTitle($this->lng->txt("statistic"),"icon_usr_b.gif",$this->lng->txt("obj_".$this->object->getType()));
				
		$header_names = array ($this->lng->txt("frm_statistics_ranking"),$this->lng->txt("login"), $this->lng->txt("lastname"),$this->lng->txt("firstname"));
			 
		$tbl->setHeaderNames($header_names);

		$header_params = array("ref_id"		=> $this->ref_id, "cmd"			=> "statistic" );
		$header_fields = array("ranking","login","lastname", "firstname");		

		$tbl->setHeaderVars($header_fields,$header_params);
		$tbl->setColumnWidth(array("","25%","25%","25%"));

		// table properties
    	$tbl->enable("hits");
    	$tbl->disable("sort");
		$tbl->setOrderColumn($sort_by);
		$tbl->setOrderDirection($sort_order);
		$tbl->setLimit(0);
		$tbl->setOffset(0);
		$tbl->setData($data);

		$tbl->render();
				
		$this->tpl->parseCurrentBlock();			
			
	}
	


	// PRIVATE
	function __initFileObject()
	{
		include_once "./Modules/Forum/classes/class.ilFileDataImportForum.php";

		$this->file_obj =& new ilFileDataImportForum();

		return true;
	}

	function __initParserObject($a_xml_file)
	{
		include_once "./Modules/Forum/classes/class.ilForumImportParser.php";

		$this->parser_obj =& new ilForumImportParser($a_xml_file,$this->ref_id);

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

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			if ($a_thread != 0)
			{
				$_GET["pos_pk"] = $a_posting;
				$_GET["thr_pk"] = $a_thread;
				$_GET["ref_id"] = $a_target;
				$_GET["cmdClass"] = "ilObjForumGUI";
				$_GET["cmd"] = "showThreadFrameset";
				//include_once("forums_frameset.php");
				include_once("repository.php");
				exit;
			}
			else
			{
				$_GET["ref_id"] = $a_target;
				include_once("repository.php");
				exit;
			}
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			include("repository.php");
			exit;
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);

	}

	/**
	* Output forum frameset.
	*/
	function showThreadFramesetObject()
	{
		global $ilUser, $lng, $ilDB, $ilAccess, $ilNavigationHistory, $ilCtrl;
		
		require_once "./Modules/Forum/classes/class.ilForum.php";
		require_once "./Modules/Forum/classes/class.ilObjForum.php";
		
		$lng->loadLanguageModule("forum");
		
		$forumObj = new ilObjForum($_GET["ref_id"]);
		
		if($_GET['mark_read'])
		{
			$forumObj->markThreadRead($ilUser->getId(),(int) $_GET['thr_pk']);
			ilUtil::sendInfo($lng->txt('forums_thread_marked'),true);
		}
		
		
		// delete post and its sub-posts
		if ($_GET["action"] == "ready_delete" && $_POST["confirm"] != "")
		{
			$frm = new ilForum();
		
			$frm->setForumId($forumObj->getId());
			$frm->setForumRefId($forumObj->getRefId());
		
			$dead_thr = $frm->deletePost($_GET["pos_pk"]);
				
			// if complete thread was deleted ...
			if ($dead_thr == $_GET["thr_pk"])
			{
				$frm->setWhereCondition("top_frm_fk = ".$ilDB->quote($forumObj->getId()));
				$topicData = $frm->getOneTopic();
		
				ilUtil::sendInfo($lng->txt("forums_post_deleted"),true);
				
				if ($topicData["top_num_threads"] > 0)
				{
					$this->ctrl->redirect($this, "showThreads");
				}
				else
				{
					$this->ctrl->redirect($this, "createThread");
				}
			}
			ilUtil::sendInfo($lng->txt("forums_post_deleted"));
		}
		
		
		$session_name = "viewmode_".$forumObj->getId();
		
		if (isset($_GET["viewmode"]))
		{
			$_SESSION[$session_name] = $_GET["viewmode"];
		}
		if(!$_SESSION[$session_name])
		{
			$_SESSION[$session_name] = $forumObj->getDefaultView() == 1 ? 'tree' : 'flat';
		}
		
		if ($_SESSION[$session_name] == "tree")
		{
			include_once("Services/Frameset/classes/class.ilFramesetGUI.php");
			$fs_gui = new ilFramesetGUI();
			$fs_gui->setMainFrameName("content");
			$fs_gui->setSideFrameName("tree");
			$fs_gui->setFramesetTitle($forumObj->getTitle());
		
			if(isset($_GET["target"]))
			{
				$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
				$fs_gui->setSideFrameSource(
					$this->ctrl->getLinkTarget($this, "showExplorer"));
				$this->ctrl->setParameter($this, "pos_pk", $_GET["pos_pk"]);
				$fs_gui->setMainFrameSource(
					$this->ctrl->getLinkTarget($this, "viewThread")."#".$_GET["pos_pk"]);
				//"./forums_threads_view.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]".
				//				"&pos_pk=$_GET[pos_pk]#$_GET[pos_pk]");
			}
			else
			{
				$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
				$fs_gui->setSideFrameSource(
					$this->ctrl->getLinkTarget($this, "showExplorer"));
				$fs_gui->setMainFrameSource(
					$this->ctrl->getLinkTarget($this, "viewThread"));
				//"./forums_threads_view.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
			}
			$fs_gui->show();
			exit();
		}
		else
		{
			if(isset($_GET["target"]))
			{
				$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
				$this->ctrl->setParameter($this, "pos_pk", $_GET["pos_pk"]);
				$this->ctrl->redirect($this, "viewThread", $_GET["pos_pk"]);
				//header("location: forums_threads_view.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]".
				//	   "&pos_pk=$_GET[pos_pk]#$_GET[pos_pk]");
				//exit;
			}
			else
			{
				$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
				$this->ctrl->redirect($this, "viewThread");
				//header("location: forums_threads_view.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
				//exit;
			}
		}
	}

	/**
	* Show Forum Explorer.
	*/
	function showExplorerObject()
	{
		global $tpl, $lng;
		
		require_once "./Modules/Forum/classes/class.ilForumExplorer.php";

		$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
		$tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));
		
		$exp = new ilForumExplorer("./repository.php?cmd=viewThread&cmdClass=ilobjforumgui&thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]",$_GET["thr_pk"],(int) $_GET['ref_id']);
		$exp->setTargetGet("pos_pk");
		
		if ($_GET["fexpand"] == "")
		{
			$forum = new ilForum();
			$tmp_array = $forum->getFirstPostNode($_GET["thr_pk"]);
			$expanded = $tmp_array["id"];
		}
		else
			$expanded = $_GET["fexpand"];
			
		$exp->setExpand($expanded);
		
		//build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();
		
		$tpl->setCurrentBlock("content");
		//$tpl->setVariable("TXT_EXPLORER_HEADER", $lng->txt("forums_posts"));
		$tpl->setVariable("EXP_REFRESH", $lng->txt("refresh"));
		$tpl->setVariable("EXPLORER",$output);
		$this->ctrl->setParameter($this, "fexpand", $_GET["fexpand"]);
		$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
		$tpl->setVariable("ACTION",
			$this->ctrl->getLinkTarget($this, "showExplorer"));
		$tpl->parseCurrentBlock();
		
		$tpl->show(false);
		exit;
	}
	
	
	function prepareThreadScreen($a_forum_obj)
	{
		global $tpl, $lng, $ilTabs, $ilias, $ilUser;
		
		$session_name = "viewmode_".$a_forum_obj->getId();
		$t_frame = ilFrameTargetInfo::_getFrame("MainContent");

		$tpl->getStandardTemplate();
		ilUtil::sendInfo();
		ilUtil::infoPanel();
		
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_frm_b.gif"));

		$ilTabs->setBackTarget($lng->txt("all_topics"),
			"repository.php?ref_id=$_GET[ref_id]",
			$t_frame);
	
		// by answer view
		$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
		$this->ctrl->setParameter($this, "viewmode", "tree");
		$ilTabs->addTarget("order_by_answers",
			$this->ctrl->getLinkTarget($this, "showThreadFrameset"),
			"","", $t_frame);
	
		// by date view
		$this->ctrl->setParameter($this, "viewmode", "flat");
		$ilTabs->addTarget("order_by_date",
			$this->ctrl->getLinkTarget($this, "showThreadFrameset"),
			"","", $t_frame);
		$this->ctrl->clearParameters($this);
	
		if (!isset($_SESSION[$session_name]) or $_SESSION[$session_name] == "flat")
		{
			$ilTabs->setTabActive("order_by_date");
		}
		else
		{
			$ilTabs->setTabActive("order_by_answers");
		}
	
		$frm =& $a_forum_obj->Forum;
		$frm->setForumId($a_forum_obj->getId());
		
		if ($ilias->getSetting("forum_notification") != 0 &&
			!$frm->isForumNotificationEnabled($ilUser->getId()))
		{
			$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
			$ilTabs->addTarget("forums_notification",
				$this->ctrl->getLinkTarget($this, "showThreadNotification"),
				"","");
			$this->ctrl->clearParameters($this);
		}
	}
	
	/**
	* View single thread.
	*/
	function viewThreadObject()
	{
		global $ilias, $tpl, $lng, $ilUser, $ilAccess, $ilTabs, $rbacsystem,
			$rbacreview, $ilDB, $ilNavigationHistory, $ilCtrl;
		
		require_once "./Modules/Forum/classes/class.ilObjForum.php";
		require_once "./Modules/Forum/classes/class.ilFileDataForum.php";
		
		$lng->loadLanguageModule("forum");
		
		
		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilCtrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
			$ilNavigationHistory->addItem($_GET["ref_id"],
				$ilCtrl->getLinkTarget($this, "showThreadFrameset"), "frm");
		}
		
		$forumObj = new ilObjForum($_GET["ref_id"]);
		$frm =& $forumObj->Forum;
		
		// SAVE LAST ACCESS
		$forumObj->updateLastAccess($ilUser->getId(),(int) $_GET['thr_pk']);
		
		// mark post read if explorer link was clicked
		if($_GET['thr_pk'] and $_GET['pos_pk'])
		{
			$forumObj->markPostRead($ilUser->getId(),(int) $_GET['thr_pk'],(int) $_GET['pos_pk']);
		}
		$file_obj =& new ilFileDataForum($forumObj->getId(),$_GET["pos_pk"]);
		
		$frm->setForumId($forumObj->getId());
		$frm->setForumRefId($forumObj->getRefId());
				
		if (!$ilAccess->checkAccess("read,visible", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}
		
		$this->prepareThreadScreen($forumObj);
		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.forums_threads_view.html",
			"Modules/Forum");

		$formData = $_POST["formData"];
		// form processing (edit & reply)
		if ($_GET["action"] == "ready_showreply" || $_GET["action"] == "ready_showedit")
		{
			// check form-dates
			$errors = "";

			if (trim($formData["message"]) == "") $errors .= $lng->txt("forums_the_post").", ";
			if ($errors != "") $errors = substr($errors, 0, strlen($errors)-2);

			if ($errors != "")
			{
				ilUtil::sendInfo($lng->txt("form_empty_fields")." ".$errors);
				$_GET["action"] = substr($_GET["action"], 6);
				$_GET["show_post"] = 1;
			}
		}

		// UPLOAD FILE
		// DELETE FILE
		if(isset($_POST["cmd"]["delete_file"]))
		{
			$file_obj->unlinkFiles($_POST["del_file"]);
			ilUtil::sendInfo("File deleted");
		}
		// DOWNLOAD FILE
		if($_GET["file"])
		{
			if(!$path = $file_obj->getAbsolutePath(urldecode($_GET["file"])))
			{
				ilUtil::sendInfo("Error reading file!");
			}
			else
			{
				ilUtil::deliverFile($path,urldecode($_GET["file"]));
			}
		}
		

		$session_name = "viewmode_".$forumObj->getId();
		if($_SESSION[$session_name] == 'flat')
		{
			$new_order = "answers";
			$orderField = "frm_posts_tree.date";
		}
		else
		{
			$new_order = "date";
			$orderField = "frm_posts_tree.rgt";
		}
				
		// get forum- and thread-data
		$frm->setWhereCondition("top_frm_fk = ".$ilDB->quote($frm->getForumId()));
		
		if (is_array($topicData = $frm->getOneTopic()))
		{
			$frm->setWhereCondition("thr_pk = ".$ilDB->quote($_GET["thr_pk"]));
			$threadData = $frm->getOneThread();

			$tpl->setTitle($lng->txt("forums_thread")." \"".$threadData["thr_subject"]."\"");
			
			// Visit-Counter
			$frm->setDbTable("frm_threads");
			$frm->setWhereCondition("thr_pk = ".$ilDB->quote($_GET["thr_pk"]));
			$frm->updateVisits($_GET["thr_pk"]);
		
			// ********************************************************************************
			// build location-links
			include_once("./Modules/Forum/classes/class.ilForumLocatorGUI.php");
			$frm_loc =& new ilForumLocatorGUI();
			$frm_loc->setRefId($_GET["ref_id"]);
			$frm_loc->setForum($frm);
			$frm_loc->setThread($_GET["thr_pk"], $threadData["thr_subject"]);
			$frm_loc->display();
																		 
			// set tabs
			// display different buttons depending on viewmode
		
			$session_name = "viewmode_".$forumObj->getId();
			$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
					
			// menu template (contains linkbar, new topic and print thread button)
			$menutpl =& new ilTemplate("tpl.forums_threads_menu.html", true, true,
				"Modules/Forum");
		
			if($forumObj->getCountUnread($ilUser->getId(),(int) $_GET['thr_pk']))
			{
				$menutpl->setCurrentBlock("btn_cell");
				$this->ctrl->setParameter($this, "mark_read", "1");
				$this->ctrl->setParameter($this, "thr_pk", $_GET['thr_pk']);
				//$menutpl->setVariable("BTN_LINK",
				//	"forums_frameset.php?mark_read=1&ref_id=".$_GET["ref_id"]."&thr_pk=".$_GET['thr_pk']);
				$menutpl->setVariable("BTN_LINK",
					$this->ctrl->getLinkTarget($this, "showThreadFrameset"));
				$this->ctrl->clearParameters($this);
				$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
				$menutpl->setVariable("BTN_TARGET","target=\"$t_frame\"");
				$menutpl->setVariable("BTN_TXT", $lng->txt("forums_mark_read"));
				$menutpl->parseCurrentBlock();
			}

			// print thread
			$menutpl->setCurrentBlock("btn_cell");
			$this->ctrl->setParameterByClass("ilforumexportgui", "print_thread", $_GET["thr_pk"]);
			$this->ctrl->setParameterByClass("ilforumexportgui", "thr_top_fk", $threadData["thr_top_fk"]);
			$menutpl->setVariable("BTN_LINK",
				$this->ctrl->getLinkTargetByClass("ilforumexportgui", "printThread"));
			$menutpl->setVariable("BTN_TARGET","target=\"_new\"");
			$menutpl->setVariable("BTN_TXT", $lng->txt("forums_print_thread"));
			$menutpl->parseCurrentBlock();
		
			// ********************************************************************************
		
			// form processing (edit & reply)
			if ($_GET["action"] == "ready_showreply" || $_GET["action"] == "ready_showedit" || $_GET["action"] == "ready_censor")
			{
				if ($_GET["action"] != "ready_censor")
				{
					$_GET["show_post"] = 0;
						
					// Generating new posting
					if ($_GET["action"] == "ready_showreply")
					{
						// reply: new post
		//echo "<br>1:".htmlentities($formData["message"]);
						$newPost = $frm->generatePost($topicData["top_pk"], $_GET["thr_pk"],
													  ($frm->isAnonymized() ? 0 : $_SESSION["AccountId"]), ilUtil::stripSlashes($formData["message"]),
													  $_GET["pos_pk"],$_POST["notify"],
													  $formData["subject"]
														? ilUtil::stripSlashes($formData["subject"])
														: $threadData["thr_subject"],
													  ilUtil::stripSlashes($formData["alias"]));
							
						ilUtil::sendInfo($lng->txt("forums_post_new_entry"));
						if(isset($_FILES["userfile"]))
						{
							$tmp_file_obj =& new ilFileDataForum($forumObj->getId(),$newPost);
							$tmp_file_obj->storeUploadedFile($_FILES["userfile"]);
						}
		
					}
					else
					{
						// edit: update post
						if ($frm->updatePost(
								ilUtil::stripSlashes($formData["message"]),
								$_GET["pos_pk"],
								$_POST["notify"],
								$formData["subject"] ? ilUtil::stripSlashes($formData["subject"]) : $threadData["thr_subject"]))
						{
							ilUtil::sendInfo($lng->txt("forums_post_modified"));
						}
						if(isset($_FILES["userfile"]))
						{
							$file_obj->storeUploadedFile($_FILES["userfile"]);
						}
					}
		
					if ($_SESSION["viewmode_".$forumObj->getId()] == "tree")
					{
						$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
						$tpl->setVariable("JAVASCRIPT",
							$this->ctrl->getLinkTarget($this, "showExplorer"));
						$this->ctrl->clearParameters($this);
					}

				} // if ($_GET["cmd"] != "ready_censor")
				// insert censorship
				elseif ($_POST["confirm"] != "" && $_GET["action"] == "ready_censor")
				{
					$frm->postCensorship($formData["cens_message"], $_GET["pos_pk"],1);
				}
				elseif ($_POST["cancel"] != "" && $_GET["action"] == "ready_censor")
				{
					$frm->postCensorship($formData["cens_message"], $_GET["pos_pk"]);
				}
			}
		
			// get first post of thread
			$first_node = $frm->getFirstPostNode($_GET["thr_pk"]);
		
			// get complete tree of thread
			$frm->setOrderField($orderField);
		//echo "orderField:$orderField:<br>";
		
			$subtree_nodes = $frm->getPostTree($first_node);
		
			$posNum = count($subtree_nodes);
		
			$pageHits = $frm->getPageHits();
		
			$z = 0;
		
			// navigation to browse
			if ($posNum > $pageHits)
			{
				$params = array(
					"ref_id"		=> $_GET["ref_id"],
					"thr_pk"		=> $_GET["thr_pk"],
					"orderby"		=> $_GET["orderby"]
				);
		
				if (!$_GET["offset"])
				{
					$Start = 0;
				}
				else
				{
					$Start = $_GET["offset"];
				}
		
				$linkbar = ilUtil::Linkbar($ilCtrl->getLinkTarget($this, "viewThread"),
					$posNum,$pageHits,$Start,$params);
		//echo ":$linkbar:";
				if ($linkbar != "")
				{
					$menutpl->setCurrentBlock("linkbar");
					$menutpl->setVariable("LINKBAR", $linkbar);
					$menutpl->parseCurrentBlock();
				}
			}
		
			$menutpl->setCurrentBlock("btn_row");
			$menutpl->parseCurrentBlock();
			$tpl->setVariable("THREAD_MENU", $menutpl->get());
		
		
			// assistance val for anchor-links
			$jump = 0;

			// generate post-dates
			foreach ($subtree_nodes as $node)
			{
		//echo ":".$frm->convertDate($node["create_date"]).":<br>";
				if ($_GET["pos_pk"] && $_GET["pos_pk"] == $node["pos_pk"])
				{
					$jump ++;
				}
		
				if ($posNum > $pageHits && $z >= ($Start+$pageHits))
				{
					// if anchor-link was not found ...
					if ($_GET["pos_pk"] && $jump < 1)
					{
						$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
						$this->ctrl->setParameter($this, "pos_pk", $_GET["pos_pk"]);
						$this->ctrl->setParameter($this, "offset", ($Start+$pageHits));
						$this->ctrl->setParameter($this, "orderby", $_GET["orderby"]);
						$this->ctrl->redirect($this, "viewThread", $_GET["pos_pk"]);
						//header("location: forums_threads_view.php?thr_pk=".$_GET["thr_pk"]."&ref_id=".
						//	   $_GET["ref_id"]."&pos_pk=".$_GET["pos_pk"]."&offset=".($Start+$pageHits)."&orderby=".$_GET["orderby"]);
						exit();
					}
					else
					{
						break;
					}
				}
		
				if (($posNum > $pageHits && $z >= $Start) || $posNum <= $pageHits)
				{
					if ($rbacsystem->checkAccess("edit_post", $_GET["ref_id"]))
					{
						// reply/edit
						if (($_GET["action"] == "showreply" || $_GET["action"] == "showedit") && $_GET["pos_pk"] == $node["pos_pk"])
						{
							// EDIT ATTACHMENTS
							if (count($file_obj->getFilesOfPost()) && $_GET["action"] == "showedit")
							{
								foreach ($file_obj->getFilesOfPost() as $file)
								{
									$tpl->setCurrentBlock("ATTACHMENT_EDIT_ROW");
									$tpl->setVariable("FILENAME",$file["name"]);
									$tpl->setVariable("CHECK_FILE",ilUtil::formCheckbox(0,"del_file[]",$file["name"]));
									$tpl->parseCurrentBlock();
								}
		
								$tpl->setCurrentBlock("reply_attachment_edit");
								//$tpl->setVariable("FILE_DELETE_ACTION",
								//	"forums_threads_view.php?ref_id=$_GET[ref_id]&cmd=showedit".
								//	"&pos_pk=$_GET[pos_pk]&thr_pk=$_GET[thr_pk]");
								$tpl->setVariable("TXT_ATTACHMENTS_EDIT",$lng->txt("forums_attachments_edit"));
								$tpl->setVariable("ATTACHMENT_EDIT_DELETE",$lng->txt("forums_delete_file"));
								$tpl->parseCurrentBlock();
							}
		
							// ADD ATTACHMENTS
							$tpl->setCurrentBlock("reply_attachment");
							$tpl->setVariable("TXT_ATTACHMENTS_ADD",$lng->txt("forums_attachments_add"));
							#						$tpl->setVariable("UPLOAD_ACTION","forums_threads_view.php?ref_id=$_GET[ref_id]&cmd=showedit".
							#										  "&pos_pk=$_GET[pos_pk]&thr_pk=$_GET[thr_pk]");
							$tpl->setVariable("BUTTON_UPLOAD",$lng->txt("upload"));
							$tpl->parseCurrentBlock();
							$tpl->setCurrentBlock("reply_post");
							$tpl->setVariable("REPLY_ANKER", $_GET["pos_pk"]);
		
							if ($frm->isAnonymized() && $_GET["action"] == "showreply")
							{
								$tpl->setVariable("TXT_FORM_ALIAS",$lng->txt("forums_your_name"));
								$tpl->setVariable("TXT_ALIAS_INFO",$lng->txt("forums_use_alias"));
							}

							$tpl->setVariable("TXT_FORM_SUBJECT",$lng->txt("forums_subject"));
							if ($_GET["action"] == "showreply")
							{
								$tpl->setVariable("TXT_FORM_MESSAGE", $lng->txt("forums_your_reply"));
							}
							else
							{
								$tpl->setVariable("TXT_FORM_MESSAGE", $lng->txt("forums_edit_post"));
							}
		
							if ($_GET["action"] == "showreply")
							{
								if ($frm->isAnonymized())
								{
									$tpl->setVariable("ALIAS_VALUE",
										($_GET["show_post"] == 1 ?
											ilUtil::prepareFormOutput(stripslashes($_POST["formData"]["alias"])) :
											""));
								}
								$tpl->setVariable("SUBJECT_VALUE",
									($_GET["show_post"] == 1 ?
										ilUtil::prepareFormOutput(stripslashes($_POST["formData"]["subject"])) :
										ilUtil::prepareFormOutput(stripslashes($threadData["thr_subject"]))));
								$tpl->setVariable("MESSAGE_VALUE",
									($_GET["show_post"] == 1 ?
										ilUtil::prepareFormOutput(stripslashes($_POST["formData"]["message"])) :
										$frm->prepareText($node["message"],1)));
							}
							else
							{
								$tpl->setVariable("SUBJECT_VALUE",
									($_GET["show_post"] == 1 ?
										ilUtil::prepareFormOutput(stripslashes($_POST["formData"]["subject"])) :
										ilUtil::prepareFormOutput(stripslashes($node["subject"]))));
								$tpl->setVariable("MESSAGE_VALUE",
									($_GET["show_post"] == 1 ?
										ilUtil::prepareFormOutput(stripslashes($_POST["formData"]["message"])) :
										$frm->prepareText($node["message"],2)));
							}
							// NOTIFY
							include_once 'Services/Mail/classes/class.ilMail.php';
							$umail = new ilMail($_SESSION["AccountId"]);

							if ($rbacsystem->checkAccess("mail_visible",$umail->getMailObjectReferenceId()))
							{
								global $ilUser;
								
								// only if gen. notification is disabled and forum isn't anonymous
								if (!$frm->isThreadNotificationEnabled($ilUser->getId(), $_GET["thr_pk"]) &&
									!$frm->isAnonymized())
								{
									$tpl->setCurrentBlock("notify");
									$tpl->setVariable("NOTIFY",$lng->txt("forum_notify_me"));
									if ($_GET["show_post"] == 1) $tpl->setVariable("NOTIFY_CHECKED",$_POST["notify"] ? "checked=\"checked\"" : "");
									else $tpl->setVariable("NOTIFY_CHECKED",$node["notify"] ? "checked=\"checked\"" : "");
									$tpl->parseCurrentBlock();
								}
							}
		
/*							if ($frm->isAnonymized())
							{
								$tpl->setCurrentBlock("anonymize");
								$tpl->setVariable("TXT_ANONYMIZE",$lng->txt("forum_anonymize"));
								$tpl->setVariable("ANONYMIZE",$lng->txt("forum_anonymize_desc"));
								$tpl->parseCurrentBlock();
							}*/

							$tpl->setVariable("SUBMIT", $lng->txt("submit"));
							$tpl->setVariable("RESET", $lng->txt("reset"));
							$this->ctrl->setParameter($this, "action", "ready_".$_GET["action"]);
							$this->ctrl->setParameter($this, "pos_pk", $_GET["pos_pk"]);
							$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
							$this->ctrl->setParameter($this, "offset", $Start);
							$this->ctrl->setParameter($this, "orderby", $_GET["orderby"]);
							$tpl->setVariable("FORMACTION",
								$this->ctrl->getLinkTarget($this, "viewThread"));
								//basename($_SERVER["PHP_SELF"])."?action=ready_".$_GET["action"]."&ref_id=".
								//$_GET["ref_id"]."&pos_pk=".$_GET["pos_pk"]."&thr_pk=".$_GET["thr_pk"].
								//"&offset=".$Start."&orderby=".$_GET["orderby"]);
							$this->ctrl->clearParameters($this);
							$tpl->parseCurrentBlock("reply_post");
		
						} // if (($_GET["cmd"] == "showreply" || $_GET["cmd"] == "showedit") && $_GET["pos_pk"] == $node["pos_pk"])
						else
						{
							// button: delete article
							if ($rbacsystem->checkAccess("delete_post", $_GET["ref_id"]))
							{
								// 2. delete-level
								if ($_GET["action"] == "delete" && $_GET["pos_pk"] == $node["pos_pk"])
								{
									$tpl->setCurrentBlock("kill_cell");
									$tpl->setVariable("KILL_ANKER", $_GET["pos_pk"]);
									$tpl->setVariable("KILL_SPACER","<hr noshade=\"noshade\" width=\"100%\" size=\"1\" align=\"center\">");
									$tpl->setVariable("TXT_KILL", $lng->txt("forums_info_delete_post"));
		//							$tpl->setVariable("DEL_FORMACTION", basename($_SERVER["PHP_SELF"])."?cmd=ready_delete&ref_id=".$_GET["ref_id"]."&pos_pk=".$node["pos_pk"]."&thr_pk=".$_GET["thr_pk"]."&offset=".$Start."&orderby=".$_GET["orderby"]);
									$this->ctrl->setParameter($this, "action", "ready_delete");
									$this->ctrl->setParameter($this, "pos_pk", $node["pos_pk"]);
									$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
									$this->ctrl->setParameter($this, "offset", $Start);
									$this->ctrl->setParameter($this, "orderby", $_GET["orderby"]);
									$tpl->setVariable("DEL_FORMACTION",
										$this->ctrl->getLinkTarget($this, "showThreadFrameset"));
									//"forums_frameset.php?action=ready_delete&ref_id=".
									//				  $_GET["ref_id"]."&pos_pk=".$node["pos_pk"]."&thr_pk=".$_GET["thr_pk"].
									//				  "&offset=".$Start."&orderby=".$_GET["orderby"]);
									$this->ctrl->clearParameters($this);
									$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
									//$t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", "frm");
									$tpl->setVariable("DEL_FORM_TARGET", $t_frame);
									$tpl->setVariable("CANCEL_BUTTON", $lng->txt("cancel"));
									$tpl->setVariable("CONFIRM_BUTTON", $lng->txt("confirm"));
									$tpl->parseCurrentBlock("kill_cell");
								}
								else
								{
									// 1. delete-level
									if ($_GET["action"] != "censor" || $_GET["pos_pk"] != $node["pos_pk"])
									{
										$tpl->setCurrentBlock("del_cell");
										
										$this->ctrl->setParameter($this, "action", "delete");
										$this->ctrl->setParameter($this, "pos_pk", $node["pos_pk"]);
										$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
										$this->ctrl->setParameter($this, "offset", $Start);
										$this->ctrl->setParameter($this, "orderby", $_GET["orderby"]);

										$tpl->setVariable("DEL_LINK",
											$this->ctrl->getLinkTarget($this, "viewThread", $node["pos_pk"]));
										//"forums_threads_view.php?action=delete&pos_pk=".
										//$node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".$Start.
										//"&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".
										//$node["pos_pk"]);
										$this->ctrl->clearParameters($this);
										$tpl->setVariable("DEL_BUTTON", $lng->txt("delete"));
										$tpl->parseCurrentBlock("del_cell");
									}
								}
		
								// censorship
								// 2. cens formular
								if ($_GET["action"] == "censor" && $_GET["pos_pk"] == $node["pos_pk"])
								{
									$tpl->setCurrentBlock("censorship_cell");
									$tpl->setVariable("CENS_ANKER", $_GET["pos_pk"]);
									$tpl->setVariable("CENS_SPACER","<hr noshade=\"noshade\" width=\"100%\" size=\"1\" align=\"center\">");
									$this->ctrl->setParameter($this, "action", "ready_censor");
									$this->ctrl->setParameter($this, "pos_pk", $node["pos_pk"]);
									$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
									$this->ctrl->setParameter($this, "offset", $Start);
									$this->ctrl->setParameter($this, "orderby", $_GET["orderby"]);

									$tpl->setVariable("CENS_FORMACTION",
										$this->ctrl->getLinkTarget($this, "viewThread"));
									//basename($_SERVER["PHP_SELF"])."?action=ready_censor&ref_id=".
									//				  $_GET["ref_id"]."&pos_pk=".$node["pos_pk"]."&thr_pk=".$_GET["thr_pk"].
									//				  "&offset=".$Start."&orderby=".$_GET["orderby"]);
									$this->ctrl->clearParameters($this);
									$tpl->setVariable("TXT_CENS_MESSAGE", $lng->txt("forums_the_post"));
									$tpl->setVariable("TXT_CENS_COMMENT", $lng->txt("forums_censor_comment").":");
									$tpl->setVariable("CENS_MESSAGE", $frm->prepareText($node["pos_cens_com"],2));
									$tpl->setVariable("CANCEL_BUTTON", $lng->txt("cancel"));
									$tpl->setVariable("CONFIRM_BUTTON", $lng->txt("confirm"));
		
									if ($node["pos_cens"] == 1)
									{
										$tpl->setVariable("TXT_CENS", $lng->txt("forums_info_censor2_post"));
										$tpl->setVariable("CANCEL_BUTTON", $lng->txt("yes"));
										$tpl->setVariable("CONFIRM_BUTTON", $lng->txt("no"));
									}
									else
										$tpl->setVariable("TXT_CENS", $lng->txt("forums_info_censor_post"));
		
									$tpl->parseCurrentBlock("censorship_cell");
								}
								elseif (($_GET["action"] == "delete" && $_GET["pos_pk"] != $node["pos_pk"]) || $_GET["action"] != "delete")
								{
									// 1. cens button
									$tpl->setCurrentBlock("cens_cell");
									$this->ctrl->setParameter($this, "action", "censor");
									$this->ctrl->setParameter($this, "pos_pk", $node["pos_pk"]);
									$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
									$this->ctrl->setParameter($this, "offset", $Start);
									$this->ctrl->setParameter($this, "orderby", $_GET["orderby"]);
									
									$tpl->setVariable("CENS_LINK",
										$this->ctrl->getLinkTarget($this, "viewThread", $node["pos_pk"]));
									//"forums_threads_view.php?action=censor&pos_pk=".
									//	$node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".
									//	$Start."&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".
									//	$node["pos_pk"]);
									$this->ctrl->clearParameters($this);
									$tpl->setVariable("CENS_BUTTON", $lng->txt("censorship"));
									$tpl->parseCurrentBlock("cens_cell");
								}
								// READ LINK
								if(!$forumObj->isRead($ilUser->getId(),$node['pos_pk']))
								{
									$tpl->setCurrentBlock("read_cell");
									$this->ctrl->setParameter($this, "pos_pk", $node["pos_pk"]);
									$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
									$this->ctrl->setParameter($this, "offset", $Start);
									$this->ctrl->setParameter($this, "orderby", $_GET["orderby"]);
									$tpl->setVariable("READ_LINK",
										$this->ctrl->getLinkTarget($this, "viewThread", $node["pos_pk"]));
									//"forums_threads_view.php?pos_pk=".
									//				  $node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".
									//				  $Start."&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".
									//				  $node["pos_pk"]);
									$tpl->setVariable("READ_BUTTON", $lng->txt("is_read"));
									$this->ctrl->clearParameters($this);
									$tpl->parseCurrentBlock();
								}
		
							} // if ($rbacsystem->checkAccess("delete post", $_GET["ref_id"]))
		
							if (($_GET["action"] != "delete") || ($_GET["action"] == "delete" && $_GET["pos_pk"] != $node["pos_pk"]))
							{
								if ($_GET["action"] != "censor" || $_GET["pos_pk"] != $node["pos_pk"])
								{
									// button: edit article
									if ($frm->checkEditRight($node["pos_pk"]) && $node["pos_cens"] != 1)
									{
										$tpl->setCurrentBlock("edit_cell");
										$this->ctrl->setParameter($this, "action", "showedit");
										$this->ctrl->setParameter($this, "pos_pk", $node["pos_pk"]);
										$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
										$this->ctrl->setParameter($this, "offset", $Start);
										$this->ctrl->setParameter($this, "orderby", $_GET["orderby"]);
										$tpl->setVariable("EDIT_LINK",
											$this->ctrl->getLinkTarget($this, "viewThread", $node["pos_pk"]));
										//"forums_threads_view.php?action=showedit&pos_pk=".
										//				$node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".$Start."&orderby=".
										//				$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".$node["pos_pk"]);
										$tpl->setVariable("EDIT_BUTTON", $lng->txt("edit"));
										$this->ctrl->clearParameters($this);
										$tpl->parseCurrentBlock("edit_cell");
									}
		
									if ($node["pos_cens"] != 1)
									{
										// button: print
										$tpl->setCurrentBlock("print_cell");
										$this->ctrl->setParameterByClass("ilforumexportgui", "print_post", $node["pos_pk"]);
										$this->ctrl->setParameterByClass("ilforumexportgui", "top_pk", $topicData["top_pk"]);
										$this->ctrl->setParameterByClass("ilforumexportgui", "thr_pk", $threadData["thr_pk"]);
										$tpl->setVariable("PRINT_LINK",
											$this->ctrl->getLinkTargetByClass("ilforumexportgui", "printPost"));
										//"forums_export.php?&print_post=".
										//	$node["pos_pk"]."&top_pk=".$topicData["top_pk"]."&thr_pk=".
										//	$threadData["thr_pk"]);
										$tpl->setVariable("PRINT_BUTTON", $lng->txt("print"));
										$tpl->parseCurrentBlock("print_cell");
									}
									if ($node["pos_cens"] != 1)
									{
									// button: reply
									$tpl->setCurrentBlock("reply_cell");
									$this->ctrl->setParameter($this, "action", "showreply");
									$this->ctrl->setParameter($this, "pos_pk", $node["pos_pk"]);
									$this->ctrl->setParameter($this, "offset", $Start);
									$this->ctrl->setParameter($this, "orderby", $_GET["orderby"]);
									$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
									$tpl->setVariable("REPLY_LINK",
										$this->ctrl->getLinkTarget($this, "viewThread", $node["pos_pk"]));
									//"forums_threads_view.php?cmd=showreply&pos_pk=".
									//$node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".$Start."&orderby=".
									//$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".$node["pos_pk"]);
									$tpl->setVariable("REPLY_BUTTON", $lng->txt("reply"));
									$tpl->parseCurrentBlock("reply_cell");
									$this->ctrl->clearParameters($this);
									}
								}
		
								$tpl->setVariable("POST_ANKER", $node["pos_pk"]);
		
							} // if (($_GET["cmd"] != "delete") || ($_GET["cmd"] == "delete" && $_GET["pos_pk"] != $node["pos_pk"]))
		
							$tpl->setVariable("SPACER","<hr noshade=\"noshade\" width=\"100%\" size=\"1\" align=\"center\">");
		
						} // else
		
					} // if ($rbacsystem->checkAccess("write", $_GET["ref_id"]))
					else
					{
						if(!$forumObj->isRead($ilUser->getId(),$node['pos_pk']))
						{
							$tpl->setCurrentBlock("read_cell");
							$this->ctrl->setParameter($this, "pos_pk", $node["pos_pk"]);
							$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
							$this->ctrl->setParameter($this, "offset", $Start);
							$this->ctrl->setParameter($this, "orderby", $_GET["orderby"]);
							$tpl->setVariable("READ_LINK",
								$this->ctrl->getLinkTarget($this, "viewThread", $node["pos_pk"]));
							$tpl->setVariable("READ_BUTTON", $lng->txt("is_read"));
							$this->ctrl->clearParameters($this);
							$tpl->parseCurrentBlock();
						}
		
						$tpl->setVariable("POST_ANKER", $node["pos_pk"]);
					}
					// DOWNLOAD ATTACHMENTS
					$tmp_file_obj =& new ilFileDataForum($forumObj->getId(),$node["pos_pk"]);
					if(count($tmp_file_obj->getFilesOfPost()))
					{
						if($node["pos_pk"] != $_GET["pos_pk"] || $_GET["action"] != "showedit")
						{
							foreach($tmp_file_obj->getFilesOfPost() as $file)
							{
								$tpl->setCurrentBlock("attachment_download_row");
								$this->ctrl->setParameter($this, "pos_pk", $node["pos_pk"]);
								$this->ctrl->setParameter($this, "file", urlencode($file["name"]));
								$tpl->setVariable("HREF_DOWNLOAD",
									$this->ctrl->getLinkTarget($this, "viewThread"));
								//"forums_threads_view.php?ref_id=$_GET[ref_id]&pos_pk=$node[pos_pk]&file=".
								//		urlencode($file["name"]));
								$tpl->setVariable("TXT_FILENAME", $file["name"]);
								$this->ctrl->clearParameters($this);
								$tpl->parseCurrentBlock();
							}
							$tpl->setCurrentBlock("attachments");
							$tpl->setVariable("TXT_ATTACHMENTS_DOWNLOAD",$lng->txt("forums_attachments"));
							$tpl->setVariable("DOWNLOAD_IMG",
								ilUtil::getImagePath("icon_attachment.gif"));
							$tpl->setVariable("TXT_DOWNLOAD_ATTACHMENT",
								$lng->txt("forums_download_attachment"));
							$tpl->parseCurrentBlock();
						}
					}
		
					$tpl->setCurrentBlock("posts_row");
					$rowCol = ilUtil::switchColor($z,"tblrow1","tblrow2");
					if (($_GET["action"] != "delete" && $_GET["action"] != "censor") || $_GET["pos_pk"] != $node["pos_pk"])
					{
						$tpl->setVariable("ROWCOL", $rowCol);
					}
					else
					{
						$tpl->setVariable("ROWCOL", "tblrowmarked");
					}
		
					// get author data
					unset($author);
					if (ilObject::_exists($node["author"]))
					{
						$author = $frm->getUser($node["author"]);
					}
					else
					{
						unset($node["author"]);
					}
		
					if ($frm->isAnonymized())
					{
						$usr_data = array(
							"usr_id" => 0,
							"login" => $node["alias"],
							"public_profile" => "n"
						);
					}

					// GET USER DATA, USED FOR IMPORTED USERS
					else
					{					
						$usr_data = $frm->getUserData($node["author"],$node["import_name"]);
					}
		
					$this->ctrl->setParameter($this, "pos_pk", $node["pos_pk"]);
					$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
					$backurl = urlencode(
						$this->ctrl->getLinkTarget($this, "viewThread", $node["pos_pk"]));
					//"forums_threads_view.php?ref_id=".$_GET["ref_id"].
					//		 "&thr_pk=".$_GET["thr_pk"].
					//		 "&pos_pk=".$node["pos_pk"]."#".$node["pos_pk"]);
		
					// get create- and update-dates
					if ($node["update_user"] > 0)
					{
						$span_class = "";
		
						// last update from moderator?
						$posMod = $frm->getModeratorFromPost($node["pos_pk"]);
		
						if (is_array($posMod) && $posMod["top_mods"] > 0)
						{
							$MODS = $rbacreview->assignedUsers($posMod["top_mods"]);
							
							if (is_array($MODS))
							{
								if (in_array($node["update_user"], $MODS))
									$span_class = "moderator_small";
							}
						}
		
						$node["update"] = $frm->convertDate($node["update"]);
						#unset($lastuser);
						#$lastuser = $frm->getUser($node["update_user"]);

						$last_user_data = $frm->getUserData($node['update_user']);
						if ($span_class == "")
							$span_class = "small";
		
		
						if($last_user_data['usr_id'])
						{
							if ($last_user_data["public_profile"] == "n")
							{
								$edited_author = $last_user_data['login'];
							}
							else
							{
								$this->ctrl->setParameter($this, "backurl", $backurl);
								$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
								$this->ctrl->setParameter($this, "user", $last_user_data['usr_id']);
								$edited_author = 
									$this->ctrl->getLinkTarget($this, "showUser");
								$edited_author = "<a href=\"".
									$edited_author.
									"\">".$last_user_data['login']."</a>";
								$this->ctrl->clearParameters($this);
							}
						}
						else
						{
							$edited_author = $last_user_data['login'];
						}

						$tpl->setVariable("POST_UPDATE", $lng->txt("edited_at").": ".
							$node["update"]." - ".strtolower($lng->txt("by"))." ".$edited_author);

					} // if ($node["update_user"] > 0)
					
					
					if ($frm->isAnonymized())
					{
						if ($usr_data["login"] != "") $tpl->setVariable("AUTHOR", $usr_data["login"]);
						else $tpl->setVariable("AUTHOR", $lng->txt("forums_anonymous"));
					}
					else
					{
						if($node["author"])
						{
							$user_obj = new ilObjUser($usr_data["usr_id"]);
							// user image
							$webspace_dir = ilUtil::getWebspaceDir();
							$image_dir = $webspace_dir."/usr_images";
							$xthumb_file = $image_dir."/usr_".$user_obj->getID()."_xsmall.jpg";
							if ($user_obj->getPref("public_upload") == "y" &&
								$user_obj->getPref("public_profile") == "y" &&
								@is_file($xthumb_file))
							{
								$tpl->setCurrentBlock("usr_image");
								$tpl->setVariable("USR_IMAGE", $xthumb_file."?t=".rand(1, 99999));
								$tpl->parseCurrentBlock();
								//$tpl->setCurrentBlock("posts_row");
							}
							$tpl->setCurrentBlock("posts_row");
			
							//$t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", "frm");
							//$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
							if ($usr_data["public_profile"] == "n")
							{
								$tpl->setVariable("AUTHOR",
									$usr_data["login"]);
							}
							else
							{
								$tpl->setVariable("TXT_REGISTERED", $lng->txt("registered_since").":");
								$tpl->setVariable("REGISTERED_SINCE",$frm->convertDate($author->getCreateDate()));
		
								$this->ctrl->setParameter($this, "backurl", $backurl);
								$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
								$this->ctrl->setParameter($this, "user", $usr_data["usr_id"]);
								$href = $this->ctrl->getLinkTarget($this, "showUser");
								$tpl->setVariable("AUTHOR",
									"<a href=\"".$href."\">".$usr_data["login"]."</a>");
							}
			
							$numPosts = $frm->countUserArticles($author->id);
							$tpl->setVariable("TXT_NUM_POSTS", $lng->txt("forums_posts").":");
							$tpl->setVariable("NUM_POSTS",$numPosts);

							if ($frm->_isModerator($_GET["ref_id"], $ilUser->getId()) && $usr_data["public_profile"] != "n")
							{
								$tpl->setVariable("USR_NAME", $usr_data["firstname"]." ".$usr_data["lastname"]);
							}
						}
						else
						{
							$tpl->setCurrentBlock("posts_row");
							$tpl->setVariable("AUTHOR",$usr_data["login"]);
						}
					}

					// make links in post usable
					$node["message"] = ilUtil::makeClickable($node["message"]);
		
					// prepare post
					$node["message"] = $frm->prepareText($node["message"]);
		
					$tpl->setVariable("TXT_CREATE_DATE",$lng->txt("forums_thread_create_date"));
		
					if($forumObj->isRead($ilUser->getId(),$node['pos_pk']))
					{
						$tpl->setVariable("SUBJECT",stripslashes($node["subject"]));
					}
					else
					{
						if($forumObj->isNew($ilUser->getId(),$_GET['thr_pk'],$node['pos_pk']))
						{
							$tpl->setVariable("SUBJECT","<i><b>".stripslashes($node["subject"])."</b></i>");
						}
						else
						{
							$tpl->setVariable("SUBJECT","<b>".stripslashes($node["subject"])."</b>");
						}
					}
		
					$tpl->setVariable("POST_DATE",$frm->convertDate($node["create_date"]));
					$tpl->setVariable("SPACER","<hr noshade width=100% size=1 align='center'>");
					if ($node["pos_cens"] > 0)
						$tpl->setVariable("POST","<span class=\"moderator\">".nl2br(stripslashes($node["pos_cens_com"]))."</span>");
					else
					{
						// post from moderator?
						$modAuthor = $frm->getModeratorFromPost($node["pos_pk"]);
		
						$spanClass = "";
		
						if (is_array($modAuthor) && $modAuthor["top_mods"] > 0)
						{
							unset($MODS);
		
							$MODS = $rbacreview->assignedUsers($modAuthor["top_mods"]);
		
							if (is_array($MODS))
							{
								if (in_array($node["author"], $MODS))
									$spanClass = "moderator";
							}
						}
						if ($spanClass != "")
							$tpl->setVariable("POST","<span class=\"".$spanClass."\">".nl2br(stripslashes($node["message"]))."</span>");
						else
							$tpl->setVariable("POST",nl2br(stripslashes($node["message"])));
					}
		
					$tpl->parseCurrentBlock("posts_row");
		
				} // if (($posNum > $pageHits && $z >= $Start) || $posNum <= $pageHits)
		
				$z++;
		
			} // foreach($subtree_nodes as $node)
		}
		else
		{
			$tpl->setCurrentBlock("posts_no");
			$tpl->setVAriable("TXT_MSG_NO_POSTS_AVAILABLE",$lng->txt("forums_posts_not_available"));
			$tpl->parseCurrentBlock("posts_no");
		}
		
		$tpl->setCurrentBlock("posttable");
		$tpl->setVariable("COUNT_POST", $lng->txt("forums_count_art").": ".$posNum);
		
		$tpl->setVariable("TXT_AUTHOR", $lng->txt("author"));
		$tpl->setVariable("TXT_POST", $lng->txt("forums_thread").": ".$threadData["thr_subject"]);
		
		$tpl->parseCurrentBlock("posttable");
		
		$tpl->setVariable("TPLPATH", $tpl->vars["TPLPATH"]);
		
		$tpl->setCurrentBlock("perma_link");
		$tpl->setVariable("PERMA_LINK", ILIAS_HTTP_PATH.
			"/goto.php?target=".
			"frm".
			"_".$_GET["ref_id"]."_".$_GET["thr_pk"]."&client_id=".CLIENT_ID);
		$tpl->setVariable("TXT_PERMA_LINK", $lng->txt("perma_link"));
		$tpl->setVariable("PERMA_TARGET", "_top");
		$tpl->parseCurrentBlock();
	}
	
	/**
	* Show user profile.
	*/
	function showUserObject()
	{
		global $lng, $tpl, $rbacsystem, $ilias, $ilDB;
		
		require_once "./Modules/Forum/classes/class.ilForum.php";
		
		$lng->loadLanguageModule("forum");
		
		$ref_obj =& ilObjectFactory::getInstanceByRefId($_GET["ref_id"]);
		if($ref_obj->getType() == "frm")
		{
			$forumObj = new ilObjForum($_GET["ref_id"]);
			$frm =& $forumObj->Forum;
			$frm->setForumId($forumObj->getId());
			$frm->setForumRefId($forumObj->getRefId());
		}
		else
		{
			$frm =& new ilForum();
		}
		
		$tpl->addBlockFile("CONTENT", "content", "tpl.forums_user_view.html",
			"Modules/Forum");
		$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
		$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		
		// locator
		require_once("./Modules/Forum/classes/class.ilForumLocatorGUI.php");
		$frm_loc =& new ilForumLocatorGUI();
		$frm_loc->setRefId($_GET["ref_id"]);
		if ($ref_obj->getType() == "frm")
		{
			$frm_loc->setForum($frm);
		}
		if (!empty($_GET["thr_pk"]))
		{
			$frm->setWhereCondition("thr_pk = ".$ilDB->quote($_GET["thr_pk"]));
			$threadData = $frm->getOneThread();
			$frm_loc->setThread($_GET["thr_pk"], $threadData["thr_subject"]);
		}
		$frm_loc->showUser(true);
		$frm_loc->display();
		
		require_once ("classes/class.ilObjUserGUI.php");
		
		$_GET["obj_id"] = $_GET["user"];
		$user_gui = new ilObjUserGUI("",$_GET["user"], false, false);
		// count articles of user
		$numPosts = $frm->countUserArticles($_GET["user"]);
		$add = array($lng->txt("forums_posts") => $numPosts);
		$user_gui->insertPublicProfile("USR_PROFILE","usr_profile", $add);
		
		// display infopanel if something happened
		ilUtil::infoPanel();
		
		//$tpl->setCurrentBlock("usertable");
		if($_GET['backurl'])
		{
			$tpl->setCurrentBlock("btn_cell");
			$tpl->setVariable("BTN_LINK",urldecode($_GET["backurl"]));
			$tpl->setVariable("BTN_TXT", $lng->txt("back"));
			$tpl->parseCurrentBlock();
		}
		
		if (!$rbacsystem->checkAccess("read", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}
		
		$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("userdata"));
		
		// get user data
		$author = $frm->getUser($_GET["user"]);
		
		$tpl->setVariable("TPLPATH", $tpl->vars["TPLPATH"]);
		
		//$tpl->show();
	}
	
	/**
	* Perform form action in threads list.
	*/
	function performThreadsActionObject()
	{
		global $ilUser;
		
		$forumObj = new ilObjForum($_GET["ref_id"]);
		$frm =& $forumObj->Forum;

		if(is_array($_POST["forum_id"]))
		{
			if ($_POST["action"] == "enable_notifications")
			{
				for ($i = 0; $i < count($_POST["forum_id"]); $i++)
				{
					$frm->enableThreadNotification($ilUser->getId(), $_POST["forum_id"][$i]);
				}
	
				$this->ctrl->redirect($this, "showThreads");
			}
			else if ($_POST["action"] == "disable_notifications")
			{
				for ($i = 0; $i < count($_POST["forum_id"]); $i++)
				{
					$frm->disableThreadNotification($ilUser->getId(), $_POST["forum_id"][$i]);
				}
	
				$this->ctrl->redirect($this, "showThreads");
			}
			else
			{
				$this->ctrl->setCmd("exportHTML");
				$this->ctrl->setCmdClass("ilForumExportGUI");
				$this->executeCommand();
			
				unset($topicData);
			}
		}
		else
		{
			$this->ctrl->redirect($this, "showThreads");
		}
	}
	
	/**
	* New Thread form.
	*/
	function createThreadObject($errors = "")
	{
		global $lng, $tpl, $rbacsystem, $ilias, $ilDB;
		
		require_once "./Modules/Forum/classes/class.ilObjForum.php";
		
		$lng->loadLanguageModule("forum");
		
		$forumObj = new ilObjForum($_GET["ref_id"]);
		$frm =& $forumObj->Forum;
		
		$frm->setForumId($forumObj->getId());
		$frm->setForumRefId($forumObj->getRefId());
		
		$frm->setWhereCondition("top_frm_fk = ".$ilDB->quote($frm->getForumId()));
		$topicData = $frm->getOneTopic();
		
		$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.forums_threads_new.html",
			"Modules/Forum");
		$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		
		$tpl->setCurrentBlock("header_image");
		$tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_frm_b.gif"));
		$tpl->parseCurrentBlock();
		$tpl->setVariable("HEADER", $lng->txt("frm")." \"".$forumObj->getTitle()."\"");
		
		if ($errors != "")
		{
			ilUtil::sendInfo($lng->txt("form_empty_fields")." ".$errors);
		}

		// display infopanel if something happened
		ilUtil::infoPanel();
		
		if (!$rbacsystem->checkAccess("edit_post",$forumObj->getRefId()))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}
		
		require_once("./Modules/Forum/classes/class.ilForumLocatorGUI.php");
		$frm_loc =& new ilForumLocatorGUI();
		$frm_loc->setRefId($_GET["ref_id"]);
		$frm_loc->setForum($frm);
		$frm_loc->display();
		
		
		
		// ********************************************************************************		
		$tpl->setCurrentBlock("new_thread");
		$tpl->setVariable("TXT_REQUIRED_FIELDS", $lng->txt("required_field"));
		$tpl->setVariable("TXT_SUBJECT", $lng->txt("forums_thread"));
		$tpl->setVariable("SUBJECT_VALUE", ilUtil::prepareFormOutput(stripslashes($_POST["formData"]["subject"])));
		$tpl->setVariable("TXT_MESSAGE", $lng->txt("forums_the_post"));
		$tpl->setVariable("MESSAGE_VALUE", ilUtil::prepareFormOutput(stripslashes($_POST["formData"]["message"])));
		if ($forumObj->isAnonymized())
		{
			$tpl->setVariable("TXT_ALIAS", $lng->txt("forums_your_name"));
			$tpl->setVariable("ALIAS_VALUE", $_POST["formData"]["alias"]);
			$tpl->setVariable("TXT_ALIAS_INFO", $lng->txt("forums_use_alias"));
		}		
		
		include_once 'Services/Mail/classes/class.ilMail.php';
		$umail = new ilMail($_SESSION["AccountId"]);
		// catch hack attempts
		if ($rbacsystem->checkAccess("mail_visible",$umail->getMailObjectReferenceId()) &&
			!$forumObj->isAnonymized())
		{
			$tpl->setCurrentBlock("notify");
			$tpl->setVariable("TXT_NOTIFY",$lng->txt("forum_direct_notification"));
			$tpl->setVariable("NOTIFY",$lng->txt("forum_notify_me_directly"));
			if ($_POST["formData"]["notify"] == 1) $tpl->setVariable("NOTIFY_CHECKED", "checked");
			$tpl->parseCurrentBlock();
			if ($ilias->getSetting("forum_notification") != 0)
			{
				$tpl->setCurrentBlock("notify_posts");
				$tpl->setVariable("TXT_NOTIFY_POSTS",$lng->txt("forum_general_notification"));
				$tpl->setVariable("NOTIFY_POSTS",$lng->txt("forum_notify_me_generally"));
				if ($_POST["formData"]["notify_posts"] == 1) $tpl->setVariable("NOTIFY_POSTS_CHECKED", "checked");
				$tpl->parseCurrentBlock();
			}
		}
/*		if ($frm->isAnonymized())
		{
			$tpl->setCurrentBlock("anonymize");
			$tpl->setVariable("TXT_ANONYMIZE",$lng->txt("forum_anonymize"));
			$tpl->setVariable("ANONYMIZE",$lng->txt("forum_anonymize_desc"));
			$tpl->parseCurrentBlock();
		}*/
		$tpl->setVariable("SUBMIT", $lng->txt("submit"));
		$tpl->setVariable("RESET", $lng->txt("reset"));
		$tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this, "addThread"));
		$tpl->setVariable("TXT_NEW_TOPIC", $lng->txt("forums_new_thread"));
		
		$tpl->setCurrentBlock("attachment");
		$tpl->setVariable("TXT_ATTACHMENTS_ADD",$lng->txt("forums_attachments_add"));
		$tpl->setVariable("BUTTON_UPLOAD",$lng->txt("upload"));
		$tpl->parseCurrentBlock("attachment");
		
		$tpl->parseCurrentBlock("new_thread");
		
		$tpl->setVariable("TPLPATH", $tpl->vars["TPLPATH"]);
		
		#$tpl->show();
	}
	
	
	/**
	* Add New Thread.
	*/
	function addThreadObject()
	{
		global $lng, $tpl, $ilDB;
		
		$forumObj = new ilObjForum($_GET["ref_id"]);
		$frm =& $forumObj->Forum;
		$frm->setForumId($forumObj->getId());
		$frm->setForumRefId($forumObj->getRefId());
		
		$frm->setWhereCondition("top_frm_fk = ".$ilDB->quote($frm->getForumId()));
		$topicData = $frm->getOneTopic();

		$formData = $_POST["formData"];
	
		// check form-dates
		$errors = "";

		if (trim($formData["subject"]) == "") $errors .= $lng->txt("forums_thread").", ";
		if (trim($formData["message"]) == "") $errors .= $lng->txt("forums_the_post").", ";
		if ($errors != "") $errors = substr($errors, 0, strlen($errors)-2);

		if ($errors != "")
		{
			$this->createThreadObject($errors);
		}
		else
		{	
			
			// build new thread
			if ($forumObj->isAnonymized())
			{
				$newPost = $frm->generateThread(
								$topicData["top_pk"],
								0,
								ilUtil::stripSlashes($formData["subject"]),
								ilUtil::stripSlashes($formData["message"]),
								$formData["notify"],
								$formData["notify_posts"],
								ilUtil::stripSlashes($formData["alias"])
				);
			}
			else
			{
				$newPost = $frm->generateThread(
								$topicData["top_pk"],
								$_SESSION["AccountId"],
								ilUtil::stripSlashes($formData["subject"]),
								ilUtil::stripSlashes($formData["message"]),
								$formData["notify"],
								$formData["notify_posts"]
				);
			}
			
			// file upload
			if(isset($_FILES["userfile"]))
			{
				$tmp_file_obj =& new ilFileDataForum($forumObj->getId(),$newPost);
				$tmp_file_obj->storeUploadedFile($_FILES["userfile"]);
			}
			// end file upload		
			
			// Visit-Counter
			$frm->setDbTable("frm_data");
			$frm->setWhereCondition("top_pk = ".$ilDB->quote($topicData["top_pk"]));
			$frm->updateVisits($topicData["top_pk"]);
			// on success: change location
			$frm->setWhereCondition("thr_top_fk = ".$ilDB->quote($topicData["top_pk"])." AND thr_subject = ".
									$ilDB->quote($formData["subject"])." AND thr_num_posts = 1");		
	
#			if (is_array($thrData = $frm->getOneThread()))
#			{
				ilUtil::redirect('repository.php?ref_id='.$forumObj->getRefId());
#			} 
		}
		
#		ilUtil::redirect('repository.php?ref_id='.$forumObj->getRefId());
	}
	
	
	/**
	* Show Notification Tab
	*/
	function showThreadNotificationObject()
	{
		global $lng, $tpl, $rbacsystem, $ilias, $ilUser, $ilTabs, $ilDB;
		
		require_once "./Modules/Forum/classes/class.ilObjForum.php";
		require_once "./Modules/Forum/classes/class.ilFileDataForum.php";

		$lng->loadLanguageModule("forum");
		
		$forumObj = new ilObjForum($_GET["ref_id"]);
		$frm =& $forumObj->Forum;
		
		$frm->setForumId($forumObj->getId());
		$frm->setForumRefId($forumObj->getRefId());
		
		$this->prepareThreadScreen($forumObj);
		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.forums_threads_notification.html",
			"Modules/Forum");
		$ilTabs->setTabActive("forums_notification");

		if (!$rbacsystem->checkAccess("read,visible", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}
		
		// get forum- and thread-data
		$frm->setWhereCondition("top_frm_fk = ".$ilDB->quote($frm->getForumId()));
		
		if (is_array($topicData = $frm->getOneTopic()))
		{
			$frm->setWhereCondition("thr_pk = ".$ilDB->quote($_GET["thr_pk"]));
			$threadData = $frm->getOneThread();
			$tpl->setTitle($lng->txt("forums_thread")." \"".$threadData["thr_subject"]."\"");
			
			// ********************************************************************************
			// build location-links
			include_once("./Modules/Forum/classes/class.ilForumLocatorGUI.php");
			$frm_loc =& new ilForumLocatorGUI();
			$frm_loc->setRefId($_GET["ref_id"]);
			$frm_loc->setForum($frm);
			$frm_loc->setThread($_GET["thr_pk"], $threadData["thr_subject"]);
			$frm_loc->display();
		
			// set tabs
			// display different buttons depending on viewmode
		
			$this->ctrl->setParameter($this, "thr_pk", $_GET["thr_pk"]);
			if ($frm->isThreadNotificationEnabled($ilUser->getId(), $_GET["thr_pk"]))
			{
				$tpl->setVariable("TXT_STATUS", $lng->txt("forums_notification_is_enabled"));
				$tpl->setVariable("TXT_SUBMIT", $lng->txt("forums_disable_notification"));
				$tpl->setVariable("FORMACTION",
					$this->ctrl->getFormAction($this, "disableThreadNotification"));
				$tpl->setVariable("CMD", "disableThreadNotification");
			}
			else
			{
				$tpl->setVariable("TXT_STATUS", $lng->txt("forums_notification_is_disabled"));
				$tpl->setVariable("TXT_SUBMIT", $lng->txt("forums_enable_notification"));
				$tpl->setVariable("FORMACTION",
					$this->ctrl->getFormAction($this, "enableThreadNotification"));
				$tpl->setVariable("CMD", "enableThreadNotification");
			}
			$this->ctrl->clearParameters($this);
		}
	}
	
	/**
	* Enable forum notification.
	*/
	function enableForumNotificationObject()
	{
		global $ilUser, $lng, $ilDB;

		$forumObj = new ilObjForum($_GET["ref_id"]);
		$frm =& $forumObj->Forum;
		$frm->setForumId($forumObj->getId());
		
		$frm->enableForumNotification($ilUser->getId());
		
		$this->showThreadsObject();
	}

	/**
	* Disable forum notification.
	*/
	function disableForumNotificationObject()
	{
		global $ilUser, $lng, $ilDB;
		
		$forumObj = new ilObjForum($_GET["ref_id"]);
		$frm =& $forumObj->Forum;
		$frm->setForumId($forumObj->getId());

		$frm->disableForumNotification($ilUser->getId());
		
		$this->showThreadsObject();
	}

	/**
	* Enable thread notification.
	*/
	function enableThreadNotificationObject()
	{
		global $ilUser, $lng, $ilDB;

		$forumObj = new ilObjForum($_GET["ref_id"]);
		$frm =& $forumObj->Forum;
		$frm->setForumId($forumObj->getId());
		$frm->setForumRefId($forumObj->getRefId());
		$frm->setWhereCondition("top_frm_fk = ".$ilDB->quote($frm->getForumId()));
		$frm->setWhereCondition("thr_pk = ".$ilDB->quote($_GET["thr_pk"]));
		
		$frm->enableThreadNotification($ilUser->getId(), $_GET["thr_pk"]);
		ilUtil::sendInfo($lng->txt("forums_notification_enabled"));
		
		$this->showThreadNotificationObject();
	}

	/**
	* Disable thread notification.
	*/
	function disableThreadNotificationObject()
	{
		global $ilUser, $lng, $ilDB;
		
		$forumObj = new ilObjForum($_GET["ref_id"]);
		$frm =& $forumObj->Forum;
		$frm->setForumId($forumObj->getId());
		$frm->setForumRefId($forumObj->getRefId());
		$frm->setWhereCondition("top_frm_fk = ".$ilDB->quote($frm->getForumId()));
		$frm->setWhereCondition("thr_pk = ".$ilDB->quote($_GET["thr_pk"]));

		$frm->disableThreadNotification($ilUser->getId(), $_GET["thr_pk"]);
		ilUtil::sendInfo($lng->txt("forums_notification_disabled"));
		
		$this->showThreadNotificationObject();
	}

	/**
	* No editing allowd in forums. Notifications only.
	*/
	function checkEnableColumnEdit()
	{
		return false;
	}
	
	/**
	* Set column settings.
	*/
	function setColumnSettings($column_gui)
	{
		global $lng, $ilAccess;
		
		$lng->loadLanguageModule("frm");
		$column_gui->setBlockProperty("news", "title", $lng->txt("frm_latest_postings"));
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$column_gui->setBlockProperty("news", "settings", true);
			$column_gui->setBlockProperty("news", "public_notifications_option", true);
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
		
	 	if(!$_POST['clone_source'])
	 	{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->createObject();
			return false;
	 	}
		$source_id = $_POST['clone_source'];
		$this->lng->loadLanguageModule('frm');

	 	$new_type = $_REQUEST['new_type'];
	 	$this->ctrl->setParameter($this,'clone_source',(int) $_POST['clone_source']);
	 	$this->ctrl->setParameter($this,'new_type',$new_type);
	 	
	 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.frm_wizard_page.html','Modules/Forum');
	 	$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
	 	$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_'.$new_type.'.gif'));
	 	$this->tpl->setVariable('ALT_IMG',$this->lng->txt('obj_'.$new_type));
	 	$this->tpl->setVariable('TXT_DUPLICATE',$this->lng->txt('frm_wizard_page'));
	 	$this->tpl->setVariable('INFO_THREADS',$this->lng->txt('fmr_copy_threads_info'));
	 	$this->tpl->setVariable('THREADS',$this->lng->txt('forums_threads'));
	 	
	 	$forum_id = $ilObjDataCache->lookupObjId((int) $_POST['clone_source']);
	 	include_once('Modules/Forum/classes/class.ilForum.php');
	 	$threads = ilForum::_getThreads($forum_id,ilForum::SORT_TITLE);
	 	foreach($threads as $thread_id => $title)
	 	{
	 		$this->tpl->setCurrentBlock('thread_row');
	 		$this->tpl->setVariable('CHECK_THREAD',ilUtil::formCheckbox(0,'cp_options['.$source_id.'][threads][]',$thread_id));
	 		$this->tpl->setVariable('NAME_THREAD',$title);
	 		$this->tpl->parseCurrentBlock();
	 	}
	 	$this->tpl->setVariable('SELECT_ALL',$this->lng->txt('select_all'));
	 	$this->tpl->setVariable('JS_FIELD','cp_options['.$source_id.'][threads]');
	 	$this->tpl->setVariable('BTN_COPY',$this->lng->txt('obj_'.$new_type.'_duplicate'));
	 	$this->tpl->setVariable('BTN_BACK',$this->lng->txt('btn_back'));
	}
	
	/**
	*
	*/
	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $_GET["ref_id"]);
		}
	}

} // END class.ilObjForumGUI
?>
