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
	 * @deprecated Note this method is deprecated. There are several issues with hacking into already rendered html
	 * as provided here:
	 * - The generation of html might be performed twice (especially if REPLACE is used).
	 * - There is limited access to data used to generate the original html. If needed this data needs to be gathered again.
	 * - If an element inside the html needs to be changed, some crude string replace magic is needed.
	 *
	 *
	 * Modify HTML output of GUI elements. Modifications modes are:
	 * - ilUIHookPluginGUI::KEEP (No modification)
	 * - ilUIHookPluginGUI::REPLACE (Replace default HTML with your HTML)
	 * - ilUIHookPluginGUI::APPEND (Append your HTML to the default HTML)
	 * - ilUIHookPluginGUI::PREPEND (Prepend your HTML to the default HTML)
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param array $a_par array of parameters (depend on $a_comp and $a_part)
	 *
	 * @return array array with entries "mode" => modification mode, "html" => your html
	 */
	function getHTML($a_comp, $a_part, $a_par = array())
	{
		return array("mode" => ilUIHookPluginGUI::KEEP, "html" => "");
	}

	/**
	 * @deprecated Note this method is deprecated. User Interface components are migrated towards the UIComponents and
	 * Global Screen which do not make use of the mechanism provided here. Make use of the extension possibilities provided
	 * by Global Screen and UI Components instead.
	 *
	 * In ILIAS 6.0 still working for working for:
	 * - $a_comp="Services/Ini" ; $a_part="init_style"
	 * - $a_comp="" ; $a_part="tabs"
	 * - $a_comp="" ; $a_part="sub_tabs"
	 *
	 * Allows to modify user interface objects before they generate their output.
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param array $a_par array of parameters (depend on $a_comp and $a_part)
	 */
	function modifyGUI($a_comp, $a_part, $a_par = array())
	{
	}

	/**
	 * @deprecated Reason, see getHTML
	 *
	 * Modify HTML based on default html and plugin response
	 *
	 * @param	string	default html
	 * @param	string	response from plugin
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
	function checkGotoHook($a_target)
	{
		return array("target" => false);
	}

}
?>