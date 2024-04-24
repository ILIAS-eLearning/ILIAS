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

declare(strict_types=1);

/**
 * GUI class for random question set general config form
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 *
 * @ilCtrl_Calls ilTestRandomQuestionSetGeneralConfigFormGUI: ilFormPropertyDispatchGUI
 */
class ilTestRandomQuestionSetGeneralConfigFormGUI extends ilPropertyFormGUI
{
    protected bool $edit_mode_enabled = true;

    public function __construct(
        private ilTestRandomQuestionSetConfigGUI $questionSetConfigGUI,
        private ilTestRandomQuestionSetConfig $questionSetConfig
    ) {
        parent::__construct();
        $this->lng->loadLanguageModule('form');
    }

    public function isEditModeEnabled(): bool
    {
        return $this->edit_mode_enabled;
    }

    public function setEditModeEnabled(bool $edit_mode_enabled): void
    {
        $this->edit_mode_enabled = $edit_mode_enabled;
    }

    public function build(): void
    {
        $this->setFormAction($this->ctrl->getFormAction($this->questionSetConfigGUI));

        $this->setTitle($this->lng->txt('tst_rnd_quest_set_cfg_general_form'));
        $this->setId('tstRndQuestSetCfgGeneralForm');

        $this->addCommandButton(
            ilTestRandomQuestionSetConfigGUI::CMD_SAVE_GENERAL_CONFIG_FORM,
            $this->lng->txt('save')
        );

        $requirePoolsQuestionsHomoScored = new ilCheckboxInputGUI(
            $this->lng->txt('tst_inp_all_quest_points_equal_per_pool'),
            'quest_points_equal_per_pool'
        );

        $requirePoolsQuestionsHomoScored->setInfo(
            $this->lng->txt('tst_inp_all_quest_points_equal_per_pool_desc')
        );

        $requirePoolsQuestionsHomoScored->setChecked(
            (bool)
            $this->questionSetConfig->arePoolsWithHomogeneousScoredQuestionsRequired()
        );

        $this->addItem($requirePoolsQuestionsHomoScored);

        // question amount config mode (per test / per pool)

        $questionAmountConfigMode = new ilRadioGroupInputGUI(
            $this->lng->txt('tst_inp_quest_amount_cfg_mode'),
            'quest_amount_cfg_mode'
        );

        $questionAmountConfigMode->setValue($this->fetchValidQuestionAmountConfigModeWithFallbackModePerTest(
            $this->questionSetConfig
        ));

        $questionAmountConfigModePerTest = new ilRadioOption(
            $this->lng->txt('tst_inp_quest_amount_cfg_mode_test'),
            ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST
        );

        $questionAmountConfigMode->addOption($questionAmountConfigModePerTest);

        $questionAmountConfigModePerPool = new ilRadioOption(
            $this->lng->txt('tst_inp_quest_amount_cfg_mode_pool'),
            ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_POOL
        );

        $questionAmountConfigMode->addOption($questionAmountConfigModePerPool);

        $questionAmountConfigMode->setRequired(true);

        $this->addItem($questionAmountConfigMode);

        // question amount per test

        $questionAmountPerTest = new ilNumberInputGUI(
            $this->lng->txt('tst_inp_quest_amount_per_test'),
            'quest_amount_per_test'
        );

        $questionAmountPerTest->setRequired(true);
        $questionAmountPerTest->setMinValue(1);
        $questionAmountPerTest->allowDecimals(false);
        $questionAmountPerTest->setMinvalueShouldBeGreater(false);
        $questionAmountPerTest->setSize(4);

        $questionAmountPerTest->setValue(
            (string) $this->questionSetConfig->getQuestionAmountPerTest()
        );

        $questionAmountConfigModePerTest->addSubItem($questionAmountPerTest);

        if (!$this->isEditModeEnabled()) {
            $requirePoolsQuestionsHomoScored->setDisabled(true);
            $questionAmountConfigMode->setDisabled(true);
            $questionAmountPerTest->setDisabled(true);
        }
    }

    private function fetchValidQuestionAmountConfigModeWithFallbackModePerTest(ilTestRandomQuestionSetConfig $config): ?string
    {
        switch ($config->getQuestionAmountConfigurationMode()) {
            case ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST:
            case ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_POOL:

                return $config->getQuestionAmountConfigurationMode();
        }

        return ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST;
    }

    public function save(): array
    {
        $log_array = [];
        $question_equal_per_pool = $this->getItemByPostVar('quest_points_equal_per_pool')->getChecked();
        $this->questionSetConfig->setPoolsWithHomogeneousScoredQuestionsRequired(
            $question_equal_per_pool
        );

        $log_array['tst_inp_all_quest_points_equal_per_pool_desc'] = $question_equal_per_pool ? '{{ true }}' : '{{ false }}';

        $question_amount_configuration_mode = $this->getItemByPostVar('quest_amount_cfg_mode')->getValue();
        $this->questionSetConfig->setQuestionAmountConfigurationMode(
            $question_amount_configuration_mode
        );

        $log_array['tst_inp_quest_amount_cfg_mode'] = $this->questionSetConfig->getQuestionAmountPerTest()
            ? '{{ tst_inp_quest_amount_cfg_mode_test }}' : '{{ tst_inp_quest_amount_cfg_mode_pool }}';

        $this->questionSetConfig->setQuestionAmountPerTest(null);
        if (!$this->questionSetConfig->getQuestionAmountPerTest()) {
            $question_amount_per_test = (int) $this->getItemByPostVar('quest_amount_per_test')->getValue();
            $this->questionSetConfig->setQuestionAmountPerTest(
                $question_amount_per_test
            );
            $log_array['tst_inp_quest_amount_per_test'] = $question_amount_per_test;
        }

        $this->questionSetConfig->saveToDb();
        return $log_array;
    }
}
