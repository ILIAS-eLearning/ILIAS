<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiAuthToken
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiAuthToken
{
    const DB_TABLE_NAME = 'cmix_token';

    const OPENSSL_ENCRYPTION_METHOD = 'aes128';

    const OPENSSL_IV = '1234567890123456';
    
    /**
     * @var int
     */
    protected $ref_id;
    
    /**
     * @var int
     */
    protected $obj_id;
    
    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var string
     */
    protected $token;
    
    /**
     * @var string
     */
    protected $valid_until;
    
    /**
     * @var
     */
    protected $lrs_type_id;
    
    /**
     * @var
     */
    protected $cmi5_session;

    /**
     * @var
     */
    protected $cmi5_session_data;

    /**
     * @var
     */
    protected $returned_for_cmi5_session;

    /**
     * @return int
     */
    public function getRefId() : int
    {
        return $this->ref_id;
    }
    
    /**
     * @param int $ref_id
     */
    public function setRefId(int $ref_id)
    {
        $this->ref_id = $ref_id;
    }
    
    /**
     * @return int
     */
    public function getObjId() : int
    {
        return $this->obj_id;
    }
    
    /**
     * @param int $obj_id
     */
    public function setObjId(int $obj_id)
    {
        $this->obj_id = $obj_id;
    }
    
    /**
     * @return int
     */
    public function getUsrId() : int
    {
        return $this->usr_id;
    }
    
    /**
     * @param int $usr_id
     */
    public function setUsrId(int $usr_id)
    {
        $this->usr_id = $usr_id;
    }
    
    /**
     * @return string
     */
    public function getToken() : string
    {
        return $this->token;
    }
    
    /**
     * @param string $token
     */
    public function setToken(string $token)
    {
        $this->token = $token;
    }
    
    /**
     * @return string
     */
    public function getValidUntil() : string
    {
        return $this->valid_until;
    }
    
    /**
     * @param string $valid_until
     */
    public function setValidUntil(string $valid_until)
    {
        $this->valid_until = $valid_until;
    }
    
    /**
     * @return mixed
     */
    public function getLrsTypeId()
    {
        return $this->lrs_type_id;
    }
    
    /**
     * @param mixed $lrs_type_id
     */
    public function setLrsTypeId($lrs_type_id)
    {
        $this->lrs_type_id = $lrs_type_id;
    }

    /**
     * @return string
     */
    public function getCmi5Session()
    {
        return $this->cmi5_session;
    }

    /**
     * @param string $cmi5_session
     */
    public function setCmi5Session($cmi5_session)
    {
        $this->cmi5_session = $cmi5_session;
    }

    /**
     * @return string
     */
    public function getCmi5SessionData()
    {
        return $this->cmi5_session_data;
    }

    /**
     * @param string $cmi5_session_data
     */
    public function setCmi5SessionData($cmi5_session_data)
    {
        $this->cmi5_session_data = $cmi5_session_data;
    }

    /**
     * @return string
     */
    public function getReturnedForCmi5Session()
    {
        return $this->returned_for_cmi5_session;
    }

    /**
     * @param string $returned_for_cmi5_session
     */
    public function setReturnedForCmi5Session($returned_for_cmi5_session)
    {
        $this->returned_for_cmi5_session = $returned_for_cmi5_session;
    }

    public function update()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $DIC->database()->update(
            self::DB_TABLE_NAME,
            [
                'valid_until' => array('timestamp', $this->getValidUntil()),
                'ref_id' => array('integer', $this->getRefId()),
                'obj_id' => array('integer', $this->getObjId()),
                'usr_id' => array('integer', $this->getUsrId()),
                'lrs_type_id' => array('integer', $this->getLrsTypeId()),
                'cmi5_session' => array('text', $this->getCmi5Session()),
                'returned_for_cmi5_session' => array('text', $this->getReturnedForCmi5Session()),
                'cmi5_session_data' => array('clob', $this->getCmi5SessionData())
            ],
            [
                'token' => array('text', $this->getToken()),
            ]
        );
    }
    
    public static function insertToken($usrId, $refId, $objId, $lrsTypeId, $a_token, $a_time)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilDB = $DIC->database();
        
        $ilDB->insert(
            self::DB_TABLE_NAME,
            array(
                'token' => array('text', $a_token),
                'valid_until' => array('timestamp', $a_time),
                'ref_id' => array('integer', $refId),
                'obj_id' => array('integer', $objId),
                'usr_id' => array('integer', $usrId),
                'lrs_type_id' => array('integer', $lrsTypeId)
            )
        );
        // 'cmi5_session' defaults always to '' by inserting
        // 'returned_for_cmi5_session' defaults always to '' by inserting
    }
    
    public static function deleteTokenByObjIdAndUsrId($objId, $usrId)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilDB = $DIC->database();
        
        $query = "
			DELETE FROM " . self::DB_TABLE_NAME . "
			WHERE obj_id = %s AND usr_id = %s
		";
        
        $ilDB->manipulateF($query, array('integer', 'integer'), array($objId, $usrId));
    }

    public static function deleteTokenByObjIdAndRefIdAndUsrId($objId, $refId, $usrId)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilDB = $DIC->database();
        
        $query = "
			DELETE FROM " . self::DB_TABLE_NAME . "
			WHERE obj_id = %s AND ref_id = %s AND usr_id = %s
		";
        
        $ilDB->manipulateF($query, array('integer', 'integer', 'integer'), array($objId, $refId, $usrId));
    }
    
    public function delete()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilDB = $DIC->database();
        
        $query = "
			DELETE FROM " . self::DB_TABLE_NAME . "
			WHERE obj_id = %s AND ref_id = %s AND usr_id = %s
		";
        
        $ilDB->manipulateF($query, array('integer', 'integer', 'integer'), array($this->getObjId(), $this->getRefId(), $this->getUsrId()));
    }

    public static function deleteExpiredTokens()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilDB = $DIC->database();
        
        $query = "DELETE FROM " . self::DB_TABLE_NAME . " WHERE valid_until < CURRENT_TIMESTAMP";
        $ilDB->manipulate($query);
    }
    
    
    public static function selectCurrentTimestamp()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilDB = $DIC->database();

        $query = "SELECT CURRENT_TIMESTAMP";
        $result = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($result);
        
        return $row['CURRENT_TIMESTAMP'];
    }
    
    public static function createToken()
    {
        return (new \Ramsey\Uuid\UuidFactory())->uuid4()->toString();
    }
    
    public static function fillToken($usrId, $refId, $objId, $lrsTypeId = 0)
    {
        //$seconds = $this->getTimeToDelete();
        $seconds = 86400; // TODO: invalidation interval
        
        $nowTimeDT = self::selectCurrentTimestamp();
        
        $nowTime = new ilDateTime($nowTimeDT, IL_CAL_DATETIME);
        
        $nowTimeTS = $nowTime->get(IL_CAL_UNIX);
        $newTimeTS = $nowTimeTS + $seconds;
        
        $newTime = new ilDateTime($newTimeTS, IL_CAL_UNIX);
        
        //self::deleteTokenByObjIdAndUsrId($object->getId(), $usrId);
        
        try {
            $tokenObject = self::getInstanceByObjIdAndRefIdAndUsrId($objId, $refId, $usrId, false);
            $tokenObject->setValidUntil($newTime->get(IL_CAL_DATETIME));
            $tokenObject->update();
            
            $token = $tokenObject->getToken();
        } catch (ilCmiXapiException $e) {
            $token = self::createToken();
            self::insertToken($usrId, $refId, $objId, $lrsTypeId, $token, $newTime->get(IL_CAL_DATETIME));
        }
        
        // TODO: move to cronjob ;-)
        // TODO: check cmi5 sessions of token and if not terminated -> abandoned statement
        self::deleteExpiredTokens();
        
        return $token;
    }
    
    /**
     * @param $token
     * @return ilCmiXapiAuthToken
     * @throws ilCmiXapiException
     */
    public static function getInstanceByToken($token)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $query = "
			SELECT * FROM " . self::DB_TABLE_NAME . "
			WHERE token = %s AND valid_until > CURRENT_TIMESTAMP
		";
        
        $res = $DIC->database()->queryF($query, array('text'), array($token));
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $tokenObject = new self();
            $tokenObject->setToken($token);
            $tokenObject->setValidUntil($row['valid_until']);
            $tokenObject->setUsrId($row['usr_id']);
            $tokenObject->setObjId($row['obj_id']);
            $tokenObject->setRefId($row['ref_id']);
            $tokenObject->setLrsTypeId($row['lrs_type_id']);
            $tokenObject->setCmi5Session($row['cmi5_session']);
            $tokenObject->setReturnedForCmi5Session($row['returned_for_cmi5_session']);
            $tokenObject->setCmi5SessionData($row['cmi5_session_data']);
            
            return $tokenObject;
        }
        
        throw new ilCmiXapiException('no valid token found for: ' . $token);
    }
    
    /**
     * @param int $objId
     * @param int $usrId
     * @return ilCmiXapiAuthToken
     * @throws ilCmiXapiException
     */
    public static function getInstanceByObjIdAndUsrId($objId, $usrId, $checkValid = true)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilDB = $DIC->database();
        
        $query = "SELECT * FROM " . self::DB_TABLE_NAME . " WHERE obj_id = %s AND usr_id = %s";
        
        if ($checkValid) {
            $query .= " AND valid_until > CURRENT_TIMESTAMP";
        }
        
        $result = $ilDB->queryF($query, array('integer', 'integer'), array($objId, $usrId));
        
        $row = $ilDB->fetchAssoc($result);
        
        if ($row) {
            $tokenObject = new self();
            $tokenObject->setToken($row['token']);
            $tokenObject->setValidUntil($row['valid_until']);
            $tokenObject->setUsrId($row['usr_id']);
            $tokenObject->setObjId($row['obj_id']);
            $tokenObject->setRefId($row['ref_id']);
            $tokenObject->setLrsTypeId($row['lrs_type_id']);
            $tokenObject->setCmi5Session($row['cmi5_session']);
            $tokenObject->setReturnedForCmi5Session($row['returned_for_cmi5_session']);
            $tokenObject->setCmi5SessionData($row['cmi5_session_data']);
            
            return $tokenObject;
        }
        
        throw new ilCmiXapiException('no valid token found for: ' . $objId . '/' . $usrId);
    }
    
    /**
     * @param int $objId
     * @param int $refId
     * @param int $usrId
     * @return ilCmiXapiAuthToken
     * @throws ilCmiXapiException
     */
    public static function getInstanceByObjIdAndRefIdAndUsrId($objId, $refId, $usrId, $checkValid = true)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilDB = $DIC->database();
        
        $query = "SELECT * FROM " . self::DB_TABLE_NAME . " WHERE obj_id = %s AND ref_id = %s AND usr_id = %s";
        
        if ($checkValid) {
            $query .= " AND valid_until > CURRENT_TIMESTAMP";
        }
        
        $result = $ilDB->queryF($query, array('integer', 'integer', 'integer'), array($objId, $refId, $usrId));
        
        $row = $ilDB->fetchAssoc($result);
        
        if ($row) {
            $tokenObject = new self();
            $tokenObject->setToken($row['token']);
            $tokenObject->setValidUntil($row['valid_until']);
            $tokenObject->setUsrId($row['usr_id']);
            $tokenObject->setObjId($row['obj_id']);
            $tokenObject->setRefId($row['ref_id']);
            $tokenObject->setLrsTypeId($row['lrs_type_id']);
            $tokenObject->setCmi5Session($row['cmi5_session']);
            $tokenObject->setReturnedForCmi5Session($row['returned_for_cmi5_session']);
            $tokenObject->setCmi5SessionData($row['cmi5_session_data']);
            
            return $tokenObject;
        }
        
        throw new ilCmiXapiException('no valid token found for: ' . $objId . '/' . $usrId);
    }

    /*
    public static function bindCmi5Session(string $token, string $cmi5_session)
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilDB->manipulate("UPDATE " . self::DB_TABLE_NAME . " SET cmi5_session = " . $ilDB->quote($cmi5_session, 'text') . " WHERE token = " . $ilDB->quote($token, 'text'));
    }
    */

    /**
     * @param int $usrId
     * @param int $objId
     * @param int $refId
     * @return string
     * @throws ilCmiXapiException
     */
    
    public static function getCmi5SessionByUsrIdAndObjIdAndRefId(int $usrId, int $objId, $refId = null)
    {
        global $DIC;
        $ilDB = $DIC->database();
        if (empty($refId)) {
            $query = "SELECT cmi5_session FROM " . self::DB_TABLE_NAME . " WHERE usr_id = %s AND obj_id = %s";
            $result = $ilDB->queryF($query, array('integer', 'integer'), array($usrId, $objId));
        } else {
            $query = "SELECT cmi5_session FROM " . self::DB_TABLE_NAME . " WHERE usr_id = %s AND obj_id = %s AND ref_id = %s";
            $result = $ilDB->queryF($query, array('integer', 'integer', 'integer'), array($usrId, $objId, $refId));
        }
        
        $row = $ilDB->fetchAssoc($result);
        
        if ($row && $row['cmi5_session'] != '') {
            return $row['cmi5_session'];
        }
        throw new ilCmiXapiException('no valid cmi5_session found for: ' . $objId . '/' . $usrId);
    }
    
    /**
     * @return string
     * @throws ilCmiXapiException
     */
    public static function getWacSalt()
    {
        include 'data/wacsalt.php';
        
        if (isset($salt)) {
            return $salt;
        }
        
        throw new ilCmiXapiException('no salt for encryption provided');
    }
}
