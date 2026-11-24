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

use ILIAS\Data\Privacy\Factory;
use ILIAS\Data\Privacy\Source\UserInput;
use ILIAS\Data\Privacy\TechnicalRequirement\TechnicalRequirements;
use ILIAS\Data\Privacy\Types\PostalAddressValue;
use ILIAS\User\Profile\Data;
use PHPUnit\Framework\TestCase;

/**
 * What a map shows of a person, depending on what the person has published.
 */
class ilMapGUIPersonDataTest extends TestCase
{
    private function gui(): ilMapGUIPersonDataTestDouble
    {
        return new ilMapGUIPersonDataTestDouble();
    }

    /**
     * @param list<string> $published
     */
    private function user(array $published): ilObjUser
    {
        $data = new Data(
            alias: 'ada',
            firstname: 'Ada',
            lastname: '<Lovelace>',
            institution: 'ETH',
            department: 'CS',
            postal_address: new Factory()->postalAddress(
                new PostalAddressValue('Main 5', 'Berne', '3011', 'CH'),
                new UserInput('test')
            ),
            geo_coordinates: ['latitude' => '46.9', 'longitude' => '7.4', 'zoom' => 12]
        );
        $user = $this->createStub(ilObjUser::class);
        $user->method('getProfileData')->willReturn($data);
        $user->method('getPref')->willReturnCallback(
            static fn(string $key): string => in_array(substr($key, strlen('public_')), $published, true) ? 'y' : 'n'
        );
        return $user;
    }

    public function testOnlyTheNameWithoutPublishedFields(): void
    {
        $this->assertSame('Ada &lt;Lovelace&gt;', $this->gui()->marker($this->user([])));
    }

    public function testPublishedFieldsWithTheirDelimiters(): void
    {
        $this->assertSame(
            'Ada &lt;Lovelace&gt;<br />ETH, CS<br />Main 5<br />3011 Berne<br />CH',
            $this->gui()->marker($this->user(['institution', 'department', 'street', 'zipcode', 'city', 'country']))
        );
        $this->assertSame('Ada &lt;Lovelace&gt;<br />CS<br />Berne', $this->gui()->marker($this->user(['department', 'city'])));
    }

    public function testTheLocationOnlyIfPublished(): void
    {
        $this->assertNull($this->gui()->location($this->user([])));
        $this->assertSame('7.4', $this->gui()->location($this->user(['location']))?->longitude);
    }

    public function testTheLoginForTheList(): void
    {
        $this->assertSame('ada', $this->gui()->login($this->user([])));
    }
}

class ilMapGUIPersonDataTestDouble extends ilMapGUI
{
    public function __construct()
    {
        $this->privacy_requirements = new TechnicalRequirements();
    }

    public function getHtml(): string
    {
        return '';
    }

    public function getUserListHtml(): string
    {
        return '';
    }

    public function marker(ilObjUser $user): string
    {
        return $this->userMarkerInfo($user);
    }

    public function location(ilObjUser $user): ?\ILIAS\Data\Privacy\Types\GeoLocationValue
    {
        return $this->userMarkerLocation($user);
    }

    public function login(ilObjUser $user): string
    {
        return $this->userListLogin($user);
    }
}
