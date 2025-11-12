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

namespace ILIAS\LearningModule\Question\Usage;

use Generator;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\RetrievalBase;
use ilCtrl;
use ilLMPageObject;
use ilLMPageObjectGUI;
use ILIAS\TestQuestionPool\Questions\PublicInterface as QuestionInfo;

class Retrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected array $question_data;

    public function __construct(
        protected int $obj_id,
        protected ilCtrl $ctrl,
        protected QuestionInfo $question_info
    ) {
    }

    protected function getQuestionData(): array
    {
        if (isset($this->question_data)) {
            return $this->question_data;
        }
        $res = ilLMPageObject::queryQuestionsOfLearningModule($this->obj_id, '', '', 0, 0);
        return $this->question_data = $res['set'];
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): Generator {
        $question_data = $this->getQuestionData();

        $question_data = $this->applyRange($question_data, $range);

        $data = [];
        foreach ($question_data as $datum) {
            $data[] = [
                'id' => $datum['question_id'],
                'title' => $this->question_info->getGeneralQuestionProperties((int) $datum['question_id'])->getTitle(),
                'page_title' => ilLMPageObject::_lookupTitle((int) $datum['page_id']),
                'page_link' => $this->getLinkToPage((int) $datum['page_id'])
            ];
        }
        yield from $data;
    }

    protected function getLinkToPage(int $page_id): string
    {
        $this->ctrl->setParameterByClass(ilLMPageObjectGUI::class, 'obj_id', $page_id);
        $link = $this->ctrl->getLinkTargetByClass(ilLMPageObjectGUI::class, 'edit');
        $this->ctrl->clearParameterByClass(ilLMPageObjectGUI::class, 'obj_id');
        return $link;
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        return count($this->getQuestionData());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === 'last_update';
    }
}
