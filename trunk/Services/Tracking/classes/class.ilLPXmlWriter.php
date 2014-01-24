<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";

/**
 * XML writer learning progress
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesTracking
 */
class ilLPXmlWriter extends ilXmlWriter
{
	private $add_header = true;
	
	private $timestamp = "";
	private $include_ref_ids = false;
	private $type_filter = array();

	/**
	 * Constructor
	 */
	public function __construct($a_add_header)
	{
		$this->add_header = $a_add_header;
		parent::__construct();
	}
	
	/**
	 * Set timestamp
	 *
	 * @param string $a_val timestamp (YYYY-MM-DD hh:mm:ss)	
	 */
	function setTimestamp($a_val)
	{
		$this->timestamp = $a_val;
	}
	
	/**
	 * Get timestamp
	 *
	 * @return string timestamp (YYYY-MM-DD hh:mm:ss)
	 */
	function getTimestamp()
	{
		return $this->timestamp;
	}
	
	/**
	 * Set include ref ids
	 *
	 * @param bool $a_val include ref ids	
	 */
	function setIncludeRefIds($a_val)
	{
		$this->include_ref_ids = $a_val;
	}
	
	/**
	 * Get include ref ids
	 *
	 * @return bool include ref ids
	 */
	function getIncludeRefIds()
	{
		return $this->include_ref_ids;
	}
	
	/**
	 * Set type filter
	 *
	 * @param array $a_val string of arrays	
	 */
	function setTypeFilter($a_val)
	{
		$this->type_filter = $a_val;
	}
	
	/**
	 * Get type filter
	 *
	 * @return array string of arrays
	 */
	function getTypeFilter()
	{
		return $this->type_filter;
	}
	
	/**
	 * Write XML
	 * @return 
	 * @throws UnexpectedValueException Thrown if obj_id is not of type webr or no obj_id is given 
	 */
	public function write()
	{
		$this->init();
		if($this->add_header)
		{
			$this->buildHeader();
		}
		$this->addLPInformation();
	}
	
	/**
	 * Build XML header
	 * @return 
	 */
	protected function buildHeader()
	{
		//$this->xmlSetDtdDef("<!DOCTYPE LearningProgress PUBLIC \"-//ILIAS//DTD WebLinkAdministration//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_weblinks_4_0.dtd\">");
		//$this->xmlSetGenCmt("WebLink Object");
		$this->xmlHeader();

		return true;
	}
	
	
	/**
	 * Init xml writer
	 * @return bool
	 * @throws UnexpectedValueException Thrown if obj_id is not of type webr 
	 */
	protected function init()
	{
		$this->xmlClear();
		
/*		if(!$this->obj_id)
		{
			throw new UnexpectedValueException('No obj_id given: ');
		}*/
	}
	
	/**
	 * Add lp information as xml
	 *
	 * @param
	 * @return
	 */
	function addLPInformation()
	{
		global $ilDB;
		
		$this->xmlStartTag('LPData', array());
		
		$set = $ilDB->query($q = "SELECT * FROM ut_lp_marks ".
			" WHERE status_changed >= ".$ilDB->quote($this->getTimestamp(), "timestamp")
			);

		while ($rec = $ilDB->fetchAssoc($set))
		{
			$ref_ids = array();
			if ($this->getIncludeRefIds())
			{
				$ref_ids = ilObject::_getAllReferences($rec["obj_id"]);
			}
			
			if (!is_array($this->getTypeFilter()) ||
				(count($this->getTypeFilter()) == 0) ||
				in_array(ilObject::_lookupType($rec["obj_id"]), $this->getTypeFilter()))
			{
				$this->xmlElement('LPChange',
					array(
						'UserId' => $rec["usr_id"],
						'ObjId' => $rec["obj_id"],
						'RefIds' => implode($ref_ids, ","),
						'Timestamp' => $rec["status_changed"],
						'LPStatus' => $rec["status"]
						)
				);
			}
		}
		
		$this->xmlEndTag('LPData');
	}

}
?>