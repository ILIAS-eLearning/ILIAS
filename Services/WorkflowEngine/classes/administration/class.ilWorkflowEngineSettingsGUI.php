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

        $cb_input->setChecked(false);
        return $form_instance->getHTML();
    }
}
