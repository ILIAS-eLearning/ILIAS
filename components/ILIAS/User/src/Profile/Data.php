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

use ILIAS\User\Profile\Fields\Standard\Genders;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class Data
{
    private array $system_information = [];

    public function __construct(
        private ?int $id = null,
        private string $alias = '',
        private ?ResourceIdentification $avatar_rid = null,
        private string $firstname = '',
        private string $lastname = '',
        private string $title = '',
        private ?Genders $gender = null,
        private ?\DateTimeImmutable $birthday = null,
        private string $institution = '',
        private string $department = '',
        private string $street = '',
        private string $city = '',
        private string $zipcode = '',
        private string $country = '',
        private string $email = '',
        private ?string $second_email = null,
        private string $phone_office = '',
        private string $phone_home = '',
        private string $phone_mobile = '',
        private string $fax = '',
        private string $matriculation = '',
        private string $hobby = '',
        private string $referral_comment = '',
        private array $geo_coordinates = [],
        private array $additional_fields = []
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function withAlias(string $alias): self
    {
        $clone = clone $this;
        $clone->alias = $alias;
        return $clone;
    }

    public function getAvatarRid(): ?ResourceIdentification
    {
        return $this->avatar_rid;
    }

    public function withAvatarRid(?ResourceIdentification $avatar_rid): self
    {
        $clone = clone $this;
        $clone->avatar_rid = $avatar_rid;
        return $clone;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function withFirstname(string $firstname): self
    {
        $clone = clone $this;
        $clone->firstname = $firstname;
        return $clone;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function withLastname(string $lastname): self
    {
        $clone = clone $this;
        $clone->lastname = $lastname;
        return $clone;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function withTitle(string $title): self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getGender(): ?Genders
    {
        return $this->gender;
    }

    public function withGender(?Genders $gender): self
    {
        $clone = clone $this;
        $clone->gender = $gender;
        return $clone;
    }

    public function getBirthday(): ?\DateTimeImmutable
    {
        return $this->birthday;
    }

    public function withBirthday(?\DateTimeImmutable $birthday): self
    {
        $clone = clone $this;
        $clone->birthday = $birthday;
        return $clone;
    }

    public function getInstitution(): string
    {
        return $this->institution;
    }

    public function withInstitution(string $institution): self
    {
        $clone = clone $this;
        $clone->institution = $institution;
        return $clone;
    }

    public function getDepartment(): string
    {
        return $this->department;
    }

    public function withDepartment(string $department): self
    {
        $clone = clone $this;
        $clone->department = $department;
        return $clone;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function withStreet(string $street): self
    {
        $clone = clone $this;
        $clone->street = $street;
        return $clone;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function withCity(string $city): self
    {
        $clone = clone $this;
        $clone->city = $city;
        return $clone;
    }

    public function getZipcode(): string
    {
        return $this->zipcode;
    }

    public function withZipcode(string $zipcode): self
    {
        $clone = clone $this;
        $clone->zipcode = $zipcode;
        return $clone;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function withCountry(string $country): self
    {
        $clone = clone $this;
        $clone->country = $country;
        return $clone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function withEmail(string $email): self
    {
        $clone = clone $this;
        $clone->email = $email;
        return $clone;
    }

    public function getSecondEmail(): ?string
    {
        return $this->second_email;
    }

    public function withSecondEmail(?string $email): self
    {
        $clone = clone $this;
        $clone->second_email = $email;
        return $clone;
    }

    public function getPhoneOffice(): string
    {
        return $this->phone_office;
    }

    public function withPhoneOffice(string $phone): self
    {
        $clone = clone $this;
        $clone->phone_office = $phone;
        return $clone;
    }

    public function getPhoneHome(): string
    {
        return $this->phone_home;
    }

    public function withPhoneHome(string $phone): self
    {
        $clone = clone $this;
        $clone->phone_home = $phone;
        return $clone;
    }

    public function getPhoneMobile(): string
    {
        return $this->phone_mobile;
    }

    public function withPhoneMobile(string $phone): self
    {
        $clone = clone $this;
        $clone->phone_mobile = $phone;
        return $clone;
    }

    public function getFax(): string
    {
        return $this->fax;
    }

    public function withFax(string $fax): self
    {
        $clone = clone $this;
        $clone->fax = $fax;
        return $clone;
    }

    public function getMatriculation(): string
    {
        return $this->matriculation;
    }

    public function withMatriculation(string $matriculation): self
    {
        $clone = clone $this;
        $clone->matriculation = $matriculation;
        return $clone;
    }

    public function getHobby(): string
    {
        return $this->hobby;
    }

    public function withHobby(string $hobby): self
    {
        $clone = clone $this;
        $clone->hobby = $hobby;
        return $clone;
    }

    public function getReferralComment(): string
    {
        return $this->referral_comment;
    }

    public function withReferralComment(string $comment): self
    {
        $clone = clone $this;
        $clone->referral_comment = $comment;
        return $clone;
    }

    public function getGeoCoordinates(): array
    {
        return $this->geo_coordinates;
    }

    public function withGeoCoordinates(array $coordinates): self
    {
        $clone = clone $this;
        $clone->geo_coordinates = $coordinates;
        return $clone;
    }

    public function getAdditionalFieldByIdentifier(string $identifier): mixed
    {
        return $this->additional_fields[$identifier] ?? null;
    }

    public function withAdditionalFieldByIdentifier(string $identifier, mixed $value): self
    {
        $clone = clone $this;
        $clone->additional_fields[$identifier] = $value;
        return $clone;
    }

    public function getAdditionalFieldsStorageValues(\ilDBInterface $db): string
    {
        return rtrim(
            array_reduce(
                array_keys($this->additional_fields),
                fn(string $c, string $field_id) => $c . array_reduce(
                    $this->additional_fields[$field_id],
                    fn(string $ci, string $value) => $ci . "({$db->quote($this->id, \ilDBConstants::T_INTEGER)}, "
                    . "{$db->quote($field_id, \ilDBConstants::T_TEXT)}, {$db->quote($value, \ilDBConstants::T_TEXT)}),",
                    ''
                ),
                ''
            ),
            ','
        );
    }

    public function getSystemInformation(): array
    {
        return $this->system_information;
    }

    public function withSystemInformation(array $system_information): self
    {
        $clone = clone $this;
        $clone->system_information = $system_information;
        return $clone;
    }
}
