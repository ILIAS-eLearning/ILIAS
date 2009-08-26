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
		$this->setLimit(999);
		
		//$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("type"), "type", "1");
		$this->addColumn($this->lng->txt("search_title_description"), "title");
		if($this->enabledRelevance())
		{
			$this->addColumn($this->lng->txt('lucene_relevance_short'),'s_relevance','50px');
			$this->setDefaultOrderField("s_relevance");
			$this->setDefaultOrderDirection("desc");
		}
		$this->addColumn('', "", "10px");
		
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
		$type 	= $a_set['type'];
		$title 	= $a_set['title'];
		$description = $a_set['description'];
		$relevance = $a_set['relevance'];
		
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
		
		global $lng;
		
		if($this->enabledRelevance())
		{
			$width1 = (int) ((int) $relevance / 2);
			$width2 = (int) ((50 - $width1));
			
			$this->tpl->setCurrentBlock('relev');
			$this->tpl->setVariable('VAL_REL',sprintf("%d %%",$relevance));
			$this->tpl->setVariable('WIDTH_A',$width1);
			$this->tpl->setVariable('WIDTH_B',$width2);
			$this->tpl->setVariable('IMG_A',ilUtil::getImagePath("relevance_blue.gif"));
			$this->tpl->setVariable('IMG_B',ilUtil::getImagePath("relevance_dark.gif"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("ITEM_HTML", $html);
		$this->tpl->setVariable("ACTION_HTML", $item_list_gui->getCommandsHTML());
		$this->tpl->setVariable("TYPE_IMG", ilUtil::img(ilUtil::getImagePath("icon_$type.gif"),
			$lng->txt("icon")." ".$lng->txt("obj_".$type)));
	}
	
	/**
	 * Check if relevance is visible
	 * @return 
	 */
	protected function enabledRelevance()
	{
		return ilSearchSettings::getInstance()->enabledLucene() and ilSearchSettings::getInstance()->isRelevanceVisible();
	}

}
?>
