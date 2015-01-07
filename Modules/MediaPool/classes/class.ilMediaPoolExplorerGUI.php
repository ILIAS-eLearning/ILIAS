<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

/**
 * Media pool explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ModulesMediaPool
 */
class ilMediaPoolExplorerGUI extends ilTreeExplorerGUI
{
	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_media_pool)
	{
		global $ilUser;
		
		$this->media_pool = $a_media_pool;
		parent::__construct("mep_exp", $a_parent_obj, $a_parent_cmd, $a_media_pool->getTree());
		
		$this->setTypeWhiteList(array("dummy", "fold"));
		$this->setSkipRootNode(false);
		$this->setAjax(true);
		$this->setOrderField("title");
	}

	/**
	 * Get node content
	 *
	 * @param array 
	 * @return
	 */
	function getNodeContent($a_node)
	{
		global $lng;

		if ($a_node["child"] == $this->getNodeId($this->getRootNode()))
		{
			return $this->media_pool->getTitle();
		}
				
		return $a_node["title"];
	}
	
	/**
	 * Get node icon
	 *
	 * @param array 
	 * @return
	 */
	function getNodeIcon($a_node)
	{
		if ($a_node["child"] == $this->getNodeId($this->getRootNode()))
		{
			$icon = ilUtil::getImagePath("icon_mep.svg");
		}
		else
		{
			$icon = ilUtil::getImagePath("icon_".$a_node["type"].".svg");
		}
		
		return $icon;
	}

	/**
	 * Is node highlighted?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node visible true/false
	 */
	function isNodeHighlighted($a_node)
	{
		if ($a_node["child"] == $_GET["mepitem_id"] ||
			($_GET["mepitem_id"] == "" && $a_node["child"] == $this->getNodeId($this->getRootNode())))
		{
			return true;
		}
		return false;
	}	
	
	/**
	 * Get href for node
	 *
	 * @param mixed $a_node node object/array
	 * @return string href attribute
	 */
	function getNodeHref($a_node)
	{
		global $ilCtrl;
		
		$ilCtrl->setParameterByClass("ilobjmediapoolgui", "mepitem_id", $a_node["child"]);
		$ret = $ilCtrl->getLinkTargetByClass("ilobjmediapoolgui", "listMedia");
		$ilCtrl->setParameterByClass("ilobjmediapoolgui", "mepitem_id", $_GET["mepitem_id"]);
		return $ret;
	}

}

?>
