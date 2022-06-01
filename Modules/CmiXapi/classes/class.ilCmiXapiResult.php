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
    protected int $id;
 
    protected int $objId;

    protected int $usrId;

    protected int $version;

    protected float $score;

    protected string $status;

    protected string $lastUpdate;

    private ilDBInterface $database;
    
    /**
     * ilCmiXapiResult constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->database = $DIC->database();
        $this->id = 0;
        $this->objId = 0;
        $this->usrId = 0;
        $this->version = 0;
        $this->score = 0.0;
        $this->status = '';
        $this->lastUpdate = '';
    }
    
    public function getId() : int
    {
        return $this->id;
    }
    
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    
    public function getObjId() : int
    {
        return $this->objId;
    }
    
    public function setObjId(int $objId) : void
    {
        $this->objId = $objId;
    }
    
    public function getUsrId() : int
    {
        return $this->usrId;
    }
    
    public function setUsrId(int $usrId) : void
    {
        $this->usrId = $usrId;
    }
    
    public function getVersion() : int
    {
        return $this->version;
    }
    
    public function setVersion(int $version) : void
    {
        $this->version = $version;
    }
    
    public function getScore() : float
    {
        return $this->score;
    }
    
    public function setScore(float $score) : void
    {
        $this->score = $score;
    }
    
    public function getStatus() : string
    {
        return $this->status;
    }
    
    public function setStatus(string $status) : void
    {
        $this->status = $status;
    }
    
    public function getLastUpdate() : string
    {
        return $this->lastUpdate;
    }
    
    public function setLastUpdate(string $lastUpdate) : void
    {
        $this->lastUpdate = $lastUpdate;
    }
    
    public function save() : void
    {
        if ($this->getId()) {
            $this->update();
        } else {
            $this->insert();
        }
    }
    
    protected function update() : void
    {
        $this->database->update('cmix_results', array(
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
    
    protected function insert() : void
    {
        $this->setId($this->database->nextId('cmix_results'));
        
        $this->database->insert('cmix_results', array(
            'id' => array('intger', $this->getId()),
            'obj_id' => array('intger', $this->getObjId()),
            'usr_id' => array('intger', $this->getUsrId()),
            'version' => array('intger', $this->getVersion()),
            'score' => array('float', $this->getScore()),
            'status' => array('text', $this->getStatus()),
            'last_update' => array('timestamp', ilCmiXapiAuthToken::selectCurrentTimestamp())
        ));
    }

    protected function assignFromDbRow(array $row) : void
    {
        $this->setId($row['id']);
        $this->setObjId($row['obj_id']);
        $this->setUsrId($row['usr_id']);
        $this->setVersion($row['version']);
        $this->setScore($row['score']);
        $this->setStatus($row['status']);
        $this->setLastUpdate($row['last_update']);
    }

    /**
     * @throws ilCmiXapiException
     */
    public static function getInstanceByObjIdAndUsrId(int $objId, int $usrId) : \ilCmiXapiResult
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

    public static function getEmptyInstance() : \ilCmiXapiResult
    {
        return new self();
    }

    /**
     * @return ilCmiXapiResult[]
     */
    public static function getResultsForObject(int $objId) : array
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
