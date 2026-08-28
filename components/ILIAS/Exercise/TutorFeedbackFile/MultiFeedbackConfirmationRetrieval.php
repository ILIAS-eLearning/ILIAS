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

namespace ILIAS\Exercise\TutorFeedbackFile;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class MultiFeedbackConfirmationRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;

    public function __construct(
        protected InternalDomainService $domain,
        protected \ilExAssignment $assignment
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->applyOrder($this->collectData(), $order);
        $data = $this->applyRange($data, $range);

        foreach ($data as $row) {
            yield $row;
        }
    }

    public function count(
        array $filter = [],
        array $parameters = []
    ): int {
        return count($this->collectData());
    }

    public function isFieldNumeric(string $field): bool
    {
        return false;
    }

    /**
     * @param list<string> $ids
     * @return array<int, list<string>>
     */
    public function getSelectedFiles(array $ids): array
    {
        $selected_ids = array_map('strval', $ids);
        $files = [];

        foreach ($this->collectData() as $file) {
            if (!in_array($file['id'], $selected_ids, true)) {
                continue;
            }

            $files[(int) $file['user_id']][] = $file['id'];
        }

        return $files;
    }

    /**
     * @return list<string>
     */
    public function getAllFileIds(): array
    {
        $ids = [];
        foreach ($this->collectData() as $file) {
            $ids[] = $file['id'];
        }

        return $ids;
    }

    protected function collectData(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $exercise = new \ilObjExercise($this->assignment->getExerciseId(), false);
        $feedback_zip = $this->domain->assignment()->tutorFeedbackZip();
        $this->data = [];

        foreach ($feedback_zip->getFiles(
            $exercise,
            $this->assignment->getId(),
            $this->domain->user()->getId()
        ) as $file) {
            $file['id'] = $feedback_zip->getFileMd5((int) $file['user_id'], $file['file']);
            $this->data[] = $file;
        }

        return $this->data;
    }
}
