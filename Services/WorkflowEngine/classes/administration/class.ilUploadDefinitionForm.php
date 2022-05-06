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

/**
 * Upload definition
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilUploadDefinitionForm
{
    protected ilPropertyFormGUI $form;
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    public function getForm(string $action) : ilPropertyFormGUI
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($action);
        $this->form->setTitle($this->lng->txt('upload_process'));

        $upload_input = new ilFileInputGUI($this->lng->txt('process_definition_file'), 'process_file');
        $upload_input->setSuffixes(['xml', 'bpmn', 'bpmn2']);
        $upload_input->setInfo($this->lng->txt('process_definition_file_info'));
        $upload_input->setRequired(true);
        $this->form->addItem($upload_input);

        $this->form->addCommandButton('upload', $this->lng->txt('upload_process'));
        $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $this->form;
    }
}
