<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Cache/classes/class.ilCache.php");

/**
 * Example cache class. This class shoul fit two purposes
 * - As an example of the abstract ilCache class
 * - As a class that is used by unit tests for testing the ilCache class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesCache
 */
class ilExampleCache extends ilCache
{
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct()
	{
		parent::__construct("ServicesCache", "Example", false);
		$this->setExpiresAfter(5);		// only five seconds to make a hit
				// usually you would this value from some setting
	}
	
	/**
	 * Determine entry value
	 *
	 * @param	string		entry id
	 * @return	string		entry value
	 */
	function determineEntryValue($a_id)
	{
		// normally the $a_id contains a string with
		// several parts, e.g. userid:refid for a news cache
		// the parts would be separated and
		// the value would be determined by using application classes
		// of the corresponding service or module.
		// here we just return a dummy value
		return "value_".$a_id;
	}
}
?>
