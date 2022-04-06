<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

class ilPDSelectedItemsBlockMembershipsViewGUI extends ilPDSelectedItemsBlockViewGUI
{
    public function getGroups() : array
    {
        if ($this->viewSettings->isSortedByLocation()) {
            return $this->groupItemsByLocation();
        } elseif ($this->viewSettings->isSortedByStartDate()) {
            return $this->groupItemsByStartDate();
        } elseif ($this->viewSettings->isSortedByAlphabet()) {
            return $this->sortItemsByAlphabetInOneGroup();
        }

        return $this->groupItemsByType();
    }

    public function getScreenId() : string
    {
        return 'crs_grp';
    }

    public function getTitle() : string
    {
        return $this->lng->txt('my_courses_groups');
    }

    public function supportsSelectAll() : bool
    {
        return false;
    }

    public function mayRemoveItem($refId) : bool
    {
        return $this->accessHandler->checkAccess('leave', $refId);
    }

    public function getIntroductionHtml() : string
    {
        $tpl = new ilTemplate('tpl.dashboard_my_memberships_intro.html', true, true, 'Services/Dashboard');
        $tpl->setVariable('IMG_PD_LARGE', ilObject::_getIcon(0, 'big', 'pd'));
        $tpl->setVariable('TXT_WELCOME', $this->lng->txt('pd_my_memberships_intro'));
        $tpl->setVariable('TXT_INTRO_1', $this->lng->txt('pd_my_memberships_intro2'));

        return $tpl->get();
    }
}
