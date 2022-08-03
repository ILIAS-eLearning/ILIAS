<?php

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

namespace ILIAS\LTI\ToolProvider\DataConnector;

use ILIAS\LTI\ToolProvider\PlatformNonce;
use ILIAS\LTI\ToolProvider\Context;
use ILIAS\LTI\ToolProvider\ResourceLink;
use ILIAS\LTI\ToolProvider\ResourceLinkShare;
use ILIAS\LTI\ToolProvider\ResourceLinkShareKey;
use ILIAS\LTI\ToolProvider\Platform;
use ILIAS\LTI\ToolProvider\UserResult;
use ILIAS\LTI\ToolProvider\Tool;
use ILIAS\LTI\ToolProvider\Util;
//UK: added
use \ILIAS\LTI\ToolProvider\AccessToken;

/**
 * Class to provide a connection to a persistent store for LTI objects
 *
 * This class assumes no data persistence - it should be extended for specific database connections.
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class DataConnector
{

    /**
     * Default name for database table used to store platforms.
     */
    const PLATFORM_TABLE_NAME = 'lti2_consumer';

    /**
     * Default name for database table used to store platforms.
     *
     * @deprecated Use DataConnector::PLATFORM_TABLE_NAME instead
     * @see DataConnector::PLATFORM_TABLE_NAME
     */
    const CONSUMER_TABLE_NAME = self::PLATFORM_TABLE_NAME;

    /**
     * Default name for database table used to store contexts.
     */
    const CONTEXT_TABLE_NAME = 'lti2_context';

    /**
     * Default name for database table used to store resource links.
     */
    const RESOURCE_LINK_TABLE_NAME = 'lti2_resource_link';

    /**
     * Default name for database table used to store users.
     */
    const USER_RESULT_TABLE_NAME = 'lti2_user_result';

    /**
     * Default name for database table used to store resource link share keys.
     */
    const RESOURCE_LINK_SHARE_KEY_TABLE_NAME = 'lti2_share_key';

    /**
     * Default name for database table used to store nonce values.
     */
    const NONCE_TABLE_NAME = 'lti2_nonce';

    /**
     * Default name for database table used to store access token values.
     */
    const ACCESS_TOKEN_TABLE_NAME = 'lti2_access_token';

    /**
     * Default name for database table used to store tools.
     */
    const TOOL_TABLE_NAME = 'lti2_tool';

    /**
     * Database connection.
     *
     * @var object|resource $db
     */
    protected $db = null;

    /**
     * Prefix for database table names.
     *
     * @var string $dbTableNamePrefix
     */
    protected string $dbTableNamePrefix = '';

    /**
     * SQL date format (default = 'Y-m-d')
     *
     * @var string $dateFormat
     */
    protected string $dateFormat = 'Y-m-d';

    /**
     * SQL time format (default = 'H:i:s')
     *
     * @var string $timeFormat
     */
    protected string $timeFormat = 'H:i:s';

    /**
     * Class constructor
     * @param object $db                Database connection object
     * @param string $dbTableNamePrefix Prefix for database table names (optional, default is none)
     */
    protected function __construct(object $db, string $dbTableNamePrefix = '')
    {
        $this->db = $db;
        $this->dbTableNamePrefix = $dbTableNamePrefix;
    }

    ###
    ###  Platform methods
    ###

//    /**
//     * Load tool consumer object.
//     *
//     * @deprecated Use loadPlatform() instead
//     * @see DataConnector::loadPlatform()
//     *
//     * @param ToolConsumer $consumer  Tool consumer object
//     *
//     * @return bool    True if the tool consumer object was successfully loaded
//     */
//    public function loadToolConsumer($consumer)
//    {
//        Util::logDebug('Method ceLTIc\LTI\DataConnector\DataConnector::loadToolConsumer() has been deprecated; please use ceLTIc\LTI\DataConnector\DataConnector::loadPlatform() instead.',
//            true);
//        return $this->loadPlatform($consumer);
//    }

//    /**
//     * Save tool consumer object.
//     *
//     * @deprecated Use savePlatform() instead
//     * @see DataConnector::savePlatform()
//     *
//     * @param ToolConsumer $consumer  Tool consumer object
//     *
//     * @return bool    True if the tool consumer object was successfully saved
//     */
//    public function saveToolConsumer($consumer)
//    {
//        Util::logDebug('Method ceLTIc\LTI\DataConnector\DataConnector::saveToolConsumer() has been deprecated; please use ceLTIc\LTI\DataConnector\DataConnector::savePlatform() instead.',
//            true);
//        return $this->savePlatform($consumer);
//    }

