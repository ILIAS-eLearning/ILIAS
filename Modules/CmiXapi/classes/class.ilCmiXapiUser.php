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
 * Class ilCmiXapiUser
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiUser
{
    public const DB_TABLE_NAME = 'cmix_users';

    protected ?int $objId;

    protected ?int $usrId;

    protected ?int $privacyIdent;

    protected bool $proxySuccess;

    protected bool $satisfied;

    protected ilCmiXapiDateTime $fetchUntil;

    protected string $usrIdent;

    protected string $registration;

    private ilDBInterface $database;


    public function __construct(?int $objId = null, ?int $usrId = null, ?int $privacyIdent = null)
    {
        global $DIC;
        $this->database = $DIC->database();
        $this->objId = $objId;
        $this->usrId = $usrId;
        $this->privacyIdent = $privacyIdent;
        $this->proxySuccess = false;
        $this->satisfied = false;
        $this->fetchUntil = new ilCmiXapiDateTime(0, IL_CAL_UNIX);
        $this->usrIdent = '';
        $this->registration = '';

        if ($objId !== null && $usrId !== null && $privacyIdent !== null) {
            $this->load();
        }
    }

    public function getObjId(): ?int
    {
        return $this->objId;
    }

    public function setObjId(int $objId): void
    {
        $this->objId = $objId;
    }

    public function getPrivacyIdent(): ?int
    {
        return $this->privacyIdent;
    }

    public function setPrivacyIdent(int $privacyIdent): void
    {
        $this->privacyIdent = $privacyIdent;
    }

    public function getUsrId(): ?int
    {
        return $this->usrId;
    }

    public function setUsrId(int $usrId): void
    {
        $this->usrId = $usrId;
    }

    public function getUsrIdent(): string
    {
        return $this->usrIdent;
    }

    public function setUsrIdent(string $usrIdent): void
    {
        $this->usrIdent = $usrIdent;
    }

    public function getRegistration(): string
    {
        return $this->registration;
    }

    public function setRegistration(string $registration): void
    {
        $this->registration = $registration;
    }

    public static function getIliasUuid(): ?string
    {
        $setting = new ilSetting('cmix');
        // Fallback
        if (null == $setting->get('ilias_uuid', null)) {
            // $uuid = (new \Ramsey\Uuid\UuidFactory())->uuid4()->toString();
            $uuid = self::getUUID(32);
            $setting->set('ilias_uuid', $uuid);
        }
        return $setting->get('ilias_uuid');
    }

    public function hasProxySuccess(): bool
    {
        return $this->proxySuccess;
    }

    public function setProxySuccess(bool $proxySuccess): void
    {
        $this->proxySuccess = $proxySuccess;
    }

    public function setSatisfied(bool $satisfied): void
    {
        $this->satisfied = $satisfied;
    }

    /**
     * @return bool $satisfied
     */
    public function getSatisfied(): bool
    {
        return $this->satisfied;
    }

    public function getFetchUntil(): ilCmiXapiDateTime
    {
        return $this->fetchUntil;
    }

    public function setFetchUntil(ilCmiXapiDateTime $fetchUntil): void
    {
        $this->fetchUntil = $fetchUntil;
    }

    protected function load(): void
    {
        $res = $this->database->queryF(
            "SELECT * FROM " . self::DB_TABLE_NAME . " WHERE obj_id = %s AND usr_id = %s AND privacy_ident = %s",
            array('integer', 'integer', 'integer'),
            array($this->getObjId(), $this->getUsrId(), $this->getPrivacyIdent())
        );

        while ($row = $this->database->fetchAssoc($res)) {
            $this->assignFromDbRow($row);
        }
    }

    public function assignFromDbRow($dbRow): void
    {
        $this->setObjId((int) $dbRow['obj_id']);
        $this->setUsrId((int) $dbRow['usr_id']);
        $this->setProxySuccess((bool) $dbRow['proxy_success']);
        $this->setSatisfied((bool) $dbRow['satisfied']);
        $this->setFetchUntil(new ilCmiXapiDateTime($dbRow['fetched_until'], IL_CAL_DATETIME));
        $this->setUsrIdent((string) $dbRow['usr_ident']);
        $this->setPrivacyIdent((int) $dbRow['privacy_ident']);
        $this->setRegistration((string) $dbRow['registration']);
    }

    public function save(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->database()->replace(
            self::DB_TABLE_NAME,
            array(
                'obj_id' => array('integer', (int) $this->getObjId()),
                'usr_id' => array('integer', (int) $this->getUsrId()),
                'privacy_ident' => array('integer', (int) $this->getPrivacyIdent())
            ),
            array(
                'proxy_success' => array('integer', (int) $this->hasProxySuccess()),
                'fetched_until' => array('timestamp', $this->getFetchUntil()->get(IL_CAL_DATETIME)),
                'usr_ident' => array('text', $this->getUsrIdent()),
                'registration' => array('text', $this->getRegistration()),
                'satisfied' => array('integer', (int) $this->getSatisfied())
            )
        );
    }

    // ToDo Only for Deletion -> Core
    /**
     * @return \ilCmiXapiUser[]
     */
    public static function getInstancesByObjectIdAndUsrId(int $objId, int $usrId): array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $res = $DIC->database()->queryF(
            "SELECT * FROM " . self::DB_TABLE_NAME . " WHERE obj_id = %s AND usr_id = %s",
            array('integer', 'integer'),
            array($objId, $usrId)
        );
        $cmixUsers = array();
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $cmixUser = new self();
            $cmixUser->assignFromDbRow($row);
            $cmixUsers[] = $cmixUser;
        }
        return $cmixUsers;
    }

    public static function getInstanceByObjectIdAndUsrIdent(int $objId, string $usrIdent): \ilCmiXapiUser
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $res = $DIC->database()->queryF(
            "SELECT * FROM " . self::DB_TABLE_NAME . " WHERE obj_id = %s AND usr_ident = %s",
            array('integer', 'text'),
            array($objId, $usrIdent)
        );

        $cmixUser = new self();

        while ($row = $DIC->database()->fetchAssoc($res)) {
            $cmixUser->assignFromDbRow($row);
        }

        return $cmixUser;
    }

    public static function saveProxySuccess(int $objId, int $usrId, int $privacyIdent): void //TODO
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $DIC->database()->update(
            self::DB_TABLE_NAME,
            array(
                'proxy_success' => array('integer', 1)
            ),
            array(
                'obj_id' => array('integer', (int) $objId),
                'usr_id' => array('integer', (int) $usrId),
                'privacy_ident' => array('integer', (int) $privacyIdent)
            )
        );
    }

    public static function getIdent(int $userIdentMode, ilObjUser $user): string
    {
        switch ($userIdentMode) {
            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_USER_ID:

                return self::buildPseudoEmail((string) $user->getId(), self::getIliasUuid());

            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_LOGIN:

                return self::buildPseudoEmail($user->getLogin(), self::getIliasUuid());

            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT:

                return self::buildPseudoEmail($user->getExternalAccount(), self::getIliasUuid());

            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_SHA256:

                return self::buildPseudoEmail(hash("sha256",'' . $user->getId() . $user->getCreateDate()), self::getIliasUuid());

            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_SHA256URL:
                $tmpHash = hash("sha256",'' . $user->getId() . $user->getCreateDate()) . '@' . str_replace('www.', '', $_SERVER['HTTP_HOST']);
                if (strlen($tmpHash) > 80) {
                    $tmpHash = substr($tmpHash,strlen($tmpHash)-80);
                }
                return $tmpHash;

            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_RANDOM:

                return self::buildPseudoEmail(self::getUserObjectUniqueId(), self::getIliasUuid());

            case ilObjCmiXapi::PRIVACY_IDENT_REAL_EMAIL:

                return $user->getEmail();
        }

        return '';
    }

    public static function getIdentAsId(int $userIdentMode, ilObjUser $user): string
    {
        switch ($userIdentMode) {
            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_USER_ID:

                return (string) $user->getId();

            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_LOGIN:

                return $user->getLogin();

            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT:

                return $user->getExternalAccount();

            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_SHA256:

                return hash("sha256",'' . $user->getId() . $user->getCreateDate());

            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_SHA256URL:
                $tmpHash = hash("sha256",'' . $user->getId() . $user->getCreateDate());
                $tmpHost = '@' . str_replace('www.', '', $_SERVER['HTTP_HOST']);
                if (strlen($tmpHash . $tmpHost) > 80) {
                    $tmpHash = substr($tmpHash,strlen($tmpHash) - (80 - strlen($tmpHost)));
                }
                return $tmpHash;

            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_RANDOM:

                return self::getUserObjectUniqueId();

            case ilObjCmiXapi::PRIVACY_IDENT_REAL_EMAIL:

                return 'realemail' . $user->getId();
        }

        return '';
    }

    protected static function buildPseudoEmail(string $mbox, string $domain): string
    {
        return "{$mbox}@{$domain}.ilias";
    }

    public static function getName(int $userNameMode, ilObjUser $user): string
    {
        $usrName = "";
        switch ($userNameMode) {
            case ilObjCmiXapi::PRIVACY_NAME_FIRSTNAME:
                $usrName = $user->getFirstname();
                break;

            case ilObjCmiXapi::PRIVACY_NAME_LASTNAME:
                $usrName = $user->getUTitle() ? $user->getUTitle() . ' ' : '';
                $usrName .= $user->getLastname();
                break;

            case ilObjCmiXapi::PRIVACY_NAME_FULLNAME:
                $usrName = $user->getFullname();
                break;

            case ilObjCmiXapi::PRIVACY_NAME_NONE:
            default:
                $usrName = '-';
                break;
        }
        return $usrName;
    }

    /**
     * @return ilCmiXapiUser[]
     */
    public static function getUsersForObject(int $objId, bool $asUsrId = false): array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $res = $DIC->database()->queryF(
            "SELECT * FROM " . self::DB_TABLE_NAME . " WHERE obj_id = %s",
            array('integer'),
            array($objId)
        );

        $users = [];

        if ($asUsrId === false) {
            while ($row = $DIC->database()->fetchAssoc($res)) {
                $cmixUser = new self();
                $cmixUser->assignFromDbRow($row);

                $users[] = $cmixUser;
            }
        } else {
            while ($row = $DIC->database()->fetchAssoc($res)) {
                $users[] = $row['usr_id'];
            }
        }
        return $users;
    }

    /**
     * @return string[] $usrIdents
     */
    public static function getUserIdents(int $objId, int $usrId): array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $res = $DIC->database()->queryF(
            "SELECT usr_ident FROM " . self::DB_TABLE_NAME . " WHERE obj_id = %s AND usr_id = %s",
            array('integer','integer'),
            array($objId,$usrId)
        );

        $usrIdents = [];
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $usrIdents[] = $row['usr_ident'];
        }
        return $usrIdents;
    }

    public static function exists(int $objId, int $usrId, int $privacyIdent = 999): bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        if ($privacyIdent == 999) {
            $query = "SELECT count(*) cnt FROM " . self::DB_TABLE_NAME . " WHERE obj_id = %s AND usr_id = %s";
            $res = $DIC->database()->queryF(
                $query,
                array('integer', 'integer'),
                array($objId, $usrId)
            );
        } else {
            $query = "SELECT count(*) cnt FROM " . self::DB_TABLE_NAME . " WHERE obj_id = %s AND usr_id = %s AND privacy_ident = %s";
            $res = $DIC->database()->queryF(
                $query,
                array('integer', 'integer', 'integer'),
                array($objId, $usrId, $privacyIdent)
            );
        }

        while ($row = $DIC->database()->fetchAssoc($res)) {
            return (bool) $row['cnt'];
        }

        return false;
    }

    /**
     * @return int[]
     */
    public static function getCmixObjectsHavingUsersMissingProxySuccess(): array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = "
			SELECT DISTINCT cu.obj_id
			FROM " . self::DB_TABLE_NAME . " cu
			INNER JOIN object_data od
			ON od.obj_id = cu.obj_id
			AND od.type = 'cmix'
			WHERE cu.proxy_success != %s
		";

        $res = $DIC->database()->queryF($query, array('integer'), array(1));

        $objects = array();

        while ($row = $DIC->database()->fetchAssoc($res)) {
            $objects[] = (int) $row['obj_id'];
        }

        return $objects;
    }

    public static function updateFetchedUntilForObjects(ilCmiXapiDateTime $fetchedUntil, array $objectIds): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $IN_objIds = $DIC->database()->in('obj_id', $objectIds, false, 'integer');

        $query = "UPDATE " . self::DB_TABLE_NAME . " SET fetched_until = %s WHERE $IN_objIds";
        $DIC->database()->manipulateF($query, array('timestamp'), array($fetchedUntil->get(IL_CAL_DATETIME)));
    }

    /**
     * @return int[]
     */
    public static function lookupObjectIds(int $usrId, string $type = ''): array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $TYPE_JOIN = '';

        if (strlen($type)) {
            $TYPE_JOIN = "
				INNER JOIN object_data od
				ON od.obj_id = cu.obj_id
				AND od.type = {$DIC->database()->quote($type, 'text')}
			";
        }

        $query = "
			SELECT cu.obj_id
			FROM " . self::DB_TABLE_NAME . " cu
			{$TYPE_JOIN}
			WHERE cu.usr_id = {$DIC->database()->quote($usrId, 'integer')}
		";

        $res = $DIC->database()->query($query);

        $objIds = [];

        while ($row = $DIC->database()->fetchAssoc($res)) {
            $objIds[] = (int) $row['obj_id'];
        }

        return $objIds;
    }
    public static function getUserObjectUniqueId(int $length = 32): string
    {
        // $storedId = self::readUserObjectUniqueId();
        // if( (bool)strlen($storedId) ) {
        // return strstr($storedId,'@', true);
        // }

        // $getId = function( $length ) {
        // $multiplier = floor($length/8) * 2;
        // $uid = str_shuffle(str_repeat(uniqid(), $multiplier));

        // try {
        // $ident = bin2hex(random_bytes($length));
        // } catch (Exception $e) {
        // $ident = $uid;
        // }

        // $start = rand(0, strlen($ident) - $length - 1);
        // return substr($ident, $start, $length);
        // };

        $id = self::getUUID($length);//$getId($length);
        $exists = self::userObjectUniqueIdExists($id);
        while ($exists) {
            $id = self::getUUID($length);//$getId($length);
            $exists = self::userObjectUniqueIdExists($id);
        }

        return $id;
    }

    public static function getUUID(int $length = 32): string
    {
        $multiplier = floor($length / 8) * 2;
        $uid = str_shuffle(str_repeat(uniqid(), $multiplier));

        try {
            $ident = bin2hex(random_bytes($length));
        } catch (Exception $e) {
            $ident = $uid;
        }

        $start = rand(0, strlen($ident) - $length - 1);
        return substr($ident, $start, $length);
    }

    private static function userObjectUniqueIdExists(string $id): bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = "SELECT usr_ident FROM " . self::DB_TABLE_NAME . " WHERE " . $DIC->database()->like('usr_ident', 'text', $id . '@%');
        $result = $DIC->database()->query($query);
        return $result->numRows() != 0;
    }

    public static function generateCMI5Registration(int $objId, int $usrId): \Ramsey\Uuid\UuidInterface
    {
        return (new \Ramsey\Uuid\UuidFactory())->uuid3(self::getIliasUuid(), $objId . '-' . $usrId);
    }

    public static function generateRegistration(ilObjCmiXapi $obj, ilObjUser $user): \Ramsey\Uuid\UuidInterface
    {
        return (new \Ramsey\Uuid\UuidFactory())->uuid3(self::getIliasUuid(), $obj->getRefId() . '-' . $user->getId());
    }
}
