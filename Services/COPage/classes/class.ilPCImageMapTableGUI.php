<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");
include_once("Services/MediaObjects/classes/class.ilImageMapTableGUI.php");

/**
* TableGUI class for pc image map editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCImageMapTableGUI extends ilImageMapTableGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_pc_media_object,
		$a_parent_node_name)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->access = $DIC->access();

		$this->parent_node_name = $a_parent_node_name;
		$this->pc_media_object = $a_pc_media_object;
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_pc_media_object->getMediaObject());
	}

	/**
	* Get items of current folder
	*/
	function getItems()
	{
		$std_alias_item = new ilMediaAliasItem($this->pc_media_object->dom,
			$this->pc_media_object->hier_id, "Standard", $this->pc_media_object->getPcId(),
			$this->parent_node_name);
		$areas = $std_alias_item->getMapAreas();

		foreach ($areas as $k => $a)
		{
			$areas[$k]["title"] = $a["Link"]["Title"];
		}
		$areas = ilUtil::sortArray($areas, "title", "asc", false, true);
		$this->setData($areas);
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilAccess = $this->access;

		$i = $a_set["Nr"];
		$this->tpl->setVariable("CHECKBOX",
			ilUtil::formCheckBox("", "area[]", $i));
		$this->tpl->setVariable("VAR_NAME", "name_".$i);
		$this->tpl->setVariable("VAL_NAME", trim($a_set["Link"]["Title"]));
		$this->tpl->setVariable("VAL_SHAPE", $a_set["Shape"]);
		
		$this->tpl->setVariable("VAL_HIGHL_MODE",
			ilUtil::formSelect($a_set["HighlightMode"], "hl_mode_".$i,
				$this->highl_modes, false, true));
		$this->tpl->setVariable("VAL_HIGHL_CLASS",
			ilUtil::formSelect($a_set["HighlightClass"], "hl_class_".$i,
				$this->highl_classes, false, true));
		
		$this->tpl->setVariable("VAL_COORDS",
			implode(explode(",", $a_set["Coords"]), ", "));
		switch ($a_set["Link"]["LinkType"])
		{
			case "ExtLink":
				$this->tpl->setVariable("VAL_LINK", $a_set["Link"]["Href"]);
				break;

			case "IntLink":
				$link_str = $this->parent_obj->getMapAreaLinkString($a_set["Link"]["Target"],
					$a_set["Link"]["Type"], $a_set["Link"]["TargetFrame"]);
				$this->tpl->setVariable("VAL_LINK", $link_str);
				break;
		}
	}

}
?>
