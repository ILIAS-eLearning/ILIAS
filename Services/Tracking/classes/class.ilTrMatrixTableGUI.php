<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");

/**
 * name table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilTrMatrixTableGUI extends ilLPTableBaseGUI
{
	protected $obj_ids = NULL;
	protected $objective_ids = NULL;

	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $ref_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $ilObjDataCache;

		$this->setId("trsmtx");
		$this->ref_id = $ref_id;
		$this->obj_id = ilObject::_lookupObjId($ref_id);

		$this->initFilter();

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setLimit(9999);
	
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormActionByClass(get_class($this)));
		$this->setRowTemplate("tpl.user_object_matrix_row.html", "Services/Tracking");
		$this->setDefaultOrderField("login");
		$this->setDefaultOrderDirection("asc");

		$this->addColumn($this->lng->txt("login"), "login");


		$labels = $this->getSelectableColumns();
		$selected = $this->getSelectedColumns();
		foreach ($selected as $c)
		{
			$title = $labels[$c]["txt"];
			$tooltip = "";
			if(isset($labels[$c]["icon"]))
			{
				$alt = $lng->txt($labels[$c]["type"]);
				$icon = '<img src="'.$labels[$c]["icon"].'" alt="'.$alt.'" title="'.$alt.'" />';
				if(sizeof($selected) > 5)
				{
					$tooltip = $title;
					$title = $icon;
				}
				else
				{
					$title = $icon.' '.$title;
				}
			}
			$this->addColumn($title, $labels[$c]["id"], "", false, "", $tooltip);
		}
	}

	function initFilter()
    {
		global $lng;

		$item = $this->addFilterItemByMetaType("name", ilTable2GUI::FILTER_TEXT);
		$this->filter["name"] = $item->getValue();
	}

	function getSelectableColumns()
	{
		global $ilObjDataCache;
		
		$columns = array();

		if($this->obj_ids === NULL)
		{
			$this->obj_ids = $this->getItems();
		}
		if($this->obj_ids)
		{
			foreach($this->obj_ids as $obj_id)
			{
				if($obj_id == $this->obj_id)
				{
					$parent = array("txt" => $this->lng->txt("status"),
						"default" => true);
				}
				else
				{
					$title = $ilObjDataCache->lookupTitle($obj_id);
					$type = $ilObjDataCache->lookupType($obj_id);
					$icon = ilUtil::getTypeIconPath($type, $obj_id, "small");
					if(!$title)
					{
						if($type == "sess")
						{
							include_once "Modules/Session/classes/class.ilObjSession.php";
							$sess = new ilObjSession($obj_id, false);
							$title = $sess->getPresentationTitle();
						}
					}
					$columns["obj_".$obj_id] = array("txt" => $title, "icon" => $icon, "type" => $type, "default" => true);
				}
			}
			if(sizeof($this->objective_ids))
			{
				foreach($this->objective_ids as $obj_id => $title)
				{
					$columns["objtv_".$obj_id] = array("txt" => $title, "default" => true);
				}
			}

			if($parent)
			{
				$columns["obj_".$this->obj_id] = $parent;
			}
		}

		$columns["last_access"] = array("txt" => $this->lng->txt("last_access"), 
			"id" => "last_access",
			"default" => false);
		$columns["spent_seconds"] = array("txt" => $this->lng->txt("trac_spent_seconds"), 
			"id" => "spent_seconds",
			"default" => false);
		
		return $columns;
	}

	function getItems()
	{
		global $lng, $tree;

		// $this->determineOffsetAndOrder();

		include_once("./Services/Tracking/classes/class.ilTrQuery.php");
		$collection = ilTrQuery::getObjectIds($this->obj_id, $this->ref_id, true);
		if($collection["object_ids"])
		{
			$data = ilTrQuery::getUserObjectMatrix($this->obj_id, $collection["object_ids"], $this->filter["name"]);

			if($collection["objectives_parent_id"] && $data["users"])
			{
				$objectives = ilTrQuery::getUserObjectiveMatrix($collection["objectives_parent_id"], $data["users"]);
				if($objectives["cnt"])
				{
					$objective_columns = array();
					foreach($objectives["set"] as $row)
					{
						if(isset($data["set"][$row["usr_id"]]))
						{
							$obj_id = "objtv_".$row["obj_id"];
							$data["set"][$row["usr_id"]]["objects"][$obj_id] = array("status"=>$row["status"]);

							if(!in_array($obj_id, $this->objective_ids))
							{
								$this->objective_ids[$obj_id] = $row["title"];
							}
						}
					}
				}
			}

			$this->setMaxCount($data["cnt"]);
			$this->setData($data["set"]);

			return $collection["object_ids"];
		}
		return false;
	}

	function fillRow(array $a_set)
	{
		$this->tpl->setVariable("VAL_LOGIN", $a_set["login"]);

		foreach ($this->getSelectedColumns() as $c)
		{
			switch($c)
			{
				case "last_access":
				case "spent_seconds":
					$this->tpl->setCurrentBlock($c);
					$this->tpl->setVariable("VAL_".strtoupper($c), $this->parseValue($c, $a_set[$c], ""));
					$this->tpl->parseCurrentBlock();
					break;

				case (substr($c, 0, 4) == "obj_"):
					$obj_id = substr($c, 4);
					if(!isset($a_set["objects"][$obj_id]))
					{
						$data = array("status"=>0);
					}
					else
					{
						$data = $a_set["objects"][$obj_id];
						if($data["percentage"] == "0")
						{
							$data["percentage"] = NULL;
						}
					}
					$this->tpl->setCurrentBlock("objects");
					$this->tpl->setVariable("VAL_STATUS", $this->parseValue("status", $data["status"], ""));
					$this->tpl->setVariable("VAL_PERCENTAGE", $this->parseValue("percentage", $data["percentage"], ""));
					$this->tpl->parseCurrentBlock();
					break;


				case (substr($c, 0, 6) == "objtv_"):
					$obj_id = substr($c, 6);
					if(!isset($a_set["objects"][$obj_id]))
					{
						$data = array("status"=>0);
					}
					else
					{
						$data = $a_set["objects"][$obj_id];
					}
					$this->tpl->setCurrentBlock("objects");
					$this->tpl->setVariable("VAL_STATUS", $this->parseValue("status", $data["status"], ""));
					$this->tpl->parseCurrentBlock();
					break;
			}
		}
	}
}

?>