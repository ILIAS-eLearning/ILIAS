<?php declare(strict_types=1);

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
 * @author Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls ilObjCategoryReferenceGUI: ilPermissionGUI, ilInfoScreenGUI, ilPropertyFormGUI
 */
class ilObjCategoryReferenceGUI extends ilContainerReferenceGUI
{
    protected ilHelpGUI $help;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->target_type = 'cat';
        $this->reference_type = 'catr';

        $this->access = $DIC->access();
        $this->help = $DIC["ilHelp"];
        parent::__construct($a_data, $a_id, true, false);
    }

    public static function _goto(string $a_target) : void
    {
        $target_ref_id = ilContainerReference::_lookupTargetRefId(ilObject::_lookupObjId((int) $a_target));
        ilObjCategoryGUI::_goto((string) $target_ref_id);
    }
}
