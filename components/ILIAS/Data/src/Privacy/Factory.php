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

namespace ILIAS\Data\Privacy;

use ILIAS\Data\Privacy\Source\Source;
use ILIAS\Data\Privacy\Types\Affiliation;
use ILIAS\Data\Privacy\Types\AffiliationValue;
use ILIAS\Data\Privacy\Types\GeoLocation;
use ILIAS\Data\Privacy\Types\GeoLocationValue;
use ILIAS\Data\Privacy\Types\Login;
use ILIAS\Data\Privacy\Types\PersonName;
use ILIAS\Data\Privacy\Types\PersonNameValue;
use ILIAS\Data\Privacy\Types\PostalAddress;
use ILIAS\Data\Privacy\Types\PostalAddressValue;

/**
 * Creates privacy data types.
 *
 * A stateless factory: create it with `new` or inject it.
 */
readonly class Factory
{
    public function postalAddress(PostalAddressValue $value, Source $source): PostalAddress
    {
        return new PostalAddress($value, $source);
    }

    public function personName(PersonNameValue $value, Source $source): PersonName
    {
        return new PersonName($value, $source);
    }

    public function affiliation(AffiliationValue $value, Source $source): Affiliation
    {
        return new Affiliation($value, $source);
    }

    public function geoLocation(GeoLocationValue $value, Source $source): GeoLocation
    {
        return new GeoLocation($value, $source);
    }

    public function login(string $login, Source $source): Login
    {
        return new Login($login, $source);
    }
}
