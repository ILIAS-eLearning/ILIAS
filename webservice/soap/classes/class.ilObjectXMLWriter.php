<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";

/**
* XML writer class
*
* Class to simplify manual writing of xml documents.
* It only supports writing xml sequentially, because the xml document
* is saved in a string with no additional structure information.
* The author is responsible for well-formedness and validity
* of the xml document.
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*/
class ilObjectXMLWriter extends ilXmlWriter
{
	const TIMING_DEACTIVATED = 0;
	const TIMING_TEMPORARILY_AVAILABLE = 1;
	const TIMING_PRESETTING = 2;
	
	const TIMING_VISIBILITY_OFF = 0;
	const TIMING_VISIBILITY_ON = 1;
	
	
	var $ilias;

	var $xml;
	var $enable_operations = false;
	var $objects = array();
	var $user_id = 0;
	
	protected $check_permission = false;

	/**
	* constructor
	* @param	string	xml version
	* @param	string	output encoding
	* @param	string	input encoding
	* @access	public
	*/
	function ilObjectXMLWriter()
	{
		global $ilias,$ilUser;

		parent::ilXmlWriter();

		$this->ilias =& $ilias;
		$this->user_id = $ilUser->getId();
	}
	
	public function enablePermissionCheck($a_status)
	{
		$this->check_permission = $a_status;
	}
	
	public function isPermissionCheckEnabled()
	{
		return $this->check_permission;
	}

	function setUserId($a_id)
	{
		$this->user_id = $a_id;
	}
	function getUserId()
	{
		return $this->user_id;
	}

	function enableOperations($a_status)
	{
		$this->enable_operations = $a_status;

		return true;
	}

	function enabledOperations()
	{
		return $this->enable_operations;
	}

	function setObjects($objects)
	{
		$this->objects = $objects;
	}

	function __getObjects()
	{
		return $this->objects ? $this->objects : array();
	}

	function start()
	{
		global $ilAccess,$objDefinition;
		
		$this->__buildHeader();

		foreach($this->__getObjects() as $object)
		{
			if(method_exists($object, 'getType') and $objDefinition->isRBACObject($object->getType()))
			{
				if($this->isPermissionCheckEnabled() and !$ilAccess->checkAccessOfUser($this->getUserId(),'read','',$object->getRefId()))
				{
					continue;
				}
			}
			$this->__appendObject($object);
		}
		$this->__buildFooter();

		return true;
	}

	function getXML()
	{
		return $this->xmlDumpMem(false);
	}


	// PRIVATE
	function __appendObject(&$object)
	{

	  global $tree, $rbacreview;

	  	$id = $object->getId();
		if ($object->getType() == "role" && $rbacreview->isRoleDeleted($id))
		{
			return;				
		}			
	  
		$attrs = array('type' => $object->getType(),
			       'obj_id' => $id);

		$this->xmlStartTag('Object',$attrs);
		$this->xmlElement('Title',null,$object->getTitle());
		$this->xmlElement('Description',null,$object->getDescription());
		$this->xmlElement('Owner',null,$object->getOwner());
		$this->xmlElement('CreateDate',null,$object->getCreateDate());
		$this->xmlElement('LastUpdate',null,$object->getLastUpdateDate());
		$this->xmlElement('ImportId',null,$object->getImportId());

		foreach(ilObject::_getAllReferences($object->getId()) as $ref_id)
		{
			if (!$tree->isInTree($ref_id))
				continue;
			
			$attr = array('ref_id' => $ref_id, 'parent_id'=> $tree->getParentId(intval($ref_id)));
			$attr['accessInfo'] = $this->__getAccessInfo($object,$ref_id);			
			$this->xmlStartTag('References',$attr);
			$this->__appendTimeTargets($ref_id);
			$this->__appendOperations($ref_id,$object->getType());
			$this->__appendPath ($ref_id);
			$this->xmlEndTag('References');
		}
		$this->xmlEndTag('Object');
	}
	
