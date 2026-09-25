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

/**
 * Logs in the user of a launch: celtic/lti checks the launch, this class creates or updates the ILIAS
 * account of the user, gives it the roles of the platform and of the released object, and remembers the
 * launch in the session for the LTI view.
 *
 * A user of a platform has the authentication mode lti_<platform id>. The name and the static methods are
 * fixed: ilAuthProviderFactory and ilAuthUtils use them.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilAuthProviderLTI extends ilAuthProvider
{
    private const string AUTH_MODE_PREFIX = 'lti_';

    public static function getAuthModeByKey(string $a_auth_key): string
    {
        $parts = explode('_', $a_auth_key);

        return count($parts) > 1 ? self::AUTH_MODE_PREFIX . $parts[1] : 'lti';
    }

    public static function getKeyByAuthMode(string $a_auth_mode): string
    {
        $parts = explode('_', $a_auth_mode);

        return count($parts) > 1 ? ilAuthUtils::AUTH_PROVIDER_LTI . '_' . $parts[1] : (string) ilAuthUtils::AUTH_PROVIDER_LTI;
    }

    /**
     * @return array the ids of the active platforms that have launched or may launch ILIAS
     */
    public static function getActiveAuthModes(): array
    {
        return self::lookupPlatformIds(true);
    }

    /**
     * @return array the ids of all platforms that have launched or may launch ILIAS
     */
    public static function getAuthModes(): array
    {
        return self::lookupPlatformIds(false);
    }

    public static function lookupConsumer(int $a_sid): string
    {
        global $DIC;

        $db = $DIC->database();
        $row = $db->fetchAssoc($db->queryF('SELECT title FROM lti_ext_consumer WHERE id = %s', ['integer'], [$a_sid]));

        return ($row['title'] ?? '') . ' (ID ' . $a_sid . ')';
    }

    public static function getServerIdByAuthMode(string $a_auth_mode): ?int
    {
        return self::isAuthModeLTI($a_auth_mode) ? (int) explode('_', $a_auth_mode)[1] : null;
    }

    public static function isAuthModeLTI(string $a_auth_mode): bool
    {
        $parts = explode('_', $a_auth_mode);

        return count($parts) > 1 && $parts[0] === (string) ilAuthUtils::AUTH_PROVIDER_LTI && $parts[1] !== '';
    }

    public function doAuthentication(ilAuthStatus $status): bool
    {
        $receiver = new ilLTILaunchReceiver(new ilLTIDataConnector());
        $receiver->receive();
        $parameters = $receiver->getMessageParameters();
        if (!$receiver->ok || $parameters === null || $parameters === []) {
            return $this->handleAuthenticationFail($status, 'empty_lti_message_parameters');
        }

        $release = $this->lookupRelease((int) $receiver->platform->getRecordId(), $parameters);
        if ($release === null || $release['ref_id'] === 0) {
            return $this->handleAuthenticationFail($status, 'lti_auth_failed_invalid_key');
        }
        if (!$release['active'] || !$receiver->platform->enabled) {
            return $this->handleAuthenticationFail($status, 'lti_consumer_inactive');
        }

        $usr_id = $this->syncUser($release, $parameters);
        $this->assignLocalRoles($usr_id, $release['platform_id'], $release['ref_id'], $receiver->userResult);
        $this->rememberLaunch($release['ref_id'], $parameters);

        $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
        $status->setAuthenticatedUserId($usr_id);

        return true;
    }

    /**
     * The platform and the object a launch is for. A registration of lti2_consumer for a single object
     * (ref_id > 0) is for that object. The registration of an LTI Advantage platform (ref_id 0) is for
     * the object its target link names, as long as the object is released to the platform.
     *
     * @param array $parameters
     * @return array|null
     */
    private function lookupRelease(int $record_id, array $parameters): ?array
    {
        global $DIC;

        $db = $DIC->database();
        $row = $db->fetchAssoc($db->queryF(
            'SELECT c.ref_id, e.id, e.prefix, e.user_language, e.role, e.active FROM lti2_consumer c'
            . ' JOIN lti_ext_consumer e ON e.id = c.ext_consumer_id WHERE c.consumer_pk = %s',
            ['integer'],
            [$record_id]
        ));
        if ($row === null) {
            return null;
        }

        $ref_id = (int) $row['ref_id'];
        if ($ref_id === 0) {
            $ref_id = $this->getTargetRefId($parameters);
            if (!new ilLTIRelease($ref_id, (int) $row['id'])->isReleased()) {
                $ref_id = 0;
            }
        }

        return [
            'ref_id' => $ref_id > 0 && ilObject::_exists($ref_id, true) ? $ref_id : 0,
            'platform_id' => (int) $row['id'],
            'prefix' => (string) $row['prefix'],
            'language' => (string) $row['user_language'],
            'role' => (int) $row['role'],
            'active' => (bool) $row['active'],
        ];
    }

    /**
     * The account is only valid as long as a session, it is extended with every launch.
     *
     * @param array $release
     * @param array $parameters
     */
    private function syncUser(array $release, array $parameters): int
    {
        global $DIC;

        // the id the platform gives the user: user_id of LTI 1.1, the claim sub of LTI Advantage
        $account = (string) ($parameters['user_id'] ?? '');
        $auth_mode = self::AUTH_MODE_PREFIX . $release['platform_id'];
        $login = ilObjUser::_checkExternalAuthAccount($auth_mode, $account);
        $session_expire = (int) $DIC['ilClientIniFile']->readVariable('session', 'expire');

        $user = $login ? new ilObjUser(ilObjUser::_lookupId($login)) : new ilObjUser();
        $user->setFirstname($this->getProfileValue($parameters, 'lis_person_name_given', '-'));
        $user->setLastname($this->getProfileValue($parameters, 'lis_person_name_family', '-'));
        $user->setEmail($this->getProfileValue($parameters, 'lis_person_contact_email_primary', ''));
        $user->setActive(true);
        $user->setTimeLimitUnlimited(false);
        if ($user->getTimeLimitUntil() < time() + $session_expire) {
            $user->setTimeLimitFrom(time() - 60);
            $user->setTimeLimitUntil(time() + $session_expire);
        }

        if ($login) {
            $user->update();
            $user->refreshLogin();
        } else {
            $user->setLogin(ilAuthUtils::_generateLogin($this->buildLogin($release['prefix'], $account)));
            $user->setPasswd('', ilObjUser::PASSWD_CRYPTED);
            $user->setAuthMode($auth_mode);
            $user->setExternalAccount($account);
            $user->setProfileIncomplete(false);
            $user->setGender('n');
            $user->setLanguage($release['language']);
            $user->setTimeLimitOwner(USER_FOLDER_ID);
            $user->setOwner(SYSTEM_USER_ID);
            $user->setAgreeDate(new ilDateTime(time(), IL_CAL_UNIX)->get(IL_CAL_DATETIME));
            $user->setTitle($user->getFullname());
            $user->setDescription($user->getEmail());
            $user->create();
            $user->setLastPasswordChangeTS(time());
            $user->saveAsNew();
            $user->writePrefs();
        }

        if ($release['role'] > 0) {
            $DIC->rbac()->admin()->assignUser($release['role'], $user->getId());
        }

        return $user->getId();
    }

    /**
     * A value of the profile the platform sent, or the default when it sent none or one longer than ILIAS
     * keeps (128 characters for names and email).
     *
     * @param array $parameters
     */
    private function getProfileValue(array $parameters, string $name, string $default): string
    {
        $value = trim((string) ($parameters[$name] ?? ''));

        return $value === '' || ilStr::strLen($value) > 128 ? $default : $value;
    }

    /**
     * The login of a new user: the prefix of the platform and the id it gives the user. That id may be long or
     * hold characters a login does not allow, which are replaced; the external account keeps it as it came.
     */
    private function buildLogin(string $prefix, string $account): string
    {
        $login = (string) preg_replace('/[^A-Za-z0-9_.+*@!$%~-]/', '_', $prefix . '_' . $account);

        // below the 190 characters of the column, leaving room for the number _generateLogin() may append
        return strlen($login) > 180 ? substr($prefix, 0, 40) . '_' . hash('sha256', $account) : $login;
    }

    /**
     * The roles of a new launch replace the ones of the last, in the object and in the released objects
     * above it.
     */
    private function assignLocalRoles(int $usr_id, int $platform_id, int $ref_id, ?ceLTIc\LTI\User $lti_user): void
    {
        global $DIC;

        if ($lti_user === null) {
            return;
        }

        foreach ($DIC->repositoryTree()->getPathId($ref_id) as $path_ref_id) {
            $release = new ilLTIRelease((int) $path_ref_id, $platform_id);
            foreach ($release->getAllRoles() as $role_id) {
                $DIC->rbac()->admin()->deassignUser($role_id, $usr_id);
            }
            foreach ($release->getLocalRoles($lti_user) as $role_id) {
                $DIC->rbac()->admin()->assignUser($role_id, $usr_id);
            }
        }
    }

    /**
     * What the LTI view needs of the launch, for the object the platform launched.
     *
     * @param array $parameters
     */
    private function rememberLaunch(int $ref_id, array $parameters): void
    {
        ilSession::set('lti_context_ids', [$ref_id]);
        ilSession::set('lti_' . $ref_id . '_post_data', [
            'launch_presentation_return_url' => (string) ($parameters['launch_presentation_return_url'] ?? ''),
            'launch_presentation_css_url' => (string) ($parameters['launch_presentation_css_url'] ?? ''),
            'resource_link_title' => (string) ($parameters['resource_link_title'] ?? ''),
        ]);
        ilSession::set('lti_init_target', ilObject::_lookupType($ref_id, true) . '_' . $ref_id);
    }

    /**
     * The object an LTI Advantage launch is for, from the target link ILIAS gives out: lti.php?ref_id=N.
     *
     * @param array $parameters
     */
    private function getTargetRefId(array $parameters): int
    {
        parse_str((string) parse_url((string) ($parameters['target_link_uri'] ?? ''), PHP_URL_QUERY), $query);

        return (int) ($query['ref_id'] ?? 0);
    }

    /**
     * @return array
     */
    private static function lookupPlatformIds(bool $active_only): array
    {
        global $DIC;

        $db = $DIC->database();
        $condition = $active_only ? ' WHERE e.active = 1 AND c.enabled = 1' : '';
        $result = $db->query(
            'SELECT DISTINCT e.id FROM lti_ext_consumer e JOIN lti2_consumer c ON c.ext_consumer_id = e.id' . $condition
        );

        $ids = [];
        while ($row = $db->fetchAssoc($result)) {
            $ids[] = (int) $row['id'];
        }

        return $ids;
    }
}
