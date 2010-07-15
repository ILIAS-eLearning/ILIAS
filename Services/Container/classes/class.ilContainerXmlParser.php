<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Export/classes/class.ilExportOptions.php';

/**
* XML parser for container structure
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesContainer
*/
class ilContainerXmlParser
{
	private $source = 0;
	private $mapping = null;
	private $xml = '';
	
	private $sxml = null;

	/**
	 * Constructor
	 */
	public function __construct(ilImportMapping $mapping,$xml = '')
	{
		$this->mapping = $mapping;
		$this->xml = $xml;
	}
	
	public function parse()
	{
		$this->sxml = simplexml_load_string($this->xml);
		
		foreach($this->sxml->Item as $item)
		{
			$this->initItem($item,$this->mapping->getTargetId());
		}
	}
	
	/**
	 * Init Item
	 * @param object $item
	 * @param object $a_parent_node
	 * @return 
	 */
	protected function initItem($item, $a_parent_node)
	{
		$title = (string) $item['Title'];
		$ref_id = (string) $item['RefId'];
		$obj_id = (string) $item['Id'];
		$type = (string) $item['Type'];

		$new_ref = $this->createObject($ref_id,$obj_id,$type,$title,$a_parent_node);		
		
		foreach($item->Item as $subitem)
		{
			$this->initItem($subitem, $new_ref);
		}
	}
	
	/**
	 * Create the objects
	 * @param object $ref_id
	 * @param object $obj_id
	 * @param object $type
	 * @param object $title
	 * @param object $parent_node
	 * @return 
	 */
	protected function createObject($ref_id,$obj_id,$type,$title,$parent_node)
	{
		global $objDefinition;

		$class_name = "ilObj".$objDefinition->getClassName($type);
		$location = $objDefinition->getLocation($type);

		include_once($location."/class.".$class_name.".php");
		$new = new $class_name();
		$new->setTitle($title);
		$new->create(true);
		$new->createReference();
		$new->putInTree($parent_node);
		$new->setPermissions($parent_node);
		
		$this->mapping->addMapping('Services/Container','objs', $obj_id, $new->getId());
		$this->mapping->addMapping('Services/Container','refs',$ref_id,$new->getRefId());
		
		return $new->getRefId();
	}
}
?>