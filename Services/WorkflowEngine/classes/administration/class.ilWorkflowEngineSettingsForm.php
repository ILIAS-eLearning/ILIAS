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

    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;

    /** @var \ilLanguage $lng */
    protected $lng;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->lng = $this->dic->language();
    }

    public function getForm($action)
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($action);
        $this->form->setTitle($this->lng->txt('settings'));

        $activation_checkbox = new ilCheckboxInputGUI($this->lng->txt('activate'), 'activate');
        $activation_checkbox->setDisabled(true);
        $this->form->addItem($activation_checkbox);

        if ($this->dic->rbac()->system()->checkAccess("visible,read", (int) $_GET['ref_id'])) {
            //$this->form->addCommandButton('save', $this->lng->txt('save'));
            $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }

        return $this->form;
    }
}
