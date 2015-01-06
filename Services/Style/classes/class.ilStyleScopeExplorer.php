<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

/**
 * Class ilStyleScopyExplorer
 *
 * @version $Id$
 * @ingroup ServicesStyle
 */
class ilStyleScopeExplorer extends ilExplorer
{
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	*/
	function ilStyleScopeExplorer($a_target)
	{
		if ($_GET["id"] > 0)
		{
			$this->style_id = $_GET["id"];
		}
		else
		{
			$this->style_id = $_GET["stlye_id"];
		}
		
		parent::ilExplorer($a_target);
	}
	
	function formatHeader(&$tpl,$a_obj_id,$a_option)
	{
		global $lng, $ilias, $ilCtrl;

		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE", ilUtil::getImagePath("icon_root.svg"));
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		$ilCtrl->setParameterByClass("ilobjstylesettingsgui",
			"cat", 0);
		$ilCtrl->setParameterByClass("ilobjstylesettingsgui",
			"style_id", $this->style_id);

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", $lng->txt("repository"));
		$tpl->setVariable("LINK_TARGET", $ilCtrl->getLinkTargetByClass("ilobjstylesettingsgui",
			"saveScope"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("element");
		$tpl->parseCurrentBlock();
	}

	/**
	* get link target
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl;
		
		$ilCtrl->setParameterByClass("ilobjstylesettingsgui",
			"cat", $a_node_id);
		$ilCtrl->setParameterByClass("ilobjstylesettingsgui",
			"style_id", $this->style_id);
		
		return $ilCtrl->getLinkTargetByClass("ilobjstylesettingsgui",
			"saveScope");
	}

} // END class.ilExplorer
?>
