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
 * @author Fabian Wolf <wolf@leifos.com>
 * @extends ilContainerReferenceGUI
 * @ilCtrl_Calls ilObjGroupReferenceGUI: ilPermissionGUI, ilInfoScreenGUI, ilPropertyFormGUI
 * @ingroup ModulesGroupReference
 */
class ilObjGroupReferenceGUI extends ilContainerReferenceGUI
{
    /**
     * ilObjGroupReferenceGUI constructor.
     * @param $a_data
     * @param int $a_id
     * @param bool $a_call_by_reference
     * @param bool $a_prepare_output
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = false)
    {
        $this->target_type = 'grp';
        $this->reference_type = 'grpr';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
    }

    /**
     * Execute command
     *
     * @access public
     */
    public function executeCommand(): void
    {
        parent::executeCommand();
    }


    /**
     *  Support for goto php
     *
     * @param int $a_target
     */
    public static function _goto($a_target)
    {
        include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');
        $target_ref_id = ilContainerReference::_lookupTargetRefId(ilObject::_lookupObjId($a_target));

        include_once('./Modules/Group/classes/class.ilObjGroupGUI.php');
        ilObjGroupGUI::_goto($target_ref_id);
    }
}
