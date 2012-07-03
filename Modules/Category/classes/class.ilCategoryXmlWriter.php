<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";

/**
 * Class for category export
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilCategoryXmlWriter extends ilXmlWriter
{
	const MODE_SOAP = 1;
	const MODE_EXPORT = 2;
	
	private $mode = self::MODE_SOAP;
	private $xml;
	private $category;

	/**
	* constructor
	* @param	string	xml version
	* @param	string	output encoding
	* @param	string	input encoding
	* @access	public
	*/
	public function __construct(ilObjCategory $cat = null)
	{
		global $ilias;

		parent::__construct();

		$this->category = $cat;
	}

	/**
	 * Set export mode
	 * @param int $a_mode
	 */
	public function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}

	/**
	 * get export mode
	 * @return int
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * Get category object
	 * @return ilObjCategory
	 */
	public function getCategory()
	{
		return $this->category;
	}


	public function export()
	{
		if($this->getMode() == self::MODE_EXPORT)
		{
			$this->buildHeader();
			$this->buildCategory();
			$this->buildFooter();
		}
		
	}

	/**
	 * get XML
	 * @return string
	 */
	public function getXml()
	{
		return $this->xmlDumpMem(false);
	}

	/**
	 * Build xml header
	 * @global <type> $ilSetting
	 * @return <type>
	 */
	protected  function buildHeader()
	{
		global $ilSetting;

		$this->xmlSetDtdDef("<!DOCTYPE category PUBLIC \"-//ILIAS//DTD Group//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_category_4_3.dtd\">");
		$this->xmlSetGenCmt("Export of ILIAS category ". $this->getCategory()->getId()." of installation ".$ilSetting->get('inst_id').".");
		$this->xmlHeader();


		return true;
	}

	/**
	 * Build category xml
	 */
	protected function buildCategory()
	{
		$this->xmlStartTag('Category');
	}
	
	/**
	 * Add footer elements
	 */
	protected function buildFooter()
	{
		$this->xmlEndTag('Category');
	}


}
?>