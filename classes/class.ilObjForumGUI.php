<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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


/**
* Class ilObjForumGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id$Id: class.ilObjForumGUI.php,v 1.16 2004/11/26 10:43:49 smeyer Exp $
*
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

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
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

		$this->lng->loadLanguageModule('forum');
	}

	function &executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
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

	function showThreadsObject()
	{
		global $rbacsystem;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.forums_threads_liste.html");

		if($rbacsystem->checkAccess('edit_post',$this->object->getRefId()))
		{
			$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
			// display button
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",'forums_threads_new.php?ref_id='.$this->object->getRefId());
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt('forums_new_thread'));
			$this->tpl->parseCurrentBlock();
		}


		// start: form operations
		//????????????????????????????????????????
		if (isset($_POST["cmd"]["submit"]))
		{	
			if(is_array($_POST["forum_id"]))
			{
				$startTbl = "frm_threads";
		
				require_once "forums_export.php";		
		
				unset($topicData);

			}
			
		}

		$frm =& $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->setForumRefId($this->object->getRefId());
		$frm->setWhereCondition("top_frm_fk = ".$frm->getForumId());

		if (is_array($topicData = $frm->getOneTopic()))
		{
			// Visit-Counter
			$frm->setDbTable("frm_data");
			$frm->setWhereCondition("top_pk = ".$topicData["top_pk"]);
			$frm->updateVisits($topicData["top_pk"]);
	
			// get list of threads
			$frm->setOrderField("thr_date DESC");
			$resThreads = $frm->getThreadList($topicData["top_pk"]);
			$thrNum = $resThreads->numRows();
			$pageHits = $frm->getPageHits();
	
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
					if ($thrNum > $pageHits && $z >= ($Start+$pageHits))
					{
						break;
					}
		
					if (($thrNum > $pageHits && $z >= $Start) || $thrNum <= $pageHits)
					{
						// GET USER DATA, USED FOR IMPORTED USERS
						$usr_data = $frm->getUserData($thrData["thr_usr_id"],$thrData["import_name"]);


						$this->tpl->setCurrentBlock("threads_row");
						$rowCol = ilUtil::switchColor($z,"tblrow2","tblrow1");
						$this->tpl->setVariable("ROWCOL", $rowCol);
				
						$thrData["thr_date"] = $frm->convertDate($thrData["thr_date"]);
						$this->tpl->setVariable("DATE",$thrData["thr_date"]);
						$this->tpl->setVariable("TITLE","<a href=\"forums_frameset.php?thr_pk=".
										  $thrData["thr_pk"]."&ref_id=".$_GET["ref_id"]."\">".
										  $thrData["thr_subject"]."</a>");
				
						$this->tpl->setVariable("NUM_POSTS",$thrData["thr_num_posts"]);	
				
						$this->tpl->setVariable("NUM_VISITS",$thrData["visits"]);	
				
						// get author data

						if($thrData["thr_usr_id"] && $usr_data["usr_id"] != 0)
						{
							$this->tpl->setVariable("AUTHOR","<a href=\"forums_user_view.php?ref_id=".$this->object->getRefId().
													"&user=".$usr_data["usr_id"]."&backurl=repository&offset=".
													$Start."\">".$usr_data["login"]."</a>");
						}
						else
						{
							$this->tpl->setVariable("AUTHOR",$usr_data["login"]);
						}
					
				
						// get last-post data
						$lpCont = "";				
						if ($thrData["thr_last_post"] != "")
						{
							$lastPost = $frm->getLastPost($thrData["thr_last_post"]);
						}
						// TODOOOOOOOOOOOOOOOOOOO
						$last_usr_data = $frm->getUserData($lastPost["pos_usr_id"],$lastPost["import_name"]);
						if (is_array($lastPost))
						{				
							$lastPost["pos_message"] = $frm->prepareText($lastPost["pos_message"]);
							$lpCont = $lastPost["pos_date"]."<br/>".strtolower($this->lng->txt("from"))."&nbsp;";
							$lpCont .= "<a href=\"forums_frameset.php?pos_pk=".
								$lastPost["pos_pk"]."&thr_pk=".$lastPost["pos_thr_fk"]."&ref_id=".
								$_GET["ref_id"]."#".$lastPost["pos_pk"]."\">".$last_usr_data["login"]."</a>";
						}

						$this->tpl->setVariable("LAST_POST", $lpCont);	
				
						$this->tpl->setVariable("FORUM_ID", $thrData["thr_pk"]);		
						$this->tpl->setVariable("THR_TOP_FK", $thrData["thr_top_fk"]);		
				
						$this->tpl->setVariable("TXT_PRINT", $this->lng->txt("print"));
				
						$this->tpl->setVariable("THR_IMGPATH",$this->tpl->tplPath);
				
						$this->tpl->parseCurrentBlock("threads_row");
				
					} // if (($thrNum > $pageHits && $z >= $Start) || $thrNum <= $pageHits)
			
					$z ++;
			
				} // while ($thrData = $resThreads->fetchRow(DB_FETCHMODE_ASSOC))
		
				$this->tpl->setVariable("TXT_SELECT_ALL", $this->lng->txt("select_all"));		
				$this->tpl->setVariable("FORMACTION",'forums_threads_liste.php?ref_id='.$this->object->getRefId());
				$this->tpl->setVariable("TXT_OK",$this->lng->txt("ok"));			
				$this->tpl->setVariable("TXT_EXPORT_HTML", $this->lng->txt("export_html"));
				$this->tpl->setVariable("TXT_EXPORT_XML", $this->lng->txt("export_xml"));
				$this->tpl->setVariable("IMGPATH",$this->tpl->tplPath);
		
			} // if ($thrNum > 0)	
		
		} // if (is_array($topicData = $frm->getOneTopic()))
		else
		{
			$this->tpl->setCurrentBlock("threads_no");
			$this->tpl->setVariable("TXT_MSG_NO_THREADS_AVAILABLE",$this->lng->txt("forums_threads_not_available"));
			$this->tpl->parseCurrentBlock("threads_no");
		}

		$this->tpl->setCurrentBlock("threadtable");
		$this->tpl->setVariable("COUNT_THREAD", $this->lng->txt("forums_count_thr").": ".$thrNum);
		$this->tpl->setVariable("TXT_DATE", $this->lng->txt("date"));
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_TOPIC", $this->lng->txt("forums_thread"));
		$this->tpl->setVariable("TXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TXT_NUM_POSTS", $this->lng->txt("forums_articles"));
		$this->tpl->setVariable("TXT_NUM_VISITS", $this->lng->txt("visits"));
		$this->tpl->setVariable("TXT_LAST_POST", $this->lng->txt("forums_last_post"));
		$this->tpl->parseCurrentBlock("threadtable");
	}

	function createObject()
	{
		//add template for buttons
		parent::createObject();

		return true;
	}

	function updateObject()
	{
		if(strlen($_POST['Fobject']['title']))
		{
			$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
			$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
			$this->update = $this->object->update();

			sendInfo($this->lng->txt("msg_obj_modified"));
		}
		else
		{
			sendInfo($this->lng->txt('frm_title_required'));
		}
		return $this->editObject();
	}

	/**
	* display edit form (usually called by editObject)
	*
	* @access	private
	* @param	array	$fields		key/value pairs of input fields
	*/
	function properties()
	{

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.forum_properties.html");

		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
		$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		$this->tpl->setVariable("DESC", ilUtil::stripSlashes($this->object->getDescription()));


		$this->tpl->setVariable("FORMACTION", "repository.php?ref_id=".$this->object->getRefId());
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}

	function saveProperties()
	{
		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["desc"]));
		$this->update = $this->object->update();

		sendInfo($this->lng->txt("msg_obj_modified"),true);
		ilUtil::redirect("repository.php?ref_id=".$this->object->getRefId());
	}


	function importObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"],"frm"))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->getTemplateFile("import","frm");

		$this->tpl->setVariable("FORMACTION","adm_object.php?ref_id=".$this->ref_id."&cmd=gateway&new_type=frm");
		$this->tpl->setVariable("TXT_IMPORT_FORUM",$this->lng->txt("forum_import"));
		$this->tpl->setVariable("TXT_IMPORT_FILE",$this->lng->txt("forum_import_file"));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN_IMPORT",$this->lng->txt("import"));

		return true;
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
			sendInfo($this->lng->txt("import_forum_finished"),true);
			ilUtil::redirect("adm_object.php?ref_id=".$_GET["ref_id"]);
		}
		else
		{
			sendInfo($this->message);
			$this->importObject();
		}
	}


	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$forumObj = parent::saveObject();

		// setup rolefolder & default local roles (moderator)
		$roles = $forumObj->initDefaultRoles();

		// ...finally assign moderator role to creator of forum object
		$rbacadmin->assignUser($roles[0], $forumObj->getOwner(), "n");
		ilObjUser::updateActiveRoles($forumObj->getOwner());
			
		// insert new forum as new topic into frm_data
		$top_data = array(
            "top_frm_fk"   		=> $forumObj->getId(),
			"top_name"   		=> addslashes($forumObj->getTitle()),
            "top_description" 	=> addslashes($forumObj->getDescription()),
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
		$q .= "('".$top_data["top_frm_fk"]."','".$top_data["top_name"]."','".$top_data["top_description"]."','".
			$top_data["top_num_posts"]."','".$top_data["top_num_threads"]."','".$top_data["top_last_post"]."','".
			$top_data["top_mods"]."','".$top_data["top_date"]."','".$top_data["top_usr_id"]."')";
		$this->ilias->db->query($q);

		// always send a message
		sendInfo($this->lng->txt("frm_added"),true);
		header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		exit();
	}

	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if($rbacsystem->checkAccess('write',$this->ref_id))
		{
			include_once './classes/class.ilRepositoryExplorer.php';

			$tabs_gui->addTarget("view_content",ilRepositoryExplorer::buildLinkTarget($this->ref_id,'frm'));
		}
		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$tabs_gui->addTarget("edit_properties",
								 $this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this));
		}
		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
								 $this->ctrl->getLinkTarget($this, "perm"), "perm", get_class($this));
		}


		return true;
	}


	// PRIVATE
	function __initFileObject()
	{
		include_once "classes/class.ilFileDataImportForum.php";

		$this->file_obj =& new ilFileDataImportForum();

		return true;
	}

	function __initParserObject($a_xml_file)
	{
		include_once "classes/class.ilForumImportParser.php";

		$this->parser_obj =& new ilForumImportParser($a_xml_file,$this->ref_id);

		return true;
	}

} // END class.ilObjForumGUI
?>
