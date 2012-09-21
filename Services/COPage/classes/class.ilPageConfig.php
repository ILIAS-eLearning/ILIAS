<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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