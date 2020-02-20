<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLTIProviderReleasedObjectsTableGUI extends ilObjectTableGUI
{
    /**
     * init table
     */
    public function init()
    {
        if ($this->enabledRowSelectionInput()) {
            $this->addColumn('', 'id', '5px');
        }
        
        $this->addColumn($this->lng->txt('type'), 'type', '30px');
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('lti_consumer'), 'consumer', '30%');
        
        $this->setOrderColumn('title');
        $this->setRowTemplate('tpl.lti_object_table_row.html', 'Services/LTI');
    }
    
    /**
     * Fill row
     * @param type $set
     */
    public function fillRow($set)
    {
        parent::fillRow($set);
        
        $this->tpl->setVariable('CONSUMER_TITLE', $set['consumer']);
    }
    
    public function parse()
    {
        $rows = ilObjLTIAdministration::readReleaseObjects();
        
        $counter = 0;
        $set = array();
        foreach ($rows as $row) {
            $ref_id = $row['ref_id'];
            
            
            $type = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
            if ($type == 'rolf') {
                continue;
            }
            
            $set[$counter]['ref_id'] = $ref_id;
            $set[$counter]['obj_id'] = ilObject::_lookupObjId($ref_id);
            $set[$counter]['type'] = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
            $set[$counter]['title'] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
            $set[$counter]['consumer'] = $row['title'];
            $counter++;
        }
        $this->setData($set);
    }
}
