<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');
/**
 * Extended data table
 */
class ilSessionMaterialsTableGUI extends ilTable2GUI
{

	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $tree;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->parent_ref_id = $tree->getParentId($a_parent_obj->object->getRefId());
		$this->material_items = $a_parent_obj->event_items->getItems();

		$this->setTitle($lng->txt("event_assign_materials_table"));
		$this->setDescription($this->lng->txt('event_assign_materials_info'));
		$this->setEnableNumInfo(false);
		$this->setRowTemplate("tpl.session_materials_row.html","Modules/Session");

		$this->setId("sess_materials_". $a_parent_obj->object->getId());
		$this->setFormName('materials');
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj,$a_parent_cmd));
		$this->addCommandButton("saveMaterials", $lng->txt("save"));

		$this->addColumn("", "f", 1);
		$this->addColumn($lng->txt("crs_materials"), "object", "90%" );
		$this->addColumn($lng->txt("status"), "active", 5);
		$this->setSelectAllCheckbox('items');

		$this->getDataFromDb();
	}

	/**
	 * Get data and put it into an array
	 */
	function getDataFromDb()
	{
		global $tree, $objDefinition;


		$nodes = $tree->getSubTree($tree->getNodeData($this->parent_ref_id));
		$materials = array();

		foreach($nodes as $node)
		{
			// No side blocks here
			if ($node['child'] == $this->parent_ref_id ||
				$objDefinition->isSideBlock($node['type']) ||
				in_array($node['type'], array('sess', 'itgr', 'rolf')))
			{
				continue;
			}

			if($node['type'] == 'rolf')
			{
				continue;
			}

			$node["sorthash"] = (int)(!in_array($node['ref_id'],$this->material_items)).$node["title"];
			$materials[] = $node;
		}

		$materials = ilUtil::sortArray($materials, "sorthash", "ASC");
		$this->setData($materials);
	}

	/**
	 * Fill a single data row.
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$this->tpl->setVariable('TYPE_IMG', ilObject::_getIcon('', 'tiny', $a_set['type']));
		$this->tpl->setVariable('IMG_ALT',$this->lng->txt('obj_'.$a_set['type']));

		$this->tpl->setVariable("VAL_POSTNAME","items");
		$this->tpl->setVariable("VAL_ID",$a_set['ref_id']);

		if(in_array($a_set['ref_id'],$this->material_items))
		{
			$this->tpl->setVariable("VAL_CHECKED","checked");
		}

		$this->tpl->setVariable("COLL_TITLE",$a_set['title']);

		if(strlen($a_set['description']))
		{
			$this->tpl->setVariable("COLL_DESC",$a_set['description']);
		}
		$this->tpl->setVariable("ASSIGNED_IMG_OK",in_array($a_set['ref_id'],$this->material_items) ?
			ilUtil::getImagePath('icon_ok.png') :
			ilUtil::getImagePath('icon_not_ok.png'));
		$this->tpl->setVariable("ASSIGNED_STATUS",$this->lng->txt('event_material_assigned'));

		include_once('./Services/Tree/classes/class.ilPathGUI.php');
		$path = new ilPathGUI();
		$path->enableDisplayCut(true);
		$path->enableTextOnly(false);
		$this->tpl->setVariable("PATH",$this->lng->txt('path'));
		$this->tpl->setVariable("COLL_PATH",$path->getPath($this->getParentObject()->getContainerRefId(), $a_set['ref_id']));
	}

}