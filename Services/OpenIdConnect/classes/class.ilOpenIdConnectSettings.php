<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */

use ILIAS\Filesystem\Filesystem;

class ilOpenIdConnectSettings
{
    private const STORAGE_ID = 'oidc';

    public const FILE_STORAGE = 'openidconnect/login_form_image';
    public const DEFAULT_SCOPE = 'openid';
    public const LOGIN_ELEMENT_TYPE_TXT = 0;
    public const LOGIN_ELEMENT_TYPE_IMG = 1;
    public const LOGIN_ENFORCE = 0;
    public const LOGIN_STANDARD = 1;
    public const LOGOUT_SCOPE_GLOBAL = 0;
    public const LOGOUT_SCOPE_LOCAL = 1;

    private static ?self $instance = null;

    private ilSetting $storage;
    private Filesystem $filesystem;
    private bool $active = false;
    private string $provider = '';
    private string $client_id = '';
    private string $secret = '';
    private int $login_element_type = self::LOGIN_ELEMENT_TYPE_TXT;
    private ?string $login_element_img_name = null;
    private ?string $login_element_text = null;
    private int $login_prompt_type = self::LOGIN_ENFORCE;
    private ?int $logout_scope = null;
    private bool $custom_session = false;
    private int $session_duration = 60;
    private ?bool $allow_sync;
    private ?int $role;
    private string $uid = '';
    /** @var array<string, string> */
    private array $profile_map = [];
    /** @var array<string, bool> */
    private array $profile_update_map = [];
    /** @var array<int, array{value: string, update: bool}> */
    private array $role_mappings = [];
    /** @var string[] */
    private array $additional_scopes = [];

    private function __construct()
    {
        global $DIC;

        $this->storage = new ilSetting(self::STORAGE_ID);
        $this->filesystem = $DIC->filesystem()->web();
        $this->load();
    }

    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function setActive(bool $active) : void
    {
        $this->active = $active;
    }

    public function getActive() : bool
    {
        return $this->active;
    }

    public function setProvider(string $url) : void
    {
        $this->provider = $url;
    }

    public function getProvider() : string
    {
        return $this->provider;
    }

    public function setClientId(string $client_id) : void
    {
        $this->client_id = $client_id;
    }

    public function getClientId() : string
    {
        return $this->client_id;
    }

    public function setSecret(string $secret) : void
    {
        $this->secret = $secret;
    }

    public function getSecret() : string
    {
        return $this->secret;
    }

    public function setLoginElementType(int $type) : void
    {
        $this->login_element_type = $type;
    }

    public function getLoginElementType() : int
    {
        return $this->login_element_type;
    }

    public function setLoginElementImage(string $a_img_name) : void
    {
        $this->login_element_img_name = $a_img_name;
    }

    public function getLoginElementImage() : string
    {
        return $this->login_element_img_name;
    }

    public function setLoginElementText(string $text) : void
    {
        $this->login_element_text = $text;
    }


    public function getLoginElemenText() : string
    {
        return $this->login_element_text;
    }

    public function setLoginPromptType(int $a_type) : void
    {
        $this->login_prompt_type = $a_type;
    }

    public function getLoginPromptType() : int
    {
        return $this->login_prompt_type;
    }

    public function setLogoutScope(int $a_scope) : void
    {
        $this->logout_scope = $a_scope;
    }

    public function getLogoutScope() : int
    {
        return $this->logout_scope;
    }

    public function useCustomSession(bool $a_stat) : void
    {
        $this->custom_session = $a_stat;
    }

    public function isCustomSession() : bool
    {
        return $this->custom_session;
    }

    public function setSessionDuration(int $a_duration) : void
    {
        $this->session_duration = $a_duration;
    }

    public function getSessionDuration() : int
    {
        return $this->session_duration;
    }

    public function isSyncAllowed() : bool
    {
        return $this->allow_sync;
    }

    public function allowSync(bool $a_stat) : void
    {
        $this->allow_sync = $a_stat;
    }

    public function setRole(int $role) : void
    {
        $this->role = $role;
    }

    public function getRole() : int
    {
        return $this->role;
    }

    public function setUidField(string $field) : void
    {
        $this->uid = $field;
    }

    public function getUidField() : string
    {
        return $this->uid;
    }

    /**
     * @return string[]
     */
    public function getAdditionalScopes() : array
    {
        return $this->additional_scopes;
    }

    /**
     * @param string[] $additional_scopes
     * @return void
     */
    public function setAdditionalScopes(array $additional_scopes) : void
    {
        $this->additional_scopes = $additional_scopes;
    }

    /**
     * @return string[]
     */
    public function getAllScopes() : array
    {
        $scopes = $this->additional_scopes;
        array_unshift($scopes, self::DEFAULT_SCOPE);

        return $scopes;
    }

    /**
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function deleteImageFile() : void
    {
        if ($this->filesystem->has(self::FILE_STORAGE . '/' . $this->getLoginElementImage())) {
            $this->filesystem->delete(self::FILE_STORAGE . '/' . $this->getLoginElementImage());
        }
    }

    public function hasImageFile() : bool
    {
        return
            $this->getLoginElementImage() !== '' &&
            $this->filesystem->has(self::FILE_STORAGE . '/' . $this->getLoginElementImage());
    }

    public function getImageFilePath() : string
    {
        return implode(
            '/',
            [
                ilFileUtils::getWebspaceDir(),
                self::FILE_STORAGE . '/' . $this->getLoginElementImage()
            ]
        );
    }

    /**
     * @param array<int, array{value: string, update: bool}> $a_role_mappings
     */
    public function setRoleMappings(array $a_role_mappings) : void
    {
        $this->role_mappings = $a_role_mappings;
    }

