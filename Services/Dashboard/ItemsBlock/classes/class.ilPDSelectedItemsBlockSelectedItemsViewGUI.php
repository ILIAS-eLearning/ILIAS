<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilPDSelectedItemsBlockSelectedItemsViewGUI
 */
class ilPDSelectedItemsBlockSelectedItemsViewGUI extends ilPDSelectedItemsBlockViewGUI
{
    /**
     * @inheritdoc
     */
    public function getGroups() : array
    {
        if ($this->viewSettings->isSortedByLocation()) {
            return $this->groupItemsByLocation();
        }

        return $this->groupItemsByType();
    }

    /**
     * @inheritdoc
     */
    public function getScreenId() : string
    {
        return 'sel_items';
    }

    /**
     * @inheritdoc
     */
    public function getTitle() : string
    {
        return $this->lng->txt('dash_favourites');
    }

    /**
     * @inheritdoc
     */
    public function supportsSelectAll() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getIntroductionHtml() : string
    {
        $tpl = new ilTemplate('tpl.dashboard_intro.html', true, true, 'Services/Dashboard');
        $tpl->setVariable('IMG_PD_LARGE', ilObject::_getIcon('', 'big', 'pd'));
        $tpl->setVariable('TXT_WELCOME', $this->lng->txt('pdesk_intro'));

        $tpl->setVariable('TXT_INTRO_2', sprintf(
            $this->lng->txt('pdesk_intro3'),
            '<a href="' . ilLink::_getStaticLink(1, 'root', true) . '">' . $this->getRepositoryTitle() . '</a>'
        ));
        $tpl->setVariable('TXT_INTRO_3', $this->lng->txt('pdesk_intro4'));

        return $tpl->get();
    }
}
