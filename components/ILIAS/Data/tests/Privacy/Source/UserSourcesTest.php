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

use ILIAS\Data\Privacy\Source\Sources;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UserSourcesTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function userColumnProvider(): array
    {
        return [
            'postalAddress' => ['postalAddress', 'usr_data.(street,city,zipcode,country)'],
            'personName' => ['personName', 'usr_data.(firstname,lastname,title)'],
            'affiliation' => ['affiliation', 'usr_data.(institution,department)'],
            'geoLocation' => ['geoLocation', 'usr_data.(latitude,longitude,loc_zoom)'],
            'login' => ['login', 'usr_data.login'],
        ];
    }

    #[DataProvider('userColumnProvider')]
    public function testUserColumns(string $getter, string $expected_description): void
    {
        $this->assertSame($expected_description, new Sources()->user()->$getter()->describe());
    }
}
