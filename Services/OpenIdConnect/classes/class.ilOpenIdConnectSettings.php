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
    const FILE_STORAGE = 'openidconnect/login_form_image';
    const STORAGE_ID = 'oidc';
    const DEFAULT_SCOPE = 'openid';

    const LOGIN_ELEMENT_TYPE_TXT = 0;
    const LOGIN_ELEMENT_TYPE_IMG = 1;

    const LOGIN_ENFORCE = 0;
    const LOGIN_STANDARD = 1;

    const LOGOUT_SCOPE_GLOBAL = 0;
    const LOGOUT_SCOPE_LOCAL = 1;

    private static ?ilOpenIdConnectSettings $instance = null;

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

    private array $profile_map = [];

    private array $profile_update_map = [];

    private array $role_mappings = [];

    private array $additional_scopes = [];

    private function __construct()
    {
        global $DIC;

        $this->storage = new ilSetting(self::STORAGE_ID);
        $this->filesystem = $DIC->filesystem()->web();
        $this->load();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance() : \ilOpenIdConnectSettings
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return new self::$instance;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active) : void
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function getActive() : bool
    {
        return $this->active;
    }

    /**
     * @param string $url
     */
    public function setProvider(string $url) : void
    {
        $this->provider = $url;
    }

    /**
     * @return string
     */
    public function getProvider() : string
    {
        return $this->provider;
    }

    /**
     * @param string $client_id
     */
    public function setClientId(string $client_id) : void
    {
        $this->client_id = $client_id;
    }

    /**
     * @return string
     */
    public function getClientId() : string
    {
        return $this->client_id;
    }

    /**
     * @param string $secret
     */
    public function setSecret(string $secret) : void
    {
        $this->secret = $secret;
    }

    /**
     * Get secret
     */
    public function getSecret() : string
    {
        return $this->secret;
    }

    /**
     * Set login element type
     */
    public function setLoginElementType(int $type) : void
    {
        $this->login_element_type = $type;
    }

    /**
     * @return int
     */
    public function getLoginElementType() : int
    {
        return $this->login_element_type;
    }

    /**
     * @param string $a_img_name
     */
    public function setLoginElementImage(string $a_img_name) : void
    {
        $this->login_element_img_name = $a_img_name;
    }

    /**
     * @return string
     */
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

    /**
     * @param int $a_type
     */
    public function setLoginPromptType(int $a_type) : void
    {
        $this->login_prompt_type = $a_type;
    }

    /**
     * @return int
     */
    public function getLoginPromptType() : int
    {
        return $this->login_prompt_type;
    }

    /**
     * @param int $a_scope
     */
    public function setLogoutScope(int $a_scope) : void
    {
        $this->logout_scope = $a_scope;
    }

    /**
     * @return int
     */
    public function getLogoutScope() : int
    {
        return $this->logout_scope;
    }

    /**
     * @param bool $a_stat
     */
    public function useCustomSession(bool $a_stat) : void
    {
        $this->custom_session = $a_stat;
    }

    /**
     * @return bool
     */
    public function isCustomSession() : bool
    {
        return $this->custom_session;
    }

    /**
     * @param int $a_duration
     */
    public function setSessionDuration(int $a_duration) : void
    {
        $this->session_duration = $a_duration;
    }

    /**
     * @return int
     */
    public function getSessionDuration() : int
    {
        return $this->session_duration;
    }

    /**
     * @return bool
     */
    public function isSyncAllowed() : bool
    {
        return $this->allow_sync;
    }

    /**
     * @param bool $a_stat
     */
    public function allowSync(bool $a_stat) : void
    {
        $this->allow_sync = $a_stat;
    }

    /**
     * @param int $role
     */
    public function setRole(int $role) : void
    {
        $this->role = $role;
    }

    /**
     * @return int
     */
    public function getRole() : int
    {
        return $this->role;
    }

    /**
     * @param string $field
     */
    public function setUidField(string $field) : void
    {
        $this->uid = $field;
    }

    /**
     * @return string
     */
    public function getUidField() : string
    {
        return $this->uid;
    }

    /**
     * @return array
     */
    public function getAdditionalScopes() : array
    {
        return $this->additional_scopes;
    }

    /**
     * @param array $additional_scopes
     */
    public function setAdditionalScopes(array $additional_scopes) : void
    {
        $this->additional_scopes = $additional_scopes;
    }

    /**
     * @return array
     */
    public function getAllScopes() : array
    {
        $scopes = $this->additional_scopes;
        array_unshift($scopes, self::DEFAULT_SCOPE);

        return $scopes;
    }

    /**
     * Delete image file
     *
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function deleteImageFile() : void
    {
        if ($this->filesystem->has(self::FILE_STORAGE . '/' . $this->getLoginElementImage())) {
            $this->filesystem->delete(self::FILE_STORAGE . '/' . $this->getLoginElementImage());
        }
    }

    /**
     * @return bool
     */
    public function hasImageFile() : bool
    {
        return
            strlen($this->getLoginElementImage()) &&
            $this->filesystem->has(self::FILE_STORAGE . '/' . $this->getLoginElementImage());
    }

    /**
     * @return string
     */
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
     * @param array $a_role_mappings
     */
    public function setRoleMappings(array $a_role_mappings) : void
    {
        $this->role_mappings = $a_role_mappings;
    }

    /**
     * Get role mappings
     */
    public function getRoleMappings() : array
    {
        return (array) $this->role_mappings;
    }

    public function getRoleMappingValueForId($a_role_id) : string
    {
        if (
            isset($this->role_mappings[$a_role_id]) &&
            isset($this->role_mappings[$a_role_id]['value'])
        ) {
            return (string) $this->role_mappings[$a_role_id]['value'];
        }
        return '';
    }

    public function getRoleMappingUpdateForId($a_role_id) : bool
    {
        if (
            isset($this->role_mappings[$a_role_id]) &&
            isset($this->role_mappings[$a_role_id]['update'])
        ) {
            return (bool) $this->role_mappings[$a_role_id]['update'];
        }
        return false;
    }

    public function validateScopes(string $provider, array $custom_scopes) : array
    {
        try {
            $curl = new ilCurlConnection($provider . '/.well-known/openid-configuration');
            $curl->init();

            $curl->setOpt(CURLOPT_HEADER, 0);
            $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
            $curl->setOpt(CURLOPT_TIMEOUT, 4);

            $response = json_decode($curl->exec());

            if ($curl->getInfo(CURLINFO_RESPONSE_CODE) !== 200) {
                return array();
            }

            $available_scopes = $response->scopes_supported;
            array_unshift($custom_scopes, self::DEFAULT_SCOPE);

            $result = array_diff($custom_scopes, $available_scopes);
        } catch (ilCurlConnectionException $e) {
            throw $e;
        } finally {
            $curl->close();
        }

        //TODO PHP8-REVIEW: Variable '$result' is probably undefined
        return $result;
    }

    /**
     * Save in settings
     */
    public function save() : void
    {
        $this->storage->set('active', $this->getActive() ? '1' : '0');
        $this->storage->set('provider', $this->getProvider());
        $this->storage->set('client_id', $this->getClientId());
        $this->storage->set('secret', $this->getSecret());
        $this->storage->set('scopes', (string) serialize($this->getAdditionalScopes()));
        $this->storage->set('le_img', $this->getLoginElementImage());
        $this->storage->set('le_text', $this->getLoginElemenText());
        $this->storage->set('le_type', (string) $this->getLoginElementType());
        $this->storage->set('prompt_type', (string) $this->getLoginPromptType());
        $this->storage->set('logout_scope', (string) $this->getLogoutScope());
        $this->storage->set('custom_session', (string) $this->isCustomSession());
        $this->storage->set('session_duration', (string) $this->getSessionDuration());
        $this->storage->set('allow_sync', (string) $this->isSyncAllowed());
        $this->storage->set('role', (string) $this->getRole());
        $this->storage->set('uid', (string) $this->getUidField());

        foreach ($this->getProfileMappingFields() as $field => $lang_key) {
            $this->storage->set('pmap_' . $field, $this->getProfileMappingFieldValue($field));
            $this->storage->set('pumap_' . $field, $this->getProfileMappingFieldUpdate($field) ? '1' : '0');
        }
        $this->storage->set('role_mappings', (string) serialize($this->getRoleMappings()));
    }

    /**
     * Load from settings
     */
    protected function load() : void
    {
        foreach ($this->getProfileMappingFields() as $field => $lang_key) {
            $this->profile_map[$field] = (string) $this->storage->get('pmap_' . $field, '');
            $this->profile_update_map[$field] = (bool) $this->storage->get('pumap_' . $field, '');
        }

        $this->setActive((bool) $this->storage->get('active', (string) 0));
        $this->setProvider($this->storage->get('provider', ''));
        $this->setClientId($this->storage->get('client_id', ''));
        $this->setSecret($this->storage->get('secret', ''));
        $this->setAdditionalScopes((array) unserialize($this->storage->get('scopes', serialize([]))));
        $this->setLoginElementImage($this->storage->get('le_img', ''));
        $this->setLoginElementText((string) $this->storage->get('le_text'));
        $this->setLoginElementType((int) $this->storage->get('le_type'));
        $this->setLoginPromptType((int) $this->storage->get('prompt_type', (string) self::LOGIN_ENFORCE));
        $this->setLogoutScope((int) $this->storage->get('logout_scope', (string) self::LOGOUT_SCOPE_GLOBAL));
        $this->useCustomSession((bool) $this->storage->get('custom_session'), (string) false);
        $this->setSessionDuration((int) $this->storage->get('session_duration', (string) 60));
        $this->allowSync((bool) $this->storage->get('allow_sync'), (string) false);
        $this->setRole((int) $this->storage->get('role'), (string) 0);
        $this->setUidField((string) $this->storage->get('uid'), '');
        $this->setRoleMappings((array) unserialize($this->storage->get('role_mappings', serialize([]))));
    }

    public function getProfileMappingFieldValue(string $field) : string
    {
        return (string) $this->profile_map[$field];
    }

    public function setProfileMappingFieldValue(string $field, string $value) : void
    {
        $this->profile_map[$field] = $value;
    }

    public function getProfileMappingFieldUpdate(string $field) : bool
    {
        return (bool) $this->profile_update_map[$field];
    }

    public function setProfileMappingFieldUpdate(string $field, bool $value) : void
    {
        $this->profile_update_map[$field] = $value;
    }

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
