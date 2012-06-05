<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";
include_once './Services/Export/classes/class.ilExportOptions.php';

/**
* XML writer for container structure
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesContainer
*/
class ilContainerXmlWriter extends ilXmlWriter
{
	protected $exp_options = null;
	private $source = 0;

	/**
	 * Constructor
	 */
	public function __construct($a_ref_id)
	{
		parent::__construct();
		$this->source = $a_ref_id;
		$this->exp_options = ilExportOptions::getInstance();
		
	}
	
	/**
	 * Write XML
	 * @return 
	 * @throws UnexpectedValueException Thrown if obj_id is not of type webr or no obj_id is given 
	 */
	public function write()
	{
		global $tree;
		
		$this->xmlStartTag('Items');
		$this->writeSubitems($this->source);
		$this->xmlEndTag('Items');
	}
	
	/**
	 * Write tree childs
	 * Recursive method
	 * @param object $a_ref_id
	 * @return 
	 */
	protected function writeSubitems($a_ref_id)
	{
		global $tree;
	
		$mode = $this->exp_options->getOptionByRefId($a_ref_id, ilExportOptions::KEY_ITEM_MODE);
		if($mode == NULL or $mode == ilExportOptions::EXPORT_OMIT)
		{
			return false;
		}

		$obj_id = ilObject::_lookupObjId($a_ref_id);
			
		$this->xmlStartTag(
			'Item',
			array(
				'RefId'		=> $a_ref_id,
				'Id'		=> $obj_id,
				'Title'		=> ilObject::_lookupTitle($obj_id),
				'Type'		=> ilObject::_lookupType($obj_id)
			)
		);
		
		$this->writeCourseItemInformation($a_ref_id);
		
		foreach($tree->getChilds($a_ref_id) as $node)
		{
			$this->writeSubitems($node['child']);
		}
		
		$this->xmlEndTag('Item');
		return true;
	}
	
	
	/**
	 * Write course item information 
	 * Starting time, ending time...
	 * @param int $a_ref_id
	 * @return 
	 */
	protected function writeCourseItemInformation($a_ref_id)
	{
		include_once './Services/Object/classes/class.ilObjectActivation.php';
		$item = ilObjectActivation::getItem($a_ref_id);
		
		$this->xmlStartTag(
			'Timing',
			array(
				'Type'		=> $item['timing_type'],
				'Visible'	=> $item['visible'],
				'Changeable'=> $item['changeable'],
				)
		);
		if($item['timing_start'])
		{
			$tmp_date = new ilDateTime($item['timing_start'],IL_CAL_UNIX);
			$this->xmlElement('Start',array(),$tmp_date->get(IL_CAL_DATETIME,'',ilTimeZone::UTC));
		}
		if($item['timing_end'])
		{
			$tmp_date = new ilDateTime($item['timing_end'],IL_CAL_UNIX);
			$this->xmlElement('End',array(),$tmp_date->get(IL_CAL_DATETIME,'',ilTimeZone::UTC));
		}
		if($item['suggestion_start'])
		{
			$tmp_date = new ilDateTime($item['suggestion_start'],IL_CAL_UNIX);
			$this->xmlElement('SuggestionStart',array(),$tmp_date->get(IL_CAL_DATETIME,'',ilTimeZone::UTC));
		}
		if($item['suggestion_end'])
		{
			$tmp_date = new ilDateTime($item['suggestion_end'],IL_CAL_UNIX);
			$this->xmlElement('SuggestionEnd',array(),$tmp_date->get(IL_CAL_DATETIME,'',ilTimeZone::UTC));
		}
		if($item['earliest_start'])
		{
			$tmp_date = new ilDateTime($item['earliest_start'],IL_CAL_UNIX);
			$this->xmlElement('EarliestStart',array(),$tmp_date->get(IL_CAL_DATETIME,'',ilTimeZone::UTC));
		}
		if($item['latest_end'])
		{
			$tmp_date = new ilDateTime($item['latest_end'],IL_CAL_UNIX);
			$this->xmlElement('LatestEnd',array(),$tmp_date->get(IL_CAL_DATETIME,'',ilTimeZone::UTC));
		}
		
		$this->xmlEndTag('Timing');
			
	}
	
	/**
	 * Build XML header
	 * @return 
	 */
	protected function buildHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE Container PUBLIC \"-//ILIAS//DTD Container//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_container_4_1.dtd\">");
		$this->xmlSetGenCmt("Container object");
		$this->xmlHeader();

		return true;
	}
	
	
}
?>