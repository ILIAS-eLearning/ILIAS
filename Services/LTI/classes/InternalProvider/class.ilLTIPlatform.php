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

use ILIAS\LTI\ToolProvider;
//use ILIAS\LTI\ToolProvider\Platform;
//use ILIAS\LTI\ToolProvider\DataConnector\DataConnector;
use ILIAS\LTI\ToolProvider\Service;
use ILIAS\LTI\ToolProvider\Http\HTTPMessage;
//use ILIAS\LTIOAuth;
use ILIAS\LTI\ToolProvider\ApiHook\ApiHook;

/**
 * LTI provider for LTI launch
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLTIPlatform extends ToolProvider\Platform
{
    /**
     * @var int ref_id
     */
    protected int $ref_id = 0;

    /**
     * @var int ext consumer id
     */
    protected int $ext_consumer_id = 0;

    /**
     * @var string
     */
    protected string $title = '';

    /**
     * @var string
     */
    protected string $description = '';

    /**
     * @var string
     */
    protected string $prefix = '';

    /**
     * @var string
     */
    protected string $user_language = '';

    /**
     * @var int
     */
    protected int $role = 0;

    /**
     * @var bool
     */
    protected bool $active = false;

    /**
     * Setting values (LTI parameters, custom parameters and local parameters).
     *
     * @var array $settings
     */
    private ?array $settings = null;

    /**
     * System ID value.
     *
     * @var int|null $id
     */
    private ?int $id = null;

    /**
     * Consumer key/client ID value.
     *
     * @var string|null $key
     */
    private ?string $key = null;

    /**
     * Class constructor.
     * @param ilLTIDataConnector|null $dataConnector A data connector object
     */
    public function __construct(ilLTIDataConnector $dataConnector = null)
    {
        $this->initialize();
        if (empty($dataConnector)) {
            $dataConnector = ilLTIDataConnector::getDataConnector();
        }
        $this->dataConnector = $dataConnector;
    }

    /**
     * Initialise the platform.
     */
    public function initialize() : void
    {
        $this->id = null;
        $this->key = null;
        $this->name = null;
        $this->secret = null;
        $this->signatureMethod = 'HMAC-SHA1';
        $this->encryptionMethod = '';
        $this->rsaKey = null;
        $this->kid = null;
        $this->jku = '';
        $this->platformId = null;
        $this->clientId = null;
        $this->deploymentId = null;
        $this->ltiVersion = null;
        $this->consumerName = null;
        $this->consumerVersion = null;
        $this->consumerGuid = null;
        $this->profile = null;
        $this->toolProxy = null;
        $this->settings = array();
        $this->protected = false;
        $this->enabled = false;
        $this->enableFrom = null;
        $this->enableUntil = null;
        $this->lastAccess = null;
        $this->idScope = ILIAS\LTI\ToolProvider\Tool::ID_SCOPE_ID_ONLY;
        $this->defaultEmail = '';
        $this->created = null;
        $this->updated = null;
        //added
        $this->authorizationServerId = '';
        $this->authenticationUrl = '';
        $this->accessTokenUrl = '';
        $this->debugMode = false;
    }


    public function setExtConsumerId(int $a_id) : void
    {
        $this->ext_consumer_id = $a_id;
    }
    
    public function getExtConsumerId() : int
    {
        return $this->ext_consumer_id;
    }
    
    public function setRefId(int $a_ref_id) : void
    {
        $this->ref_id = $a_ref_id;
    }
    
    public function getRefId() : int
    {
        return $this->ref_id;
    }

