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
    
    public function update()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->database()->update(
            'cmix_token',
            [
                'valid_until' => array('timestamp', $this->getValidUntil()),
                'ref_id' => array('integer', $this->getRefId()),
                'obj_id' => array('integer', $this->getObjId()),
                'usr_id' => array('integer', $this->getUsrId()),
                'lrs_type_id' => array('integer', $this->getLrsTypeId())
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
            'cmix_token',
            array(
                'token' => array('text', $a_token),
                'valid_until' => array('timestamp', $a_time),
                'ref_id' => array('integer', $refId),
                'obj_id' => array('integer', $objId),
                'usr_id' => array('integer', $usrId),
                'lrs_type_id' => array('integer', $lrsTypeId)
            )
        );
    }
    
    public static function deleteTokenByObjIdAndUsrId($objId, $usrId)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilDB = $DIC->database();
        
        $query = "
			DELETE FROM cmix_token
			WHERE obj_id = %s AND usr_id = %s
		";
        
        $ilDB->manipulateF($query, array('integer', 'integer'), array($objId, $usrId));
    }
    
    public static function deleteExpiredTokens()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilDB = $DIC->database();
        
        $query = "DELETE FROM cmix_token WHERE valid_until < CURRENT_TIMESTAMP";
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
            $tokenObject = self::getInstanceByObjIdAndUsrId($objId, $usrId, false);
            $tokenObject->setValidUntil($newTime->get(IL_CAL_DATETIME));
            $tokenObject->update();
            
            $token = $tokenObject->getToken();
        } catch (ilCmiXapiException $e) {
            $token = self::createToken();
            self::insertToken($usrId, $refId, $objId, $lrsTypeId, $token, $newTime->get(IL_CAL_DATETIME));
        }
        
        // TODO: move to cronjob ;-)
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
			SELECT * FROM cmix_token
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
        
		$query = "SELECT * FROM cmix_token WHERE obj_id = %s AND usr_id = %s";
		
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
            
            return $tokenObject;
        }
        
        throw new ilCmiXapiException('no valid token found for: ' . $objId . '/' . $usrId);
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
