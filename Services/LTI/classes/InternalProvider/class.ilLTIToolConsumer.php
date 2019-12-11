<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use IMSGlobal\LTI\ToolProvider\ToolConsumer;

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
    protected $ref_id;


    /**
     * @var int ext consumer id
     */
    protected $ext_consumer_id = 0;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var string
     */
    protected $user_language = '';

    /**
     * @var int
     */
    protected $role = 0;

    
    public function setExtConsumerId($a_id)
    {
        $this->ext_consumer_id = $a_id;
    }
    
    public function getExtConsumerId()
    {
        return $this->ext_consumer_id;
    }
    
    public function setRefId($a_ref_id)
    {
        $this->ref_id = $a_ref_id;
    }
    
    public function getRefId()
    {
        return $this->ref_id;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->getRecordId();
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        // $this->consumerName = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }
    
    
    /**
     * Create a secret
     */
    public function createSecret()
    {
        $this->setSecret(\IMSGlobal\LTI\ToolProvider\DataConnector\DataConnector::getRandomString(12));
    }

    /**
     * @param string $lang  (int?)
     */
    public function setLanguage($lang)
    {
        $this->user_language = $lang;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->user_language;
    }

    /**
     * @param integer $value
     */
    public function setActive($value)
    {
        $this->active = $value;
    }

    /**
     * @return integer
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param integer $role_id
     */
    public function setRole($role_id)
    {
        $this->role = $role_id;
    }

    /**
     * @return integer
     */
    public function getRole()
    {
        return $this->role;
    }
    
    
    public function setEnabled($a_status)
    {
        $this->enabled = $a_status;
    }
    
    public function getEnabled()
    {
        return $this->enabled;
    }

    // local_role_always_member, default_skin

    /**
     * Load the tool consumer from the database by its record ID.
     *
     * @param int             $id                The consumer key record ID
     * @param DataConnector   $dataConnector    Database connection object
     *
     * @return object ToolConsumer       The tool consumer object
     */
    public static function fromRecordId($id, $dataConnector)
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
     *
     * @param type $id
     * @param type $dataConnector
     */
    public static function fromExternalConsumerId($id, $dataConnector)
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
     * @param type $a_ext_consumer_id
     * @param type $a_ref_id
     * @param ilLTIDataConnector $a_data_connector
     * @retrun ilLTIToolConsumer
     */
    public static function fromGlobalSettingsAndRefId($a_ext_consumer_id, $a_ref_id, ilLTIDataConnector $a_data_connector)
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
    public function saveGlobalToolConsumerSettings(ilLTIDataConnector $dataConnector)
    {
        $dataConnector->saveGlobalToolConsumerSettings($this);
    }
    
    /**
     * Delete global tool consumer settings
     * @param ilLTIDataConnector $dataConnector
     */
    public function deleteGlobalToolConsumerSettings(ilLTIDataConnector $dataConnector)
    {
        $dataConnector->deleteGlobalToolConsumerSettings($this);
    }


    /**
     * Save the tool consumer to the database with ILIAS extension.
     *
     * @return boolean True if the object was successfully saved
     */
    public function saveLTI($dataConnector)
    {
        $ok = $dataConnector->saveToolConsumerILIAS($this);
        return $ok;
    }
}
