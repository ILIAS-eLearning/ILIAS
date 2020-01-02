<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockViewGUI.php';

/**
 * Class ilPDSelectedItemsBlockSelectedItemsViewGUI
 */
class ilPDSelectedItemsBlockSelectedItemsViewGUI extends ilPDSelectedItemsBlockViewGUI
{
    /**
     * @inheritdoc
     */
    public function getGroups()
    {
        if ($this->viewSettings->isSortedByLocation()) {
            return $this->groupItemsByLocation();
        }

        return $this->groupItemsByType();
    }

    /**
     * @inheritdoc
     */
    public function getScreenId()
    {
        return 'sel_items';
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->lng->txt('pd_my_offers');
    }

    /**
     * @inheritdoc
     */
    public function supportsSelectAll()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getIntroductionHtml()
    {
        $tpl = new ilTemplate('tpl.pd_intro.html', true, true, 'Services/PersonalDesktop');
        $tpl->setVariable('IMG_PD_LARGE', ilObject::_getIcon('', 'big', 'pd'));
        $tpl->setVariable('TXT_WELCOME', $this->lng->txt('pdesk_intro'));
        $tpl->setVariable("TXT_INTRO_1", sprintf($this->lng->txt('pdesk_intro2'), $this->lng->txt('to_desktop')));
        
        require_once 'Services/Link/classes/class.ilLink.php';
        $tpl->setVariable('TXT_INTRO_2', sprintf(
            $this->lng->txt('pdesk_intro3'),
            '<a href="' . ilLink::_getStaticLink(1, 'root', true) . '">' . $this->getRepositoryTitle() . '</a>'
        ));
        $tpl->setVariable('TXT_INTRO_3', $this->lng->txt('pdesk_intro4'));

        return $tpl->get();
    }
}
