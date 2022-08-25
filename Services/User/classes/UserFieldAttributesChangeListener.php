<?php

declare(strict_types=1);

namespace ILIAS\Services\User;

use ILIAS\DI\Container;
use ilLanguage;

abstract class UserFieldAttributesChangeListener
{
    protected ilLanguage $lng;
    protected Container $dic;

    public function __construct(Container $dic)
    {
        $this->dic = $dic;
        $this->lng = $dic->language();
    }

    /**
     * Should return a description for a user profile field if the listener is interested in a change of a field attribute.
     * Returning null or an empty string will skip the listener.
     * @param string $fieldName
     * @param string $attribute
     * @return string|null
     */
    abstract public function getDescriptionForField(string $fieldName, string $attribute): ?string;

    /**
     * Should return the component name like it would be used to raise an event
     * @return string
     * @example "Services/Mail"
     */
    abstract public function getComponentName(): string;
}
