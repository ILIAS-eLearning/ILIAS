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

namespace ILIAS\Data\Privacy\Source\Known;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class KnownSourcesTest extends TestCase
{
    public function testUserSourcesInstanceIsCached(): void
    {
        $sources = new KnownSources();

        $this->assertSame($sources->user(), $sources->user());
    }

    public function testPostalAddressIsCompound(): void
    {
        $source = new KnownSources()->user()->postalAddress();

        $this->assertSame('usr_data', $source->getTable());
        $this->assertSame(['street', 'city', 'zipcode', 'country'], $source->getColumns());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function userColumnProvider(): array
    {
        return [
            'userId' => ['userId', 'usr_data.usr_id'],
            'login' => ['login', 'usr_data.login'],
            'externalAccount' => ['externalAccount', 'usr_data.ext_account'],
            'email' => ['email', 'usr_data.email'],
            'secondEmail' => ['secondEmail', 'usr_data.second_email'],
            'phoneOffice' => ['phoneOffice', 'usr_data.phone_office'],
            'phoneHome' => ['phoneHome', 'usr_data.phone_home'],
            'phoneMobile' => ['phoneMobile', 'usr_data.phone_mobile'],
            'fax' => ['fax', 'usr_data.fax'],
            'firstname' => ['firstname', 'usr_data.firstname'],
            'lastname' => ['lastname', 'usr_data.lastname'],
            'title' => ['title', 'usr_data.title'],
            'gender' => ['gender', 'usr_data.gender'],
            'institution' => ['institution', 'usr_data.institution'],
            'department' => ['department', 'usr_data.department'],
            'street' => ['street', 'usr_data.street'],
            'city' => ['city', 'usr_data.city'],
            'zipcode' => ['zipcode', 'usr_data.zipcode'],
            'country' => ['country', 'usr_data.country'],
            'birthday' => ['birthday', 'usr_data.birthday'],
            'hobby' => ['hobby', 'usr_data.hobby'],
            'matriculation' => ['matriculation', 'usr_data.matriculation'],
            'clientIp' => ['clientIp', 'usr_data.client_ip'],
            'lastLogin' => ['lastLogin', 'usr_data.last_login'],
            'lastPasswordChange' => ['lastPasswordChange', 'usr_data.last_password_change'],
        ];
    }

    #[DataProvider('userColumnProvider')]
    public function testUserColumns(string $getter, string $expected_description): void
    {
        $this->assertSame(
            $expected_description,
            new KnownSources()->user()->$getter()->describe()
        );
    }
}
