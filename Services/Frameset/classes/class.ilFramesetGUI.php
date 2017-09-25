<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilFramesetGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilFramesetGUI
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilSetting
	 */
	protected $settings;


	/**
	* Constructor
	* @access	public
	*/
	function __construct()
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->settings = $DIC->settings();
		$lng = $DIC->language();
		
		$this->setMainWidth("*");
		$this->setSideWidth("25%");
		
		// default titles (accessibility revision)
		// should not been overwritten, if no good reason is given
		$this->setSideFrameTitle($lng->txt("side_frame"));
		$this->setMainFrameTitle($lng->txt("content_frame"));
	}
	
	/**
	* set title for frameset (is normally shown by browser)
	*/
	function setFramesetTitle($a_fs_title)
	{
		$this->frameset_title = $a_fs_title;
	}
	
	/**
	* source url for main frame
	*/
	function setMainFrameSource($a_main_source)
	{
		$this->main_frame_source = $a_main_source;
	}

	/**
	* title for main frame
	*/
	function setMainFrameTitle($a_main_title)
	{
		$this->main_frame_title = $a_main_title;
	}

	/**
	* name for main frame
	*/
	function setMainFrameName($a_main_name)
	{
		$this->main_frame_name = $a_main_name;
	}

	/**
	* source url for side frame
	*/
	function setSideFrameSource($a_side_source)
	{
		$this->side_frame_source = $a_side_source;
	}

	/**
	* title for side frame
	*/
	function setSideFrameTitle($a_side_title)
	{
		$this->side_frame_title = $a_side_title;
	}
	
	/**
	* name for main frame
	*/
	function setSideFrameName($a_side_name)
	{
		$this->side_frame_name = $a_side_name;
	}

	/**
	* Set Main Width.
	*
	* @param	string	$a_mainwidth	Main Width
	*/
	function setMainWidth($a_mainwidth)
	{
		$this->mainwidth = $a_mainwidth;
	}

	/**
	* Get Main Width.
	*
	* @return	string	Main Width
	*/
	function getMainWidth()
	{
		return $this->mainwidth;
	}

	/**
	* Set Side Width.
	*
	* @param	string	$a_sidewidth	Side Width
	*/
	function setSideWidth($a_sidewidth)
	{
		$this->sidewidth = $a_sidewidth;
	}

	/**
	* Get Side Width.
	*
	* @return	string	Side Width
	*/
	function getSideWidth()
	{
		return $this->sidewidth;
	}

	/**
	 * Get
	 */
	function get()
	{
		return $this->show(true);
	}
	
	
	/**
	 * Show frameset
	 */
	function show($a_get_only = false)
	{
		$ilSetting = $this->settings;
		
		if ($ilSetting->get("tree_frame") == "right")
		{
			$main = "LEFT";
			$side = "RIGHT";
		}
		else
		{
			$main = "RIGHT";
			$side = "LEFT";
		}

		$tpl = new ilTemplate("tpl.frameset.html", true, false);
		$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		$tpl->setVariable("PAGETITLE", "- ".ilUtil::stripScriptHTML($this->frameset_title));
		$tpl->setVariable("SRC_".$main, $this->main_frame_source);
		$tpl->setVariable("SRC_".$side, $this->side_frame_source);
		$tpl->setVariable("TITLE_".$main, $this->main_frame_title);
		$tpl->setVariable("TITLE_".$side, $this->side_frame_title);
		$tpl->setVariable("NAME_".$main, $this->main_frame_name);
		$tpl->setVariable("NAME_".$side, $this->side_frame_name);
		$tpl->setVariable("WIDTH_".$main, $this->getMainWidth());
		$tpl->setVariable("WIDTH_".$side, $this->getSideWidth());
		if ($ilSetting->get('short_inst_name') != "")
		{
			$tpl->setVariable("WINDOW_TITLE",
				$ilSetting->get('short_inst_name'));
		}
		else
		{
			$tpl->setVariable("WINDOW_TITLE",
				"ILIAS");
		}

		if ($a_get_only)
		{
			return $tpl->get();
		}
		else
		{
			$tpl->show("DEFAULT", false);
		}
	}

}
