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
     * @var \ilGlobalPageTemplate
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
    public function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");
        $this->main_tpl->setTitle($this->lng->txt("my_courses_groups"));

        switch ($next_class) {
            case "ilpdmembershipblockgui":
                $ctrl->setReturn($this, "show");
                $block = new ilPDMembershipBlockGUI(true);
                $ret = $this->ctrl->forwardCommand($block);
                if ($ret != "") {
                    $this->main_tpl->setContent($ret);
                }
                break;

            default:
                if (in_array($cmd, array("show"))) {
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
        $lng = $this->lng;

        $main_tpl->setTitle($lng->txt("my_courses_groups"));

        $block = new ilPDMembershipBlockGUI(true);
        $main_tpl->setContent($block->getHTML());
    }
}
