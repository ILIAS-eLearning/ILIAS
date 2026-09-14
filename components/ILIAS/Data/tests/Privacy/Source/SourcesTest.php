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

namespace ILIAS\Data\Privacy\Source;

use PHPUnit\Framework\TestCase;

class SourcesTest extends TestCase
{
    public function testDbTableColumn(): void
    {
        $source = new DbTableColumn('usr_data', 'street');

        $this->assertSame('usr_data', $source->getTable());
        $this->assertSame('street', $source->getColumn());
        $this->assertSame('usr_data.street', $source->describe());
    }

    public function testDbTableColumns(): void
    {
        $source = new DbTableColumns('usr_data', 'street', 'city', 'zipcode', 'country');

        $this->assertSame('usr_data', $source->getTable());
        $this->assertSame(['street', 'city', 'zipcode', 'country'], $source->getColumns());
        $this->assertSame('usr_data.(street,city,zipcode,country)', $source->describe());
    }

    public function testDbTableColumnsRequiresAtLeastOneColumn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DbTableColumns('usr_data');
    }

    public function testUserInput(): void
    {
        $source = new UserInput('registration_form');

        $this->assertSame('registration_form', $source->getContext());
        $this->assertSame('user_input:registration_form', $source->describe());
    }

    public function testExternalApi(): void
    {
        $source = new ExternalApi('shibboleth', 'homePostalAddress');

        $this->assertSame('shibboleth', $source->getService());
        $this->assertSame('homePostalAddress', $source->getField());
        $this->assertSame('api:shibboleth.homePostalAddress', $source->describe());
    }

    public function testSessionData(): void
    {
        $source = new SessionData('user_id');

        $this->assertSame('user_id', $source->getKey());
        $this->assertSame('session:user_id', $source->describe());
    }

    public function testLegacySource(): void
    {
        $this->assertSame('legacy:unclassified', new LegacySource()->describe());
        $source = new LegacySource('ilObjUser::setStreet');
        $this->assertSame('ilObjUser::setStreet', $source->getHint());
        $this->assertSame('legacy:ilObjUser::setStreet', $source->describe());
    }

    public function testFactoryBuildsAllSources(): void
    {
        $sources = new Sources();

        $this->assertSame('user_input:profile_form', $sources->userInput('profile_form')->describe());
        $this->assertSame('api:shibboleth.street', $sources->externalApi('shibboleth', 'street')->describe());
        $this->assertSame('session:user_id', $sources->sessionData('user_id')->describe());
        $this->assertSame('legacy:some_setter', $sources->legacy('some_setter')->describe());
        $this->assertSame('legacy:unclassified', $sources->legacy()->describe());
    }

    public function testFactoryInheritsKnownSourcesCatalogue(): void
    {
        $this->assertSame(
            'usr_data.(street,city,zipcode,country)',
            new Sources()->user()->postalAddress()->describe()
        );
    }
}
