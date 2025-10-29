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

namespace ILIAS\Help\GuidedTour\Step;

use ilDBInterface;
use ILIAS\Help\GuidedTour\InternalDataService;

class StepDBRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected InternalDataService $data
    ) {
    }

    protected function getMaxOrderNr(int $tour_id): int
    {
        $set = $this->db->queryF(
            "SELECT MAX(order_nr) max_order FROM help_gt_step " .
            " WHERE tour_id = %s",
            ["integer"],
            [$tour_id]
        );
        if ($record = $this->db->fetchAssoc($set)) {
            return (int) $record["max_order"];
        }
        return 0;
    }

    public function create(Step $step): int
    {
        $id = $this->db->nextId('help_gt_step');
        $order_nr = $this->getMaxOrderNr($step->getTourId()) + 10;
        $this->db->insert('help_gt_step', [
            'id' => ['integer', $id],
            'tour_id' => ['integer', $step->getTourId()],
            'order_nr' => ['integer', $order_nr],
            'type' => ['integer', $step->getType()->value],
            'element_id' => ['text', $step->getElementId()]
        ]);
        return $id;
    }

    public function update(Step $step): void
    {
        $this->db->update('help_gt_step', [
            'tour_id' => ['integer', $step->getTourId()],
            'order_nr' => ['integer', $step->getOrderNr()],
            'type' => ['integer', $step->getType()->value],
            'element_id' => ['text', $step->getElementId()]
        ], [
            'id' => ['integer', $step->getId()]
        ]);
    }

    public function delete(int $tour_id, int $step_id): void
    {
        $this->db->manipulateF(
            "DELETE FROM help_gt_step WHERE " .
            " id = %s AND tour_id = %s",
            ["integer", "integer"],
            [$step_id, $tour_id]
        );
    }

    public function getById(int $id): ?Step
    {
        $set = $this->db->queryF(
            'SELECT * FROM help_gt_step WHERE id = %s',
            ['integer'],
            [$id]
        );

        $record = $this->db->fetchAssoc($set);
        if ($record === false) {
            return null;
        }

        return $this->mapRecordToStep($record);
    }

    /**
     * @return \Generator<Step>
     */
    public function getStepsOfTour(int $tour_id): \Generator
    {
        $set = $this->db->queryF(
            "SELECT * FROM help_gt_step " .
            " WHERE tour_id = %s ORDER BY order_nr ASC",
            ["integer"],
            [$tour_id]
        );
        while ($record = $this->db->fetchAssoc($set)) {
            yield $this->mapRecordToStep($record);
        }
    }

    public function countStepsOfTour(int $tour_id): int
    {
        $set = $this->db->queryF(
            "SELECT COUNT(*) cnt FROM help_gt_step " .
            " WHERE tour_id = %s",
            ["integer"],
            [$tour_id]
        );
        if ($record = $this->db->fetchAssoc($set)) {
            return (int) $record["cnt"];
        }
        return 0;
    }

    public function saveOrder(int $tour_id, array $order): void
    {
        $order_nr = 0;
        foreach ($order as $step_id) {
            $order_nr += 10;
            $this->db->update(
                "help_gt_step",
                [
                "order_nr" => ["integer", $order_nr],
            ],
                [    // where
                    "id" => ["integer", $step_id],
                    "tour_id" => ["integer", $tour_id],
                ]
            );
        }
    }

    protected function mapRecordToStep(array $record): Step
    {
        return $this->data->step(
            (int) $record['id'],
            (int) $record['tour_id'],
            (int) $record['order_nr'],
            StepType::from((int) $record['type']),
            (string) $record['element_id']
        );
    }
}
