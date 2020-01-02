<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/SystemCheck/classes/class.ilSCGroup.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCGroups
{

    /**
     * @var ilSCGroup
     */
    private static $instance = null;
    
    private $groups = array();
    
    /**
     * Singleton constructor
     */
    private function __construct()
    {
        $this->read();
    }
    
    /**
     * Get singleton instance
     * @return ilSCGroups
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            return self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Update from component definition reader
     * @param type $a_component_id
     */
    public function updateFromComponentDefinition($a_component_id)
    {
        foreach ($this->getGroups() as $group) {
            if ($group->getComponentId() == $a_component_id) {
                return $group->getId();
            }
        }

        $component_group = new ilSCGroup();
        $component_group->setComponentId($a_component_id);
        $component_group->create();
        
        return $component_group->getId();
    }
    
    /**
     *
     */
    public function lookupGroupByComponentId($a_component_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT id FROM sysc_groups ' .
                'WHERE component = ' . $ilDB->quote($a_component_id, 'text');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->id;
        }
        return 0;
    }
    
    
    /**
     * Get groups
     * @return ilSCGroup[]
     */
    public function getGroups()
    {
        return (array) $this->groups;
    }
    
    /**
     * read groups
     */
    protected function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT id FROM sysc_groups ' .
                'ORDER BY id ';
        $res = $ilDB->query($query);
        
        $this->groups = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->groups[] = new ilSCGroup($row->id);
        }
    }
}
