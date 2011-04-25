<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for interactive image overlays
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilPCIIMOverlaysTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_mob)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->mob = $a_mob;
		$this->setData($this->getOverlays());
		$this->setTitle($lng->txt("cont_overlay_images"));
		
		$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("filename"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.iim_overlays_row.html", "Services/COPage");

		$this->addMultiCommand("", $lng->txt(""));
		$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	 * Get overlays
	 *
	 * @return array array of overlays
	 */
	function getOverlays()
	{
		$ov = array();
		$files = $this->mob->getFilesOfDirectory("overlays");
		foreach ($files as $f)
		{
			$ov[] = array("filename" => $f);
		}
		return $ov;
	}
	
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("FILENAME", $a_set["filename"]);
	}

}
?>
