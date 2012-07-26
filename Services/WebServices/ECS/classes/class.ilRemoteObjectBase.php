<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObject2.php";

/** 
 * Remote object app base class
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ingroup ServicesWebServicesECS
*/

abstract class ilRemoteObjectBase extends ilObject2
{
	protected $local_information;
	protected $remote_link;
	protected $organization;
	protected $mid;	
	protected $auth_hash = '';

	/**
	 * Constructor
	 * 
	 * @param int $a_id
	 * @param bool $a_call_by_reference 
	 * @return ilObject
	 */
	public function __construct($a_id = 0,$a_call_by_reference = true)
	{
		global $ilDB;
		
		parent::__construct($a_id,$a_call_by_reference);					
		$this->db = $ilDB;
	}
	
	/**
	 * Get db table name
	 * 
	 * @return string 
	 */
	abstract protected function getTableName();
	
	/**
	 * lookup organization
	 *
	 * @param int $a_obj_id
	 * @param string $a_table
	 * @return string
	 */
	public static function _lookupOrganization($a_obj_id, $a_table)
	{
		global $ilDB;
		
		$query = "SELECT organization FROM ".$a_table.
			" WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->organization;
		}
		return '';
	}	

	/**
	 * set organization
	 *
	 * @param string $a_organization
	 */
	public function setOrganization($a_organization)
	{
	 	$this->organization = $a_organization;
	}
	
	/**
	 * get organization
	 *
	 * @return string
	 */
	public function getOrganization()
	{
	 	return $this->organization;
	}
	
	/**
	 * get local information
	 *
	 * @return string
	 */
	public function getLocalInformation()
	{
	 	return $this->local_information;
	}
	
	/**
	 * set local information
	 *
	 * @param string $a_info
	 */
	public function setLocalInformation($a_info)
	{
	 	$this->local_information = $a_info;
	}
	
	/**
	 * get mid
	 *
	 * @return int
	 */
	public function getMID()
	{
	 	return $this->mid;
	}
	
	/**
	 * set mid
	 *
	 * @param int $a_mid mid
	 */
	public function setMID($a_mid)
	{
	 	$this->mid = $a_mid;
	}
	
	/**
	 * lookup owner mid
	 *
	 * @param int $a_obj_id obj_id
	   @param string $a_table
	 * @return int
	 */
	public static function _lookupMID($a_obj_id, $a_table)
	{
		global $ilDB;
		
		$query = "SELECT mid FROM ".$a_table.
			" WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->mid;
		}
		return 0;
	}
	
	/**
	 * lookup obj ids by mid
	 *
	 * @param int $a_mid mid
	 * @param string $a_table
	 * @return array obj ids
	 */
	public static function _lookupObjIdsByMID($a_mid, $a_table)
	{
		global $ilDB;
					
		$query = "SELECT obj_id FROM ".$a_table.
			" WHERE mid = ".$ilDB->quote($a_mid ,'integer')." ";			
		$res = $ilDB->query($query);
		$obj_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$obj_ids[] = $row->obj_id;
		}
		return $obj_ids;
	}
	
	/**
	 * set remote link
	 *
	 * @param string $a_link link to original object
	 */
	public function setRemoteLink($a_link)
	{
	 	$this->remote_link = $a_link;
	}
	
	/**
	 * get remote link
	 *
	 * @return string
	 */
	public function getRemoteLink()
	{
	 	return $this->remote_link;
	}
	
	/**
	 * get full remote link 
	 * Including ecs generated hash and auth mode
	 *
	 * @return string
	 */
	public function getFullRemoteLink()
	{
	 	global $ilUser;
	 	
	 	include_once('./Services/WebServices/ECS/classes/class.ilECSUser.php');
	 	$user = new ilECSUser($ilUser);
	 	$ecs_user_data = $user->toGET();
	 	return $this->getRemoteLink().'&ecs_hash='.$this->auth_hash.$ecs_user_data;
	}
	
	/**
	 * create authentication resource on ecs server
	 *
	 * @return bool
	 */
	public function createAuthResource()
	{
	 	global $ilLog;
	 	
	 	include_once './Services/WebServices/ECS/classes/class.ilECSAuth.php';
	 	include_once './Services/WebServices/ECS/classes/class.ilECSConnector.php';
		include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
		include_once './Services/WebServices/ECS/classes/class.ilECSSetting.php';

		try
		{	 	
			$server_id = ilECSImport::lookupServerId($this->getId());
			
			$connector = new ilECSConnector(ilECSSetting::getInstanceByServerId($server_id));
			$auth = new ilECSAuth();
			$auth->setUrl($this->getRemoteLink());
			$this->auth_hash = $connector->addAuth(@json_encode($auth),$this->getMID());
			return true;
		}
		catch(ilECSConnectorException $exc)
		{
			$ilLog->write(__METHOD__.': Caught error from ECS Auth resource: '.$exc->getMessage());	
			return false;
		}
	}
	
	/**
	 * Create remote object
	 */
	public function doCreate()
	{
		global $ilDB;
		
		$fields = array(
			"obj_id" => array("integer", $this->getId()),
			"local_information" => array("text", ""),
			"remote_link" => array("text", ""),
			"mid" => array("integer", 0),
			"organization" => array("text", "")		
		);
		
		$this->doCreateCustomFields($fields);
	
		$ilDB->insert($this->getTableName(), $fields);
	}
	
	/**
	 * Add custom fields to db insert
	 * @param array $a_fields 
	 */
	protected function doCreateCustomFields(array &$a_fields)
	{
		
	}

	/**
	 * Update remote object 
	 */
	public function doUpdate()
	{
		global $ilDB;
		
		$fields = array(
			"local_information" => array("text", $this->getLocalInformation()),
			"remote_link" => array("text", $this->getRemoteLink()),
			"mid" => array("integer", $this->getMID()),
			"organization" => array("text", $this->getOrganization())		
		);
		
		$this->doUpdateCustomFields($fields);
		
		$where = array("obj_id" => array("integer", $this->getId()));
		
		$ilDB->update($this->getTableName(), $fields, $where);		
	}
	
	/**
	 * Add custom fields to db update
	 * @param array $a_fields 
	 */
	protected function doUpdateCustomFields(array &$a_fields)
	{
		
	}

	/**
	 * Delete remote object
	 */
	public function doDelete()
	{
		global $ilDB;
		
		//put here your module specific stuff
		include_once('./Services/WebServices/ECS/classes/class.ilECSImport.php');
		ilECSImport::_deleteByObjId($this->getId());
		
		$query = "DELETE FROM ".$this->getTableName().
			" WHERE obj_id = ".$this->db->quote($this->getId() ,'integer')." ";
		$ilDB->manipulate($query);
	}
	
	/**
	 * read settings
	 */
	public function doRead()
	{		
		$query = "SELECT * FROM ".$this->getTableName().
			" WHERE obj_id = ".$this->db->quote($this->getId() ,'integer')." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setLocalInformation($row->local_information);
			$this->setRemoteLink($row->remote_link);
			$this->setMID($row->mid);
			$this->setOrganization($row->organization);
			
			$this->doReadCustomFields($row);
		}
	}
	
	/**
	 * Read custom fields from db row
	 * @param array $a_row 
	 */
	protected function doReadCustomFields(array $a_row)
	{
		
	}
	
	/**
	 * create remote object from ECSContent object
	 *
	 * @param int $a_server_id
	 * @param ilECSEContent $ecs_content object with object settings
	 * @param int $a_mid
	 * @return ilObject
	 */
	public static function _createFromECSEContent($a_server_id,ilECSEContent $ecs_content, $a_mid)
	{		
		include_once './Services/WebServices/ECS/classes/class.ilECSSetting.php' ;
		include_once './Services/WebServices/ECS/classes/class.ilECSCategoryMapping.php';
		$ecs_settings = ilECSSetting::getInstanceByServerId($a_server_id);

		// Cannot instantiate abstract class
		#$remote_obj = new self();
		include_once './Modules/RemoteCourse/classes/class.ilObjRemoteCourse.php';
		$remote_obj = new ilObjRemoteCourse();
		$remote_obj->setType('rcrs');
		// Static
		#$remote_obj->setType($this->type);
		$remote_obj->setOwner(0);
		$new_obj_id = $remote_obj->create();
		
		// won't work for personal workspace
		$remote_obj->createReference();
		$remote_obj->putInTree(ilECSCategoryMapping::getMatchingCategory($a_server_id,$ecs_content));
		$remote_obj->setPermissions($ecs_settings->getImportId());
		
		$remote_obj->setECSImported($a_server_id,$ecs_content->getEContentId(),$a_mid,$new_obj_id);
		$remote_obj->updateFromECSContent($a_server_id,$ecs_content);
		
		return $remote_obj;
	}
	
	/**
	 * update remote object settings from ecs content
	 *
	 * @param int $a_server_id
	 * @param ilECSEContent object with object settings
	 */
	public function updateFromECSContent($a_server_id,ilECSEContent $ecs_content)
	{				
		$this->setTitle($ecs_content->getTitle());
		$this->setDescription($ecs_content->getAbstract());
		$this->setOrganization($ecs_content->getOrganization());
		$this->setRemoteLink($ecs_content->getURL());
		$this->setMID($ecs_content->getOwner());		
		
		include_once('./Services/WebServices/ECS/classes/class.ilECSDataMappingSettings.php');
		include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDValue.php');
		include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');				
		$mappings = ilECSDataMappingSettings::getInstanceByServerId($a_server_id);
		
		$this->updateCustomFromECSContent($ecs_content, $mappings);

		// we are updating late to custom values can be set
		$this->update();
				
		include_once './Services/WebServices/ECS/classes/class.ilECSCategoryMapping.php';
		ilECSCategoryMapping::handleUpdate($a_server_id,$ecs_content,$this->getId());
										
		return true;
	}
	
	/**
	 * update remote object settings from ecs content
	 *
	 * @param ilECSEContent $a_ecs_content object with object settings
	 * @param ilECSDataMappingSettings $a_mappings 
	 */
	protected function updateCustomFromECSContent(ilECSEContent $a_ecs_content, ilECSDataMappingSettings $a_mappings)
	{	
				
	}
	
	/**
	 * set status to imported from ecs
	 *
	 * @param int $a_server_id
	 * @param int $a_econtent_id
	 * @param int $a_mid
	 * @param int $a_obj_id
	 */
	public function setECSImported($a_server_id,$a_econtent_id,$a_mid,$a_obj_id)
	{
		include_once('./Services/WebServices/ECS/classes/class.ilECSImport.php');
	 	$import = new ilECSImport($a_server_id,$a_obj_id);
	 	$import->setEContentId($a_econtent_id);
	 	$import->setMID($a_mid);
	 	$import->save();
	}
	
	/**
	 * Is remote object from same installation?
	 * 
	 * @return boolean 
	 */
	public function isLocalObject()
	{
		include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');
		include_once('./Services/WebServices/ECS/classes/class.ilECSImport.php');
		if(ilECSExport::_isRemote(ilECSImport::lookupServerId($this->getId()),
			ilECSImport::_lookupEContentId($this->getId())))
		{
			return false;
		}
		return true;
	}
}
?>