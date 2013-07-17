<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Config class for page editing
 *
 * @author Alex Killing <alex.killing.gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
class ilPageConfig
{
	var $int_link_filter = array();
	var $prevent_rte_usage = false;
	var $use_attached_content = false;
	var $pc_defs = array();
	var $pc_enabled = array();
	var $enabledinternallinks = false;
	var $enable_keywords = false;
	var $enable_anchors = false;
	var $enablewikilinks = false;
	
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct()
	{
		// load pc_defs
		include_once("./Services/COPage/classes/class.ilCOPagePCDef.php");
		$this->pc_defs = ilCOPagePCDef::getPCDefinitions();
		foreach ($this->pc_defs as $def)
		{
			$this->setEnablePCType($def["name"], (bool) $def["def_enabled"]);
		}
	}
	
	/**
	 * Set enable pc type
	 *
	 * @param boolean $a_val enable pc type true/false	
	 */
	function setEnablePCType($a_pc_type, $a_val)
	{
		$this->pc_enabled[$a_pc_type] = $a_val;
	}
	
	/**
	 * Get enable pc type
	 *
	 * @return boolean enable pc type true/false
	 */
	function getEnablePCType($a_pc_type)
	{
		return $this->pc_enabled[$a_pc_type];
	}

	/**
	 * Set enable keywords handling
	 *
	 * @param	boolean	keywords handling
	 */
	function setEnableKeywords($a_val)
	{
		$this->enable_keywords = $a_val;
	}
	
	/**
	 * Get enable keywords handling
	 *
	 * @return	boolean	keywords handling
	 */
	function getEnableKeywords()
	{
		return $this->enable_keywords;
	}

	/**
	 * Set enable anchors
	 *
	 * @param	boolean	anchors
	 */
	function setEnableAnchors($a_val)
	{
		$this->enable_anchors = $a_val;
	}
	
	/**
	 * Get enable anchors
	 *
	 * @return	boolean	anchors
	 */
	function getEnableAnchors()
	{
		return $this->enable_anchors;
	}

	/**
	 * Set Enable internal links.
	 *
	 * @param	boolean	$a_enabledinternallinks	Enable internal links
	 */
	function setEnableInternalLinks($a_enabledinternallinks)
	{
		$this->enabledinternallinks = $a_enabledinternallinks;
	}

	/**
	 * Get Enable internal links.
	 *
	 * @return	boolean	Enable internal links
	 */
	function getEnableInternalLinks()
	{
		return $this->enabledinternallinks;
	}

	/**
	 * Set Enable Wiki Links.
	 *
	 * @param	boolean	$a_enablewikilinks	Enable Wiki Links
	 */
	function setEnableWikiLinks($a_enablewikilinks)
	{
		$this->enablewikilinks = $a_enablewikilinks;
	}

	/**
	 * Get Enable Wiki Links.
	 *
	 * @return	boolean	Enable Wiki Links
	 */
	function getEnableWikiLinks()
	{
		return $this->enablewikilinks;
	}


	/**
	 * Add internal links filter
	 *
	 * @param	string	internal links filter
	 */
	function addIntLinkFilter($a_val)
	{
		global $lng;
		
		$this->setLocalizationLanguage($lng->getLangKey());
		if (is_array($a_val))
		{
			$this->int_link_filter =
				array_merge($a_val, $this->int_link_filter);
		}
		else
		{
			$this->int_link_filter[] = $a_val;
		}
	}
	
	/**
	 * Get internal links filter
	 *
	 * @return	string	internal links filter
	 */
	function getIntLinkFilters()
	{
		return $this->int_link_filter;
	}

	/**
	 * Set internal links filter type list to white list
	 *
	 * @param	boolean white list
	 */
	function setIntLinkFilterWhiteList($a_white_list)
	{
		$this->link_filter_white_list = $a_white_list;
	}

	/**
	 * Get internal links filter type list to white list
	 *
	 * @return	boolean white list
	 */
	function getIntLinkFilterWhiteList()
	{
		return $this->link_filter_white_list;
	}

	/**
	 * Set prevent rte usage
	 *
	 * @param	boolean	prevent rte usage
	 */
	function setPreventRteUsage($a_val)
	{
		$this->prevent_rte_usage = $a_val;
	}

	/**
	 * Get prevent rte usage
	 *
	 * @return	boolean	prevent rte usage
	 */
	function getPreventRteUsage()
	{
		return $this->prevent_rte_usage;
	}
	
	/**
	 * Set localizazion language
	 *
	 * @param string $a_val lang key	
	 */
	function setLocalizationLanguage($a_val)
	{
		$this->localization_lang = $a_val;
	}
	
	/**
	 * Get localizazion language
	 *
	 * @return string lang key
	 */
	function getLocalizationLanguage()
	{
		return $this->localization_lang;
	}
	
	/**
	 * Set use attached content
	 *
	 * @param string $a_val use initial attached content	
	 */
	function setUseAttachedContent($a_val)
	{
		$this->use_attached_content = $a_val;
	}
	
	/**
	 * Get use attached content
	 *
	 * @return string use initial attached content
	 */
	function getUseAttachedContent()
	{
		return $this->use_attached_content;
	}
}
?>