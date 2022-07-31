<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

// namespace ILIAS\LTI\Tool\DataConnector;

use ILIAS\LTI\ToolProvider;
use ILIAS\LTI\ToolProvider\PlatformNonce;
use ILIAS\LTI\ToolProvider\Context;
use ILIAS\LTI\ToolProvider\ResourceLink;
use ILIAS\LTI\ToolProvider\ResourceLinkShare;
use ILIAS\LTI\ToolProvider\ResourceLinkShareKey;
use ILIAS\LTI\ToolProvider\Platform;
//use ILIAS\LTI\ToolProvider\User;
use ILIAS\LTI\ToolProvider\UserResult;
use ILIAS\LTI\ToolProvider\Tool;
use ILIAS\LTI\ToolProvider\Util;
//UK: added
use \ILIAS\LTI\ToolProvider\AccessToken;

class ilLTIDataConnector extends ToolProvider\DataConnector\DataConnector
{
    private ?\ilLogger $logger = null;

    private ilDBInterface $database;

    /**
     * ilLTIDataConnector constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->database = $DIC->database();

        $this->logger = ilLoggerFactory::getLogger('ltis');
        $this->db = null;
        $this->dbTableNamePrefix = "";
    }

    //
    // next functions based on LTI Data Connector for MySQLi
    //
    /**
     * Load platform object.
     * @param Platform $platform Platform object
     * @return boolean True if the tool consumer object was successfully loaded
     */
    public function loadPlatform(\ILIAS\LTI\ToolProvider\Platform $platform) : bool
    {
        $ok = false;
        $allowMultiple = false;
        $id = $platform->getRecordId();
        $query = 'SELECT consumer_pk, name, consumer_key, secret, ' .
            'platform_id, client_id, deployment_id, public_key, ' .
            'lti_version, signature_method, consumer_name, consumer_version, consumer_guid, ' .
            'profile, tool_proxy, settings, protected, enabled, ' .
            'enable_from, enable_until, last_access, created, updated, ext_consumer_id, ref_id ' .
            'FROM lti2_consumer WHERE ';
        if (!is_null($id)) {
            $query .= 'consumer_pk = %s';
            $types = array('integer');
            $values = array($id);
        } elseif (!empty($platform->platformId)) {
            if (empty($platform->clientId)) {
                $allowMultiple = true;
                $query .= '(platform_id = %s)';
                $types = array('text');
                $values = array($platform->platformId);
            } elseif (empty($platform->deploymentId)) {
                $allowMultiple = true;
                $query .= '(platform_id = %s) AND (client_id = %s)';
                $types = array('text','text');
                $values = array($platform->platformId, $platform->clientId);
            } else {
                $query .= '(platform_id = %s) AND (client_id = %s) AND (deployment_id = %s)';
                $types = array('text','text','text');
                $values = array($platform->platformId, $platform->clientId, $platform->deploymentId);
            }
        } elseif (!empty($platform->getKey())) {
            $key = $platform->getKey();
            $query .= 'consumer_key = %s';
            $types = array('text');
            $values = array($key);
        } else {
            return false;
        }
//        $ok = $this->executeQuery($sql, $stmt);
//        if ($ok) {
//            $rsConsumer = $stmt->get_result();
//            $ok = $rsConsumer !== false;
//            if ($ok) {
//                $row = $rsConsumer->fetch_object();
//                $ok = $row && ($allowMultiple || is_null($rsConsumer->fetch_object()));
//            }
//        }
//        if ($ok) {
        $res = $this->database->queryF($query, $types, $values);
        while ($row = $this->database->fetchObject($res)) {
            $platform->setRecordId(intval($row->consumer_pk));
            $platform->name = $row->name;
            $platform->setkey((string) $row->consumer_key);
            $platform->secret = $row->secret;
            $platform->platformId = $row->platform_id;
            $platform->clientId = $row->client_id;
            $platform->deploymentId = $row->deployment_id;
            $platform->rsaKey = $row->public_key;
            $platform->ltiVersion = $row->lti_version;
            $platform->signatureMethod = $row->signature_method;
            $platform->consumerName = $row->consumer_name;
            $platform->consumerVersion = $row->consumer_version;
            $platform->consumerGuid = $row->consumer_guid;
            $platform->profile = json_decode((string) $row->profile);
            $platform->toolProxy = $row->tool_proxy;
            $settings = json_decode($row->settings, true);
            if (!is_array($settings)) {
                $settings = @unserialize($row->settings);  // check for old serialized setting
            }
            if (!is_array($settings)) {
                $settings = array();
            }
            $platform->setSettings($settings);
            $platform->protected = (intval($row->protected) === 1);
            $platform->enabled = (intval($row->enabled) === 1);
            $platform->enableFrom = null;
            if (!is_null($row->enable_from)) {
                $platform->enableFrom = strtotime($row->enable_from);
            }
            $platform->enableUntil = null;
            if (!is_null($row->enable_until)) {
                $platform->enableUntil = strtotime($row->enable_until);
            }
            $platform->lastAccess = null;
            if (!is_null($row->last_access)) {
                $platform->lastAccess = strtotime($row->last_access);
            }
            $platform->created = strtotime($row->created);
            $platform->updated = strtotime($row->updated);
            //ILIAS specific
            $platform->setExtConsumerId(intval($row->ext_consumer_id));
            $platform->setRefId((int) $row->ref_id);
            // if ($platform->setTitle) $platform->setTitle($row->title);
            // if ($platform->setDescription) $platform->setDescription($row->description);
            // if ($platform->setPrefix) $platform->setPrefix($row->prefix);
            // if ($platform->setPrefix) $platform->setLanguage($row->user_language);
            // if ($platform->setPrefix) $platform->setRole($row->role);
            // local_role_always_member
            // default_skin
            $this->fixPlatformSettings($platform, false);
            $ok = true;
        }
        return $ok;
    }
    #######
//    /**
//     * Load tool consumer settings
//     * @param ilLTIPlatform $platform
//     * @return bool
//     */
//    public function loadObjectToolConsumerSettings(ilLTIPlatform $platform) : bool
//    {
//        $this->loadGlobalToolConsumerSettings($platform);
//
//        $ilDB = $this->database;
//
//        $query = 'SELECT * from lti2_consumer where id = ' . $ilDB->quote($platform->getExtConsumerId(), 'integer');
//        $res = $ilDB->query($query);
//        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
//            $platform->setTitle($row->title);
//            $platform->setDescription($row->description);
//            $platform->setPrefix($row->prefix);
//            $platform->setLanguage($row->user_language);
//            $platform->setRole($row->role);
//            $platform->setActive((bool) $row->active);
//            return true;
//        }
//        return false;
//    }

