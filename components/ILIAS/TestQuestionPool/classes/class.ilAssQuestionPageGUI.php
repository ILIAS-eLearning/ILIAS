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
 * Question page GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilAssQuestionPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilAssQuestionPageGUI: ilPublicUserProfileGUI, ilCommentGUI
 * @ilCtrl_Calls ilAssQuestionPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 *
 * @ingroup components\ILIASTestQuestionPool
 */
class ilAssQuestionPageGUI extends ilPageObjectGUI
{
    private $question_info_html = '';
    private $question_actions_html = '';

    public function __construct(int $a_id = 0)
    {
        parent::__construct('qpl', $a_id);
        $this->setEnabledPageFocus(false);
    }

    protected function isPageContainerToBeRendered(): bool
    {
        return $this->getRenderPageContainer();
    }

    public function showPage(): string
    {
        $config = $this->getPageConfig();
        $config->setEnablePageToc(true);
        $this->setPageConfig($config);
        // fau.
        return parent::showPage();
    }

    public function finishEditing(): void
    {
        $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
    }

    public function postOutputProcessing(string $output): string
    {
        return preg_replace('/src="\./ims', 'src="' . ILIAS_HTTP_PATH . '/', $output);
    }

    // fau: testNav - support the addition of question info and actions below the title

    /**
     * Set the HTML of a question info block below the title (number, status, ...)
     * @param string	$a_html
     */
    public function setQuestionInfoHTML($a_html): void
    {
        $this->question_info_html = $a_html;
    }

    /**
     * Set the HTML of a question actions block below the title
     * @param string 	$a_html
     */
    public function setQuestionActionsHTML($a_html): void
    {
        $this->question_actions_html = $a_html;
    }

    /**
     * Replace page toc placeholder with question info and actions
     * @todo: 	support question info and actions in the page XSL directly
     * 			the current workaround avoids changing the COPage service
     */
    public function insertPageToc(string $a_output): string
    {
        if (!empty($this->question_info_html) || !empty($this->question_actions_html)) {
            $tpl = new ilTemplate('tpl.tst_question_subtitle_blocks.html', true, true, 'components/ILIAS/TestQuestionPool');
            $tpl->setVariable('QUESTION_INFO', $this->question_info_html);
            $tpl->setVariable('QUESTION_ACTIONS', $this->question_actions_html);
            $a_output = str_replace("{{{{{PageTOC}}}}}", $tpl->get(), $a_output);
        } else {
            $a_output = str_replace("{{{{{PageTOC}}}}}", '', $a_output);
        }
        return $a_output;
    }
    // fau.
}
