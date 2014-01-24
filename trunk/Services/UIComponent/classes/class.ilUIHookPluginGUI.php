<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User interface hook class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUIComponent
 */
class ilUIHookPluginGUI
{
	protected $plugin_object = null;
	
	const UNSPECIFIED = "";
	const KEEP = "";
	const REPLACE = "r";
	const APPEND = "a";
	const PREPEND = "p";

	/**
	 * Set plugin object
	 *
	 * @param	object	plugin object
	 */
	final function setPluginObject($a_val)
	{
		$this->plugin_object = $a_val;
	}

	/**
	 * Get plugin object
	 *
	 * @return	object	plugin object
	 */
	final function getPluginObject()
	{
		return $this->plugin_object;
	}

	/**
	 * Get html for ui area
	 *
	 * @param
	 * @return
	 */
	function getHTML($a_comp, $a_part, $a_par = array())
	{
		return array("mode" => ilUIHookPluginGUI::KEEP, "html" => "");
	}

	/**
	 * Modify user interface, paramters contain classes that can be modified
	 *
	 * @param
	 * @return
	 */
	function modifyGUI($a_comp, $a_part, $a_par = array())
	{
	}

	/**
	 * Modify HTML based on default html and plugin response
	 *
	 * @param	string	default html
	 * @param	string	resonse from plugin
	 * @return	string	modified html
	 */
	final function modifyHTML($a_def_html, $a_resp)
	{
		switch ($a_resp["mode"])
		{
			case ilUIHookPluginGUI::REPLACE:
				$a_def_html = $a_resp["html"];
				break;
			case ilUIHookPluginGUI::APPEND:
				$a_def_html.= $a_resp["html"];
				break;
			case ilUIHookPluginGUI::PREPEND:
				$a_def_html = $a_resp["html"].$a_def_html;
				break;
		}
		return $a_def_html;
	}

	/**
	 * Goto script hook
	 *
	 * Can be used to interfere with the goto script behaviour
	 */
	function gotoHook()
	{
	}

	/**
	 * Goto script hook
	 *
	 * Can be used to interfere with the goto script behaviour
	 */
	function checkGotoHook()
	{
		return array("target" => false);
	}

}
?>