//    /**
//     * Delete tool consumer object.
//     *
//     * @deprecated Use deletePlatform() instead
//     * @see DataConnector::deletePlatform()
//     *
//     * @param ToolConsumer $consumer  Tool consumer object
//     *
//     * @return bool    True if the tool consumer object was successfully deleted
//     */
//    public function deleteToolConsumer($consumer)
//    {
//        Util::logDebug('Method ceLTIc\LTI\DataConnector\DataConnector::deleteToolConsumer() has been deprecated; please use ceLTIc\LTI\DataConnector\DataConnector::deletePlatform() instead.',
//            true);
//        return $this->deletePlatform($consumer);
//    }

//    /**
//     * Load tool consumer objects.
//     *
//     * @deprecated Use getPlatforms() instead
//     * @see DataConnector::getPlatforms()
//     *
//     * @return ToolConsumer[] Array of all defined tool consumer objects
//     */
//    public function getToolConsumers()
//    {
//        Util::logDebug('Method ceLTIc\LTI\DataConnector\DataConnector::getToolConsumers() has been deprecated; please use ceLTIc\LTI\DataConnector\DataConnector::getPlatforms() instead.',
//            true);
//        return $this->getPlatforms();
//    }

    /**
     * Load platform object.
     * @param Platform $platform Platform object
     * @return bool    True if the platform object was successfully loaded
     */
    public function loadPlatform(Platform $platform) : bool
    {
        $platform->secret = 'secret';
        $platform->enabled = true;
        $now = time();
        $platform->created = $now;
        $platform->updated = $now;

        return true;
    }

    /**
     * Save platform object.
     * @param Platform $platform Platform object
     * @return bool    True if the platform object was successfully saved
     */
    public function savePlatform(Platform $platform) : bool
    {
        $platform->updated = time();

        return true;
    }

    /**
     * Delete platform object.
     * @param Platform $platform Platform object
     * @return bool    True if the platform object was successfully deleted
     */
    public function deletePlatform(Platform $platform) : bool
    {
        $platform->initialize();

        return true;
    }

    /**
     * Load platform objects.
     *
     * @return Platform[] Array of all defined Platform objects
     */
    public function getPlatforms() : array
    {
        return array();
    }

    ###
    ###  Context methods
    ###

    /**
     * Load context object.
     * @param Context $context Context object
     * @return bool    True if the context object was successfully loaded
     */
    public function loadContext(Context $context) : bool
    {
        $now = time();
        $context->created = $now;
        $context->updated = $now;

        return true;
    }

    /**
     * Save context object.
     * @param Context $context Context object
     * @return bool    True if the context object was successfully saved
     */
    public function saveContext(Context $context) : bool
    {
        $context->updated = time();

        return true;
    }

    /**
     * Delete context object.
     * @param Context $context Context object
     * @return bool    True if the Context object was successfully deleted
     */
    public function deleteContext(Context $context) : bool
    {
        $context->initialize();

        return true;
    }

    ###
    ###  ResourceLink methods
    ###

    /**
     * Load resource link object.
     * @param ResourceLink $resourceLink ResourceLink object
     * @return bool    True if the resource link object was successfully loaded
     */
    public function loadResourceLink(ResourceLink $resourceLink) : bool
    {
        $now = time();
        $resourceLink->created = $now;
        $resourceLink->updated = $now;

        return true;
    }

    /**
     * Save resource link object.
     * @param ResourceLink $resourceLink ResourceLink object
     * @return bool    True if the resource link object was successfully saved
     */
    public function saveResourceLink(ResourceLink $resourceLink) : bool
    {
        $resourceLink->updated = time();

        return true;
    }

    /**
     * Delete resource link object.
     * @param ResourceLink $resourceLink ResourceLink object
     * @return bool    True if the resource link object was successfully deleted
     */
    public function deleteResourceLink(ResourceLink $resourceLink) : bool
    {
        $resourceLink->initialize();

        return true;
    }

    /**
     * Get array of user objects.
     * Obtain an array of UserResult objects for users with a result sourcedId.  The array may include users from other
     * resource links which are sharing this resource link.  It may also be optionally indexed by the user ID of a specified scope.
     * @param ResourceLink $resourceLink Resource link object
     * @param bool         $localOnly    True if only users within the resource link are to be returned (excluding users sharing this resource link)
     * @param int          $idScope      Scope value to use for user IDs
     * @return UserResult[] Array of UserResult objects
     */
    public function getUserResultSourcedIDsResourceLink(ResourceLink $resourceLink, bool $localOnly, int $idScope) : array
    {
        return array();
    }

    /**
     * Get array of shares defined for this resource link.
     * @param ResourceLink $resourceLink ResourceLink object
     * @return ResourceLinkShare[] Array of ResourceLinkShare objects
     */
    public function getSharesResourceLink(ResourceLink $resourceLink) : array
    {
        return array();
    }

    ###
    ###  PlatformNonce methods
    ###

