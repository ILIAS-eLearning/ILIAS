<?php declare(strict_types=1);

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */



/**
 * individual log levels for components
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 */
class ilLogComponentLevels
{
    protected static ?ilLogComponentLevels $instance = null;
    /**
     * @var ilLogComponentLevel[]
     */
    protected array $components = array();

    protected ilDBInterface $db;
    
    /**
     * constructor
     */
    protected function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->read();
    }
    
    public static function getInstance() : ilLogComponentLevels
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * @param string $a_component_id
     */
    public static function updateFromXML($a_component_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
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
     * Get component levels
     * @return ilLogComponentLevel[]
     */
    public function getLogComponents() : array
    {
        return $this->components;
    }
    
    public function read() : void
    {
        $query = 'SELECT * FROM log_components ';
        $res = $this->db->query($query);
        
        $this->components = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->components[] = new ilLogComponentLevel((string) $row->component_id, (int) $row->log_level);
        }
    }
}
