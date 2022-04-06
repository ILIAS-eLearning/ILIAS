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

class ilPDSelectedItemsBlockSelectedItemsViewGUI extends ilPDSelectedItemsBlockViewGUI
{
    public function getGroups() : array
    {
        if ($this->viewSettings->isSortedByLocation()) {
            return $this->groupItemsByLocation();
        } elseif ($this->viewSettings->isSortedByAlphabet()) {
            return $this->sortItemsByAlphabetInOneGroup();
        }

        return $this->groupItemsByType();
    }

    public function getScreenId() : string
    {
        return 'sel_items';
    }

    public function getTitle() : string
    {
        return $this->lng->txt('dash_favourites');
    }

    public function supportsSelectAll() : bool
    {
        return true;
    }

    public function getIntroductionHtml() : string
    {
        $tpl = new ilTemplate('tpl.dashboard_intro.html', true, true, 'Services/Dashboard');
        $tpl->setVariable('IMG_PD_LARGE', ilObject::_getIcon(0, 'big', 'pd'));
        $tpl->setVariable('TXT_WELCOME', $this->lng->txt('pdesk_intro'));

        $tpl->setVariable('TXT_INTRO_2', sprintf(
            $this->lng->txt('pdesk_intro3'),
            '<a href="' . ilLink::_getStaticLink(1, 'root', true) . '">' . $this->getRepositoryTitle() . '</a>'
        ));
        $tpl->setVariable('TXT_INTRO_3', $this->lng->txt('pdesk_intro4'));

        return $tpl->get();
    }
}