//    /**
//     * Load nonce object.
//     *
//     * @deprecated Use loadPlatformNonce() instead
//     * @see DataConnector::loadPlatformNonce()
//     *
//     * @param ConsumerNonce $nonce Nonce object
//     *
//     * @return bool    True if the nonce object was successfully loaded
//     */
//    public function loadConsumerNonce($nonce)
//    {
//        Util::logDebug('Method ceLTIc\LTI\DataConnector\DataConnector::loadConsumerNonce() has been deprecated; please use ceLTIc\LTI\DataConnector\DataConnector::loadPlatformNonce() instead.',
//            true);
//        return $this->loadPlatformNonce($nonce);
//    }

//    /**
//     * Save nonce object.
//     *
//     * @deprecated Use savePlatformNonce() instead
//     * @see DataConnector::savePlatformNonce()
//     *
//     * @param ConsumerNonce $nonce Nonce object
//     *
//     * @return bool    True if the nonce object was successfully saved
//     */
//    public function saveConsumerNonce($nonce)
//    {
//        Util::logDebug('Method ceLTIc\LTI\DataConnector\DataConnector::saveConsumerNonce() has been deprecated; please use ceLTIc\LTI\DataConnector\DataConnector::savePlatformNonce() instead.',
//            true);
//        return $this->savePlatformNonce($nonce);
//    }

