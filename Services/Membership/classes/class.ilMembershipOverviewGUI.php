<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Membership overview
 *
 * @ilCtrl_Calls ilMembershipOverviewGUI: ilPDMembershipBlockGUI
 *
 * @author killing@leifos.de
 */
class ilMembershipOverviewGUI
{
    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilLanguage
     */
    protected $lng;


    /**
     * @var \ilTemplate
     */
    protected $main_tpl;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    /**
     * Execute command
     */
    function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        switch ($next_class)
        {
            default:
                if (in_array($cmd, array("show")))
                {
                    $this->$cmd();
                }
        }
        $this->main_tpl->printToStdout();
    }

    /**
     * Show
     */
    protected function show()
    {
        $main_tpl = $this->main_tpl;

        $block = new ilPDMembershipBlockGUI();
        $main_tpl->setContent($block->getHTML());
    }

}