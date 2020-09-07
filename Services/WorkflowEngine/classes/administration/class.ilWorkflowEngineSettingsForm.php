<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * Settings Form
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowEngineSettingsForm
{
    /** @var ilPropertyFormGUI $form */
    protected $form;

    /** @var \ilLanguage $lng */
    protected $lng;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC['lng'];
    }

    public function getForm($action)
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($action);
        $this->form->setTitle($this->lng->txt('settings'));

        $activation_checkbox = new ilCheckboxInputGUI($this->lng->txt('activate'), 'activate');
        $this->form->addItem($activation_checkbox);

        $this->form->addCommandButton('save', $this->lng->txt('save'));
        $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $this->form;
    }
}
