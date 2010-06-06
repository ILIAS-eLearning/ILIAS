<?php 
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for export package manager classes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesExport
 */
interface ilExportPackage
{
	/**
	 * Get export sequence
	 *
	 * @param	string		target release, e.g. "4.1.0"
	 * @param	string		target release, e.g. "4.1.0"
	 * @return	array		of sequence steps (array of array("component", "ds_class", "entity", "ids")
	 */
	public function getXmlExportSequence($a_target_release, $a_id);
	
}
?>