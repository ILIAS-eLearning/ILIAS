<?php

namespace ILIAS\ResourceStorage\Stakeholder\Repository;

use ActiveRecord;

/**
 * Class ARStakeholder
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @deprecated
 */
class ARStakeholder extends ActiveRecord
{

    /**
     * @inheritDoc
     */
    public function getConnectorContainerName() : string
    {
        return 'il_resource_stakeh';
    }

    /**
     * @var string
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $internal;
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     * @con_index      true
     */
    protected $identification;
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     * @con_index      true
     */
    protected $stakeholder_id;
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $stakeholder_class;

    /**
     * @return string
     */
    public function getInternal() : string
    {
        return $this->internal;
    }

    /**
     * @param string $internal
     * @return ARStakeholder
     */
    public function setInternal(string $internal) : ARStakeholder
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
     * @return ARStakeholder
     */
    public function setIdentification(string $identification) : ARStakeholder
    {
        $this->identification = $identification;
        return $this;
    }

    /**
     * @return string
     */
    public function getStakeholderId() : string
    {
        return $this->stakeholder_id;
    }

    /**
     * @param string $stakeholder_id
     * @return ARStakeholder
     */
    public function setStakeholderId(string $stakeholder_id) : ARStakeholder
    {
        $this->stakeholder_id = $stakeholder_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getStakeholderClass() : string
    {
        return $this->stakeholder_class;
    }

    /**
     * @param string $stakeholder_class
     * @return ARStakeholder
     */
    public function setStakeholderClass(string $stakeholder_class) : ARStakeholder
    {
        $this->stakeholder_class = $stakeholder_class;
        return $this;
    }

}
