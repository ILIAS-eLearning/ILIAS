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
 * @author Stefan Meyer <meyer.leifos.com>
 */
class ilMemberViewGUI
{
    public static function showMemberViewSwitch(int $a_ref_id) : bool
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        
        $settings = ilMemberViewSettings::getInstance();
        if (!$settings->isEnabled()) {
            return false;
        }
        $tree = $DIC->repositoryTree();
        $lng = $DIC->language();
        $ilTabs = $DIC->tabs();
        
        // No course or group in path => aborting
        if (!$tree->checkForParentType($a_ref_id, 'crs') &&
            !$tree->checkForParentType($a_ref_id, 'grp')) {
            return false;
        }
        
        // TODO: check edit_permission
        
        $type = ilObject::_lookupType(ilObject::_lookupObjId($a_ref_id));
        if (($type === 'crs' || $type === 'grp') && $ilAccess->checkAccess('write', '', $a_ref_id)) {
            $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_ref_id);
            $ilCtrl->setParameterByClass("ilrepositorygui", "mv", "1");
            $ilCtrl->setParameterByClass("ilrepositorygui", "set_mode", "flat");
            $ilTabs->addNonTabbedLink(
                "members_view",
                $lng->txt('mem_view_activate'),
                $ilCtrl->getLinkTargetByClass("ilrepositorygui", "")
            );
            $ilCtrl->clearParametersByClass("ilrepositorygui");
            return true;
        }
        return true;
    }
}