    /**
     * Load global tool consumer settings in consumer
     * @param ilLTIPlatform $platform
     * @return bool
     */
    public function loadGlobalToolConsumerSettings(ilLTIPlatform $platform) : bool
    {
        $ilDB = $this->database;

        $query = 'SELECT * from lti_ext_consumer where id = ' . $ilDB->quote($platform->getExtConsumerId(), 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $platform->setTitle($row->title);
            $platform->setDescription($row->description);
            $platform->setPrefix($row->prefix);
            $platform->setLanguage($row->user_language);
            $platform->setRole((int) $row->role);
            $platform->setActive((bool) $row->active);
            return true;
        }
        return false;
    }

//    /**
//     * Load extended tool consumer object with ILIAS extension.
//     * @param Platform $platform Platform object
//     * @return boolean True if the tool consumer object was successfully loaded
//     */
//    public function loadToolConsumerILIAS(ilLTIPlatform $platform) : bool
//    {
//        global $DIC;
//        $ilDB = $DIC->database(); // TODO PHP8 Review: Move Global Access to Constructor
//
//        $ok = false;
//        $query = 'SELECT consumer_pk, name, consumer_key256, consumer_key, secret, lti_version, ' .
//            'consumer_name, consumer_version, consumer_guid, ' .
//            'profile, tool_proxy, settings, protected, enabled, ' .
//            'enable_from, enable_until, last_access, created, updated, ' .
//            'ext_consumer_id, ref_id ' .
//            #'title, description, prefix, user_language, role, local_role_always_member, default_skin ' .
//            'FROM lti2_consumer ' .
//            #'FROM lti2_consumer, lti_ext_consumer ' .
//            'WHERE ';
//        #'WHERE lti_ext_consumer.id = consumer_pk AND ';
//        if (!empty($platform->getRecordId())) {
//            $query .= 'consumer_pk = %s';
//            $types = array('integer');
//            $values = array($platform->getRecordId());
//        } else {
//            $query .= 'consumer_key256 = %s';
//            $types = array('text');
//            $key256 = ilLTIDataConnector::getConsumerKey($platform->getKey());
//            $values = array($key256);
//        }
//        // $rsConsumer = mysql_query($sql);
//        $res = $ilDB->queryF($query, $types, $values);
//        // if ($rsConsumer) {
//        while ($row = $ilDB->fetchObject($res)) {
//            // while ($row = mysql_fetch_object($rsConsumer)) {
//            if (empty($key256) || empty($row->consumer_key) || ($platform->getKey() === $row->consumer_key)) {
//                $platform->setRecordId(intval($row->consumer_pk));
//                $platform->name = $row->name;
//                $platform->setkey(empty($row->consumer_key) ? $row->consumer_key256 : $row->consumer_key);
//                $platform->secret = $row->secret;
//                $platform->ltiVersion = $row->lti_version;
//                $platform->consumerName = $row->consumer_name;
//                $platform->consumerVersion = $row->consumer_version;
//                $platform->consumerGuid = $row->consumer_guid;
//                $platform->profile = json_decode((string) $row->profile); // TODO PHP8 Review: Undefined Property
//                $platform->toolProxy = $row->tool_proxy; // TODO PHP8 Review: Undefined Property
//                $settings = unserialize($row->settings);
//                if (!is_array($settings)) {
//                    $settings = array();
//                }
//                $platform->setSettings($settings);
//                $platform->protected = (intval($row->protected) === 1);
//                $platform->enabled = (intval($row->enabled) === 1);
//                $platform->enableFrom = null;
//                if (!is_null($row->enable_from)) {
//                    $platform->enableFrom = strtotime($row->enable_from);
//                }
//                $platform->enableUntil = null;
//                if (!is_null($row->enable_until)) {
//                    $platform->enableUntil = strtotime($row->enable_until);
//                }
//                $platform->lastAccess = null;
//                if (!is_null($row->last_access)) {
//                    $platform->lastAccess = strtotime($row->last_access);
//                }
//                $platform->created = strtotime($row->created);
//                $platform->updated = strtotime($row->updated);
//
//                //ILIAS specific
//                $platform->setExtConsumerId((int) $row->ext_consumer_id);
//                $platform->setRefId((int) $row->ref_id);
//                #$platform->setTitle($row->title);
//                #$platform->setDescription($row->description);
//                #$platform->setPrefix($row->prefix);
//                #$platform->setLanguage($row->user_language);
//                #$platform->setRole($row->role);
//                // local_role_always_member
//                // default_skin
//
//                $ok = true;
//                break;
//            }
//            // }
//            // mysql_free_result($rsConsumer);
//        }
//
//        $this->loadGlobalToolConsumerSettings($platform);
//        return $ok;
//    }

    /**
     * Lookup record id for global settings and ref_id
     * @param ilLTIPlatform $platform
     * @return int|null
     */
    public function lookupRecordIdByGlobalSettingsAndRefId(ilLTIPlatform $platform) : ?int
    {
        $db = $this->database;

        $query = 'SELECT consumer_pk from lti2_consumer ' .
            'WHERE ext_consumer_id = ' . $db->quote($platform->getExtConsumerId(), 'integer') . ' ' .
            'AND ref_id = ' . $db->quote($platform->getRefId(), 'integer');
        $res = $db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->consumer_pk;
        }
        return null;
    }

