<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './webservice/soap/classes/class.ilSoapAdministration.php';

/**
* Soap methods for adminstrating web links
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*/
class ilSoapWebLinkAdministration extends ilSoapAdministration
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Get Weblink xml
	 * @param string $a_sid
	 * @param int $a_ref_id
	 * @return string
	 */
	public function readWebLink($sid,$ref_id)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		if(!strlen($ref_id))
		{
			return $this->__raiseError('No ref id given. Aborting!',
									   'Client');
		}
		global $rbacsystem, $tree, $ilLog;

		// get obj_id
		if(!$obj_id = ilObject::_lookupObjectId($ref_id))
		{
			return $this->__raiseError('No weblink found for id: '.$ref_id,
									   'Client');
		}

		if(ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError("Parent with ID $ref_id has been deleted.", 'Client');
		}
		
		// Check access
		$permission_ok = false;
		$write_permission_ok = false;
		foreach($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id)
		{
			if($rbacsystem->checkAccess('edit',$ref_id))
			{
				$write_permission_ok = true;
				break;
			}		    
		    if($rbacsystem->checkAccess('read',$ref_id))
			{
				$permission_ok = true;
				break;
			}
			
		}

		if(!$permission_ok && !$write_permission_ok)
		{
			return $this->__raiseError('No permission to edit the object with id: '.$ref_id,
									   'Server');
		}

		try
		{
			include_once './Modules/WebResource/classes/class.ilWebLinkXmlWriter.php';
			$writer = new ilWebLinkXmlWriter();
			$writer->setObjId($obj_id);
			$writer->write();
		
			return $writer->xmlDumpMem(true);
		}
		catch(UnexpectedValueException $e)
		{
			return $this->__raiseError($e->getMessage(), 'Client');
		}
	}
	
    /**
     * add an exercise with id.
     *
     * @param string $session_id    current session
     * @param int $target_id refid of parent in repository
     * @param string $weblink_xml  xml description of weblink
     *
     * @return int reference id in the tree, 0 if not successful
     */
	public function createWebLink($sid,$target_id, $weblink_xml) 
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		global $rbacsystem, $tree, $ilLog;

		if(!$target_obj =& ilObjectFactory::getInstanceByRefId($target_id,false))
		{
			return $this->__raiseError('No valid target given.', 'Client');
		}

		if(ilObject::_isInTrash($target_id))
		{
			return $this->__raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_OBJECT_DELETED');
		}

		// Check access
		// TODO: read from object definition
		$allowed_types = array('cat','grp','crs','fold','root');
		if(!in_array($target_obj->getType(), $allowed_types))
		{
			return $this->__raiseError('No valid target type. Target must be reference id of "course, group, root, category or folder"', 'Client');
		}

		if(!$rbacsystem->checkAccess('create',$target_id,"webr"))
		{
			return $this->__raiseError('No permission to create weblink in target  '.$target_id.'!', 'Client');
		}
		
		
		// create object, put it into the tree and use the parser to update the settings
		include_once './Modules/WebResource/classes/class.ilObjLinkResource.php';
		include_once './Modules/WebResource/classes/class.ilWebLinkXmlParser.php';

		$webl = new ilObjLinkResource();
		$webl->setTitle('XML Import');
		$webl->create(true);
		$webl->createReference();
		$webl->putInTree($target_id);
		$webl->setPermissions($target_id);
		
		try 
		{
			$parser = new ilWebLinkXmlParser($webl,$weblink_xml);
			$parser->setMode(ilWebLinkXmlParser::MODE_CREATE);
			$parser->start();
		}
		catch(ilSaxParserException $e)
		{
			return $this->__raiseError($e->getMessage(),'Client');
		}
		catch(ilWebLinkXMLParserException $e)
		{
			return $this->__raiseError($e->getMessage(),'Client');
		}
		
		// Check if required
		return $webl->getRefId();
	}
	
    /**
     * update a weblink with id.
     *
     * @param string $session_id    current session
     * @param int $ref_id   refid id of weblink in repository
     * @param string $weblink_xml  xml description
     *
     * @return boolean true, if update successful, false otherwise
     */
	function updateWebLink($sid, $ref_id, $weblink_xml) 
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		global $rbacsystem, $tree, $ilLog;

		if(ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError('Cannot perform update since weblink has been deleted.', 'CLIENT_OBJECT_DELETED');
		}
		// get obj_id
		if(!$obj_id = ilObject::_lookupObjectId($ref_id))
		{
			return $this->__raiseError('No weblink found for id: '.$ref_id,
									   'CLIENT_OBJECT_NOT_FOUND');
		}

		// Check access
		$permission_ok = false;
		foreach($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id)
		{
			if($rbacsystem->checkAccess('edit',$ref_id))
			{
				$permission_ok = true;
				break;
			}
		}

		if(!$permission_ok)
		{
			return $this->__raiseError('No permission to edit the weblink with id: '.$ref_id,
									'Server');
		}

		$webl = ilObjectFactory::getInstanceByObjId($obj_id, false);
		if(!is_object($webl) or $webl->getType()!= "webr")
		{
			return $this->__raiseError('Wrong obj id or type for weblink with id '.$ref_id,
									'Client');
		}

		try 
		{
			include_once './Modules/WebResource/classes/class.ilWebLinkXmlParser.php';
			$parser = new ilWebLinkXmlParser($webl,$weblink_xml);
			$parser->setMode(ilWebLinkXmlParser::MODE_UPDATE);
			$parser->start();
		}
		catch(ilSaxParserException $e)
		{
			return $this->__raiseError($e->getMessage(),'Client');
		}
		catch(ilWebLinkXMLParserException $e)
		{
			return $this->__raiseError($e->getMessage(),'Client');
		}
		
		// Check if required
		return true;
	}
}
?>
