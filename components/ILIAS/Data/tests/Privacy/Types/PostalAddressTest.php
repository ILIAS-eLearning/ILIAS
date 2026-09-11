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

use ILIAS\Data\Privacy\Fixtures\InMemoryPrivacyLogger;
use ILIAS\Data\Privacy\Fixtures\PrivacyDataTypeAssertions;
use ILIAS\Data\Privacy\Purpose\DisplayToUser;
use ILIAS\Data\Privacy\Purpose\StoreInTable;
use ILIAS\Data\Privacy\Source\DbTableColumns;
use ILIAS\Data\Privacy\Source\UserInput;
use PHPUnit\Framework\TestCase;

class PostalAddressTest extends TestCase
{
    use PrivacyDataTypeAssertions;

    private InMemoryPrivacyLogger $logger;
    private PostalAddress $address;

    protected function setUp(): void
    {
        $this->logger = new InMemoryPrivacyLogger();
        $this->address = new PostalAddress(
            new PostalAddressValue('Mainstreet 5', 'Berne', '3011', 'CH'),
            new DbTableColumns('usr_data', 'street', 'city', 'zipcode', 'country'),
            $this->logger
        );
    }

    public function testResolveReturnsValueAndLogs(): void
    {
        $value = $this->address->resolve(new DisplayToUser('public_profile'));

        $this->assertSame('Mainstreet 5', $value->street);
        $this->assertSame('Berne', $value->city);
        $this->assertSame('3011', $value->zipcode);
        $this->assertSame('CH', $value->country);

        $this->logger->assertLoggedOnce();
        $this->logger->assertLastSourceIs('usr_data.(street,city,zipcode,country)');
    }

    public function testResolveWithStoreInTableLogsTarget(): void
    {
        $this->address->resolve(
            new StoreInTable(new DbTableColumns('usr_data', 'street', 'city', 'zipcode', 'country'))
        );

        $this->logger->assertLastPurposeIs('store_in:usr_data.(street,city,zipcode,country)');
    }

    public function testWithersReplaceSingleFieldsWithoutLogging(): void
    {
        $changed = $this->address
            ->withStreet('Sidestreet 7', new UserInput('profile_form'))
            ->withCity('Zurich', new UserInput('profile_form'))
            ->withZipcode('8001', new UserInput('profile_form'))
            ->withCountry('DE', new UserInput('profile_form'));

        $this->logger->assertNothingLogged();

        $value = $changed->resolve(new DisplayToUser('test'));
        $this->assertSame('Sidestreet 7', $value->street);
        $this->assertSame('Zurich', $value->city);
        $this->assertSame('8001', $value->zipcode);
        $this->assertSame('DE', $value->country);
    }

    public function testWitherReplacesSourceAndKeepsLogger(): void
    {
        $changed = $this->address->withStreet('Sidestreet 7', new UserInput('profile_form'));

        $this->assertSame('user_input:profile_form', $changed->getSource()->describe());

        $changed->resolve(new DisplayToUser('test'));
        $this->logger->assertLoggedOnce();
        $this->logger->assertLastSourceIs('user_input:profile_form');
    }

    public function testWitherDoesNotMutateOriginal(): void
    {
        $this->address->withStreet('Sidestreet 7', new UserInput('profile_form'));

        $value = $this->address->resolve(new DisplayToUser('test'));
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
}
