<?php

// namespace IMSGlobal\LTI\ToolProvider\DataConnector;

use IMSGlobal\LTI\ToolProvider\DataConnector;
use IMSGlobal\LTI\ToolProvider;
use IMSGlobal\LTI\ToolProvider\ConsumerNonce;
use IMSGlobal\LTI\ToolProvider\Context;
use IMSGlobal\LTI\ToolProvider\ResourceLink;
use IMSGlobal\LTI\ToolProvider\ResourceLinkShareKey;
use IMSGlobal\LTI\ToolProvider\ToolConsumer;
use IMSGlobal\LTI\ToolProvider\User;

/**
 * Class to represent an LTI Data Connector for ILIAS
 *
 * @author  Uwe Kohnle based on Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version 3.0.0
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */



class ilLTIDataConnector extends ToolProvider\DataConnector\DataConnector
{
    /**
     * @var \ilLogger
     */
    private $logger = null;


    /**
     * ilLTIDataConnector constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->lti();
        $this->db = "";
        $this->dbTableNamePrefix = "";
    }

    //
    // next functions based on DataConnector_mysql
    //
    /**
     * Load tool consumer object.
     *
     * @param ToolConsumer $consumer ToolConsumer object
     *
     * @return boolean True if the tool consumer object was successfully loaded
     */
    public function loadToolConsumer($consumer)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ok = false;
        $query = 'SELECT consumer_pk, name, consumer_key256, consumer_key, secret, lti_version, ' .
                       'consumer_name, consumer_version, consumer_guid, ' .
                       'profile, tool_proxy, settings, protected, enabled, ' .
                       'enable_from, enable_until, last_access, created, updated ' .
                       // 'enable_from, enable_until, last_access, created, updated, ' .
                       // 'title, description, prefix, user_language, role, local_role_always_member, default_skin ' .
                       'FROM lti2_consumer WHERE ';
        // 'FROM lti2_consumer, lti_ext_consumer ' .
        // 'WHERE lti_ext_consumer.id = consumer_pk AND ';
        if (!empty($consumer->getRecordId())) {
            $query .= 'consumer_pk = %s';
            $types = array('integer');
            $values = array($consumer->getRecordId());
        } else {
            $query .= 'consumer_key256 = %s';
            $types = array('text');
            $key256 = ToolProvider\DataConnector\DataConnector::getConsumerKey($consumer->getKey());
            $values = array($key256);
        }
        // $rsConsumer = mysql_query($sql);
        $res = $ilDB->queryF($query, $types, $values);
        // if ($rsConsumer) {
        while ($row = $ilDB->fetchObject($res)) {
            // while ($row = mysql_fetch_object($rsConsumer)) {
            if (empty($key256) || empty($row->consumer_key) || ($consumer->getKey() === $row->consumer_key)) {
                $consumer->setRecordId(intval($row->consumer_pk));
                $consumer->name = $row->name;
                $consumer->setkey(empty($row->consumer_key) ? $row->consumer_key256 : $row->consumer_key);
                $consumer->secret = $row->secret;
                $consumer->ltiVersion = $row->lti_version;
                $consumer->consumerName = $row->consumer_name;
                $consumer->consumerVersion = $row->consumer_version;
                $consumer->consumerGuid = $row->consumer_guid;
                $consumer->profile = json_decode($row->profile);
                $consumer->toolProxy = $row->tool_proxy;
                $settings = unserialize($row->settings);
                if (!is_array($settings)) {
                    $settings = array();
                }
                $consumer->setSettings($settings);
                $consumer->protected = (intval($row->protected) === 1);
                $consumer->enabled = (intval($row->enabled) === 1);
                $consumer->enableFrom = null;
                if (!is_null($row->enable_from)) {
                    $consumer->enableFrom = strtotime($row->enable_from);
                }
                $consumer->enableUntil = null;
                if (!is_null($row->enable_until)) {
                    $consumer->enableUntil = strtotime($row->enable_until);
                }
                $consumer->lastAccess = null;
                if (!is_null($row->last_access)) {
                    $consumer->lastAccess = strtotime($row->last_access);
                }
                $consumer->created = strtotime($row->created);
                $consumer->updated = strtotime($row->updated);
                //ILIAS specific
                // if ($consumer->setTitle) $consumer->setTitle($row->title);
                // if ($consumer->setDescription) $consumer->setDescription($row->description);
                // if ($consumer->setPrefix) $consumer->setPrefix($row->prefix);
                // if ($consumer->setPrefix) $consumer->setLanguage($row->user_language);
                // if ($consumer->setPrefix) $consumer->setRole($row->role);
                // local_role_always_member
                // default_skin

                $ok = true;
                break;
            }
            // }
            // mysql_free_result($rsConsumer);
        }

