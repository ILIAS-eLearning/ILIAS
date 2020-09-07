<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Object/classes/class.ilObjectTableGUI.php';

/**
 * GUI class for the workflow of copying objects
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesObject
 */
class ilObjectCopyCourseGroupSelectionTableGUI extends ilObjectTableGUI
{
    /**
     * Set objects
     * @param type $a_obj_ids
     */
    public function setObjects($a_obj_ids)
    {
        $ref_ids = array();
        foreach ($a_obj_ids as $obj_id) {
            $all_ref_ids = ilObject::_getAllReferences($obj_id);
            $ref_ids[] = end($all_ref_ids);
        }
        return parent::setObjects($ref_ids);
    }
    
    /**
     * Init table
     */
    public function init()
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();

        $this->enableRowSelectionInput(true);
        
        parent::init();
        
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
        
        $this->enableObjectPath(true);
        $this->addCommandButton('saveSourceMembership', $this->lng->txt('btn_next'));
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }
    
    /**
     * Fill row selection input
     * @param type $set
     */
    public function fillRowSelectionInput($set)
    {
        $this->tpl->setCurrentBlock('row_selection_input');
        $this->tpl->setVariable('OBJ_INPUT_TYPE', 'radio');
        $this->tpl->setVariable('OBJ_INPUT_NAME', 'source');
        $this->tpl->setVariable('OBJ_INPUT_VALUE', $set['ref_id']);
    }
    
    /**
     * Customize path
     * @param \ilPathGUI $path
     * @return \ilPathGUI
     */
    public function customizePath(\ilPathGUI $path)
    {
        $path->setUseImages(true);
        $path->enableTextOnly(false);
        return $path;
    }
}
