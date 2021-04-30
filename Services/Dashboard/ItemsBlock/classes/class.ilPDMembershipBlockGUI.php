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
     * Is block displayed on membership overview?
     */
    protected $on_mem_overview;

    /**
     * ilPDSelectedItemsBlockGUI constructor.
     */
    public function __construct($on_mem_overview = false)
    {
        parent::__construct();
        $this->lng->loadLanguageModule("dash");
        $this->lng->loadLanguageModule("mmbr");
        $this->on_mem_overview = $on_mem_overview;
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
     * Return to context
     * @param
     * @return
     */
    protected function returnToContext()
    {
        if ($this->on_mem_overview) {
            $this->ctrl->redirectByClass('ilmembershipoverviewgui', '');
        }
        parent::returnToContext();
    }

    /**
     * Get view title
     * @return string
     */
    protected function getViewTitle()
    {
        if ($this->on_mem_overview) {
            return $this->lng->txt("mmbr_memberships");
        }
        return parent::getViewTitle();
    }


    /**
     * No item entry
     *
     * @return string
     */
    public function getNoItemFoundContent() : string
    {
        $txt = $this->lng->txt("rep_mo_mem_dash");
        return $txt;
    }
}
