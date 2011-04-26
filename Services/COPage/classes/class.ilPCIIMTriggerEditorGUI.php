<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPCImageMapEditorGUI.php");

/**
* User interface class for page content map editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPCIIMTriggerEditorGUI: ilInternalLinkGUI
*
* @ingroup ServicesCOPage
*/
class ilPCIIMTriggerEditorGUI extends ilPCImageMapEditorGUI
{
	/**
	* Constructor
	*/
	function __construct($a_content_obj, $a_page)
	{
		parent::__construct($a_content_obj, $a_page);
	}
	
	/**
	 * Get parent node name
	 *
	 * @return string name of parent node
	 */
	function getParentNodeName()
	{
		return "InteractiveImage";
	}

	/**
	 * Get editor title
	 *
	 * @return string editor title
	 */
	function getEditorTitle()
	{
		global $lng;
		
		return $lng->txt("cont_pc_iim");
	}

	/**
	 * Get trigger table
	 */
	function getImageMapTableHTML()
	{
		include_once("./Services/COPage/classes/class.ilPCIIMTriggerTableGUI.php");
		$image_map_table = new ilPCIIMTriggerTableGUI($this, "editMapAreas", $this->content_obj,
			$this->getParentNodeName());
		return $image_map_table->getHTML();
	}

	/**
	 * Get toolbar
	 *
	 * @return object toolbar
	 */
	function getToolbar()
	{
		global $ilCtrl, $lng;
		
		// toolbar
		$tb = new ilToolbarGUI();
		$tb->setFormAction($ilCtrl->getFormAction($this));
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$options = array(
			"Rect" => $lng->txt("cont_Rect"),
			"Circle" => $lng->txt("cont_Circle"),
			"Poly" => $lng->txt("cont_Poly"),
			);
		$si = new ilSelectInputGUI($lng->txt("cont_shape"), "shape");
		$si->setOptions($options);
		$tb->addInputItem($si, true);
		$tb->addFormButton($lng->txt("cont_add_shape_trigger"), "addNewArea");
		$tb->addSeparator();
		$tb->addFormButton($lng->txt("cont_add_marker_trigger"), "addMarker");
		
		return $tb;
	}

	/**
	 * Delete trigger
	 */
	function deleteTrigger()
	{
		global $ilCtrl, $lng;
		
		if (!isset($_POST["area"]))
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "editMapAreas");
		}

//		$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//			$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());

		if (count($_POST["area"]) > 0)
		{
			$i = 0;

			foreach ($_POST["area"] as $area_nr)
			{
				$this->std_alias_item->deleteMapArea($area_nr - $i);
				$i++;
			}
			$this->updated = $this->page->update();
			ilUtil::sendSuccess($lng->txt("cont_areas_deleted"), true);
		}

		$ilCtrl->redirect($this, "editMapAreas");
	}

}
?>