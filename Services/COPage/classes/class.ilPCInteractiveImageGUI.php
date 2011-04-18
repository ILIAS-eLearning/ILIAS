<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once ("./Services/COPage/classes/class.ilPageContentGUI.php");
require_once ("./Services/COPage/classes/class.ilPCInteractiveImage.php");

/**
 * User interface class for interactive images
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilPCInteractiveImageGUI: ilPCImageMapEditorGUI
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
			$tpl->setTitleIcon(ilUtil::getImagePath("icon_mob_b.gif"));
			$this->getTabs($this->tabs_gui);

			$mob = $this->content_obj->getMediaObject();
			if (is_object($mob))
			{
				$tpl->setVariable("HEADER", $lng->txt("mob").": ".
					$this->content_obj->getMediaObject()->getTitle());
				$mob_gui =& new ilObjMediaObjectGUI("", $this->content_obj->getMediaObject()->getId(),false, false);
				$mob_gui->setBackTitle($this->page_back_title);
				$mob_gui->setEnabledMapAreas($this->getEnabledMapAreas());
				$mob_gui->getTabs($this->tabs_gui);
			}
		}
		else
		{
		}

		switch($next_class)
		{
			case "ilobjmediaobjectgui":
				include_once ("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
				$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_mob_b.gif"));
				$this->tpl->setTitle($this->lng->txt("mob").": ".
					$this->content_obj->getMediaObject()->getTitle());
				$mob_gui =& new ilObjMediaObjectGUI("", $this->content_obj->getMediaObject()->getId(),false, false);
				$mob_gui->setBackTitle($this->page_back_title);
				$mob_gui->setEnabledMapAreas($this->getEnabledMapAreas());
				$ret =& $this->ctrl->forwardCommand($mob_gui);
				break;

			// instance image map editing
			case "ilpcimagemapeditorgui":
				require_once("./Services/COPage/classes/class.ilPCImageMapEditorGUI.php");
				$ilTabs->setTabActive("cont_inst_map_areas");
				$image_map_edit = new ilPCImageMapEditorGUI($this->content_obj,
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
	 * Insert new media object form.
	 */
	function insert($a_post_cmd = "edpost", $a_submit_cmd = "create_mob", $a_input_error = false)
	{
		global $ilTabs, $tpl, $ilCtrl, $lng;
		
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
	 * Init  form.
	 *
	 * @param        int        $a_mode        Edit Mode
	 */
	public function initForm($a_mode = "edit")
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		// image file
		$fi = new ilFileInputGUI($lng->txt("cont_file"), "image_file");
		$fi->setSuffixes(array("jpeg", "jpg", "png", "gif"));
		$form->addItem($fi);
		
		// save and cancel commands
		if ($a_mode == "create")
		{
			$form->addCommandButton("create_iim", $lng->txt("save"));
			$form->addCommandButton("cancelCreate", $lng->txt("cancel"));
		}
		else
		{
			$form->addCommandButton("update", $lng->txt("save"));
			$form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
		}
	                
		$form->setTitle($lng->txt("cont_ed_insert_iim"));
		$form->setFormAction($ilCtrl->getFormAction($this));
	 
		return $form;
	}

	/**
	 * Create new content element
	 */
	function create()
	{
		global $ilCtrl, $lng;
		
		$this->content_obj = new ilPCInteractiveImage($this->dom);
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
	 * Align media object to center
	 */
	function centerAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard",
			$this->content_obj->getPcId());
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
			$this->content_obj->getPcId());
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
			$this->content_obj->getPcId());
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
			$this->content_obj->getPcId());
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
			$this->content_obj->getPcId());
		$std_alias_item->setHorizontalAlign("RightFloat");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	 * Add tabs to ilTabsGUI object
	 *
	 * @param	object		$tab_gui		ilTabsGUI object
	 * @param	boolean		$a_create		new creation true/false
	 */
	function getTabs(&$tab_gui, $a_create = false, $a_change_obj_ref = false)
	{
		global $ilCtrl, $ilTabs;

		if (!$a_create)
		{
			$ilTabs->addTarget("cont_mob_inst_prop",
				$ilCtrl->getLinkTarget($this, "editAlias"), "editAlias",
				get_class($this));

			if ($this->getEnabledMapAreas())
			{
				$st_item = $this->content_obj->getMediaObject()->getMediaItem("Standard");
				if (is_object($st_item))
				{
					$format = $st_item->getFormat();
					if (substr($format, 0, 5) == "image")
					{
						$ilTabs->addTarget("cont_inst_map_areas",
							$ilCtrl->getLinkTargetByClass("ilpcimagemapeditorgui", "editMapAreas"), array(),
							"ilpcimagemapeditorgui");
					}
				}
			}
		}
		else
		{

			$ilCtrl->setParameter($this, "subCmd", "insertFromPool");
			$ilTabs->addSubTabTarget("cont_mob_from_media_pool",
				$ilCtrl->getLinkTarget($this, $cmd), $cmd);
			$ilCtrl->setParameter($this, "subCmd", "");
		}
	}

}
?>
