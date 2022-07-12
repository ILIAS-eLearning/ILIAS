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
 * Settings Form
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowEngineSettingsForm
{
    private \ILIAS\WorkflowEngine\Service $service;
    protected ilPropertyFormGUI $form;
    protected \ILIAS\DI\Container $dic;
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->lng = $this->dic->language();
        $this->service = $DIC->workflowEngine();
    }

    public function getForm(string $action) : ilPropertyFormGUI
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
