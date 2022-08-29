<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';

/**
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI extends ilToolbarGUI
{
    public ilCtrl $ctrl;
    public ilTestRandomQuestionSetConfigGUI $questionSetConfigGUI;
    public ilTestRandomQuestionSetConfig $questionSetConfig;

    public function __construct(ilCtrl $ctrl, ilLanguage $lng, ilTestRandomQuestionSetConfigGUI $questionSetConfigGUI, ilTestRandomQuestionSetConfig $questionSetConfig)
    {
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->questionSetConfigGUI = $questionSetConfigGUI;
        $this->questionSetConfig = $questionSetConfig;

        parent::__construct();
    }

    public function build(): void
    {
        $this->setFormAction($this->ctrl->getFormAction($this->questionSetConfigGUI));

        if ($this->questionSetConfig->doesSelectableQuestionPoolsExist()) {
            $this->populateNewQuestionSelectionRuleInputs();
        }
    }

    private function buildSourcePoolSelectOptionsArray($availablePools): array
    {
        $sourcePoolSelectOptionArray = array();

        foreach ($availablePools as $poolId => $poolData) {
            $sourcePoolSelectOptionArray[$poolId] = $poolData['title'];
        }

        return $sourcePoolSelectOptionArray;
    }

    private function populateNewQuestionSelectionRuleInputs(): void
    {
        $this->addFormButton(
            $this->lng->txt('tst_rnd_quest_set_tb_add_pool_btn'),
            ilTestRandomQuestionSetConfigGUI::CMD_SHOW_POOL_SELECTOR_EXPLORER
        );
    }
}
