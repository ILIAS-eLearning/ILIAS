<?php

declare(strict_types=1);

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

/**
 * Additional information about a user, used in context of assignments
 */
class ilPRGUserInformation
{
    public const COLNAMES = [
        'firstname',
        'lastname',
        'login',
        'active',
        'email',
        'gender',
        'title',
    ];

    protected ilUserDefinedData $udf;
    protected string $orgu_repr;
    protected string $firstname;
    protected string $lastname;
    protected bool $active;
    protected string $login;
    protected string $email;
    protected string $gender;
    protected string $title;

    public function __construct(
        ilUserDefinedData $udf,
        string $orgu_repr,
        string $firstname,
        string $lastname,
        string $login,
        bool $active,
        string $email,
        string $gender,
        string $title
    ) {
        $this->udf = $udf;
        $this->orgu_repr = $orgu_repr;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->active = $active;
        $this->login = $login;
        $this->email = $email;
        $this->gender = $gender;
        $this->title = $title;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }
    public function getLastname(): string
    {
        return $this->lastname;
    }
    public function isActive(): bool
    {
        return $this->active;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getLogin(): string
    {
        return $this->login;
    }
    public function getOrguRepresentation(): string
    {
        return $this->orgu_repr;
    }
    public function getUdf(string $field)
    {
        return $this->udf->get($field);
    }
    public function getAllUdf(): ilUserDefinedData
    {
        return $this->udf;
    }

    public function getFullname(): string
    {
        return $this->lastname . ', ' . $this->firstname;
    }
    public function getGender(): string
    {
        return $this->gender;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
}
