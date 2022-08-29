<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

/**
 * Class ilUnitConfigurationRepository
 */
class ilUnitConfigurationRepository
{
    protected int $consumer_id = 0;
    protected ilLanguage $lng;
    protected ilDBInterface $db;
    /** @var assFormulaQuestionUnit[] */
    private array $units = [];
    /** @var assFormulaQuestionUnit[]|assFormulaQuestionUnitCategory[]  */
    private array $categorizedUnits = [];

    public function __construct(int $consumer_id)
    {
        global $DIC;

        $lng = $DIC->language();

        $this->db = $DIC->database();
        $this->consumer_id = $consumer_id;
        $this->lng = $lng;
    }

    public function setConsumerId(int $consumer_id): void
    {
        $this->consumer_id = $consumer_id;
    }

    public function getConsumerId(): int
    {
        return $this->consumer_id;
    }

    public function isCRUDAllowed(int $category_id): bool
    {
        $res = $this->db->queryF(
            'SELECT * FROM il_qpl_qst_fq_ucat WHERE category_id = %s',
            ['integer'],
            [$category_id]
        );
        $row = $this->db->fetchAssoc($res);
        return isset($row['question_fi']) && (int) $row['question_fi'] === $this->getConsumerId();
    }

    public function copyCategory(int $category_id, int $question_fi, ?string $category_name = null): int
    {
        $res = $this->db->queryF(
            'SELECT category FROM il_qpl_qst_fq_ucat WHERE category_id = %s',
            ['integer'],
            [$category_id]
        );
        $row = $this->db->fetchAssoc($res);

        if (null === $category_name) {
            $category_name = $row['category'];
        }

        $next_id = $this->db->nextId('il_qpl_qst_fq_ucat');
        $this->db->insert(
            'il_qpl_qst_fq_ucat',
            [
                'category_id' => ['integer', $next_id],
                'category' => ['text', $category_name],
                'question_fi' => ['integer', (int) $question_fi]
            ]
        );

        return $next_id;
    }

    public function copyUnitsByCategories(int $from_category_id, int $to_category_id, int $qustion_fi): void
    {
        $res = $this->db->queryF(
            'SELECT * FROM il_qpl_qst_fq_unit WHERE category_fi = %s',
            ['integer'],
            [$from_category_id]
        );
        $i = 0;
        $units = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $next_id = $this->db->nextId('il_qpl_qst_fq_unit');

            $units[$i]['old_unit_id'] = $row['unit_id'];
            $units[$i]['new_unit_id'] = $next_id;

            $this->db->insert(
                'il_qpl_qst_fq_unit',
                [
                    'unit_id' => ['integer', $next_id],
                    'unit' => ['text', $row['unit']],
                    'factor' => ['float', $row['factor']],
                    'baseunit_fi' => ['integer', (int) $row['baseunit_fi']],
                    'category_fi' => ['integer', (int) $to_category_id],
                    'sequence' => ['integer', (int) $row['sequence']],
                    'question_fi' => ['integer', (int) $qustion_fi]
                ]
            );
            $i++;
        }

