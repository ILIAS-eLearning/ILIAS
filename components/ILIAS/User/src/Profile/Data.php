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
use ILIAS\Data\Privacy\TechnicalRequirement\LegacyAccess;
use ILIAS\Data\Privacy\Source\LegacySource;
use ILIAS\Data\Privacy\Factory as PrivacyFactory;
use ILIAS\Data\Privacy\PrivacyDataBundle;
use ILIAS\Data\Privacy\Source\Known\UserSources;
use ILIAS\Data\Privacy\Types\Affiliation;
use ILIAS\Data\Privacy\Types\AffiliationValue;
use ILIAS\Data\Privacy\Types\GeoLocation;
use ILIAS\Data\Privacy\Types\GeoLocationValue;
use ILIAS\Data\Privacy\Types\Login;
use ILIAS\Data\Privacy\Types\PersonName;
use ILIAS\Data\Privacy\Types\PersonNameValue;
use ILIAS\Data\Privacy\Types\PostalAddress;
use ILIAS\Data\Privacy\Types\PostalAddressValue;

/**
 * The profile of a user. As privacy data bundle, it groups the profile
 * fields that are privacy data types so far; the remaining fields join as
 * they get a type.
 */
class Data implements PrivacyDataBundle
{
    private array $system_information = [];
    private PostalAddress $postal_address;

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
        ?PostalAddress $postal_address = null,
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
        $this->postal_address = $postal_address ?? new PrivacyFactory()->postalAddress(
            new PostalAddressValue(),
            new UserSources()->postalAddress()
        );
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

    public function getPostalAddress(): PostalAddress
    {
        return $this->postal_address;
    }

    public function withPostalAddress(PostalAddress $postal_address): self
    {
        $clone = clone $this;
        $clone->postal_address = $postal_address;
        return $clone;
    }

    /**
     * @deprecated Resolve {@see getPostalAddress()} with a real requirement instead.
     */
    public function getStreet(): string
    {
        return $this->resolveAddressForLegacyGetter()->street;
    }

    /**
     * @deprecated Derive {@see getPostalAddress()} with a real source and use {@see withPostalAddress()} instead.
     */
    public function withStreet(string $street): self
    {
        return $this->withPostalAddress(
            $this->postal_address->withStreet($street, new LegacySource('profile_data_wither'))
        );
    }

    /**
     * @deprecated Resolve {@see getPostalAddress()} with a real requirement instead.
     */
    public function getCity(): string
    {
        return $this->resolveAddressForLegacyGetter()->city;
    }

    /**
     * @deprecated Derive {@see getPostalAddress()} with a real source and use {@see withPostalAddress()} instead.
     */
    public function withCity(string $city): self
    {
        return $this->withPostalAddress(
            $this->postal_address->withCity($city, new LegacySource('profile_data_wither'))
        );
    }

    /**
     * @deprecated Resolve {@see getPostalAddress()} with a real requirement instead.
     */
    public function getZipcode(): string
    {
        return $this->resolveAddressForLegacyGetter()->zipcode;
    }

    /**
     * @deprecated Derive {@see getPostalAddress()} with a real source and use {@see withPostalAddress()} instead.
     */
    public function withZipcode(string $zipcode): self
    {
        return $this->withPostalAddress(
            $this->postal_address->withZipcode($zipcode, new LegacySource('profile_data_wither'))
        );
    }

    /**
     * @deprecated Resolve {@see getPostalAddress()} with a real requirement instead.
     */
    public function getCountry(): string
    {
        return $this->resolveAddressForLegacyGetter()->country;
    }

    /**
     * @deprecated Derive {@see getPostalAddress()} with a real source and use {@see withPostalAddress()} instead.
     */
    public function withCountry(string $country): self
    {
        return $this->withPostalAddress(
            $this->postal_address->withCountry($country, new LegacySource('profile_data_wither'))
        );
    }

    private function resolveAddressForLegacyGetter(): PostalAddressValue
    {
        return $this->postal_address->resolve(new LegacyAccess('profile_data_getter'));
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

    public function parts(): iterable
    {
        yield 'login' => $this->getLogin();
        yield 'name' => $this->getPersonName();
        yield 'affiliation' => $this->getAffiliation();
        yield 'postal_address' => $this->postal_address;
        $geo_location = $this->getGeoLocation();
        if ($geo_location !== null) {
            yield 'geo_location' => $geo_location;
        }
    }

    /**
     * The login as privacy data type. Wrapped when read; the string
     * accessors stay until all consumers are migrated.
     */
    public function getLogin(): Login
    {
        return new PrivacyFactory()->login($this->alias, new UserSources()->login());
    }

    /**
     * The name as privacy data type. Wrapped when read; the string
     * accessors stay until all consumers are migrated.
     */
    public function getPersonName(): PersonName
    {
        return new PrivacyFactory()->personName(
            new PersonNameValue($this->firstname, $this->lastname, $this->title),
            new UserSources()->personName()
        );
    }

    /**
     * Institution and department as privacy data type. Wrapped when read;
     * the string accessors stay until all consumers are migrated.
     */
    public function getAffiliation(): Affiliation
    {
        return new PrivacyFactory()->affiliation(
            new AffiliationValue($this->institution, $this->department),
            new UserSources()->affiliation()
        );
    }

    /**
     * The location the person entered, or null if there is none. Wrapped
     * when read; the coordinate accessors stay until all consumers are
     * migrated.
     */
    public function getGeoLocation(): ?GeoLocation
    {
        $latitude = (string) ($this->geo_coordinates['latitude'] ?? '');
        $longitude = (string) ($this->geo_coordinates['longitude'] ?? '');
        if ((float) $latitude === 0.0 || (float) $longitude === 0.0) {
            return null;
        }

        return new PrivacyFactory()->geoLocation(
            new GeoLocationValue($latitude, $longitude, (int) ($this->geo_coordinates['zoom'] ?? 0)),
            new UserSources()->geoLocation()
        );
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

    public function withAdditionalFieldByIdentifier(string $identifier, array $value): self
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
                    fn(string $ci, ?string $value) => $value === null
                        ? $ci
                        : $ci . "({$db->quote($this->id, \ilDBConstants::T_INTEGER)}," . PHP_EOL
                            . "{$db->quote($field_id, \ilDBConstants::T_TEXT)}," . PHP_EOL
                            . "{$db->quote($value, \ilDBConstants::T_TEXT)}),",
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
