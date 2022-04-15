<?php

namespace ILIAS\LTI\ToolProvider;

use ILIAS\LTI\ToolProvider\DataConnector\DataConnector;

/**
 * Class to represent a tool consumer resource link share key
 *
 * @author  Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version 3.0.2
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
class ResourceLinkShareKey
{

/**
 * Maximum permitted life for a share key value.
 */
    const MAX_SHARE_KEY_LIFE = 168;  // in hours (1 week)
    /**
     * Default life for a share key value.
     */
    const DEFAULT_SHARE_KEY_LIFE = 24;  // in hours
    /**
     * Minimum length for a share key value.
     */
    const MIN_SHARE_KEY_LENGTH = 5;
    /**
     * Maximum length for a share key value.
     */
    const MAX_SHARE_KEY_LENGTH = 32;

    /**
     * ID for resource link being shared.
     *
     * @var string|null $resourceLinkId
     */
    public ?string $resourceLinkId = null;
    /**
     * Length of share key.
     *
     * @var int|null $length
     */
    public ?int $length = null;
    /**
     * Life of share key.
     *
     * @var int|null $life
     */
    public ?int $life = null;  // in hours
    /**
     * Whether the sharing arrangement should be automatically approved when first used.
     *
     * @var boolean $autoApprove
     */
    public bool $autoApprove = false;
    /**
     * Date/time when the share key expires.
     *
     * @var int|null $expires
     */
    public ?int $expires = null;

    /**
     * Share key value.
     *
     * @var string|null $id
     */
    private ?string $id = null;
    /**
     * Data connector.
     *
     * @var DataConnector|null $dataConnector
     */
    private ?\ILIAS\LTI\ToolProvider\DataConnector\DataConnector $dataConnector = null;

    /**
     * Class constructor.
     * @param ResourceLink $resourceLink Resource_Link object
     * @param string|null  $id           Value of share key (optional, default is null)
     */
    public function __construct(ResourceLink $resourceLink, ?string $id = null)
    {
        $this->initialize();
        $this->dataConnector = $resourceLink->getDataConnector();
        $this->resourceLinkId = $resourceLink->getRecordId();
        $this->id = $id;
        if (!empty($id)) {
            $this->load();
        }
    }

    /**
     * Initialise the resource link share key.
     */
    public function initialize()
    {
        $this->length = null;
        $this->life = null;
        $this->autoApprove = false;
        $this->expires = null;
    }

    /**
     * Initialise the resource link share key.
     *
     * Pseudonym for initialize().
     */
    public function initialise()
    {
        $this->initialize();
    }

    /**
     * Save the resource link share key to the database.
     *
     * @return boolean True if the share key was successfully saved
     */
    public function save() : bool
    {
        if (empty($this->life)) {
            $this->life = self::DEFAULT_SHARE_KEY_LIFE;
        } else {
            $this->life = max(min($this->life, self::MAX_SHARE_KEY_LIFE), 0);
        }
        $this->expires = time() + ($this->life * 60 * 60);
        if (empty($this->id)) {
            if (empty($this->length) || !is_numeric($this->length)) {
                $this->length = self::MAX_SHARE_KEY_LENGTH;
            } else {
                $this->length = max(min($this->length, self::MAX_SHARE_KEY_LENGTH), self::MIN_SHARE_KEY_LENGTH);
            }
            $this->id = DataConnector::getRandomString($this->length);
        }

        return $this->dataConnector->saveResourceLinkShareKey($this);
    }

    /**
     * Delete the resource link share key from the database.
     *
     * @return boolean True if the share key was successfully deleted
     */
    public function delete() : bool
    {
        return $this->dataConnector->deleteResourceLinkShareKey($this);
    }

    /**
     * Get share key value.
     *
     * @return string Share key value
     */
    public function getId() : ?string
    {
        return $this->id;
    }

    ###
    ###  PRIVATE METHOD
    ###

    /**
     * Load the resource link share key from the database.
     */
    private function load()
    {
        $this->initialize();
        $this->dataConnector->loadResourceLinkShareKey($this);
        if (!is_null($this->id)) {
            $this->length = strlen($this->id);
        }
        if (!is_null($this->expires)) {
            $this->life = ($this->expires - time()) / 60 / 60;
        }
    }
}
