<?php
  /*
   +-----------------------------------------------------------------------------+
   | ILIAS open source                                                           |
   +-----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
   |                                                                             |
   | This program is free software; you can redistribute it and/or               |
   | modify it under the terms of the GNU General Public License                 |
   | as published by the Free Software Foundation; either version 2              |
   | of the License, or (at your option) any later version.                      |
   |                                                                             |
   | This program is distributed in the hope that it will be useful,             |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
   | GNU General Public License for more details.                                |
   |                                                                             |
   | You should have received a copy of the GNU General Public License           |
   | along with this program; if not, write to the Free Software                 |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
   +-----------------------------------------------------------------------------+
  */


  /**
   * Soap object administration methods
   *
   * @author Stefan Meyer <meyer@leifos.com>
   * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
   * @version $Id$
   *
   * @package ilias
   */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapObjectAdministration extends ilSoapAdministration
{
	/**
	 * Add desktop items for user
	 * @param string $sid
	 * @param int $user_id
	 * @param int[] $reference_ids
	 * @return bool
	 *
	 */
	public function addDesktopItems($sid, $user_id, $reference_ids)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}

		global $DIC;

		$access = $DIC->rbac()->system();
		$logger = $DIC->logger()->wsrv();

		if(!$access->checkAccess('edit_userassignment', ROLE_FOLDER_ID))
		{
			$logger->warning('Missing permission "edit_userassignment".');
			return $this->__raiseError(
				'Missing permission "edit_userassignment".',
				'Client'
			);
		}

		$user = ilObjectFactory::getInstanceByObjId($user_id, false);
		if(!$user instanceof ilObjUser)
		{
			$logger->warning('Invalid user id given. Cannot instantiate user for id: ' . $user_id);
			return $this->__raiseError(
				'Invalid user id given. Cannot instantiate user for id: ' . $user_id,
				'Client'
			);
		}
		$num_added = 0;
		foreach($reference_ids as $ref_id)
		{
			// short "validation" of reference id
			$ref_obj = ilObjectFactory::getInstanceByRefId($ref_id,false);
			if(!$ref_obj instanceof ilObject)
			{
				$logger->warning('Invalid reference id passed to SOAP::addDesktopItems: ' . $ref_id);
				continue;
			}

			$num_added++;
			ilObjUser::_addDesktopItem(
				$user->getId(),
				$ref_id,
				ilObject::_lookupType($ref_id,true)
			);

		}
		return $num_added;
	}

	/**
	 * Remove desktop items for user
	 * @param string $sid
	 * @param int $user_id
	 * @param int[] $reference_ids
	 * @return bool
	 *
	 */
	public function removeDesktopItems($sid, $user_id, $reference_ids)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}

		global $DIC;

		$access = $DIC->rbac()->system();
		$logger = $DIC->logger()->wsrv();

		if(!$access->checkAccess('edit_userassignment', ROLE_FOLDER_ID))
		{
			$logger->warning('Missing permission "edit_userassignment".');
			return $this->__raiseError(
				'Missing permission "edit_userassignment".',
				'Client'
			);
		}

		$user = ilObjectFactory::getInstanceByObjId($user_id, false);
		if(!$user instanceof ilObjUser)
		{
			$logger->warning('Invalid user id given. Cannot instantiate user for id: ' . $user_id);
			return $this->__raiseError(
				'Invalid user id given. Cannot instantiate user for id: ' . $user_id,
				'Client'
			);
		}
		$num_removed = 0;
		foreach($reference_ids as $ref_id)
		{
			// short "validation" of reference id
			$ref_obj = ilObjectFactory::getInstanceByRefId($ref_id,false);
			if(!$ref_obj instanceof ilObject)
			{
				$logger->warning('Invalid reference id passed to SOAP::removeDesktopItems: ' . $ref_id);
				continue;
			}

			$num_added++;
			ilObjUser::_dropDesktopItem(
				$user->getId(),
				$ref_id,
				ilObject::_lookupType($ref_id,true)
			);

		}
		return $num_removed;
	}


	function getObjIdByImportId($sid,$import_id)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		if(!$import_id)
		{
			return $this->__raiseError('No import id given.',
									   'Client');
		}

		global $DIC;

		$ilLog = $DIC['ilLog'];

		$obj_id = ilObject::_lookupObjIdByImportId($import_id);
		$ilLog->write("SOAP getObjIdByImportId(): import_id = ".$import_id.' obj_id = '.$obj_id);

		return $obj_id ? $obj_id : "0";
	}

	function getRefIdsByImportId($sid,$import_id)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		if(!$import_id)
		{
			return $this->__raiseError('No import id given.',
									   'Client');
		}

		global $DIC;

		$tree = $DIC['tree'];

		$obj_id = ilObject::_lookupObjIdByImportId($import_id);


		$ref_ids = ilObject::_getAllReferences($obj_id);

		foreach($ref_ids as $ref_id)
		{
			// only get non deleted reference ids
			if ($tree->isInTree($ref_id))
			{
				$new_refs[] = $ref_id;
			}
		}
		return $new_refs ? $new_refs : array();
	}

	function getRefIdsByObjId($sid,$obj_id)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		if(!$obj_id)
		{
			return $this->__raiseError('No object id given.',
									   'Client');
		}

		$ref_ids = ilObject::_getAllReferences($obj_id);
		foreach($ref_ids as $ref_id)
		{
			$new_refs[] = $ref_id;
		}
		return $new_refs ? $new_refs : array();
	}

	/**
	*	Returns a array of object ids which match the references id, given by a comma seperated string.
	*
	*	@param	string $sid	Session ID
	*	@param	array of int $ref ids as comma separated list
	*	@return	array of ref ids, same order as object ids there for there might by duplicates
	*
	*/
	function getObjIdsByRefIds($sid, $ref_ids)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}


		if(!count($ref_ids) || !is_array ($ref_ids))
		{
			return $this->__raiseError('No reference id(s) given.', 'Client');
		}

		$obj_ids = array();
		if (count($ref_ids)) {
			foreach ($ref_ids as $ref_id)
			{
				$ref_id = trim($ref_id);
				if (!is_numeric($ref_id)){
					return $this->__raiseError('Reference ID has to be numeric. Value: '.$ref_id, 'Client');
				}

				$obj_id = ilObject::_lookupObjectId($ref_id);
				if (!$obj_id){
					return $this->__raiseError('No object found for reference ID. Value: '.$ref_id, 'Client');
				}
				if (!ilObject::_hasUntrashedReference($obj_id)){
					return $this->__raiseError('No untrashed reference found for reference ID. Value: '.$ref_id, 'Client');
				}
				$obj_ids[] = $obj_id;
			}
		}
		return $obj_ids;
	}



	function getObjectByReference($sid,$a_ref_id,$user_id)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		if(!is_numeric($a_ref_id))
		{
			return $this->__raiseError('No valid reference id given. Please choose an existing reference id of an ILIAS object',
									   'Client');
		}

		if(!$tmp_obj = ilObjectFactory::getInstanceByRefId($a_ref_id,false))
		{
			return $this->__raiseError('Cannot create object instance!','Server');
		}


		if(ilObject::_isInTrash($a_ref_id))
		{
			return $this->__raiseError("Object with ID $a_ref_id has been deleted.", 'Client');
		}

		include_once './webservice/soap/classes/class.ilObjectXMLWriter.php';

		$xml_writer = new ilObjectXMLWriter();
		$xml_writer->enablePermissionCheck(true);
		if($user_id)
		{
			$xml_writer->setUserId($user_id);
			$xml_writer->enableOperations(true);
		}
		$xml_writer->setObjects(array($tmp_obj));
		if($xml_writer->start())
		{
			return $xml_writer->getXML();
		}

		return $this->__raiseError('Cannot create object xml !','Server');
	}

	function getObjectsByTitle($sid,$a_title,$user_id)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		if(!strlen($a_title))
		{
			return $this->__raiseError('No valid query string given.',
									   'Client');
		}

		include_once './Services/Search/classes/class.ilQueryParser.php';

		$query_parser = new ilQueryParser($a_title);
		$query_parser->setMinWordLength(0,true);
		$query_parser->setCombination(QP_COMBINATION_AND);
		$query_parser->parse();
		if(!$query_parser->validate())
		{
			return $this->__raiseError($query_parser->getMessage(),
									   'Client');
		}

		include_once './Services/Search/classes/class.ilObjectSearchFactory.php';

		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search = new ilLikeObjectSearch($query_parser);

		#$object_search =& ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
		$object_search->setFields(array('title'));
		$object_search->appendToFilter('role');
		$object_search->appendToFilter('rolt');
		$res =& $object_search->performSearch();
		if($user_id)
		{
			$res->setUserId($user_id);
		}

		$res->filter(ROOT_FOLDER_ID,true);
		
		$objs = array();
		foreach($res->getUniqueResults() as $entry)
		{
			if($entry['type'] == 'role' or $entry['type'] == 'rolt')
			{
				if($tmp = ilObjectFactory::getInstanceByObjId($entry['obj_id'],false))
				{
					$objs[] = $tmp;
				}
				continue;
			}
			if($tmp = ilObjectFactory::getInstanceByRefId($entry['ref_id'],false))
			{
				$objs[] = $tmp;
			}
		}
		if(!count($objs))
		{
			return '';
		}

		include_once './webservice/soap/classes/class.ilObjectXMLWriter.php';

		$xml_writer = new ilObjectXMLWriter();
		$xml_writer->enablePermissionCheck(true);
		if($user_id)
		{
			$xml_writer->setUserId($user_id);
			$xml_writer->enableOperations(true);
		}
		$xml_writer->setObjects($objs);
		if($xml_writer->start())
		{
			return $xml_writer->getXML();
		}

		return $this->__raiseError('Cannot create object xml !','Server');
	}

	function searchObjects($sid,$types,$key,$combination,$user_id)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		if(!is_array($types))
		{
			return $this->__raiseError('Types must be an array of object types.',
									   'Client');
		}
		if($combination != 'and' and $combination != 'or')
		{
			return $this->__raiseError('No valid combination given. Must be "and" or "or".',
									   'Client');
		}

		// begin-patch fm
		include_once './Services/Search/classes/class.ilSearchSettings.php';
		if(ilSearchSettings::getInstance()->enabledLucene()) {

			ilSearchSettings::getInstance()->setMaxHits(25);

			$typeFilterQuery = '';
			if (is_array($types)) {
				foreach ($types as $objectType) {
					if (0 === strlen($typeFilterQuery)) {
						$typeFilterQuery .= '+( ';
					} else {
						$typeFilterQuery .= 'OR';
					}
					$typeFilterQuery .= (' type:' . (string)$objectType . ' ');
				}
				$typeFilterQuery .= ') ';
			}

			include_once './Services/Search/classes/Lucene/class.ilLuceneQueryParser.php';
			$query_parser = new ilLuceneQueryParser($typeFilterQuery . $key);
			$query_parser->parse();

			include_once './Services/Search/classes/Lucene/class.ilLuceneSearcher.php';
			$searcher = ilLuceneSearcher::getInstance($query_parser);
			$searcher->search();

			include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultFilter.php';
			include_once './Services/Search/classes/Lucene/class.ilLucenePathFilter.php';
			$filter = ilLuceneSearchResultFilter::getInstance($user_id);
			$filter->setCandidates($searcher->getResult());
			$filter->filter();

			$result_ids = $filter->getResults();
			$objs = array();
			$objs[ROOT_FOLDER_ID] = ilObjectFactory::getInstanceByRefId(ROOT_FOLDER_ID,false);
			foreach ((array) $result_ids as $ref_id => $obj_id) {

				$obj = ilObjectFactory::getInstanceByRefId($ref_id,false);
				if($obj instanceof ilObject)
				{
					$objs[] = $obj;
				}
			}
			include_once './Services/Search/classes/Lucene/class.ilLuceneHighlighterResultParser.php';
			$highlighter = new ilLuceneHighlighterResultParser();
			if($filter->getResultObjIds())
			{
				$highlighter = $searcher->highlight($filter->getResultObjIds());
			}
		}
		else
		{

			include_once './Services/Search/classes/class.ilQueryParser.php';

			$query_parser = new ilQueryParser($key);
			#$query_parser->setMinWordLength(3);
			$query_parser->setCombination($combination == 'and' ? QP_COMBINATION_AND : QP_COMBINATION_OR);
			$query_parser->parse();
			if(!$query_parser->validate())
			{
				return $this->__raiseError($query_parser->getMessage(),
										   'Client');
			}

			#include_once './Services/Search/classes/class.ilObjectSearchFactory.php';
			#$object_search =& ilObjectSearchFactory::_getObjectSearchInstance($query_parser);

			include_once './Services/Search/classes/Like/class.ilLikeObjectSearch.php';
			$object_search =  new ilLikeObjectSearch($query_parser);

			$object_search->setFilter($types);

			$res =& $object_search->performSearch();
			if($user_id)
			{
				$res->setUserId($user_id);
			}
			// begin-patch fm
			$res->setMaxHits(100);
			// begin-patch fm
			$res->filter(ROOT_FOLDER_ID,$combination == 'and' ? true : false);

			$counter = 0;
			$objs = array();
			foreach($res->getUniqueResults() as $entry)
			{
				$obj = ilObjectFactory::getInstanceByRefId($entry['ref_id'],false);
				if($obj instanceof ilObject)
				{
					$objs[] = $obj;
				}
			}
		}

		if(!count($objs))
		{
			return '';
		}

		include_once './webservice/soap/classes/class.ilObjectXMLWriter.php';

		$xml_writer = new ilObjectXMLWriter();

		// begin-patch fm
		if(ilSearchSettings::getInstance()->enabledLucene())
		{
			$xml_writer->enableReferences(false);
			$xml_writer->setMode(ilObjectXmlWriter::MODE_SEARCH_RESULT);
			$xml_writer->setHighlighter($highlighter);
		}

		$xml_writer->enablePermissionCheck(true);

		if($user_id)
		{
			$xml_writer->setUserId($user_id);
			$xml_writer->enableOperations(true);
		}

		$xml_writer->setObjects($objs);
		if($xml_writer->start())
		{
			#$GLOBALS['DIC']['ilLog']->write(__METHOD__.': '.$xml_writer->xmlDumpMem(true));
			return $xml_writer->getXML();
		}

		return $this->__raiseError('Cannot create object xml !','Server');
	}

	function getTreeChilds($sid,$ref_id,$types,$user_id)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}

		$all = false;

		global $DIC;

		$tree = $DIC['tree'];

		if(!$target_obj =& ilObjectFactory::getInstanceByRefId($ref_id,false))
		{
			return $this->__raiseError('No valid reference id given.',
									   'Client');
		}
		if (intval($ref_id) == SYSTEM_FOLDER_ID) {
		    return $this->__raiseError('No valid reference id given.',
									   'Client');
		}

		if(!$types)
		{
			$all = true;
		}

 		$objs = array();

		// begin-patch filemanager
		include_once './Services/WebServices/FileManager/classes/class.ilFMSettings.php';
		if(in_array('parent', (array) $types))
		{
			$objs[] = $target_obj;
		}
		// end-patch filemanager

		foreach($tree->getChilds($ref_id,'title') as $child)
		{
			if($all or in_array($child['type'],$types))
			{
				if($tmp = ilObjectFactory::getInstanceByRefId($child['ref_id'],false))
				{
					$objs[] = $tmp;
				}
			}
		}

		include_once './webservice/soap/classes/class.ilObjectXMLWriter.php';

		$xml_writer = new ilObjectXMLWriter();
		// begin-patch filemanager
		if(ilFMSettings::getInstance()->isEnabled())
		{
			$xml_writer->enableReferences(false);
		}
		// end-patch filemanager
		$xml_writer->enablePermissionCheck(true);
		$xml_writer->setObjects($objs);
		$xml_writer->enableOperations(true);
		if($user_id)
		{
			$xml_writer->setUserId($user_id);
		}

        if ($xml_writer->start())
        {
			#$GLOBALS['DIC']['ilLog']->write(__METHOD__.': '.$xml_writer->getXML());
            return $xml_writer->getXML();
        }

		return $this->__raiseError('Cannot create object xml !','Server');
	}

    function getXMLTree($sid, $ref_id, $types, $user_id)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
		}

		global $DIC;

		$tree = $DIC['tree'];

		$nodedata = $tree->getNodeData($ref_id);
		$nodearray = $tree->getSubTree($nodedata);


		$filter = (array) $types;

		global $DIC;

		$objDefinition = $DIC['objDefinition'];
		foreach($nodearray as $node)
		{
			if(!$objDefinition->isAdministrationObject($node['type']) && !$objDefinition->isSystemObject($node['type']))
			{
				if(!in_array($node['type'], $filter))
				{
					if($tmp = ilObjectFactory::getInstanceByRefId($node['ref_id'], false))
					{
						$nodes[] = $tmp;
					}
				}
			}
		}


		include_once './webservice/soap/classes/class.ilObjectXMLWriter.php';

		$xml_writer = new ilObjectXMLWriter();
		$xml_writer->enablePermissionCheck(true);
		$xml_writer->setObjects($nodes);
		$xml_writer->enableOperations(false);

		if($user_id)
		{
			$xml_writer->setUserId($user_id);
		}

		if($xml_writer->start())
		{
			return $xml_writer->getXML();
		}

		return $this->__raiseError('Cannot create object xml !', 'Server');
	}

	function addObject($sid,$a_target_id,$a_xml)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		if(!strlen($a_xml))
		{
			return $this->__raiseError('No valid xml string given.',
									   'Client');
		}

		global $DIC;

		$rbacsystem = $DIC['rbacsystem'];
		$objDefinition = $DIC['objDefinition'];
		$ilUser = $DIC['ilUser'];
		$lng = $DIC['lng'];
		$ilObjDataCache = $DIC['ilObjDataCache'];

		if(!$target_obj =& ilObjectFactory::getInstanceByRefId($a_target_id,false))
		{
			return $this->__raiseError('No valid target given.',
									   'Client');
		}

		if(ilObject::_isInTrash($a_target_id))
		{
			return $this->__raiseError("Parent with ID $a_target_id has been deleted.", 'Client');
		}

		$allowed_types = array('root','cat','grp','crs','fold');
		if(!in_array($target_obj->getType(),$allowed_types))
		{
			return $this->__raiseError('No valid target type. Target must be reference id of "course, group, category or folder"',
									   'Client');
		}

		$allowed_subtypes = $target_obj->getPossibleSubObjects();

		foreach($allowed_subtypes as $row)
		{
			if($row['name'] != 'rolf')
			{
				$allowed[] = $row['name'];
			}
		}

		include_once './webservice/soap/classes/class.ilObjectXMLParser.php';
		
		$xml_parser = new ilObjectXMLParser($a_xml, true);
		try {
			$xml_parser->startParsing();
		} 
		catch (ilSaxParserException $se){
			return $this->__raiseError($se->getMessage(),'Client');
		}
		catch(ilObjectXMLException $e) {
			return $this->__raiseError($e->getMessage(),'Client');
		}

		foreach($xml_parser->getObjectData() as $object_data)
		{
			$res = $this->validateReferences('create',$object_data,$a_target_id);
			if($this->isFault($res))
			{
				return $res;
			}
			
			// Check possible subtype
			if(!in_array($object_data['type'],$allowed))
			{
				return $this->__raiseError('Objects of type: '.$object_data['type'].' are not allowed to be subobjects of type '.
										   $target_obj->getType().'!',
										   'Client');
			}
			if(!$rbacsystem->checkAccess('create',$a_target_id,$object_data['type']))
			{
				return $this->__raiseError('No permission to create objects of type '.$object_data['type'].'!',
										   'Client');
			}
			// begin-patch fm
			/*
			if($object_data['type'] == 'crs')
			{
				return $this->__raiseError('Cannot create course objects. Use method addCourse() ',
										   'Client');
			}
			*/
			// end-patch fm

			// It's not possible to add objects with non unique import ids
			if(strlen($object_data['import_id']) and ilObject::_lookupObjIdByImportId($object_data['import_id']))
			{
				return $this->__raiseError('An object with import id '.$object_data['import_id'].' already exists!',
										   'Server');
			}

			// call gui object method
			$class_name = $objDefinition->getClassName($object_data['type']);
			$location = $objDefinition->getLocation($object_data['type']);

			$class_constr = "ilObj".$class_name;
			require_once($location."/class.ilObj".$class_name.".php");

			$newObj = new $class_constr();
			
			if(isset($object_data['owner']) && strlen($object_data['owner']))
			{
				if((int)$object_data['owner'])
				{
					if(ilObject::_exists((int)$object_data['owner']) && 
					   $ilObjDataCache->lookupType((int)$object_data['owner']) == 'usr')
					{
						$newObj->setOwner((int)$object_data['owner']);
					}
				}
				else
				{
					$usr_id = ilObjUser::_lookupId(trim($object_data['owner']));
					if((int)$usr_id)
					{
						$newObj->setOwner((int)$usr_id);
					}
				}
			}

			$newObj->setType($object_data['type']);
			if(strlen($object_data['import_id']))
			{
				$newObj->setImportId($object_data['import_id']);
			}

			if($objDefinition->supportsOfflineHandling($newObj->getType()))
			{
				$newObj->setOfflineStatus((bool) $object_data['offline']);
			}
			$newObj->setTitle($object_data['title']);
			$newObj->setDescription($object_data['description']);
			$newObj->create(); // true for upload
			$newObj->createReference();
			$newObj->putInTree($a_target_id);
			$newObj->setPermissions($a_target_id);
			
			switch($object_data['type'])
			{
				case 'grp':
					// Add member
					$newObj->addMember($object_data['owner'] ? $object_data['owner'] : $ilUser->getId(),
									   $newObj->getDefaultAdminRole());
					break;

				// begin-patch fm
				case 'crs':
					$newObj->getMemberObject()->add($ilUser->getId(),IL_CRS_ADMIN);
					break;
				// end-patch fm
				case 'lm':
					$newObj->createLMTree();
					break;
				case 'cat':
					/** @var $newObj ilObjCategory */
					$newObj->addTranslation($object_data["title"],$object_data["description"], $lng->getLangKey(), true);
					break;
			}
			
			$this->addReferences($newObj,$object_data);

		}
		$ref_id = $newObj->getRefId();
		return  $ref_id  ? $ref_id : "0";		
	}

	function addReference($sid,$a_source_id,$a_target_id)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		if(!is_numeric($a_source_id))
		{
			return $this->__raiseError('No source id given.',
									   'Client');
		}
		if(!is_numeric($a_target_id))
		{
			return $this->__raiseError('No target id given.',
									   'Client');
		}

		global $DIC;

		$objDefinition = $DIC['objDefinition'];
		$rbacsystem = $DIC['rbacsystem'];
		$tree = $DIC['tree'];

		if(!$source_obj =& ilObjectFactory::getInstanceByRefId($a_source_id,false))
		{
			return $this->__raiseError('No valid source id given.',
									   'Client');
		}
		if(!$target_obj =& ilObjectFactory::getInstanceByRefId($a_target_id,false))
		{
			return $this->__raiseError('No valid target id given.',
									   'Client');
		}

		if(!$objDefinition->allowLink($source_obj->getType()) and 
			$source_obj->getType() != 'cat' and
			$source_obj->getType() != 'crs')
		{
			return $this->__raiseError('Linking of object type: '.$source_obj->getType().' is not allowed',
									   'Client');
		}

		$allowed_subtypes = $target_obj->getPossibleSubObjects();
		foreach($allowed_subtypes as $row)
		{
			if($row['name'] != 'rolf')
			{
				$allowed[] = $row['name'];
			}
		}
		if(!in_array($source_obj->getType(),$allowed))
		{
			return $this->__raiseError('Objects of type: '.$source_obj->getType().' are not allowed to be subobjects of type '.
									   $target_obj->getType().'!',
									   'Client');
		}

		// Permission checks
		if(!$rbacsystem->checkAccess('create',$target_obj->getRefId(),$source_obj->getType()))
		{
			return $this->__raiseError('No permission to create objects of type '.$source_obj->getType().'!',
									   'Client');
		}
		if(!$rbacsystem->checkAccess('delete',$source_obj->getRefId()))
		{
			return $this->__raiseError('No permission to link object with id: '.$source_obj->getRefId().'!',
									   'Client');
		}
		
		
		if($source_obj->getType() != 'cat' and $source_obj->getType() != 'crs')
		{
			// check if object already linked to target
			$possibleChilds = $tree->getChildsByType($target_obj->getRefId(), $source_obj->getType());
			foreach ($possibleChilds as $child) 
			{
				if ($child["obj_id"] == $source_obj->getId())
					return $this->__raiseError("Object already linked to target.","Client");
			}
			
			// Finally link it to target position
	
			$new_ref_id = $source_obj->createReference();
			$source_obj->putInTree($target_obj->getRefId());
			$source_obj->setPermissions($target_obj->getRefId());
			
			return $new_ref_id ? $new_ref_id : "0";
		}
		else
		{
			switch($source_obj->getType())
			{
				case 'cat':
					include_once('./Modules/CategoryReference/classes/class.ilObjCategoryReference.php');
					$new_ref = new ilObjCategoryReference();
					break;
					
				case 'crs':
					include_once('./Modules/CourseReference/classes/class.ilObjCourseReference.php');
					$new_ref = new ilObjCourseReference();
					break;
				case 'grp':
					include_once('./Modules/GroupReference/classes/class.ilObjGroupReference.php');
					$new_ref = new ilObjGroupReference();
					break;
			}
			$new_ref->create();
			$new_ref_id = $new_ref->createReference();
			
			$new_ref->putInTree($target_obj->getRefId());
			$new_ref->setPermissions($target_obj->getRefId());
			
			$new_ref->setTargetId($source_obj->getId());
			$new_ref->update();
			
			return $new_ref_id ? $new_ref_id : 0;
		}
	}

	function deleteObject($sid,$reference_id)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		if(!is_numeric($reference_id))
		{
			return $this->__raiseError('No reference id given.',
									   'Client');
		}
		global $DIC;

		$tree = $DIC['tree'];
		$rbacsystem = $DIC['rbacsystem'];
		$rbacadmin = $DIC['rbacadmin'];

		if(!$del_obj =& ilObjectFactory::getInstanceByRefId($reference_id,false))
		{
			return $this->__raiseError('No valid reference id given.',
									   'Client');
		}
		if(!$rbacsystem->checkAccess('delete',$del_obj->getRefId()))
		{
			return $this->__raiseError('No permission to delete object with id: '.$del_obj->getRefId().'!',
									   'Client');
		}

		// Delete tree
		if($tree->isDeleted($reference_id))
		{
			return $this->__raiseError('Node already deleted','Server');
		}

		if($del_obj->getType() == 'rolf')
		{
			return $this->__raiseError('Delete is not available for role folders.','Client');
		}

		$subnodes = $tree->getSubtree($tree->getNodeData($reference_id));
		foreach($subnodes as $subnode)
		{
			$rbacadmin->revokePermission($subnode["child"]);
			// remove item from all user desktops
			$affected_users = ilUtil::removeItemFromDesktops($subnode["child"]);
		}
		if(!$tree->saveSubTree($reference_id,true))
		{
			return $this->__raiseError('Node already deleted','Client');
		}

		return true;
	}

	function removeFromSystemByImportId($sid,$import_id)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		if(!strlen($import_id))
		{
			return $this->__raiseError('No import id given. Aborting!',
									   'Client');
		}
		global $DIC;

		$rbacsystem = $DIC['rbacsystem'];
		$tree = $DIC['tree'];
		$ilLog = $DIC['ilLog'];

		// get obj_id
		if(!$obj_id = ilObject::_lookupObjIdByImportId($import_id))
		{
			return $this->__raiseError('No object found with import id: '.$import_id,
									   'Client');
		}

		// Check access
		$permission_ok = false;
		foreach($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id)
		{
			if($rbacsystem->checkAccess('delete',$ref_id))
			{
				$permission_ok = true;
				break;
			}
		}
		if(!$permission_ok)
		{
			return $this->__raiseError('No permission to delete the object with import id: '.$import_id,
									   'Server');
		}

		// Delete all references (delete permssions and entries in object_reference)
		foreach($ref_ids as $ref_id)
		{
			// All subnodes
			$node_data = $tree->getNodeData($ref_id);
			$subtree_nodes = $tree->getSubtree($node_data);

			foreach($subtree_nodes as $node)
			{
				$ilLog->write('Soap: removeFromSystemByImportId(). Deleting object with title id: '.$node['title']);
				$tmp_obj = ilObjectFactory::getInstanceByRefId($node['ref_id']);
				if(!is_object($tmp_obj))
				{
					return $this->__raiseError('Cannot create instance of reference id: '.$node['ref_id'],
											   'Server');
				}
				$tmp_obj->delete();
			}
			// Finally delete tree
			$tree->deleteTree($node_data);

		}

		return true;
	}


	function updateObjects($sid,$a_xml)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		if(!strlen($a_xml))
		{
			return $this->__raiseError('No valid xml string given.',
									   'Client');
		}

		global $DIC;

		$rbacreview = $DIC['rbacreview'];
		$rbacsystem = $DIC['rbacsystem'];
		$lng = $DIC['lng'];
		$ilAccess = $DIC['ilAccess'];
		$objDefinition = $DIC['objDefinition'];

		include_once './webservice/soap/classes/class.ilObjectXMLParser.php';
		$xml_parser = new ilObjectXMLParser($a_xml, true);
		try 
		{
			$xml_parser->startParsing();
		} 
		catch (ilSaxParserException $se){
			return $this->__raiseError($se->getMessage(),'Client');
		}
		catch(ilObjectXMLException $e) {
			return $this->__raiseError($e->getMessage(),'Client');
		}


		// Validate incoming data
		$object_datas = $xml_parser->getObjectData();
		foreach($object_datas as & $object_data)
		{
			$res = $this->validateReferences('update',$object_data);
			if($this->isFault($res))
			{
				return $res;
			}


			if(!$object_data["obj_id"])
			{
				return $this->__raiseError('No obj_id in xml found.', 'Client');
			}
			elseif ((int) $object_data["obj_id"] == -1 && count($object_data["references"])>0)
			{
				// object id might be unknown, resolve references instead to determine object id
				// all references should point to the same object, so using the first one is ok.
				foreach ($object_data["references"] as $refid) 
				{
					if(ilObject::_isInTrash($refid))
					{
						continue;
					}
					break;
				}	
				
				$obj_id_from_refid = ilObject::_lookupObjectId($object_data["references"][0], false);
				if (!$obj_id_from_refid)
				{
					return $this->__raiseError('No obj_id found for reference id '.$object_data["references"][0], 'CLIENT_OBJECT_NOT_FOUND');
				} else
				{
					$tmp_obj = ilObjectFactory::getInstanceByObjId($object_data['obj_id'], false);
					$object_data["obj_id"] = $obj_id_from_refid;
				}
			}
			
			$tmp_obj = ilObjectFactory::getInstanceByObjId($object_data['obj_id'], false);
			if ($tmp_obj == null)
			{
				return $this->__raiseError('No object for id '.$object_data['obj_id'].'!', 'CLIENT_OBJECT_NOT_FOUND');
			} 
			else 
			{
				$object_data["instance"] = $tmp_obj;
			}
			
			if($object_data['type'] == 'role')
			{
				$rolf_ids = $rbacreview->getFoldersAssignedToRole($object_data['obj_id'],true);
				$rolf_id = $rolf_ids[0];

				if(!$rbacsystem->checkAccess('write',$rolf_id))
				{
					return $this->__raiseError('No write permission for object with id '.$object_data['obj_id'].'!', 'Client');
				}
			}
			else
			{
				$permission_ok = false;
				foreach(ilObject::_getAllReferences($object_data['obj_id']) as $ref_id)
				{
					if($ilAccess->checkAccess('write','',$ref_id))
					{
						$permission_ok = true;
						break;
					}
				}
				if(!$permission_ok)
				{
					return $this->__raiseError('No write permission for object with id '.$object_data['obj_id'].'!', 'Client');
				}
			}
		}
		// perform update
		if (count ($object_datas) > 0)
		{
			foreach($object_datas as $object_data)
			{
				$this->updateReferences($object_data);
				
				/**
				 * @var ilObject
				 */
				$tmp_obj = $object_data["instance"];
				$tmp_obj->setTitle($object_data['title']);
				$tmp_obj->setDescription($object_data['description']);

				if($objDefinition->supportsOfflineHandling($tmp_obj->getType()))
				{
					$tmp_obj->setOfflineStatus($object_data['offline']);
				}

				switch ($object_data['type']) 
				{
					case 'cat':
						$tmp_obj->updateTranslation($object_data["title"],$object_data["description"], $lng->getLangKey(), $lng->getLangKey());
						break;
				}
				$tmp_obj->update();
				if(strlen($object_data['owner']) && is_numeric($object_data['owner']))
				{
				    $tmp_obj->setOwner($object_data['owner']);
					$tmp_obj->updateOwner();
				}
				
			}
			return true;
		}
		return false;
	}
	
	function moveObject ($sid, $ref_id, $target_id) 
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}

		include_once './webservice/soap/classes/class.ilSoapUtils.php';
		global $DIC;

		$rbacreview = $DIC['rbacreview'];
		$rbacadmin = $DIC['rbacadmin'];
		$objDefinition = $DIC['objDefinition'];
		$rbacsystem = $DIC['rbacsystem'];
		$lng = $DIC['lng'];
		$ilUser = $DIC['ilUser'];
		$tree = $DIC['tree'];
		
		// does source object exist
		if(!$source_object_type = ilObjectFactory::getTypeByRefId($ref_id, false))
		{
			return $this->__raiseError('No valid source given.', 'Client');
		}

		// does target object exist
		if(!$target_object_type = ilObjectFactory::getTypeByRefId($target_id, false))
		{
			return $this->__raiseError('No valid target given.', 'Client');
		}
		
		// check for trash
		if(ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError('Object is trashed.', 'Client');
		}
		
		if(ilObject::_isInTrash($target_id))
		{
			return $this->__raiseError('Object is trashed.', 'Client');
		}
		
		$canAddType = $this->canAddType($source_object_type, $target_object_type, $target_id);
		if ($this->isFault($canAddType)) 
		{
			return $canAddType; 			
		}
		
		// check if object already linked to target
		$possibleChilds = $tree->getChildsByType($target_id, $ref_id);
		foreach ($possibleChilds as $child) 
		{
			if ($child["obj_id"] == $ref_id)
				return $this->__raiseError("Object already exists in target.","Client");
		}
		
		// CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF		
		if ($tree->isGrandChild($ref_id,$target_id))
		{
			return $this->__raiseError("Cannot move object into itself.","Client");		
		}
		
		$old_parent = $tree->getParentId($ref_id);
		$tree->moveTree($ref_id,$target_id);
		$rbacadmin->adjustMovedObjectPermissions($ref_id,$old_parent);
				
		include_once('./Services/Conditions/classes/class.ilConditionHandler.php');
		ilConditionHandler::_adjustMovedObjectConditions($ref_id);
		return true;
	}

	/**
	 * copy object in repository
	 * $sid	session id
	 * $settings_xml contains copy wizard settings following ilias_copy_wizard_settings.dtd
	 */
	function copyObject($sid, $copy_settings_xml) 
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}


		include_once './webservice/soap/classes/class.ilSoapUtils.php';
		global $DIC;

		$rbacreview = $DIC['rbacreview'];
		$objDefinition = $DIC['objDefinition'];
		$rbacsystem = $DIC['rbacsystem'];
		$lng = $DIC['lng'];
		$ilUser = $DIC['ilUser'];

		include_once './webservice/soap/classes/class.ilCopyWizardSettingsXMLParser.php';
		$xml_parser = new ilCopyWizardSettingsXMLParser($copy_settings_xml);
		try {
			$xml_parser->startParsing();
		} catch (ilSaxParserException $se){
			return $this->__raiseError($se->getMessage(), "Client");
		}

		// checking copy permissions, objects and create permissions
		if(!$rbacsystem->checkAccess('copy',$xml_parser->getSourceId()))
		{
			return $this->__raiseError("Missing copy permissions for object with reference id ".$xml_parser->getSourceId(), 'Client');
		}

		// checking copy permissions, objects and create permissions
		$source_id = $xml_parser->getSourceId();
		$target_id = $xml_parser->getTargetId();


		// does source object exist
		if(!$source_object_type = ilObjectFactory::getTypeByRefId($source_id, false))
		{
			return $this->__raiseError('No valid source given.', 'Client');
		}

		// does target object exist
		if(!$target_object_type = ilObjectFactory::getTypeByRefId($xml_parser->getTargetId(), false))
		{
			return $this->__raiseError('No valid target given.', 'Client');
		}


		$canAddType = $this->canAddType($source_object_type, $target_object_type, $target_id);
		if ($this->isFault($canAddType)) 
		{
			return $canAddType; 			
		}

		// if is container object than clone with sub items
		$options = $xml_parser->getOptions();
