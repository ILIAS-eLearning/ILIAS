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
	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * @var ilObjectDefinition
	 */
	protected $obj_definition;

	private $source = 0;
	private $mapping = null;
	private $xml = '';
	
	private $sxml = null;
	private $root_id = 0;
	
	static public $style_map = array();

	/**
	 * Constructor
	 */
	public function __construct(ilImportMapping $mapping,$xml = '')
	{
		global $DIC;

		$this->settings = $DIC->settings();
		$this->obj_definition = $DIC["objDefinition"];
		$this->mapping = $mapping;
		$this->xml = $xml;
	}

	/**
	 * Get ilImportMapping object
	 *
	 * @return ilImportMapping $map
	 */
	public function getMapping()
	{
		return $this->mapping;
	}
	
	public function parse($a_root_id)
	{
		$this->sxml = simplexml_load_string($this->xml);
		$this->root_id = $a_root_id;
		
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
		$ilSetting = $this->settings;
		
		$title = (string) $item['Title'];
		$ref_id = (string) $item['RefId'];
		$obj_id = (string) $item['Id'];
		$type = (string) $item['Type'];
		
		
		$new_ref = $this->getMapping()->getMapping('Services/Container', 'refs', $ref_id);

		if(
			!$new_ref &&
			($obj_id == $this->root_id)
		)
		{
			// if container without subitems a dummy container has already been created
			// see ilImportContainer::createDummy()			
			$new_ref = $this->mapping->getMapping('Services/Container', 'refs', 0);
			
			// see below and ilContainerImporter::finalProcessing()
			$this->mapping->addMapping('Services/Container','objs', $obj_id, ilObject::_lookupObjId($new_ref));
		}
		
		if(!$new_ref)
		{
			$new_ref = $this->createObject($ref_id,$obj_id,$type,$title,$a_parent_node);	
		}

		// Course item information		
		foreach($item->Timing as $timing)
		{
			$this->parseTiming($new_ref,$a_parent_node,$timing);
		}

		foreach($item->Item as $subitem)
		{
			$this->initItem($subitem, $new_ref);
		}
			
		$new_obj_id = $this->mapping->getMapping('Services/Container', 'objs', $obj_id);
			
		// style
		if((int)$item['Style'])
		{
			self::$style_map[(int)$item['Style']][] = $new_obj_id;
		}
		
		// pages
		if($ilSetting->get('enable_cat_page_edit', false))
		{								
			if((bool)$item['Page'])
			{			
				$this->mapping->addMapping('Services/COPage', 'pg', 'cont:'.$obj_id, 'cont:'.$new_obj_id);		
			}
			
			if((bool)$item['StartPage'])
			{				
				$this->mapping->addMapping('Services/COPage', 'pg', 'cstr:'.$obj_id, 'cstr:'.$new_obj_id);		
			}			
		}		
	}
	
	/**
	 * Parse timing info
	 * @param object $a_ref_id
	 * @param object $a_parent_id
	 * @param object $timing
	 * @return 
	 */
	protected function parseTiming($a_ref_id,$a_parent_id,$timing)
	{
		$type = (string) $timing['Type'];
		$visible = (string) $timing['Visible'];
		$changeable = (string) $timing['Changeable'];
		
		include_once './Services/Object/classes/class.ilObjectActivation.php';
		$crs_item = new ilObjectActivation();
		$crs_item->setTimingType($type);
		$crs_item->toggleVisible((bool) $visible);
		$crs_item->toggleChangeable((bool) $changeable);
		
		foreach($timing->children() as $sub)
		{
			switch((string) $sub->getName())
			{
				case 'Start':
					$dt = new ilDateTime((string) $sub,IL_CAL_DATETIME,ilTimeZone::UTC);
					$crs_item->setTimingStart($dt->get(IL_CAL_UNIX));
					break;
				
				case 'End':
					$dt = new ilDateTime((string) $sub,IL_CAL_DATETIME,ilTimeZone::UTC);
					$crs_item->setTimingEnd($dt->get(IL_CAL_UNIX));
					break;

				case 'SuggestionStart':
					$dt = new ilDateTime((string) $sub,IL_CAL_DATETIME,ilTimeZone::UTC);
					$crs_item->setSuggestionStart($dt->get(IL_CAL_UNIX));
					break;

				case 'SuggestionEnd':
					$dt = new ilDateTime((string) $sub,IL_CAL_DATETIME,ilTimeZone::UTC);
					$crs_item->setSuggestionEnd($dt->get(IL_CAL_UNIX));
					break;
				
				case 'EarliestStart':
					$dt = new ilDateTime((string) $sub,IL_CAL_DATETIME,ilTimeZone::UTC);
					$crs_item->setEarliestStart($dt->get(IL_CAL_UNIX));
					break;

				case 'LatestEnd':
					$dt = new ilDateTime((string) $sub,IL_CAL_DATETIME,ilTimeZone::UTC);
					$crs_item->setLatestEnd($dt->get(IL_CAL_UNIX));
					break;
			}
		}
		
		
		if($crs_item->getTimingStart())
		{
			$crs_item->update($a_ref_id, $a_parent_id);
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
		$objDefinition = $this->obj_definition;

		// A mapping for this object already exists => create reference
		$new_obj_id = $this->getMapping()->getMapping('Services/Container', 'objs', $obj_id);
		if($new_obj_id)
		{
			include_once './Services/Object/classes/class.ilObjectFactory.php';
			$obj = ilObjectFactory::getInstanceByObjId($new_obj_id,false);
			if($obj instanceof  ilObject)
			{
				$obj->createReference();
				$obj->putInTree($parent_node);
				$obj->setPermissions($parent_node);
				$this->mapping->addMapping('Services/Container','refs',$ref_id,$obj->getRefId());
				return $obj->getRefId();
			}
		}

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