    /**
     * @return array<int, array{value: string, update: bool}>
     */
    public function getRoleMappings() : array
    {
        return $this->role_mappings;
    }

    public function getRoleMappingValueForId(int $a_role_id) : string
    {
        if (isset($this->role_mappings[$a_role_id]['value'])) {
            return (string) $this->role_mappings[$a_role_id]['value'];
        }

        return '';
    }

    public function getRoleMappingUpdateForId(int $a_role_id) : bool
    {
        if (isset($this->role_mappings[$a_role_id]['update'])) {
            return (bool) $this->role_mappings[$a_role_id]['update'];
        }

        return false;
    }

    public function validateScopes(string $provider, array $custom_scopes) : array
    {
        $result = [];
        try {
            $curl = new ilCurlConnection($provider . '/.well-known/openid-configuration');
            $curl->init();

            $curl->setOpt(CURLOPT_HEADER, 0);
            $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
            $curl->setOpt(CURLOPT_TIMEOUT, 4);

            $response = json_decode($curl->exec(), false, 512, JSON_THROW_ON_ERROR);

            if ($curl->getInfo(CURLINFO_RESPONSE_CODE) === 200) {
                $available_scopes = $response->scopes_supported;
                array_unshift($custom_scopes, self::DEFAULT_SCOPE);

                $result = array_diff($custom_scopes, $available_scopes);
            }
        } finally {
            if (isset($curl)) {
                $curl->close();
            }
        }
        return $result;
    }

    public function save() : void
    {
        $this->storage->set('active', (string) ((int) $this->getActive()));
        $this->storage->set('provider', $this->getProvider());
        $this->storage->set('client_id', $this->getClientId());
        $this->storage->set('secret', $this->getSecret());
        $this->storage->set('scopes', serialize($this->getAdditionalScopes()));
        $this->storage->set('le_img', $this->getLoginElementImage());
        $this->storage->set('le_text', $this->getLoginElemenText());
        $this->storage->set('le_type', (string) $this->getLoginElementType());
        $this->storage->set('prompt_type', (string) $this->getLoginPromptType());
        $this->storage->set('logout_scope', (string) $this->getLogoutScope());
        $this->storage->set('custom_session', (string) ((int) $this->isCustomSession()));
        $this->storage->set('session_duration', (string) $this->getSessionDuration());
        $this->storage->set('allow_sync', (string) ((int) $this->isSyncAllowed()));
        $this->storage->set('role', (string) $this->getRole());
        $this->storage->set('uid', $this->getUidField());

        foreach ($this->getProfileMappingFields() as $field => $lang_key) {
            $this->storage->set('pmap_' . $field, $this->getProfileMappingFieldValue($field));
            $this->storage->set('pumap_' . $field, (string) ((int) $this->getProfileMappingFieldUpdate($field)));
        }
        $this->storage->set('role_mappings', serialize($this->getRoleMappings()));
    }

    protected function load() : void
    {
        foreach ($this->getProfileMappingFields() as $field => $lang_key) {
            $this->profile_map[$field] = (string) $this->storage->get('pmap_' . $field, '');
            $this->profile_update_map[$field] = (bool) $this->storage->get('pumap_' . $field, '0');
        }

        $this->setActive((bool) $this->storage->get('active', '0'));
        $this->setProvider($this->storage->get('provider', ''));
        $this->setClientId($this->storage->get('client_id', ''));
        $this->setSecret($this->storage->get('secret', ''));
        $this->setAdditionalScopes((array) unserialize(
            $this->storage->get('scopes', serialize([])),
            ['allowed_classes' => false]
        ));
        $this->setLoginElementImage($this->storage->get('le_img', ''));
        $this->setLoginElementText((string) $this->storage->get('le_text'));
        $this->setLoginElementType((int) $this->storage->get('le_type'));
        $this->setLoginPromptType((int) $this->storage->get('prompt_type', (string) self::LOGIN_ENFORCE));
        $this->setLogoutScope((int) $this->storage->get('logout_scope', (string) self::LOGOUT_SCOPE_GLOBAL));
        $this->useCustomSession((bool) $this->storage->get('custom_session', '0'));
        $this->setSessionDuration((int) $this->storage->get('session_duration', '60'));
        $this->allowSync((bool) $this->storage->get('allow_sync', '0'));
        $this->setRole((int) $this->storage->get('role', '0'));
        $this->setUidField((string) $this->storage->get('uid', ''));
        $this->setRoleMappings((array) unserialize(
            $this->storage->get('role_mappings', serialize([])),
            ['allowed_classes' => false]
        ));
    }

    public function getProfileMappingFieldValue(string $field) : string
    {
        return (string) ($this->profile_map[$field] ?? '');
    }

    public function setProfileMappingFieldValue(string $field, string $value) : void
    {
        $this->profile_map[$field] = $value;
    }

    public function getProfileMappingFieldUpdate(string $field) : bool
    {
        return (bool) ($this->profile_update_map[$field] ?? false);
    }

    public function setProfileMappingFieldUpdate(string $field, bool $value) : void
    {
        $this->profile_update_map[$field] = $value;
    }

    /**
     * @return array<string, string>
     */
    public function getProfileMappingFields() : array
    {
        return [
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'email' => 'email',
            'birthday' => 'birthday'
        ];
    }
}
