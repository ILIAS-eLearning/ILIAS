<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once ("./Services/COPage/classes/class.ilPageContentGUI.php");
require_once ("./Services/COPage/classes/class.ilPCInteractiveImage.php");
include_once("./Services/COPage/classes/class.ilMediaAliasItem.php");

/**
 * User interface class for interactive images
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilPCInteractiveImageGUI: ilPCIIMTriggerEditorGUI
 *
 * @ingroup ServicesCOPage
 */
class ilPCInteractiveImageGUI extends ilPageContentGUI
{
	function __construct($a_pg_obj, $a_content_obj, $a_hier_id = 0, $a_pc_id = "")
	{
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $tpl, $lng, $ilTabs;

		
//		$this->getCharacteristicsOfCurrentStyle("media_cont");	// scorm-2004
		
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get current command
		$cmd = $this->ctrl->getCmd();

		if (is_object ($this->content_obj))
		{
			$tpl->setTitleIcon(ilUtil::getImagePath("icon_mob.svg"));
			$this->getTabs($this->tabs_gui);

/*			$mob = $this->content_obj->getMediaObject();
			if (is_object($mob))
			{
				$tpl->setVariable("HEADER", $lng->txt("mob").": ".
					$this->content_obj->getMediaObject()->getTitle());
				$mob_gui =& new ilObjMediaObjectGUI("", $this->content_obj->getMediaObject()->getId(),false, false);
				$mob_gui->setBackTitle($this->page_back_title);
				$mob_gui->setEnabledMapAreas($this->getEnabledMapAreas());
				$mob_gui->getTabs($this->tabs_gui);
			}*/
		}
		else
		{
		}

		switch($next_class)
		{
			// trigger editor
			case "ilpciimtriggereditorgui":
				require_once("./Services/COPage/classes/class.ilPCIIMTriggerEditorGUI.php");
				$ilTabs->setTabActive("triggers");
				$image_map_edit = new ilPCIIMTriggerEditorGUI($this->content_obj,
					$this->pg_obj);
				$ret = $this->ctrl->forwardCommand($image_map_edit);
				$tpl->setContent($ret);
				break;
			
			default:
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}

	/**
	 * Add tabs to ilTabsGUI object
	 *
	 * @param	object		$tab_gui		ilTabsGUI object
	 * @param	boolean		$a_create		new creation true/false
	 */
	function getTabs(&$tab_gui, $a_create = false, $a_change_obj_ref = false)
	{
		global $ilCtrl, $ilTabs, $lng;

		if (!$a_create)
		{
			
			$ilTabs->setBackTarget($lng->txt("pg"),
				$ilCtrl->getParentReturn($this)
				);

			$ilTabs->addTab("triggers",
				$lng->txt("cont_active_areas"),
				$ilCtrl->getLinkTargetByClass("ilpciimtriggereditorgui", "editMapAreas")
				);

			$ilTabs->addTab("list_overlays",
				$lng->txt("cont_overlay_images"),
				$ilCtrl->getLinkTarget($this, "listOverlayImages")
				);

			$ilTabs->addTab("content_popups",
				$lng->txt("cont_content_popups"),
				$ilCtrl->getLinkTarget($this, "listContentPopups")
				);

			$ilTabs->addTab("edit_base_image",
				$lng->txt("cont_base_image")." & ".$lng->txt("cont_caption"),
				$ilCtrl->getLinkTarget($this, "editBaseImage")
				);

		}
	}

	/**
	 * Insert new media object form.
	 */
	function insert($a_post_cmd = "edpost", $a_submit_cmd = "create_mob", $a_input_error = false)
	{
		global $ilTabs, $tpl, $ilCtrl, $lng;
		
		ilUtil::sendInfo($lng->txt("cont_iim_create_info"));
		
//		$this->getTabs($ilTabs, true);
//		$ilTabs->setSubTabActive("cont_new_mob");
		
		if ($a_input_error)
		{
			$form = $this->form;
		}
		else
		{
			$form = $this->initForm("create");
		}
		$form->setFormAction($ilCtrl->getFormAction($this));

		$this->displayValidationError();
		
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Edit
	 */
	function edit()
	{
		global $tpl, $ilCtrl;
		
		$ilCtrl->redirectByClass(array("ilpcinteractiveimagegui", "ilpciimtriggereditorgui"), "editMapAreas");
		//$tpl->setContent("hh");
	}
	
	/**
	 * Edit base image
	 *
	 * @param
	 * @return
	 */
	function editBaseImage($a_form = null)
	{
		global $tpl, $ilTabs, $lng;
		
		$ilTabs->activateTab("edit_base_image");
		
		$form = $this->initForm();
		$tpl->setContent($form->getHTML());
	}
	
	
	/**
	 * Init creation/base image form.
	 *
	 * @param        int        $a_mode        Edit Mode
	 */
	public function initForm($a_mode = "edit")
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		// image file
		$fi = new ilImageFileInputGUI($lng->txt("cont_file"), "image_file");
		$fi->setAllowDeletion(false);
		if ($a_mode == "edit")
		{
			$fi->setImage($this->content_obj->getBaseThumbnailTarget());
		}
		$form->addItem($fi);
		
		if ($a_mode == "edit")
		{
			// caption
			$ti = new ilTextInputGUI($this->lng->txt("cont_caption"), "caption");
			$ti->setMaxLength(200);
			$ti->setSize(50);
			$form->addItem($ti);
		}
		
		// save and cancel commands
		if ($a_mode == "create")
		{
			$form->setTitle($lng->txt("cont_ed_insert_iim"));
			$form->addCommandButton("create_iim", $lng->txt("save"));
			$form->addCommandButton("cancelCreate", $lng->txt("cancel"));
		}
		else
		{
			// get caption
			$std_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
				$this->content_obj->getPcId(), "InteractiveImage");
			$ti->setValue($std_alias_item->getCaption());
			
			$form->setTitle($lng->txt("cont_edit_base_image"));
			$form->addCommandButton("update", $lng->txt("save"));
		}
	                
		$form->setFormAction($ilCtrl->getFormAction($this));
	 
		return $form;
	}

