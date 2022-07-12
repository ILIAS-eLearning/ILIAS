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
 * Table gui for copy progress
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilObjectCopyProgressTableGUI extends ilTable2GUI
{
    protected array $objects = [];

    public function __construct(ilObjectCopyGUI $parent_obj, string $parent_cmd, int $id)
    {
        $this->setId('obj_copy_progress_table_' . $id);
        parent::__construct($parent_obj, $parent_cmd);
    }

    public function setObjectInfo(array $ref_ids) : void
    {
        $this->objects = $ref_ids;
    }

    public function getObjects() : array
    {
        return $this->objects;
    }
    
    public function setRedirectionUrl(?string $url) : void
    {
        $this->main_tpl->addOnLoadCode('il.CopyRedirection.setRedirectUrl("' . $url . '")');
    }

    public function init() : void
    {
        $this->main_tpl->addJavaScript('./Services/CopyWizard/js/ilCopyRedirection.js');
        $this->main_tpl->addOnLoadCode('il.CopyRedirection.checkDone()');
        $this->setExternalSorting(true);
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));

        $this->setRowTemplate('tpl.object_copy_progress_table_row.html', 'Services/Object');

        $this->addColumn($this->lng->txt('obj_target_location'));
        $this->addColumn($this->lng->txt('obj_copy_progress'));
    }

    protected function fillRow(array $set) : void
    {
        $this->tpl->setVariable('VAL_ID', $set['ref_id']);
        $this->tpl->setVariable('OBJ_TITLE', $set['title']);

        if (strlen($set['description'])) {
            $this->tpl->setVariable('VAL_DESC', $set['description']);
        }

        $this->tpl->setVariable('TYPE_IMG', ilObject::_getIcon($set['obj_id'], "small", $set['type']));
        $this->tpl->setVariable('TYPE_STR', $this->lng->txt('obj_' . $set['type']));

        $progress = ilProgressBar::getInstance();
        $progress->setType(ilProgressBar::TYPE_SUCCESS);
        $progress->setMin(0);
        $progress->setMax($set['max_steps']);
        $progress->setCurrent(0);
        $progress->setAnimated(true);
        $progress->setId((string) $set['copy_id']);

        $this->ctrl->setParameter($this->getParentObject(), 'copy_id', $set['copy_id']);
        $progress->setAsyncStatusUrl(
            $this->ctrl->getLinkTarget(
                $this->getParentObject(),
                'updateProgress',
                '',
                true
            )
        );

        $progress->setAsynStatusTimeout(1);
        $this->tpl->setVariable('PROGRESS_BAR', $progress->render());
    }

    public function parse() : void
    {
        $counter = 0;
        $set = [];
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
