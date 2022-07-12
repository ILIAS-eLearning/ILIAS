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

require_once('./Services/COPage/classes/class.ilPageObjectGUI.php');
require_once('./Modules/TestQuestionPool/classes/class.ilAssQuestionPage.php');

/**
 * Question page GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilAssQuestionPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilAssQuestionPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilAssQuestionPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 *
 * @ingroup ModulesTestQuestionPool
 */
class ilAssQuestionPageGUI extends ilPageObjectGUI
{
    const TEMP_PRESENTATION_TITLE_PLACEHOLDER = '___TEMP_PRESENTATION_TITLE_PLACEHOLDER___';

    private $originalPresentationTitle = '';

    // fau: testNav - variables for info and actions HTML
    private $questionInfoHTML = '';
    private $questionActionsHTML = '';
    // fau.
    protected \ILIAS\TestQuestionPool\InternalRequestService $testrequest;

    /**
     * Constructor
     *
     * @param int $a_id
     * @param int $a_old_nr
     */
    public function __construct($a_id = 0, $a_old_nr = 0)
    {
        global $DIC;
        $this->testrequest = $DIC->testQuestionPool()->internal()->request();
        parent::__construct('qpl', $a_id, $a_old_nr);
        $this->setEnabledPageFocus(false);
    }

    public function getOriginalPresentationTitle() : string
    {
        return $this->originalPresentationTitle;
    }

    public function setOriginalPresentationTitle($originalPresentationTitle) : void
    {
        $this->originalPresentationTitle = $originalPresentationTitle;
    }

    protected function isPageContainerToBeRendered() : bool
    {
        return $this->getRenderPageContainer();
    }

    public function showPage() : string
    {
        $this->setOriginalPresentationTitle($this->getPresentationTitle());

        $this->setPresentationTitle(self::TEMP_PRESENTATION_TITLE_PLACEHOLDER);

        // fau: testNav - enable page toc as placeholder for info and actions block (see self::insertPageToc)
        $config = $this->getPageConfig();
        $config->setEnablePageToc('y');
        $this->setPageConfig($config);
        // fau.
        return parent::showPage();
    }
    
    public function finishEditing() : void
    {
        $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
    }

    public function postOutputProcessing(string $a_output) : string
    {
        $a_output = str_replace(
            self::TEMP_PRESENTATION_TITLE_PLACEHOLDER,
            $this->getOriginalPresentationTitle(),
            $a_output
        );

        $a_output = preg_replace("/src=\"\\.\\//ims", "src=\"" . ILIAS_HTTP_PATH . "/", $a_output);

        return $a_output;
    }

    // fau: testNav - support the addition of question info and actions below the title

    /**
     * Set the HTML of a question info block below the title (number, status, ...)
     * @param string	$a_html
     */
    public function setQuestionInfoHTML($a_html) : void
    {
        $this->questionInfoHTML = $a_html;
    }

    /**
     * Set the HTML of a question actions block below the title
     * @param string 	$a_html
     */
    public function setQuestionActionsHTML($a_html) : void
    {
        $this->questionActionsHTML = $a_html;
    }

    /**
     * Replace page toc placeholder with question info and actions
     * @todo: 	support question info and actions in the page XSL directly
     * 			the current workaround avoids changing the COPage service
     */
    public function insertPageToc(string $a_output) : string
    {
        if (!empty($this->questionInfoHTML) || !empty($this->questionActionsHTML)) {
            $tpl = new ilTemplate('tpl.tst_question_subtitle_blocks.html', true, true, 'Modules/TestQuestionPool');
            $tpl->setVariable('QUESTION_INFO', $this->questionInfoHTML);
            $tpl->setVariable('QUESTION_ACTIONS', $this->questionActionsHTML);
            $a_output = str_replace("{{{{{PageTOC}}}}}", $tpl->get(), $a_output);
        } else {
            $a_output = str_replace("{{{{{PageTOC}}}}}", '', $a_output);
        }
        return $a_output;
    }
    // fau.
}
