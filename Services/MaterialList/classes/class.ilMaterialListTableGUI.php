<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Course material table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesMaterialList
 */
class ilMaterialListTableGUI extends ilTable2GUI
{			
	protected $read_only; // [bool]
	
	function __construct($a_parent_obj, $a_parent_cmd, $a_crs_id, $a_read_only, $a_add_new = false, array $a_post = null)
	{
		global $ilCtrl, $lng;
		
		$this->read_only = (bool)$a_read_only;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setId("crsmlst");		
		$this->setLimit(9999);
		$this->setTitle($lng->txt("matlist_tab"));
		
		if(!$this->read_only)
		{
			$this->addColumn("", "");
		}
		else
		{
			$a_add_new = false;
			$a_post = null;
		}
		
		$this->addColumn($lng->txt("matlist_participants_count"), "pcnt");
		$this->addColumn($lng->txt("matlist_course_count"), "ccnt");
		$this->addColumn($lng->txt("matlist_product_id"), "prod");
		$this->addColumn($lng->txt("matlist_title"), "title");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.course_material_row.html", "Services/MaterialList");
		
		$this->getItems($a_crs_id, $a_add_new, $a_post);

		if(!$this->read_only &&
			sizeof($this->row_data))
		{
			$this->addCommandButton("updateMaterial", $lng->txt("save"));		
			$this->addMultiCommand("confirmMaterialDelete", $lng->txt("delete"));					
		}			
	}
	
	protected function getItems($a_crs_id, $a_add_new = false, array $a_post = null)
	{
		$data = array();
			
		if($a_post)
		{
			foreach($a_post["data"] as $id => $item)
			{
				$data[] = array("id" => $id, 
					"pcnt" => $item["pcnt"], 
					"ccnt" => $item["ccnt"], 
					"prod" => $item["prod"], 
					"title" => $item["title"],					
					"err_pcnt" => in_array("pcnt", (array)$a_post["errors"][$id]), 
					"err_ccnt" => in_array("ccnt", (array)$a_post["errors"][$id]), 
					"err_prod" => in_array("prod", (array)$a_post["errors"][$id]), 
					"err_title" => in_array("title", (array)$a_post["errors"][$id]));
				
				if($id == -1)
				{
					$a_add_new = false;
				}
			}			
		}
		else
		{
			include_once "Services/MaterialList/classes/class.ilMaterialList.php";
			foreach(ilMaterialList::getRawList($a_crs_id) as $id => $item)
			{
				$data[] = array("id" => $id, 
					"pcnt" => $item["amount_per_participant"], 
					"ccnt" => $item["amount_per_course"], 
					"prod" => $item["item_number"], 
					"title" => $item["description"]);
			}						
		}
		if($a_add_new)
		{
			for($loop = 1; $loop <= $a_add_new; $loop++)
			{
				$data[] = array("id" => -$loop, 
					"pcnt" => "", 
					"ccnt" => "", 
					"prod" => "", 
					"title" => "");			
			}
		}
		
		$this->setData($data);
	}
	
	protected function fillRow($a_set)
	{		
		if(!$this->read_only)
		{
			$this->tpl->setCurrentBlock("edit_bl");
			$this->tpl->setVariable("ID", $a_set["id"]);
			$this->tpl->setVariable("PART_COUNT", $a_set["pcnt"]);
			$this->tpl->setVariable("CRS_COUNT", $a_set["ccnt"]);
			$this->tpl->setVariable("PROD_ID", $a_set["prod"]);
			$this->tpl->setVariable("TITLE", $a_set["title"]);

			// validation
			if($a_set["err_pcnt"])
			{
				$this->tpl->setVariable("PART_COUNT_STYLE", " style=\"border:1px solid red;\"");
			}
			if($a_set["err_ccnt"])
			{
				$this->tpl->setVariable("CRS_COUNT_STYLE", " style=\"border:1px solid red;\"");
			}
			if($a_set["err_prod"])
			{
				$this->tpl->setVariable("PROD_ID_STYLE", " style=\"border:1px solid red;\"");
			}
			if($a_set["err_title"])
			{
				$this->tpl->setVariable("TITLE_STYLE", " style=\"border:1px solid red;\"");
			}						
		}
		else
		{
			$this->tpl->setCurrentBlock("view_bl");
			$this->tpl->setVariable("PART_COUNT_VIEW", $a_set["pcnt"]);
			$this->tpl->setVariable("CRS_COUNT_VIEW", $a_set["ccnt"]);
			$this->tpl->setVariable("PROD_ID_VIEW", $a_set["prod"]);
			$this->tpl->setVariable("TITLE_VIEW", $a_set["title"]);
		}
		
		$this->tpl->parseCurrentBlock();
	}
}
