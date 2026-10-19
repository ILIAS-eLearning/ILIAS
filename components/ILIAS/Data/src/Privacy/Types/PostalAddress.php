<?php

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

declare(strict_types=1);

namespace ILIAS\Data\Privacy\Types;

use ILIAS\Data\Privacy\AbstractPrivacyDataType;
use ILIAS\Data\Privacy\Source\Source;

/**
 * A user's postal address as protected personal data.
 *
 * The wither methods transform single fields without resolving: the raw
 * value never leaves the type, so no technical requirement is needed.
 *
 * The source describes the value as a whole and is replaced by every
 * wither (last write wins): after `withStreet(..., $ldap)`, the address
 * counts as coming from LDAP, also for fields LDAP did not set.
 *
 * @extends AbstractPrivacyDataType<PostalAddressValue>
 */
readonly class PostalAddress extends AbstractPrivacyDataType
{
    public static function label(): string
    {
        return 'the postal address';
    }

    public const array FIELDS = ['street', 'city', 'zipcode', 'country'];

    /**
     * Takes the given fields from another postal address. Neither address
     * is resolved: copying a value between two wrappers is not disclosure.
     *
     * @param list<value-of<self::FIELDS>> $fields
     */
    public function withFieldsOf(PostalAddress $other, array $fields, Source $source): self
    {
        return $this->withFields(
            array_intersect_key((array) $other->value, array_flip($fields)),
            $fields,
            $source
        );
    }

    public function withStreet(string $street, Source $source): self
    {
        return $this->withFields(['street' => $street], ['street'], $source);
    }

    public function withCity(string $city, Source $source): self
    {
        return $this->withFields(['city' => $city], ['city'], $source);
    }

    public function withZipcode(string $zipcode, Source $source): self
    {
        return $this->withFields(['zipcode' => $zipcode], ['zipcode'], $source);
    }

    public function withCountry(string $country, Source $source): self
    {
        return $this->withFields(['country' => $country], ['country'], $source);
    }

    /**
     * @param array<string, string> $values
     * @param list<string>          $fields
     */
    private function withFields(array $values, array $fields, Source $source): self
    {
        $unknown = array_diff($fields, self::FIELDS);
        if ($unknown !== []) {
            throw new \InvalidArgumentException('Unknown postal address field(s): ' . implode(', ', $unknown));
        }

        return $this->withValue(
            new PostalAddressValue(...array_merge((array) $this->value, $values)),
            $source
        );
    }
}