//    /**
//     * Delete nonce object.
//     *
//     * @deprecated Use deletePlatformNonce() instead
//     * @see DataConnector::deletePlatformNonce()
//     *
//     * @param ConsumerNonce $nonce Nonce object
//     *
//     * @return bool    True if the nonce object was successfully deleted
//     */
//    public function deleteConsumerNonce($nonce)
//    {
//        Util::logDebug('Method ceLTIc\LTI\DataConnector\DataConnector::deleteConsumerNonce() has been deprecated; please use ceLTIc\LTI\DataConnector\DataConnector::deletePlatformNonce() instead.',
//            true);
//        return $this->deletePlatformNonce($nonce);
//    }

    /**
     * Load nonce object.
     * @param PlatformNonce $nonce Nonce object
     * @return bool    True if the nonce object was successfully loaded
     */
    public function loadPlatformNonce(PlatformNonce $nonce) : bool
    {
        return false;  // assume the nonce does not already exist
    }

    /**
     * Save nonce object.
     * @param PlatformNonce $nonce Nonce object
     * @return bool    True if the nonce object was successfully saved
     */
    public function savePlatformNonce(PlatformNonce $nonce) : bool
    {
        return true;
    }

    /**
     * Delete nonce object.
     * @param PlatformNonce $nonce Nonce object
     * @return bool    True if the nonce object was successfully deleted
     */
    public function deletePlatformNonce(PlatformNonce $nonce) : bool
    {
        return true;
    }

    ###
    ###  AccessToken methods
    ###

    /**
     * Load access token object.
     * @param AccessToken $accessToken Access token object  // UK: changed from
     * @return bool    True if the nonce object was successfully loaded
     */
    public function loadAccessToken(AccessToken $accessToken) : bool
    {
        return false;  // assume the access token does not already exist
    }

    /**
     * Save access token object.
     * @param AccessToken $accessToken Access token object
     * @return bool    True if the access token object was successfully saved
     */
    public function saveAccessToken(AccessToken $accessToken) : bool
    {
        return true;
    }

    ###
    ###  ResourceLinkShareKey methods
    ###

    /**
     * Load resource link share key object.
     * @param ResourceLinkShareKey $shareKey ResourceLink share key object
     * @return bool    True if the resource link share key object was successfully loaded
     */
    public function loadResourceLinkShareKey(ResourceLinkShareKey $shareKey) : bool
    {
        return true;
    }

    /**
     * Save resource link share key object.
     * @param ResourceLinkShareKey $shareKey Resource link share key object
     * @return bool    True if the resource link share key object was successfully saved
     */
    public function saveResourceLinkShareKey(ResourceLinkShareKey $shareKey) : bool
    {
        return true;
    }

    /**
     * Delete resource link share key object.
     * @param ResourceLinkShareKey $shareKey Resource link share key object
     * @return bool    True if the resource link share key object was successfully deleted
     */
    public function deleteResourceLinkShareKey(ResourceLinkShareKey $shareKey) : bool
    {
        return true;
    }

    ###
    ###  UserResult methods
    ###

    /**
     * Load user object.
     * @param UserResult $userresult UserResult object
     * @return bool    True if the user object was successfully loaded
     */
    public function loadUserResult(UserResult $userresult) : bool
    {
        $now = time();
        $userresult->created = $now;
        $userresult->updated = $now;

        return true;
    }

    /**
     * Save user object.
     * @param UserResult $userresult UserResult object
     * @return bool    True if the user object was successfully saved
     */
    public function saveUserResult(UserResult $userresult) : bool
    {
        $userresult->updated = time();

        return true;
    }

    /**
     * Delete user object.
     * @param UserResult $userresult UserResult object
     * @return bool    True if the user object was successfully deleted
     */
    public function deleteUserResult(UserResult $userresult) : bool
    {
        $userresult->initialize();

        return true;
    }

    ###
    ###  Tool methods
    ###

    /**
     * Load tool object.
     * @param Tool $tool Tool object
     * @return bool    True if the tool object was successfully loaded
     */
    public function loadTool(Tool $tool) : bool
    {
        $tool->secret = 'secret';
        $tool->enabled = true;
        $now = time();
        $tool->created = $now;
        $tool->updated = $now;

        return true;
    }

    /**
     * Save tool object.
     * @param Tool $tool Tool object
     * @return bool    True if the tool object was successfully saved
     */
    public function saveTool(Tool $tool) : bool
    {
        $tool->updated = time();

        return true;
    }

    /**
     * Delete tool object.
     * @param Tool $tool Tool object
     * @return bool    True if the tool object was successfully deleted
     */
    public function deleteTool(Tool $tool) : bool
    {
        $tool->initialize();

        return true;
    }

    /**
     * Load platform objects.
     *
     * @return Tool[] Array of all defined Tool objects
     */
    public function getTools() : array
    {
        return array();
    }

    ###
    ###  Other methods
    ###

    /**
     * Create data connector object.
     * A data connector provides access to persistent storage for the different objects.
     * Names of tables may be given a prefix to allow multiple versions to share the same schema.  A separate sub-class is defined for
     * each different database connection - the class to use is determined by inspecting the database object passed, but this can be overridden
     * (for example, to use a bespoke connector) by specifying a type.  If no database is passed then this class is used which acts as a dummy
     * connector with no persistence.
     * @param object|null $db                A database connection object or string (optional, default is no persistence)
     * @param string      $dbTableNamePrefix Prefix for database table names (optional, default is none)
     * @param string      $type              The type of data connector (optional, default is based on $db parameter)
     * @return DataConnector Data connector object
     */
    public static function getDataConnector(object $db = null, string $dbTableNamePrefix = '', string $type = '') : DataConnector
    {
        if (is_null($dbTableNamePrefix)) {
            $dbTableNamePrefix = '';
        }
        if (!is_null($db) && empty($type)) {
            if (is_object($db)) {
                $type = get_class($db);
            } elseif (is_resource($db)) {
                $type = strtok(get_resource_type($db), ' ');
            }
        }
        $type = strtolower($type);
        if ($type === 'pdo') {
            if ($db->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
                $type .= '_pgsql';
            } elseif ($db->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'oci') {
                $type .= '_oci';
            }
        }
        if (!empty($type)) {
            $type = "DataConnector_{$type}";
        } else {
            $type = 'DataConnector';
        }
        $type = "\\ceLTIc\\LTI\\DataConnector\\{$type}";
        $dataConnector = new $type($db, $dbTableNamePrefix);

        return $dataConnector;
    }

