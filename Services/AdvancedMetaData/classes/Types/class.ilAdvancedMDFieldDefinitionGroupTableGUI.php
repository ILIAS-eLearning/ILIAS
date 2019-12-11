<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/Table/classes/class.ilTable2GUI.php');

/** 
 * Table GUI for complex AdvMD options
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesAdvancedMetaData 
 */
class ilAdvancedMDFieldDefinitionGroupTableGUI extends ilTable2GUI
{	
	protected $def; // [ilAdvancedMDFieldDefinition]
	
	public function __construct($a_parent_obj, $a_parent_cmd = "", ilAdvancedMDFieldDefinition $a_def)
	{
	 	global $lng,$ilCtrl;
		
		$this->def = $a_def;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
	 	$this->addColumn($lng->txt("option"), "option");
		
		foreach($this->def->getTitles() as $element => $title)
		{
			$this->addColumn($title, $element);
		}
		
		$this->addColumn($lng->txt("action"), "");
	 	
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.edit_complex_row.html", "Services/AdvancedMetaData");
		$this->setDefaultOrderField("option");
		
		$this->initItems($a_def);
	}
	
	protected function initItems(ilAdvancedMDFieldDefinition $a_def)
	{
		$data = array();
		
		foreach($a_def->getOptions() as $option)
		{
			$item = array("option" => $option);
			
			$a_def->exportOptionToTableGUI($option, $item);
			
			$data[] = $item;
		}
		
		$this->setData($data);
	}
	
	public function fillRow($a_set)
	{
		global $lng, $ilCtrl;
		
		$this->tpl->setVariable("OPTION", $a_set["option"]);
		
		$this->tpl->setCurrentBlock("field_bl");
		foreach(array_keys($this->def->getTitles()) as $element)
		{			
			$this->tpl->setVariable("FIELD", trim($a_set[$element]));
			$this->tpl->parseCurrentBlock();
		}
		
		// action
		$ilCtrl->setParameter($this->getParentObject(), "oid", md5($a_set["option"]));
		$url = $ilCtrl->getLinkTarget($this->getParentObject(), "editComplexOption");
		$ilCtrl->setParameter($this->getParentObject(), "oid", "");
		
		$this->tpl->setVariable("ACTION_URL", $url);
		$this->tpl->setVariable("ACTION_TXT", $lng->txt("edit"));
		
	}
}

?>