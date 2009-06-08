<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for search results
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesSearch
*/
class ilSearchResultTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_presenter)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->presenter = $a_presenter;
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($lng->txt("search_results"));
		$this->setLimit(9999);
		
		//$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("type"), "", "1");
		$this->addColumn($this->lng->txt("search_title_description"), "", "80%");
		$this->addColumn($this->lng->txt("actions"), "", "20%");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.search_result_row.html", "Services/Search");
		//$this->disable("footer");
		$this->setEnableTitle(true);
		$this->setEnableNumInfo(false);
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng;
		
		$obj_id = $a_set["obj_id"];
		$ref_id = $a_set["ref_id"];
		$type = ilObject::_lookupType($obj_id);
		$title = $this->presenter->lookupTitle($obj_id,0);
		$description = $this->presenter->lookupDescription($obj_id,0);
		
		if(!$type)
		{
			return false;
		}
		
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchObjectListGUIFactory.php';
		$item_list_gui = ilLuceneSearchObjectListGUIFactory::factory($type);
		
		$item_list_gui->setContainerObject($this->parent_obj);
		$item_list_gui->setSearchFragment($this->presenter->lookupContent($obj_id,0));
		$item_list_gui->setSeparateCommands(true);
		$this->presenter->appendAdditionalInformation($item_list_gui,$ref_id,$obj_id,$type);
		
		if($html = $item_list_gui->getListItemHTML($ref_id,$obj_id,$title,$description))
		{				
			$item_html[$ref_id]['html'] = $html;
			$item_html[$ref_id]['type'] = $type;
		}

		$this->tpl->setVariable("ITEM_HTML", $html);
		$this->tpl->setVariable("ACTION_HTML", $item_list_gui->getCommandsHTML());
		$this->tpl->setVariable("TYPE_IMG", ilUtil::img(ilUtil::getImagePath("icon_$type.gif"),
			$lng->txt("icon")." ".$lng->txt("obj_".$type)));
	}

}
?>
