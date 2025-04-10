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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/TestQuestionPool
 */
class ilAssQuestionRelatedNavigationBarGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;
    protected $instantResponseCmd;
    protected $instantResponseEnabled;

    public function __construct(ilCtrl $ctrl, ilLanguage $lng)
    {
        $this->ctrl = $ctrl;
        $this->lng = $lng;
    }

    public function setInstantResponseEnabled($instantFeedbackEnabled): void
    {
        $this->instantResponseEnabled = $instantFeedbackEnabled;
    }

    public function isInstantResponseEnabled()
    {
        return $this->instantResponseEnabled;
    }

    public function setInstantResponseCmd($instantResponseCmd): void
    {
        $this->instantResponseCmd = $instantResponseCmd;
    }

    public function getInstantResponseCmd()
    {
        return $this->instantResponseCmd;
    }

    public function getHTML(): string
    {
        $navTpl = new ilTemplate('tpl.qst_question_related_navigation.html', true, true, 'components/ILIAS/TestQuestionPool');

        $parseQuestionRelatedNavigation = false;

        if ($this->isInstantResponseEnabled()) {
            $navTpl->setCurrentBlock("direct_feedback");
            $navTpl->setVariable("CMD_SHOW_INSTANT_RESPONSE", $this->getInstantResponseCmd());
            $navTpl->setVariable("TEXT_SHOW_INSTANT_RESPONSE", $this->lng->txt("check"));
            $navTpl->parseCurrentBlock();

            $parseQuestionRelatedNavigation = true;
        }

        if ($parseQuestionRelatedNavigation) {
            $navTpl->setCurrentBlock("question_related_navigation");
            $navTpl->parseCurrentBlock();
        }

        return $navTpl->get();
    }
}
