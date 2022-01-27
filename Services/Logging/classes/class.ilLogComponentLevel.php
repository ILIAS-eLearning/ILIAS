<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * individual log levels for components
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 */
class ilLogComponentLevel
{
    private string $compontent_id = '';
    private ?int $component_level = null;

    protected ilDBInterface $db;
    
    public function __construct($a_component_id, $a_level = null)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->compontent_id = $a_component_id;
        if ($a_level === null) {
            $this->read();
        } else {
            $this->setLevel($a_level);
        }
    }
    
    public function getComponentId()
    {
        return $this->compontent_id;
    }
    
    public function setLevel($a_level)
    {
        $this->component_level = $a_level;
    }
    
    public function getLevel()
    {
        return $this->component_level;
    }
    
    public function update()
    {
        
        ilLoggerFactory::getLogger('log')->debug('update called');
        
        $this->db->replace(
            'log_components',
            array('component_id' => array('text',$this->getComponentId())),
            array('log_level' => array('integer',$this->getLevel()))
        );
    }
    
    /**
     * Read entry
     * @global type $ilDB
     */
    public function read()
    {
        
        $query = 'SELECT * FROM log_components ' .
                'WHERE component_id = ' . $this->db->quote($this->getComponentId(), 'text');
        
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->component_level = $row->log_level;
        }
    }
}
