<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * LM Chapter
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id\$
 * @ingroup 
 */
class ilLMChapterImportForm
{
	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	protected $tree = null;
	protected $node_id = null;
	protected $confirm = false;
	protected $perform = false;

	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_slm, $a_node_id, $a_first_child, $a_confirm = false)
	{
		global $DIC;

		$this->user = $DIC->user();
		$this->lng = $DIC->language();
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Tree.php");
		$this->slm = $a_slm;
		$this->tree = new ilSCORM2004Tree($this->slm->getId());
		$this->node_id = $a_node_id;
		$this->node = $this->tree->getNodeData($this->node_id);
		$this->current_depth = $this->node["depth"];
		if ($a_first_child)
		{
			$this->current_depth++;
			$this->current_parent = $this->node;
		}
		else
		{
			$this->current_parent = $this->tree->getNodeData($this->node["parent"]);
		}
		if ($this->current_parent["child"] == $this->tree->readRootId())
		{
			$this->current_parent["title"] = ilObject::_lookupTitle($this->slm->getId());
			$this->current_parent["type"] = "sahs";
		}
		$this->first_child = $a_first_child;
		$this->confirm = $a_confirm;
		$this->correct = true;
	}

	/**
	 * Is correct?
	 *
	 * @param
	 * @return
	 */
	function isCorrect()
	{
		return $this->correct;
	}

	function performInserts()
	{
		// act like in confirm mode
		$this->confirm = true;
		$this->perform = true;
		$this->processHierarchy();
	}

	function processHierarchy()
	{
		$ilUser = $this->user;

		$target = ($this->first_child)
			? IL_FIRST_NODE
			: $this->node_id;

		// get chapters
		$chapters = $ilUser->getClipboardObjects("st", true);

		if ($this->perform)
		{
			$this->current_parent["insert_id"] = $this->current_parent["child"];
			$this->target[$this->current_parent["insert_id"]] = $target;
		}
		foreach ($chapters as $chap)
		{
			$chap["parent"] = $this->current_parent;
			$this->addNode($chap, $this->current_parent, $this->current_depth);
		}
	}

	/**
	 * Get html
	 *
	 * @param
	 * @return
	 */
	function getHTML()
	{
		$ilUser = $this->user;

		include_once("./Services/UIComponent/NestedList/classes/class.ilNestedList.php");
		$this->list = new ilNestedList();
		$this->list->setListClass("noStyle");

		$this->list->addListNode(
			ilUtil::img(ilUtil::getImagePath("icon_".$this->current_parent["type"].".svg"))." ".
			$this->current_parent["title"]." ".
			"",
			$this->current_parent["id"], 0);

		$this->processHierarchy();

		return $this->list->getHTML();
	}

	/**
	 * Add node
	 *
	 * @param
	 * @return
	 */
	function addNode($a_node, $a_parent, $a_depth)
	{
		$ilUser = $this->user;
		$lng = $this->lng;

		//$tpl = new ilTemplate("tpl.lm_chap_import_node.html", true, true, "Modules/Scorm2004");
		$lng->loadLanguageModule("content");

		$options = array(
			"" => $lng->txt("cont_assign_to_parent"),
			"chap" => $lng->txt("obj_chap"),
			"sco" => $lng->txt("obj_sco"),
			"ass" => $lng->txt("obj_ass")
		);

		if (!$this->confirm)
		{
			$sel = ($a_node["type"] != "pg")
				? $sel = ilUtil::formSelect($_POST["node"][$a_node["id"]], "node[".$a_node["id"]."]", $options, false, true)
				: "";
			$img = ilUtil::img(ilUtil::getImagePath("icon_".$a_node["type"].".svg"));
		}
		else
		{
			// if scos/assets are nested in scos/assets, put the tree one level up
//echo "<br>-".$a_node["title"]."-".$_POST["node"][$a_node["id"]]."-".$_POST["node"][$a_parent["id"]]."-";
			if (in_array($_POST["node"][$a_node["id"]], array("sco", "ass")) && in_array($_POST["node"][$a_parent["id"]], array("sco", "ass")))
			{
//echo "j";
//var_dump($a_parent);
//		echo "<br>getting-from-".$a_parent["title"]."-".$a_parent["id"]."-".$a_target."-";
				$a_parent = $a_parent["parent"];
				$a_depth--;
			}

			$sel = ($a_node["type"] != "pg")
				? $sel = "<strong>".$lng->txt("obj_".$_POST["node"][$a_node["id"]])."</strong>"
				: "";
			$ctype = ($a_node["type"] != "pg")
				? $_POST["node"][$a_node["id"]]
				: "pg";

			$parent_type = $a_parent["type"];
			if (isset($_POST["node"][$a_parent["id"]]))
			{
				$parent_type = $_POST["node"][$a_parent["id"]];
			}
//echo "<br>-".$a_node["title"]."-".$parent_type."-";
			$img = ilUtil::img(ilUtil::getImagePath("icon_".$ctype.".svg"));

			$error = "";
			switch ($a_depth)
			{
				case 0:
				case 1:
					$this->correct = false;
					break;

				case 2:
					if (!in_array($ctype, array("", "chap", "sco", "ass")))
					{
						$error = '<span class="alert">'.$lng->txt("cont_type_not_allowed").": ".$lng->txt("obj_".$ctype)."</span>";
						if ($_POST["node"][$a_node["id"]] != "" || $a_node["type"] == "pg")
						{
							$this->correct = false;
						}
					}
					break;

				default:
					if ($parent_type == "chap" && !in_array($ctype, array("sco", "ass")) ||
						($parent_type != "chap" && !in_array($ctype, array("pg"))))
					{
						$error = '<span class="alert">'.$lng->txt("cont_type_not_allowed").": ".$lng->txt("obj_".$ctype)."</span>";
						if ($_POST["node"][$a_node["id"]] != "" || $a_node["type"] == "pg")
						{
							$this->correct = false;
						}
					}
					break;
			}
		}

		// if node should be inserted, increase depth
		if (!$this->confirm || $_POST["node"][$a_node["id"]] != "" || $a_node["type"] == "pg")
		{
			if (!$this->perform)
			{
				$this->list->addListNode(
					$img." ".
					$a_node["title"]." ".
					$sel." ".$error,
					$a_node["id"], $a_parent["id"]);
			}
			else
			{
				if ($a_parent["insert_id"] > 0)
				{
					$target = $this->target[$a_parent["insert_id"]];

					// create new node of ctype, put it under parent (respect after node from hierarchy)
					switch ($ctype)
					{
						case "chap":
							include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Chapter.php");
							$chap = new ilSCORM2004Chapter($this->slm);
							$chap->setTitle($a_node["title"]);
							$chap->setSLMId($this->slm->getId());
							$chap->create();
							ilSCORM2004Node::putInTree($chap, $a_parent["insert_id"], $target);
							$a_node["insert_id"] = $chap->getId();
							break;

						case "ass":
							include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Asset.php");
							$ass = new ilSCORM2004Asset($this->slm);
							$ass->setTitle($a_node["title"]);
							$ass->setSLMId($this->slm->getId());
							$ass->create();
							ilSCORM2004Node::putInTree($ass, $a_parent["insert_id"], $target);
							$a_node["insert_id"] = $ass->getId();
							break;

						case "sco":
							include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");
							$sco = new ilSCORM2004Sco($this->slm);
							$sco->setTitle($a_node["title"]);
							$sco->setSLMId($this->slm->getId());
							$sco->create();
							ilSCORM2004Node::putInTree($sco, $a_parent["insert_id"], $target);
							$a_node["insert_id"] = $sco->getId();
							break;

						case "pg":
							include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
							$copied_nodes = array();
							$a_node["insert_id"] = ilSCORM2004Node::pasteTree($this->slm, $a_node["id"], $a_parent["insert_id"], $target,
								$a_node["insert_time"], $copied_nodes,
								true, true, "lm");
							break;
					}

					$this->target[$a_parent["insert_id"]] = $a_node["insert_id"];

					// set $a_node["insert_id"] to new id

				}
			}

			$a_depth++;
			$parent = $a_node;
		}
		else
		{
			$parent = $a_parent;
		}

		$childs = $ilUser->getClipboardChilds($a_node["id"], $a_node["insert_time"]);
		foreach ($childs as $c)
		{
			$c["parent"] = $a_node;
//		$c["parent_target"] = rand(1,100);
//		echo "<br>setting-".$c["parent_target"]."-into-".$c["id"]."-".$c["title"]."-";
			$this->addNode($c, $parent, $a_depth);
		}
	}



}

?>