<?php

/*
 * Presentation of ecs enrolment status
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSEnrolmentStatus
{
	const STATUS_ACTIVE = 'active';
	const STATUS_PENDING = 'pending';
	const STATUS_DENIED = 'denied';
	const STATUS_REJECTED = 'rejected';
	const STATUS_UNSUBSCRIBED = 'unsubscribed';
	const STATUS_ACCOUNT_DEACTIVATED = 'account_deactivated';
	
	const ID_EPPN = 'ecs_ePPN';
	const ID_LOGIN_UID = 'ecs_loginUID';
	const ID_LOGIN = 'ecs_login';
	const ID_UID = 'ecs_uid';
	const ID_EMAIL = 'ecs_email';
	const ID_PERSONAL_UNIQUE_CODE = 'ecs_PersonalUniqueCode';
	const ID_CUSTOM = 'ecs_custom';
	

	// json fields
	public $url = '';
	public $id = '';
	public $personID = '';
	public $personIDtype = '';
	public $status = '';
	
	
	public function __construct()
	{
		
	}
	
	public function setUrl($a_url)
	{
		$this->url = $a_url;
	}
	
	public function getUrl()
	{
		return $this->url;
	}
	
	public function setId($a_id)
	{
		$this->id = $a_id;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setPersonId($a_person)
	{
		$this->personID = $a_person;
	}
	
	public function getPersonId()
	{
		return $this->personID;
	}
	
	public function setPersonIdType($a_type)
	{
		$this->personIDtype = $a_type;
	}
	
	public function getPersonIdType()
	{
		return $this->personIDtype;
	}
	
	public function setStatus($a_status)
	{
		$this->status = $a_status;
	}
	
	public function getStatus()
	{
		return $this->status;
	}
	
	public function loadFromJson($json)
	{
		$this->setId($json->id);
		$this->setPersonId($json->personID);
		$this->setPersonIdType($json->personIDtype);
		$this->setUrl($json->url);
		$this->setStatus($json->status);
	}
}
?>
