<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author Stefan Meyer <meyer.leifos.com>
 */
class ilMemberViewGUI
{
    
    /**
     * Show member view switch
     * @return
     * @param int $a_ref_id
     */
    public static function showMemberViewSwitch($a_ref_id)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        
        $settings = ilMemberViewSettings::getInstance();
        if (!$settings->isEnabled()) {
            return false;
        }
        global $DIC;

        $tpl = $DIC["tpl"];
        $tree = $DIC->repositoryTree();
        $lng = $DIC->language();
        $ilTabs = $DIC->tabs();
        
        // No course or group in path => aborting
        if (!$tree->checkForParentType($a_ref_id, 'crs') and
            !$tree->checkForParentType($a_ref_id, 'grp')) {
            return false;
        }
        
        // TODO: check edit_permission
        
        $active = $settings->isActive();
        
        $type = ilObject::_lookupType(ilObject::_lookupObjId($a_ref_id));
        if (($type == 'crs' or $type == 'grp') and $ilAccess->checkAccess('write', '', $a_ref_id)) {
            $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_ref_id);
            $ilCtrl->setParameterByClass("ilrepositorygui", "mv", "1");
            $ilCtrl->setParameterByClass("ilrepositorygui", "set_mode", "flat");
            $ilTabs->addNonTabbedLink(
                "members_view",
                $lng->txt('mem_view_activate'),
                $ilCtrl->getLinkTargetByClass("ilrepositorygui", "frameset")
            );
            $ilCtrl->clearParametersByClass("ilrepositorygui");
            return true;
        }
        return true;
    }
}
