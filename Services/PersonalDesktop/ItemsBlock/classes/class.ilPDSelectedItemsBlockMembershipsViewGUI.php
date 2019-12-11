<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockMembershipsViewGUI.php';

/**
 * Class ilPDSelectedItemsBlockMembershipsViewGUI
 */
class ilPDSelectedItemsBlockMembershipsViewGUI extends ilPDSelectedItemsBlockViewGUI
{
    /**
     * @inheritdoc
     */
    public function getGroups()
    {
        if ($this->viewSettings->isSortedByLocation()) {
            return $this->groupItemsByLocation();
        } elseif ($this->viewSettings->isSortedByStartDate()) {
            return $this->groupItemsByStartDate();
        }

        return $this->groupItemsByType();
    }

    /**
     * @inheritdoc
     */
    public function getScreenId()
    {
        return 'crs_grp';
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->lng->txt('pd_my_memberships');
    }

    /**
     * @inheritdoc
     */
    public function supportsSelectAll()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function mayRemoveItem($refId)
    {
        return $this->accessHandler->checkAccess('leave', $refId);
    }

    /**
     * @inheritdoc
     */
    public function getIntroductionHtml()
    {
        $tpl = new ilTemplate('tpl.pd_my_memberships_intro.html', true, true, 'Services/PersonalDesktop');
        $tpl->setVariable('IMG_PD_LARGE', ilObject::_getIcon('', 'big', 'pd'));
        $tpl->setVariable('TXT_WELCOME', $this->lng->txt('pd_my_memberships_intro'));
        $tpl->setVariable('TXT_INTRO_1', $this->lng->txt('pd_my_memberships_intro2'));

        return $tpl->get();
    }
}
