<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for recent changes in wiki
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesMediaPool
*/
class ilMediaPoolTableGUI extends ilTable2GUI
{
	const IL_MEP_SELECT = "select";
	const IL_MEP_EDIT = "edit";
	const IL_MEP_SELECT_CONTENT = "selectc";
	var $insert_command = "create_mob";
	const IL_MEP_SELECT_SINGLE = "selectsingle";
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd,
		$a_media_pool, $a_folder_par = "obj_id",
		$a_mode = ilMediaPoolTableGUI::IL_MEP_EDIT, $a_all_objects = false)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		if ($a_all_objects)
		{
			$this->setId("mepall".$a_parent_obj->object->getId());
		}
		else
		{
			if (is_object($a_parent_obj->object))
			{
				$this->setId("mepfold".$a_parent_obj->object->getId());
			}
		}
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setMode($a_mode);
		$this->all_objects = $a_all_objects;
		$lng->loadLanguageModule("mep");
		
		$this->media_pool = $a_media_pool;
		$this->tree = ilObjMediaPool::getPoolTree($this->media_pool->getId());
		$this->folder_par = $a_folder_par;
		
		if ($this->all_objects)
		{
			$this->setExternalSorting(true);
			$this->initFilter();
		}
		// folder determination
		if ($_GET[$this->folder_par] > 0)
		{
			$this->current_folder = $_GET[$this->folder_par];
		}
		else if ($_SESSION["mep_pool_folder"] > 0  && $this->tree->isInTree($_SESSION["mep_pool_folder"]))
		{
			$this->current_folder = $_SESSION["mep_pool_folder"];
		}
		else
		{
			$this->current_folder = $this->tree->getRootId();
		}
		$_SESSION["mep_pool_folder"] = $this->current_folder;

		$this->addColumn("", "", "1");	// checkbox
		$this->addColumn($lng->txt("mep_thumbnail"), "", "1");
		$this->addColumn($lng->txt("mep_title_and_description"), "", "100%");
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.mep_list_row.html", "Modules/MediaPool");
		$this->getItems();

		// title
		if ($this->current_folder != $this->tree->getRootId() && !$this->all_objects)
		{
			$node = $this->tree->getNodeData($this->current_folder);
			$this->setTitle(
				$lng->txt("mep_choose_from_folder").": ".$node["title"], "icon_fold.svg",
				$node["title"]);
		}
		else
		{
			$this->setTitle(
				$lng->txt("mep_choose_from_mep").": ".
				ilObject::_lookupTitle($this->media_pool->getId()),
				"icon_mep.svg",
				ilObject::_lookupTitle($this->media_pool->getId()));
		}
		
		// action commands
		if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()) &&
			$this->getMode() == ilMediaPoolTableGUI::IL_MEP_EDIT)
		{
			$this->addMultiCommand("copyToClipboard", $lng->txt("cont_copy_to_clipboard"));
			$this->addMultiCommand("confirmRemove", $lng->txt("remove"));
			
			if (!$this->all_objects)
			{
/*				$this->addCommandButton("createFolderForm", $lng->txt("mep_create_folder"));
				$this->addCommandButton("createMediaObject", $lng->txt("mep_create_mob"));
				
				$mset = new ilSetting("mobs");
				if ($mset->get("mep_activate_pages"))
				{
					$this->addCommandButton("createMediaPoolPage", $lng->txt("mep_create_content_snippet"));
				}*/
			}
		}

		if ($this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT_SINGLE)
		{
			// ... even more coupling with ilpcmediaobjectgui
			$this->addMultiCommand("selectObjectReference", $lng->txt("cont_select"));
		}
		
		if ($this->getMode() == ilMediaPoolTableGUI::IL_MEP_EDIT &&
			$ilAccess->checkAccess("write", "", $this->media_pool->getRefId()))
		{
			$this->setSelectAllCheckbox("id");
		}
	}

	/**
	 * Set inser command
	 *
	 * @param	string	inser command
	 */
	function setInsertCommand($a_val)
	{
		$this->insert_command = $a_val;
	}
	
	/**
	 * Get inser command
	 *
	 * @return	string	inser command
	 */
	function getInsertCommand()
	{
		return $this->insert_command;
	}

	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng, $rbacreview, $ilUser;
		
		// title/description
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["title"] = $ti->getValue();
		
		// keyword
		$GLOBALS['lng']->loadLanguageModule('meta');
		$ke = new ilTextInputGUI($lng->txt('meta_keyword'),'keyword');
		$ke->setMaxLength(64);
		$ke->setSize(20);
		$this->addFilterItem($ke);
		$ke->readFromSession();
		$this->filter['keyword'] = $ke->getValue();
		
		// Caption
		$ca = new ilTextInputGUI($lng->txt('cont_caption'),'caption');
		$ca->setMaxLength(64);
		$ca->setSize(20);
		$this->addFilterItem($ca);
		$ca->readFromSession();
		$this->filter['caption'] = $ca->getValue();
		
		// format
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$options = array(
			"" => $lng->txt("mep_all"),
			);
		$formats = $this->media_pool->getUsedFormats();
		$options = array_merge($options, $formats);
		$si = new ilSelectInputGUI($this->lng->txt("mep_format"), "format");
		$si->setOptions($options);
		$this->addFilterItem($si);
		$si->readFromSession();
		$this->filter["format"] = $si->getValue();
		
	}

	/**
	* Set Mode.
	*
	* @param	string	$a_mode	Mode
	*/
	function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}

	/**
	* Get Mode.
	*
	* @return	string	Mode
	*/
	function getMode()
	{
		return $this->mode;
	}

	/**
	* Get items of current folder
	*/
	function getItems()
	{
		if (!$this->all_objects)
		{
			$fobjs = $this->media_pool->getChilds($this->current_folder, "fold");
			$f2objs = array();
			foreach ($fobjs as $obj)
			{
				$f2objs[$obj["title"].":".$obj["child"]] = $obj;
			}
			ksort($f2objs);
			
			// get current media objects / pages
			if ($this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT)
			{
				$mobjs = $this->media_pool->getChilds($this->current_folder, "mob");
			}
			else if ($this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT_CONTENT)
			{
				$mobjs = $this->media_pool->getChilds($this->current_folder, "pg");
			}
			else
			{
				$mobjs = $this->media_pool->getChildsExceptFolders($this->current_folder);
			}
			$m2objs = array();
			foreach ($mobjs as $obj)
			{
				$m2objs[$obj["title"].":".$obj["child"]] = $obj;
			}
			ksort($m2objs);
			
			// merge everything together
			$objs = array_merge($f2objs, $m2objs);
		}
		else
		{
			$objs = $this->media_pool->getMediaObjects(
				$this->filter["title"],
				$this->filter["format"],
				$this->filter['keyword'],
				$this->filter['caption']
			);
		}
//var_dump($objs);
		$this->setData($objs);
	}

	/**
	 * Prepare output
	 *
	 * @param
	 * @return
	 */
	function prepareOutput()
	{
		global $lng;
		
		if ($this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT ||
			$this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT_CONTENT)
		{
			$this->addMultiCommand($this->getInsertCommand(), $lng->txt("insert"));
			$this->addCommandButton("cancelCreate", $lng->txt("cancel"));
		}
	}	

	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilAccess;

		$this->tpl->setCurrentBlock("link");
		
		switch($a_set["type"])
		{
			case "fold":
				$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
				$ilCtrl->setParameter($this->parent_obj, $this->folder_par, $a_set["obj_id"]);
				$this->tpl->setVariable("LINK_VIEW",
					$ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
				$this->tpl->parseCurrentBlock();
				
				if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()) &&
					$this->getMode() == ilMediaPoolTableGUI::IL_MEP_EDIT)
				{
					$this->tpl->setCurrentBlock("edit");
					$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
					$ilCtrl->setParameter($this->parent_obj, $this->folder_par, $a_set["obj_id"]);
					$this->tpl->setVariable("EDIT_LINK",
						$ilCtrl->getLinkTarget($this->parent_obj, "editFolder"));
					$ilCtrl->setParameter($this->parent_obj, $this->folder_par, $_GET[$this->folder_par]);
					$this->tpl->parseCurrentBlock();
				}
				
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("IMG", ilUtil::img(ilUtil::getImagePath("icon_".$a_set["type"].".svg")));
				$ilCtrl->setParameter($this->parent_obj, $this->folder_par, $this->current_folder);
				break;

			case "pg":
				if ($this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT ||
					$this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT_SINGLE)
				{
					$this->tpl->setVariable("TXT_NO_LINK_TITLE", $a_set["title"]);
				}
				else
				{
					$this->tpl->setVariable("ONCLICK", "il.MediaPool.preview('".$a_set["child"]."'); return false;");
					$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
					$ilCtrl->setParameterByClass("ilobjmediapoolgui", "mepitem_id", $a_set["child"]);
				}
				
				if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()) &&
					$this->getMode() == ilMediaPoolTableGUI::IL_MEP_EDIT)
				{
					$this->tpl->setCurrentBlock("edit");
					$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
					$ilCtrl->setParameterByClass("ilmediapoolpagegui", "mepitem_id", $a_set["child"]);
					$this->tpl->setVariable("EDIT_LINK",
						$ilCtrl->getLinkTargetByClass("ilmediapoolpagegui", "edit"));
					$this->tpl->parseCurrentBlock();
				}
				
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("IMG", ilUtil::img(ilUtil::getImagePath("icon_pg.svg")));
				$ilCtrl->setParameter($this->parent_obj, $this->folder_par, $this->current_folder);
				break;

			case "mob":
				if ($this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT ||
					$this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT_SINGLE)
				{
					$this->tpl->setVariable("TXT_NO_LINK_TITLE", $a_set["title"]);
				}
				else
				{
					$this->tpl->setVariable("ONCLICK", "il.MediaPool.preview('".$a_set["child"]."'); return false;");
					$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
					$ilCtrl->setParameterByClass("ilobjmediaobjectgui", "mepitem_id", $a_set["child"]);
					$ilCtrl->setParameter($this->parent_obj, "mob_id", $a_set["foreign_id"]);
				}
					
				// edit link
				if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()) &&
					$this->getMode() == ilMediaPoolTableGUI::IL_MEP_EDIT)
				{
					$this->tpl->setCurrentBlock("edit");
					$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
					$this->tpl->setVariable("EDIT_LINK",
						$ilCtrl->getLinkTargetByClass("ilobjmediaobjectgui", "edit"));
					$this->tpl->parseCurrentBlock();
				}
				
				$this->tpl->setCurrentBlock("link");
				$this->tpl->setCurrentBlock("tbl_content");
				
				// output thumbnail (or mob icon)
				if (ilObject::_lookupType($a_set["foreign_id"]) == "mob")
				{
					$mob = new ilObjMediaObject($a_set["foreign_id"]);
					$med = $mob->getMediaItem("Standard");
					$target = "";
					if ($med)
					{
						$target = $med->getThumbnailTarget();
					}
					if ($target != "")
					{
						$this->tpl->setVariable("IMG", ilUtil::img($target));
					}
					else
					{
						$this->tpl->setVariable("IMG",
							ilUtil::img(ilUtil::getImagePath("icon_".$a_set["type"].".svg")));
					}
					if ($med && ilUtil::deducibleSize($med->getFormat()) &&
						$med->getLocationType() == "Reference")
					{
						$size = @getimagesize($med->getLocation());
						if ($size[0] > 0 && $size[1] > 0)
						{
							$wr = $size[0] / 80;
							$hr = $size[1] / 80;
							$r = max($wr, hr);
							$w = (int) ($size[0]/$r);
							$h = (int) ($size[1]/$r);
							$this->tpl->setVariable("IMG",
								ilUtil::img($med->getLocation(), "", $w, $h));
						}
					}
					
					// output media info
					include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
					$this->tpl->setVariable("MEDIA_INFO",
						ilObjMediaObjectGUI::_getMediaInfoHTML($mob));
					$ilCtrl->setParameter($this->parent_obj, $this->folder_par, $this->current_folder);
				}
				break;
		}

		if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()))
		{
			if (($this->getMode() == ilMediaPoolTableGUI::IL_MEP_EDIT ||
					($this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT && $a_set["type"] == "mob") ||
					($this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT_CONTENT && $a_set["type"] == "pg")
					))
			{
				$this->tpl->setCurrentBlock("chbox");
				$this->tpl->setVariable("CHECKBOX_ID", $a_set["child"]);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("tbl_content");
			}
			else if ($this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT_SINGLE && $a_set["type"] == "mob")
			{
				$this->tpl->setCurrentBlock("radio");
				$this->tpl->setVariable("RADIO_ID", $a_set["child"]);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("tbl_content");
			}
		}
	}

	/**
	 * get HTML
	 *
	 * @param
	 * @return
	 */
	function render()
	{
		global $ilCtrl, $lng;
		
		$mtpl = new ilTemplate("tpl.media_sel_table.html", true, true, "Modules/MediaPool");
		
		$pre = "";
		if ($this->current_folder != $this->tree->getRootId() && !$this->all_objects)
		{
			$path = $this->tree->getPathFull($this->current_folder);

			include_once("./Services/Locator/classes/class.ilLocatorGUI.php");
			$loc = new ilLocatorGUI();
			foreach ($path as $p)
			{
				$ilCtrl->setParameter($this->parent_obj, $this->folder_par, $p["child"]);
				$title = $p["title"];
				if ($this->tree->getRootId() == $p["child"])
				{
					$title = ilObject::_lookupTitle($this->media_pool->getId());
				}
				$loc->addItem(
					$title,
					$ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd)
					);
			}
			$ilCtrl->setParameter($this->parent_obj, $this->folder_par,
				$this->current_folder);
			
			$mtpl->setCurrentBlock("loc");
			$mtpl->setVariable("LOC", $loc->getHTML());
			$mtpl->parseCurrentBlock();
		}

		$mtpl->setVariable("TABLE", parent::render());
		
		return $mtpl->get();
	}
}
?>
