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
		
		foreach($tree->getChilds($a_ref_id) as $node)
		{
			$this->writeSubitems($node['child']);
		}
		
		$this->xmlEndTag('Item');
		return true;
	}
	
	/**
	 * Build XML header
	 * @return 
	 */
	protected function buildHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE WebLinks PUBLIC \"-//ILIAS//DTD WebLinkAdministration//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_weblinks_4_0.dtd\">");
		$this->xmlSetGenCmt("WebLink Object");
		$this->xmlHeader();

		return true;
	}
	
	
}
?>