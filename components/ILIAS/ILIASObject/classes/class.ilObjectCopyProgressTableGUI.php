<?php

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

declare(strict_types=1);

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Factory as UIFactory;

/**
 * Table gui for copy progress
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilObjectCopyProgressTableGUI extends ilTable2GUI
{
    protected array $objects = [];

    public function __construct(
        protected DataFactory $data_factory,
        protected UIRenderer $ui_renderer,
        protected UIFactory $ui_factory,
        ilObjectCopyGUI $parent_obj,
        string $parent_cmd,
        int $id,
    ) {
        $this->setId('obj_cp_prog_tbl_' . $id);
        parent::__construct($parent_obj, $parent_cmd);
    }

    public function setObjectInfo(array $ref_ids): void
    {
        $this->objects = $ref_ids;
    }

    public function getObjects(): array
    {
        return $this->objects;
    }

    public function setRedirectionUrl(?string $url): void
    {
        $this->main_tpl->addOnLoadCode('il.CopyRedirection.setRedirectUrl("' . $url . '")');
    }

    public function init(): void
    {
        $this->main_tpl->addJavaScript('assets/js/ilCopyRedirection.js');
        $this->main_tpl->addOnLoadCode('il.CopyRedirection.checkDone()');
        $this->setExternalSorting(true);
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));

        $this->setRowTemplate('tpl.object_copy_progress_table_row.html', 'components/ILIAS/ILIASObject');

        $this->addColumn($this->lng->txt('obj_target_location'));
        $this->addColumn($this->lng->txt('obj_copy_progress'));
    }

    protected function fillRow(array $set): void
    {
        $this->tpl->setVariable('VAL_ID', $set['ref_id']);
        $this->tpl->setVariable('OBJ_TITLE', $set['title']);

        if (strlen($set['description'])) {
            $this->tpl->setVariable('VAL_DESC', $set['description']);
        }

        $this->tpl->setVariable('TYPE_IMG', ilObject::_getIcon($set['obj_id'], "small", $set['type']));
        $this->tpl->setVariable('TYPE_STR', $this->lng->txt('obj_' . $set['type']));

        $this->ctrl->setParameter($this->getParentObject(), '_copy_id', $set['copy_id']);
        $this->ctrl->setParameter($this->getParentObject(), '_max_steps', $set['max_steps']);

        $progress_endpoint = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' .
            $this->ctrl->getLinkTarget(
                $this->getParentObject(),
                'updateProgress',
                '',
                true
            )
        );

        $progress_bar = $this->ui_factory->progress()->bar(
            sprintf($this->lng->txt('obj_copy_progress_to'), $set['title']),
            $progress_endpoint,
        );

        $this->tpl->setVariable('PROGRESS_BAR', $this->ui_renderer->render($progress_bar));

        // start pulling progress from $progress_endpoint once the page has loaded
        $this->main_tpl->addOnLoadCode("
            il.UI.Progress.Bar.indeterminate(
                '{$progress_bar->getUpdateSignal()}',
                '{$this->lng->txt('obj_copy_progress_estimate')}'
            );
        ");
    }

    public function parse(): void
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
