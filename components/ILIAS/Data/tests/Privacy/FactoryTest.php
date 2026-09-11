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

use ILIAS\Data\Privacy\Fixtures\InMemoryPrivacyLogger;
use ILIAS\Data\Privacy\Logger\CompositeLogger;
use ILIAS\Data\Privacy\Purpose\DisplayToUser;
use ILIAS\Data\Privacy\Purpose\Purposes;
use ILIAS\Data\Privacy\Source\Sources;
use ILIAS\Data\Privacy\Source\UserInput;
use ILIAS\Data\Privacy\Types\PostalAddress;
use ILIAS\Data\Privacy\Types\PostalAddressValue;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testPostalAddressCreation(): void
    {
        $factory = new Factory(new CompositeLogger([]));

        $address = $factory->postalAddress(
            new PostalAddressValue('Mainstreet 5', 'Berne', '3011', 'CH'),
            new UserInput('profile_form')
        );

        $this->assertInstanceOf(PostalAddress::class, $address);
        $this->assertSame('user_input:profile_form', $address->getSource()->describe());
    }

    public function testCreatedTypesLogToTheBoundLogger(): void
    {
        $logger = new InMemoryPrivacyLogger();
        $factory = new Factory($logger);

        $address = $factory->postalAddress(
            new PostalAddressValue('Mainstreet 5', 'Berne', '3011', 'CH'),
            new UserInput('profile_form')
        );
        $address->resolve(new DisplayToUser('public_profile'));

        $logger->assertLoggedOnce();
        $logger->assertLastPurposeIs('display_to_user:public_profile');
        $logger->assertLastDataTypeIs(PostalAddress::class);
    }

    public function testServicesExposeFactorySourcesAndPurposes(): void
    {
        $services = new ServicesImpl(new CompositeLogger([]), new Sources(), new Purposes());

        $this->assertSame($services->factory(), $services->factory());
        $this->assertSame($services->sources(), $services->sources());
        $this->assertSame($services->purposes(), $services->purposes());
        $this->assertSame(
            'usr_data.(street,city,zipcode,country)',
            $services->sources()->user()->postalAddress()->describe()
        );
    }
}
