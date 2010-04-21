<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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

	/**
	 * Add internal links filter
	 *
	 * @param	string	internal links filter
	 */
	function addIntLinkFilter($a_val)
	{
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
}
?>