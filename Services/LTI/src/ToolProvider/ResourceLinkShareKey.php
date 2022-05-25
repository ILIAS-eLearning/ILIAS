<?php

namespace ILIAS\LTI\ToolProvider;

use ILIAS\LTI\ToolProvider\DataConnector\DataConnector;

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
 * Class to represent a platform resource link share key
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
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
     * @var int|null $resourceLinkId
     */
    public ?int $resourceLinkId = null;

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
     * @var bool    $autoApprove
     */
    public bool $autoApprove = false;

    /**
     * Timestamp for when the share key expires.
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
    private ?DataConnector $dataConnector = null;

    /**
     * Class constructor.
     * @param ResourceLink $resourceLink ResourceLink object
     * @param string|null  $id           Value of share key (optional, default is null)
     */
    public function __construct(ResourceLink $resourceLink, string $id = null)
    {
        $this->initialize();
        $this->dataConnector = $resourceLink->getDataConnector();
        $this->id = $id;
        if (!empty($id)) {
            $this->load();
        } else {
            $this->resourceLinkId = $resourceLink->getRecordId();
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
     * Synonym for initialize().
     */
    public function initialise()
    {
        $this->initialize();
    }

    /**
     * Save the resource link share key to the database.
     *
     * @return bool    True if the share key was successfully saved
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
            $this->id = Util::getRandomString($this->length);
        }

        return $this->dataConnector->saveResourceLinkShareKey($this);
    }

    /**
     * Delete the resource link share key from the database.
     *
     * @return bool    True if the share key was successfully deleted
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
            $this->length = strlen(strval($this->id));
        }
        if (!is_null($this->expires)) {
            $this->life = ($this->expires - time()) / 60 / 60;
        }
    }
}
