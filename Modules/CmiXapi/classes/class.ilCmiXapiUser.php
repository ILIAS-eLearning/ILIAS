<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    /**
     * @var int
     */
    protected $objId;
    
    /**
     * @var int
     */
    protected $usrId;
    
    /**
     * @var bool
     */
    protected $proxySuccess;
    
    /**
     * @var ilCmiXapiDateTime
     */
    protected $fetchUntil;
    
    /**
     * @var string
     */
    protected $usrIdent;
    
    public function __construct($objId = null, $usrId = null)
    {
        $this->objId = $objId;
        $this->usrId = $usrId;
        $this->proxySuccess = false;
        $this->fetchUntil = new ilCmiXapiDateTime(0, IL_CAL_UNIX);
        $this->usrIdent = '';
        
        if ($objId !== null && $usrId !== null) {
            $this->load();
        }
    }
    
    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }
    
    /**
     * @param int $objId
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;
    }
    
    /**
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }
    
    /**
     * @param int $usrId
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;
    }
    
    /**
     * @return string
     */
    public function getUsrIdent() : string
    {
        return $this->usrIdent;
    }
    
    /**
     * @param string $usrIdent
     */
    public function setUsrIdent(string $usrIdent)
    {
        $this->usrIdent = $usrIdent;
    }
    
    /**
     * @return string
     */
    public static function getIliasUuid() : string
    {
        $setting = new ilSetting('cmix');
        $ilUuid = $setting->get('ilias_uuid');
        return $ilUuid;
    }
    
    /**
     * @return bool
     */
    public function hasProxySuccess()
    {
        return $this->proxySuccess;
    }
    
    /**
     * @param bool $proxySuccess
     */
    public function setProxySuccess($proxySuccess)
    {
        $this->proxySuccess = $proxySuccess;
    }
    
    /**
     * @return ilCmiXapiDateTime
     */
    public function getFetchUntil() : ilCmiXapiDateTime
    {
        return $this->fetchUntil;
    }
    
    /**
     * @param ilCmiXapiDateTime $fetchUntil
     */
    public function setFetchUntil(ilCmiXapiDateTime $fetchUntil)
    {
        $this->fetchUntil = $fetchUntil;
    }
    
    public function load()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $res = $DIC->database()->queryF(
            "SELECT * FROM cmix_users WHERE obj_id = %s AND usr_id = %s",
            array('integer', 'integer'),
            array($this->getObjId(), $this->getUsrId())
        );
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $this->assignFromDbRow($row);
        }
    }
    
    public function assignFromDbRow($dbRow)
    {
        $this->setObjId((int) $dbRow['obj_id']);
        $this->setUsrId((int) $dbRow['usr_id']);
        $this->setProxySuccess((bool) $dbRow['proxy_success']);
        $this->setFetchUntil(new ilCmiXapiDateTime($dbRow['fetched_until'], IL_CAL_DATETIME));
        $this->setUsrIdent((string) $dbRow['usr_ident']);
    }
    
    public function save()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->database()->replace(
            'cmix_users',
            array(
                'obj_id' => array('integer', (int) $this->getObjId()),
                'usr_id' => array('integer', (int) $this->getUsrId())
            ),
            array(
                'proxy_success' => array('integer', (int) $this->hasProxySuccess()),
                'fetched_until' => array('timestamp', $this->getFetchUntil()->get(IL_CAL_DATETIME)),
                'usr_ident' => array('text', $this->getUsrIdent())
            )
        );
    }
    
    public static function getInstanceByObjectIdAndUsrIdent($objId, $usrIdent)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $res = $DIC->database()->queryF(
            "SELECT * FROM cmix_users WHERE obj_id = %s AND usr_ident = %s",
            array('integer', 'integer'),
            array($objId, $usrIdent)
        );
        
        $cmixUser = new self();
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $cmixUser->assignFromDbRow($row);
        }
        
        return $cmixUser;
    }
    
    /**
     * @param int $objId
     * @param int $usrId
     */
    public static function saveProxySuccess($objId, $usrId)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->database()->update(
            'cmix_users',
            array(
                'proxy_success' => array('integer', (int) true)
            ),
            array(
                'obj_id' => array('integer', (int) $objId),
                'usr_id' => array('integer', (int) $usrId)
            )
        );
    }
    
    /**
     * @param string $userIdentMode
     * @param ilObjUser $user
     * @return string
     */
    public static function getIdent($userIdentMode, ilObjUser $user)
    {
        switch ($userIdentMode) {
            case ilObjCmiXapi::USER_IDENT_IL_UUID_USER_ID:
                
                return self::buildPseudoEmail($user->getId(), self::getIliasUuid());
                
            case ilObjCmiXapi::USER_IDENT_IL_UUID_LOGIN:
                
                return self::buildPseudoEmail($user->getLogin(), self::getIliasUuid());
                
            case ilObjCmiXapi::USER_IDENT_IL_UUID_EXT_ACCOUNT:
                
                return self::buildPseudoEmail($user->getExternalAccount(), self::getIliasUuid());
                
            case ilObjCmiXapi::USER_IDENT_REAL_EMAIL:
                
                return $user->getEmail();
        }
        
        return '';
    }
    
    /**
     * @param string $userIdentMode
     * @param ilObjUser $user
     * @return integer/string
     */
    public static function getIdentAsId($userIdentMode, ilObjUser $user)
    {
        switch ($userIdentMode) {
            case ilObjCmiXapi::USER_IDENT_IL_UUID_USER_ID:
                
                return $user->getId();
                
            case ilObjCmiXapi::USER_IDENT_IL_UUID_LOGIN:
                
                return $user->getLogin();
                
            case ilObjCmiXapi::USER_IDENT_IL_UUID_EXT_ACCOUNT:
                
                return $user->getExternalAccount();
                
            case ilObjCmiXapi::USER_IDENT_REAL_EMAIL:
                
                return 'realemail' . $user->getId();
        }
        
        return '';
    }

    /**
     * @param string $mbox
     * @param string $domain
     * @return string
     */
    protected static function buildPseudoEmail($mbox, $domain)
    {
        return "{$mbox}@{$domain}.ilias";
    }
    
    /**
     * @param string $userNameMode
     * @param ilObjUser $user
     * @return string|null
     */
    public static function getName($userNameMode, ilObjUser $user)
    {
        switch ($userNameMode) {
            case ilObjCmiXapi::USER_NAME_FIRSTNAME:
                
                $usrName = $user->getFirstname();
                break;
            
            case ilObjCmiXapi::USER_NAME_LASTNAME:
                
                $usrName = $user->getUTitle() ? $user->getUTitle() . ' ' : '';
                $usrName .= $user->getLastname();
                break;
            
            case ilObjCmiXapi::USER_NAME_FULLNAME:
                
                $usrName = $user->getFullname();
                break;
            
            case ilObjCmiXapi::USER_NAME_NONE:
            default:
                
                $usrName = '';
                break;
        }
        
        return $usrName;
    }
    
    /**
     * @param int $object
     * @return ilCmiXapiUser[]
     */
    public static function getUsersForObject($objId) : array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $res = $DIC->database()->queryF(
            "SELECT * FROM cmix_users WHERE obj_id = %s",
            array('integer'),
            array($objId)
        );
        
        $users = [];
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $cmixUser = new self();
            $cmixUser->assignFromDbRow($row);
            
            $users[] = $cmixUser;
        }
        
        return $users;
    }
    
    public static function getUserIdFromIdent($userIdentMode, $xapiUserIdent)
    {
        switch ($userIdentMode) {
            case ilObjCmiXapi::USER_IDENT_REAL_EMAIL:
                
                /**
                 * TODO: lookup user by email
                 * - handle none found (e.g. when different email was used in external app)
                 * - handle multiple found and find fallback behaviour
                 */
                
                $iliasUserId = 0;
                
                break;
                
            
            case ilObjCmiXapi::USER_IDENT_IL_UUID_USER_ID:
                
                $setting = new ilSetting('cmix');
                $nicId = $setting->get('ilias_uuid');
                
                $matches = null;
                if (preg_match('/^mailto:(\d+)@' . $nicId . '\.ilias$/', $xapiUserIdent, $matches)) {
                    $iliasUserId = $matches[1];
                } else {
                    /**
                     * TODO: handle not parseable xapi user ident
                     */
                    $iliasUserId = 0;
                }
                
                break;
        }
        
        return $iliasUserId;
    }
    
    public static function getUserFromIdent($object, $xapiUserIdent)
    {
        if ($object instanceof ilObjCmiXapi) {
            $userIdentMode = $object->getUserIdent();
        } else {
            //BUG LTI
            $userIdentMode = ilObjCmiXapi::USER_IDENT_IL_UUID_USER_ID;
        }

        $iliasUserId = self::getUserIdFromIdent($userIdentMode, $xapiUserIdent);
        return ilObjectFactory::getInstanceByObjId($iliasUserId, false);
    }
    
    public static function exists($objId, $usrId)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $query = "SELECT count(*) cnt FROM cmix_users WHERE obj_id = %s AND usr_id = %s";

        $res = $DIC->database()->queryF(
            $query,
            array('integer', 'integer'),
            array($objId, $usrId)
        );
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            return (bool) $row['cnt'];
        }
        
        return false;
    }
    
    public static function getCmixObjectsHavingUsersMissingProxySuccess()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $query = "
			SELECT DISTINCT cu.obj_id
			FROM cmix_users cu
			INNER JOIN object_data od
			ON od.obj_id = cu.obj_id
			AND od.type = 'cmix'
			WHERE cu.proxy_success != %s
		";
        
        $res = $DIC->database()->queryF($query, array('integer'), array(1));
        
        $objects = array();
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $objects[] = $row['obj_id'];
        }
        
        return $objects;
    }

    public static function updateFetchedUntilForObjects(ilCmiXapiDateTime $fetchedUntil, $objectIds)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $IN_objIds = $DIC->database()->in('obj_id', $objectIds, false, 'integer');
        
        $query = "UPDATE cmix_users SET fetched_until = %s WHERE $IN_objIds";
        $DIC->database()->manipulateF($query, array('timestamp'), array($fetchedUntil->get(IL_CAL_DATETIME)));
    }
    
    public static function lookupObjectIds($usrId, $type = '')
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
			FROM cmix_users cu
			{$TYPE_JOIN}
			WHERE cu.usr_id = {$DIC->database()->quote($usrId, 'integer')}
		";
        
        $res = $DIC->database()->query($query);
        
        $objIds = [];
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $objIds[] = $row['obj_id'];
        }
        
        return $objIds;
    }

    public static function getRegistration(ilObjCmiXapi $obj, ilObjUser $user)
    {
        return (new \Ramsey\Uuid\UuidFactory())->uuid3(self::getIliasUuid(),$obj->getRefId() . '-' . $user->getId());
    }

