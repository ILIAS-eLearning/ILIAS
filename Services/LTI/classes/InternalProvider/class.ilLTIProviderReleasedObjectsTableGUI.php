<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLTIProviderReleasedObjectsTableGUI extends ilObjectTableGUI
{
    //only because type in ilObjectTableGUI - could be erased
    public function __construct(?object $a_parent_obj, string $a_parent_cmd, $a_id)
    {
        $this->setId('obj_table_' . $a_id);
        parent::__construct($a_parent_obj, $a_parent_cmd, "");
    }

    /**
     * init table
     */
    public function init() : void
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
     * @param array $a_set
     */
    public function fillRow(array $a_set) : void
    {
        parent::fillRow($a_set);
        
        $this->tpl->setVariable('CONSUMER_TITLE', $a_set['consumer']);
    }
    
    public function parse() : void
    {
        $rows = ilObjLTIAdministration::readReleaseObjects();
        
        $counter = 0;
        $set = array();
        foreach ($rows as $row) {
            $ref_id = (int) $row['ref_id'];
            
            
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
