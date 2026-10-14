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

namespace ILIAS\User\Profile;

use ILIAS\Data\Privacy\TechnicalRequirement\DisplayToUser;
use PHPUnit\Framework\TestCase;

class DataPrivacyTest extends TestCase
{
    public function testWrapsProfileFieldsWhenRead(): void
    {
        $data = new Data(
            alias: 'ada',
            firstname: 'Ada',
            lastname: 'Lovelace',
            title: 'Dr.',
            institution: 'ETH',
            department: 'CS',
            geo_coordinates: ['latitude' => '46.9', 'longitude' => '7.4', 'zoom' => 12]
        );
        $requirement = new DisplayToUser('on a page', 'the tester', 'test it');

        $this->assertSame('ada', $data->getLogin()->resolve($requirement));
        $this->assertSame('usr_data.login', $data->getLogin()->getSource()->describe());
        $this->assertSame('Dr.', $data->getPersonName()->resolve($requirement)->title);
        $this->assertSame('CS', $data->getAffiliation()->resolve($requirement)->department);
        $this->assertSame('7.4', $data->getGeoLocation()?->resolve($requirement)->longitude);
        $this->assertSame(
            'usr_data.(latitude,longitude,loc_zoom)',
            $data->getGeoLocation()->getSource()->describe()
        );
    }

    public function testNoGeoLocationWithoutCoordinates(): void
    {
        $this->assertNull(new Data()->getGeoLocation());
        $this->assertNull(new Data(geo_coordinates: ['latitude' => '46.9', 'longitude' => '0'])->getGeoLocation());
    }

    public function testTheProfileIsABundleOfItsTypedFields(): void
    {
        $data = new Data(alias: 'ada', geo_coordinates: ['latitude' => '46.9', 'longitude' => '7.4']);

        $this->assertSame(
            ['login', 'name', 'affiliation', 'postal_address', 'geo_location'],
            array_keys(iterator_to_array($data->parts()))
        );
        foreach ($data->parts() as $part) {
            $this->assertInstanceOf(\ILIAS\Data\Privacy\PrivacyDataType::class, $part);
        }
    }
}
