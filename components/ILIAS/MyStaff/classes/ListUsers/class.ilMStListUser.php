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

namespace ILIAS\MyStaff\ListUsers;

use ilObjUser;
use ILIAS\Data\Privacy\Purpose\LegacyAccess;
use ILIAS\Data\Privacy\Source\LegacySource;
use ILIAS\Data\Privacy\Types\PostalAddress;
use ILIAS\Data\Privacy\Types\PostalAddressValue;

/**
 * Class ilMStListUser
 * @author Martin Studer <ms@studer-raimann.ch>
 */
final class ilMStListUser
{
    private int $usr_id;
    private string $gender;
    private int $active;
    private string $login;
    private string $title;
    private string $hobby;
    private string $institution;
    private string $department;
    private ?PostalAddress $postal_address = null;
    private string $matriculation;
    private string $firstname;
    private string $lastname;
    private string $email;
    private string $second_email;

    public function getUsrId(): int
    {
        return $this->usr_id;
    }

    public function setUsrId(int $usr_id): void
    {
        $this->usr_id = $usr_id;
    }

    public function getActive(): int
    {
        return $this->active;
    }

    public function setActive(int $active): void
    {
        $this->active = $active;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getSecondEmail(): string
    {
        return $this->second_email;
    }

    public function setSecondEmail(string $second_email): void
    {
        $this->second_email = $second_email;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getHobby(): string
    {
        return $this->hobby;
    }

    public function setHobby(string $hobby): void
    {
        $this->hobby = $hobby;
    }

    public function getInstitution(): string
    {
        return $this->institution;
    }

    public function setInstitution(string $institution): void
    {
        $this->institution = $institution;
    }

    public function getDepartment(): string
    {
        return $this->department;
    }

    public function setDepartment(string $department): void
    {
        $this->department = $department;
    }

    public function getPostalAddress(): ?PostalAddress
    {
        return $this->postal_address;
    }

    public function setPostalAddress(?PostalAddress $postal_address): void
    {
        $this->postal_address = $postal_address;
    }

    /**
     * @deprecated Resolve {@see getPostalAddress()} with a real purpose instead.
     */
    public function getStreet(): string
    {
        return $this->resolveAddressForLegacyGetter()->street;
    }

    /**
     * @deprecated Use {@see setPostalAddress()} with a real source instead.
     */
    public function setStreet(string $street): void
    {
        $this->postal_address = $this->postalAddressForLegacySetter()
            ->withStreet($street, new LegacySource('mst_list_user_setter'));
    }

    /**
     * @deprecated Resolve {@see getPostalAddress()} with a real purpose instead.
     */
    public function getZipcode(): string
    {
        return $this->resolveAddressForLegacyGetter()->zipcode;
    }

    /**
     * @deprecated Use {@see setPostalAddress()} with a real source instead.
     */
    public function setZipcode(string $zipcode): void
    {
        $this->postal_address = $this->postalAddressForLegacySetter()
            ->withZipcode($zipcode, new LegacySource('mst_list_user_setter'));
    }

    /**
     * @deprecated Resolve {@see getPostalAddress()} with a real purpose instead.
     */
    public function getCity(): string
    {
        return $this->resolveAddressForLegacyGetter()->city;
    }

    /**
     * @deprecated Use {@see setPostalAddress()} with a real source instead.
     */
    public function setCity(string $city): void
    {
        $this->postal_address = $this->postalAddressForLegacySetter()
            ->withCity($city, new LegacySource('mst_list_user_setter'));
    }

    /**
     * @deprecated Resolve {@see getPostalAddress()} with a real purpose instead.
     */
    public function getCountry(): string
    {
        return $this->resolveAddressForLegacyGetter()->country;
    }

    /**
     * @deprecated Use {@see setPostalAddress()} with a real source instead.
     */
    public function setCountry(string $country): void
    {
        $this->postal_address = $this->postalAddressForLegacySetter()
            ->withCountry($country, new LegacySource('mst_list_user_setter'));
    }

    private function resolveAddressForLegacyGetter(): PostalAddressValue
    {
        return $this->postal_address?->resolve(new LegacyAccess('mst_list_user_getter'))
            ?? new PostalAddressValue();
    }

    private function postalAddressForLegacySetter(): PostalAddress
    {
        return $this->postal_address
            ?? new PostalAddress(new PostalAddressValue(), new LegacySource('mst_list_user_setter'));
    }

    public function getMatriculation(): string
    {
        return $this->matriculation;
    }

    public function setMatriculation(string $matriculation): void
    {
        $this->matriculation = $matriculation;
    }

    public function returnIlUserObj(): ilObjUser
    {
        return new ilObjUser($this->usr_id);
    }
}
