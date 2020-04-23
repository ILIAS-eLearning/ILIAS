<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjLTIConsumerLaunch
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */

class ilLTIConsumerResult
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $obj_id;

    /**
     * @var integer
     */
    public $usr_id;

    /**
     * @var float
     */
    public $result;


    /**
     * Get a result by id
     * @param integer id
     * @return LTIConsumerResult of null if not exists
     */
    public static function getById($a_id)
    {
        global $DIC;

        $query = 'SELECT * FROM lti_consumer_results'
            . ' WHERE id = ' . $DIC->database()->quote($a_id, 'integer');

        $res = $DIC->database()->query($query);
        if ($row = $DIC->database()->fetchAssoc($res)) {
            $resObj = new ilLTIConsumerResult;
            $resObj->fillData($row);
            return $resObj;
        } else {
            return null;
        }
    }

    /**
     * Get a result by object and user key
     *
     * @param integer   object id
     * @param integer   user id
     * @param boolean   save a new result object result if not exists
     *
     * @return ilLTIConsumerResult
     */
    public static function getByKeys($a_obj_id, $a_usr_id, $a_create = false)
    {
        global $DIC;

        $query = 'SELECT * FROM lti_consumer_results'
            . ' WHERE obj_id = ' . $DIC->database()->quote($a_obj_id, 'integer')
            . ' AND usr_id = ' . $DIC->database()->quote($a_usr_id, 'integer');

        $res = $DIC->database()->query($query);
        if ($row = $DIC->database()->fetchAssoc($res)) {
            $resObj = new ilLTIConsumerResult;
            $resObj->fillData($row);
            return $resObj;
        } elseif ($a_create) {
            $resObj = new ilLTIConsumerResult;
            $resObj->obj_id = $a_obj_id;
            $resObj->usr_id = $a_usr_id;
            $resObj->result = null;
            $resObj->save();
            return $resObj;
        } else {
            return null;
        }
    }

    /**
     * Fill the properties with data from an array
     * @param array assoc data
     */
    protected function fillData($data)
    {
        $this->id = $data['id'];
        $this->obj_id = $data['obj_id'];
        $this->usr_id = $data['usr_id'];
        $this->result = $data['result'];
    }

    /**
     * Save a result object
     */
    public function save()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if (!isset($this->usr_id) or !isset($this->obj_id)) {
            return false;
        }
        if (!isset($this->id)) {
            $this->id = $DIC->database()->nextId('lti_consumer_results');
        }
        $DIC->database()->replace(
            'lti_consumer_results',
            array(
                'id' => array('integer', $this->id)
            ),
            array(
                'obj_id' => array('integer', $this->obj_id),
                'usr_id' => array('integer', $this->usr_id),
                'result' => array('float', $this->result)
            )
        );
        return true;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }
    
    /**
     * @return int
     */
    public function getUsrId()
    {
        return $this->usr_id;
    }
    
    /**
     * @return float
     */
    public function getResult()
    {
        return $this->result;
    }
    
    /**
     * @param $objId
     * @return ilLTIConsumerResult[]
     */
    public static function getResultsForObject($objId)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $query = 'SELECT * FROM lti_consumer_results'
            . ' WHERE obj_id = ' . $DIC->database()->quote($objId, 'integer');
        
        $res = $DIC->database()->query($query);
        
        $results = [];
        
        if ($row = $DIC->database()->fetchAssoc($res)) {
            $resObj = new ilLTIConsumerResult;
            $resObj->fillData($row);
            
            $results[$resObj->getUsrId()] = $resObj;
        }
        
        return $results;
    }
}
