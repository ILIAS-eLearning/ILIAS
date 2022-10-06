<?php

declare(strict_types=1);

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
 * Class ilTestRandomQuestionSelectionTableGUI
 */
class ilTestRandomQuestionSelectionTableGUI extends \ilTable2GUI
{
    private ilObjTest $test;

    public function __construct($a_parent_obj, string $a_parent_cmd, ilObjTest $test)
    {
        $this->test = $test;

        $this->setId('tst_rnd_qst_sel_' . $test->getRefId());
        parent::__construct($a_parent_obj, $a_parent_cmd, '');

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate('tpl.il_as_tst_random_question_offer.html', 'Modules/Test');
    }

    public function build(int $numberOfQuestions, int $selectedPool): self
    {
        $this->setTitle($this->lng->txt('tst_question_offer'));

        $questionIds = $this->test->randomSelectQuestions(
            $numberOfQuestions,
            $selectedPool
        );
        $questionpools = $this->test->getAvailableQuestionpools(true);

        $data = [];
        foreach ($questionIds as $questionId) {
            $dataset = $this->test->getQuestionDataset($questionId);
            $data[] = [
                'title' => $dataset->title,
                'description' => $dataset->description,
                'type' => assQuestion::_getQuestionTypeName($dataset->type_tag),
                'author' => $dataset->author,
                'pool' => $questionpools[$dataset->obj_fi]['title'],
            ];
        }
        $this->setData($data);

        $this->addHiddenInput('nr_of_questions', (string) $numberOfQuestions);
        $this->addHiddenInput('sel_qpl', (string) $selectedPool);
        $this->addHiddenInput('chosen_questions', implode(',', $questionIds));

        $this->addColumn($this->lng->txt('tst_question_title'));
        $this->addColumn($this->lng->txt('description'));
        $this->addColumn($this->lng->txt('tst_question_type'));
        $this->addColumn($this->lng->txt('author'));
        $this->addColumn($this->lng->txt('qpl'));

        $this->setNoEntriesText($this->lng->txt('no_questions_available'));

        if (count($data) > 0) {
            $this->addCommandButton('insertRandomSelection', $this->lng->txt('random_accept_sample'));
            $this->addCommandButton('createRandomSelection', $this->lng->txt('random_another_sample'));
        }
        $this->addCommandButton('cancelRandomSelect', $this->lng->txt('cancel'));

        return $this;
    }
}
