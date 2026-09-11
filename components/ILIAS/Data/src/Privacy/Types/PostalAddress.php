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
 * value never leaves the type, so no purpose is needed and nothing is
 * logged. The given source replaces the current one (last write wins).
 *
 * @extends AbstractPrivacyDataType<PostalAddressValue>
 */
final readonly class PostalAddress extends AbstractPrivacyDataType
{
    public function withStreet(string $street, Source $source): self
    {
        return $this->withValue(
            new PostalAddressValue(
                $street,
                $this->value->city,
                $this->value->zipcode,
                $this->value->country
            ),
            $source
        );
    }

    public function withCity(string $city, Source $source): self
    {
        return $this->withValue(
            new PostalAddressValue(
                $this->value->street,
                $city,
                $this->value->zipcode,
                $this->value->country
            ),
            $source
        );
    }

    public function withZipcode(string $zipcode, Source $source): self
    {
        return $this->withValue(
            new PostalAddressValue(
                $this->value->street,
                $this->value->city,
                $zipcode,
                $this->value->country
            ),
            $source
        );
    }

    public function withCountry(string $country, Source $source): self
    {
        return $this->withValue(
            new PostalAddressValue(
                $this->value->street,
                $this->value->city,
                $this->value->zipcode,
                $country
            ),
            $source
        );
    }
}
