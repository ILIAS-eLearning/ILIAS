<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    protected ilPropertyFormGUI $form;

    protected \ILIAS\DI\Container $dic;

    /** @var \ilLanguage $lng */
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->lng = $this->dic->language();
        $this->service = $DIC->workflowEngine();// TODO PHP8-REVIEW Property dynamically declared
    }

    public function getForm($action) : ilPropertyFormGUI
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($action);
        $this->form->setTitle($this->lng->txt('settings'));

        $activation_checkbox = new ilCheckboxInputGUI($this->lng->txt('activate'), 'activate');
        $this->form->addItem($activation_checkbox);

        if ($this->dic->rbac()->system()->checkAccess("visible,read", $this->service->internal()->request()->getRefId())) {
            $this->form->addCommandButton('save', $this->lng->txt('save'));
            $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }

        return $this->form;
    }
}