        foreach ($units as $unit) {
            //update unit : baseunit_fi
            $this->db->update(
                'il_qpl_qst_fq_unit',
                ['baseunit_fi' => ['integer', (int) $unit['new_unit_id']]],
                [
                    'baseunit_fi' => ['integer', $unit['old_unit_id']],
                    'category_fi' => ['integer', $to_category_id]
                ]
            );

            //update var : unit_fi
            $this->db->update(
                'il_qpl_qst_fq_var',
                ['unit_fi' => ['integer', (int) $unit['new_unit_id']]],
                [
                    'unit_fi' => ['integer', $unit['old_unit_id']],
                    'question_fi' => ['integer', $qustion_fi]
                ]
            );

            //update res : unit_fi
            $this->db->update(
                'il_qpl_qst_fq_res',
                ['unit_fi' => ['integer', (int) $unit['new_unit_id']]],
                [
                    'unit_fi' => ['integer', $unit['old_unit_id']],
                    'question_fi' => ['integer', $qustion_fi]
                ]
            );

            //update res_unit : unit_fi
            $this->db->update(
                'il_qpl_qst_fq_res_unit',
                ['unit_fi' => ['integer', (int) $unit['new_unit_id']]],
                [
                    'unit_fi' => ['integer', $unit['old_unit_id']],
                    'question_fi' => ['integer', $qustion_fi]
                ]
            );
        }
    }

    public function getCategoryUnitCount(int $id): int
    {
        $result = $this->db->queryF(
            "SELECT * FROM il_qpl_qst_fq_unit WHERE category_fi = %s",
            ['integer'],
            [$id]
        );

        return $this->db->numRows($result);
    }

    public function isUnitInUse(int $id): bool
    {
        $result_1 = $this->db->queryF(
            "SELECT unit_fi FROM il_qpl_qst_fq_res_unit WHERE unit_fi = %s",
            ['integer'],
            [$id]
        );

        $result_2 = $this->db->queryF(
            "SELECT unit_fi FROM il_qpl_qst_fq_var WHERE unit_fi = %s",
            ['integer'],
            [$id]
        );
        $result_3 = $this->db->queryF(
            "SELECT unit_fi FROM il_qpl_qst_fq_res WHERE unit_fi = %s",
            ['integer'],
            [$id]
        );

        $cnt_1 = $this->db->numRows($result_1);
        $cnt_2 = $this->db->numRows($result_2);
        $cnt_3 = $this->db->numRows($result_3);

        return $cnt_1 > 0 || $cnt_2 > 0 || $cnt_3 > 0;
    }

    public function checkDeleteCategory(int $id): ?string
    {
        $res = $this->db->queryF(
            'SELECT unit_id FROM il_qpl_qst_fq_unit WHERE category_fi = %s',
            ['integer'],
            [$id]
        );

        if ($this->db->numRows($res)) {
            while ($row = $this->db->fetchAssoc($res)) {
                $unit_res = $this->checkDeleteUnit((int) $row['unit_id'], $id);
                if (!is_null($unit_res)) {
                    return $unit_res;
                }
            }
        }

        return null;
    }

    public function deleteUnit(int $id): ?string
    {
        $res = $this->checkDeleteUnit($id);
        if (!is_null($res)) {
            return $res;
        }

        $affectedRows = $this->db->manipulateF(
            "DELETE FROM il_qpl_qst_fq_unit WHERE unit_id = %s",
            ['integer'],
            [$id]
        );

        if ($affectedRows > 0) {
            $this->clearUnits();
        }

        return null;
    }

    protected function loadUnits(): void
    {
        $result = $this->db->query(
            "
			SELECT units.*, il_qpl_qst_fq_ucat.category, baseunits.unit baseunit_title
			FROM il_qpl_qst_fq_unit units
			INNER JOIN il_qpl_qst_fq_ucat ON il_qpl_qst_fq_ucat.category_id = units.category_fi
			LEFT JOIN il_qpl_qst_fq_unit baseunits ON baseunits.unit_id = units.baseunit_fi
			ORDER BY il_qpl_qst_fq_ucat.category, units.sequence"
        );

        if ($this->db->numRows($result)) {
            while ($row = $this->db->fetchAssoc($result)) {
                $unit = new assFormulaQuestionUnit();
                $unit->initFormArray($row);
                $this->addUnit($unit);
            }
        }
    }

    /**
     * @return assFormulaQuestionUnit[]|assFormulaQuestionUnitCategory[]
     */
    public function getCategorizedUnits(): array
    {
        if (count($this->categorizedUnits) === 0) {
            $result = $this->db->queryF(
                "
				SELECT	units.*, il_qpl_qst_fq_ucat.category, il_qpl_qst_fq_ucat.question_fi, baseunits.unit baseunit_title
				FROM	il_qpl_qst_fq_unit units
				INNER JOIN il_qpl_qst_fq_ucat ON il_qpl_qst_fq_ucat.category_id = units.category_fi
				LEFT JOIN il_qpl_qst_fq_unit baseunits ON baseunits.unit_id = units.baseunit_fi
				WHERE	units.question_fi = %s
				ORDER BY il_qpl_qst_fq_ucat.category, units.sequence",
                ['integer'],
                [$this->getConsumerId()]
            );

            if ($this->db->numRows($result) > 0) {
                $category = 0;
                while ($row = $this->db->fetchAssoc($result)) {
                    $unit = new assFormulaQuestionUnit();
                    $unit->initFormArray($row);

                    if ($category !== $unit->getCategory()) {
                        $cat = new assFormulaQuestionUnitCategory();
                        $cat->initFormArray([
                            'category_id' => (int) $row['category_fi'],
                            'category' => $row['category'],
                            'question_fi' => (int) $row['question_fi'],
                        ]);
                        $this->categorizedUnits[] = $cat;
                        $category = $unit->getCategory();
                    }

                    $this->categorizedUnits[] = $unit;
                }
            }
        }

        return $this->categorizedUnits;
    }

    protected function clearUnits(): void
    {
        $this->units = [];
    }

    protected function addUnit(assFormulaQuestionUnit $unit): void
    {
        $this->units[$unit->getId()] = $unit;
    }

    /**
     * @return assFormulaQuestionUnit[]
     */
    public function getUnits(): array
    {
        if (count($this->units) === 0) {
            $this->loadUnits();
        }
        return $this->units;
    }

    /**
     * @param int $category
     * @return assFormulaQuestionUnit[]
     */
    public function loadUnitsForCategory(int $category): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $units = [];
        $result = $ilDB->queryF(
            "SELECT units.*, baseunits.unit baseunit_title, il_qpl_qst_fq_ucat.category
			FROM il_qpl_qst_fq_unit units
			INNER JOIN il_qpl_qst_fq_ucat ON il_qpl_qst_fq_ucat.category_id = units.category_fi  
			LEFT JOIN il_qpl_qst_fq_unit baseunits ON baseunits.unit_id = units.baseunit_fi
			WHERE il_qpl_qst_fq_ucat.category_id = %s 
			ORDER BY units.sequence",
            ['integer'],
            [$category]
        );

        if ($result->numRows() > 0) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $unit = new assFormulaQuestionUnit();
                $unit->initFormArray($row);
                $units[] = $unit;
            }
        }

        return $units;
    }

    /**
     * @param int $id
     * @return assFormulaQuestionUnit|null
     */
    public function getUnit(int $id): ?assFormulaQuestionUnit
    {
        if (count($this->units) === 0) {
            $this->loadUnits();
        }

        if (array_key_exists($id, $this->units)) {
            return $this->units[$id];
        }

        // Maybe this is a new unit, reload $this->units

        $this->loadUnits();

        return $this->units[$id] ?? null;
    }

    /**
     * @return array<int, array{value: int, text: string, qst_id: int}>
     */
    public function getUnitCategories(): array
    {
        $categories = [];
        $result = $this->db->queryF(
            "SELECT * FROM il_qpl_qst_fq_ucat WHERE question_fi > %s ORDER BY category",
            ['integer'],
            [0]
        );

        if ($this->db->numRows($result)) {
            while ($row = $this->db->fetchAssoc($result)) {
                $value = strcmp('-qpl_qst_formulaquestion_' . $row['category'] . '-', $this->lng->txt($row['category'])) === 0
                    ? $row['category']
                    : $this->lng->txt($row['category']);

                if (trim($row['category']) !== '') {
                    $cat = [
                        'value' => (int) $row['category_id'],
                        'text' => $value,
                        'qst_id' => (int) $row['question_fi']
                    ];
                    $categories[(int) $row['category_id']] = $cat;
                }
            }
        }

        return $categories;
    }

    /**
     * @return array<int, array{value: int, text: string, qst_id: int}>
     */
    public function getAdminUnitCategories(): array
    {
        $categories = [];

        $result = $this->db->queryF(
            "SELECT * FROM il_qpl_qst_fq_ucat WHERE question_fi = %s  ORDER BY category",
            ['integer'],
            [0]
        );

        if ($result = $this->db->numRows($result)) {
            while ($row = $this->db->fetchAssoc($result)) {
                $value = strcmp('-qpl_qst_formulaquestion_' . $row['category'] . '-', $this->lng->txt($row['category'])) === 0
                    ? $row['category']
                    : $this->lng->txt($row['category']);

                if (trim($row['category']) !== '') {
                    $cat = [
                        'value' => (int) $row['category_id'],
                        'text' => $value,
                        'qst_id' => (int) $row['question_fi']
                    ];
                    $categories[(int) $row['category_id']] = $cat;
                }
            }
        }

        return $categories;
    }

    public function saveUnitOrder(int $unit_id, int $sequence): void
    {
        $this->db->manipulateF(
            'UPDATE il_qpl_qst_fq_unit SET sequence = %s WHERE unit_id = %s AND question_fi = %s',
            ['integer', 'integer', 'integer'],
            [$sequence, $unit_id, $this->getConsumerId()]
        );
    }

    public function checkDeleteUnit(int $id, ?int $category_id = null): ?string
    {
        $result = $this->db->queryF(
            "SELECT * FROM il_qpl_qst_fq_var WHERE unit_fi = %s",
            ['integer'],
            [$id]
        );
        if ($this->db->numRows($result) > 0) {
            return $this->lng->txt("err_unit_in_variables");
        }

        $result = $this->db->queryF(
            "SELECT * FROM il_qpl_qst_fq_res WHERE unit_fi = %s",
            ['integer'],
            [$id]
        );
        if ($this->db->numRows($result) > 0) {
            return $this->lng->txt("err_unit_in_results");
        }

        if (!is_null($category_id)) {
            $result = $this->db->queryF(
                "SELECT * FROM il_qpl_qst_fq_unit WHERE baseunit_fi = %s AND category_fi != %s",
                ['integer', 'integer', 'integer'],
                [$id, $id, $category_id]
            );
        } else {
            $result = $this->db->queryF(
                "SELECT * FROM il_qpl_qst_fq_unit WHERE baseunit_fi = %s AND unit_id != %s",
                ['integer', 'integer'],
                [$id, $id]
            );
        }

        if ($this->db->numRows($result) > 0) {
            return $this->lng->txt("err_unit_is_baseunit");
        }

        return null;
    }

    public function getUnitCategoryById(int $id): assFormulaQuestionUnitCategory
    {
        $query = 'SELECT * FROM il_qpl_qst_fq_ucat WHERE category_id = ' . $this->db->quote($id, 'integer');
        $res = $this->db->query($query);
        if (!$this->db->numRows($res)) {
            throw new ilException('un_category_not_exist');
        }

        $row = $this->db->fetchAssoc($res);
        $category = new assFormulaQuestionUnitCategory();
        $category->initFormArray($row);
        return $category;
    }

    public function saveCategory(assFormulaQuestionUnitCategory $category): void
    {
        $res = $this->db->queryF(
            'SELECT * FROM il_qpl_qst_fq_ucat WHERE category = %s AND question_fi = %s AND category_id != %s',
            ['text', 'integer', 'integer'],
            [$category->getCategory(), $this->getConsumerId(), $category->getId()]
        );
        if ($this->db->numRows($res)) {
            throw new ilException('err_wrong_categoryname');
        }

        $this->db->manipulateF(
            'UPDATE il_qpl_qst_fq_ucat SET category = %s WHERE question_fi = %s AND category_id = %s',
            ['text', 'integer', 'integer'],
            [$category->getCategory(), $this->getConsumerId(), $category->getId()]
        );
    }

    public function saveNewUnitCategory(assFormulaQuestionUnitCategory $category): void
    {
        $res = $this->db->queryF(
            'SELECT category FROM il_qpl_qst_fq_ucat WHERE category = %s AND question_fi = %s',
            ['text', 'integer'],
            [$category->getCategory(), $this->getConsumerId()]
        );
        if ($this->db->numRows($res)) {
            throw new ilException('err_wrong_categoryname');
        }

        $next_id = $this->db->nextId('il_qpl_qst_fq_ucat');
        $this->db->manipulateF(
            "INSERT INTO il_qpl_qst_fq_ucat (category_id, category, question_fi) VALUES (%s, %s, %s)",
            ['integer', 'text', 'integer'],
            [
                $next_id,
                $category->getCategory(),
                $this->getConsumerId()
            ]
        );
        $category->setId($next_id);
    }

    /**
     * @return assFormulaQuestionUnitCategory[]
     */
    public function getAllUnitCategories(): array
    {
        $categories = [];
        $result = $this->db->queryF(
            "SELECT * FROM il_qpl_qst_fq_ucat WHERE question_fi = %s OR question_fi = %s ORDER BY category",
            ['integer', 'integer'],
            [$this->getConsumerId(), 0]
        );

        if ($result->numRows() > 0) {
            while ($row = $this->db->fetchAssoc($result)) {
                $category = new assFormulaQuestionUnitCategory();
                $category->initFormArray($row);
                $categories[] = $category;
            }
        }
        return $categories;
    }

    public function deleteCategory(int $id): ?string
    {
        $res = $this->checkDeleteCategory($id);
        if (!is_null($res)) {
            return $this->lng->txt('err_category_in_use');
        }

        $res = $this->db->queryF(
            'SELECT * FROM il_qpl_qst_fq_unit WHERE category_fi = %s',
            ['integer'],
            [$id]
        );
        while ($row = $this->db->fetchAssoc($res)) {
            $this->deleteUnit((int) $row['unit_id']);
        }

        $ar = $this->db->manipulateF(
            'DELETE FROM il_qpl_qst_fq_ucat WHERE category_id = %s',
            ['integer'],
            [$id]
        );

        if ($ar > 0) {
            $this->clearUnits();
        }

        return null;
    }

    public function createNewUnit(assFormulaQuestionUnit $unit): void
    {
        $next_id = $this->db->nextId('il_qpl_qst_fq_unit');
        $this->db->manipulateF(
            'INSERT INTO il_qpl_qst_fq_unit (unit_id, unit, factor, baseunit_fi, category_fi, sequence, question_fi) VALUES (%s, %s, %s, %s, %s, %s, %s)',
            ['integer', 'text', 'float', 'integer', 'integer', 'integer', 'integer'],
            [
                $next_id,
                $unit->getUnit(),
                1,
                0,
                $unit->getCategory(),
                0,
                $this->getConsumerId()
            ]
        );
        $unit->setId($next_id);
        $unit->setFactor(1.0);
        $unit->setBaseUnit(0);
        $unit->setSequence(0);

        $this->clearUnits();
    }

    public function saveUnit(assFormulaQuestionUnit $unit): void
    {
        $res = $this->db->queryF(
            'SELECT unit_id FROM il_qpl_qst_fq_unit WHERE unit_id = %s',
            ['integer'],
            [$unit->getId()]
        );
        if ($this->db->numRows($res)) {
            $row = $this->db->fetchAssoc($res);

            if ($unit->getBaseUnit() === 0 || $unit->getBaseUnit() === $unit->getId()) {
                $unit->setFactor(1);
            }

            $ar = $this->db->manipulateF(
                'UPDATE il_qpl_qst_fq_unit SET unit = %s, factor = %s, baseunit_fi = %s, category_fi = %s, sequence = %s WHERE unit_id = %s AND question_fi = %s',
                ['text', 'float', 'integer', 'integer', 'integer', 'integer', 'integer'],
                [
                    $unit->getUnit(), $unit->getFactor(), (int) $unit->getBaseUnit(),
                    $unit->getCategory(),
                    $unit->getSequence(),
                    $unit->getId(),
                    $this->getConsumerId()
                ]
            );
            if ($ar > 0) {
                $this->clearUnits();
            }
        }
    }

    public function cloneUnits(int $from_consumer_id, int $to_consumer_id): void
    {
        $category_mapping = [];

        $res = $this->db->queryF("SELECT * FROM il_qpl_qst_fq_ucat WHERE question_fi = %s", ['integer'], [$from_consumer_id]);
        while ($row = $this->db->fetchAssoc($res)) {
            $new_category_id = $this->copyCategory($row['category_id'], $to_consumer_id);
            $category_mapping[$row['category_id']] = $new_category_id;
        }

        foreach ($category_mapping as $old_category_id => $new_category_id) {
            $res = $this->db->queryF(
                'SELECT * FROM il_qpl_qst_fq_unit WHERE category_fi = %s',
                ['integer'],
                [$old_category_id]
            );

            $i = 0;
            $units = [];
            while ($row = $this->db->fetchAssoc($res)) {
                $next_id = $this->db->nextId('il_qpl_qst_fq_unit');

                $units[$i]['old_unit_id'] = $row['unit_id'];
                $units[$i]['new_unit_id'] = $next_id;

                $this->db->insert(
                    'il_qpl_qst_fq_unit',
                    [
                        'unit_id' => ['integer', $next_id],
                        'unit' => ['text', $row['unit']],
                        'factor' => ['float', $row['factor']],
                        'baseunit_fi' => ['integer', (int) $row['baseunit_fi']],
                        'category_fi' => ['integer', (int) $new_category_id],
                        'sequence' => ['integer', (int) $row['sequence']],
                        'question_fi' => ['integer', $to_consumer_id]
                    ]
                );
                $i++;
            }

            foreach ($units as $unit) {
                //update unit : baseunit_fi
                $this->db->update(
                    'il_qpl_qst_fq_unit',
                    ['baseunit_fi' => ['integer', (int) $unit['new_unit_id']]],
                    [
                        'baseunit_fi' => ['integer', (int) $unit['old_unit_id']],
                        'question_fi' => ['integer', $to_consumer_id]
                    ]
                );

                //update var : unit_fi
                $this->db->update(
                    'il_qpl_qst_fq_var',
                    ['unit_fi' => ['integer', (int) $unit['new_unit_id']]],
                    [
                        'unit_fi' => ['integer', (int) $unit['old_unit_id']],
                        'question_fi' => ['integer', $to_consumer_id]
                    ]
                );

                //update res : unit_fi
                $this->db->update(
                    'il_qpl_qst_fq_res',
                    ['unit_fi' => ['integer', (int) $unit['new_unit_id']]],
                    [
                        'unit_fi' => ['integer', (int) $unit['old_unit_id']],
                        'question_fi' => ['integer', $to_consumer_id]
                    ]
                );

                //update res_unit : unit_fi
                $this->db->update(
                    'il_qpl_qst_fq_res_unit',
                    ['unit_fi' => ['integer', (int) $unit['new_unit_id']]],
                    [
                        'unit_fi' => ['integer', (int) $unit['old_unit_id']],
                        'question_fi' => ['integer', $to_consumer_id]
                    ]
                );
            }
        }
    }
}
