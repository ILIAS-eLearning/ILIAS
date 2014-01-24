<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Modules/Course/classes/class.ilCourseXMLWriter.php';
include_once './Services/Export/classes/class.ilXmlExporter.php';

/**
* Folder export
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesBooking
*/
class ilCourseExporter extends ilXmlExporter
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
	 * Get head dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	public function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids)
	{
		include_once './Services/Export/classes/class.ilExportOptions.php';
		$eo = ilExportOptions::getInstance();

		$obj_id = end($a_ids);
		if($eo->getOption(ilExportOptions::KEY_ROOT) != $obj_id)
		{
			return array();
		}
		if(count(ilExportOptions::getInstance()->getSubitemsForExport()) > 1)
		{
			return array(
				array(
					'component'		=> 'Services/Container',
					'entity'		=> 'struct',
					'ids'			=> $a_ids
				)
			);
		}
		return array();		
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
		$course_ref_id = end(ilObject::_getAllReferences($a_id));
		$course = ilObjectFactory::getInstanceByRefId($course_ref_id,false);
		
		if(!$course instanceof ilObjCourse)
		{
			$GLOBALS['ilLog']->write(__METHOD__. $a_id . ' is not instance of type course');
			return ''; 
		}
		
		$this->writer = new ilCourseXMLWriter($course);
		$this->writer->setMode(ilCourseXMLWriter::MODE_EXPORT);
		$this->writer->start();
		return $this->writer->xmlDumpMem(false);
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
				"namespace" => "http://www.ilias.de/Modules/Course/crs/4_1",
				"xsd_file" => "ilias_course_4_1.xsd",
				"uses_dataset" => false,
				"min" => "4.1.0",
				"max" => "")
		);
	}
}
?>