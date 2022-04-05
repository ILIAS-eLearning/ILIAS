<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * GUI class for the workflow of copying objects
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilObjectCopyCourseGroupSelectionTableGUI extends ilObjectTableGUI
{
    public function setObjects(array $obj_ids) : void
    {
        $ref_ids = array();
        foreach ($obj_ids as $obj_id) {
            $all_ref_ids = ilObject::_getAllReferences($obj_id);
            $ref_ids[] = end($all_ref_ids);
        }
        parent::setObjects($ref_ids);
    }
    
    public function init() : void
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
    
    public function fillRowSelectionInput(array $set) : void
    {
        $this->tpl->setCurrentBlock('row_selection_input');
        $this->tpl->setVariable('OBJ_INPUT_TYPE', 'radio');
        $this->tpl->setVariable('OBJ_INPUT_NAME', 'source');
        $this->tpl->setVariable('OBJ_INPUT_VALUE', $set['ref_id']);
    }
    
    public function customizePath(ilPathGUI $path) : ilPathGUI
    {
        $path->setUseImages(true);
        $path->enableTextOnly(false);
        return $path;
    }
}
