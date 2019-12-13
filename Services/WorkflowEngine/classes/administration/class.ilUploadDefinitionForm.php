<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * Upload definition
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilUploadDefinitionForm
{
    /** @var ilPropertyFormGUI $form */
    protected $form;

    /** @var \ilLanguage $lng */
    protected $lng;

    public function __construct()
    {
        global $DIC;
        /** @var ilLanguage $lng */
        $this->lng = $DIC['lng'];
    }

    public function getForm($action)
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($action);
        $this->form->setTitle($this->lng->txt('upload_process'));

        $upload_input = new ilFileInputGUI($this->lng->txt('process_definition_file'), 'process_file');
        $upload_input->setSuffixes(array('xml','bpmn','bpmn2'));
        $upload_input->setInfo($this->lng->txt('process_definition_file_info'));
        $upload_input->setRequired(true);
        $this->form->addItem($upload_input);

        $this->form->addCommandButton('upload', $this->lng->txt('upload_process'));
        $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $this->form;
    }
}
