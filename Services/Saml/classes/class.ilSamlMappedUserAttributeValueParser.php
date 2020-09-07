<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlMappedUserAttributeValueParser
 */
class ilSamlMappedUserAttributeValueParser
{
    const ATTR_REGEX = '/^(.*?)(\|(\d+))?$/';
    
    /**
     * @var \ilExternalAuthUserAttributeMappingRule
     */
    protected $rule;

    /**
     * @var array
     */
    protected $userData = [];

    /**
     * ilSamlMappedUserAttributeValueParser constructor.
     * @param ilExternalAuthUserAttributeMappingRule $rule
     * @param array                                  $userData
     */
    public function __construct(\ilExternalAuthUserAttributeMappingRule $rule, array $userData)
    {
        $this->rule = $rule;
        $this->userData = $userData;
    }

    /**
     * @return int
     */
    protected function getValueIndex()
    {
        $index = 0;

        $matches = array();
        preg_match(self::ATTR_REGEX, $this->rule->getExternalAttribute(), $matches);

        if (is_array($matches) && isset($matches[3]) && is_numeric($matches[3])) {
            $index = (int) $matches[3];
        }

        return $index >= 0 ? $index : 0;
    }

    /**
     * @return string
     */
    public function getAttributeKey()
    {
        $attribute = '';

        $matches = array();
        preg_match(self::ATTR_REGEX, $this->rule->getExternalAttribute(), $matches);

        if (is_array($matches) && isset($matches[1]) && is_string($matches[1])) {
            $attribute = $matches[1];
        }

        return $attribute;
    }

    /**
     * @throws \ilSamlException
     * @return mixed
     */
    public function parse()
    {
        $attributeKey = $this->getAttributeKey();

        if (!array_key_exists($attributeKey, $this->userData)) {
            throw new \ilSamlException(sprintf(
                "Configured external attribute of mapping '%s' -> '%s' does not exist in SAML attribute data.",
                $this->rule->getAttribute(),
                $this->rule->getExternalAttribute()
            ));
        }

        $value = $this->userData[$attributeKey];

        if (is_array($value)) {
            $valueIndex = $this->getValueIndex();

            if (!array_key_exists($valueIndex, $value)) {
                throw new \ilSamlException(sprintf(
                    "Configured external attribute of mapping '%s' -> '%s' does not exist in SAML attribute data.",
                    $this->rule->getAttribute(),
                    $this->rule->getExternalAttribute()
                ));
            }

            $value = $value[$valueIndex];
        }

        if (!is_scalar($value)) {
            throw new \ilSamlException(sprintf(
                "Could not parse a scalar value based on the user attribute mapping '%s' -> '%s'.",
                $this->rule->getAttribute(),
                $this->rule->getExternalAttribute()
            ));
        }

        return $value;
    }
}
