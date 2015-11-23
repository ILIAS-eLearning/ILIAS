<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Repository/classes/class.ilRepositoryExplorer.php';

/**
 * Class ilShopRepositoryExplorer
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilShopRepositoryExplorer extends ilRepositoryExplorer
{
	/**
	 * id of root folder
	 * @var int root folder id
	 */
	public $root_id;
	/**
	 * @var
	 */
	public $output;
	
	/**
	 * @var 
	 */
	public $ctrl;

	/**
	 * @param     $a_target
	 * @param int $a_top_node
	 */
	public function __construct($a_target, $a_top_node = 0)
	{
		parent::ilRepositoryExplorer($a_target, $a_top_node);
	}

	/**
	 * set force open path
	 * @param $a_path
	 */
	public function setForceOpenPath($a_path)
	{
		$this->force_open_path = $a_path;
	}

	/**
	 * @param int       $a_node_id
	 * @param string    $a_type
	 * @return string
	 */
	public function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl;
		
		switch($a_type)
		{
			case "cat":
				return "ilias.php?baseClass=ilshopcontroller&ref_id=".$a_node_id;

			case "catr":
				return "ilias.php?cmd=redirect&baseClass=ilshopcontroller&ref_id=".$a_node_id;

			case "grp":
				return "ilias.php?baseClass=ilshopcontroller&ref_id=".$a_node_id;

			case "crs":
				return "ilias.php?baseClass=ilshopcontroller&ref_id=".$a_node_id;

			case "crsr":
				return "ilias.php?cmd=redirect&baseClass=ilshopcontroller&ref_id=".$a_node_id;

			case 'rcrs':
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node_id);
				$link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "infoScreen");
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
				return $link;

			default:
				include_once('./Services/Link/classes/class.ilLink.php');
				return ilLink::_getStaticLink($a_node_id, $a_type, true);
		}
	}
	
	/**
	 * @param $tpl
	 * @param integer $a_obj_id
	 * @param integer $a_option
	 */
	public function formatHeader(&$tpl, $a_obj_id, $a_option)
	{
		global $lng, $tree;

		// custom icons
		$path = ilObject::_getIcon($a_obj_id, "tiny", "root");

		$tpl->setCurrentBlock("icon");
		$nd = $tree->getNodeData(ROOT_FOLDER_ID);
		$title = $nd["title"];

		if ($title == "ILIAS")
		{
			$title = $lng->txt("repository");
		}

		$tpl->setVariable("ICON_IMAGE", $path);
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt("icon")." ".$title);
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", $title);
		
		$tpl->setVariable("LINK_TARGET", "ilias.php?baseClass=ilshopcontroller&ref_id=1");
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("element");
		$tpl->parseCurrentBlock();
	}
} 