//    public function getId() : ?int
//    {
//        return $this->getRecordId();
//    }

    public function setTitle(string $title) : void
    {
        $this->title = $title;
        // $this->consumerName = $title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setPrefix(string $prefix) : void
    {
        $this->prefix = $prefix;
    }

    public function getPrefix() : string
    {
        return $this->prefix;
    }

    public function setSecret(string $secret) : void
    {
        $this->secret = $secret;
    }

    public function getSecret() : ?string
    {
        return $this->secret;
    }

    /**
     * Create a secret
     */
    public function createSecret() : void
    {
        $this->setSecret(\ILIAS\LTI\ToolProvider\Util::getRandomString(12));
    }

    /**
     * @param string $lang
     */
    public function setLanguage(string $lang) : void
    {
        $this->user_language = $lang;
    }

    public function getLanguage() : string
    {
        return $this->user_language;
    }

    public function setActive(bool $value) : void
    {
        $this->active = $value;
    }

    public function getActive() : bool
    {
        return $this->active;
    }

    public function setRole(int $role_id) : void
    {
        $this->role = $role_id;
    }

    public function getRole() : int
    {
        return $this->role;
    }
    
    
    public function setEnabled(bool $a_status) : void
    {
        $this->enabled = $a_status;
    }
    
    public function getEnabled() : bool
    {
        return $this->enabled;
    }

    // local_role_always_member, default_skin

    /**
     * Load the platform from the database by its platform, client and deployment IDs.
     * @param string             $platformId    The platform ID
     * @param string             $clientId      The client ID
     * @param string             $deploymentId  The deployment ID
     * @param ilLTIDataConnector $dataConnector A data connector object
     * @param bool               $autoEnable    True if the platform is to be enabled automatically (optional, default is false)
     * @return Platform                         The platform object
     */
    public static function fromPlatformId(string $platformId, string $clientId, string $deploymentId, ilLTIDataConnector $dataConnector = null, bool $autoEnable = false) : ilLTIPlatform
    {
        $platform = new ilLTIPlatform($dataConnector);
        $platform->initialize();
        $platform->platformId = $platformId;
        $platform->clientId = $clientId;
        $platform->deploymentId = $deploymentId;
        $dataConnector->loadPlatform($platform);
        $dataConnector->loadGlobalToolConsumerSettings($platform);
        return $platform;
    }

    /**
     * Load the platform from the database by its consumer key.
     * @param string             $key           Consumer key
     * @param ilLTIDataConnector $dataConnector A data connector object
     * @param bool               $autoEnable    true if the platform is to be enabled automatically (optional, default is false)
     * @return \ilLTIPlatform Platform       The platform object
     */
    public static function fromConsumerKey(?string $key = null, $dataConnector = null, bool $autoEnable = false) : \ilLTIPlatform
    {
        $platform = new ilLTIPlatform($dataConnector);
        $platform->initialize();
        $platform->setKey($key);
        ilLoggerFactory::getLogger('ltis')->debug('Loading with key: ' . $platform->getKey());
        $dataConnector->loadPlatform($platform);
        $dataConnector->loadGlobalToolConsumerSettings($platform);
        return $platform;
    }

    /**
     * Load the platform from the database by its record ID.
     * @param int        $id            The platform record ID
     * @param ilLTIDataConnector $dataConnector Database connection object
     * @return \ilLTIPlatform Platform       The platform object
     */
    public static function fromRecordId(int $id, ilLTIDataConnector $dataConnector) : \ilLTIPlatform
    {
//        $platform = new static($dataConnector);
        $platform = new ilLTIPlatform($dataConnector);
        $platform->initialize();
        $platform->setRecordId((int) $id);
        ilLoggerFactory::getLogger('ltis')->info('Loading with record id: ' . $platform->getRecordId());
        $dataConnector->loadPlatform($platform);
        $dataConnector->loadGlobalToolConsumerSettings($platform);
        return $platform;
    }

    /**
     * @param int                $id
     * @param ilLTIDataConnector $dataConnector
     * @return ilLTIPlatform
     */
    public static function fromExternalConsumerId(int $id, ilLTIDataConnector $dataConnector) : \ilLTIPlatform
    {
        $platform = new ilLTIPlatform($dataConnector);
        //$platform->setRecordId((int) $id);
        //$dataConnector->loadPlatform($platform);
        $platform->initialize();
        $platform->setExtConsumerId($id);
        if (!$dataConnector->loadGlobalToolConsumerSettings($platform)) {
            $platform->initialize();
        }
        return $platform;
    }

    /**
     * Load consumer from global settings and ref_id
     * @param int                $a_ext_consumer_id
     * @param int                $a_ref_id
     * @param ilLTIDataConnector $a_data_connector
     * @return ilLTIPlatform
     */
    public static function fromGlobalSettingsAndRefId(int $a_ext_consumer_id, int $a_ref_id, ilLTIDataConnector $a_data_connector) : ilLTIPlatform
    {
        $toolConsumer = new ilLTIPlatform(null, $a_data_connector);
        $toolConsumer->initialize();
        $toolConsumer->setExtConsumerId($a_ext_consumer_id);
        $toolConsumer->setRefId($a_ref_id);
        
        $consumer_pk = $a_data_connector->lookupRecordIdByGlobalSettingsAndRefId($toolConsumer);
        if ($consumer_pk != null) {
            $toolConsumer = self::fromRecordId($consumer_pk, $a_data_connector);
        }
        return $toolConsumer;
    }


    /**
     * Save global consumer settings.
     * @param ilLTIDataConnector $dataConnector
     */
    public function saveGlobalToolConsumerSettings(ilLTIDataConnector $dataConnector) : void
    {
        $dataConnector->saveGlobalToolConsumerSettings($this);
    }
    
    /**
     * Delete global tool consumer settings
     * @param ilLTIDataConnector $dataConnector
     */
    public function deleteGlobalToolConsumerSettings(ilLTIDataConnector $dataConnector) : void
    {
        $dataConnector->deleteGlobalToolConsumerSettings($this);
    }

    /**
     * Save the tool consumer to the database with ILIAS extension.
     * @param ilLTIDataConnector $dataConnector
     * @return boolean True if the object was successfully saved
     */
    public function saveLTI(ilLTIDataConnector $dataConnector) : bool
    {
        $ok = $dataConnector->saveToolConsumerILIAS($this);
        return $ok;
    }
}
