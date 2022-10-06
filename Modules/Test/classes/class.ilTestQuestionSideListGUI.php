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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestQuestionSideListGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTestPlayerAbstractGUI
     */
    private $targetGUI;

    /**
     * @var array
     */
    private $questionSummaryData;

    /**
     * @var integer
     */
    private $currentSequenceElement;

    /**
     * @var string
     */
    private $currentPresentationMode;

    /**
     * @var bool
     */
    private $disabled;

    /**
     * @param ilCtrl $ctrl
     * @param ilLanguage $lng
     */
    public function __construct(ilCtrl $ctrl, ilLanguage $lng)
    {
        $this->ctrl = $ctrl;
        $this->lng = $lng;

        $this->questionSummaryData = array();
        $this->currentSequenceElement = null;
        $this->disabled = false;
    }

    /**
     * @return ilTestPlayerAbstractGUI
     */
    public function getTargetGUI(): ilTestPlayerAbstractGUI
    {
        return $this->targetGUI;
    }

    /**
     * @param ilTestPlayerAbstractGUI $targetGUI
     */
    public function setTargetGUI($targetGUI)
    {
        $this->targetGUI = $targetGUI;
    }

    /**
     * @return array
     */
    public function getQuestionSummaryData(): array
    {
        return $this->questionSummaryData;
    }

    /**
     * @param array $questionSummaryData
     */
    public function setQuestionSummaryData($questionSummaryData)
    {
        $this->questionSummaryData = $questionSummaryData;
    }

    /**
     * @return int
     */
    public function getCurrentSequenceElement(): ?int
    {
        return $this->currentSequenceElement;
    }

    /**
     * @param int $currentSequenceElement
     */
    public function setCurrentSequenceElement($currentSequenceElement)
    {
        $this->currentSequenceElement = $currentSequenceElement;
    }

    /**
     * @return string
     */
    public function getCurrentPresentationMode(): string
    {
        return $this->currentPresentationMode;
    }

    /**
     * @param string $currentPresentationMode
     */
    public function setCurrentPresentationMode($currentPresentationMode)
    {
        $this->currentPresentationMode = $currentPresentationMode;
    }

    /**
     * @return boolean
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * @param boolean $disabled
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }

    /**
     * @return ilPanelGUI
     */
    private function buildPanel(): ilPanelGUI
    {
        require_once 'Services/UIComponent/Panel/classes/class.ilPanelGUI.php';
        $panel = ilPanelGUI::getInstance();
        $panel->setHeadingStyle(ilPanelGUI::HEADING_STYLE_SUBHEADING);
        $panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_SECONDARY);
        $panel->setHeading($this->lng->txt('list_of_questions'));
        return $panel;
    }

    /**
     * @return string
     */
    private function renderList(): string
    {
        $tpl = new ilTemplate('tpl.il_as_tst_list_of_questions_short.html', true, true, 'Modules/Test');

        foreach ($this->getQuestionSummaryData() as $row) {
            $title = ilLegacyFormElementsUtil::prepareFormOutput($row['title']);

            if (strlen($row['description'])) {
                $description = " title=\"" . htmlspecialchars($row['description']) . "\" ";
            } else {
                $description = "";
            }

            $active = ($row['sequence'] == $this->getCurrentSequenceElement()) ? ' active' : '';

            $class = (
                $row['worked_through'] ? 'answered' . $active : 'unanswered' . $active
            );

            $headerclass = ($row['sequence'] == $this->getCurrentSequenceElement()) ? 'bold' : '';

            if ($row['marked']) {
                $tpl->setCurrentBlock("mark_icon");
                $tpl->setVariable("ICON_SRC", ilUtil::getImagePath('marked.svg'));
                $tpl->setVariable("ICON_TEXT", $this->lng->txt('tst_question_marked'));
                $tpl->setVariable("ICON_CLASS", 'ilTestMarkQuestionIcon');
                $tpl->parseCurrentBlock();
            }

            if ($this->isDisabled() || $row['disabled']) {
                $tpl->setCurrentBlock('disabled_entry');
                $tpl->setVariable('CLASS', $class);
                $tpl->setVariable('HEADERCLASS', $headerclass);
                $tpl->setVariable('ITEM', $title);
                $tpl->setVariable('DESCRIPTION', $description);
                $tpl->parseCurrentBlock();
            } else {
                // fau: testNav - show mark icon in side list
                // fau.
                $tpl->setCurrentBlock('linked_entry');
                $tpl->setVariable('HREF', $this->buildLink($row['sequence']));
                $tpl->setVariable('NEXTCMD', ilTestPlayerCommands::SHOW_QUESTION);
                $tpl->setVariable('NEXTSEQ', $row['sequence']);
                $tpl->setVariable('HEADERCLASS', $headerclass);
                $tpl->setVariable('CLASS', $class);
                $tpl->setVariable('ITEM', $title);
                $tpl->setVariable("DESCRIPTION", $description);
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock('item');
        }

        return $tpl->get();
    }

    /**
     * @return string
     */
    public function getHTML(): string
    {
        $panel = $this->buildPanel();
        $panel->setBody($this->renderList());
        return $panel->getHTML();
    }

    /**
     * @param $row
     * @return string
     */
    private function buildLink($sequenceElement): string
    {
        $this->ctrl->setParameter(
            $this->getTargetGUI(),
            'pmode',
            ''
        );

        $this->ctrl->setParameter(
            $this->getTargetGUI(),
            'sequence',
            $sequenceElement
        );

        $href = $this->ctrl->getLinkTarget($this->getTargetGUI(), ilTestPlayerCommands::SHOW_QUESTION);

        $this->ctrl->setParameter(
            $this->getTargetGUI(),
            'pmode',
            $this->getCurrentPresentationMode()
        );
        $this->ctrl->setParameter(
            $this->getTargetGUI(),
            'sequence',
            $this->getCurrentSequenceElement()
        );
        return $href;
    }
}
