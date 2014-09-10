<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";

/**
* XML writer for folders
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesFolder
*/
class ilFolderXmlWriter extends ilXmlWriter
{
	private $add_header = true;
	
	private $obj_id = 0;
	private $folder = null;

	/**
	 * Constructor
	 */
	public function __construct($a_add_header)
	{
		$this->add_header = $a_add_header;
		parent::__construct();
	}
	
	/**
	 * Set obj_id of weblink object
	 * @param int obj_id
	 * @return bool
	 */
	public function setObjId($a_obj_id)
	{
		$this->obj_id = $a_obj_id;
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
		$this->xmlStartTag('Folder',array('Id' => $this->folder->getId()));
		$this->xmlElement('Title',array(),$this->folder->getTitle());
		$this->xmlElement('Description',array(),$this->folder->getDescription());
		include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
		ilContainerSortingSettings::_exportContainerSortingSettings($this,$this->obj_id);
		$this->xmlEndTag('Folder');
	}
	
	/**
	 * Build XML header
	 * @return 
	 */
	protected function buildHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE WebLinks PUBLIC \"-//ILIAS//DTD WebLinkAdministration//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_fold_4_5.dtd\">");
		$this->xmlSetGenCmt("Export of a ILIAS Folder");
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
		
		if(!$this->obj_id)
		{
			throw new UnexpectedValueException('No obj_id given: ');
		}
		include_once './Services/Object/classes/class.ilObjectFactory.php';
		if(!$this->folder = ilObjectFactory::getInstanceByObjId($this->obj_id,false))
		{
			throw new UnexpectedValueException('Invalid obj_id given: '.$this->obj_id);
		}
		if($this->folder->getType() != 'fold')
		{
			throw new UnexpectedValueException('Invalid obj_id given. Object is not of type folder');
		}
	}
}
?>