//    /**
//     * Generate v3 UUID
//     *
//     * Version 3 UUIDs are named based. They require a namespace (another
//     * valid UUID) and a value (the name). Given the same namespace and
//     * name, the output is always the same.
//     *
//     * @param	uuid	$namespace
//     * @param	string	$name
//     */
//    public static function uuid_v3($namespace, $name)
//    {
//        if(!self::is_uuid_valid($namespace)) return false;
//
//        // Get hexadecimal components of namespace
//        $nhex = str_replace(array('-','{','}'), '', $namespace);
//
//        // Binary Value
//        $nstr = '';
//
//        // Convert Namespace UUID to bits
//        for($i = 0; $i < strlen($nhex); $i+=2)
//        {
//            $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
//        }
//
//        // Calculate hash value
//        $hash = md5($nstr . $name);
//
//        return sprintf('%08s-%04s-%04x-%04x-%12s',
//
//            // 32 bits for "time_low"
//            substr($hash, 0, 8),
//
//            // 16 bits for "time_mid"
//            substr($hash, 8, 4),
//
//            // 16 bits for "time_hi_and_version",
//            // four most significant bits holds version number 3
//            (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,
//
//            // 16 bits, 8 bits for "clk_seq_hi_res",
//            // 8 bits for "clk_seq_low",
//            // two most significant bits holds zero and one for variant DCE1.1
//            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
//
//            // 48 bits for "node"
//            substr($hash, 20, 12)
//        );
//    }
//    public static function is_uuid_valid($uuid) {
//        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.
//                '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
//    }

}
