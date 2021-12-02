<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once('./Services/ContainerReference/classes/class.ilContainerReferenceGUI.php');
/**
 *
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjCourseReferenceGUI: ilPermissionGUI, ilInfoScreenGUI, ilPropertyFormGUI
 * @ilCtrl_Calls ilObjCourseReferenceGUI: ilCommonActionDispatcherGUI, ilLearningProgressGUI
 *
 * @ingroup ModulesCourseReference
 */
class ilObjCourseReferenceGUI extends ilContainerReferenceGUI implements ilCtrlBaseClassInterface
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
     *
     * @access public
     *
     */
    public function executeCommand()
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
     *
     * @access public
     */
    protected function getTabs()
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
    public function initForm($a_mode = self::MODE_EDIT) : ilPropertyFormGUI
    {
        $form = parent::initForm($a_mode);

        if ($a_mode == self::MODE_CREATE) {
            return $form;
        }

        $path_info = \ilCourseReferencePathInfo::getInstanceByRefId($this->object->getRefId(), $this->object->getTargetRefId());


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
    protected function loadPropertiesFromSettingsForm(ilPropertyFormGUI $form) : bool
    {
        $ok = true;
        $ok = parent::loadPropertiesFromSettingsForm($form);

        $path_info = ilCourseReferencePathInfo::getInstanceByRefId($this->object->getRefId(), $this->object->getTargetRefId());

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
     *
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

        $ctrl->initBaseClass(ilRepositoryGUI::class);
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
