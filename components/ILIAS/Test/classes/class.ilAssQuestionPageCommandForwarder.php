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

use ILIAS\Test\RequestDataCollector;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

/**
 * Class ilTestCtrlForwarder
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/Test
 */
class ilAssQuestionPageCommandForwarder
{
    private int $question_id;

    public function __construct(
        private readonly ilObjTest $test_obj,
        private readonly ilLanguage $lng,
        private readonly ilCtrl $ctrl,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly GeneralQuestionPropertiesRepository $questionrepository,
        private readonly RequestDataCollector $testrequest
    ) {
        $this->question_id = $this->testrequest->getQuestionId();
    }

    public function forward(): void
    {
        if ($this->testrequest->raw('prev_qid')) {
            $this->ctrl->setParameter($this, 'prev_qid', $this->testrequest->raw('prev_qid'));
        }

        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath(0)
        );
        $this->tpl->parseCurrentBlock();

        // syntax style
        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $this->tpl->parseCurrentBlock();
        $q_gui = assQuestionGUI::_getQuestionGUI("", $this->question_id);

        $q_gui->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_PREVIEW);
        $q_gui->setQuestionTabs();
        $q_gui->outAdditionalOutput();
        $q_gui->getObject();
        $question = $q_gui->getObject();
        $question->setObjId($this->test_obj->getId());
        $q_gui->setObject($question);

        if ($this->ctrl->getCmd() === 'edit'
            && $this->test_obj->evalTotalPersons() !== 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('question_is_part_of_running_test'), true);
            $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
        }

        $this->ctrl->saveParameter($this, "q_id");
        $this->lng->loadLanguageModule("content");
        $this->ctrl->setReturnByClass("ilAssQuestionPageGUI", "view");
        $this->ctrl->setReturnByClass("ilObjTestGUI", "questions");
        $page_gui = new ilAssQuestionPageGUI($this->testrequest->getQuestionId());

        $page_gui->setEditPreview(true);
        $page_gui->setQuestionHTML(array($q_gui->getObject()->getId() => $q_gui->getPreview(true)));
        $page_gui->setTemplateTargetVar("ADM_CONTENT");
        $page_gui->setOutputMode($this->test_obj->evalTotalPersons() == 0 ? "edit" : 'preview');
        $page_gui->setHeader($question->getTitle());
        $page_gui->setPresentationTitle(
            $question->getTitle()
            . ' [' . $this->lng->txt('question_id_short')
            . ': ' . $question->getId() . ']'
        );

        $html = $this->ctrl->forwardCommand($page_gui);
        $this->tpl->setContent($html);
    }
}
