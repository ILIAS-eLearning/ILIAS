<?php

/**
 * Class ilShibbolethSettings
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilShibbolethSettings
{
    /**
     * @var string
     */
    private const PREFIX = 'shib_';
    /**
     * @var string
     */
    private const DEFAULT_IDP_LIST = "urn:mace:organization1:providerID, Example Organization 1\nurn:mace:organization2:providerID, Example Organization 2, /Shibboleth.sso/WAYF/SWITCHaai";
    /**
     * @var string
     */
    private const DEFAULT_LOGIN_BUTTON = "assets/images/auth/shib_login_button.svg";
    /**
     * @var string
     */
    private const DEFAULT_ORGANISATION_SELECTION = "external_wayf";

    public const ACCOUNT_CREATION_ENABLED = "enabled";
    public const ACCOUNT_CREATION_WITH_APPROVAL = "with_approval";
    public const ACCOUNT_CREATION_DISABLED = "disabled";

    protected ilSetting $settings;
    protected array $data = [];

    /** @var array<string, bool> */
    protected array $user_fields = [
        'firstname' => true,
        'lastname' => true,
        'email' => true,
        'title' => false,
        'gender' => false,
        'institution' => false,
        'department' => false,
        'zipcode' => false,
        'city' => false,
        'country' => false,
        'street' => false,
        'phone_office' => false,
        'phone_home' => false,
        'phone_mobile' => false,
        'language' => false,
        'matriculation' => false,
    ];

    public function __construct()
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->read();
    }

    /**
     * @return array<string, bool>
     */
    public function getUserFields(): array
    {
        return $this->user_fields;
    }

    public function read(): void
    {
        $filtered_data = array_filter(
            $this->settings->getAll(),
            static fn($value, string $key): bool => str_starts_with($key, self::PREFIX),
            ARRAY_FILTER_USE_BOTH
        );

        array_walk($filtered_data, function ($v, string $k): void {
            $this->data[str_replace(self::PREFIX, '', $k)] = $v === '' ? null : $v;
        });
    }

    public function get(string $a_keyword, ?string $a_default_value = null): string
    {
        $a_keyword = str_replace(self::PREFIX, '', $a_keyword);

        return (string) ($this->data[$a_keyword] ?? $a_default_value);
    }

    public function getAll(): array
    {
        return $this->data;
    }

    public function set(string $a_key, string $a_val): void
    {
        $a_key = str_replace(self::PREFIX, '', $a_key);
        $this->data[$a_key] = $a_val;
    }

    public function store(): void
    {
        foreach ($this->data as $key => $value) {
            $this->settings->set(self::PREFIX . $key, (string) $value);
        }
    }

    public function getDefaultRole(): int
    {
        return $this->data['user_default_role'] ?? 4;
    }

    public function setDefaultRole(int $role_id): void
    {
        $this->data['user_default_role'] = $role_id;
    }

    public function getIdPList(): string
    {
        return $this->data['idp_list'] ?? self::DEFAULT_IDP_LIST;
    }

    public function setIdPList(string $list): void
    {
        $this->data['idp_list'] = $list;
    }

    public function getLoginButton(): string
    {
        return $this->data['login_button'] ?? self::DEFAULT_LOGIN_BUTTON;
    }

    public function setLoginButton(string $login_button): void
    {
        $this->data['login_button'] = $login_button;
    }

    public function getOrganisationSelectionType(): string
    {
        return $this->data['hos_type'] ?? self::DEFAULT_ORGANISATION_SELECTION;
    }

    public function setOrganisationSelectionType(string $type): void
    {
        $this->data['hos_type'] = $type;
    }

    public function isActive(): bool
    {
        return (bool) ($this->data['active'] ?? false);
    }

    public function setActive(bool $status): void
    {
        $this->data['active'] = $status;
    }

    public function isLocalAuthAllowed(): bool
    {
        return (bool) ($this->data['auth_allow_local'] ?? false);
    }

    public function setAllowLocalAuth(bool $status): void
    {
        $this->data['auth_allow_local'] = $status;
    }

    public function getAccountCreation(): string
    {
        return $this->data['account_creation'] ?? self::ACCOUNT_CREATION_ENABLED;
    }

    public function setAccountCreation(string $value): void
    {
        $this->data['account_creation'] = $value;
    }

    public function getFederationName(): string
    {
        return ($this->data['federation_name'] ?? '');
    }

    public function setFederationName(string $federation): void
    {
        $this->data['federation_name'] = $federation;
    }
}
