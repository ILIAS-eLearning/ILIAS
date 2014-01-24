<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Container/classes/class.ilContainerXmlWriter.php';
include_once './Services/Export/classes/class.ilXmlExporter.php';

/**
* container structure export
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesContainer
*/
class ilContainerExporter extends ilXmlExporter
{
	private $writer = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
			
	}
	
	/**
	 * Init export
	 * @return 
	 */
	public function init()
	{
	}
	
	/**
	 * Get xml
	 * @param object $a_entity
	 * @param object $a_schema_version
	 * @param object $a_id
	 * @return 
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Received id = '.$a_id);
		$writer = new ilContainerXmlWriter(end(ilObject::_getAllReferences($a_id)));
		$writer->write();
		return $writer->xmlDumpMem(false);
	}
	
	/**
	 * Returns schema versions that the component can export to.
	 * ILIAS chooses the first one, that has min/max constraints which
	 * fit to the target release. Please put the newest on top.
	 *
	 * @return
	 */
	public function getValidSchemaVersions($a_entity)
	{
		return array (
			"4.1.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Folder/fold/4_1",
				"xsd_file" => "ilias_fold_4_1.xsd",
				"uses_dataset" => false,
				"min" => "4.1.0",
				"max" => "")
		);
	}
}
?>