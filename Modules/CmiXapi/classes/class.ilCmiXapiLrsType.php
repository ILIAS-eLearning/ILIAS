<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiLrsType
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiLrsType
{
    const DB_TABLE_NAME = 'cmix_lrs_types';
    public static function getDbTableName()
    {
        return self::DB_TABLE_NAME;
    }
    
    const AVAILABILITY_NONE = 0;  // Type is not longer available (error message)
    const AVAILABILITY_EXISTING = 1; // Existing objects of the can be used, but no new created
    const AVAILABILITY_CREATE = 2;  // New objects of this type can be created
    
    const LAUNCH_TYPE_PAGE = "page";
    const LAUNCH_TYPE_LINK = "link";
    const LAUNCH_TYPE_EMBED = "embed";
    
    const USER_IDENT_IL_UUID_USER_ID = 'il_uuid_user_id';
    const USER_IDENT_IL_UUID_LOGIN = 'il_uuid_login';
    const USER_IDENT_IL_UUID_EXT_ACCOUNT = 'il_uuid_ext_account';
    const USER_IDENT_IL_UUID_RANDOM = 'il_uuid_random';
    const USER_IDENT_REAL_EMAIL = 'real_email';
    
    const USER_NAME_NONE = 'none';
    const USER_NAME_FIRSTNAME = 'firstname';
    const USER_NAME_LASTNAME = 'lastname';
    const USER_NAME_FULLNAME = 'fullname';
    
    const ENDPOINT_STATEMENTS_SUFFIX = 'statements';
    const ENDPOINT_AGGREGATE_SUFFIX = 'statements/aggregate';
    
    protected $type_id;

    protected $title;
    protected $description;
    protected $availability = self::AVAILABILITY_CREATE;
    protected $lrs_endpoint;
    protected $lrs_key;
    protected $lrs_secret;
    protected $user_ident;
    protected $user_name;
    protected $force_privacy_settings;
    protected $privacy_comment_default;
    protected $external_lrs;
    
    protected $time_to_delete;
    protected $launch_type = self::LAUNCH_TYPE_LINK;
    
    protected $remarks;
    protected $template;
    
    /**
     * @var bool
     */
    protected $bypassProxyEnabled = false;
    
    /**
     * Constructor
     */
    public function __construct($a_type_id = 0)
    {
        if ($a_type_id) {
            $this->type_id = $a_type_id;
            $this->read();
        }
    }
    
    /**
     * @param int id
     */
    public function setTypeId($a_type_id)
    {
        $this->type_id = $a_type_id;
    }
    
    /**
     * @return int id
     */
    public function getTypeId()
    {
        return $this->type_id;
    }
    
    /**
     * @param string title
     */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }
    
    /**
     * @return string title
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * @param string description
     */
    public function setDescription($a_description)
    {
        $this->description = $a_description;
    }
    
    /**
     * @return string description
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * @param integer availability
     */
    public function setAvailability($a_availability)
    {
        $this->availability = $a_availability;
    }
    
    /**
     * @return integer availability
     */
    public function getAvailability()
    {
        return $this->availability;
    }
    
    /**
     * @return bool
     */
    public function isAvailable()
    {
        if ($this->getAvailability() == self::AVAILABILITY_CREATE) {
            return true;
        }
        
        if ($this->getAvailability() == self::AVAILABILITY_EXISTING) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @param string time_to_delete
     */
    public function setTimeToDelete($a_time_to_delete)
    {
        $this->time_to_delete = $a_time_to_delete;
    }
    
    /**
     * @return string time_to_delete
     */
    public function getTimeToDelete()
    {
        return $this->time_to_delete;
    }
    
    public function setLrsEndpoint($a_endpoint)
    {
        $this->lrs_endpoint = $a_endpoint;
    }
    
    public function getLrsEndpoint()
    {
        return $this->lrs_endpoint;
    }
    
    public function setLrsKey($a_lrs_key)
    {
        $this->lrs_key = $a_lrs_key;
    }
    
    public function getLrsKey()
    {
        return $this->lrs_key;
    }
    
    public function setLrsSecret($a_lrs_secret)
    {
        $this->lrs_secret = $a_lrs_secret;
    }
    
    public function getLrsSecret()
    {
        return $this->lrs_secret;
    }
    
    public function setUserIdent($a_option)
    {
        $this->user_ident = $a_option;
    }
    
    public function getUserIdent()
    {
        return $this->user_ident;
    }
    
    public function setUserName($a_option)
    {
        $this->user_name = $a_option;
    }
    
    public function getUserName()
    {
        return $this->user_name;
    }
    
    /**
     * @return bool
     */
    public function getForcePrivacySettings()
    {
        return $this->force_privacy_settings;
    }
    
    /**
     * @param bool $force_privacy_settings
     */
    public function setForcePrivacySettings($force_privacy_settings)
    {
        $this->force_privacy_settings = $force_privacy_settings;
    }
    
    public function setPrivacyCommentDefault($a_option)
    {
        $this->privacy_comment_default = $a_option;
    }
    
    public function getPrivacyCommentDefault()
    {
        return $this->privacy_comment_default;
    }
    
    public function setExternalLrs($a_option)
    {
        $this->external_lrs = $a_option;
    }
    
    public function getExternalLrs()
    {
        return $this->external_lrs;
    }
    
    /**
     * @return string launch_type
     */
    public function getLaunchType()
    {
        return $this->launch_type;
    }
    
    /**
     * @param string remarks
     */
    public function setRemarks($a_remarks)
    {
        $this->remarks = $a_remarks;
    }
    
    /**
     * @return string remarks
     */
    public function getRemarks()
    {
        return $this->remarks;
    }
    
    /**
     * @return string template
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    /**
     * @return bool
     */
    public function isBypassProxyEnabled() : bool
    {
        return $this->bypassProxyEnabled;
    }
    
    /**
     * @param bool $bypassProxyEnabled
     */
    public function setBypassProxyEnabled(bool $bypassProxyEnabled)
    {
        $this->bypassProxyEnabled = $bypassProxyEnabled;
    }
    
    public function read()
    {
        global $ilDB, $ilErr;
        
        $query = "SELECT * FROM {$this->getDbTableName()} WHERE type_id = %s";
        
        $res = $ilDB->queryF($query, ['integer'], [$this->getTypeId()]);
        $row = $ilDB->fetchObject($res);
        if ($row) {
            $this->setTypeId($row->type_id);
            
            $this->setTitle($row->title);
            $this->setDescription($row->description);
            $this->setAvailability($row->availability);
            $this->setLrsEndpoint($row->lrs_endpoint);
            $this->setLrsKey($row->lrs_key);
            $this->setLrsSecret($row->lrs_secret);
            $this->setUserIdent($row->user_ident);
            $this->setUserName($row->user_name);
            $this->setForcePrivacySettings((bool) $row->force_privacy_settings);
            $this->setPrivacyCommentDefault($row->privacy_comment_default);
            $this->setExternalLrs($row->external_lrs);
            
            $this->setTimeToDelete($row->time_to_delete);
            $this->setRemarks($row->remarks);
            
            $this->setBypassProxyEnabled((bool) $row->bypass_proxy);
            
            return true;
        }
        
        return false;
    }
    
    public function save()
    {
        if ($this->getTypeId()) {
            $this->update();
        } else {
            $this->create();
        }
    }
    
    public function create()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $this->setTypeId(
            $DIC->database()->nextId($this->getDbTableName())
        );
        
        $this->update();
    }
    
    public function update()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->database()->replace(
            $this->getDbTableName(),
            array(
                'type_id' => array('integer', $this->getTypeId())
            ),
            array(
                'title' => array('text', $this->getTitle()),
                'description' => array('clob', $this->getDescription()),
                'availability' => array('integer', $this->getAvailability()),
                'remarks' => array('clob', $this->getRemarks()),
                'time_to_delete' => array('integer', $this->getTimeToDelete()),
                'lrs_endpoint' => array('text', $this->getLrsEndpoint()),
                'lrs_key' => array('text', $this->getLrsKey()),
                'lrs_secret' => array('text', $this->getLrsSecret()),
                'user_ident' => array('text', $this->getUserIdent()),
                'user_name' => array('text', $this->getUserName()),
                'force_privacy_settings' => array('integer', (int) $this->getForcePrivacySettings()),
                'privacy_comment_default' => array('text', $this->getPrivacyCommentDefault()),
                'external_lrs' => array('integer', $this->getExternalLrs()),
                'bypass_proxy' => array('integer', (int) $this->isBypassProxyEnabled())
            )
        );
        
        return true;
    }
    
    public function delete()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $query = "DELETE FROM {$this->getDbTableName()} WHERE type_id = %s";
        $DIC->database()->manipulateF($query, ['integer'], [$this->getTypeId()]);
        
        return true;
    }
    
    public function getLrsEndpointStatementsLink()
    {
        return $this->getLrsEndpoint() . '/' . self::ENDPOINT_STATEMENTS_SUFFIX;
    }
    
    public function getLrsEndpointStatementsAggregationLink()
    {
        return dirname(dirname($this->getLrsEndpoint())) . '/api/' . self::ENDPOINT_AGGREGATE_SUFFIX;
    }
    
    public function getBasicAuth()
    {
        return self::buildBasicAuth($this->getLrsKey(), $this->getLrsSecret());
    }
    
    public static function buildBasicAuth($lrsKey, $lrsSecret)
    {
        return 'Basic ' . base64_encode("{$lrsKey}:{$lrsSecret}");
    }

    public static function getLaunchData($objId)
    {
        $launchMethod = "AnyWindow"; // $this->object->getLaunchMethod(),
        $moveOn = "Completed";
        $launchMode = "Normal";
        return json_encode([
            "contextTemplate" => [
                "contextActivities" => [
                    "grouping" => [
                        "objectType" => "Activity",
                        "id" => "http://course-repository.example.edu/identifiers/courses/02baafcf/aus/4c07"
                    ]
                ],
                "extensions" => [
                    "https://w3id.org/xapi/cmi5/context/extensions/sessionid" => "32e96d95-8e9c-4162-b3ac-66df22d171c5"
                ]
            ],
            "launchMode" => $launchMode,
            "launchMethod" => $launchMethod,
            "moveOn" => $moveOn
        ]);
    }
}
