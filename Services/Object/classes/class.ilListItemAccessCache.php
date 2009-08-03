<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Cache/classes/class.ilCache.php");

/**
 * Caches (check) access information on list items.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesObject
 */
class ilListItemAccessCache extends ilCache
{
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct()
	{
		parent::__construct("ServicesObject", "CheckAccess", false);
		$this->setExpiresAfter(1800);
	}
}
?>
