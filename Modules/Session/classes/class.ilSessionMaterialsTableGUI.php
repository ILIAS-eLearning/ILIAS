<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');
/**
 * Session data set class
 *
 * @author Fabian Wolf <wolf@leifos.com>
 * @version $Id$
 * @ingroup ingroup ModulesSession
 */
class ilSessionMaterialsTableGUI extends ilTable2GUI
{

	protected $container_ref_id;
	protected $material_items;

	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $tree;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->parent_ref_id = $tree->getParentId($a_parent_obj->object->getRefId());

		//$this->setEnableNumInfo(false);
		//$this->setLimit(100);
		$this->setRowTemplate("tpl.session_materials_row.html","Modules/Session");

		$this->setFormName('materials');
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj,$a_parent_cmd));
		$this->addCommandButton("saveMaterials", $lng->txt("save"));

		$this->addColumn("", "f", 1);
		$this->addColumn($lng->txt("crs_materials"), "object", "90%" );
		$this->addColumn($lng->txt("status"), "active", 5);
		$this->setSelectAllCheckbox('items');
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

			$node["sorthash"] = (int)(!in_array($node['ref_id'],$this->getMaterialItems())).$node["title"];
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
		$this->tpl->setVariable('TYPE_IMG', ilObject::_getIcon('', 'tiny', $a_set['type']));
		$this->tpl->setVariable('IMG_ALT',$this->lng->txt('obj_'.$a_set['type']));

		$this->tpl->setVariable("VAL_POSTNAME","items");
		$this->tpl->setVariable("VAL_ID",$a_set['ref_id']);

		if(in_array($a_set['ref_id'],$this->getMaterialItems()))
		{
			$this->tpl->setVariable("VAL_CHECKED","checked");
		}

		$this->tpl->setVariable("COLL_TITLE",$a_set['title']);

		if(strlen($a_set['description']))
		{
			$this->tpl->setVariable("COLL_DESC",$a_set['description']);
		}
		$this->tpl->setVariable("ASSIGNED_IMG_OK",in_array($a_set['ref_id'],$this->getMaterialItems()) ?
			ilUtil::getImagePath('icon_ok.svg') :
			ilUtil::getImagePath('icon_not_ok.svg'));
		$this->tpl->setVariable("ASSIGNED_STATUS",$this->lng->txt('event_material_assigned'));

		include_once('./Services/Tree/classes/class.ilPathGUI.php');
		$path = new ilPathGUI();
		$path->enableDisplayCut(true);
		$path->enableTextOnly(false);
		$this->tpl->setVariable("PATH",$this->lng->txt('path'));
		$this->tpl->setVariable("COLL_PATH",$path->getPath($this->getContainerRefId(), $a_set['ref_id']));
	}

	/**
	 * Set Material Items
	 * @param $a_set
	 */
	public function setMaterialItems($a_set)
	{
		$this->material_items = $a_set;
	}

	/**
	 * Get Material Items
	 * @return mixed
	 */
	public function getMaterialItems()
	{
		return $this->material_items;
	}

	/**
	 * Set Mcontainer ref id
	 * @param $a_set
	 */
	public function setContainerRefId($a_set)
	{
		$this->container_ref_id = $a_set;
	}

	/**
	 * Get container ref id
	 * @return mixed
	 */
	public function getContainerRefId()
	{
		return $this->container_ref_id;
	}

}