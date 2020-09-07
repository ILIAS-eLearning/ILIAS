<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilWorkflowEngineSettingsGUI
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowEngineSettingsGUI
{
    /** @var  ilObjWorkflowEngineGUI */
    protected $parent_gui;

    /**
     * ilWorkflowEngineSettingsGUI constructor.
     *
     * @param \ilObjWorkflowEngineGUI $parent_gui
     */
    public function __construct(ilObjWorkflowEngineGUI $parent_gui)
    {
        $this->parent_gui = $parent_gui;
    }

    /**
     * @param string $command
     *
     * @return string
     */
    public function handle($command)
    {
        global $DIC;
        /** @var ilSetting $ilSetting */
        $ilSetting = $DIC['ilSetting'];

        require_once './Services/WorkflowEngine/classes/administration/class.ilWorkflowEngineSettingsForm.php';
        $form = new ilWorkflowEngineSettingsForm();
        $form_instance = $form->getForm($this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'settings.save'));
        $cb_input = $form_instance->getItemByPostVar('activate');

        if ($command == 'view') {
            $cb_input->setChecked((bool) $ilSetting->get('wfe_activation', 0));
            return $form_instance->getHTML();
        }

        if ($command == 'save') {
            if (isset($_POST['cmd']['cancel'])) {
                ilUtil::redirect(
                    html_entity_decode($this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view'))
                );
            }
            if ($form_instance->checkInput()) {
                $form_instance->setValuesByPost();
                $ilSetting->set('wfe_activation', (int) $cb_input->getChecked());

                ilUtil::sendSuccess($this->parent_gui->lng->txt('settings_saved'), true);
                ilUtil::redirect(
                    html_entity_decode($this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'settings.view'))
                );
            }
        }
    }
}
