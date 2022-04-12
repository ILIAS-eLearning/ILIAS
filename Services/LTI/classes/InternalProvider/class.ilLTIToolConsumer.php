<?php declare(strict_types=1);

use ILIAS\LTI\ToolProvider\ToolConsumer;

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
 * LTI provider for LTI launch
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLTIToolConsumer extends ToolConsumer
{
    /**
     * @var int ref_id
     */
    protected int $ref_id;


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

    public function getId() : ?int
    {
        return $this->getRecordId();
    }

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

    public function getSecret() : string
    {
        return $this->secret;
    }
    
    
    /**
     * Create a secret
     */
    public function createSecret() : void
    {
        $this->setSecret(\ILIAS\LTI\ToolProvider\DataConnector\DataConnector::getRandomString(12));
    }

    /**
     * @param string $lang  (int?)
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
     * Load the tool consumer from the database by its record ID.
     *
     * @param int             $id                The consumer key record ID
     * @param \ILIAS\LTI\ToolProvider\DataConnector\DataConnector   $dataConnector    Database connection object
     *
     * @return object ToolConsumer       The tool consumer object
     */
    public static function fromRecordId(int $id, \ILIAS\LTI\ToolProvider\DataConnector\DataConnector $dataConnector) : \ilLTIToolConsumer
    {
        $toolConsumer = new ilLTIToolConsumer(null, $dataConnector);

        $toolConsumer->initialize();
        $toolConsumer->setRecordId($id);
        if (!$dataConnector->loadToolConsumerILIAS($toolConsumer)) {
            $toolConsumer->initialize();
        }
        $toolConsumer->setRecordId($id);

        ilLoggerFactory::getLogger('lti')->info('Loading with record id: ' . $toolConsumer->getRecordId());

        return $toolConsumer;
    }

    /**
     * @param int                $id
     * @param ilLTIDataConnector $dataConnector
     * @return ilLTIToolConsumer
     */
    public static function fromExternalConsumerId(int $id, ilLTIDataConnector $dataConnector) : \ilLTIToolConsumer
    {
        $toolConsumer = new ilLTIToolConsumer(null, $dataConnector);
        $toolConsumer->initialize();
        $toolConsumer->setExtConsumerId($id);
        if (!$dataConnector->loadGlobalToolConsumerSettings($toolConsumer)) {
            $toolConsumer->initialize();
        }
        return $toolConsumer;
    }

    /**
     * Load consumer from global settings and ref_id
     * @param int                $a_ext_consumer_id
     * @param int                $a_ref_id
     * @param ilLTIDataConnector $a_data_connector
     * @return ilLTIToolConsumer|object
     */
    public static function fromGlobalSettingsAndRefId(int $a_ext_consumer_id, int $a_ref_id, ilLTIDataConnector $a_data_connector)
    {
        $toolConsumer = new ilLTIToolConsumer(null, $a_data_connector);
        $toolConsumer->initialize();
        $toolConsumer->setExtConsumerId($a_ext_consumer_id);
        $toolConsumer->setRefId($a_ref_id);
        
        $consumer_pk = $a_data_connector->lookupRecordIdByGlobalSettingsAndRefId($toolConsumer);
        if ($consumer_pk) {
            return self::fromRecordId($consumer_pk, $a_data_connector);
        }
        $toolConsumer->initialize();
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