//		print_r($options);
		$source_object = ilObjectFactory::getInstanceByRefId($source_id);
		if ($source_object instanceof ilContainer) {
			// get client id from sid
			$clientid = substr($sid, strpos($sid, "::") + 2);
			$sessionid = str_replace("::".$clientid, "", $sid);
			// call container clone
			$ret = $source_object->cloneAllObject($sessionid, $clientid,
				$source_object_type,
				$target_id,
				$source_id,
				$options, true);

			return $ret['ref_id'];
			
		} else {
			// create copy wizard settings
			$copy_id = ilCopyWizardOptions::_allocateCopyId();
			$wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
			$wizard_options->saveOwner($ilUser->getId());
			$wizard_options->saveRoot($source_id);

			foreach($options as $source_id => $option)
			{
				$wizard_options->addEntry($source_id,$option);
			}
			$wizard_options->read();

			// call object clone
			$newObject = $source_object->cloneObject($xml_parser->getTargetId(), $copy_id);
			return is_object($newObject) ? $newObject->getRefId() : -1;
		}
	}

	function getPathForRefId($sid, $ref_id) 
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}

		global $DIC;

		$ilAccess = $DIC['ilAccess'];
		$objDefinition = $DIC['objDefinition'];
		$rbacsystem = $DIC['rbacsystem'];
		$lng = $DIC['lng'];
		$ilUser = $DIC['ilUser'];
		
		if(!$rbacsystem->checkAccess('read', $ref_id))
		{
			return $this->__raiseError("Missing read permissions for object with reference id ".$ref_id, 'Client');
		}
		
		if (ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError("Object is in Trash", 'Client');			
		}
		global $DIC;

		$tree = $DIC['tree'];
		$lng = $DIC['lng'];
		$items = $tree->getPathFull($ref_id);
				
		include_once 'webservice/soap/classes/class.ilXMLResultSet.php';
		include_once 'webservice/soap/classes/class.ilXMLResultSetWriter.php';
		include_once 'Modules/Course/classes/class.ilCourseXMLWriter.php';

		$xmlResultSet = new ilXMLResultSet();
		$xmlResultSet->addColumn("ref_id");
		$xmlResultSet->addColumn("type");
		$xmlResultSet->addColumn("title");
		
		$writer = new ilXMLResultSetWriter($xmlResultSet);
		foreach ($items as $item) {
			if ($item["ref_id"] == $ref_id)
				continue;
			if ($item["title"] == "ILIAS" && $item["type"] == "root")
			{
				$item["title"] = $lng->txt("repository");
			}						
			
			$row = new ilXMLResultSetRow();
			$xmlResultSet->addRow($row);
			$row->setValue("ref_id", $item["ref_id"]);	
			$row->setValue("type", $item["type"]);
			$row->setValue("title", $item["title"]);
		}		
		$writer->start();
		return $writer->getXML();
	}
	
	
	private function canAddType ($type, $target_type, $target_id) 
	{
		// checking for target subtypes. Can we add source to target
		global $DIC;

		$objDefinition = $DIC['objDefinition'];
		$rbacsystem = $DIC['rbacsystem'];
		
		$allowed_types = array('root','cat','grp','crs','fold');
		if(!in_array($target_type, $allowed_types))
		{
			return $this->__raiseError('No valid target type. Target must be reference id of "course, group, category or folder"', 'Client');
		}

		$allowed_subtypes = $objDefinition->getSubObjects($target_type);
		$allowed = array();
		
		foreach($allowed_subtypes as $row)
		{
			if($row['name'] != 'rolf')
			{
				$allowed[] = $row['name'];
			}
		}

		if(!in_array($type, $allowed))
		{
			return $this->__raiseError('Objects of type: '.$type.' are not allowed to be subobjects of type '.$target_type.'!','Client');
		}
		if(!$rbacsystem->checkAccess('create',$target_id, $type))
		{
			return $this->__raiseError('No permission to create objects of type '.$type.'!', 'Client');
		}
		
		return true;		
	}
	
	private function validateReferences($a_action,$a_object_data,$a_target_id = 0)
	{
		global $DIC;

		$ilAccess = $DIC['ilAccess'];
		
		if(!isset($a_object_data['references']) or !count($a_object_data['references']))
		{
			return true;
		}
		if($a_action == 'create') 
		{
			if(count($a_object_data['references']) > 1)
			{
				if(in_array($a_object_data['type'],array('cat','crs','grp','fold')))
				{
					return $this->__raiseError("Cannot create references for type ".$a_object_data['type'],
						'Client');
				}
			}
			if(count($a_object_data['references']) == 1)
			{
				if($a_target_id != $a_object_data['references'][0]['parent_id'])
				{
					return $this->__raiseError("Cannot create references for type ".$a_object_data['type'],
						'Client');
				}
			}
			
			foreach($a_object_data['references'] as $ref_data)
			{
				if(!$ref_data['parent_id']) 
				{
					return $this->__raiseError('Element References: No parent Id given!','Client');
				}

				$target_type = ilObject::_lookupType(ilObject::_lookupObjId($ref_data['parent_id']));
				$can_add_type = $this->canAddType($a_object_data['type'],$target_type,$ref_data['parent_id']);
				if($this->isFault($can_add_type))
				{
					return $can_add_type;
				}
			}
			return true;
		}
		
		if($a_action == 'update')
		{
			foreach($a_object_data['references'] as $ref_data)
			{
				if(!$ref_data['ref_id'])
				{
					return $this->__raiseError('Element References: No reference id given!','Client');
				}
				// check permissions
				if(!$ilAccess->checkAccess('write','',$ref_data['ref_id']))
				{
					return $this->__raiseError('No write permission for object with reference id '.$ref_data['ref_id'].'!', 'Client');
				}
				// TODO: check if all references belong to the same object
			}
			return true;
		}
	}
	
	private function updateReferences($a_object_data)
	{
		global $DIC;

		$tree = $DIC['tree'];
		$ilLog = $DIC['ilLog'];
		
		if(!isset($a_object_data['references']) or !count($a_object_data['references']))
		{
			return true;
		}
		
		foreach($a_object_data['references'] as $ref_data)
		{
			if(isset($ref_data['time_target']) /* and ($crs_ref_id = $tree->checkForParentType($ref_data['ref_id'],'crs')) */)
			{				
				include_once('./webservice/soap/classes/class.ilObjectXMLWriter.php');				
				include_once('./Services/Object/classes/class.ilObjectActivation.php');
				$old = ilObjectActivation::getItem($ref_data['ref_id']);
				
				$items = new ilObjectActivation();
				$items->toggleChangeable(isset($ref_data['time_target']['changeable']) ? $ref_data['time_target']['changeable'] : $old['changeable']);
				$items->setTimingStart(isset($ref_data['time_target']['starting_time']) ? $ref_data['time_target']['starting_time'] : $old['timing_start']);
				$items->setTimingEnd(isset($ref_data['time_target']['ending_time']) ? $ref_data['time_target']['ending_time'] : $old['timing_end']);
				$items->toggleVisible(isset($ref_data['time_target']['timing_visibility']) ? $ref_data['time_target']['timing_visibility'] : $old['visible']);
				$items->setSuggestionStart(isset($ref_data['time_target']['suggestion_start']) ? $ref_data['time_target']['suggestion_start'] : $old['suggestion_start']);
				$items->setSuggestionEnd(isset($ref_data['time_target']['suggestion_end']) ? $ref_data['time_target']['suggestion_end'] : $old['suggestion_end']);

				switch($ref_data['time_target']['timing_type']) 
				{
					case ilObjectXMLWriter::TIMING_DEACTIVATED:
						$ilLog->write(__METHOD__.ilObjectActivation::TIMINGS_DEACTIVATED.' '.$ref_data['time_target']['timing_type']);
						$items->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
						break;
						
					case ilObjectXMLWriter::TIMING_TEMPORARILY_AVAILABLE:
						$ilLog->write(__METHOD__.ilObjectActivation::TIMINGS_ACTIVATION.' '.$ref_data['time_target']['timing_type']);
						$items->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);
						break;
					
					case ilObjectXMLWriter::TIMING_PRESETTING:
						$ilLog->write(__METHOD__.ilObjectActivation::TIMINGS_PRESETTING.' '.$ref_data['time_target']['timing_type']);
						$items->setTimingType(ilObjectActivation::TIMINGS_PRESETTING);
						break;
				}
				$items->update($ref_data['ref_id']);
			}
		}
		return true;
	}
	
	
	private function addReferences($source,$a_object_data)
	{
		global $DIC;

		$tree = $DIC['tree'];
		$ilLog = $DIC['ilLog'];
		
		if(!isset($a_object_data['references']) or !count($a_object_data['references']))
		{
			return true;
		}
		
		$original_id = $source->getRefId();
		
		foreach($a_object_data['references'] as $ref_data)
		{
			$new_ref_id = $ref_id = $original_id;
			if($tree->getParentId($original_id) != $ref_data['parent_id'])
			{
				// New reference requested => create it
				$new_ref_id = $source->createReference();
				$source->putInTree($ref_data['parent_id']);
				$source->setPermissions($ref_data['parent_id']);
			}
			if(isset($ref_data['time_target']) /* and ($crs_ref_id = $tree->checkForParentType($new_ref_id,'crs')) */)
			{
				include_once('./webservice/soap/classes/class.ilObjectXMLWriter.php');
				include_once('./Services/Object/classes/class.ilObjectActivation.php');
				
				if(!isset($ref_data['time_target']['starting_time']))
				{
					$ref_data['time_target']['starting_time'] = time();
				}
				if(!isset($ref_data['time_target']['ending_time']))
				{
					$ref_data['time_target']['ending_time'] = time();
				}
				
				$items = new ilObjectActivation();
				$items->toggleChangeable($ref_data['time_target']['changeable']);
				$items->setTimingStart($ref_data['time_target']['starting_time']);
				$items->setTimingEnd($ref_data['time_target']['ending_time']);
				$items->toggleVisible($ref_data['time_target']['timing_visibility']);
				$items->setSuggestionStart($ref_data['time_target']['suggestion_start']);
				$items->setSuggestionEnd($ref_data['time_target']['suggestion_end']);

				switch($ref_data['time_target']['timing_type']) 
				{
					case ilObjectXMLWriter::TIMING_DEACTIVATED:
						$ilLog->write(__METHOD__.ilObjectActivation::TIMINGS_DEACTIVATED.' '.$ref_data['time_target']['timing_type']);
						$items->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
						break;
						
					case ilObjectXMLWriter::TIMING_TEMPORARILY_AVAILABLE:
						$ilLog->write(__METHOD__.ilObjectActivation::TIMINGS_ACTIVATION.' '.$ref_data['time_target']['timing_type']);
						$items->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);
						break;
					
					case ilObjectXMLWriter::TIMING_PRESETTING:
						$ilLog->write(__METHOD__.ilObjectActivation::TIMINGS_PRESETTING.' '.$ref_data['time_target']['timing_type']);
						$items->setTimingType(ilObjectActivation::TIMINGS_PRESETTING);
						break;
				}
				$items->update($new_ref_id);
			}
		}
		
	}
	
	

}
?>