//    /**
//     * Generate a random string.
//     *
//     * The generated string will only comprise letters (upper- and lower-case) and digits.
//     *
//     * @deprecated Use Util::getRandomString() instead
//     * @see Util::getRandomString()
//     *
//     * @param int $length Length of string to be generated (optional, default is 8 characters)
//     *
//     * @return string Random string
//     */
//    public static function getRandomString($length = 8)
//    {
//        Util::logDebug('Method ceLTIc\LTI\DataConnector::getRandomString() has been deprecated; please use ceLTIc\LTI\Util::getRandomString() instead.',
//            true);
//        return Util::getRandomString($length);
//    }

    /**
     * Escape a string for use in a database query.
     * Any single quotes in the value passed will be replaced with two single quotes.  If a null value is passed, a string
     * of 'null' is returned (which will never be enclosed in quotes irrespective of the value of the $addQuotes parameter.
     * @param string $value     Value to be escaped
     * @param bool   $addQuotes If true the returned string will be enclosed in single quotes (optional, default is true)
     * @return string The escaped string.
     */
    public function escape(string $value, bool $addQuotes = true) : string
    {
        return static::quoted($value, $addQuotes);
    }

    /**
     * Quote a string for use in a database query.
     * Any single quotes in the value passed will be replaced with two single quotes.  If a null value is passed, a string
     * of 'null' is returned (which will never be enclosed in quotes irrespective of the value of the $addQuotes parameter.
     * @param string $value     Value to be quoted
     * @param bool   $addQuotes If true the returned string will be enclosed in single quotes (optional, default is true)
     * @return string The quoted string.
     */
    public static function quoted(string $value, bool $addQuotes = true) : string
    {
        if (is_null($value)) {
            $value = 'null';
        } else {
            $value = str_replace('\'', '\'\'', $value);
            if ($addQuotes) {
                $value = "'{$value}'";
            }
        }

        return $value;
    }

    /**
     * Adjust the settings for any platform properties being stored as a setting value.
     * @param Platform $platform Platform object
     * @param bool     $isSave   True if the settings are being saved
     */
    protected function fixPlatformSettings(Platform $platform, bool $isSave)
    {
        if (!$isSave) {
            $platform->authorizationServerId = $platform->getSetting('_authorization_server_id', $platform->authorizationServerId);
            $platform->setSetting('_authorization_server_id');
            $platform->authenticationUrl = $platform->getSetting('_authentication_request_url', $platform->authenticationUrl);
            $platform->setSetting('_authentication_request_url');
            $platform->accessTokenUrl = $platform->getSetting('_oauth2_access_token_url', $platform->accessTokenUrl);
            $platform->setSetting('_oauth2_access_token_url');
            $platform->jku = $platform->getSetting('_jku', $platform->jku);
            $platform->setSetting('_jku');
            $platform->encryptionMethod = $platform->getSetting('_encryption_method', $platform->encryptionMethod);
            $platform->setSetting('_encryption_method');
            $platform->debugMode = $platform->getSetting('_debug', $platform->debugMode ? 'true' : 'false') === 'true';
            $platform->setSetting('_debug');
            if ($platform->debugMode) {
                Util::$logLevel = Util::LOGLEVEL_DEBUG;
            }
        } else {
            $platform->setSetting(
                '_authorization_server_id',
                !empty($platform->authorizationServerId) ? $platform->authorizationServerId : null
            );
            $platform->setSetting(
                '_authentication_request_url',
                !empty($platform->authenticationUrl) ? $platform->authenticationUrl : null
            );
            $platform->setSetting('_oauth2_access_token_url', !empty($platform->accessTokenUrl) ? $platform->accessTokenUrl : null);
            $platform->setSetting('_jku', !empty($platform->jku) ? $platform->jku : null);
            $platform->setSetting('_encryption_method', !empty($platform->encryptionMethod) ? $platform->encryptionMethod : null);
            $platform->setSetting('_debug', $platform->debugMode ? 'true' : null);
        }
    }

    /**
     * Adjust the settings for any tool properties being stored as a setting value.
     * @param Tool $tool   Tool object
     * @param bool $isSave True if the settings are being saved
     */
    protected function fixToolSettings(Tool $tool, bool $isSave)
    {
        if (!$isSave) {
            $tool->encryptionMethod = $tool->getSetting('_encryption_method', $tool->encryptionMethod);
            $tool->setSetting('_encryption_method');
            $tool->debugMode = $tool->getSetting('_debug', $tool->debugMode ? 'true' : 'false') === 'true';
            $tool->setSetting('_debug');
            if ($tool->debugMode) {
                Util::$logLevel = Util::LOGLEVEL_DEBUG;
            }
        } else {
            $tool->setSetting('_encryption_method', !empty($tool->encryptionMethod) ? $tool->encryptionMethod : null);
            $tool->setSetting('_debug', $tool->debugMode ? 'true' : null);
        }
    }
}
