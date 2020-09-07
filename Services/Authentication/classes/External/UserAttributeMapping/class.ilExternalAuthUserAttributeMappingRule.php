<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilExternalAuthUserAttributeMappingRule
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilExternalAuthUserAttributeMappingRule
{
    /**
     * @var string
     */
    protected $attribute = '';

    /**
     * @var string
     */
    protected $external_attribute = '';

    /**
     * @var bool
     */
    protected $update_automatically = false;

    /**
     * @return string
     */
    public function getExternalAttribute()
    {
        return $this->external_attribute;
    }

    /**
     * @param string $external_attribute
     */
    public function setExternalAttribute($external_attribute)
    {
        $this->external_attribute = $external_attribute;
    }

    /**
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param string $attribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * @return boolean
     */
    public function isAutomaticallyUpdated()
    {
        return $this->update_automatically;
    }

    /**
     * @param boolean $update_automatically
     */
    public function updateAutomatically($update_automatically)
    {
        $this->update_automatically = $update_automatically;
    }
}
