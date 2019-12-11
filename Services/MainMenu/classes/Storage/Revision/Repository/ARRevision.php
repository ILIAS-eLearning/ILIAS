<?php

namespace ILIAS\MainMenu\Storage\Revision\Repository;

use ActiveRecord;

/**
 * Class ARRevision
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ARRevision extends ActiveRecord
{

    /**
     * @inheritDoc
     */
    public function getConnectorContainerName()
    {
        return 'il_resource_revision';
    }


    /**
     * @var string
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $internal;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $identification;
    /**
     * @var bool
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $available;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $version_number;


    /**
     * @return string
     */
    public function getInternal() : string
    {
        return $this->internal;
    }


    /**
     * @param string $internal
     *
     * @return ARRevision
     */
    public function setInternal(string $internal) : ARRevision
    {
        $this->internal = $internal;

        return $this;
    }


    /**
     * @return string
     */
    public function getIdentification() : string
    {
        return $this->identification;
    }


    /**
     * @param string $identification
     *
     * @return ARRevision
     */
    public function setIdentification(string $identification) : ARRevision
    {
        $this->identification = $identification;

        return $this;
    }


    /**
     * @return bool
     */
    public function isAvailable() : bool
    {
        return (bool) $this->available;
    }


    /**
     * @param bool $available
     *
     * @return ARRevision
     */
    public function setAvailable(bool $available) : ARRevision
    {
        $this->available = $available;

        return $this;
    }


    /**
     * @return int
     */
    public function getVersionNumber() : int
    {
        return (int) $this->version_number;
    }


    /**
     * @param int $version_number
     *
     * @return ARRevision
     */
    public function setVersionNumber(int $version_number) : ARRevision
    {
        $this->version_number = $version_number;

        return $this;
    }
}
