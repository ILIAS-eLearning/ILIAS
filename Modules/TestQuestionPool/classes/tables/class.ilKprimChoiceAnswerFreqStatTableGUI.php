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
class ilKprimChoiceAnswerFreqStatTableGUI extends ilAnswerFrequencyStatisticTableGUI
{
    /**
     * @var assKprimChoice
     */
    protected $question;

    protected function getTrueOptionLabel()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        return $this->question->getTrueOptionLabelTranslation(
            $DIC->language(),
            $this->question->getOptionLabel()
        );
    }

    protected function getFalseOptionLabel()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        return $this->question->getFalseOptionLabelTranslation(
            $DIC->language(),
            $this->question->getOptionLabel()
        );
    }


    public function initColumns(): void
    {
        $lng = $this->DIC->language();
        $this->addColumn($lng->txt('tst_corr_answ_stat_tbl_header_answer'), '');
        $this->addColumn($lng->txt('tst_corr_answ_stat_tbl_header_frequency') . ': ' . $this->getTrueOptionLabel(), '');
        $this->addColumn($lng->txt('tst_corr_answ_stat_tbl_header_frequency') . ': ' . $this->getFalseOptionLabel(), '');

        foreach ($this->getData() as $row) {
            if (isset($row['addable'])) {
                $this->setActionsColumnEnabled(true);
                $this->addColumn('', '', '1%');
                break;
            }
        }
    }

    public function fillRow(array $a_set): void
    {
        $this->tpl->setCurrentBlock('answer');
        $this->tpl->setVariable('ANSWER', ilLegacyFormElementsUtil::prepareFormOutput($a_set['answer']));
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock('frequency');
        $this->tpl->setVariable('FREQUENCY', $a_set['frequency_true']);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock('frequency');
        $this->tpl->setVariable('FREQUENCY', $a_set['frequency_false']);
        $this->tpl->parseCurrentBlock();
    }
}