        return $ok;
    }
    
    /**
     * Load tool consumer settings
     * @param ilLTIToolConsumer $consumer
     */
    public function loadObjectToolConsumerSettings(ilLTIToolConsumer $consumer)
    {
        $this->loadGlobalToolConsumerSettings($consumer);
        
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * from lti2_consumer where id = ' . $ilDB->quote($consumer->getExtConsumerId(), 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $consumer->setTitle($row->title);
            $consumer->setDescription($row->description);
            $consumer->setPrefix($row->prefix);
            $consumer->setLanguage($row->user_language);
            $consumer->setRole($row->role);
            $consumer->setActive($row->active);
            return true;
        }
        return false;
    }
    
    /**
     * Load global tool consumer settings in consumer
     * @param ilLTIToolConsumer $consumer
     */
    public function loadGlobalToolConsumerSettings(ilLTIToolConsumer $consumer)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * from lti_ext_consumer where id = ' . $ilDB->quote($consumer->getExtConsumerId(), 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $consumer->setTitle($row->title);
            $consumer->setDescription($row->description);
            $consumer->setPrefix($row->prefix);
            $consumer->setLanguage($row->user_language);
            $consumer->setRole($row->role);
            $consumer->setActive($row->active);
            return true;
        }
        return false;
    }
    
    /**
     * Load extended tool consumer object with ILIAS extension.
     *
     * @param ToolConsumer $consumer ToolConsumer object
     *
     * @return boolean True if the tool consumer object was successfully loaded
     */
    public function loadToolConsumerILIAS(ilLTIToolConsumer $consumer)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ok = false;
        $query = 'SELECT consumer_pk, name, consumer_key256, consumer_key, secret, lti_version, ' .
                       'consumer_name, consumer_version, consumer_guid, ' .
                       'profile, tool_proxy, settings, protected, enabled, ' .
                       'enable_from, enable_until, last_access, created, updated, ' .
                       'ext_consumer_id, ref_id ' .
                       #'title, description, prefix, user_language, role, local_role_always_member, default_skin ' .
                       'FROM lti2_consumer ' .
                       #'FROM lti2_consumer, lti_ext_consumer ' .
                       'WHERE ';
        #'WHERE lti_ext_consumer.id = consumer_pk AND ';
        if (!empty($consumer->getRecordId())) {
            $query .= 'consumer_pk = %s';
            $types = array('integer');
            $values = array($consumer->getRecordId());
        } else {
            $query .= 'consumer_key256 = %s';
            $types = array('text');
            $key256 = ToolProvider\DataConnector\DataConnector::getConsumerKey($consumer->getKey());
            $values = array($key256);
        }
        // $rsConsumer = mysql_query($sql);
        $res = $ilDB->queryF($query, $types, $values);
        // if ($rsConsumer) {
        while ($row = $ilDB->fetchObject($res)) {
            // while ($row = mysql_fetch_object($rsConsumer)) {
            if (empty($key256) || empty($row->consumer_key) || ($consumer->getKey() === $row->consumer_key)) {
                $consumer->setRecordId(intval($row->consumer_pk));
                $consumer->name = $row->name;
                $consumer->setkey(empty($row->consumer_key) ? $row->consumer_key256 : $row->consumer_key);
                $consumer->secret = $row->secret;
                $consumer->ltiVersion = $row->lti_version;
                $consumer->consumerName = $row->consumer_name;
                $consumer->consumerVersion = $row->consumer_version;
                $consumer->consumerGuid = $row->consumer_guid;
                $consumer->profile = json_decode($row->profile);
                $consumer->toolProxy = $row->tool_proxy;
                $settings = unserialize($row->settings);
                if (!is_array($settings)) {
                    $settings = array();
                }
                $consumer->setSettings($settings);
                $consumer->protected = (intval($row->protected) === 1);
                $consumer->enabled = (intval($row->enabled) === 1);
                $consumer->enableFrom = null;
                if (!is_null($row->enable_from)) {
                    $consumer->enableFrom = strtotime($row->enable_from);
                }
                $consumer->enableUntil = null;
                if (!is_null($row->enable_until)) {
                    $consumer->enableUntil = strtotime($row->enable_until);
                }
                $consumer->lastAccess = null;
                if (!is_null($row->last_access)) {
                    $consumer->lastAccess = strtotime($row->last_access);
                }
                $consumer->created = strtotime($row->created);
                $consumer->updated = strtotime($row->updated);
                    
                //ILIAS specific
                $consumer->setExtConsumerId($row->ext_consumer_id);
                $consumer->setRefId($row->ref_id);
                #$consumer->setTitle($row->title);
                #$consumer->setDescription($row->description);
                #$consumer->setPrefix($row->prefix);
                #$consumer->setLanguage($row->user_language);
                #$consumer->setRole($row->role);
                // local_role_always_member
                // default_skin

                $ok = true;
                break;
            }
            // }
            // mysql_free_result($rsConsumer);
        }
        
        $this->loadGlobalToolConsumerSettings($consumer);
        return $ok;
    }
    
    
    /**
     * Lookup record id for global settings and ref_id
     * @param ilLTIToolConsumer $consumer
     * @return type
     */
    public function lookupRecordIdByGlobalSettingsAndRefId(ilLTIToolConsumer $consumer)
    {
        $db = $GLOBALS['DIC']->database();
        
        $query = 'SELECT consumer_pk from lti2_consumer ' .
            'WHERE ext_consumer_id = ' . $db->quote($consumer->getExtConsumerId(), 'integer') . ' ' .
            'AND ref_id = ' . $db->quote($consumer->getRefId(), 'integer');
        $res = $db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->consumer_pk;
        }
        return null;
    }
    
    /**
     * Save tool consumer object.
     *
     * @param ToolConsumer $consumer Consumer object
     *
     * @return boolean True if the tool consumer object was successfully saved
     */
    public function saveToolConsumer($consumer)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $id = $consumer->getRecordId();
        $key = $consumer->getKey();
        $key256 = ToolProvider\DataConnector\DataConnector::getConsumerKey($key);
        // $key256 = $this->getConsumerKey($key);
        if ($key === $key256) {
            $key = null;
        }
        $protected = ($consumer->protected) ? 1 : 0;
        $enabled = ($consumer->enabled)? 1 : 0;
        $profile = (!empty($consumer->profile)) ? json_encode($consumer->profile) : null;
        $settingsValue = serialize($consumer->getSettings());
        $time = time();
        $now = date("{$this->dateFormat} {$this->timeFormat}", $time);
        $from = null;
        if (!is_null($consumer->enableFrom)) {
            $from = date("{$this->dateFormat} {$this->timeFormat}", $consumer->enableFrom);
        }
        $until = null;
        if (!is_null($consumer->enableUntil)) {
            $until = date("{$this->dateFormat} {$this->timeFormat}", $consumer->enableUntil);
        }
        $last = null;
        if (!is_null($consumer->lastAccess)) {
            $last = date($this->dateFormat, $consumer->lastAccess);
        }
        
        if (empty($id)) {
            $consumer->setRecordId($ilDB->nextId('lti_ext_consumer'));
            $id = $consumer->getRecordId();
            $consumer->created = $time;
            $consumer->updated = $time;
            if ($key256 == null) {
                $key256 = $id . ToolProvider\DataConnector\DataConnector::getRandomString(10);
            }

            // $query = "INSERT INTO {$this->dbTableNamePrefix}" . $this->CONSUMER_TABLE_NAME . ' (consumer_key256, consumer_key, name, ' .
            $query = 'INSERT INTO lti2_consumer (consumer_key256, consumer_key, name, ' .
                        'secret, lti_version, consumer_name, consumer_version, consumer_guid, profile, tool_proxy, settings, protected, enabled, ' .
                        'enable_from, enable_until, last_access, created, updated, consumer_pk) ' .
                        'VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)';
            $types = array("text", "text", "text",
                        "text", "text", "text", "text", "text", "text", "text", "text", "integer", "integer",
                        "timestamp", "timestamp", "timestamp", "timestamp", "timestamp", "integer");
            $values = array($key256, $key, $consumer->name,
                        $consumer->secret, $consumer->ltiVersion, $consumer->consumerName, $consumer->consumerVersion, $consumer->consumerGuid, $profile, $consumer->toolProxy, $settingsValue, $protected, $enabled,
                        $from, $until, $last, $now, $now, $id);
            $ilDB->manipulateF($query, $types, $values);
        } else {
            $consumer->updated = $time;
            
            $query = 'UPDATE lti2_consumer SET ' .
                           'consumer_key256 = %s, consumer_key = %s, name = %s, ' .
                           'secret= %s, lti_version = %s, consumer_name = %s, consumer_version = %s, consumer_guid = %s, ' .
                           'profile = %s, tool_proxy = %s, settings = %s, protected = %s, enabled = %s, ' .
                           'enable_from = %s, enable_until = %s, last_access = %s, updated = %s ' .
                           'WHERE consumer_pk = %s';
            $types = array("text", "text", "text",
                        "text", "text", "text", "text", "text", "text", "text", "text", "integer", "integer",
                        "timestamp", "timestamp", "timestamp", "timestamp", "integer");
            $values = array($key256, $key, $consumer->name,
                        $consumer->secret, $consumer->ltiVersion, $consumer->consumerName, $consumer->consumerVersion, $consumer->consumerGuid, $profile, $consumer->toolProxy, $settingsValue, $protected, $enabled,
                        $from, $until, $last, $now, $id);
            $ilDB->manipulateF($query, $types, $values);
        }
        return $true;
    }

    /**
     * Save lti_ext_consumer
     * @global type $DIC
     */
    public function saveGlobalToolConsumerSettings(ilLTIToolConsumer $consumer)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        if (!$consumer->getExtConsumerId()) {
            // create
            $new_id = $ilDB->nextId('lti_ext_consumer');
            $query = 'INSERT INTO lti_ext_consumer (title, description, prefix, user_language, role, id, active) ' .
                'VALUES (%s, %s, %s, %s, %s, %s, %s)';
            $types = ["text", "text", "text", "text", "integer", "integer", 'integer'];
            $values = [
                $consumer->getTitle(),
                $consumer->getDescription(),
                $consumer->getPrefix(),
                $consumer->getLanguage(),
                $consumer->getRole(),
                $new_id,
                (int) $consumer->getActive()
            ];
            $ilDB->manipulateF($query, $types, $values);
            $consumer->setExtConsumerId($new_id);
            return true;
        } else {
            // update
            $query = 'update lti_ext_consumer set ' .
                'title  = ' . $ilDB->quote($consumer->getTitle(), 'text') . ', ' .
                'description = ' . $ilDB->quote($consumer->getDescription(), 'text') . ', ' .
                'prefix = ' . $ilDB->quote($consumer->getPrefix(), 'text') . ', ' .
                'user_language = ' . $ilDB->quote($consumer->getLanguage(), 'text') . ', ' .
                'role = ' . $ilDB->quote($consumer->getRole(), 'integer') . ', ' .
                'active = ' . $ilDB->quote((int) $consumer->getActive(), 'integer') . ' ' .
                'where id = ' . $ilDB->quote($consumer->getExtConsumerId(), 'integer');
            $ilDB->manipulate($query);
            return true;
        }
    }
    

    /**
     * Save extended tool consumer object with ILIAS extensions.
     *
     * @param ToolConsumer $consumer Consumer object
     *
     * @return boolean True if the tool consumer object was successfully saved
     */
    public function saveToolConsumerILIAS(ilLTIToolConsumer $consumer)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $id = $consumer->getRecordId();
        $key = $consumer->getKey();
        $key256 = ToolProvider\DataConnector\DataConnector::getConsumerKey($key);
        // $key256 = $this->getConsumerKey($key);
        if ($key === $key256) {
            $key = null;
        }
        $protected = ($consumer->protected) ? 1 : 0;
        $enabled = ($consumer->enabled)? 1 : 0;
        $profile = (!empty($consumer->profile)) ? json_encode($consumer->profile) : null;
        $settingsValue = serialize($consumer->getSettings());
        $time = time();
        $now = date("{$this->dateFormat} {$this->timeFormat}", $time);
        $from = null;
        if (!is_null($consumer->enableFrom)) {
            $from = date("{$this->dateFormat} {$this->timeFormat}", $consumer->enableFrom);
        }
        $until = null;
        if (!is_null($consumer->enableUntil)) {
            $until = date("{$this->dateFormat} {$this->timeFormat}", $consumer->enableUntil);
        }
        $last = null;
        if (!is_null($consumer->lastAccess)) {
            $last = date($this->dateFormat, $consumer->lastAccess);
        }
        
        $consumer->name = $consumer->getTitle();//50UK
        if (empty($id)) {
            $consumer->setRecordId($ilDB->nextId('lti2_consumer'));
            $id = $consumer->getRecordId();
            $consumer->created = $time;
            $consumer->updated = $time;
            if ($key256 == null) {
                $key256 = $id . ToolProvider\DataConnector\DataConnector::getRandomString(10);
            }

            // $query = "INSERT INTO {$this->dbTableNamePrefix}" . $this->CONSUMER_TABLE_NAME . ' (consumer_key256, consumer_key, name, ' .
            $query = 'INSERT INTO lti2_consumer (consumer_key256, consumer_key, name, ' .
                        'secret, lti_version, consumer_name, consumer_version, consumer_guid, profile, tool_proxy, settings, protected, enabled, ' .
                        'enable_from, enable_until, last_access, created, updated, consumer_pk,ext_consumer_id,ref_id) ' .
                        'VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)';
            $types = array("text", "text", "text",
                        "text", "text", "text", "text", "text", "text", "text", "text", "integer", "integer",
                        "timestamp", "timestamp", "timestamp", "timestamp", "timestamp", "integer", 'integer', 'integer');
            $values = array($key256, $key, $consumer->name,
                        $consumer->secret, $consumer->ltiVersion, $consumer->consumerName, $consumer->consumerVersion, $consumer->consumerGuid, $profile, $consumer->toolProxy, $settingsValue, $protected, $enabled,
                        $from, $until, $last, $now, $now, $id, $consumer->getExtConsumerId(), $consumer->getRefId());
            $ilDB->manipulateF($query, $types, $values);
        } else {
            $consumer->updated = $time;
            
            $query = 'UPDATE lti2_consumer SET ' .
                           'consumer_key256 = %s, consumer_key = %s, name = %s, ' .
                           'secret= %s, lti_version = %s, consumer_name = %s, consumer_version = %s, consumer_guid = %s, ' .
                           'profile = %s, tool_proxy = %s, settings = %s, protected = %s, enabled = %s, ' .
                           'enable_from = %s, enable_until = %s, last_access = %s, updated = %s ' .
                           'WHERE consumer_pk = %s';
            $types = array("text", "text", "text",
                        "text", "text", "text", "text", "text", "text", "text", "text", "integer", "integer",
                        "timestamp", "timestamp", "timestamp", "timestamp", "integer");
            $values = array($key256, $key, $consumer->name,
                        $consumer->secret, $consumer->ltiVersion, $consumer->consumerName, $consumer->consumerVersion, $consumer->consumerGuid, $profile, $consumer->toolProxy, $settingsValue, $protected, $enabled,
                        $from, $until, $last, $now, $id);
            $ilDB->manipulateF($query, $types, $values);
        }
        
        return true;
    }
    
    /**
     *  Delete global tool consumer settings
     */
    public function deleteGlobalToolConsumerSettings(ilLTIToolConsumer $consumer)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM lti_ext_consumer WHERE id = %s';
        $types = array("integer");
        $values = array($consumer->getExtConsumerId());
        $ilDB->manipulateF($query, $types, $values);

        $query = 'DELETE FROM lti_ext_consumer_otype WHERE consumer_id = %s';
        $types = array("integer");
        $values = array($consumer->getExtConsumerId());
        $ilDB->manipulateF($query, $types, $values);
        
        
        // delete all assigned lti consumers
        $consumer->initialize();
        return true;
    }

    /**
     * Delete tool consumer object.
     *
     * @param ToolConsumer $consumer Consumer object
     *
     * @return boolean True if the tool consumer object was successfully deleted
     */
    public function deleteToolConsumer($consumer)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];


        // Delete any nonce values for this consumer
        $query = "DELETE FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::NONCE_TABLE_NAME . ' WHERE consumer_pk = %s';
        $types = array("integer");
        $values = array($consumer->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any outstanding share keys for resource links for this consumer
        $query = 'DELETE sk ' .
                       "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' sk ' .
                       "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON sk.resource_link_pk = rl.resource_link_pk ' .
                       'WHERE rl.consumer_pk = %s';
        $types = array("integer");
        $values = array($consumer->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any outstanding share keys for resource links for contexts in this consumer
        $query = 'DELETE sk ' .
                       "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' sk ' .
                       "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON sk.resource_link_pk = rl.resource_link_pk ' .
                       "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ON rl.context_pk = c.context_pk ' .
                       'WHERE c.consumer_pk = %s';
        $types = array("integer");
        $values = array($consumer->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any users in resource links for this consumer
        $query = 'DELETE u ' .
                       "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' u ' .
                       "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON u.resource_link_pk = rl.resource_link_pk ' .
                       'WHERE rl.consumer_pk = %s';
        $types = array("integer");
        $values = array($consumer->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any users in resource links for contexts in this consumer
        $query = 'DELETE u ' .
                       "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' u ' .
                       "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON u.resource_link_pk = rl.resource_link_pk ' .
                       "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ON rl.context_pk = c.context_pk ' .
                       'WHERE c.consumer_pk = %s';
        $types = array("integer");
        $values = array($consumer->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Update any resource links for which this consumer is acting as a primary resource link
        $query = "UPDATE {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' prl ' .
                       "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON prl.primary_resource_link_pk = rl.resource_link_pk ' .
                       'SET prl.primary_resource_link_pk = NULL, prl.share_approved = NULL ' .
                       'WHERE rl.consumer_pk = %s';
        $types = array("integer");
        $values = array($consumer->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Update any resource links for contexts in which this consumer is acting as a primary resource link
        $query = "UPDATE {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' prl ' .
                       "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON prl.primary_resource_link_pk = rl.resource_link_pk ' .
                       "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ON rl.context_pk = c.context_pk ' .
                       'SET prl.primary_resource_link_pk = NULL, prl.share_approved = NULL ' .
                       'WHERE c.consumer_pk = %s';
        $types = array("integer");
        $values = array($consumer->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any resource links for this consumer
        $query = 'DELETE rl ' .
                       "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ' .
                       'WHERE rl.consumer_pk = %s';
        $types = array("integer");
        $values = array($consumer->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any resource links for contexts in this consumer
        $query = 'DELETE rl ' .
                       "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ' .
                       "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ON rl.context_pk = c.context_pk ' .
                       'WHERE c.consumer_pk = %s';
        $types = array("integer");
        $values = array($consumer->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any contexts for this consumer
        $query = 'DELETE c ' .
                       "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ' .
                       'WHERE c.consumer_pk = %s';
        $types = array("integer");
        $values = array($consumer->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete consumer
        $query = 'DELETE c ' .
                       "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONSUMER_TABLE_NAME . ' c ' .
                       'WHERE c.consumer_pk = %s';
        $types = array("integer");
        $values = array($consumer->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        if ($ok) {
            $consumer->initialize();
        }

        return $ok;
    }
    
    /**
     * Get global consumer settings
     * @global type $DIC
     * @return \ilLTIToolConsumer[]
     */
    public function getGlobalToolConsumerSettings()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $consumers = array();
        $query = 'SELECT * from lti_ext_consumer ';
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $consumer = new ilLTIToolConsumer(null, $this);
            $consumer->setExtConsumerId($row->id);
            $consumer->setTitle($row->title);
            $consumer->setDescription($row->description);
            $consumer->setPrefix($row->prefix);
            $consumer->setLanguage($row->user_language);
            $consumer->setRole($row->role);
            $consumer->setActive($row->active);
            $consumers[] = $consumer;
        }
        return $consumers;
    }

    

    ###
    #    Load all tool consumers from the database
    ###
    public function getToolConsumers()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        require_once 'Services/LTI/classes/InternalProvider/class.ilLTIToolConsumer.php';
        $consumers = array();
        $query = 'SELECT consumer_pk, name, consumer_key256, consumer_key, secret, lti_version, ' .
                       'consumer_name, consumer_version, consumer_guid, ' .
                       'profile, tool_proxy, settings, protected, enabled, ' .
                       'enable_from, enable_until, last_access, created, updated, ' .
                       'title, description, prefix, user_language, role, local_role_always_member, default_skin ' .
                       'FROM lti2_consumer, lti_ext_consumer ' .
                       'WHERE lti_ext_consumer.id = consumer_pk';

        // $sql = 'SELECT consumer_pk, consumer_key, consumer_key, name, secret, lti_version, consumer_name, consumer_version, consumer_guid, ' .
        // 'profile, tool_proxy, settings, ' .
        // 'protected, enabled, enable_from, enable_until, last_access, created, updated ' .
        // "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONSUMER_TABLE_NAME . ' ' .
        // 'ORDER BY name';
        // $rsConsumers = mysql_query($sql);
        // if ($rsConsumers) {
        // while ($row = mysql_fetch_object($rsConsumers)) {
        $res = $ilDB->query($query);
        // if ($rsConsumer) {
        while ($row = $ilDB->fetchObject($res)) {
            // $consumer = new ToolProvider\ToolConsumer($row->consumer_key, $this); //ACHTUNG: FEHLER IN BIBLIOTHEK; $row->consumer_key ist i.d.R. null
            $consumer = new ilLTIToolConsumer(null, $this);
            $consumer->setRecordId(intval($row->consumer_pk));
            $consumer->name = $row->name;
            $consumer->secret = $row->secret;
            $consumer->ltiVersion = $row->lti_version;
            $consumer->consumerName = $row->consumer_name;
            $consumer->consumerVersion = $row->consumer_version;
            $consumer->consumerGuid = $row->consumer_guid;
            $consumer->profile = json_decode($row->profile);
            $consumer->toolProxy = $row->tool_proxy;
            $settings = unserialize($row->settings);
            if (!is_array($settings)) {
                $settings = array();
            }
            $consumer->setSettings($settings);
            $consumer->protected = (intval($row->protected) === 1);
            $consumer->enabled = (intval($row->enabled) === 1);
            $consumer->enableFrom = null;
            if (!is_null($row->enable_from)) {
                $consumer->enableFrom = strtotime($row->enable_from);
            }
            $consumer->enableUntil = null;
            if (!is_null($row->enable_until)) {
                $consumer->enableUntil = strtotime($row->enable_until);
            }
            $consumer->lastAccess = null;
            if (!is_null($row->last_access)) {
                $consumer->lastAccess = strtotime($row->last_access);
            }
            $consumer->created = strtotime($row->created);
            $consumer->updated = strtotime($row->updated);
            //ILIAS specific
            $consumer->setTitle($row->title);
            $consumer->setDescription($row->description);
            $consumer->setPrefix($row->prefix);
            $consumer->setLanguage($row->user_language);
            $consumer->setRole($row->role);
            // local_role_always_member
                // default_skin
                $consumer->setKey($row->consumer_key256);//ACHTUNG: hier müsste evtl. consumer_key sein
                $consumers[] = $consumer;
        }
        // mysql_free_result($rsConsumers);
        // }

        return $consumers;
    }
    ###
    ###  ToolProxy methods
    ###

    ###
    #    Load the tool proxy from the database
    ###
    public function loadToolProxy($toolProxy)
    {
        return false;
    }

    ###
    #    Save the tool proxy to the database
    ###
    public function saveToolProxy($toolProxy)
    {
        return false;
    }

    ###
    #    Delete the tool proxy from the database
    ###
    public function deleteToolProxy($toolProxy)
    {
        return false;
    }

    ###
    ###  Context methods
    ###

    /**
     * Load context object.
     *
     * @param Context $context Context object
     *
     * @return boolean True if the context object was successfully loaded
     */
    public function loadContext($context)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ok = false;
        if (!empty($context->getRecordId())) {
            $query = 'SELECT context_pk, consumer_pk, lti_context_id, settings, created, updated ' .
                           "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' ' .
                           'WHERE (context_pk = %s)';
            $types = array("integer");
            $values = array($context->getRecordId());
        } else {
            $query = 'SELECT context_pk, consumer_pk, lti_context_id, settings, created, updated ' .
                           "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' ' .
                           'WHERE (consumer_pk = %s) AND (lti_context_id = %s)';
            $types = array("integer","text");
            $values = array($context->getConsumer()->getRecordId(), $context->ltiContextId);
        }
        $rs_context = $ilDB->queryF($query, $types, $values);
        if ($rs_context) {
            $row = $ilDB->fetchObject($rs_context);
            if ($row) {
                $context->setRecordId(intval($row->context_pk));
                $context->setConsumerId(intval($row->consumer_pk));
                $context->ltiContextId = $row->lti_context_id;
                $settings = unserialize($row->settings);
                if (!is_array($settings)) {
                    $settings = array();
                }
                $context->setSettings($settings);
                $context->created = strtotime($row->created);
                $context->updated = strtotime($row->updated);
                $ok = true;
            }
        }

        return $ok;
    }

    /**
     * Save context object.
     *
     * @param Context $context Context object
     *
     * @return boolean True if the context object was successfully saved
     */
    public function saveContext($context)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $time = time();
        $now = date("{$this->dateFormat} {$this->timeFormat}", $time);
        $settingsValue = serialize($context->getSettings());
        $id = $context->getRecordId();
        $consumer_pk = $context->getConsumer()->getRecordId();
        if (empty($id)) {
            $context->setRecordId($ilDB->nextId(ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME));
            $id = $context->getRecordId();
            $context->created = $time;

            $query = "INSERT INTO {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' (context_pk, consumer_pk, lti_context_id, ' .
                           'settings, created, updated) ' .
                           'VALUES (%s, %s, %s, %s, %s, %s)';
            $types = array("integer","integer","text","text","timestamp","timestamp");
            $values = array($id, $consumer_pk, $context->ltiContextId, $settingsValue, $now, $now);
        } else {
            $query = "UPDATE {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' SET ' .
                           'lti_context_id = %s, settings = %s, ' .
                           'updated = %s' .
                           'WHERE (consumer_pk = %s) AND (context_pk = %s)';
            $types = array("text","text","timestamp","integer","integer");
            $values = array($context->ltiContextId, $settingsValue, $now, $consumer_pk, $id);
        }
        $ok = $ilDB->manipulateF($query, $types, $values);
        if ($ok) {
            $context->updated = $time;
        }

        return $ok;
    }

    /**
     * Delete context object.
     *
     * @param Context $context Context object
     *
     * @return boolean True if the Context object was successfully deleted
     */
    public function deleteContext($context)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // Delete any outstanding share keys for resource links for this context
        $query = 'DELETE sk ' .
                       "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' sk ' .
                       "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON sk.resource_link_pk = rl.resource_link_pk ' .
                       'WHERE rl.context_pk = %s';
        $types = array("integer");
        $values = array($context->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any users in resource links for this context
        $query = 'DELETE u ' .
                       "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' u ' .
                       "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON u.resource_link_pk = rl.resource_link_pk ' .
                       'WHERE rl.context_pk = %s';
        $types = array("integer");
        $values = array($context->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Update any resource links for which this consumer is acting as a primary resource link
        $query = "UPDATE {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' prl ' .
                       "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON prl.primary_resource_link_pk = rl.resource_link_pk ' .
                       'SET prl.primary_resource_link_pk = null, prl.share_approved = null ' .
                       'WHERE rl.context_pk = %s';
        $types = array("integer");
        $values = array($context->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any resource links for this consumer
        $query = 'DELETE rl ' .
                       "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ' .
                       'WHERE rl.context_pk = %s';
        $types = array("integer");
        $values = array($context->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete context
        $query = 'DELETE c ' .
                       "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ' .
                       'WHERE c.context_pk = %s';
        $types = array("integer");
        $values = array($context->getRecordId());
        $ok = $ilDB->manipulateF($query, $types, $values);
        if ($ok) {
            $context->initialize();
        }

        return $ok;
    }

    ###
    ###  ResourceLink methods
    ###

    /**
     * Load resource link object.
     *
     * @param ResourceLink $resourceLink Resource_Link object
     *
     * @return boolean True if the resource link object was successfully loaded
     */
    public function loadResourceLink($resourceLink)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ok = false;
        if (!empty($resourceLink->getRecordId())) {
            $query = 'SELECT resource_link_pk, context_pk, consumer_pk, lti_resource_link_id, settings, primary_resource_link_pk, share_approved, created, updated ' .
                           "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' ' .
                           'WHERE (resource_link_pk = %s)';
            $types = array("integer");
            $values = array($resourceLink->getRecordId());
        } elseif (!empty($resourceLink->getContext())) {
            $query = 'SELECT resource_link_pk, context_pk, consumer_pk, lti_resource_link_id, settings, primary_resource_link_pk, share_approved, created, updated ' .
                           "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' ' .
                           'WHERE (context_pk = %s) AND (lti_resource_link_id = %s)';
            $types = array("integer","text");
            $values = array($resourceLink->getContext()->getRecordId(), $resourceLink->getId());
        } else {
            $query = 'SELECT r.resource_link_pk, r.context_pk, r.consumer_pk, r.lti_resource_link_id, r.settings, r.primary_resource_link_pk, r.share_approved, r.created, r.updated ' .
                           "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' r LEFT OUTER JOIN ' .
                           $this->dbTableNamePrefix . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ON r.context_pk = c.context_pk ' .
                           ' WHERE ((r.consumer_pk = %s) OR (c.consumer_pk = %s)) AND (lti_resource_link_id = %s)';
            $types = array("integer","integer","text");
            $values = array($resourceLink->getConsumer()->getRecordId(), $resourceLink->getConsumer()->getRecordId(), $resourceLink->getId());
        }
        $rsContext = $ilDB->queryF($query, $types, $values);
        if ($rsContext) {
            $row = $ilDB->fetchObject($rsContext);
            if ($row) {
                $resourceLink->setRecordId(intval($row->resource_link_pk));
                if (!is_null($row->context_pk)) {
                    $resourceLink->setContextId(intval($row->context_pk));
                } else {
                    $resourceLink->setContextId(null);
                }
                if (!is_null($row->consumer_pk)) {
                    $resourceLink->setConsumerId(intval($row->consumer_pk));
                } else {
                    $resourceLink->setConsumerId(null);
                }
                $resourceLink->ltiResourceLinkId = $row->lti_resource_link_id;
                $settings = unserialize($row->settings);
                if (!is_array($settings)) {
                    $settings = array();
                }
                $resourceLink->setSettings($settings);
                if (!is_null($row->primary_resource_link_pk)) {
                    $resourceLink->primaryResourceLinkId = intval($row->primary_resource_link_pk);
                } else {
                    $resourceLink->primaryResourceLinkId = null;
                }
                $resourceLink->shareApproved = (is_null($row->share_approved)) ? null : (intval($row->share_approved) === 1);
                $resourceLink->created = strtotime($row->created);
                $resourceLink->updated = strtotime($row->updated);
                $ok = true;
            }
        }

        return $ok;
    }

    /**
     * Save resource link object.
     *
     * @param ResourceLink $resourceLink Resource_Link object
     *
     * @return boolean True if the resource link object was successfully saved
     */
    public function saveResourceLink($resourceLink)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (is_null($resourceLink->shareApproved)) {
            $approved = 'NULL';
        } elseif ($resourceLink->shareApproved) {
            $approved = '1';
        } else {
            $approved = '0';
        }
        if (empty($resourceLink->primaryResourceLinkId)) {
            $primaryResourceLinkId = 'NULL';
        } else {
            $primaryResourceLinkId = strval($resourceLink->primaryResourceLinkId);
        }
        $time = time();
        $now = date("{$this->dateFormat} {$this->timeFormat}", $time);
        $settingsValue = serialize($resourceLink->getSettings());
        if (!empty($resourceLink->getContext())) {
            //$consumerId = 'NULL';
            $consumerId = strval($resourceLink->getConsumer()->getRecordId());
            $contextId = strval($resourceLink->getContext()->getRecordId());
        } elseif (!empty($resourceLink->getContextId())) {
            //$consumerId = 'NULL';
            $consumerId = strval($resourceLink->getConsumer()->getRecordId());
            $contextId = strval($resourceLink->getContextId());
        } else {
            $consumerId = strval($resourceLink->getConsumer()->getRecordId());
            $contextId = 'NULL';
        }
        $id = $resourceLink->getRecordId();
        if (empty($id)) {
            $resourceLink->setRecordId($ilDB->nextId(ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME));
            $id = $resourceLink->getRecordId();
            $resourceLink->created = $time;
            $query = "INSERT INTO {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' (resource_link_pk, consumer_pk, context_pk, ' .
                           'lti_resource_link_id, settings, primary_resource_link_pk, share_approved, created, updated) ' .
                           'VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)';
            $types = array("integer","integer","integer","text","text","integer","integer","timestamp","timestamp");
            $values = array($id, $consumerId, $contextId, $resourceLink->getId(), $settingsValue, $primaryResourceLinkId, $approved, $now, $now);
        } elseif ($contextId !== 'NULL') {
            $query = "UPDATE {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' SET ' .
                           'consumer_pk = %s, lti_resource_link_id = %s, settings = %s, ' .
                           'primary_resource_link_pk = %s, share_approved = %s, updated = %s ' .
                           'WHERE (context_pk = %s) AND (resource_link_pk = %s)';
            $types = array("integer","text","text","integer","integer","timestamp","integer","integer");
            $values = array($consumerId, $resourceLink->getId(), $settingsValue, $primaryResourceLinkId, $approved, $now, $contextId, $id);
        } else {
            $query = "UPDATE {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' SET ' .
                           'context_pk = %s, lti_resource_link_id = %s, settings = %s, ' .
                           'primary_resource_link_pk = %s, share_approved = %s, updated = %s ' .
                           'WHERE (consumer_pk = %s) AND (resource_link_pk = %s)';
            $types = array("integer","text","text","integer","integer","timestamp","integer","integer");
            $values = array($contextId, $resourceLink->getId(), $settingsValue, $primaryResourceLinkId, $approved, $now, $consumerId, $id);
        }
        $ok = $ilDB->manipulateF($query, $types, $values);

        $this->logger->info('Update resource link with query: ' . $query);
        $this->logger->logStack();
        $this->logger->dump($values, ilLogLevel::INFO);

        $this->logger->dump($ok);

        if ($ok) {
            $resourceLink->updated = $time;
        }

        return $ok;
    }

    /**
     * Delete resource link object.
     *
     * @param ResourceLink $resourceLink Resource_Link object
     *
     * @return boolean True if the resource link object was successfully deleted
     */
    public function deleteResourceLink($resourceLink)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // Delete any outstanding share keys for resource links for this consumer
        $query = "DELETE FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' ' .
                       'WHERE (resource_link_pk = %s)';
        $types = array("integer");
        $values = array($resourceLink->getRecordId());
        $ok = $ilDB->manipulateF($query, $types, $values);

        // Delete users
        if ($ok) {
            $query = "DELETE FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
                           'WHERE (resource_link_pk = %s)';
            $types = array("integer");
            $values = array($resourceLink->getRecordId());
            $ok = $ilDB->manipulateF($query, $types, $values);
        }

        // Update any resource links for which this is the primary resource link
        if ($ok) {
            $query = "UPDATE {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' ' .
                           'SET primary_resource_link_pk = NULL ' .
                           'WHERE (primary_resource_link_pk = %s)';
            $types = array("integer");
            $values = array($resourceLink->getRecordId());
            $ok = $ilDB->manipulateF($query, $types, $values);
        }

        // Delete resource link
        if ($ok) {
            $query = "DELETE FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' ' .
                           'WHERE (resource_link_pk = %s)';
            $types = array("integer");
            $values = array($resourceLink->getRecordId());
            $ok = $ilDB->manipulateF($query, $types, $values);
        }

        if ($ok) {
            $resourceLink->initialize();
        }

        return $ok;
    }

    /**
     * Get array of user objects.
     *
     * Obtain an array of User objects for users with a result sourcedId.  The array may include users from other
     * resource links which are sharing this resource link.  It may also be optionally indexed by the user ID of a specified scope.
     *
     * @param ResourceLink $resourceLink      Resource link object
     * @param boolean     $localOnly True if only users within the resource link are to be returned (excluding users sharing this resource link)
     * @param int         $idScope     Scope value to use for user IDs
     *
     * @return array Array of User objects
     */
    public function getUserResultSourcedIDsResourceLink($resourceLink, $localOnly, $idScope)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $users = array();

        // if ($localOnly) {
        // $query = 'SELECT u.user_pk, u.lti_result_sourcedid, u.lti_user_id, u.created, u.updated ' .
        // "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' AS u '  .
        // "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' AS rl '  .
        // 'ON u.resource_link_pk = rl.resource_link_pk ' .
        // "WHERE (rl.resource_link_pk = %d) AND (rl.primary_resource_link_pk IS NULL)",
        // $resourceLink->getRecordId());
        // } else {
        // $query = 'SELECT u.user_pk, u.lti_result_sourcedid, u.lti_user_id, u.created, u.updated ' .
        // "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' AS u '  .
        // "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' AS rl '  .
        // 'ON u.resource_link_pk = rl.resource_link_pk ' .
        // 'WHERE ((rl.resource_link_pk = %d) AND (rl.primary_resource_link_pk IS NULL)) OR ' .
        // '((rl.primary_resource_link_pk = %d) AND (share_approved = 1))',
        // $resourceLink->getRecordId(), $resourceLink->getRecordId());
        // }
        // $rsUser = mysql_query($sql);
        // if ($rsUser) {
        // while ($row = $ilDB->fetchObject($rsUser)) {
        // $user = ToolProvider\User::fromResourceLink($resourceLink, $row->lti_user_id);
        // $user->setRecordId(intval($row->user_pk));
        // $user->ltiResultSourcedId = $row->lti_result_sourcedid;
        // $user->created = strtotime($row->created);
        // $user->updated = strtotime($row->updated);
        // if (is_null($idScope)) {
        // $users[] = $user;
        // } else {
        // $users[$user->getId($idScope)] = $user;
        // }
        // }
        // }

        return $users;
    }

    /**
     * Get array of shares defined for this resource link.
     *
     * @param ResourceLink $resourceLink Resource_Link object
     *
     * @return array Array of ResourceLinkShare objects
     */
    public function getSharesResourceLink($resourceLink)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $shares = array();

        $query = 'SELECT consumer_pk, resource_link_pk, share_approved ' .
                       "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' ' .
                       'WHERE (primary_resource_link_pk = %s) ' .
                       'ORDER BY consumer_pk';
        $types = array("integer");
        $values = array($resourceLink->getRecordId());
        $rsShare = $ilDB->queryF($query, $types, $values);
        if ($rsShare) {
            while ($row = $ilDB->fetchObject($rsShare)) {
                $share = new ToolProvider\ResourceLinkShare();
                $share->resourceLinkId = intval($row->resource_link_pk);
                $share->approved = (intval($row->share_approved) === 1);
                $shares[] = $share;
            }
        }

        return $shares;
    }


    ###
    ###  ConsumerNonce methods
    ###

    /**
     * Load nonce object.
     *
     * @param ConsumerNonce $nonce Nonce object
     *
     * @return boolean True if the nonce object was successfully loaded
     */
    public function loadConsumerNonce($nonce)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ok = true;

        // Delete any expired nonce values
        $now = date("{$this->dateFormat} {$this->timeFormat}", time());//PRÜFEN UK
        $query = "DELETE FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::NONCE_TABLE_NAME . " WHERE expires <= %s";
        $types = array("timestamp");
        $values = array($now);
        $ilDB->manipulateF($query, $types, $values);
        // Load the nonce
        $query = "SELECT value AS T FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::NONCE_TABLE_NAME . ' WHERE (consumer_pk = %s) AND (value = %s)';
        $types = array("integer","text");
        $values = array($nonce->getConsumer()->getRecordId(), $nonce->getValue());
        $rs_nonce = $ilDB->queryF($query, $types, $values);
        if ($rs_nonce) {
            $row = $ilDB->fetchObject($rs_nonce);
            if (!$row) {
                $ok = false;
            }
        }

        return $ok;
    }

    /**
     * Save nonce object.
     *
     * @param ConsumerNonce $nonce Nonce object
     *
     * @return boolean True if the nonce object was successfully saved
     */
    public function saveConsumerNonce($nonce)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $expires = date("{$this->dateFormat} {$this->timeFormat}", $nonce->expires);
        $query = "INSERT INTO {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::NONCE_TABLE_NAME . " (consumer_pk, value, expires) VALUES (%s, %s, %s)";
        $types = array("integer","text","timestamp");
        $values = array($nonce->getConsumer()->getRecordId(), $nonce->getValue(), $expires);
        $ok = $ilDB->manipulateF($query, $types, $values);

        return $ok;
    }


    ###
    ###  ResourceLinkShareKey methods
    ###

    /**
     * Load resource link share key object.
     *
     * @param ResourceLinkShareKey $shareKey Resource_Link share key object
     *
     * @return boolean True if the resource link share key object was successfully loaded
     */
    public function loadResourceLinkShareKey($shareKey)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ok = false;

        // Clear expired share keys
        $now = date("{$this->dateFormat} {$this->timeFormat}", time());
        $query = "DELETE FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . " WHERE expires <= '%s'";
        $types = array("timestamp");
        $values = array($now);
        $ilDB->manipulateF($query, $types, $values);

        // Load share key
        // $id = mysql_real_escape_string($shareKey->getId());//ACHTUNG UK utf8
        $id = $shareKey->getId();
        $query = 'SELECT resource_link_pk, auto_approve, expires ' .
               "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' ' .
               "WHERE share_key_id = %s";
        $types = array("text");
        $values = array($id);
        $rsShareKey = $ilDB->queryF($query, $types, $values);
        if ($rsShareKey) {
            $row = $ilDB->fetchObject($rsShareKey);
            if ($row && (intval($row->resource_link_pk) === $shareKey->resourceLinkId)) {
                $shareKey->autoApprove = (intval($row->auto_approve) === 1);
                $shareKey->expires = strtotime($row->expires);
                $ok = true;
            }
        }

        return $ok;
    }

    /**
     * Save resource link share key object.
     *
     * @param ResourceLinkShareKey $shareKey Resource link share key object
     *
     * @return boolean True if the resource link share key object was successfully saved
     */
    public function saveResourceLinkShareKey($shareKey)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($shareKey->autoApprove) {
            $approve = 1;
        } else {
            $approve = 0;
        }
        $expires = date("{$this->dateFormat} {$this->timeFormat}", $shareKey->expires);
        $query = "INSERT INTO {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' ' .
                       '(share_key_id, resource_link_pk, auto_approve, expires) ' .
                       "VALUES (%s, %s, %s, %s)";
        $types = array("text","integer","integer","timestamp");
        $values = array($shareKey->getId(), $shareKey->resourceLinkId, $approve, $expires);
        $ok = $ilDB->manipulateF($query, $types, $values);

        return $ok;
    }

    /**
     * Delete resource link share key object.
     *
     * @param ResourceLinkShareKey $shareKey Resource link share key object
     *
     * @return boolean True if the resource link share key object was successfully deleted
     */
    public function deleteResourceLinkShareKey($shareKey)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . " WHERE share_key_id = %s";
        $types = array("text");
        $values = array($shareKey->getId());
        $ok = $ilDB->manipulateF($query, $types, $values);

        if ($ok) {
            $shareKey->initialize();
        }

        return $ok;
    }


    ###
    ###  User methods
    ###

    /**
     * Load user object.
     *
     * @param User $user User object
     *
     * @return boolean True if the user object was successfully loaded
     */
    public function loadUser($user)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ok = false;
        if ($user->getRecordId()) {
            $query = 'SELECT user_pk, resource_link_pk, lti_user_id, lti_result_sourcedid, created, updated ' .
                'FROM ' . $this->dbTableNamePrefix . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
                'WHERE user_pk = ' . $ilDB->quote($user->getRecordId(), 'integer');
        } else {
            $query = 'SELECT user_pk, resource_link_pk, lti_user_id, lti_result_sourcedid, created, updated ' .
                'FROM ' . $this->dbTableNamePrefix . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
                'WHERE resource_link_pk = ' . $ilDB->quote($user->getResourceLink()->getRecordId(), 'integer') . ' ' .
                'AND lti_user_id = ' . $ilDB->quote($user->getId(ToolProvider\ToolProvider::ID_SCOPE_ID_ONLY), 'text');
        }

        $this->logger->debug('Loading user with query: ' . $query);

        $ok = false;
        try {
            $res = $ilDB->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $user->setRecordId($row->user_pk);
                $user->setResourceLinkId($row->resource_link_pk);
                $user->ltiUserId = $row->lti_user_id;
                $user->ltiResultSourcedId = $row->lti_result_sourcedid;
                $user->created = strtotime($row->created);
                $user->updated = strtotime($row->updated);
                $ok = true;
            }
        } catch (ilDatabaseException $e) {
            $this->logger->error($e);
        }
        return $ok;

        // if (!empty($user->getRecordId())) {
            // $query = 'SELECT user_pk, resource_link_pk, lti_user_id, lti_result_sourcedid, created, updated ' .
                           // "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
                           // 'WHERE (user_pk = %d)',
            // $user->getRecordId());
        // } else {
            // $query = 'SELECT user_pk, resource_link_pk, lti_user_id, lti_result_sourcedid, created, updated ' .
                           // "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
                           // 'WHERE (resource_link_pk = %d) AND (lti_user_id = %s)',
                           // $user->getResourceLink()->getRecordId(),
                           // ToolProvider\DataConnector\DataConnector::quoted($user->getId(ToolProvider\ToolProvider::ID_SCOPE_ID_ONLY)));
        // }
        // $rsUser = mysql_query($sql);
        // if ($rsUser) {
            // $row = $ilDB->fetchObject($rsUser);
            // if ($row) {
                // $user->setRecordId(intval($row->user_pk));
                // $user->setResourceLinkId(intval($row->resource_link_pk));
                // $user->ltiUserId = $row->lti_user_id;
                // $user->ltiResultSourcedId = $row->lti_result_sourcedid;
                // $user->created = strtotime($row->created);
                // $user->updated = strtotime($row->updated);
                // $ok = true;
            // }
        // }
    }

    /**
     * Save user object.
     *
     * @param User $user User object
     *
     * @return boolean True if the user object was successfully saved
     */
    public function saveUser($user)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $this->logger->info('Save user called');

        $time = time();
        $now = date($this->dateFormat . ' ' . $this->timeFormat, $time);
        if (is_null($user->created)) {
            $user->setRecordId($ilDB->nextId($this->dbTableNamePrefix . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME));
            $user->created = $time;
            $query = 'INSERT INTO ' . $this->dbTableNamePrefix . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
                '(user_pk,resource_link_pk,lti_user_id, lti_result_sourcedid, created, updated) ' .
                'VALUES( ' .
                $ilDB->quote($user->getRecordId(), 'integer') . ', ' .
                $ilDB->quote($user->getResourceLink()->getRecordId(), 'integer') . ', ' .
                $ilDB->quote($user->getId(ToolProvider\ToolProvider::ID_SCOPE_ID_ONLY), 'text') . ', ' .
                $ilDB->quote($user->ltiResultSourcedId, 'text') . ', ' .
                $ilDB->quote($now, 'text') . ', ' .
                $ilDB->quote($now, 'text') .
                ')';
        } else {
            $user->updated = $time;
            $query = 'UPDATE ' . $this->dbTableNamePrefix . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
                'SET lti_result_sourcedid = ' . $ilDB->quote($user->ltiResultSourcedId, 'text') . ', ' .
                'updated = ' . $ilDB->quote($now, 'text') . ' ' .
                'WHERE user_pk = ' . $ilDB->quote($user->getRecordId(), 'integer');
        }

        $this->logger->debug('Saving user data with query: ' . $query);

        $ok = false;
        try {
            $ilDB->manipulate($query);
            $ok = true;
        } catch (ilDatabaseException $e) {
            $this->logger->error($e->getMessage());
        }

        return $ok;



        // $time = time();
        // $now = date("{$this->dateFormat} {$this->timeFormat}", $time);
        // if (is_null($user->created)) {
            // $query = "INSERT INTO {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' (resource_link_pk, ' .
                           // 'lti_user_id, lti_result_sourcedid, created, updated) ' .
                           // 'VALUES (%d, %s, %s, %s, %s)',
                           // $user->getResourceLink()->getRecordId(),
                           // ToolProvider\DataConnector\DataConnector::quoted($user->getId(ToolProvider\ToolProvider::ID_SCOPE_ID_ONLY)), ToolProvider\DataConnector\DataConnector::quoted($user->ltiResultSourcedId),
                           // ToolProvider\DataConnector\DataConnector::quoted($now), ToolProvider\DataConnector\DataConnector::quoted($now));
        // } else {
            // $query = "UPDATE {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
                           // 'SET lti_result_sourcedid = %s, updated = %s ' .
                           // 'WHERE (user_pk = %d)',
                           // ToolProvider\DataConnector\DataConnector::quoted($user->ltiResultSourcedId),
                           // ToolProvider\DataConnector\DataConnector::quoted($now),
                           // $user->getRecordId());
        // }
        // $ok = mysql_query($sql);
        // if ($ok) {
            // if (is_null($user->created)) {
                // $user->setRecordId(mysql_insert_id());
                // $user->created = $time;
            // }
            // $user->updated = $time;
        // }

        // return $ok;
    }

    /**
     * Delete user object.
     *
     * @param User $user User object
     *
     * @return boolean True if the user object was successfully deleted
     */
    public function deleteUser($user)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = 'DELETE from ' . $this->dbTableNamePrefix . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
            'WHERE user_pk = ' . $ilDB->quote($user->getRecordId(), 'integer');

        $ok = false;
        try {
            $ilDB->manipulate($query);
            $user->initialize();
            $ok = true;
        } catch (ilDatabaseException $e) {
            $this->logger->error($e);
        }
        return $ok;

        // $query = "DELETE FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
                       // 'WHERE (user_pk = %d)',
                       // $user->getRecordId());
        // $ok = mysql_query($sql);

        // if ($ok) {
            // $user->initialize();
        // }

        // return $ok;
    }


    /**
     * Lookup resources for user object relation
     *
     * @param $a_ref_id
     * @param $a_lti_user
     * @param $a_ext_consumer
     * @param ilDateTime $since
     * @return int[]
     *
     */
    public function lookupResourcesForUserObjectRelation($a_ref_id, $a_lti_user, $a_ext_consumer, ilDateTime $since = null)
    {
        global $DIC;

        $db = $DIC->database();
        $logger = $DIC->logger()->lti();

        $query = 'select rl.resource_link_pk ' .
            'from lti2_user_result ur join lti2_resource_link rl on rl.resource_link_pk = ur.resource_link_pk ' .
            'join lti2_consumer c on rl.consumer_pk = c.consumer_pk ' .
            'join lti_ext_consumer ec on c.ext_consumer_id = ec.id ' .
            'where c.enabled = ' . $db->quote(1, 'integer') . ' ' .
            'and ref_id = ' . $db->quote($a_ref_id, 'integer') . ' ' .
            'and ur.lti_user_id = ' . $db->quote($a_lti_user, 'text') . ' ' .
            'and ec.id = ' . $db->quote($a_ext_consumer, 'integer');

        $resource_links = [];
        try {
            $res = $db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $resource_links[] = $row->resource_link_pk;
            }
        } catch (ilDatabaseException $e) {
            $logger->error('Query execution failed with message: ' . $e->getMessage());
        }
        return $resource_links;
    }

    /**
     * @param \ilDateTime $since
     */
    public function lookupResourcesForAllUsersSinceDate(ilDateTime $since)
    {
        global $DIC;

        $db = $DIC->database();
        $logger = $DIC->logger()->lti();

        $query = 'select lti_user_id, rl.resource_link_pk, ec.id, ref_id ' .
            'from lti2_resource_link rl join lti2_user_result ur on rl.resource_link_pk = ur.resource_link_pk ' .
            'join lti2_consumer c on rl.consumer_pk = c.consumer_pk ' .
            'join lti_ext_consumer ec on ext_consumer_id = ec.id ' .
            'where c.enabled = ' . $db->quote(1, 'integer') . ' ' .
            'and rl.updated > ' . $db->quote($since->get(IL_CAL_DATETIME), 'timestamp');
        $res = $db->query($query);

        $results = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $results[$row->id . '__' . $row->lti_user_id][] = $row->resource_link_pk . '__' . $row->ref_id;
        }
        return $results;
    }
}