	/**
	 * Append time target settings for items inside of courses 
	 * @param int $ref_id Reference id of object
	 * @return void
	 */
	public function __appendTimeTargets($a_ref_id)
	{
		global $tree;
		
		if(!$tree->checkForParentType($a_ref_id,'crs')) {
			return;	
		}
		include_once('./Services/Object/classes/class.ilObjectActivation.php');
		$time_targets = ilObjectActivation::getItem($a_ref_id);
		
		switch($time_targets['timing_type'])
		{
			case ilObjectActivation::TIMINGS_DEACTIVATED:
				$type = self::TIMING_DEACTIVATED;
				break;
			case ilObjectActivation::TIMINGS_ACTIVATION:
				$type = self::TIMING_TEMPORARILY_AVAILABLE;
				break;
			case ilObjectActivation::TIMINGS_PRESETTING:
				$type = self::TIMING_PRESETTING;
				break;
			default:
				$type = self::TIMING_DEACTIVATED;
				break;
		}
		
		$this->xmlStartTag('TimeTarget',array('type' => $type));
		
#		if($type == self::TIMING_TEMPORARILY_AVAILABLE)
		{
			$vis = $time_targets['visible'] == 0 ? self::TIMING_VISIBILITY_OFF : self::TIMING_VISIBILITY_ON;
			$this->xmlElement('Timing',
				array('starting_time' => $time_targets['timing_start'],
					'ending_time' => $time_targets['timing_end'],
					'visibility' => $vis));
				
		}
#		if($type == self::TIMING_PRESETTING)
		{
			if($time_targets['changeable'] or 1)
			{
				$this->xmlElement('Suggestion',
					array('starting_time' => $time_targets['suggestion_start'],
						'ending_time' => $time_targets['suggestion_end'],
						'changeable' => $time_targets['changeable'],
						'earliest_start' => $time_targets['earliest_start'],
						'latest_end' => $time_targets['latest_end']));
			}
			else
			{
				$this->xmlElement('Suggestion',
					array('starting_time' => $time_targets['suggestion_start'],
						'ending_time' => $time_targets['suggestion_end'],
						'changeable' => $time_targets['changeable']));
			}
		}
		$this->xmlEndTag('TimeTarget');
		return;		
	}
	
	

	function __appendOperations($a_ref_id,$a_type)
	{
		global $ilAccess,$rbacreview;

		if($this->enabledOperations())
		{
		    $ops = $rbacreview->getOperationsOnTypeString($a_type);
		    if (is_array($ops))
		    {
    			foreach($ops as $ops_id)
    			{
    				$operation = $rbacreview->getOperation($ops_id);
					
    				if(count ($operation) && $ilAccess->checkAccessOfUser($this->getUserId(),$operation['operation'],'view',$a_ref_id))
    				{
    					$this->xmlElement('Operation',null,$operation['operation']);
    				}
    			}
		    }
		}
		return true;
	}


	function __appendPath ($refid){
		ilObjectXMLWriter::appendPathToObject($this, $refid);
	}
	
	function __buildHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE Objects PUBLIC \"-//ILIAS//DTD ILIAS Repositoryobjects//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_object_4_0.dtd\">");
		$this->xmlSetGenCmt("Export of ILIAS objects");
		$this->xmlHeader();
		$this->xmlStartTag("Objects");
		return true;
	}

	function __buildFooter()
	{
		$this->xmlEndTag('Objects');
	}

	function __getAccessInfo(&$object,$ref_id)
	{
		global $ilAccess;

		include_once 'Services/AccessControl/classes/class.ilAccessHandler.php';

		$ilAccess->checkAccessOfUser($this->getUserId(),'read','view',$ref_id,$object->getType(),$object->getId());

		if(!$info = $ilAccess->getInfo())
		{
			return 'granted';
		}
		else
		{
			return $info[0]['type'];
		}
	}

	
	public static function appendPathToObject ($writer, $refid){
		global $tree, $lng;
		$items = $tree->getPathFull($refid);
		$writer->xmlStartTag("Path");
		foreach ($items as $item) {
			if ($item["ref_id"] == $refid)
				continue;
			if ($item["type"] == "root")
			{
				$item["title"] = $lng->txt("repository");
			}			
			$writer->xmlElement("Element", array("ref_id" => $item["ref_id"], "type" => $item["type"]), $item["title"]);
		}		
		$writer->xmlEndTag("Path");						
	}
	
}


?>
