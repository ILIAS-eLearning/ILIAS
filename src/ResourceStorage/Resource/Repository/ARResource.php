<?php

namespace ILIAS\ResourceStorage\Resource\Repository;

use ActiveRecord;

/**
 * Class ARResource
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @deprecated
 */
class ARResource extends ActiveRecord
{

    /**
     * @inheritDoc
     */
    public function getConnectorContainerName() : string
    {
        return 'il_resource';
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
    protected $identification;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     8
     */
    protected $storage_id;


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
     * @return ARResource
     */
    public function setIdentification(string $identification) : ARResource
    {
        $this->identification = $identification;

        return $this;
    }


    /**
     * @return string
     */
    public function getStorageId() : string
    {
        return $this->storage_id;
    }


    /**
     * @param string $storage_id
     *
     * @return ARResource
     */
    public function setStorageId(string $storage_id) : ARResource
    {
        $this->storage_id = $storage_id;

        return $this;
    }
}
