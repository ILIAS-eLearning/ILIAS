<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
 * Interface for property form input GUI classes that can be used
 * in table filters
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
interface ilTableFilterItem
{
	/**
	 * Get input item HTML to be inserted into table filters
	 * @return string
	 */
	public function getTableFilterHTML();
}