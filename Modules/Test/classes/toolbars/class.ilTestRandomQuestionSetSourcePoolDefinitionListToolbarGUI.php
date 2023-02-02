<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
