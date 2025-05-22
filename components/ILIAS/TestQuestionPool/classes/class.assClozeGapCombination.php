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

class assClozeGapCombination
{
    public function __construct(
        private readonly ilDBInterface $db
    ) {

    }

    public function loadFromDb(int $question_id): array
    {
        $result = $this->db->queryF(
            'SELECT combinations.combination_id,
                    combinations.gap_fi,
                    combinations.answer,
                    combinations.row_id,
                    combinations.points,
                    combinations.best_solution,
                    combinations.question_fi,
                    cloze.cloze_type
            FROM 	qpl_a_cloze_combi_res AS combinations
            INNER JOIN qpl_a_cloze AS cloze
                            WHERE combinations.question_fi = cloze.question_fi
                            AND combinations.gap_fi = cloze.gap_id
                            AND combinations.question_fi = %s
            ORDER BY combination_id, row_id, gap_fi ASC
            ',
            ['integer'],
            [$question_id]
        );

        $return_array = [];
        while ($data = $this->db->fetchAssoc($result)) {
            if (isset($return_array[$data['combination_id'] . '::' . $data['gap_fi']])) {
                continue;
            }

            $return_array[$data['combination_id'] . '::' . $data['row_id'] . '::' . $data['gap_fi']] = [
                                    'cid' => $data['combination_id'],
                                    'gap_fi' => $data['gap_fi'],
                                    'answer' => $data['answer'],
                                    'points' => $data['points'],
                                    'row_id' => $data['row_id'],
                                    'type' => $data['cloze_type'],
                                    'best_solution' => $data['best_solution']
                                 ];
        }

        return array_values($return_array);
    }

    public function getCleanCombinationArray(int $question_id): array
    {
        $combination_from_db = $this->loadFromDb($question_id);
        $clean_array = [];
        foreach ($combination_from_db as $key => $value) {
            $clean_array[$value['cid']][$value['row_id']][$value['gap_fi']]['answer'] = $value['answer'];
            $clean_array[$value['cid']][$value['row_id']]['points'] = $value['points'];
            $clean_array[$value['cid']][$value['row_id']][$value['gap_fi']]['type'] = $value['type'];
        }
        return $clean_array;
    }

    public function saveGapCombinationToDb(
        int $question_id,
        array $gap_combinations,
        array $gap_values
    ): void {
        $best_solutions = [];
        for ($i = 0; $i < count($gap_combinations['points']); $i++) {
            $highest_points = 0;
            for ($j = 0; $j < count($gap_combinations['points'][$i]); $j++) {
                if ($highest_points < $gap_combinations['points'][$i][$j]) {
                    $highest_points = $gap_combinations['points'][$i][$j];
                    $best_solutions[$i] = $j;
                }
            }
        }
        for ($i = 0; $i < count($gap_values); $i++) {
            for ($j = 0; $j < count($gap_values[$i]); $j++) {
                for ($k = 0; $k < count($gap_values[$i][$j]); $k++) {
                    if ($best_solutions[$i] == $j) {
                        $best_solution = 1;
                    } else {
                        $best_solution = 0;
                    }
                    $this->db->manipulateF(
                        'INSERT INTO qpl_a_cloze_combi_res
			 				(combination_id, question_fi, gap_fi, row_id, answer, points, best_solution) VALUES (%s, %s, %s, %s, %s, %s, %s)',
                        [
                            'integer',
                            'integer',
                            'integer',
                            'integer',
                            'text',
                            'float',
                            'integer'
                        ],
                        [
                            $i,
                            $question_id,
                            $gap_combinations['select'][$i][$k],
                            $j,
                            $gap_values[$i][$j][$k],
                            (float) str_replace(',', '.', $gap_combinations['points'][$i][$j]),
                            $best_solution
                        ]
                    );
                }
            }
        }
    }
    public function importGapCombinationToDb(int $question_id, array $gap_combinations): void
    {
        foreach ($gap_combinations as $row) {
            if (is_object($row)) {
                $row = get_object_vars($row);
            }
            if ($question_id != -1) {
                $this->db->manipulateF(
                    'INSERT INTO qpl_a_cloze_combi_res
                        (combination_id, question_fi, gap_fi, row_id, answer, points, best_solution)
                        VALUES (%s, %s, %s, %s, %s, %s, %s)',
                    [
                        'integer',
                        'integer',
                        'integer',
                        'integer',
                        'text',
                        'float',
                        'integer'
                    ],
                    [
                        $row['cid'],
                        $question_id,
                        $row['gap_fi'],
                        $row['row_id'],
                        $row['answer'],
                        $row['points'],
                        $row['best_solution']
                    ]
                );
            }
        }
    }

    public function clearGapCombinationsFromDb($question_id): void
    {
        $this->db->manipulateF(
            'DELETE FROM qpl_a_cloze_combi_res WHERE question_fi = %s',
            [ 'integer' ],
            [ $question_id ]
        );
    }

    public function combinationExistsForQid($question_id): bool
    {
        $result = $this->db->queryF(
            'SELECT * FROM qpl_a_cloze_combi_res WHERE question_fi = %s ORDER BY gap_fi ASC',
            ['integer'],
            [$question_id]
        );
        if ($result->numRows() > 0) {
            return true;
        }
        return false;
    }

    public function getGapsWhichAreUsedInCombination($question_id): array
    {
        $result = $this->db->queryF(
            'SELECT gap_fi, combination_id FROM '
                . $this->db->quoteIdentifier('qpl_a_cloze_combi_res')
                . ' WHERE question_fi = %s GROUP BY gap_fi, combination_id',
            ['integer'],
            [$question_id]
        );
        $gaps = [];
        if ($result->numRows() > 0) {
            while ($data = $this->db->fetchAssoc($result)) {
                $gaps[$data['gap_fi']] = $data['combination_id'];
            }
        }
        return $gaps;
    }

    public function getMaxPointsForCombination(
        int $question_id,
        int $combination_id = -1
    ): float {
        $result = $this->fetchResult($question_id, $combination_id);

        $points = 0.0;
        while (($data = $this->db->fetchAssoc($result)) !== null) {
            $points += $data['points'];
        }
        return $points;
    }

    private function fetchResult(
        int $question_id,
        int $combination_id
    ): ilPDOStatement {
        if ($combination_id === -1) {
            return $this->db->queryF(
                'SELECT combination_id, points' . PHP_EOL
                . 'FROM qpl_a_cloze_combi_res' . PHP_EOL
                . 'WHERE question_fi = %s' . PHP_EOL
                . 'AND best_solution=1' . PHP_EOL
                . 'GROUP BY combination_id, points',
                ['integer'],
                [$question_id]
            );
        }
        return $this->db->queryF(
            'SELECT combination_id, points' . PHP_EOL
            . 'FROM qpl_a_cloze_combi_res' . PHP_EOL
            . 'WHERE question_fi = %s' . PHP_EOL
            . 'AND combination_id = %s' . PHP_EOL
            . 'AND best_solution=1' . PHP_EOL
            . 'GROUP BY combination_id, points',
            ['integer', 'integer'],
            [$question_id, $combination_id]
        );
    }
}
