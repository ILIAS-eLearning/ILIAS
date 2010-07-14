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
		include_once './Modules/Course/classes/class.ilObjCourse.php';

		$this->course = new ilObjCourse();
		$this->course->setTitle('XML Import');
		$this->course->create(true);
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

		$GLOBALS['ilLog']->write($a_xml);

		try 
		{
			$parser = new ilCourseXMLParser($this->course);
			$parser->setXMLContent($a_xml);
			$parser->startParsing();
			$a_mapping->addMapping('Modules/Course','course',$a_rec['Id'],$this->course->getId());
		}
		catch(ilSaxParserException $e)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Parsing failed with message, "'.$e->getMessage().'".');
		}
		catch(ilWebLinkXMLParserException $e)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Parsing failed with message, "'.$e->getMessage().'".');
		}
	}
}
?>