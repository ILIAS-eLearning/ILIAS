<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */
/**
 * Table gui for copy progress
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilObjectCopyProgressTableGUI extends ilTable2GUI
{
    protected $objects = array();

    /**
     * Constructor
     * @param type $a_parent_obj
     * @param type $a_parent_cmd
     * @param type $a_id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_id)
    {
        $this->setId('obj_copy_progress_table_' . $a_id);
        parent::__construct($a_parent_obj, $a_parent_cmd, '');
    }

    public function setObjectInfo($a_ref_ids)
    {
        $this->objects = $a_ref_ids;
    }

    public function getObjects()
    {
        return $this->objects;
    }
    
    public function setRedirectionUrl($a_url)
    {
        global $DIC;
        $DIC->ui()->mainTemplate()->addOnLoadCode('il.CopyRedirection.setRedirectUrl("' . $a_url . '")');
    }

    /**
     * Init Table
     */
    public function init()
    {
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();
        $ctrl = $DIC->ctrl();
        $lng = $DIC->language();

        $tpl->addJavaScript('./Services/CopyWizard/js/ilCopyRedirection.js');
        $tpl->addOnLoadCode('il.CopyRedirection.checkDone()');
        $this->setExternalSorting(true);
        $this->setFormAction($ctrl->getFormAction($this->getParentObject()));

        $this->setRowTemplate('tpl.object_copy_progress_table_row.html', 'Services/Object');

        $this->addColumn($lng->txt('obj_target_location'), '');
        $this->addColumn($lng->txt('obj_copy_progress'), '');
    }

    /**
     * Fill row
     * @param array $a_set
     */
    protected function fillRow(array $a_set) : void
    {
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();
        $ctrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->tpl->setVariable('VAL_ID', $a_set['ref_id']);
        $this->tpl->setVariable('OBJ_TITLE', $a_set['title']);

        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('VAL_DESC', $a_set['description']);
        }

        $this->tpl->setVariable('TYPE_IMG', ilObject::_getIcon($a_set['obj_id'], "small", $a_set['type']));
        $this->tpl->setVariable('TYPE_STR', $this->lng->txt('obj_' . $a_set['type']));

        $progress = ilProgressBar::getInstance();
        $progress->setType(ilProgressBar::TYPE_SUCCESS);
        $progress->setMin(0);
        $progress->setMax($a_set['max_steps']);
        $progress->setCurrent(0);
        $progress->setAnimated(true);
        $progress->setId($a_set['copy_id']);

        $ctrl->setParameter($this->getParentObject(), 'copy_id', $a_set['copy_id']);
        $progress->setAsyncStatusUrl(
            $ctrl->getLinkTarget(
                $this->getParentObject(),
                'updateProgress',
                '',
                true
            )
        );

        $progress->setAsynStatusTimeout(1);
        $this->tpl->setVariable('PROGRESS_BAR', $progress->render());
    }

    /**
     * Parse objects
     */
    public function parse()
    {
        $counter = 0;
        $set = array();
        foreach ($this->getObjects() as $ref_id => $copy_id) {
            $counter++;
            $set[$counter]['ref_id'] = $ref_id;
            $set[$counter]['copy_id'] = $copy_id;
            $set[$counter]['obj_id'] = ilObject::_lookupObjId($ref_id);
            $set[$counter]['type'] = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
            $set[$counter]['title'] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
            $set[$counter]['description'] = ilObject::_lookupDescription(ilObject::_lookupObjId($ref_id));
            
            $copy_info = ilCopyWizardOptions::_getInstance($copy_id);
            $copy_info->read();
            $set[$counter]['max_steps'] = $copy_info->getRequiredSteps();
        }
        $this->setData($set);
    }
}