	/**
	 * Create new content element
	 */
	function create()
	{
		global $ilCtrl, $lng;
		
		$this->content_obj = new ilPCInteractiveImage($this->getPage());
		$this->content_obj->createMediaObject();
		$media_obj = $this->content_obj->getMediaObject();
		$media_obj->setTitle($_FILES['image_file']['name']);
		$media_obj->create();
		$mob_dir = ilObjMediaObject::_getDirectory($media_obj->getId());
//		$media_obj->setStandardType("File");
		$media_obj->createDirectory();
		$media_item = new ilMediaItem();
		$media_obj->addMediaItem($media_item);
		$media_item->setPurpose("Standard");
		
		$file = $mob_dir."/".$_FILES['image_file']['name'];
		ilUtil::moveUploadedFile($_FILES['image_file']['tmp_name'],
			$_FILES['image_file']['name'], $file);

		// get mime type
		$format = ilObjMediaObject::getMimeType($file);
		$location = $_FILES['image_file']['name'];

		// set real meta and object data
		$media_item->setFormat($format);
		$media_item->setLocation($location);
		$media_item->setLocationType("LocalFile");

		ilUtil::renameExecutables($mob_dir);
		$media_obj->update();

		$this->content_obj->createAlias($this->pg_obj, $this->hier_id, $this->pc_id);
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->pg_obj->stripHierIDs();
			$this->pg_obj->addHierIDs();
			$ilCtrl->setParameter($this, "hier_id", $this->content_obj->readHierId());
			$ilCtrl->setParameter($this, "pc_id", $this->content_obj->readPCId());
			$this->content_obj->setHierId($this->content_obj->readHierId());
			$this->setHierId($this->content_obj->readHierId());
			$this->content_obj->setPCId($this->content_obj->readPCId());
			ilUtil::sendSuccess($lng->txt("cont_saved_interactive_image"), true);
			$this->ctrl->redirectByClass("ilpcinteractiveimagegui", "edit");

			//$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->insert();
		}
	}
	
	/**
	 * Update (base image)
	 */
	function update()
	{
		global $ilCtrl, $lng;
		
		$mob = $this->content_obj->getMediaObject();
		$mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
		$std_item = $mob->getMediaItem("Standard");
		$location = $_FILES['image_file']['name'];

		if ($location != "" && is_file($_FILES['image_file']['tmp_name']))
		{
			$file = $mob_dir."/".$_FILES['image_file']['name'];
			ilUtil::moveUploadedFile($_FILES['image_file']['tmp_name'],
				$_FILES['image_file']['name'], $file);

			// get mime type
			$format = ilObjMediaObject::getMimeType($file);
			$location = $_FILES['image_file']['name'];
			$std_item->setFormat($format);
			$std_item->setLocation($location);
			$std_item->setLocationType("LocalFile");
			$mob->setDescription($format);
			$mob->update();
		}

		// set caption
		$std_alias_item = new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId(), "InteractiveImage");
		$std_alias_item->setCaption(ilUtil::stripSlashes($_POST["caption"]));
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);

		$ilCtrl->redirectByClass("ilpcinteractiveimagegui", "editBaseImage");
	}
	
	
	/**
	 * Align media object to center
	 */
	function centerAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId(), "InteractiveImage");
		$std_alias_item->setHorizontalAlign("Center");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	 * align media object to left
	 */
	function leftAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId(), "InteractiveImage");
		$std_alias_item->setHorizontalAlign("Left");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	 * align media object to right
	 */
	function rightAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId(), "InteractiveImage");
		$std_alias_item->setHorizontalAlign("Right");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	 * align media object to left, floating text
	 */
	function leftFloatAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId(), "InteractiveImage");
		$std_alias_item->setHorizontalAlign("LeftFloat");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	 * align media object to right, floating text
	 */
	function rightFloatAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId(), "InteractiveImage");
		$std_alias_item->setHorizontalAlign("RightFloat");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	////
	//// Overlay Images
	////

	/**
	 * List overlay images
	 *
	 * @param
	 * @return
	 */
	function listOverlayImages()
	{
		global $tpl, $ilToolbar, $ilCtrl, $ilTabs, $lng;
		
		ilUtil::sendInfo($lng->txt("cont_iim_overlay_info"));
		
		$ilTabs->setTabActive("list_overlays");
		
		$ilToolbar->addButton($lng->txt("cont_add_images"),
			$ilCtrl->getLinkTarget($this, "addOverlayImages"));
		
		include_once("./Services/COPage/classes/class.ilPCIIMOverlaysTableGUI.php");
		$tab = new ilPCIIMOverlaysTableGUI($this, "listOverlayImages",
			$this->content_obj->getMediaObject());
		$tpl->setContent($tab->getHTML());
	}
	
	/**
	 * Add overlay images
	 */
	function addOverlayImages($a_form = null)
	{
		global $tpl;
		
		if ($a_form)
		{
			$form = $a_form;
		}
		else
		{
			$form = $this->initAddOverlaysForm();
		}
		
		$tpl->setContent($form->getHTML());
	}
	
	/**
	 * Init add overlays form
	 *
	 * @param
	 * @return
	 */
	function initAddOverlaysForm()
	{
		global $lng, $ilCtrl, $ilTabs;
		
		$ilTabs->setTabActive("list_overlays");
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTitle($lng->txt("cont_add_images"));
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		// file input
		include_once("./Services/Form/classes/class.ilFileWizardInputGUI.php");
		$fi = new ilFileWizardInputGUI($lng->txt("file"), "ovfile");
		$fi->setSuffixes(array("gif", "jpeg", "jpg", "png"));
		$fi->setFilenames(array(0 => ''));
		$fi->setRequired(true);
		$form->addItem($fi);
	
		$form->addCommandButton("uploadOverlayImages", $lng->txt("upload"));
		$form->addCommandButton("listOverlayImages", $lng->txt("cancel"));
		
		return $form;
	}
	
	
	/**
	 * Upload overlay images
	 *
	 * @param
	 * @return
	 */
	function uploadOverlayImages()
	{
		global $lng, $ilCtrl;
		
		$form = $this->initAddOverlaysForm();
		if ($form->checkInput())
		{
			if (is_array($_FILES["ovfile"]["name"]))
			{
				foreach ($_FILES["ovfile"]["name"] as $k => $v)
				{
					$name = $_FILES["ovfile"]["name"][$k];
					$mime = $_FILES["ovfile"]["type"][$k];
					$tmp_name = $_FILES["ovfile"]["tmp_name"][$k];
					$size = $_FILES["ovfile"]["size"][$k];
					
					$this->content_obj->getMediaObject()->uploadAdditionalFile($name,
						$tmp_name, "overlays");
					$piname = pathinfo($name);
					$this->content_obj->getMediaObject()->makeThumbnail("overlays/".$name,
						basename($name, ".".$piname['extension']).".png");
				}
			}
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"));
			$ilCtrl->redirect($this, "listOverlayImages");
		}
		else
		{
			$form->setValuesByPost();
			$this->addOverlayImages($form);
		}
	}
	
	/**
	* Confirm overlay deletion
	*/
	function confirmDeleteOverlays()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs;
		
		$ilTabs->setTabActive("list_overlays");

		if (!is_array($_POST["file"]) || count($_POST["file"]) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listOverlayImages");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("cont_really_delete_overlays"));
			$cgui->setCancel($lng->txt("cancel"), "listOverlayImages");
			$cgui->setConfirm($lng->txt("delete"), "deleteOverlays");
			
			foreach ($_POST["file"] as $i => $d)
			{
				$cgui->addItem("file[]", $i, $i);
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}
	
	/**
	 * Delete overlays
	 */
	function deleteOverlays()
	{
		global $ilCtrl, $lng;
		
		if (is_array($_POST["file"]) && count($_POST["file"]) != 0)
		{
			foreach ($_POST["file"] as $f)
			{
				$f = str_replace("..", "", ilUtil::stripSlashes($f));
				$this->content_obj->getMediaObject()
					->removeAdditionalFile("overlays/".$f);
			}
			
			ilUtil::sendSuccess($lng->txt("cont_overlays_have_been_deleted"), true);
		}
		$ilCtrl->redirect($this, "listOverlayImages");
	}
	
	
	////
	//// Content Popups
	////

	/**
	 * List content popups
	 */
	function listContentPopups()
	{
		global $tpl, $ilToolbar, $ilCtrl, $ilTabs, $lng;
		
		ilUtil::sendInfo($lng->txt("cont_iim_content_popups_info"));
		
		$ilTabs->setTabActive("content_popups");
		
		$ilToolbar->addButton($lng->txt("cont_add_popup"),
			$ilCtrl->getLinkTarget($this, "addPopup"));
		
		include_once("./Services/COPage/classes/class.ilPCIIMPopupTableGUI.php");
		$tab = new ilPCIIMPopupTableGUI($this, "listContentPopups",
			$this->content_obj);
		$tpl->setContent($tab->getHTML());
	}

	/**
	 * Add popup
	 *
	 * @param
	 * @return
	 */
	function addPopup()
	{
		global $ilCtrl, $lng;
		
		$this->content_obj->addContentPopup();
		$this->pg_obj->update();
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "listContentPopups");
	}
	
	/**
	 * Save popups
	 */
	function savePopups()
	{
		global $ilCtrl, $lng;
		
		if (is_array($_POST["title"]))
		{
			$titles = ilUtil::stripSlashesArray($_POST["title"]);
			$this->content_obj->savePopUps($titles);
			$this->pg_obj->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		}
		$ilCtrl->redirect($this, "listContentPopups");
	}
	
	/**
	 * Confirm popup deletion
	 */
	function confirmPopupDeletion()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs;
		
		$ilTabs->setTabActive("content_popups");
			
		if (!is_array($_POST["tid"]) || count($_POST["tid"]) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listContentPopups");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("cont_really_delete_popups"));
			$cgui->setCancel($lng->txt("cancel"), "listContentPopups");
			$cgui->setConfirm($lng->txt("delete"), "deletePopups");
			
			foreach ($_POST["tid"] as $i => $d)
			{
				$cgui->addItem("tid[]", $i, $_POST["title"][$i]);
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}
	
	/**
	 * Delete popups
	 *
	 * @param
	 * @return
	 */
	function deletePopups()
	{
		global $lng, $ilCtrl;
		
		if (is_array($_POST["tid"]) && count($_POST["tid"]) != 0)
		{
			foreach ($_POST["tid"] as $id)
			{
				$id = explode(":", $id);
				$this->content_obj->deletePopup($id[0], $id[1]);
			}
			$this->pg_obj->update();
			ilUtil::sendSuccess($lng->txt("cont_popups_have_been_deleted"), true);
		}
		$ilCtrl->redirect($this, "listContentPopups");
	}
	
}
?>
