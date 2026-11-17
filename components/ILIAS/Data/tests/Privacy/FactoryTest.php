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

use ILIAS\Data\Privacy\Source\UserInput;
use ILIAS\Data\Privacy\Types\PostalAddress;
use ILIAS\Data\Privacy\Types\PostalAddressValue;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testPostalAddressCreation(): void
    {
        $factory = new Factory();

        $address = $factory->postalAddress(
            new PostalAddressValue('Mainstreet 5', 'Berne', '3011', 'CH'),
            new UserInput('profile_form')
        );

        $this->assertInstanceOf(PostalAddress::class, $address);
        $this->assertSame('user_input:profile_form', $address->getSource()->describe());
    }


    public function testFurtherTypes(): void
    {
        $factory = new Factory();
        $source = new UserInput('profile_form');
        $requirement = new \ILIAS\Data\Privacy\TechnicalRequirement\DisplayToUser('on a page', 'the tester', 'test it');

        $name = $factory->personName(new \ILIAS\Data\Privacy\Types\PersonNameValue('Ada', 'Lovelace', 'Dr.'), $source);
        $this->assertSame('Lovelace', $name->resolve($requirement)->lastname);
        $this->assertStringNotContainsString('Lovelace', (string) $name);

        $affiliation = $factory->affiliation(new \ILIAS\Data\Privacy\Types\AffiliationValue('ETH', 'CS'), $source);
        $this->assertSame('CS', $affiliation->resolve($requirement)->department);

        $location = $factory->geoLocation(new \ILIAS\Data\Privacy\Types\GeoLocationValue('46.9', '7.4', 12), $source);
        $this->assertSame(12, $location->resolve($requirement)->zoom);

        $login = $factory->login('ada', $source);
        $this->assertSame('ada', $login->resolve($requirement));
        $this->assertStringNotContainsString('ada', (string) $login);
    }
}
