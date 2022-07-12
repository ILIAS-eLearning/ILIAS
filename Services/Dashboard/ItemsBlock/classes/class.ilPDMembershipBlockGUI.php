<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Memberships block
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPDMembershipBlockGUI: ilCommonActionDispatcherGUI
 */
class ilPDMembershipBlockGUI extends ilPDSelectedItemsBlockGUI
{
    public static string $block_type = 'pdmem';

    // Is block displayed on membership overview?
    protected bool $on_mem_overview;

    public function __construct(
        bool $on_mem_overview = false
    ) {
        parent::__construct();
        $this->lng->loadLanguageModule("dash");
        $this->lng->loadLanguageModule("mmbr");
        $this->on_mem_overview = $on_mem_overview;
    }

    protected function initViewSettings() : void
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockViewSettings::VIEW_MY_MEMBERSHIPS
        );
        $this->viewSettings->parse();

        $this->blockView = ilPDSelectedItemsBlockViewGUI::bySettings($this->viewSettings);

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
    }

    protected function returnToContext() : void
    {
        if ($this->on_mem_overview) {
            $this->ctrl->redirectByClass('ilmembershipoverviewgui', '');
        }
        parent::returnToContext();
    }

    protected function getViewTitle() : string
    {
        if ($this->on_mem_overview) {
            return $this->lng->txt("mmbr_memberships");
        }
        return parent::getViewTitle();
    }

    public function getNoItemFoundContent() : string
    {
        $txt = $this->lng->txt("rep_mo_mem_dash");
        return $txt;
    }
}
