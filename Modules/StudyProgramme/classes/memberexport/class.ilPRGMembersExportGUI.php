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

/**
 * export assignments of PRG
 */
class ilPRGMembersExportGUI extends ilMemberExportGUI
{
    public const USERFORMSETTINGS_ID = 'prg_export_settings';
    public const EXPORT_FILENAME = '%s_PRGAssignment_export_%s_%s'; //keep underscores/params!

    protected ilUserFormSettings $exportSettings;

    public function __construct(
        int $ref_id,
        protected ilStudyProgrammeUserTable $prg_user_table,
        protected DataFactory $data_factory
    ) {
        $this->ref_id = $ref_id;
        $this->obj_id = ilObject::_lookupObjId($ref_id);
        parent::__construct($ref_id);
    }

    /**
     * @inheritdoc
     */
    protected function initSettingsForm(bool $a_is_excel = false): ilPropertyFormGUI
    {
        $this->exportSettings = new ilUserFormSettings(self::USERFORMSETTINGS_ID);

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('ps_export_settings'));

        if ((bool) $a_is_excel) {
            $form->addCommandButton('exportExcel', $this->lng->txt('ps_export_excel'));
        } else {
            $form->addCommandButton('export', $this->lng->txt('ps_perform_export'));
        }
        $form->addCommandButton('show', $this->lng->txt('cancel'));

        $current_udata = array();
        $udata = new ilCheckboxGroupInputGUI($this->lng->txt('ps_export_user_data'), 'export_members');
        $form->addItem($udata);

        $columns = array_merge($this->prg_user_table->getColumns($this->obj_id, true));

        foreach ($columns as $field_info) {
            $udata->addOption(new ilCheckboxOption($field_info[1], $field_info[0]));
            if ($this->exportSettings->enabled($field_info[0])) {
                $current_udata[] = $field_info[0];
            }
        }

        $udata->setValue($current_udata);
        return $form;
    }

    /**
     * @inheritdoc
     */
    protected function handleIncoming(): void
    {
        $settings = [];
        $incoming = [];
        if ($this->http->wrapper()->post()->has('export_members')) {
            $incoming = $this->http->wrapper()->post()->retrieve(
                'export_members',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            );
        }
        if (count($incoming)) {
            foreach ($incoming as $id) {
                $settings[$id] = true;
            }
        }

        $this->exportSettings = new ilUserFormSettings(self::USERFORMSETTINGS_ID);
        $this->exportSettings->set($settings);
        $this->exportSettings->store();
    }

    /**
     * @inheritdoc
     */
    protected function initFileSystemStorage(): void
    {
        $this->fss_export = new ilFSStoragePRG($this->obj_id);
    }

    /**
     * @inheritdoc
     */
    public function export(): void
    {
        $this->exportAssignments(ilPRGMemberExport::EXPORT_CSV);
    }

    /**
     * @inheritdoc
     */
    public function exportExcel(): void
    {
        $this->exportAssignments(ilPRGMemberExport::EXPORT_EXCEL);
    }

    private function exportAssignments($type)
    {
        $this->handleIncoming();
        $prg_user_table = ilStudyProgrammeDIC::specificDicFor(
            new ilObjStudyProgramme($this->obj_id, false)
        )['ilStudyProgrammeUserTable'];
        $prg_user_table->disablePermissionCheck(true);

        $export = new ilPRGMemberExport(
            $this->ref_id,
            $this->obj_id,
            $prg_user_table,
            $this->exportSettings,
            $this->lng,
            $this->data_factory
        );

        $type_str = ($type === ilPRGMemberExport::EXPORT_CSV) ? 'csv' : 'xls';

        $filename = sprintf(
            self::EXPORT_FILENAME,
            time(),
            $type_str,
            $this->obj_id
        );

        if ($type === ilPRGMemberExport::EXPORT_CSV) {
            $filename .= '.csv';
        }
        $filepath = $this->fss_export->getMemberExportDirectory() . DIRECTORY_SEPARATOR . $filename;


        $this->fss_export->initMemberExportDirectory();
        $export->create($type, $filepath);

        if ($type === ilPRGMemberExport::EXPORT_CSV) {
            $this->fss_export->addMemberExportFile($export->getCSVString(), $filename);
        }
        $this->ctrl->redirect($this, 'show');
    }

    public function deleteExportFile(): void
    {
        $file_ids = $this->initFileIdsFromPost();
        if (!count($file_ids)) {
            $this->ctrl->redirect($this, 'show');
        }

        foreach ($this->fss_export->getMemberExportFiles() as $file) {
            if (!in_array(md5($file['name']), $_POST['id'])) {
                continue;
            }
            $this->fss_export->deleteMemberExportFile($file['name']);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('ps_files_deleted'), true);
        $this->ctrl->redirect($this, 'show');
    }

    public function downloadExportFile(): void
    {
        $fl = '';
        if ($this->http->wrapper()->query()->has('fl')) {
            $fl = $this->http->wrapper()->query()->retrieve(
                'fl',
                $this->refinery->kindlyTo()->string()
            );
        }

        $hash = trim($fl);
        if (!$hash) {
            $this->ctrl->redirect($this, 'show');
        }

        foreach ($this->fss_export->getMemberExportFiles() as $file) {
            if (md5($file['name']) == $hash) {
                $contents = $this->fss_export->getMemberExportFile($file['name']);
                $fts = $file['timest'];
                $dat = date('Y_m_d_H-i', (int)$fts);
                $down_name = str_replace($fts, $dat, $file['name']);

                $mime = 'text/csv';
                if ($file['type'] === 'xlsx') {
                    $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                }
                ilUtil::deliverData($contents, $down_name, $mime);
            }
        }
    }
}