//    /**
//     * Save platform object.
//     * @param Platform $platform Consumer object
//     * @return bool True if the tool consumer object was successfully saved
//     */
//    public function saveToolConsumer(\ILIAS\LTI\ToolProvider\Platform $platform) : bool
//    {
//        global $DIC;
//        $ilDB = $DIC->database(); // TODO PHP8 Review: Move Global Access to Constructor
//
//        $id = $platform->getRecordId();
//        $key = $platform->getKey();
//        $key256 = ToolProvider\DataConnector\DataConnector::getConsumerKey($key);
//        // $key256 = $this->getConsumerKey($key);
//        if ($key === $key256) {
//            $key = null;
//        }
//        $protected = ($platform->protected) ? 1 : 0;
//        $enabled = ($platform->enabled) ? 1 : 0;
//        $profile = (!empty($platform->profile)) ? json_encode($platform->profile) : null;
//        $settingsValue = serialize($platform->getSettings());
//        $time = time();
//        $now = date("{$this->dateFormat} {$this->timeFormat}", $time);
//        $from = null;
//        if (!is_null($platform->enableFrom)) {
//            $from = date("{$this->dateFormat} {$this->timeFormat}", $platform->enableFrom);
//        }
//        $until = null;
//        if (!is_null($platform->enableUntil)) {
//            $until = date("{$this->dateFormat} {$this->timeFormat}", $platform->enableUntil);
//        }
//        $last = null;
//        if (!is_null($platform->lastAccess)) {
//            $last = date($this->dateFormat, $platform->lastAccess);
//        }
//
//        if (empty($id)) {
//            $platform->setRecordId($ilDB->nextId('lti_ext_consumer'));
//            $id = $platform->getRecordId();
//            $platform->created = $time;
//            $platform->updated = $time;
//            if ($key256 == null) {
//                $key256 = $id . ToolProvider\DataConnector\DataConnector::getRandomString(10);
//            }
//
//            // $query = "INSERT INTO {$this->dbTableNamePrefix}" . $this->CONSUMER_TABLE_NAME . ' (consumer_key256, consumer_key, name, ' .
//            $query = 'INSERT INTO lti2_consumer (consumer_key256, consumer_key, name, ' .
//                'secret, lti_version, consumer_name, consumer_version, consumer_guid, profile, tool_proxy, settings, protected, enabled, ' .
//                'enable_from, enable_until, last_access, created, updated, consumer_pk) ' .
//                'VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)';
//            $types = array("text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "integer",
//                           "integer",
//                           "timestamp",
//                           "timestamp",
//                           "timestamp",
//                           "timestamp",
//                           "timestamp",
//                           "integer"
//            );
//            $values = array($key256,
//                            $key,
//                            $platform->name,
//                            $platform->secret,
//                            $platform->ltiVersion,
//                            $platform->consumerName,
//                            $platform->consumerVersion,
//                            $platform->consumerGuid,
//                            $profile,
//                            $platform->toolProxy, // TODO PHP8 Review: Undefined Property
//                            $settingsValue,
//                            $protected,
//                            $enabled,
//                            $from,
//                            $until,
//                            $last,
//                            $now,
//                            $now,
//                            $id
//            );
//            $ilDB->manipulateF($query, $types, $values);
//        } else {
//            $platform->updated = $time;
//
//            $query = 'UPDATE lti2_consumer SET ' .
//                'consumer_key256 = %s, consumer_key = %s, name = %s, ' .
//                'secret= %s, lti_version = %s, consumer_name = %s, consumer_version = %s, consumer_guid = %s, ' .
//                'profile = %s, tool_proxy = %s, settings = %s, protected = %s, enabled = %s, ' .
//                'enable_from = %s, enable_until = %s, last_access = %s, updated = %s ' .
//                'WHERE consumer_pk = %s';
//            $types = array("text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "text",
//                           "integer",
//                           "integer",
//                           "timestamp",
//                           "timestamp",
//                           "timestamp",
//                           "timestamp",
//                           "integer"
//            );
//            $values = array($key256,
//                            $key,
//                            $platform->name,
//                            $platform->secret,
//                            $platform->ltiVersion,
//                            $platform->consumerName,
//                            $platform->consumerVersion,
//                            $platform->consumerGuid,
//                            $profile,
//                            $platform->toolProxy, // TODO PHP8 Review: Undefined Property
//                            $settingsValue,
//                            $protected,
//                            $enabled,
//                            $from,
//                            $until,
//                            $last,
//                            $now,
//                            $id
//            );
//            $ilDB->manipulateF($query, $types, $values);
//        }
//        return true;
//    }

    /**
     * Save lti_ext_consumer
     */
    public function saveGlobalToolConsumerSettings(ilLTIPlatform $platform) : bool
    {
        $ilDB = $this->database;

        if (!$platform->getExtConsumerId()) {
            // create
            $new_id = $ilDB->nextId('lti_ext_consumer');
            $query = 'INSERT INTO lti_ext_consumer (title, description, prefix, user_language, role, id, active) ' .
                'VALUES (%s, %s, %s, %s, %s, %s, %s)';
            $types = ["text", "text", "text", "text", "integer", "integer", 'integer'];
            $values = [
                $platform->getTitle(),
                $platform->getDescription(),
                $platform->getPrefix(),
                $platform->getLanguage(),
                $platform->getRole(),
                $new_id,
                $platform->getActive()
            ];
            $ilDB->manipulateF($query, $types, $values);
            $platform->setExtConsumerId($new_id);
            return true;
        } else {
            // update
            $query = 'update lti_ext_consumer set ' .
                'title  = ' . $ilDB->quote($platform->getTitle(), 'text') . ', ' .
                'description = ' . $ilDB->quote($platform->getDescription(), 'text') . ', ' .
                'prefix = ' . $ilDB->quote($platform->getPrefix(), 'text') . ', ' .
                'user_language = ' . $ilDB->quote($platform->getLanguage(), 'text') . ', ' .
                'role = ' . $ilDB->quote($platform->getRole(), 'integer') . ', ' .
                'active = ' . $ilDB->quote((int) $platform->getActive(), 'integer') . ' ' .
                'where id = ' . $ilDB->quote($platform->getExtConsumerId(), 'integer');
            $ilDB->manipulate($query);
            return true;
        }
    }

    /**
     * Save extended tool consumer object with ILIAS extensions.
     * @param ilLTIPlatform $platform Consumer object
     * @return boolean True if the tool consumer object was successfully saved
     */
    public function saveToolConsumerILIAS(ilLTIPlatform $platform) : bool
    {
        $ilDB = $this->database;

        $id = $platform->getRecordId();
        $key = $platform->getKey();
        $protected = ($platform->protected) ? 1 : 0;
        $enabled = ($platform->enabled) ? 1 : 0;
        $profile = (!empty($platform->profile)) ? json_encode($platform->profile) : null;
//        $settingsValue = '{}';
        $this->fixPlatformSettings($platform, true);
        $settingsValue = json_encode($platform->getSettings());
        $this->fixPlatformSettings($platform, false);
        $time = time();
        $now = date("{$this->dateFormat} {$this->timeFormat}", $time);
        $from = null;
        if (!is_null($platform->enableFrom)) {
            $from = date("{$this->dateFormat} {$this->timeFormat}", $platform->enableFrom);
        }
        $until = null;
        if (!is_null($platform->enableUntil)) {
            $until = date("{$this->dateFormat} {$this->timeFormat}", $platform->enableUntil);
        }
        $last = null;
        if (!is_null($platform->lastAccess)) {
            $last = date($this->dateFormat, $platform->lastAccess);
        }

        $platform->name = $platform->getTitle();//50UK
        if (empty($id)) {
            $platform->setRecordId($ilDB->nextId('lti2_consumer'));
            $id = $platform->getRecordId();
            $platform->created = $time;
            $platform->updated = $time;

            // $query = "INSERT INTO {$this->dbTableNamePrefix}" . $this->CONSUMER_TABLE_NAME . ' (consumer_key256, consumer_key, name, ' .
            $query = 'INSERT INTO lti2_consumer (consumer_key, name, ' .
                'secret, lti_version, consumer_name, consumer_version, consumer_guid, profile, tool_proxy, settings, protected, enabled, ' .
                'enable_from, enable_until, last_access, created, updated, consumer_pk, ext_consumer_id, ref_id, platform_id, client_id, deployment_id, public_key) ' .
                'VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)';
            $types = array("text",
                           "text",
                           "text",
                           "text",
                           "text",
                           "text",
                           "text",
                           "text",
                           "text",
                           "text",
                           "integer",
                           "integer",
                           "timestamp",
                           "timestamp",
                           "timestamp",
                           "timestamp",
                           "timestamp",
                           "integer",
                           'integer',
                           'integer',
                           "text",
                           "text",
                           "text",
                           "text"
            );
            $values = array($key,
                            $platform->name,
                            $platform->secret,
                            $platform->ltiVersion,
                            $platform->consumerName,
                            $platform->consumerVersion,
                            $platform->consumerGuid,
                            $profile,
                            $platform->toolProxy,
                            $settingsValue,
                            $protected,
                            $enabled,
                            $from,
                            $until,
                            $last,
                            $now,
                            $now,
                            $id,
                            $platform->getExtConsumerId(),
                            $platform->getRefId(),
                            (string) $platform->platformId,
                            $platform->clientId,
                            $platform->deploymentId,
                            $platform->rsaKey
            );
            $ilDB->manipulateF($query, $types, $values);
        } else {
            $platform->updated = $time;

            $query = 'UPDATE lti2_consumer SET ' .
                'consumer_key = %s, name = %s, ' .
                'secret= %s, lti_version = %s, consumer_name = %s, consumer_version = %s, consumer_guid = %s, ' .
                'profile = %s, tool_proxy = %s, settings = %s, protected = %s, enabled = %s, ' .
                'enable_from = %s, enable_until = %s, last_access = %s, updated = %s, ' .
                'platform_id = %s, client_id = %s, deployment_id = %s, public_key = %s ' .
                'WHERE consumer_pk = %s';
            $types = array("text",
                           "text",
                           "text",
                           "text",
                           "text",
                           "text",
                           "text",
                           "text",
                           "text",
                           "text",
                           "integer",
                           "integer",
                           "timestamp",
                           "timestamp",
                           "timestamp",
                           "timestamp",
                           "text",
                           "text",
                           "text",
                           "text",
                           "integer"
            );
            $values = array($key,
                            $platform->name,
                            $platform->secret,
                            $platform->ltiVersion,
                            $platform->consumerName,
                            $platform->consumerVersion,
                            $platform->consumerGuid,
                            $profile,
                            $platform->toolProxy,
                            $settingsValue,
                            $protected,
                            $enabled,
                            $from,
                            $until,
                            $last,
                            $now,
                            $platform->platformId,
                            $platform->clientId,
                            $platform->deploymentId,
                            $platform->rsaKey,
                            $id
            );
            $ilDB->manipulateF($query, $types, $values);
        }

        return true;
    }

    /**
     *  Delete global tool consumer settings
     */
    public function deleteGlobalToolConsumerSettings(ilLTIPlatform $platform) : bool
    {
        $ilDB = $this->database;

        $query = 'DELETE FROM lti_ext_consumer WHERE id = %s';
        $types = array("integer");
        $values = array($platform->getExtConsumerId());
        $ilDB->manipulateF($query, $types, $values);

        $query = 'DELETE FROM lti_ext_consumer_otype WHERE consumer_id = %s';
        $types = array("integer");
        $values = array($platform->getExtConsumerId());
        $ilDB->manipulateF($query, $types, $values);

        // delete all assigned lti consumers
        $platform->initialize();
        return true;
    }

    /**
     * Delete tool consumer object.
     * @param Platform $platform Consumer object
     * @return boolean True if the tool consumer object was successfully deleted
     */
    public function deleteToolConsumer(\ILIAS\LTI\ToolProvider\Platform $platform) : bool
    {
        $ilDB = $this->database;

        // Delete any nonce values for this consumer
        $query = "DELETE FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::NONCE_TABLE_NAME . ' WHERE consumer_pk = %s';
        $types = array("integer");
        $values = array($platform->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any outstanding share keys for resource links for this consumer
        $query = 'DELETE sk ' .
            "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' sk ' .
            "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON sk.resource_link_pk = rl.resource_link_pk ' .
            'WHERE rl.consumer_pk = %s';
        $types = array("integer");
        $values = array($platform->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any outstanding share keys for resource links for contexts in this consumer
        $query = 'DELETE sk ' .
            "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' sk ' .
            "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON sk.resource_link_pk = rl.resource_link_pk ' .
            "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ON rl.context_pk = c.context_pk ' .
            'WHERE c.consumer_pk = %s';
        $types = array("integer");
        $values = array($platform->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any users in resource links for this consumer
        $query = 'DELETE u ' .
            "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' u ' .
            "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON u.resource_link_pk = rl.resource_link_pk ' .
            'WHERE rl.consumer_pk = %s';
        $types = array("integer");
        $values = array($platform->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any users in resource links for contexts in this consumer
        $query = 'DELETE u ' .
            "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' u ' .
            "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON u.resource_link_pk = rl.resource_link_pk ' .
            "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ON rl.context_pk = c.context_pk ' .
            'WHERE c.consumer_pk = %s';
        $types = array("integer");
        $values = array($platform->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Update any resource links for which this consumer is acting as a primary resource link
        $query = "UPDATE {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' prl ' .
            "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON prl.primary_resource_link_pk = rl.resource_link_pk ' .
            'SET prl.primary_resource_link_pk = NULL, prl.share_approved = NULL ' .
            'WHERE rl.consumer_pk = %s';
        $types = array("integer");
        $values = array($platform->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Update any resource links for contexts in which this consumer is acting as a primary resource link
        $query = "UPDATE {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' prl ' .
            "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ON prl.primary_resource_link_pk = rl.resource_link_pk ' .
            "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ON rl.context_pk = c.context_pk ' .
            'SET prl.primary_resource_link_pk = NULL, prl.share_approved = NULL ' .
            'WHERE c.consumer_pk = %s';
        $types = array("integer");
        $values = array($platform->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any resource links for this consumer
        $query = 'DELETE rl ' .
            "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ' .
            'WHERE rl.consumer_pk = %s';
        $types = array("integer");
        $values = array($platform->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any resource links for contexts in this consumer
        $query = 'DELETE rl ' .
            "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' rl ' .
            "INNER JOIN {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ON rl.context_pk = c.context_pk ' .
            'WHERE c.consumer_pk = %s';
        $types = array("integer");
        $values = array($platform->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete any contexts for this consumer
        $query = 'DELETE c ' .
            "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ' .
            'WHERE c.consumer_pk = %s';
        $types = array("integer");
        $values = array($platform->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

        // Delete consumer
        $query = 'DELETE c ' .
            "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONSUMER_TABLE_NAME . ' c ' .
            'WHERE c.consumer_pk = %s';
        $types = array("integer");
        $values = array($platform->getRecordId());
        $ilDB->manipulateF($query, $types, $values);

//        if ($ok) {
        $platform->initialize();
//        }

        return true;
    }

    /**
     * Get global consumer settings
     * @return \ilLTIPlatform[]
     */
    public function getGlobalToolConsumerSettings() : array
    {
        $ilDB = $this->database;

        $platforms = array();
        $query = 'SELECT * from lti_ext_consumer ';
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $platform = new ilLTIPlatform(null, $this);
            $platform->setExtConsumerId((int) $row->id);
            $platform->setTitle($row->title);
            $platform->setDescription($row->description);
            $platform->setPrefix($row->prefix);
            $platform->setLanguage($row->user_language);
            $platform->setRole((int) $row->role);
            $platform->setActive((bool) $row->active);
            $platforms[] = $platform;
        }
        return $platforms;
    }



    ###
    #    Load all tool consumers from the database
    ###
    /**
     * @return \ilLTIPlatform[]
     */
    public function getToolConsumers() : array
    {
        $ilDB = $this->database;
        $platforms = array();
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
        // "FROM {$this->dbTableNamePrefix}" . Tool\DataConnector\DataConnector::CONSUMER_TABLE_NAME . ' ' .
        // 'ORDER BY name';
        // $rsConsumers = mysql_query($sql);
        // if ($rsConsumers) {
        // while ($row = mysql_fetch_object($rsConsumers)) {
        $res = $ilDB->query($query);
        // if ($rsConsumer) {
        while ($row = $ilDB->fetchObject($res)) {
            // $platform = new Tool\Platform($row->consumer_key, $this); //ACHTUNG: FEHLER IN BIBLIOTHEK; $row->consumer_key ist i.d.R. null
            $platform = new ilLTIPlatform(null, $this);
            $platform->setRecordId(intval($row->consumer_pk));
            $platform->name = $row->name;
            $platform->secret = $row->secret;
            $platform->ltiVersion = $row->lti_version;
            $platform->consumerName = $row->consumer_name;
            $platform->consumerVersion = $row->consumer_version;
            $platform->consumerGuid = $row->consumer_guid;
            $platform->profile = json_decode($row->profile);
            $platform->toolProxy = $row->tool_proxy;
            $settings = unserialize($row->settings);
            if (!is_array($settings)) {
                $settings = array();
            }
            $platform->setSettings($settings);
            $platform->protected = (intval($row->protected) === 1);
            $platform->enabled = (intval($row->enabled) === 1);
            $platform->enableFrom = null;
            if (!is_null($row->enable_from)) {
                $platform->enableFrom = strtotime($row->enable_from);
            }
            $platform->enableUntil = null;
            if (!is_null($row->enable_until)) {
                $platform->enableUntil = strtotime($row->enable_until);
            }
            $platform->lastAccess = null;
            if (!is_null($row->last_access)) {
                $platform->lastAccess = strtotime($row->last_access);
            }
            $platform->created = strtotime($row->created);
            $platform->updated = strtotime($row->updated);
            //ILIAS specific
            $platform->setTitle($row->title);
            $platform->setDescription($row->description);
            $platform->setPrefix($row->prefix);
            $platform->setLanguage($row->user_language);
            $platform->setRole($row->role);
            // local_role_always_member
            // default_skin
            $platform->setKey($row->consumer_key256);//ACHTUNG: hier müsste evtl. consumer_key sein
            $platforms[] = $platform;
        }
        // mysql_free_result($rsConsumers);
        // }

        return $platforms;
    }
    ###
    ###  ToolProxy methods
    ###

//    ###
//    #    Load the tool proxy from the database
//    ###
//    public function loadToolProxy($toolProxy) : bool
//    {
//        return false;
//    }
//
//    ###
//    #    Save the tool proxy to the database
//    ###
//    public function saveToolProxy($toolProxy) : bool
//    {
//        return false;
//    }
//
//    ###
//    #    Delete the tool proxy from the database
//    ###
//    public function deleteToolProxy($toolProxy) : bool
//    {
//        return false;
//    }

    ###
    ###  Context methods
    ###

    /**
     * Load context object.
     * @param Context $context Context object
     * @return boolean True if the context object was successfully loaded
     */
    public function loadContext(Context $context) : bool
    {
        $ilDB = $this->database;

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
            $types = array("integer", "text");
            $values = array($context->getRecordId(), $context->ltiContextId);
        }
        $rs_context = $ilDB->queryF($query, $types, $values);
        if ($rs_context) {
            $row = $ilDB->fetchObject($rs_context);
            if ($row) {
                $context->setRecordId(intval($row->context_pk));
                $context->setPlatformId(intval($row->consumer_pk));
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
     * @param Context $context Context object
     * @return boolean True if the context object was successfully saved
     */
    public function saveContext(Context $context) : bool
    {
        $ilDB = $this->database;

        $time = time();
        $now = date("{$this->dateFormat} {$this->timeFormat}", $time);
        //old: $settingsValue = serialize($context->getSettings());
        $settingsValue = json_encode($context->getSettings());
        $id = $context->getRecordId();
        $platform_pk = $context->getPlatform()->getRecordId();
        if (empty($id)) {
            $context->setRecordId($ilDB->nextId(ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME));
            $id = $context->getRecordId();
            $context->created = $time;
            //Check remove context_pk, add type
            $query = "INSERT INTO {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME .
                ' (context_pk, consumer_pk, lti_context_id, settings, created, updated) ' .
                'VALUES (%s, %s, %s, %s, %s, %s)';
            $types = array("integer", "integer", "text", "text", "timestamp", "timestamp");
            $values = array($id, $platform_pk, $context->ltiContextId, $settingsValue, $now, $now);
        } else {
            $query = "UPDATE {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' SET ' .
                'lti_context_id = %s, settings = %s, ' .
                'updated = %s' .
                'WHERE (consumer_pk = %s) AND (context_pk = %s)';
            $types = array("text", "text", "timestamp", "integer", "integer");
            $values = array($context->ltiContextId, $settingsValue, $now, $platform_pk, $id);
        }
        $ok = (bool) $ilDB->manipulateF($query, $types, $values);
        if ($ok) {
            $context->updated = $time;
        }

        return $ok;
    }

    /**
     * Delete context object.
     * @param Context $context Context object
     * @return boolean True if the Context object was successfully deleted
     */
    public function deleteContext(Context $context) : bool
    {
        $ilDB = $this->database;

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
        $ok = (bool) $ilDB->manipulateF($query, $types, $values);
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
     * @param ResourceLink $resourceLink Resource_Link object
     * @return boolean True if the resource link object was successfully loaded
     */
    public function loadResourceLink(ResourceLink $resourceLink) : bool
    {
        $ilDB = $this->database;

        $ok = false;
        $id = $resourceLink->getRecordId();
        if (!is_null($id)) {
            $query = 'SELECT resource_link_pk, context_pk, consumer_pk, lti_resource_link_id, settings, primary_resource_link_pk, share_approved, created, updated ' .
                "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' ' .
                'WHERE (resource_link_pk = %s)';
            $types = array("integer");
            $values = array($id);
        } elseif (!empty($resourceLink->getContext())) {
            $rid = $resourceLink->getId();
            $cid = $resourceLink->getContext()->getRecordId();
            $query = 'SELECT resource_link_pk, context_pk, consumer_pk, lti_resource_link_id, settings, primary_resource_link_pk, share_approved, created, updated ' .
                "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' r ' .
                'WHERE (r.lti_resource_link_id = %s) AND ((r.context_pk = %s) OR (r.consumer_pk IN (' .
                'SELECT c.consumer_pk ' .
                "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ' .
                'WHERE (c.context_pk = %s))))';
            $types = array("text", "integer", "integer");
            $values = array($rid, $cid, $cid);
        } else {
            $id = $resourceLink->getPlatform()->getRecordId();
            $rid = $resourceLink->getId();
            $query = 'SELECT r.resource_link_pk, r.context_pk, r.consumer_pk, r.lti_resource_link_id, r.settings, r.primary_resource_link_pk, r.share_approved, r.created, r.updated ' .
                "FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' r LEFT OUTER JOIN ' .
                $this->dbTableNamePrefix . ToolProvider\DataConnector\DataConnector::CONTEXT_TABLE_NAME . ' c ON r.context_pk = c.context_pk ' .
                ' WHERE ((r.consumer_pk = %s) OR (c.consumer_pk = %s)) AND (lti_resource_link_id = %s)';
            $types = array("integer", "integer", "text");
            $values = array($id, $id, $rid);
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
                    $resourceLink->setPlatformId(intval($row->consumer_pk));
                } else {
                    $resourceLink->setPlatformId(null);
                }
                $resourceLink->title = $row->title;
                $resourceLink->ltiResourceLinkId = $row->lti_resource_link_id;
                $settings = json_decode($row->settings, true);
                if (!is_array($settings)) {
                    $settings = @unserialize($row->settings);  // check for old serialized setting
                }
                if (!is_array($settings)) {
                    $settings = array();
                }
                $resourceLink->setSettings($settings);
                if (!is_null($row->primary_resource_link_pk)) {
//                    $resourceLink->primaryResourceLinkId = intval($row->primary_resource_link_pk); //UK Check
                    $resourceLink->primaryResourceLinkId = (string) ($row->primary_resource_link_pk);
                } else {
                    $resourceLink->primaryResourceLinkId = null;
                }
                $resourceLink->shareApproved = (is_null($row->share_approved)) ? null : (intval($row->share_approved) === 1);
                $resourceLink->created = strtotime($row->created);
                $resourceLink->updated = strtotime($row->updated);
            } else {
                $ok = false;
            }
        }


        return $ok;
    }

    /**
     * Save resource link object.
     * @param ResourceLink $resourceLink Resource_Link object
     * @return boolean True if the resource link object was successfully saved
     */
    public function saveResourceLink(ResourceLink $resourceLink) : bool
    {
        $ilDB = $this->database;

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
            //$platformId = 'NULL';
            $platformId = strval($resourceLink->getContext()->getRecordId());
            $contextId = strval($resourceLink->getContext()->getRecordId());
        } elseif (!empty($resourceLink->getContextId())) {
            //$platformId = 'NULL';
            $platformId = strval($resourceLink->getContext()->getRecordId());
            $contextId = strval($resourceLink->getContextId());
        } else {
            $platformId = strval($resourceLink->getContext()->getRecordId());
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
            $types = array("integer",
                           "integer",
                           "integer",
                           "text",
                           "text",
                           "integer",
                           "integer",
                           "timestamp",
                           "timestamp"
            );
            $values = array($id,
                            $platformId,
                            $contextId,
                            $resourceLink->getId(),
                            $settingsValue,
                            $primaryResourceLinkId,
                            $approved,
                            $now,
                            $now
            );
        } elseif ($contextId !== 'NULL') {
            $query = "UPDATE {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' SET ' .
                'consumer_pk = %s, lti_resource_link_id = %s, settings = %s, ' .
                'primary_resource_link_pk = %s, share_approved = %s, updated = %s ' .
                'WHERE (context_pk = %s) AND (resource_link_pk = %s)';
            $types = array("integer", "text", "text", "integer", "integer", "timestamp", "integer", "integer");
            $values = array($platformId,
                            $resourceLink->getId(),
                            $settingsValue,
                            $primaryResourceLinkId,
                            $approved,
                            $now,
                            $contextId,
                            $id
            );
        } else {
            $query = "UPDATE {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' SET ' .
                'context_pk = %s, lti_resource_link_id = %s, settings = %s, ' .
                'primary_resource_link_pk = %s, share_approved = %s, updated = %s ' .
                'WHERE (consumer_pk = %s) AND (resource_link_pk = %s)';
            $types = array("integer", "text", "text", "integer", "integer", "timestamp", "integer", "integer");
            $values = array($contextId,
                            $resourceLink->getId(),
                            $settingsValue,
                            $primaryResourceLinkId,
                            $approved,
                            $now,
                            $platformId,
                            $id
            );
        }
        $ok = (bool) $ilDB->manipulateF($query, $types, $values);

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
     * @param ResourceLink $resourceLink Resource_Link object
     * @return boolean True if the resource link object was successfully deleted
     */
    public function deleteResourceLink(ResourceLink $resourceLink) : bool
    {
        $ilDB = $this->database;

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
            $ok = (bool) $ilDB->manipulateF($query, $types, $values);
        }

        if ($ok) {
            $resourceLink->initialize();
        }

        return $ok;
    }

    /**
     * Get array of user objects.
     * Obtain an array of User objects for users with a result sourcedId.  The array may include users from other
     * resource links which are sharing this resource link.  It may also be optionally indexed by the user ID of a specified scope.
     * @param ResourceLink $resourceLink Resource link object
     * @param boolean      $localOnly    True if only users within the resource link are to be returned (excluding users sharing this resource link)
     * @param int          $idScope      Scope value to use for user IDs
     * @return array Array of User objects
     */
    public function getUserResultSourcedIDsResourceLink(
        ResourceLink $resourceLink,
        bool $localOnly,
        int $idScope
    ) : array {
        $ilDB = $this->database;

        $users = array();

        // if ($localOnly) {
        // $query = 'SELECT u.user_pk, u.lti_result_sourcedid, u.lti_user_id, u.created, u.updated ' .
        // "FROM {$this->dbTableNamePrefix}" . Tool\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' AS u '  .
        // "INNER JOIN {$this->dbTableNamePrefix}" . Tool\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' AS rl '  .
        // 'ON u.resource_link_pk = rl.resource_link_pk ' .
        // "WHERE (rl.resource_link_pk = %d) AND (rl.primary_resource_link_pk IS NULL)",
        // $resourceLink->getRecordId());
        // } else {
        // $query = 'SELECT u.user_pk, u.lti_result_sourcedid, u.lti_user_id, u.created, u.updated ' .
        // "FROM {$this->dbTableNamePrefix}" . Tool\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' AS u '  .
        // "INNER JOIN {$this->dbTableNamePrefix}" . Tool\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' AS rl '  .
        // 'ON u.resource_link_pk = rl.resource_link_pk ' .
        // 'WHERE ((rl.resource_link_pk = %d) AND (rl.primary_resource_link_pk IS NULL)) OR ' .
        // '((rl.primary_resource_link_pk = %d) AND (share_approved = 1))',
        // $resourceLink->getRecordId(), $resourceLink->getRecordId());
        // }
        // $rsUser = mysql_query($sql);
        // if ($rsUser) {
        // while ($row = $ilDB->fetchObject($rsUser)) {
        // $user = Tool\User::fromResourceLink($resourceLink, $row->lti_user_id);
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

//    /**
//     * Get array of shares defined for this resource link.
//     * @param ResourceLink $resourceLink Resource_Link object
//     * @return array Array of ResourceLinkShare objects
//     */
//    public function getSharesResourceLink(\ILIAS\LTI\Tool\ResourceLink $resourceLink) : array
//    {
//        global $DIC;
//        $ilDB = $DIC->database();
//
//        $shares = array();
//
//        $query = 'SELECT consumer_pk, resource_link_pk, share_approved ' .
//            "FROM {$this->dbTableNamePrefix}" . Tool\DataConnector\DataConnector::RESOURCE_LINK_TABLE_NAME . ' ' .
//            'WHERE (primary_resource_link_pk = %s) ' .
//            'ORDER BY consumer_pk';
//        $types = array("integer");
//        $values = array($resourceLink->getRecordId());
//        $rsShare = $ilDB->queryF($query, $types, $values);
//        if ($rsShare) {
//            while ($row = $ilDB->fetchObject($rsShare)) {
//                $share = new Tool\ResourceLinkShare();
//                $share->resourceLinkId = intval($row->resource_link_pk);
//                $share->approved = (intval($row->share_approved) === 1);
//                $shares[] = $share;
//            }
//        }
//
//        return $shares;
//    }


    ###
    ###  PlatformNonce methods
    ###

    /**
     * Load nonce object.
     * @param PlatformNonce $nonce Nonce object
     * @return boolean True if the nonce object was successfully loaded
     */
    public function loadPlatformNonce(\ILIAS\LTI\ToolProvider\PlatformNonce $nonce) : bool
    {
        $ilDB = $this->database;

        $ok = true;

        // Delete any expired nonce values
        $now = date("{$this->dateFormat} {$this->timeFormat}", time());//PRÜFEN UK
        $query = "DELETE FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::NONCE_TABLE_NAME . " WHERE expires <= %s";
        $types = array("timestamp");
        $values = array($now);
        $ilDB->manipulateF($query, $types, $values);
        // Load the nonce
        $query = "SELECT value AS T FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::NONCE_TABLE_NAME . ' WHERE (consumer_pk = %s) AND (value = %s)';
        $types = array("integer", "text");
        $values = array($nonce->getPlatform()->getRecordId(), $nonce->getValue());
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
     * @param PlatformNonce $nonce Nonce object
     * @return boolean True if the nonce object was successfully saved
     */
    public function savePlatformNonce(\ILIAS\LTI\ToolProvider\PlatformNonce $nonce) : bool
    {
        $ilDB = $this->database;

        $expires = date("{$this->dateFormat} {$this->timeFormat}", $nonce->expires);
        $query = "INSERT INTO {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::NONCE_TABLE_NAME . " (consumer_pk, value, expires) VALUES (%s, %s, %s)";
        $types = array("integer", "text", "timestamp");
        $values = array($nonce->getPlatform()->getRecordId(), $nonce->getValue(), $expires);
        $ok = (bool) $ilDB->manipulateF($query, $types, $values);

        return $ok;
    }


    ###
    ###  ResourceLinkShareKey methods
    ###

    /**
     * Load resource link share key object.
     * @param ResourceLinkShareKey $shareKey Resource_Link share key object
     * @return boolean True if the resource link share key object was successfully loaded
     */
//    public function loadResourceLinkShareKey(\ILIAS\LTI\Tool\ResourceLinkShareKey $shareKey) : bool
    public function loadResourceLinkShareKey(ResourceLinkShareKey $shareKey) : bool
    {
        $ilDB = $this->database;

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
     * @param ResourceLinkShareKey $shareKey Resource link share key object
     * @return boolean True if the resource link share key object was successfully saved
     */
//    public function saveResourceLinkShareKey(\ILIAS\LTI\Tool\ResourceLinkShareKey $shareKey) : bool
    public function saveResourceLinkShareKey(ResourceLinkShareKey $shareKey) : bool
    {
        $ilDB = $this->database;

        if ($shareKey->autoApprove) {
            $approve = 1;
        } else {
            $approve = 0;
        }
        $expires = date("{$this->dateFormat} {$this->timeFormat}", $shareKey->expires);
        $query = "INSERT INTO {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' ' .
            '(share_key_id, resource_link_pk, auto_approve, expires) ' .
            "VALUES (%s, %s, %s, %s)";
        $types = array("text", "integer", "integer", "timestamp");
        $values = array($shareKey->getId(), $shareKey->resourceLinkId, $approve, $expires);
        $ok = (bool) $ilDB->manipulateF($query, $types, $values);

        return $ok;
    }

    /**
     * Delete resource link share key object.
     * @param ResourceLinkShareKey $shareKey Resource link share key object
     * @return boolean True if the resource link share key object was successfully deleted
     */
    public function deleteResourceLinkShareKey(ResourceLinkShareKey $shareKey) : bool
    {
        $ilDB = $this->database;

        $query = "DELETE FROM {$this->dbTableNamePrefix}" . ToolProvider\DataConnector\DataConnector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . " WHERE share_key_id = %s";
        $types = array("text");
        $values = array($shareKey->getId());
        $ok = (bool) $ilDB->manipulateF($query, $types, $values);

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
     * @param User $user User object
     * @return boolean True if the user object was successfully loaded
     */
    public function loadUser(\ILIAS\LTI\ToolProvider\User $user) : bool
    {
        $ilDB = $this->database;

        $ok = false;
        if ($user->getRecordId()) {
            $query = 'SELECT user_pk, resource_link_pk, lti_user_id, lti_result_sourcedid, created, updated ' .
                'FROM ' . $this->dbTableNamePrefix . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
                'WHERE user_pk = ' . $ilDB->quote($user->getRecordId(), 'integer');
        } else {
            $query = 'SELECT user_pk, resource_link_pk, lti_user_id, lti_result_sourcedid, created, updated ' .
                'FROM ' . $this->dbTableNamePrefix . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
                'WHERE resource_link_pk = ' . $ilDB->quote($user->getResourceLink()->getRecordId(), 'integer') . ' ' .
                'AND lti_user_id = ' . $ilDB->quote($user->getId(ToolProvider\Tool::ID_SCOPE_ID_ONLY), 'text');
        }

        $this->logger->debug('Loading user with query: ' . $query);

        $ok = false;
        try {
            $res = $ilDB->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $user->setRecordId((int) $row->user_pk);
                $user->setResourceLinkId((int) $row->resource_link_pk);
                $user->ltiUserId = (string) $row->lti_user_id;
                $user->ltiResultSourcedId = (string) $row->lti_result_sourcedid;
                $user->created = strtotime($row->created);
                $user->updated = strtotime($row->updated);
                $ok = true;
            }
        } catch (ilDatabaseException $e) {
            $this->logger->error((string) $e);
        }
        return $ok;

        // if (!empty($user->getRecordId())) {
        // $query = 'SELECT user_pk, resource_link_pk, lti_user_id, lti_result_sourcedid, created, updated ' .
        // "FROM {$this->dbTableNamePrefix}" . Tool\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
        // 'WHERE (user_pk = %d)',
        // $user->getRecordId());
        // } else {
        // $query = 'SELECT user_pk, resource_link_pk, lti_user_id, lti_result_sourcedid, created, updated ' .
        // "FROM {$this->dbTableNamePrefix}" . Tool\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
        // 'WHERE (resource_link_pk = %d) AND (lti_user_id = %s)',
        // $user->getResourceLink()->getRecordId(),
        // Tool\DataConnector\DataConnector::quoted($user->getId(Tool\Tool::ID_SCOPE_ID_ONLY)));
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
     * @param User $user User object
     * @return boolean True if the user object was successfully saved
     */
    public function saveUser(\ILIAS\LTI\ToolProvider\User $user) : bool
    {
        $ilDB = $this->database;

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
                $ilDB->quote($user->getId(ToolProvider\Tool::ID_SCOPE_ID_ONLY), 'text') . ', ' .
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
        // $query = "INSERT INTO {$this->dbTableNamePrefix}" . Tool\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' (resource_link_pk, ' .
        // 'lti_user_id, lti_result_sourcedid, created, updated) ' .
        // 'VALUES (%d, %s, %s, %s, %s)',
        // $user->getResourceLink()->getRecordId(),
        // Tool\DataConnector\DataConnector::quoted($user->getId(Tool\Tool::ID_SCOPE_ID_ONLY)), Tool\DataConnector\DataConnector::quoted($user->ltiResultSourcedId),
        // Tool\DataConnector\DataConnector::quoted($now), Tool\DataConnector\DataConnector::quoted($now));
        // } else {
        // $query = "UPDATE {$this->dbTableNamePrefix}" . Tool\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
        // 'SET lti_result_sourcedid = %s, updated = %s ' .
        // 'WHERE (user_pk = %d)',
        // Tool\DataConnector\DataConnector::quoted($user->ltiResultSourcedId),
        // Tool\DataConnector\DataConnector::quoted($now),
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
     * @param User $user User object
     * @return boolean True if the user object was successfully deleted
     */
    public function deleteUser(\ILIAS\LTI\ToolProvider\User $user) : bool
    {
        $ilDB = $this->database;

        $query = 'DELETE from ' . $this->dbTableNamePrefix . ToolProvider\DataConnector\DataConnector::USER_RESULT_TABLE_NAME . ' ' .
            'WHERE user_pk = ' . $ilDB->quote($user->getRecordId(), 'integer');

        $ok = false;
        try {
            $ilDB->manipulate($query);
            $user->initialize();
            $ok = true;
        } catch (ilDatabaseException $e) {
            $this->logger->error((string) $e);
        }
        return $ok;
    }

    /**
     * Lookup resources for user object relation
     * @param int             $a_ref_id
     * @param string          $a_lti_user
     * @param int             $a_ext_consumer
     * @param ilDateTime|null $since
     * @return int[]
     */
    public function lookupResourcesForUserObjectRelation(
        int $a_ref_id,
        string $a_lti_user,
        int $a_ext_consumer,
        ilDateTime $since = null
    ) : array {
        $db = $this->database;

        $logger = ilLoggerFactory::getLogger('ltis');

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
     * @return array<string, string[]>
     */
    public function lookupResourcesForAllUsersSinceDate(ilDateTime $since) : array
    {
        $db = $this->database;
        $logger = ilLoggerFactory::getLogger('ltis');

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

    public static function getDataConnector(object $db = null, string $dbTableNamePrefix = '', string $type = '') : ilLTIDataConnector
    {
        $dataConnector = new ilLTIDataConnector();
        return $dataConnector;
    }
}
