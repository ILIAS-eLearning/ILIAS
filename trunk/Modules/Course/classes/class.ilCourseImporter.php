<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
* folder xml importer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesCourse
*/
class ilCourseImporter extends ilXmlImporter
{
	private $course = null;
	

	public function init()
	{
	}
	
	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		include_once './Modules/Course/classes/class.ilCourseXMLParser.php';
		include_once './Modules/Course/classes/class.ilObjCourse.php';


		if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_id))
		{
			$refs = ilObject::_getAllReferences($new_id);
			$this->course = ilObjectFactory::getInstanceByRefId(end($refs),false);
			#$this->course = ilObjectFactory::getInstanceByObjId($new_id,false);
		}
		// Mapping for containers without subitems
		elseif($new_id = $a_mapping->getMapping('Services/Container','refs',0))
		{
			$this->course = ilObjectFactory::getInstanceByRefId($new_id,false);
		}
		elseif(!$this->course instanceof ilObjCourse)
		{
			$this->course = new ilObjCourse();
			$this->course->create(true);
		}

		try 
		{
			$parser = new ilCourseXMLParser($this->course);
			$parser->setXMLContent($a_xml);
			$parser->startParsing();
			$a_mapping->addMapping('Modules/Course','crs',$a_id,$this->course->getId());
		}
		catch(ilSaxParserException $e)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Parsing failed with message, "'.$e->getMessage().'".');
		}
		catch(Exception $e)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Parsing failed with message, "'.$e->getMessage().'".');
		}
	}
}
?>