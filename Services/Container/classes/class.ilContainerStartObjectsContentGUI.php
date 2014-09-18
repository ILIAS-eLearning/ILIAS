<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContainerStartObjectsContentGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * $Id: class.ilObjCourseGUI.php 47058 2014-01-08 08:07:12Z mjansen $
 *
 * @ingroup ServicesContainer
 */
class ilContainerStartObjectsContentGUI 
{	
	protected $start_object; // [ilContainerStartObjects]	
	protected $enable_desktop; // [bool]
	protected $parent_gui; // [ilContainerGUI]
	
	/**
	 * Constructor
	 * 
	 * @param ilContainer $a_parent_obj
	 */
	public function __construct(ilContainer $a_parent_obj)
	{			
		include_once "Services/Container/classes/class.ilContainerStartObjects.php";
		$this->start_object = new ilContainerStartObjects($a_parent_obj->getRefId(),
			$a_parent_obj->getId());			
	}
	
	/**
	 * Toggle add-to/remove-from-desktop
	 * 
	 * @param bool $a_value
	 * @param ilContainerGUI $a_parent_gui
	 */
	public function enableDesktop($a_value, ilContainerGUI $a_parent_gui)
	{
		$this->enable_desktop = (bool)$a_value;		
		if($this->enable_desktop)
		{
			$this->parent_gui = $a_parent_gui;
		}
	}	
	
	/**
	 * Get container start objects list (presentation)
	 * 
	 * @return string
	 */
	public function getHTML()
	{		
		global $tpl, $lng;
		
		$lng->loadLanguageModule("crs");
		
		include_once "Services/Container/classes/class.ilContainerStartObjectsContentTableGUI.php";
		$tbl = new ilContainerStartObjectsContentTableGUI($this->parent_gui, "", 
			$this->start_object, $this->enable_desktop);
		$tpl->setContent(
			$this->getPageHTML().
			$tbl->getHTML()
		);		
	}
	
	/**
	 * Render COPage
	 * 
	 * @see ilContainerGUI
	 * @return string
	 */
	protected function getPageHTML()
	{
		global $tpl, $ilSetting, $ilUser;
		
		if (!$ilSetting->get("enable_cat_page_edit"))
		{
			return;
		}
		
		$page_id = $this->start_object->getObjId();
		
		// if page does not exist, return nothing
		include_once("./Services/COPage/classes/class.ilPageUtil.php");
		if (!ilPageUtil::_existsAndNotEmpty("cstr", $page_id))
		{
			return;
		}
		
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();

		include_once("./Services/Container/classes/class.ilContainerStartObjectsPageGUI.php");	
		$page_gui = new ilContainerStartObjectsPageGUI($page_id);
		
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$page_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(0));

		$page_gui->setPresentationTitle("");
		$page_gui->setTemplateOutput(false);
		$page_gui->setHeader("");
		return $page_gui->showPage();
	}
}

?>