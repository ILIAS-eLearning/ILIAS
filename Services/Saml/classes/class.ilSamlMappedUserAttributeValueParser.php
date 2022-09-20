<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilSamlMappedUserAttributeValueParser
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilSamlMappedUserAttributeValueParser
{
    private const ATTR_REGEX = '/^(.*?)(\|(\d+))?$/';

    /**
     * @param array<string, mixed> $userData
     */
    public function __construct(private ilExternalAuthUserAttributeMappingRule $rule, private array $userData)
    {
    }

    private function getValueIndex(): int
    {
        $index = 0;

        $matches = [];
        preg_match(self::ATTR_REGEX, $this->rule->getExternalAttribute(), $matches);

        if (is_array($matches) && isset($matches[3]) && is_numeric($matches[3])) {
            $index = (int) $matches[3];
        }

        return max($index, 0);
    }

    public function getAttributeKey(): string
    {
        $attribute = '';

        $matches = [];
        preg_match(self::ATTR_REGEX, $this->rule->getExternalAttribute(), $matches);

        if (is_array($matches) && isset($matches[1]) && is_string($matches[1])) {
            $attribute = $matches[1];
        }

        return $attribute;
    }

    public function parse(): string
    {
        $attributeKey = $this->getAttributeKey();

        if (!array_key_exists($attributeKey, $this->userData)) {
            throw new ilSamlException(sprintf(
                "Configured external attribute of mapping '%s' -> '%s' does not exist in SAML attribute data.",
                $this->rule->getAttribute(),
                $this->rule->getExternalAttribute()
            ));
        }

        $value = $this->userData[$attributeKey];

        if (is_array($value)) {
            $valueIndex = $this->getValueIndex();

            if (!array_key_exists($valueIndex, $value)) {
                throw new ilSamlException(sprintf(
                    "Configured external attribute of mapping '%s' -> '%s' does not exist in SAML attribute data.",
                    $this->rule->getAttribute(),
                    $this->rule->getExternalAttribute()
                ));
            }

            $value = $value[$valueIndex];
        }

        if (!is_scalar($value)) {
            throw new ilSamlException(sprintf(
                "Could not parse a scalar value based on the user attribute mapping '%s' -> '%s'.",
                $this->rule->getAttribute(),
                $this->rule->getExternalAttribute()
            ));
        }

        return (string) $value;
    }
}
