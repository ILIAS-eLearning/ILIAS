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

use ILIAS\Data\Privacy\Fixtures\PrivacyDataTypeAssertions;
use ILIAS\Data\Privacy\TechnicalRequirement\DisplayToUser;
use ILIAS\Data\Privacy\Source\Sources;
use ILIAS\Data\Privacy\Source\UserInput;
use PHPUnit\Framework\TestCase;

class PostalAddressTest extends TestCase
{
    use PrivacyDataTypeAssertions;

    private PostalAddress $address;

    protected function setUp(): void
    {
        $this->address = new PostalAddress(
            new PostalAddressValue('Mainstreet 5', 'Berne', '3011', 'CH'),
            new Sources()->user()->postalAddress()
        );
    }

    public function testResolveReturnsValue(): void
    {
        $value = $this->address->resolve(new DisplayToUser('on a page', 'the tester', 'test it'));

        $this->assertSame('Mainstreet 5', $value->street);
        $this->assertSame('Berne', $value->city);
        $this->assertSame('3011', $value->zipcode);
        $this->assertSame('CH', $value->country);
    }

    public function testWithersReplaceSingleFields(): void
    {
        $changed = $this->address
            ->withStreet('Sidestreet 7', new UserInput('profile_form'))
            ->withCity('Zurich', new UserInput('profile_form'))
            ->withZipcode('8001', new UserInput('profile_form'))
            ->withCountry('DE', new UserInput('profile_form'));

        $value = $changed->resolve(new DisplayToUser('on a page', 'the tester', 'test it'));
        $this->assertSame('Sidestreet 7', $value->street);
        $this->assertSame('Zurich', $value->city);
        $this->assertSame('8001', $value->zipcode);
        $this->assertSame('DE', $value->country);
    }

    public function testWitherReplacesSource(): void
    {
        $changed = $this->address->withStreet('Sidestreet 7', new UserInput('profile_form'));

        $this->assertSame('user_input:profile_form', $changed->getSource()->describe());

    }

    public function testWitherDoesNotMutateOriginal(): void
    {
        $this->address->withStreet('Sidestreet 7', new UserInput('profile_form'));

        $value = $this->address->resolve(new DisplayToUser('on a page', 'the tester', 'test it'));
        $this->assertSame('Mainstreet 5', $value->street);
        $this->assertSame(
            'usr_data.(street,city,zipcode,country)',
            $this->address->getSource()->describe()
        );
    }

    public function testToStringMasksAllFields(): void
    {
        $this->assertToStringDoesNotExposeValue($this->address, 'Mainstreet 5');
        $this->assertToStringDoesNotExposeValue($this->address, 'Berne');
        $this->assertToStringDoesNotExposeValue($this->address, '3011');
        $this->assertToStringDoesNotExposeValue($this->address, 'CH');
        $this->assertStringContainsString(PostalAddress::class, (string) $this->address);
    }

    public function testWithFieldsOfTakesOnlyTheGivenFields(): void
    {
        $other = new PostalAddress(
            new PostalAddressValue('Importstreet 1', 'Basel', '4001', 'DE'),
            new UserInput('import')
        );

        $changed = $this->address->withFieldsOf($other, ['street', 'country'], new UserInput('xml_import'));

        $value = $changed->resolve(new DisplayToUser('on a page', 'the tester', 'test it'));
        $this->assertSame('Importstreet 1', $value->street);
        $this->assertSame('Berne', $value->city);
        $this->assertSame('3011', $value->zipcode);
        $this->assertSame('DE', $value->country);
        $this->assertSame('user_input:xml_import', $changed->getSource()->describe());
    }

    public function testWithFieldsOfRejectsUnknownFields(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->address->withFieldsOf($this->address, ['email'], new UserInput('import'));
    }
}
