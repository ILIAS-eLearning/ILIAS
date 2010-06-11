<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Xml importer class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesExport
 */
abstract class ilXmlImporter
{
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct()
	{

	}

	/**
	 * Import xml representation
	 *
	 * @param	string		entity
	 * @param	string		target release
	 * @param	string		id
	 * @return	string		xml string
	 */
	abstract public function importXmlRepresentation($a_entity, $a_schema_version, $a_id, $a_xml, $a_mapping);

}
?>
