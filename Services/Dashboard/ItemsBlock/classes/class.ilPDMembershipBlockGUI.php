<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Memberships block
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilPDMembershipBlockGUI: ilCommonActionDispatcherGUI
 */
class ilPDMembershipBlockGUI extends ilPDSelectedItemsBlockGUI
{
    /** @var string */
    public static $block_type = 'pdmem';

    /**
     * ilPDSelectedItemsBlockGUI constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->lng->loadLanguageModule("dash");
        $this->lng->loadLanguageModule("mmbr");
    }

    /**
     * Evaluates the view settings of this block
     */
    protected function initViewSettings()
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockViewSettings::VIEW_MY_MEMBERSHIPS
        );
        $this->viewSettings->parse();

        $this->blockView = ilPDSelectedItemsBlockViewGUI::bySettings($this->viewSettings);

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
    }

    /**
     * No item entry
     *
     * @return string
     */
    protected function getNoItemFoundContent() : string
    {
        $txt = $this->lng->txt("rep_mo_mem_dash");
        return $txt;
    }
}
