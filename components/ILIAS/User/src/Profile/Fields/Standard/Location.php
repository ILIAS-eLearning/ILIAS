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

namespace ILIAS\User\Profile\Fields\Standard;

use ILIAS\User\Context;
use ILIAS\User\Profile\Fields\NoOverrides;
use ILIAS\User\Profile\Fields\FieldDefinition;
use ILIAS\User\Profile\Fields\AvailableSections;
use ILIAS\Language\Language;

class Location implements FieldDefinition
{
    use NoOverrides;

    public function getIdentifier(): string
    {
        return 'location';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt($this->getIdentifier());
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::PersonalData;
    }

    public function hiddenInLists(): bool
    {
        return true;
    }

    public function visibleInCoursesForcedTo(): ?bool
    {
        return false;
    }

    public function visibleInGroupsForcedTo(): ?bool
    {
        return false;
    }

    public function visibleInStudyProgrammesForcedTo(): ?bool
    {
        return false;
    }

    public function requiredForcedTo(): ?bool
    {
        return false;
    }

    public function searchableForcedTo(): ?bool
    {
        return false;
    }

    public function availableInCertificatesForcedTo(): ?bool
    {
        return false;
    }

    public function getLegacyInput(
        Language $lng,
        Context $context,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $latitude = !empty($user?->getLatitude())
            ? (float) $user->getLatitude()
            : null;
        $longitude = !empty($user?->getLongitude())
            ? (float) $user->getLongitude()
            : null;
        $zoom = $user?->getLocationZoom();

        if ($latitude === null && $longitude === null && $zoom === 0) {
            $def = \ilMapUtil::getDefaultSettings();
            $latitude = (float) $def['latitude'];
            $longitude = (float) $def['longitude'];
            $zoom = (int) $def['zoom'];
        }

        $street = $user?->getStreet() ?? '';
        if ($street === '') {
            $street = $lng->txt('street');
        }
        $city = $user?->getCity() ?? '';
        if ($city === '') {
            $city = $lng->txt('city');
        }
        $country = $user?->getCountry() ?? '';
        if ($country === '') {
            $country = $lng->txt('country');
        }

        $loc_prop = new \ilLocationInputGUI(
            $lng->txt('location'),
            'location'
        );
        $loc_prop->setLatitude($latitude);
        $loc_prop->setLongitude($longitude);
        $loc_prop->setZoom($zoom);
        $loc_prop->setAddress($street . ', ' . $city . ', ' . $country);
        return $loc_prop;
    }

    public function addValueToUserObject(
        \ilObjUser $user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        if (($input['latitude'] ?? 0.0) !== 0.0) {
            $user->setLatitude((string) $input['latitude']);
        }
        if (($input['longitude'] ?? 0.0) !== 0.0) {
            $user->setLongitude((string) $input['longitude']);
        }

        $user->setLocationZoom($input['zoom'] ?? null);

        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): array
    {
        return  [
            'latitude' => $user->getLatitude(),
            'longitude' => $user->getLongitude(),
            'zoom' => $user?->getLocationZoom()
        ];
    }
}
