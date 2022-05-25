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
    
    protected int $ref_id;
    
    protected int $obj_id;
    
    protected int $usr_id;
    
    protected string $token;

    protected string $valid_until;

    protected int $lrs_type_id;

    protected ?string $cmi5_session;

    protected string $cmi5_session_data;

    protected ?string $returned_for_cmi5_session;
    
    protected ilDBInterface $db;
    
    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
    }
    
    public function getRefId() : int
    {
        return $this->ref_id;
    }
    
    public function setRefId(int $ref_id) : void
    {
        $this->ref_id = $ref_id;
    }
    
    public function getObjId() : int
    {
        return $this->obj_id;
    }
    
    public function setObjId(int $obj_id) : void
    {
        $this->obj_id = $obj_id;
    }
    
    public function getUsrId() : int
    {
        return $this->usr_id;
    }
    
    public function setUsrId(int $usr_id) : void
    {
        $this->usr_id = $usr_id;
    }
    
    public function getToken() : string
    {
        return $this->token;
    }
    
    public function setToken(string $token) : void
    {
        $this->token = $token;
    }
    
    public function getValidUntil() : string
    {
        return $this->valid_until;
    }
    
    public function setValidUntil(string $valid_until) : void
    {
        $this->valid_until = $valid_until;
    }
    
    public function getLrsTypeId() : int
    {
        return $this->lrs_type_id;
    }
    
    public function setLrsTypeId(int $lrs_type_id) : void
    {
        $this->lrs_type_id = $lrs_type_id;
    }

    public function getCmi5Session() : ?string
    {
        return $this->cmi5_session;
    }

    public function setCmi5Session(string $cmi5_session) : void
    {
        $this->cmi5_session = $cmi5_session;
    }

    public function getCmi5SessionData() : ?string
    {
        return $this->cmi5_session_data;
    }

    public function setCmi5SessionData(string $cmi5_session_data) : void
    {
        $this->cmi5_session_data = $cmi5_session_data;
    }

    public function getReturnedForCmi5Session() : ?string
    {
        return $this->returned_for_cmi5_session;
    }

    public function setReturnedForCmi5Session(string $returned_for_cmi5_session) : void
    {
        $this->returned_for_cmi5_session = $returned_for_cmi5_session;
    }

    public function update() : void
    {
        $this->db->update(
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
    
    public static function insertToken($usrId, $refId, $objId, $lrsTypeId, $a_token, $a_time) : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->database()->insert(
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
    
    public static function deleteTokenByObjIdAndUsrId(int $objId, int $usrId) : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $query = "
			DELETE FROM " . self::DB_TABLE_NAME . "
			WHERE obj_id = %s AND usr_id = %s
		";
        
        $DIC->database()->manipulateF($query, array('integer', 'integer'), array($objId, $usrId));
    }

    public static function deleteTokenByObjIdAndRefIdAndUsrId(int $objId, int $refId, int $usrId) : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = "
			DELETE FROM " . self::DB_TABLE_NAME . "
			WHERE obj_id = %s AND ref_id = %s AND usr_id = %s
		";
        
        $DIC->database()->manipulateF($query, array('integer', 'integer', 'integer'), array($objId, $refId, $usrId));
    }
    
    public function delete() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = "
			DELETE FROM " . self::DB_TABLE_NAME . "
			WHERE obj_id = %s AND ref_id = %s AND usr_id = %s
		";
        
        $DIC->database()->manipulateF($query, array('integer', 'integer', 'integer'), array($this->getObjId(), $this->getRefId(), $this->getUsrId()));
    }

    public static function deleteExpiredTokens() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = "DELETE FROM " . self::DB_TABLE_NAME . " WHERE valid_until < CURRENT_TIMESTAMP";
        $DIC->database()->manipulate($query);
    }
    
    
    public static function selectCurrentTimestamp() : string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = "SELECT CURRENT_TIMESTAMP";
        $result = $DIC->database()->query($query);
        $row = $DIC->database()->fetchAssoc($result);
        
        return (string) $row['CURRENT_TIMESTAMP'];
    }
    
    public static function createToken() : string
    {
        return (new \Ramsey\Uuid\UuidFactory())->uuid4()->toString();
    }
    
    public static function fillToken(int $usrId, int $refId, int $objId, int $lrsTypeId = 0) : string
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
     * @throws ilCmiXapiException
     */
    public static function getInstanceByToken(string $token) : \ilCmiXapiAuthToken
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
     * @throws ilCmiXapiException
     */
    public static function getInstanceByObjIdAndUsrId(int $objId, int $usrId, bool $checkValid = true) : \ilCmiXapiAuthToken
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = "SELECT * FROM " . self::DB_TABLE_NAME . " WHERE obj_id = %s AND usr_id = %s";
        
        if ($checkValid) {
            $query .= " AND valid_until > CURRENT_TIMESTAMP";
        }
        
        $result = $DIC->database()->queryF($query, array('integer', 'integer'), array($objId, $usrId));
        
        $row = $DIC->database()->fetchAssoc($result);
        
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
     * @throws ilCmiXapiException
     */
    public static function getInstanceByObjIdAndRefIdAndUsrId(int $objId, int $refId, int $usrId, bool $checkValid = true) : \ilCmiXapiAuthToken
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = "SELECT * FROM " . self::DB_TABLE_NAME . " WHERE obj_id = %s AND ref_id = %s AND usr_id = %s";
        
        if ($checkValid) {
            $query .= " AND valid_until > CURRENT_TIMESTAMP";
        }
        
        $result = $DIC->database()->queryF($query, array('integer', 'integer', 'integer'), array($objId, $refId, $usrId));
        
        $row = $DIC->database()->fetchAssoc($result);
        
        if ($row) {
            $tokenObject = new self();
            $tokenObject->setToken($row['token']);
            $tokenObject->setValidUntil($row['valid_until']);
            $tokenObject->setUsrId((int) $row['usr_id']);
            $tokenObject->setObjId((int) $row['obj_id']);
            $tokenObject->setRefId((int) $row['ref_id']);
            $tokenObject->setLrsTypeId((int) $row['lrs_type_id']);
            $tokenObject->setCmi5Session($row['cmi5_session']);
            $tokenObject->setReturnedForCmi5Session($row['returned_for_cmi5_session']);
            $tokenObject->setCmi5SessionData((string) $row['cmi5_session_data']);
            
            return $tokenObject;
        }
        
        throw new ilCmiXapiException('no valid token found for: ' . $objId . '/' . $usrId);
    }

    /**
     * @throws ilCmiXapiException
     */
    public static function getCmi5SessionByUsrIdAndObjIdAndRefId(int $usrId, int $objId, ?int $refId = null) : string
    {
        global $DIC;
        
        if (empty($refId)) {
            $query = "SELECT cmi5_session FROM " . self::DB_TABLE_NAME . " WHERE usr_id = %s AND obj_id = %s";
            $result = $DIC->database()->queryF($query, array('integer', 'integer'), array($usrId, $objId));
        } else {
            $query = "SELECT cmi5_session FROM " . self::DB_TABLE_NAME . " WHERE usr_id = %s AND obj_id = %s AND ref_id = %s";
            $result = $DIC->database()->queryF($query, array('integer', 'integer', 'integer'), array($usrId, $objId, $refId));
        }
        
        $row = $DIC->database()->fetchAssoc($result);
        
        if ($row && $row['cmi5_session'] != '') {
            return $row['cmi5_session'];
        }
        throw new ilCmiXapiException('no valid cmi5_session found for: ' . $objId . '/' . $usrId);
    }

    /**
     * @throws ilCmiXapiException
     */
    public static function getWacSalt() : string
    {
        if (isset($salt)) {
            return $salt;
        }
        
        throw new ilCmiXapiException('no salt for encryption provided');
    }
}
