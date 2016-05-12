<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilLOXmlWriter
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
*
*
*/
class ilLOXmlWriter
{
	const TYPE_TST_PO = 1;
	const TYPE_TST_ALL = 2;
	const TYPE_TST_RND = 3;

	private $ref_id = 0;
	private $obj_id = 0;
	private $writer = null;
	
	/**
	 * Constructor
	 */
	public function __construct($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
		$this->obj_id = ilObject::_lookupObjectId($a_ref_id);
		
		include_once './Services/Xml/classes/class.ilXmlWriter.php';
		$this->writer = new ilXmlWriter();
	}
	
	/**
	 * Get writer
	 * @return \ilXmlWriter
	 */
	protected function getWriter()
	{
		return $this->writer;
	}
	
	/**
	 * Write xml
	 */
	public function write()
	{
		global $ilSetting;
		
		$this->getWriter()->xmlStartTag('Objectives');
		
		// export settings
		include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
		$settings = ilLOSettings::getInstanceByObjId($this->obj_id);
		$settings->toXml($this->getWriter());
		
		$factory = new ilObjectFactory();
		$course = $factory->getInstanceByRefId($this->ref_id,false);
		if(!$course instanceof ilObjCourse)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Cannot create course instance');
			return;
		}
		
		include_once './Modules/Course/classes/class.ilCourseObjective.php';
		foreach(ilCourseObjective::_getObjectiveIds($this->obj_id) as $objective_id)
		{
			$objective = new ilCourseObjective($course, $objective_id);
			$objective->toXml($this->getWriter());
		}
		
		$this->getWriter()->xmlEndTag('Objectives');
	}
	
	/**
	 * Get xml
	 * @return type
	 */
	public function getXml()
	{
		return $this->getWriter()->xmlDumpMem(false);
	}
}
?>