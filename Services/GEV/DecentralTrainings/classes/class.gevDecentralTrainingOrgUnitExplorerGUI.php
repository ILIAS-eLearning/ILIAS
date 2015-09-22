<?php
require_once("Services/TEP/classes/class.ilTEPOrgUnitExplorerGUI.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
class gevDecentralTrainingOrgUnitExplorerGUI extends ilTEPOrgUnitExplorerGUI {

	/**
	 * Get childs of node
	 *
	 * @param string $a_parent_id parent node id
	 * @return array childs
	 */
	function getChildsOfNode($a_parent_node_id) {
		$viewable = $this->getSelectableOrgUnitIds();
		
		if ($a_parent_node_id == $this->getRootNode()["ref_id"]) {
			return $this->getAllSelectableOrgUnits();
		}

		if($this->isInArray($a_parent_node_id, $viewable["view_rekru"])) {
			$current = $viewable["view_rekru"][$a_parent_node_id];
			
			require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
			$org_unit_utils = gevOrgUnitUtils::getInstance($current["obj_id"]);
			$units = $org_unit_utils->getOrgUnitsOneTreeLevelBelowWithTitle();

			if($a_parent_node_id == gevOrgUnitUtils::getUVGOrgUnitRefId()) {
				$gev_settings = gevSettings::getInstance();
				$uvg_template_node = $gev_settings->getDBVPOUTemplateUnitId();

				foreach ($units as $key => $value) {
					if($value["obj_id"] == $uvg_template_node) {
						unset($units[$key]);
						continue;
					}

					$viewable["view"][$key] = $value;
				}
			} else {
				foreach ($units as $key => $value) {
					$viewable["view_rekru"][$key] = $value;
				}
			}

			$this->setSelectableOrgUnitIds($viewable);
			return $units;
		}

		return array();
	}

	/**
	 * Get HTML
	 */
	function getHTML()
	{
		global $tpl, $ilCtrl, $lng;

		$this->beforeRendering();

		$tpl->addJavascript(self::getLocalExplorerJsPath());
		$tpl->addJavascript(self::getLocalJsTreeJsPath());
		
		$container_id = $this->getContainerId();
		$container_outer_id = "il_expl2_jstree_cont_out_".$this->getId();
		
		// collect open nodes
		$open_nodes = array($this->getDomNodeIdForNodeId($this->getNodeId($this->getRootNode())));
		foreach ($this->open_nodes as $nid)
		{
			$open_nodes[] = $this->getDomNodeIdForNodeId($nid);
		}
		foreach ($this->custom_open_nodes as $nid)
		{
			$dnode = $this->getDomNodeIdForNodeId($nid);
			if (!in_array($dnode, $open_nodes))
			{
				$open_nodes[] = $dnode;
			}
		}

		// ilias config options
		$url = "";
		if (!$this->getOfflineMode())
		{
			if (is_object($this->parent_obj))
			{
				$url = $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd, "", true);
			}
			else
			{
				$url = $ilCtrl->getLinkTargetByClass($this->parent_obj, $this->parent_cmd, "", true);
			}
		}
		$config = array(
			"container_id" => $container_id,
			"container_outer_id" => $container_outer_id,
			"url" => $url,
			"ajax" => $this->getAjax(),
			);
		
		
		// jstree config options
		$js_tree_config = array(
			"core" => array(
				"animation" => 300,
				"initially_open" => $open_nodes,
				"open_parents" => false,
				"strings" => array("loading" => "Loading ...", new_node => "New node")
				),
			"plugins" => array("html_data", "themes"),
			"themes" => array("dots" => false, "icons" => false, "theme" => ""),
			"html_data" => array()
			);
		
		$tpl->addOnLoadCode('il.Explorer2.init('.json_encode($config).', '.json_encode($js_tree_config).');');
		
		//gev patch start
		$etpl = new ilTemplate("tpl.explorer2_decentral.html", true, true, "Services/GEV/DecentralTrainings");
		$etpl->setVariable("ADVICE",$lng->txt("gev_dec_training_org_unit_advice"));
		//gev patch end



		// render childs
		$root_node = $this->getRootNode();
		
		if (!$this->getSkipRootNode() &&
			$this->isNodeVisible($this->getRootNode()))
		{
			$this->listStart($etpl);
			$this->renderNode($this->getRootNode(), $etpl);
			$this->listEnd($etpl);
		}
		else
		{		
			$childs = $this->getChildsOfNode($this->getNodeId($root_node));
			$childs = $this->sortChilds($childs, $this->getNodeId($root_node));
			$any = false;
			foreach ($childs as $child_node)
			{
				if ($this->isNodeVisible($child_node))
				{
					if (!$any)
					{
						$this->listStart($etpl);
						$any = true;
					}
					$this->renderNode($child_node, $etpl);
				}
			}
			if ($any)
			{
				$this->listEnd($etpl);
			}
		}
		
		$etpl->setVariable("CONTAINER_ID", $container_id);
		$etpl->setVariable("CONTAINER_OUTER_ID", $container_outer_id);

		return $etpl->get();
	}
}