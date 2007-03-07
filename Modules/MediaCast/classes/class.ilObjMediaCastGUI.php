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

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjMediaCastGUI
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjMediaCastGUI:
*/
class ilObjMediaCastGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjMediaCastGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		$this->type = "mcst";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
	}
	
	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff
			
		// always send a message
		ilUtil::sendInfo($this->lng->txt("object_added"),true);
		
		ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"")));
	}
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		// tabs are defined manually here. The autogeneration via objects.xml will be deprecated in future
		// for usage examples see ilObjGroupGUI or ilObjSystemFolderGUI
	}
	
	/**
	* List items of media cast.
	*/
	function listItemsObject()
	{
		global $tpl, $lng, $ilAccess;
		
		$med_items = $this->object->getItemsArray();
		$lng->loadLanguageModule("mcst");
		
		include_once("./Modules/MediaCast/classes/class.ilMediaCastTableGUI.php");
		$table_gui = new ilMediaCastTableGUI($this, "listItems");
				
		$table_gui->setTitle($lng->txt("mcst_media_cast"));
		$table_gui->setData($med_items);
		
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$table_gui->addCommandButton("addCastItem", $lng->txt("add"));
		}
		
		$tpl->setContent($table_gui->getHTML());

	}
	
	/**
	* Add media cast item
	*/
	function addCastItemObject()
	{
		global $tpl;
		
		$this->initAddCastItemForm();
		$tpl->setContent($this->form_gui->getHTML());
	}
	
	/**
	* Init add cast item form.
	*/
	function initAddCastItemForm($a_mode = "create")
	{
		global $lng, $ilCtrl;
		
		$lng->loadLanguageModule("mcst");
		
		include("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		
		// Property Title
		$text_input = new ilTextInputGUI($lng->txt("title"), "title");
		$text_input->setRequired(true);
		$text_input->setMaxLength(200);
		$this->form_gui->addItem($text_input);
		
		// Property Content
		$text_area = new ilTextAreaInputGUI($lng->txt("description"), "description");
		$text_area->setRequired(false);
		$this->form_gui->addItem($text_area);
		
		// Property Visibility
		$radio_group = new ilRadioGroupInputGUI($lng->txt("access_scope"), "visibility");
		$radio_option = new ilRadioOption($lng->txt("access_users"), "users");
		$radio_group->addOption($radio_option);
		$radio_option = new ilRadioOption($lng->txt("access_public"), "public");
		$radio_group->addOption($radio_option);
		$radio_group->setInfo($lng->txt("mcst_visibility_info"));
		$radio_group->setRequired(true);
		$radio_group->setValue("users");
		$this->form_gui->addItem($radio_group);
		
		// File
		$file = new ilFileInputGUI($lng->txt("file"), "file");
		$file->setRequired(true);
		$file->setSuffixes(array("mp3"));
		$this->form_gui->addItem($file);
		
		// Duration
		$dur = new ilDurationInputGUI($lng->txt("mcst_duration"), "duration");
		$dur->setInfo($lng->txt("mcst_duration_info"));
		$dur->setShowDays(false);
		$dur->setShowHours(true);
		$dur->setShowSeconds(true);
		$this->form_gui->addItem($dur);
		
		// save/cancel button
		$this->form_gui->addCommandButton("saveCastItem", $lng->txt("save"));
		$this->form_gui->addCommandButton("listItems", $lng->txt("cancel"));
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this, "saveCastItem"));
		
		$this->form_gui->setTitle($lng->txt("mcst_add_new_item"));
	}
	
	
	/**
	* Save new cast item
	*/
	function saveCastItemObject()
	{
		global $tpl, $ilCtrl, $ilUser;
		
		$this->initAddCastItemForm();
		
		if ($this->form_gui->checkInput())
		{
			
			// create dummy object in db (we need an id)
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
			$mob = new ilObjMediaObject();

			$mob->setTitle($this->form_gui->getInput("title"));
			$mob->setDescription("");
			$mob->create();

			// determine and create mob directory, move uploaded file to directory
			//$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->object->getId();
			$mob->createDirectory();
			$mob_dir = ilObjMediaObject::_getDirectory($mob->getId());

			$media_item =& new ilMediaItem();
			$mob->addMediaItem($media_item);
			$media_item->setPurpose("Standard");

			$file = $mob_dir."/".$_FILES['file']['name'];
			ilUtil::moveUploadedFile($_FILES['file']['tmp_name'],
				$_FILES['file']['name'], $file);

			// determine duration
			$duration = $this->form_gui->getInput("duration");
			if ($duration["hh"] == 0 && $duration["mm"] == 0 && $duration["ss"] == 0)
			{
				include_once("./Services/MediaObjects/classes/class.ilMediaAnalyzer.php");
				$ana = new ilMediaAnalyzer();
				$ana->setFile($file);
				$ana->analyzeFile();
				$dur = $ana->getPlaytimeString();
				$dur = explode(":", $dur);
				$duration["mm"] = $dur[0];
				$duration["ss"] = $dur[1];
			}
			$duration = 
				str_pad($duration["hh"], 2 , "0", STR_PAD_LEFT).":".
				str_pad($duration["mm"], 2 , "0", STR_PAD_LEFT).":".
				str_pad($duration["ss"], 2 , "0", STR_PAD_LEFT);
			
			
			// get mime type
			$format = ilObjMediaObject::getMimeType($file);
			$location = $_FILES['file']['name'];

			// set real meta and object data
			$media_item->setFormat($format);
			$media_item->setLocation($location);
			$media_item->setLocationType("LocalFile");
			$mob->setTitle($_FILES['file']['name']);
			$mob->setDescription($format);
			$media_item->setHAlign("Left");

			ilUtil::renameExecutables($mob_dir);
			$mob->update();
			
			//
			// @todo: save usage
			//
			
			// create new media cast item
			include_once("./Modules/MediaCast/classes/class.ilMediaCastItem.php");
			$mc_item = new ilMediaCastItem();
			$mc_item->setMobId($mob->getId());
			$mc_item->setMcstId($this->object->getId());
			$mc_item->setUpdateUser($ilUser->getId());
			$mc_item->setLength($duration);
			$mc_item->setTitle($this->form_gui->getInput("title"));
			$mc_item->setDescription($this->form_gui->getInput("description"));
			$mc_item->setVisibility($this->form_gui->getInput("visibility"));
			$mc_item->create();
			
			$ilCtrl->redirect($this, "listItems");
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$tpl->setContent($this->form_gui->getHTML());
		}
	}
	
} // END class.ilObjMediaCast
?>
