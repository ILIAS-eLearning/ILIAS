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
     * @var int
     */
    protected $privacyIdent;
    
    /**
     * @var bool
     */
    protected $proxySuccess;
    
    /**
     * @var bool
     */
    protected $satisfied;

    /**
     * @var ilCmiXapiDateTime
     */
    protected $fetchUntil;
    
    /**
     * @var string
     */
    protected $usrIdent;

    /**
     * @var string
     */
    protected $registration;

    
    public function __construct($objId = null, $usrId = null, $privacyIdent = null)
    {
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
    public function getPrivacyIdent()
    {
        return $this->privacyIdent;
    }
    
    /**
     * @param int $privacyIdent
     */
    public function setPrivacyIdent($privacyIdent)
    {
        $this->privacyIdent = $privacyIdent;
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
    public function getRegistration() : string
    {
        return $this->registration;
    }

    /**
     * @param string
     */
    public function setRegistration(string $registration)
    {
        $this->registration = $registration;
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
     * @param bool $satisfied
     */
    public function setSatisfied($satisfied)
    {
        $this->satisfied = $satisfied;
    }

    /**
     * @return bool $satisfied
     */
    public function getSatisfied()
    {
        return $this->satisfied;
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
            "SELECT * FROM cmix_users WHERE obj_id = %s AND usr_id = %s AND privacy_ident = %s",
            array('integer', 'integer', 'integer'),
            array($this->getObjId(), $this->getUsrId(), $this->getPrivacyIdent())
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
        $this->setSatisfied((bool) $dbRow['satisfied']);
        $this->setFetchUntil(new ilCmiXapiDateTime($dbRow['fetched_until'], IL_CAL_DATETIME));
        $this->setUsrIdent((string) $dbRow['usr_ident']);
        $this->setPrivacyIdent((int) $dbRow['privacy_ident']);
        $this->setRegistration((string) $dbRow['registration']);
    }
    
    public function save()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->database()->replace(
            'cmix_users',
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
    
    public static function getInstanceByObjectIdAndUsrIdent($objId, $usrIdent)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $res = $DIC->database()->queryF(
            "SELECT * FROM cmix_users WHERE obj_id = %s AND usr_ident = %s",
            array('integer', 'text'),
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
     * @param int $privacyIdent
     */
    public static function saveProxySuccess($objId, $usrId, $privacyIdent) //TODO
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->database()->update(
            'cmix_users',
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
    
    /**
     * @param string $userIdentMode
     * @param ilObjUser $user
     * @return string
     */
    public static function getIdent($userIdentMode, ilObjUser $user)
    {
        switch ($userIdentMode) {
            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_USER_ID:
                
                return self::buildPseudoEmail($user->getId(), self::getIliasUuid());
                
            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_LOGIN:
                
                return self::buildPseudoEmail($user->getLogin(), self::getIliasUuid());
                
            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT:
                
                return self::buildPseudoEmail($user->getExternalAccount(), self::getIliasUuid());
                
            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_RANDOM:

                return self::buildPseudoEmail(self::getUserObjectUniqueId(), self::getIliasUuid());

            case ilObjCmiXapi::PRIVACY_IDENT_REAL_EMAIL:
                
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
            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_USER_ID:
                
                return $user->getId();
                
            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_LOGIN:
                
                return $user->getLogin();
                
            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT:
                
                return $user->getExternalAccount();
                
            case ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_RANDOM:

                return self::getUserObjectUniqueId();

            case ilObjCmiXapi::PRIVACY_IDENT_REAL_EMAIL:
                
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
     * @param int $object
     * @return ilCmiXapiUser[]
     */
    public static function getUsersForObject($objId, $asUsrId = false) : array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $res = $DIC->database()->queryF(
            "SELECT * FROM cmix_users WHERE obj_id = %s",
            array('integer'),
            array($objId)
        );
        
        $users = [];
        
        if ($asUsrId === false) 
        {
            while ($row = $DIC->database()->fetchAssoc($res)) 
            {
                $cmixUser = new self();
                $cmixUser->assignFromDbRow($row);
                
                $users[] = $cmixUser;
            }
        }
        else 
        {
            while ($row = $DIC->database()->fetchAssoc($res)) 
            {
                $users[] = $row['usr_id'];
            }
        }
        return $users;
    }
    
    /**
     * @param int $objId
     * @param int $usrId
     * @return string[] $usrIdents
     */
    public static function getUserIdents($objId, $usrId) : array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $res = $DIC->database()->queryF(
            "SELECT usr_ident FROM cmix_users WHERE obj_id = %s AND usr_id = %s",
            array('integer','integer'),
            array($objId,$usrId)
        );
        
        $usrIdents = [];
        while ($row = $DIC->database()->fetchAssoc($res)) 
        {
                $usrIdents[] = $row['usr_ident'];
        }
        return $usrIdents;
    }

    // $withIdent requires constructed object with privacyIdent!
    public static function exists($objId, $usrId, $privacyIdent = 999)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        if ($privacyIdent == 999)
        {
            $query = "SELECT count(*) cnt FROM cmix_users WHERE obj_id = %s AND usr_id = %s";
            $res = $DIC->database()->queryF(
                $query,
                array('integer', 'integer'),
                array($objId, $usrId)
            );
        }
        else
        {
            $query = "SELECT count(*) cnt FROM cmix_users WHERE obj_id = %s AND usr_id = %s AND privacy_ident = %s";
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
    /**
     * @param int $length
     * @return string
     */
    public static function getUserObjectUniqueId( $length = 32 )
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
        while( $exists ) {
            $id = self::getUUID($length);//$getId($length);
            $exists = self::userObjectUniqueIdExists($id);
        }

        return $id;

    }

	public static function getUUID($length = 32 )
	{
		$multiplier = floor($length/8) * 2;
		$uid = str_shuffle(str_repeat(uniqid(), $multiplier));

		try {
			$ident = bin2hex(random_bytes($length));
		} catch (Exception $e) {
			$ident = $uid;
		}

		$start = rand(0, strlen($ident) - $length - 1);
		return substr($ident, $start, $length);
	}

    private static function userObjectUniqueIdExists($id)
    {
        global $DIC; /** @var Container */

        $query = "SELECT usr_ident FROM cmix_users WHERE " . $DIC->database()->like('usr_ident', 'text', $id . '@%');
        $result = $DIC->database()->query($query);
        return (bool)$num = $DIC->database()->numRows($result);
    }

    public static function generateCMI5Registration($objId, $usrId)
    {
        return (new \Ramsey\Uuid\UuidFactory())->uuid3(self::getIliasUuid(),$objId . '-' . $usrId);
    }

    public static function generateRegistration(ilObjCmiXapi $obj, ilObjUser $user)
    {
        return (new \Ramsey\Uuid\UuidFactory())->uuid3(self::getIliasUuid(),$obj->getRefId() . '-' . $user->getId());
    }
}
