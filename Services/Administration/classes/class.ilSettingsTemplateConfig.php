<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Settings template config class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAdministration
 */
class ilSettingsTemplateConfig
{
	private $type;
	private $tab = array();
	private $setting = array();

	const TEXT = "text";
	const SELECT = "select";
	const BOOL = "bool";
        const CHECKBOX = "check";

	/**
	 * Constructor
	 *
	 * @param string object type
	 */
	function __construct($a_obj_type)
	{
		$this->setType($a_obj_type);
	}

	/**
	 * Set type
	 *
	 * @param	string	$a_val	type
	 */
	public function setType($a_val)
	{
		$this->type = $a_val;
	}

	/**
	 * Get type
	 *
	 * @return	string	type
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Add hidable tabs
	 *
	 * @param int tab id
	 * @param string tab text
	 */
	function addHidableTab($a_tab_id, $a_text)
	{
		$this->tabs[$a_tab_id] = array(
			"id" => $a_tab_id,
			"text" => $a_text
		);
	}

	/**
	 * Get hidable tabs
	 */
	function getHidableTabs()
	{
		return $this->tabs;
	}

	/**
	 * Add setting
	 *
	 * @param
	 * @return
	 */
	function addSetting($a_id, $a_type, $a_text, $a_hidable, $a_length = 0, $a_options = array())
	{
		$this->setting[$a_id] = array(
			"id" => $a_id,
			"type" => $a_type,
			"text" => $a_text,
			"hidable" => $a_hidable,
			"length" => $a_length,
			"options" => $a_options
		);
	}

	/**
	 * Get settings
	 * @return
	 */
	function getSettings()
	{
		return $this->setting;
	}
}

?>
