<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiResult
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiResult
{
    /**
     * @var int
     */
    protected $id;
    
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
    protected $version;
    
    /**
     * @var float
     */
    protected $score;
    
    /**
     * @var string
     */
    protected $status;
    
    /**
     * @var string
     */
    protected $lastUpdate;
    
    /**
     * ilCmiXapiResult constructor.
     * @param int $id
     * @param int $objId
     * @param int $usrId
     * @param int $version
     * @param float $score
     * @param string $status
     * @param string $lastUpdate
     */
    public function __construct()
    {
        $this->id = 0;
        $this->objId = 0;
        $this->usrId = 0;
        $this->version = 0;
        $this->score = 0.0;
        $this->status = '';
        $this->lastUpdate = '';
    }
    
    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    /**
     * @return int
     */
    public function getObjId() : int
    {
        return $this->objId;
    }
    
    /**
     * @param int $objId
     */
    public function setObjId(int $objId)
    {
        $this->objId = $objId;
    }
    
    /**
     * @return int
     */
    public function getUsrId() : int
    {
        return $this->usrId;
    }
    
    /**
     * @param int $usrId
     */
    public function setUsrId(int $usrId)
    {
        $this->usrId = $usrId;
    }
    
    /**
     * @return int
     */
    public function getVersion() : int
    {
        return $this->version;
    }
    
    /**
     * @param int $version
     */
    public function setVersion(int $version)
    {
        $this->version = $version;
    }
    
    /**
     * @return float
     */
    public function getScore() : float
    {
        return $this->score;
    }
    
    /**
     * @param float $score
     */
    public function setScore(float $score)
    {
        $this->score = $score;
    }
    
    /**
     * @return string
     */
    public function getStatus() : string
    {
        return $this->status;
    }
    
    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }
    
    /**
     * @return string
     */
    public function getLastUpdate() : string
    {
        return $this->lastUpdate;
    }
    
    /**
     * @param string $lastUpdate
     */
    public function setLastUpdate(string $lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;
    }
    
    public function save()
    {
        if ($this->getId()) {
            $this->update();
        } else {
            $this->insert();
        }
    }
    
    protected function update()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->database()->update('cmix_results', array(
                'obj_id' => array('intger', $this->getObjId()),
                'usr_id' => array('intger', $this->getUsrId()),
                'version' => array('intger', $this->getVersion()),
                'score' => array('float', $this->getScore()),
                'status' => array('text', $this->getStatus()),
                'last_update' => array('timestamp', ilCmiXapiAuthToken::selectCurrentTimestamp())
            ), array(
                'id' => array('intger', $this->getId())
        ));
    }
    
    protected function insert()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $this->setId($DIC->database()->nextId('cmix_results'));
        
        $DIC->database()->insert('cmix_results', array(
            'id' => array('intger', $this->getId()),
            'obj_id' => array('intger', $this->getObjId()),
            'usr_id' => array('intger', $this->getUsrId()),
            'version' => array('intger', $this->getVersion()),
            'score' => array('float', $this->getScore()),
            'status' => array('text', $this->getStatus()),
            'last_update' => array('timestamp', ilCmiXapiAuthToken::selectCurrentTimestamp())
        ));
    }
    
    /**
     * @param ilCmiXapiResult $result
     * @param $row
     */
    protected function assignFromDbRow($row)
    {
        $this->setId($row['id']);
        $this->setObjId($row['obj_id']);
        $this->setUsrId($row['usr_id']);
        $this->setVersion($row['version']);
        $this->setScore($row['score']);
        $this->setStatus($row['status']);
        $this->setLastUpdate($row['last_update']);
    }
    
    public static function getInstanceByObjIdAndUsrId($objId, $usrId)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $query = "
			SELECT * FROM cmix_results
			WHERE obj_id = %s AND usr_id = %s
		";
        
        $res = $DIC->database()->queryF($query, array('integer', 'integer'), array($objId, $usrId));
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $result = new self();
            $result->assignFromDbRow($row);
            
            return $result;
        }
        
        throw new ilCmiXapiException(
            "no result record exists for: usr=$usrId obj=$objId"
        );
    }
    
    public static function getEmptyInstance()
    {
        return new self();
    }
    
    /**
     * @param $objId
     * @return ilCmiXapiResult[]
     */
    public static function getResultsForObject($objId)
    {
        global $DIC;
        
        $query = 'SELECT * FROM cmix_results'
            . ' WHERE obj_id = ' . $DIC->database()->quote($objId, 'integer');
        
        $res = $DIC->database()->query($query);
        
        $results = [];
        
        if ($row = $DIC->database()->fetchAssoc($res)) {
            $result = new self();
            $result->assignFromDbRow($row);
            
            $results[$result->getUsrId()] = $result;
        }
        
        return $results;
    }
}
