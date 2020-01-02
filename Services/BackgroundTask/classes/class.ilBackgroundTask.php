<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Background task
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesBackgroundTask
 */
class ilBackgroundTask
{
    protected $id; // [int]
    protected $user_id; // [int]
    protected $start_date; // [DateTime]
    protected $status; // [string]
    protected $steps; // [int]
    protected $current_step; // [int]
    protected $handler; // [string]
    protected $params; // [array]
    protected $exists; // [bool]

    /**
     * @var ilLogger
     */
    protected $log;
    
    // :TODO:
    const STATUS_INITIALIZED = "initialized";
    const STATUS_PROCESSING = "processing";
    const STATUS_PROCESSED = "processed";
    const STATUS_CANCELLING = "cancelling";
    const STATUS_CANCELLED = "cancelled";
    const STATUS_FINISHED = "finished";
    const STATUS_FAILED = "failed";
    
    const DB_NAME = "background_task";
    
    /**
     * Constructor
     *
     * @param int $a_id
     * @return self
     */
    public function __construct($a_id = 0)
    {
        $this->log = ilLoggerFactory::getLogger('btsk');
        if ($a_id != 0) {
            $this->doRead($a_id);
        }
    }
    
    /**
     *
     * @return \ilBackgroundTaskHandler
     */
    public function getHandlerInstance()
    {
        $handler_id = $this->getHandlerId();
        $this->log->debug("Handler ID: " . $handler_id);
        if ($handler_id) {
            include_once "Services/BackgroundTask/classes/class." . $handler_id . ".php";
            return $handler_id::getInstanceFromTask($this);
        }
    }
    
    public static function getActiveByUserId($a_user_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $res = array();
        
        $set = $ilDB->query("SELECT id FROM " . self::DB_NAME .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND status = " . $ilDB->quote(self::STATUS_PROCESSING, "text"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row["id"];
        }
        
        return $res;
    }
    
    public function isToBeCancelled()
    {
        $this->doRead($this->getId());
        return ($this->getStatus() == self::STATUS_CANCELLING);
    }
    

    //
    // setter/getter
    //
    
    /**
     * Gets the handler.
     *
     * @return string The value.
     */
    public function getHandlerId()
    {
        return $this->handler;
    }
    
    /**
     * Sets the handler id.
     *
     * @param $a_val The new value.
     */
    public function setHandlerId($a_val)
    {
        $this->handler = $a_val;
    }
    
    /**
     * Gets the id.
     *
     * @return int The value.
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Sets the id.
     *
     * @param int $a_val The new value.
     */
    protected function setId($a_val)
    {
        $this->id = (int) $a_val;
    }

    /**
     * Gets the user id.
     *
     * @return int The value.
     */
    public function getUserId()
    {
        return $this->user_id;
    }
    
    /**
     * Sets the user id.
     *
     * @param int $a_val The new value.
     */
    public function setUserId($a_val)
    {
        $this->user_id = (int) $a_val;
    }
    
    /**
     * Gets the date when the download was started.
     *
     * @return ilDateTime The value.
     */
    public function getStartDate()
    {
        return $this->start_date;
    }
    
    /**
     * Sets the date when the download was started.
     *
     * @param ilDateTime $a_val The new value.
     */
    protected function setStartDate(ilDateTime $a_val)
    {
        $this->start_date = $a_val;
    }
    
    /**
     * Gets the status.
     *
     * @return int The value.
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    protected function getValidStatus()
    {
        return array(
            self::STATUS_INITIALIZED,
            self::STATUS_PROCESSING,
            self::STATUS_CANCELLING,
            self::STATUS_CANCELLED,
            self::STATUS_PROCESSED,
            self::STATUS_FINISHED,
            self::STATUS_FAILED,
        );
    }
    
    /**
     * Sets the status.
     *
     * @param int $a_val The new value.
     */
    public function setStatus($a_val)
    {
        if (in_array($a_val, $this->getValidStatus())) {
            $this->status = $a_val;
        }
    }

    /**
     * Gets the steps.
     *
     * @return int The value.
     */
    public function getSteps()
    {
        return $this->steps;
    }
    
    /**
     * Sets the steps.
     *
     * @param int $a_val The new value.
     */
    public function setSteps($a_val)
    {
        $this->steps = abs($a_val);
    }
    
    /**
     * Gets the current step.
     *
     * @return int The value.
     */
    public function getCurrentStep()
    {
        return $this->current_step;
    }
    
    /**
     * Sets the current step.
     *
     * @param int $a_val The new value.
     */
    public function setCurrentStep($a_val)
    {
        $this->current_step = min(abs($a_val), $this->getSteps());
    }
    
    /**
     * Gets the params.
     *
     * @return int The value.
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Sets the params.
     *
     * @param array $a_val The new value.
     */
    public function setParams(array $a_params = null)
    {
        $this->params = $a_params;
    }
        
    /**
     * Gets whether the download object exists.
     *
     * @return boolean The value.
     */
    public function exists()
    {
        return $this->exists;
    }
    
    
    //
    // CRUD
    //
    
    public function save()
    {
        $this->log->debug($this->getHandlerId());
        // does not exist yet?
        if (!$this->exists) {
            $this->doCreate();
        } else {
            $this->doUpdate();
        }
    }
    
    public function delete()
    {
        $this->log->debug($this->getHandlerId());
        if (!$this->exists) {
            return;
        }
        
        return $this->doDelete();
    }
    
    
    protected function doRead($a_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $this->log->debug($this->getHandlerId() . "/" . $a_id);
        
        $set = $ilDB->queryF(
            "SELECT * FROM " . self::DB_NAME . " WHERE id=%s",
            array("integer"),
            array($a_id)
        );
        
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->setId($a_id);
            $this->exists = true;
            
            $this->setUserId($rec["user_id"]);
            $this->setStartDate(new ilDateTime($rec["start_date"], IL_CAL_DATETIME));
            $this->setStatus($rec["status"]);
            $this->setSteps($rec["steps"]);
            $this->setCurrentStep($rec["cstep"]);
            $this->setHandlerId($rec["handler"]);
            $this->setParams($rec["params"]
                ? unserialize($rec["params"])
                : null);
        }
    }
    
    protected function preparePropertiesForDB()
    {
        return array(
            "user_id" => array("integer", $this->getUserId()),
            "start_date" => array("timestamp", $this->getStartDate()->get(IL_CAL_DATETIME)),
            "status" => array("text", $this->getStatus()),
            "steps" => array("integer", $this->getSteps()),
            "cstep" => array("integer", $this->getCurrentStep()),
            "handler" => array("string", $this->getHandlerId()),
            "params" => array("string", is_array($this->getParams())
                ? serialize($this->getParams())
                : null)
        );
    }
    
    protected function doCreate()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
                        
        $this->setId($ilDB->nextId(self::DB_NAME));
        $this->setStartDate(new ilDateTime(time(), IL_CAL_UNIX));
        
        $fields = $this->preparePropertiesForDB();
        $fields["id"] = array("integer", $this->getId());
        
        $ilDB->insert(self::DB_NAME, $fields);
        
        $this->exists = true;
    }
    
    protected function doUpdate()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $ilDB->update(
            self::DB_NAME,
            $this->preparePropertiesForDB(),
            array("id" => array("integer", $this->getId()))
        );
    }
    
    /**
     * Deletes the object from the database.
     */
    protected function doDelete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        return $ilDB->manipulateF(
            "DELETE FROM " . self::DB_NAME . " WHERE id=%s",
            array("integer"),
            array($this->getId())
        );
    }
}
