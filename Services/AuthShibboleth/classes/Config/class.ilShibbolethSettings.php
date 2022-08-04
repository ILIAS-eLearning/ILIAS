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

use ILIAS\Administration\Setting;
use function _PHPStan_e04cc8dfb\RingCentral\Psr7\str;

/**
 * Class ilShibbolethSettings
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilShibbolethSettings
{
    private const PREFIX = 'shib_';
    private const DEFAULT_IDP_LIST = "urn:mace:organization1:providerID, Example Organization 1\nurn:mace:organization2:providerID, Example Organization 2, /Shibboleth.sso/WAYF/SWITCHaai";
    private const DEFAULT_LOGIN_BUTTON = "templates/default/images/shib_login_button.svg";
    private const DEFAULT_ORGANISATION_SELECTION = "external_wayf";

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
    public function getUserFields() : array
    {
        return $this->user_fields;
    }

    public function read() : void
    {
        $filtered_data = array_filter(
            $this->settings->getAll(),
            static fn ($value, string $key) : bool => strpos($key, self::PREFIX) === 0,
            ARRAY_FILTER_USE_BOTH
        );

        array_walk($filtered_data, function ($v, string $k) : void {
            $this->data[str_replace(self::PREFIX, '', $k)] = $v === '' ? null : $v;
        });
    }

    public function get(string $a_keyword, ?string $a_default_value = null) : string
    {
        $a_keyword = str_replace(self::PREFIX, '', $a_keyword);

        return (string) ($this->data[$a_keyword] ?? $a_default_value);
    }
    

    /**
     * @return mixed[]
     */
    public function getAll() : array
    {
        return $this->data;
    }

    public function set(string $a_key, string $a_val) : void
    {
        $a_key = str_replace(self::PREFIX, '', $a_key);
        $this->data[$a_key] = $a_val;
    }

    public function store() : void
    {
        foreach ($this->data as $key => $value) {
            $this->settings->set(self::PREFIX . $key, (string) $value);
        }
    }

    public function getDefaultRole() : int
    {
        return $this->data['user_default_role'] ?? 4;
    }

    public function setDefaultRole(int $role_id) : void
    {
        $this->data['user_default_role'] = $role_id;
    }

    public function getIdPList() : string
    {
        return $this->data['idp_list'] ?? self::DEFAULT_IDP_LIST;
    }

    public function setIdPList(string $list) : void
    {
        $this->data['idp_list'] = $list;
    }

    public function getLoginButton() : string
    {
        return $this->data['login_button'] ?? self::DEFAULT_LOGIN_BUTTON;
    }

    public function setLoginButton(string $login_button) : void
    {
        $this->data['login_button'] = $login_button;
    }

    public function getOrganisationSelectionType() : string
    {
        return $this->data['hos_type'] ?? self::DEFAULT_ORGANISATION_SELECTION;
    }

    public function setOrganisationSelectionType(string $type) : void
    {
        $this->data['hos_type'] = $type;
    }

    public function isActive() : bool
    {
        return (bool) ($this->data['active'] ?? false);
    }

    public function setActive(bool $status) : void
    {
        $this->data['active'] = $status ? '1' : '0';
    }

    public function isLocalAuthAllowed() : bool
    {
        return (bool) ($this->data['auth_allow_local'] ?? false);
    }

    public function setAllowLocalAuth(bool $status) : void
    {
        $this->data['auth_allow_local'] = $status ? '1' : '0';
    }

    public function adminMustActivate() : bool
    {
        return (bool) ($this->data['activate_new'] ?? false);
    }

    public function setAdminMustActivate(bool $status) : void
    {
        $this->data['activate_new'] = $status ? '1' : '0';
    }

    public function getFederationName() : string
    {
        return ($this->data['federation_name'] ?? '');
    }

    public function setFederationName(string $federation) : void
    {
        $this->data['federation_name'] = $federation;
    }
}
