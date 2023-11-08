<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilSystemFolderSetupConfig implements Setup\Config
{
    /**
     * @var	string|null
     */
    protected $client_name;

    /**
     * @var	string|null
     */
    protected $client_description;

    /**
     * @var	string|null
     */
    protected $client_institution;

    /**
     * @var	string
     */
    protected $contact_firstname;

    /**
     * @var	string
     */
    protected $contact_lastname;

    /**
     * @var	string|null
     */
    protected $contact_title;

    /**
     * @var	string|null
     */
    protected $contact_position;

    /**
     * @var	string|null
     */
    protected $contact_institution;

    /**
     * @var	string|null
     */
    protected $contact_street;

    /**
     * @var	string|null
     */
    protected $contact_zipcode;

    /**
     * @var	string|null
     */
    protected $contact_city;

    /**
     * @var	string|null
     */
    protected $contact_country;

    /**
     * @var	string|null
     */
    protected $contact_phone;

    /**
     * @var	string
     */
    protected $contact_email;

    public function __construct(
        ?string $client_name,
        ?string $client_description,
        ?string $client_institution,
        string $contact_firstname,
        string $contact_lastname,
        ?string $contact_title,
        ?string $contact_position,
        ?string $contact_institution,
        ?string $contact_street,
        ?string $contact_zipcode,
        ?string $contact_city,
        ?string $contact_country,
        ?string $contact_phone,
        string $contact_email
    ) {
        $this->client_name = $client_name ? trim($client_name) : null;
        $this->client_description = $client_description ? trim($client_description) : null;
        $this->client_institution = $client_institution ? trim($client_institution) : null;
        $this->contact_firstname = trim($contact_firstname);
        $this->contact_lastname = trim($contact_lastname);
        $this->contact_title = $contact_title ? trim($contact_title) : null;
        $this->contact_position = $contact_position ? trim($contact_position) : null;
        $this->contact_institution = $contact_institution ? trim($contact_institution) : null;
        $this->contact_street = $contact_street ? trim($contact_street) : null;
        $this->contact_zipcode = $contact_zipcode ? trim($contact_zipcode) : null;
        $this->contact_city = $contact_city ? trim($contact_city) : null;
        $this->contact_country = $contact_country ? trim($contact_country) : null;
        $this->contact_phone = $contact_phone ? trim($contact_phone) : null;
        $this->contact_email = trim($contact_email);
    }

    public function getClientName(): ?string
    {
        return $this->client_name;
    }

    public function getClientDescription(): ?string
    {
        return $this->client_description;
    }

    public function getClientInstitution(): ?string
    {
        return $this->client_institution;
    }

    public function getContactFirstname(): string
    {
        return $this->contact_firstname;
    }

    public function getContactLastname(): string
    {
        return $this->contact_lastname;
    }

    public function getContactTitle(): ?string
    {
        return $this->contact_title;
    }

    public function getContactPosition(): ?string
    {
        return $this->contact_position;
    }

    public function getContactInstitution(): ?string
    {
        return $this->contact_institution;
    }

    public function getContactStreet(): ?string
    {
        return $this->contact_street;
    }

    public function getContactZipcode(): ?string
    {
        return $this->contact_zipcode;
    }

    public function getContactCity(): ?string
    {
        return $this->contact_city;
    }

    public function getContactCountry(): ?string
    {
        return $this->contact_country;
    }

    public function getContactPhone(): ?string
    {
        return $this->contact_phone;
    }

    public function getContactEMail(): string
    {
        return $this->contact_email;
    }
}
