<?php

namespace ILIAS\ResourceStorage\Information\Repository;

use ActiveRecord;

/**
 * Class ARInformation
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @deprecated
 */
class ARInformation extends ActiveRecord
{

    /**
     * @inheritDoc
     */
    public function getConnectorContainerName() : string
    {
        return 'il_resource_info';
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
     */
    protected $identification;
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $title;
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     64
     */
    protected $suffix;
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $mime_type;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $size;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $creation_date;

    /**
     * @return string
     */
    public function getInternal() : string
    {
        return $this->internal;
    }

    /**
     * @param string $internal
     * @return ARInformation
     */
    public function setInternal(string $internal) : ARInformation
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
     * @return ARInformation
     */
    public function setIdentification(string $identification) : ARInformation
    {
        $this->identification = $identification;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle() : ?string
    {
        return $this->title ?? '';
    }

    /**
     * @param string $title
     * @return ARInformation
     */
    public function setTitle(string $title) : ARInformation
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuffix() : ?string
    {
        return $this->suffix ?? '';
    }

    /**
     * @param string $suffix
     * @return ARInformation
     */
    public function setSuffix(string $suffix) : ARInformation
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType() : string
    {
        return $this->mime_type ?? '';
    }

    /**
     * @param string $mime_type
     * @return ARInformation
     */
    public function setMimeType(string $mime_type) : ARInformation
    {
        $this->mime_type = $mime_type;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize() : int
    {
        return (int) $this->size;
    }

    /**
     * @param int $size
     * @return ARInformation
     */
    public function setSize(int $size) : ARInformation
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return int
     */
    public function getCreationDate() : int
    {
        return $this->creation_date ?? 0;
    }

    /**
     * @param int $creation_date
     * @return ARInformation
     */
    public function setCreationDate(int $creation_date) : ARInformation
    {
        $this->creation_date = $creation_date;
        return $this;
    }

}
