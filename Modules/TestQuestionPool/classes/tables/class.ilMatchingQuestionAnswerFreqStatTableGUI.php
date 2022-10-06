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
 * Class ilKprimChoiceAnswerFreqStatTableGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilMatchingQuestionAnswerFreqStatTableGUI extends ilAnswerFrequencyStatisticTableGUI
{
    /**
     * @var assMatchingQuestion
     */
    protected $question;

    public function __construct($a_parent_obj, $a_parent_cmd = "", $question = "")
    {
        parent::__construct($a_parent_obj, $a_parent_cmd, $question);
        $this->setDefaultOrderField('term');
    }

    public function initColumns(): void
    {
        $this->addColumn('Term', '');
        $this->addColumn('Definition', '');
        $this->addColumn('Frequency', '');
    }

    public function fillRow(array $a_set): void
    {
        $this->tpl->setCurrentBlock('answer');
        $this->tpl->setVariable('ANSWER', $a_set['term']);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock('answer');
        $this->tpl->setVariable('ANSWER', $a_set['definition']);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock('frequency');
        $this->tpl->setVariable('FREQUENCY', $a_set['frequency']);
        $this->tpl->parseCurrentBlock();
    }
}
