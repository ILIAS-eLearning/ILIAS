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
 * Class ilMyTestResultsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/Test
 *
 * @ilCtrl_Calls ilMyTestResultsGUI: ilTestEvaluationGUI
 * @ilCtrl_Calls ilMyTestResultsGUI: ilAssQuestionPageGUI
 * @ilCtrl_Calls ilMyTestResultsGUI: ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilMyTestResultsGUI: ilAssGenFeedbackPageGUI
 */
class ilMyTestResultsGUI
{
    public const EVALGUI_CMD_SHOW_PASS_OVERVIEW = 'outUserResultsOverview';

    public function __construct(
        private readonly ?ilObjTest $test_obj,
        private readonly ilTestAccess $test_access,
        private readonly ilTestSession $test_session,
        private readonly ilTestObjectiveOrientedContainer $objective_parent,
        private readonly ilLanguage $lng,
        private readonly ilCtrl $ctrl,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly GeneralQuestionPropertiesRepository $questionrepository,
        private readonly RequestDataCollector $testrequest
    ) {
    }

    public function executeCommand(): void
    {
        /* @var ILIAS\DI\Container $DIC */
        global $DIC;

        if (!$DIC->ctrl()->getCmd()) {
            // @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
            // $DIC->ctrl()->setCmd(self::EVALGUI_CMD_SHOW_PASS_OVERVIEW);
        }

        switch ($DIC->ctrl()->getNextClass()) {
            case "iltestevaluationgui":
                $gui = new ilTestEvaluationGUI($this->test_obj);
                $gui->setObjectiveOrientedContainer($this->objective_parent);
                $gui->setTestAccess($this->test_access);
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case 'ilassquestionpagegui':
                $forwarder = new ilAssQuestionPageCommandForwarder(
                    $this->test_obj,
                    $this->lng,
                    $this->ctrl,
                    $this->tpl,
                    $this->questionrepository,
                    $this->testrequest,
                    $this->user->getId()
                );
                $forwarder->forward();
                break;
        }
    }
}
