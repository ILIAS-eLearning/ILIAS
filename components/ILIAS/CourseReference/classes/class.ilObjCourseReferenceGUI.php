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
 * @author       Stefan Meyer <meyer@leifos.com>
 * @version      $Id$
 * @ilCtrl_Calls ilObjCourseReferenceGUI: ilPermissionGUI, ilInfoScreenGUI, ilPropertyFormGUI
 * @ilCtrl_Calls ilObjCourseReferenceGUI: ilCommonActionDispatcherGUI, ilLearningProgressGUI
 * @ingroup      ModulesCourseReference
 */
class ilObjCourseReferenceGUI extends ilContainerReferenceGUI
{
    private ?ilLogger $logger = null;

    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->target_type = 'crs';
        $this->reference_type = 'crsr';
        $this->logger = $DIC->logger()->crsr();

        parent::__construct($a_data, $a_id, true, false);

        $this->lng->loadLanguageModule('crs');
    }

    /**
     * Execute command
     * @access public
     */
    public function executeCommand(): void
    {
        global $DIC;

        $user = $DIC->user();

        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case 'illearningprogressgui':
                $this->prepareOutput();
                $this->tabs_gui->activateTab('learning_progress');
                $lp_gui = new \ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $user->getId()
                );
                $this->ctrl->forwardCommand($lp_gui);
                return;
        }
        parent::executeCommand();
    }

    /**
     * Add tabs
     * @access public
     */
    protected function getTabs(): void
    {
        global $DIC;

        $help = $DIC->help();
        $help->setScreenIdComponent($this->getReferenceType());

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'settings',
                $this->lng->txt('settings'),
                $this->ctrl->getLinkTarget($this, 'edit')
            );
        }

        if (ilLearningProgressAccess::checkAccess($this->object->getRefId(), false)) {
            $this->tabs_gui->addTab(
                'learning_progress',
                $this->lng->txt('learning_progress'),
                $this->ctrl->getLinkTargetByClass(
                    [
                        ilObjCourseReferenceGUI::class,
                        ilLearningProgressGUI::class
                    ],
                    ''
                )
            );
        }
        if ($this->access->checkAccess('edit_permission', '', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'perm_settings',
                $this->lng->txt('perm_settings'),
                $this->ctrl->getLinkTargetByClass(
                    [
                        ilObjCourseReferenceGUI::class,
                        ilPermissionGUI::class
                    ],
                    'perm'
                )
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function initForm($a_mode = self::MODE_EDIT): ilPropertyFormGUI
    {
        $form = parent::initForm($a_mode);

        if ($a_mode == self::MODE_CREATE) {
            return $form;
        }

        $path_info = \ilCourseReferencePathInfo::getInstanceByRefId(
            $this->object->getRefId(),
            $this->object->getTargetRefId()
        );

        // nothing todo if no parent course is in path
        if (!$path_info->hasParentCourse()) {
            return $form;
        }

        $access = $path_info->checkManagmentAccess();

        $auto_update = new \ilCheckboxInputGUI($this->lng->txt('crs_ref_member_update'), 'member_update');
        $auto_update->setChecked($this->object->isMemberUpdateEnabled());
        $auto_update->setInfo($this->lng->txt('crs_ref_member_update_info'));
        $auto_update->setDisabled(!$access);
        $form->addItem($auto_update);

        return $form;
    }

    /**
     * @param \ilPropertyFormGUI $form
     * @return bool
     */
    protected function loadPropertiesFromSettingsForm(ilPropertyFormGUI $form): bool
    {
        $ok = true;
        $ok = parent::loadPropertiesFromSettingsForm($form);

        $path_info = ilCourseReferencePathInfo::getInstanceByRefId(
            $this->object->getRefId(),
            $this->object->getTargetRefId()
        );

        $auto_update = $form->getInput('member_update');
        if ($auto_update && !$path_info->hasParentCourse()) {
            $ok = false;
            $form->getItemByPostVar('member_update')->setAlert($this->lng->txt('crs_ref_missing_parent_crs'));
        }
        if ($auto_update && !$path_info->checkManagmentAccess()) {
            $ok = false;
            $form->getItemByPostVar('member_update')->setAlert($this->lng->txt('crs_ref_missing_access'));
        }

        // check manage members
        $this->object->enableMemberUpdate((bool) $form->getInput('member_update'));

        return $ok;
    }

    /**
     * Support for goto php
     * @return void
     * @static
     */
    public static function _goto($a_target)
    {
        global $DIC;

        $access = $DIC->access();
        $ctrl = $DIC->ctrl();
        $logger = $DIC->logger()->crsr();

        $target_ref_id = $a_target;
        $write_access = $access->checkAccess('write', '', (int) $target_ref_id);

        if ($write_access) {
            $target_class = \ilObjCourseReferenceGUI::class;
        } else {
            $target_ref_id = \ilContainerReference::_lookupTargetRefId(\ilObject::_lookupObjId($target_ref_id));
            $target_class = \ilObjCourseGUI::class;
        }

        $ctrl->setTargetScript('ilias.php');
        $ctrl->setParameterByClass($target_class, 'ref_id', $target_ref_id);
        $ctrl->redirectByClass(
            [
                \ilRepositoryGUI::class,
                $target_class
            ],
            'edit'
        );
    }
}
