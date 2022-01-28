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
    public static function getDbTableName() : string
    {
        return self::DB_TABLE_NAME;
    }
    
    const AVAILABILITY_NONE = 0;  // Type is not longer available (error message)
    const AVAILABILITY_EXISTING = 1; // Existing objects of the can be used, but no new created
    const AVAILABILITY_CREATE = 2;  // New objects of this type can be created
    
    const LAUNCH_TYPE_PAGE = "page";
    const LAUNCH_TYPE_LINK = "link";
    const LAUNCH_TYPE_EMBED = "embed";
    
    const PRIVACY_IDENT_IL_UUID_USER_ID = 0;
    const PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT = 1;
    const PRIVACY_IDENT_IL_UUID_LOGIN = 2;
    const PRIVACY_IDENT_REAL_EMAIL = 3;
    const PRIVACY_IDENT_IL_UUID_RANDOM = 4;
    
    const PRIVACY_NAME_NONE = 0;
    const PRIVACY_NAME_FIRSTNAME = 1;
    const PRIVACY_NAME_LASTNAME = 2;
    const PRIVACY_NAME_FULLNAME = 3;
    
    const ENDPOINT_STATEMENTS_SUFFIX = 'statements';
    const ENDPOINT_AGGREGATE_SUFFIX = 'statements/aggregate';
    
    protected int $type_id = 0;

    protected string $title = "";
    protected string $description = "";
    protected int $availability = self::AVAILABILITY_CREATE;
    protected string $lrs_endpoint = "";
    protected string $lrs_key = "";
    protected string $lrs_secret = "";
    protected int $privacy_ident = 3;
    protected int $privacy_name = 0;
    protected bool $force_privacy_settings = false;
    protected string $privacy_comment_default = "";
    protected bool $external_lrs = false;
    
    protected ?int $time_to_delete = null;
    protected string $launch_type = self::LAUNCH_TYPE_LINK;
    
    protected string $remarks = "";
    
    /**
     * @var bool
     */
    protected bool $bypassProxyEnabled = false;

    /** @var bool $only_moveon */
    protected bool $only_moveon = false;

    /** @var bool $achieved */
    protected bool $achieved = true;

    /** @var bool $answered */
    protected bool $answered = true;

    /** @var bool $completed */
    protected bool $completed = true;

    /** @var bool $failed */
    protected bool $failed = true;

    /** @var bool $initialized */
    protected bool $initialized = true;

    /** @var bool $passed */
    protected bool $passed = true;

    /** @var bool $progressed */
    protected bool $progressed = true;

    /** @var bool $satisfied */
    protected bool $satisfied = true;

    /** @var bool $terminated */
    protected bool $terminated = true;

    /** @var bool $hide_data */
    protected bool $hide_data = false;

    /** @var bool $timestamp */
    protected bool $timestamp = false;

    /** @var bool $duration */
    protected bool $duration = true;

    /** @var bool $no_substatements */
    protected bool $no_substatements = false;

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
    public function setTypeId(int $a_type_id) : void
    {
        $this->type_id = $a_type_id;
    }
    
    /**
     * @return int id
     */
    public function getTypeId() : int
    {
        return $this->type_id;
    }
    
    /**
     * @param string title
     */
    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }
    
    /**
     * @return string title
     */
    public function getTitle() : string
    {
        return $this->title;
    }
    
    /**
     * @param string description
     */
    public function setDescription(string $a_description) : void
    {
        $this->description = $a_description;
    }
    
    /**
     * @return string description
     */
    public function getDescription() : string
    {
        return $this->description;
    }
    
    /**
     * @param integer availability
     */
    public function setAvailability(int $a_availability) : void
    {
        $this->availability = $a_availability;
    }
    
    /**
     * @return integer availability
     */
    public function getAvailability() : int
    {
        return $this->availability;
    }
    
    public function isAvailable() : bool
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
     * @param int|null $a_time_to_delete
     * @return void
     */
    public function setTimeToDelete(?int $a_time_to_delete) : void
    {
        $this->time_to_delete = $a_time_to_delete;
    }

    /**
     * @return int|null
     */
    public function getTimeToDelete() : ?int
    {
        return $this->time_to_delete;
    }
    
    public function setLrsEndpoint(string $a_endpoint) : void
    {
        $this->lrs_endpoint = $a_endpoint;
    }
    
    public function getLrsEndpoint() : string
    {
        return $this->lrs_endpoint;
    }
    
    public function setLrsKey(string $a_lrs_key) : void
    {
        $this->lrs_key = $a_lrs_key;
    }
    
    public function getLrsKey() : string
    {
        return $this->lrs_key;
    }
    
    public function setLrsSecret(string $a_lrs_secret) : void
    {
        $this->lrs_secret = $a_lrs_secret;
    }
    
    public function getLrsSecret() : string
    {
        return $this->lrs_secret;
    }
    
    public function setPrivacyIdent(int $a_option) : void
    {
        $this->privacy_ident = $a_option;
    }
    
    public function getPrivacyIdent() : int
    {
        return $this->privacy_ident;
    }
    
    public function setPrivacyName(int $a_option) : void
    {
        $this->privacy_name = $a_option;
    }
    
    public function getPrivacyName() : int
    {
        return $this->privacy_name;
    }

    /**
     * @return bool
     */
    public function getOnlyMoveon() : bool
    {
        return $this->only_moveon;
    }

    /**
     * @param bool $only_moveon
     */
    public function setOnlyMoveon(bool $only_moveon) : void
    {
        $this->only_moveon = $only_moveon;
    }

    /**
     * @return bool
     */
    public function getAchieved() : bool
    {
        return $this->achieved;
    }

    /**
     * @param bool $achieved
     */
    public function setAchieved(bool $achieved) : void
    {
        $this->achieved = $achieved;
    }

    /**
     * @return bool
     */
    public function getAnswered() : bool
    {
        return $this->answered;
    }

    /**
     * @param bool $answered
     */
    public function setAnswered(bool $answered) : void
    {
        $this->answered = $answered;
    }

    /**
     * @return bool
     */
    public function getCompleted() : bool
    {
        return $this->completed;
    }

    /**
     * @param bool $completed
     */
    public function setCompleted(bool $completed) : void
    {
        $this->completed = $completed;
    }

    /**
     * @return bool
     */
    public function getFailed() : bool
    {
        return $this->failed;
    }

    /**
     * @param bool $failed
     */
    public function setFailed(bool $failed) : void
    {
        $this->failed = $failed;
    }

    /**
     * @return bool
     */
    public function getInitialized() : bool
    {
        return $this->initialized;
    }

    /**
     * @param bool $initialized
     */
    public function setInitialized(bool $initialized) : void
    {
        $this->initialized = $initialized;
    }

    /**
     * @return bool
     */
    public function getPassed() : bool
    {
        return $this->passed;
    }

    /**
     * @param bool $passed
     */
    public function setPassed(bool $passed) : void
    {
        $this->passed = $passed;
    }

    /**
     * @return bool
     */
    public function getProgressed() : bool
    {
        return $this->progressed;
    }

    /**
     * @param bool $progressed
     */
    public function setProgressed(bool $progressed) : void
    {
        $this->progressed = $progressed;
    }

    /**
     * @return bool
     */
    public function getSatisfied() : bool
    {
        return $this->satisfied;
    }

    /**
     * @param bool $satisfied
     */
    public function setSatisfied(bool $satisfied) : void
    {
        $this->satisfied = $satisfied;
    }

    /**
     * @return bool
     */
    public function getTerminated() : bool
    {
        return $this->terminated;
    }

    /**
     * @param bool $terminated
     */
    public function setTerminated(bool $terminated) : void
    {
        $this->terminated = $terminated;
    }

    /**
     * @return bool
     */
    public function getHideData() : bool
    {
        return $this->hide_data;
    }

    /**
     * @param bool $hide_data
     */
    public function setHideData(bool $hide_data) : void
    {
        $this->hide_data = $hide_data;
    }

    /**
     * @return bool
     */
    public function getTimestamp() : bool
    {
        return $this->timestamp;
    }

    /**
     * @param bool $timestamp
     */
    public function setTimestamp(bool $timestamp) : void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return bool
     */
    public function getDuration() : bool
    {
        return $this->duration;
    }

    /**
     * @param bool $duration
     */
    public function setDuration(bool $duration) : void
    {
        $this->duration = $duration;
    }

    /**
     * @return bool
     */
    public function getNoSubstatements() : bool
    {
        return $this->no_substatements;
    }

    /**
     * @param bool $no_substatements
     */
    public function setNoSubstatements(bool $no_substatements) : void
    {
        $this->no_substatements = $no_substatements;
    }

    public function getForcePrivacySettings() : bool
    {
        return $this->force_privacy_settings;
    }
    
    public function setForcePrivacySettings(bool $force_privacy_settings) : void
    {
        $this->force_privacy_settings = $force_privacy_settings;
    }
    
    public function setPrivacyCommentDefault(string $a_option) : void
    {
        $this->privacy_comment_default = $a_option;
    }
    
    public function getPrivacyCommentDefault() : string
    {
        return $this->privacy_comment_default;
    }
    
    public function setExternalLrs(bool $a_option) : void
    {
        $this->external_lrs = $a_option;
    }
    
    public function getExternalLrs() : bool
    {
        return $this->external_lrs;
    }
    
    /**
     * @return string launch_type
     */
    public function getLaunchType() : string
    {
        return $this->launch_type;
    }
    
    /**
     * @param string remarks
     */
    public function setRemarks(string $a_remarks) : void
    {
        $this->remarks = $a_remarks;
    }
    
    /**
     * @return string remarks
     */
    public function getRemarks() : string
    {
        return $this->remarks;
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
    public function setBypassProxyEnabled(bool $bypassProxyEnabled) : void
    {
        $this->bypassProxyEnabled = $bypassProxyEnabled;
    }

    /**
     * @access public
     */
    public function read() : bool
    {
        global $ilDB, $ilErr;
        
        $query = "SELECT * FROM " . self::DB_TABLE_NAME . " WHERE type_id = %s";
        
        $res = $ilDB->queryF($query, ['integer'], [$this->getTypeId()]);
        $row = $ilDB->fetchObject($res);
        if ($row) {
            $this->setTypeId((int) $row->type_id);
            $this->setTitle($row->title);
            $this->setDescription($row->description);
            $this->setAvailability((int) $row->availability);
            $this->setLrsEndpoint($row->lrs_endpoint);
            $this->setLrsKey($row->lrs_key);
            $this->setLrsSecret($row->lrs_secret);
            $this->setPrivacyIdent((int) $row->privacy_ident);
            $this->setPrivacyName((int) $row->privacy_name);
            $this->setForcePrivacySettings((bool) $row->force_privacy_settings);
            $this->setPrivacyCommentDefault($row->privacy_comment_default);
            $this->setExternalLrs((bool) $row->external_lrs);
            $this->setTimeToDelete((int) $row->time_to_delete);
            $this->setRemarks($row->remarks);
            $this->setBypassProxyEnabled((bool) $row->bypass_proxy);
            $this->setOnlyMoveon((bool) $row->only_moveon);
            $this->setAchieved((bool) $row->achieved);
            $this->setAnswered((bool) $row->answered);
            $this->setCompleted((bool) $row->completed);
            $this->setFailed((bool) $row->failed);
            $this->setInitialized((bool) $row->initialized);
            $this->setPassed((bool) $row->passed);
            $this->setProgressed((bool) $row->progressed);
            $this->setSatisfied((bool) $row->satisfied);
            $this->setTerminated((bool) $row->c_terminated);
            $this->setHideData((bool) $row->hide_data);
            $this->setTimestamp((bool) $row->c_timestamp);
            $this->setDuration((bool) $row->duration);
            $this->setNoSubstatements((bool) $row->no_substatements);

            return true;
        }
        
        return false;
    }
    
    public function save() : void
    {
        if ($this->getTypeId() != 0) {
            $this->update();
        } else {
            $this->create();
        }
    }
    
    /**
     * @access public
     */
    public function create() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $this->setTypeId($DIC->database()->nextId(self::DB_TABLE_NAME));
        $this->update();
    }

    /**
     * @access public
     */
    public function update() : bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->database()->replace(
            self::DB_TABLE_NAME,
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
                'privacy_ident' => array('integer', $this->getPrivacyIdent()),
                'privacy_name' => array('integer', $this->getPrivacyName()),
                'force_privacy_settings' => array('integer', (int) $this->getForcePrivacySettings()),
                'privacy_comment_default' => array('text', $this->getPrivacyCommentDefault()),
                'external_lrs' => array('integer', $this->getExternalLrs()),
                'bypass_proxy' => array('integer', (int) $this->isBypassProxyEnabled()),
                'only_moveon' => array('integer', (int) $this->getOnlyMoveon()),
                'achieved' => array('integer', (int) $this->getAchieved()),
                'answered' => array('integer', (int) $this->getAnswered()),
                'completed' => array('integer', (int) $this->getCompleted()),
                'failed' => array('integer', (int) $this->getFailed()),
                'initialized' => array('integer', (int) $this->getInitialized()),
                'passed' => array('integer', (int) $this->getPassed()),
                'progressed' => array('integer', (int) $this->getProgressed()),
                'satisfied' => array('integer', (int) $this->getSatisfied()),
                'c_terminated' => array('integer', (int) $this->getTerminated()),
                'hide_data' => array('integer', (int) $this->getHideData()),
                'c_timestamp' => array('integer', (int) $this->getTimestamp()),
                'duration' => array('integer', (int) $this->getDuration()),
                'no_substatements' => array('integer', (int) $this->getNoSubstatements())
            )
        );
        
        return true;
    }

    /**
     * @access public
     */
    public function delete() : bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = "DELETE FROM " . self::DB_TABLE_NAME . " WHERE type_id = %s";
        $DIC->database()->manipulateF($query, ['integer'], [$this->getTypeId()]);
        
        return true;
    }
    
    public function getLrsEndpointStatementsLink() : string
    {
        return $this->getLrsEndpoint() . '/' . self::ENDPOINT_STATEMENTS_SUFFIX;
    }
    
    public function getLrsEndpointStatementsAggregationLink() : string
    {
        return dirname($this->getLrsEndpoint()) . '/api/' . self::ENDPOINT_AGGREGATE_SUFFIX;
    }
    
    public function getBasicAuth() : string
    {
        return self::buildBasicAuth($this->getLrsKey(), $this->getLrsSecret());
    }
    
    public static function buildBasicAuth($lrsKey, $lrsSecret) : string
    {
        return 'Basic ' . base64_encode("{$lrsKey}:{$lrsSecret}");
    }

    public function getBasicAuthWithoutBasic() : string
    {
        return self::buildBasicAuthWithoutBasic($this->getLrsKey(), $this->getLrsSecret());
    }
    
    public static function buildBasicAuthWithoutBasic($lrsKey, $lrsSecret) : string
    {
        return base64_encode("{$lrsKey}:{$lrsSecret}");
    }
}
