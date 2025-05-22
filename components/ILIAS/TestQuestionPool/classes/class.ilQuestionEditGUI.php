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

use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\RequestDataCollector;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\Style\Content\Service as ContentStyle;

/**
 * Class ilQuestionEditGUI
 * @author		Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilQuestionEditGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI, assKprimChoiceGUI
 * @ilCtrl_Calls ilQuestionEditGUI: assOrderingQuestionGUI, assImagemapQuestionGUI
 * @ilCtrl_Calls ilQuestionEditGUI: assNumericGUI, assTextSubsetGUI, assSingleChoiceGUI, assTextQuestionGUI
 * @ilCtrl_Calls ilQuestionEditGUI: assErrorTextGUI, assOrderingHorizontalGUI, assTextSubsetGUI, assFormulaQuestionGUI
 * @ilCtrl_Calls ilQuestionEditGUI: assLongMenuGUI
 *
 * @ingroup components\ILIASTestQuestionPool
 */
class ilQuestionEditGUI
{
    private \ilGlobalTemplateInterface $main_tpl;
    private \ilTabsGUI $tabs;
    private readonly \ilHelpGUI $help;
    private readonly \ilCtrlInterface $ctrl;
    private readonly \ilAccessHandler $access;
    private readonly \ilLanguage $lng;
    private readonly \ilRbacSystem $rbac_system;
    private readonly ContentStyle $content_style;

    private readonly RequestDataCollector $request;
    private readonly GeneralQuestionPropertiesRepository $questionrepository;

    private ?int $questionid = null;
    private ?int $poolrefid = null;
    private ?int $poolobjid = null;
    private ?string $questiontype = null;
    /** @var array{object: object, method: string, parameters: string}[] */
    private array $new_id_listeners;
    private int $new_id_listener_cnt;
    private bool $selfassessmenteditingmode = false;
    private ?int $defaultnroftries = null;
    private ?ilPageConfig $page_config = null;

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->main_tpl = $DIC['tpl'];
        $this->tabs = $DIC['ilTabs'];
        $this->help = $DIC['ilHelp'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->access = $DIC['ilAccess'];
        $this->lng = $DIC['lng'];
        $this->rbac_system = $DIC['rbacsystem'];
        $this->content_style = $DIC->contentStyle();

        $local_dic = QuestionPoolDIC::dic();
        $this->request = $local_dic['request_data_collector'];
        $this->questionrepository = $local_dic['question.general_properties.repository'];

        if ($this->request->raw('qpool_ref_id')) {
            $this->setPoolRefId($this->request->raw('qpool_ref_id'));
        } elseif ($this->request->raw('qpool_obj_id')) {
            $this->setPoolObjId($this->request->raw('qpool_obj_id'));
        }
        $this->setQuestionId($this->request->getQuestionId());
        $this->setQuestionType($this->request->raw('q_type'));
        $this->lng->loadLanguageModule('assessment');

        $this->ctrl->saveParameter($this, ['qpool_ref_id', 'qpool_obj_id', 'q_id', 'q_type']);


        $this->new_id_listeners = [];
        $this->new_id_listener_cnt = 0;
    }

    public function setSelfAssessmentEditingMode(bool $a_selfassessmenteditingmode): void
    {
        $this->selfassessmenteditingmode = $a_selfassessmenteditingmode;
    }

    public function getSelfAssessmentEditingMode(): bool
    {
        return $this->selfassessmenteditingmode;
    }

    public function setDefaultNrOfTries(?int $a_defaultnroftries): void
    {
        $this->defaultnroftries = $a_defaultnroftries;
    }

    public function getDefaultNrOfTries(): ?int
    {
        return $this->defaultnroftries;
    }

    public function setPageConfig(ilPageConfig $a_val): void
    {
        $this->page_config = $a_val;
    }

    public function getPageConfig(): ?ilPageConfig
    {
        return $this->page_config;
    }

    public function executeCommand(): string
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            default:
                $question_gui = assQuestionGUI::_getQuestionGUI(
                    $this->getQuestionType() ?? '',
                    $this->getQuestionId()
                );
                $question = $question_gui->getObject();
                $question->setSelfAssessmentEditingMode(
                    $this->getSelfAssessmentEditingMode()
                );
                if ($this->getDefaultNrOfTries() > 0) {
                    $question->setDefaultNrOfTries(
                        $this->getDefaultNrOfTries()
                    );
                }

                if (is_object($this->page_config)) {
                    $question->setPreventRteUsage($this->getPageConfig()->getPreventRteUsage());
                    $question_gui->setInLearningModuleContext(get_class($this->page_config) === ilLMPageConfig::class);
                }
                $question->setObjId((int) $this->getPoolObjId());
                $question_gui->setObject($question);

                $count = $this->questionrepository->usageCount($question_gui->getObject()->getId());
                if ($count > 0) {
                    if ($this->rbac_system->checkAccess('write', $this->getPoolRefId())) {
                        $this->main_tpl->setOnScreenMessage('info', sprintf($this->lng->txt('qpl_question_is_in_use'), $count));
                    }
                }

                $this->tabs->activateTab('question');
                if ($cmd !== 'save') {
                    return (string) $this->ctrl->forwardCommand($question_gui);
                }
                if ($question_gui->saveQuestion()) {
                    $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
                }

                return (string) $question_gui->editQuestion();
        }
    }

    public function forwardToFeedbackEditGUI(assQuestionGUI $question_gui): void
    {
        $this->ctrl->forwardCommand(
            new ilAssQuestionFeedbackEditingGUI(
                $question_gui,
                $this->ctrl,
                $this->access,
                $this->main_tpl,
                $this->tabs,
                $this->lng,
                $this->help,
                $this->request,
                $this->content_style
            )
        );
    }

    public function setQuestionId(?int $a_questionid): void
    {
        $this->questionid = $a_questionid;
        $this->ctrl->setParameter($this, 'q_id', $this->questionid);
    }

    public function getQuestionId(): ?int
    {
        return $this->questionid;
    }

    public function setPoolRefId(?int $a_poolrefid): void
    {
        $this->poolrefid = $a_poolrefid;
        $this->ctrl->setParameter($this, 'qpool_ref_id', $this->poolrefid);

        if ($this->getPoolRefId() > 0) {
            $this->setPoolObjId(ilObject::_lookupObjId($this->getPoolRefId()));
        } else {
            $this->setPoolObjId(null);
        }
    }

    public function getPoolRefId(): ?int
    {
        return $this->poolrefid;
    }

    public function setPoolObjId(?int $a_poolobjid): void
    {
        $this->poolobjid = $a_poolobjid;
        $this->ctrl->setParameter($this, 'qpool_obj_id', $this->poolobjid);
    }

    public function getPoolObjId(): ?int
    {
        return $this->poolobjid;
    }

    public function setQuestionType(?string $a_questiontype): void
    {
        $this->questiontype = $a_questiontype;
        $this->ctrl->setParameter($this, 'q_type', $this->questiontype);
    }

    public function getQuestionType(): ?string
    {
        return $this->questiontype;
    }
}
