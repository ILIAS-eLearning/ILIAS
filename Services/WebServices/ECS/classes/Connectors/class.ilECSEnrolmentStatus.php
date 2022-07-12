<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Presentation of ecs enrolment status
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSEnrolmentStatus
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING = 'pending';
    public const STATUS_DENIED = 'denied';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';
    public const STATUS_ACCOUNT_DEACTIVATED = 'account_deactivated';
    
    public const ID_EPPN = 'ecs_ePPN';
    public const ID_LOGIN_UID = 'ecs_loginUID';
    public const ID_LOGIN = 'ecs_login';
    public const ID_UID = 'ecs_uid';
    public const ID_EMAIL = 'ecs_email';
    public const ID_PERSONAL_UNIQUE_CODE = 'ecs_PersonalUniqueCode';
    public const ID_CUSTOM = 'ecs_custom';
    

    // json fields
    public string $url = '';
    public string $id = '';
    public string $personID = '';
    public string $personIDtype = '';
    public string $status = '';
    
    
    public function __construct()
    {
    }
    
    public function setUrl(string $a_url) : void
    {
        $this->url = $a_url;
    }
    
    public function getUrl() : string
    {
        return $this->url;
    }
    
    public function setId(string $a_id) : void
    {
        $this->id = $a_id;
    }
    
    public function getId() : string
    {
        return $this->id;
    }
    
    public function setPersonId(string $a_person) : void
    {
        $this->personID = $a_person;
    }
    
    public function getPersonId() : string
    {
        return $this->personID;
    }
    
    public function setPersonIdType(string $a_type) : void
    {
        $this->personIDtype = $a_type;
    }
    
    public function getPersonIdType() : string
    {
        return $this->personIDtype;
    }
    
    public function setStatus(string $a_status) : void
    {
        $this->status = $a_status;
    }
    
    public function getStatus() : string
    {
        return $this->status;
    }

    public function loadFromJson(object $json) : void
    {
        $this->setId($json->id);
        $this->setPersonId($json->personID);
        $this->setPersonIdType($json->personIDtype);
        $this->setUrl($json->url);
        $this->setStatus($json->status);
    }
}
