<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * UI interface hook processor
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilUIHookProcessor
{
	var $append = array();
	var $prepend = array();
	var $replace = "";
	
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_comp, $a_part, $a_pars)
	{
		global $ilPluginAdmin;
		
		include_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");
		
		// user interface hook [uihk]
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
		$this->replaced = false;
		foreach ($pl_names as $pl)
		{
			$ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
			$gui_class = $ui_plugin->getUIClassInstance();
			$resp = $gui_class->getHTML($a_comp, $a_part, $a_pars);

			if ($resp["mode"] != ilUIHookPluginGUI::KEEP)
			{
				switch($resp["mode"])
				{
					case ilUIHookPluginGUI::PREPEND:
						$this->prepend[] = $resp["html"];
						break;
						
					case ilUIHookPluginGUI::APPEND:
						$this->append[] = $resp["html"];
						break;
						
					case ilUIHookPluginGUI::REPLACE:
						if (!$this->replaced)
						{
							$this->replace = $resp["html"];
							$this->replaced = true;
						}
						break;
				}
			}
		}
	}

	/**
	 * Should HTML be replaced completely?
	 *
	 * @return
	 */
	function replaced()
	{
		return $this->replaced;
	}
	
	/**
	 * Get HTML
	 *
	 * @param string $html html
	 * @return string html
	 */
	function getHTML($html)
	{
		if ($this->replaced)
		{
			$html = $this->replace;
		}
		foreach ($this->append as $a)
		{
			$html.= $a;
		}
		foreach ($this->prepend as $p)
		{
			$html = $p.$html;
		}
		return $html;
	}
	
}

?>
