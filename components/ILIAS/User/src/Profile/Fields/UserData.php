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

namespace ILIAS\User\Profile\Fields;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class UserData
{
    public function __construct(
        private readonly int $id,
        private readonly string $alias,
        private readonly ResourceIdentification $avatar_rid,
        private readonly string $firstname,
        private readonly string $lastname,
        private readonly string $title,
        private readonly string $gender,
        private readonly \DateTimeImmutable $birthday,
        private readonly string $institution,
        private readonly string $department,
        private readonly string $street,
        private readonly string $city,
        private readonly string $zipcode,
        private readonly string $country,
        private readonly string $email,
        private readonly string $second_email,
        private readonly string $phone_office,
        private readonly string $phone_home,
        private readonly string $phone_mobile,
        private readonly string $fax,
        private readonly string $matriculation,
        private readonly string $referral_comment,
        private readonly array $geo_coordinates,
        private readonly array $additional_fields
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getAvatarRid(): ResourceIdentification
    {
        return $this->avatar_rid;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function getBirthday(): \DateTimeImmutable
    {
        return $this->birthday;
    }

    public function getInstitution(): string
    {
        return $this->institution;
    }

    public function getDepartment(): string
    {
        return $this->department;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getZipcode(): string
    {
        return $this->zipcode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSecondEmail(): string
    {
        return $this->second_email;
    }

    public function getPhoneOffice(): string
    {
        return $this->phone_office;
    }

    public function getPhoneHome(): string
    {
        return $this->phone_home;
    }

    public function getPhoneMobile(): string
    {
        return $this->phone_mobile;
    }

    public function getFax(): string
    {
        return $this->fax;
    }

    public function getMatriculation(): string
    {
        return $this->matriculation;
    }

    public function getReferralComment(): string
    {
        return $this->referral_comment;
    }

    public function getGeoCoordinates(): array
    {
        return $this->geo_coordinates;
    }

    public function getAdditionalFieldByIdentifier(string $identifier): mixed
    {
        return $this->additional_fields[$identifier] ?? null;
    }
}
