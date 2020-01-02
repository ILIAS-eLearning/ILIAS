<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Logging/classes/class.ilLogComponentLevel.php';


/**
 * individual log levels for components
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 */
class ilLogComponentLevels
{
    protected static $instance = null;
    protected $components = array();
    
    /**
     * constructor
     */
    protected function __construct()
    {
        $this->read();
    }
    
    /**
     *
     * @return ilLogComponentLevels
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     *
     * @global type $ilDB
     * @param type $a_component_id
     */
    public static function updateFromXML($a_component_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$a_component_id) {
            return false;
        }
        
        $query = 'SELECT * FROM log_components ' .
                'WHERE component_id = ' . $ilDB->quote($a_component_id, 'text');
        $res = $ilDB->query($query);
        if (!$res->numRows()) {
            $query = 'INSERT INTO log_components (component_id) ' .
                    'VALUES (' .
                    $ilDB->quote($a_component_id, 'text') .
                    ')';
            $ilDB->manipulate($query);
        }
        return true;
    }
    
    /**
     * Get compponent level
     * @return ilLogComponentLevel[]
     */
    public function getLogComponents()
    {
        return $this->components;
    }
    
    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT * FROM log_components ';
        $res = $ilDB->query($query);
        
        $this->components = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->components[] = new ilLogComponentLevel($row->component_id, $row->log_level);
        }
    }
}
