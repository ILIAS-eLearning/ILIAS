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
* $Id$Id: class.ilObjForumGUI.php,v 1.13 2004/04/08 09:52:44 smeyer Exp $
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
		$this->type = "frm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
	}

	function createObject()
	{
		//add template for buttons

		/*
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", "adm_object.php?ref_id=".$this->ref_id."&cmd=importForm");
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("import_forum"));
		$this->tpl->parseCurrentBlock();
		*/
		parent::createObject();

		return true;
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
		$this->tpl->setVariable("TITLE", $this->object->getTitle());
		$this->tpl->setVariable("DESC", $this->object->getDescription());


		$this->tpl->setVariable("FORMACTION", "forums_threads_liste.php?ref_id=".$_GET["ref_id"]);
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
		ilUtil::redirect("forums_threads_liste.php?ref_id=".$_GET["ref_id"]);
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
		$q .= "('".$top_data["top_frm_fk"]."','".$top_data["top_name"]."','".$top_data["top_description"]."','".$top_data["top_num_posts"]."','".$top_data["top_num_threads"]."','".$top_data["top_last_post"]."','".$top_data["top_mods"]."','".$top_data["top_date"]."','".$top_data["top_usr_id"]."')";
		$this->ilias->db->query($q);

		// always send a message
		sendInfo($this->lng->txt("frm_added"),true);
		header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		exit();
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
