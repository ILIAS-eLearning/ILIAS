<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilExternalAuthUserAttributeMappingRule
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilExternalAuthUserAttributeMappingRule
{
    protected string $attribute = '';
    protected string $external_attribute = '';
    protected bool $update_automatically = false;

    public function getExternalAttribute() : string
    {
        return $this->external_attribute;
    }

    public function setExternalAttribute(string $external_attribute) : void
    {
        $this->external_attribute = $external_attribute;
    }

    public function getAttribute() : string
    {
        return $this->attribute;
    }

    public function setAttribute(string $attribute) : void
    {
        $this->attribute = $attribute;
    }

    public function isAutomaticallyUpdated() : bool
    {
        return $this->update_automatically;
    }

    public function updateAutomatically(bool $update_automatically) : void
    {
        $this->update_automatically = $update_automatically;
    }
}
