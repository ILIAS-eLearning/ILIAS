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
	protected $obj_ids = array();
	protected $objective_ids = array();

	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $ref_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $ilObjDataCache;

		$this->setId("trsmtx");
		$this->ref_id = $ref_id;
		$this->obj_id = ilObject::_lookupObjId($ref_id);

		parent::__construct($a_parent_obj, $a_parent_cmd);
		// $this->setTitle($lng->txt("tr_summary"));
		$this->setLimit(9999);
		// $this->setShowTemplates(true);

		// $this->setExternalSorting(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormActionByClass(get_class($this)));
		$this->setRowTemplate("tpl.user_object_matrix_row.html", "Services/Tracking");
		$this->setDefaultOrderField("login");
		$this->setDefaultOrderDirection("asc");

		// $this->initFilter($a_parent_obj->getObjId());

		$this->obj_ids = $this->getItems();

		$this->addColumn($this->lng->txt("login"), "login", "10%");

		if($this->obj_ids)
		{
			$cnt = 0;
			$width = floor(70/(sizeof($this->obj_ids)+sizeof($this->objective_ids)));
			foreach($this->obj_ids as $obj_id)
			{
				if(!$cnt)
				{
					$this->addColumn($this->lng->txt("status"), "", $width."%");
				}
				else
				{
					$title = $ilObjDataCache->lookupTitle($obj_id);
					if(!$title)
					{
						$type = $ilObjDataCache->lookupType($obj_id);
						if($type == "sess")
						{
							include_once "Modules/Session/classes/class.ilObjSession.php";
							$sess = new ilObjSession($obj_id, false);
							$title = $sess->getPresentationTitle();
						}
					}
					if($width < 10)
					{
						$this->addColumn($cnt, "", $width."%", false, "", $title);
					}
					else
					{
						$this->addColumn($title, "", $width."%");
					}
				}
				$cnt++;
			}
			if(sizeof($this->objective_ids))
			{
				foreach($this->objective_ids as $obj_id => $title)
				{
					if($width < 10)
					{
						$this->addColumn($cnt, "", $width."%", false, "", $title);
					}
					else
					{
						$this->addColumn($title, "", $width."%");
					}
					$cnt++;
				}
			}
		}

		$this->addColumn($this->lng->txt("last_access"), "last_access", "10%");
		$this->addColumn($this->lng->txt("trac_spent_seconds"), "spent_seconds", "10%");
	}

	function getItems()
	{
		global $lng, $tree;

		$this->determineOffsetAndOrder();

		include_once("./Services/Tracking/classes/class.ilTrQuery.php");
		$collection = ilTrQuery::getObjectIds($this->obj_id, $this->ref_id, true);
		if($collection["object_ids"])
		{
			$data = ilTrQuery::getUserObjectMatrix($this->obj_id, $collection["object_ids"]);

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
	}

	function fillRow(array $a_set)
	{
		$this->tpl->setVariable("VAL_LOGIN", $a_set["login"]);
		$this->tpl->setVariable("VAL_LAST_ACCESS", $this->parseValue("last_access", $a_set["last_access"], ""));
		$this->tpl->setVariable("VAL_SPENT_SECONDS", $this->parseValue("spent_seconds", $a_set["spent_seconds"], ""));

		$this->tpl->setCurrentBlock("objects");
		foreach($this->obj_ids as $obj_id)
		{
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
			$this->tpl->setVariable("VAL_STATUS", $this->parseValue("status", $data["status"], ""));
			$this->tpl->setVariable("VAL_PERCENTAGE", $this->parseValue("percentage", $data["percentage"], ""));
			$this->tpl->parseCurrentBlock();
		}

		if(sizeof($this->objective_ids))
		{
			foreach(array_keys($this->objective_ids) as $obj_id)
			{
				if(!isset($a_set["objects"][$obj_id]))
				{
					$data = array("status"=>0);
				}
				else
				{
					$data = $a_set["objects"][$obj_id];
				}
				$this->tpl->setVariable("VAL_STATUS", $this->parseValue("status", $data["status"], ""));
				$this->tpl->parseCurrentBlock();
			}
		}
	}
}

?>