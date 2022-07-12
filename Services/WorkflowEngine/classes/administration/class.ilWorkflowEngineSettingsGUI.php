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
 * Class ilWorkflowEngineSettingsGUI
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowEngineSettingsGUI
{
    private ilObjWorkflowEngineGUI $parent_gui;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct(ilObjWorkflowEngineGUI $parent_gui)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->parent_gui = $parent_gui;
    }

    /**
     * @todo return type never in PHP 8.1+
     * @param string $command
     * @return string
     */
    public function handle(string $command) : string
    {
        global $DIC;
        /** @var ilSetting $ilSetting */
        $ilSetting = $DIC['ilSetting'];

        $form = new ilWorkflowEngineSettingsForm();
        $form_instance = $form->getForm($this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'settings.save'));
        $cb_input = $form_instance->getItemByPostVar('activate');

        if ($command === 'view') {
            $cb_input->setChecked((bool) $ilSetting->get('wfe_activation', '0'));
            return $form_instance->getHTML();
        }

        if ($command === 'save') {
            if (isset($_POST['cmd']['cancel'])) {
                ilUtil::redirect(
                    html_entity_decode($this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view'))
                );
            }
            if ($form_instance->checkInput()) {
                $form_instance->setValuesByPost();
                $ilSetting->set('wfe_activation', (string) ((int) $cb_input->getChecked()));

                $this->main_tpl->setOnScreenMessage('success', $this->parent_gui->lng->txt('settings_saved'), true);
                ilUtil::redirect(
                    html_entity_decode($this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'settings.view'))
                );
            }
        }
        return '';
    }
}
