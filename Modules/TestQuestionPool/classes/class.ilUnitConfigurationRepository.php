<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/class.assFormulaQuestionUnit.php";
include_once "./Modules/TestQuestionPool/classes/class.assFormulaQuestionUnitCategory.php";

/**
 * Class ilUnitConfigurationRepository
 */
class ilUnitConfigurationRepository
{
    /**
     * @var int
     */
    protected $consumer_id = 0;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var
     */
    private $units = array();

    /**
     * @var array
     */
    private $categorizedUnits = array();

    /**
     * @param $consumer_id
     */
    public function __construct($consumer_id)
    {
        /**
         * @var $lng ilLanguage
         */
        global $DIC;
        $lng = $DIC['lng'];

        $this->consumer_id = $consumer_id;
        $this->lng = $lng;
    }

    /**
     * @param int $context_id
     */
    public function setConsumerId($consumer_id)
    {
        $this->consumer_id = $consumer_id;
    }

    /**
     * @return int
     */
    public function getConsumerId()
    {
        return $this->consumer_id;
    }

    /**
     * @param int $a_category_id
     * @return bool
     */
    public function isCRUDAllowed($a_category_id)
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT * FROM il_qpl_qst_fq_ucat WHERE category_id = %s',
            array('integer'),
            array($a_category_id)
        );
        $row = $ilDB->fetchAssoc($res);
        return isset($row['question_fi']) && $row['question_fi'] == $this->getConsumerId();
    }

    /**
     * @param  int    $a_category_id  copy-source
     * @param  int    $a_question_fi  copy-target
     * @param  string $a_category_name
     * @return int
     */
    public function copyCategory($a_category_id, $a_question_fi, $a_category_name = null)
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT category FROM il_qpl_qst_fq_ucat WHERE category_id = %s',
            array('integer'),
            array($a_category_id)
        );
        $row = $ilDB->fetchAssoc($res);

        if (null === $a_category_name) {
            $a_category_name = $row['category'];
        }

        $next_id = $ilDB->nextId('il_qpl_qst_fq_ucat');
        $ilDB->insert(
            'il_qpl_qst_fq_ucat',
            array(
                'category_id' => array('integer', $next_id),
                'category' => array('text', $a_category_name),
                'question_fi' => array('integer', (int) $a_question_fi)
            )
        );

        return $next_id;
    }

    /**
     * @param int $a_from_category_id
     * @param int $a_to_category_id
     * @param int $a_question_fi
     */
    public function copyUnitsByCategories($a_from_category_id, $a_to_category_id, $a_question_fi)
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT * FROM il_qpl_qst_fq_unit WHERE category_fi = %s',
            array('integer'),
            array($a_from_category_id)
        );
        $i = 0;
        $units = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $next_id = $ilDB->nextId('il_qpl_qst_fq_unit');

            $units[$i]['old_unit_id'] = $row['unit_id'];
            $units[$i]['new_unit_id'] = $next_id;

            $ilDB->insert(
                'il_qpl_qst_fq_unit',
                array(
                    'unit_id' => array('integer', $next_id),
                    'unit' => array('text', $row['unit']),
                    'factor' => array('float', $row['factor']),
                    'baseunit_fi' => array('integer', (int) $row['baseunit_fi']),
                    'category_fi' => array('integer', (int) $a_to_category_id),
                    'sequence' => array('integer', (int) $row['sequence']),
                    'question_fi' => array('integer', (int) $a_question_fi)
                )
            );
            $i++;
        }

        foreach ($units as $unit) {
            //update unit : baseunit_fi
            $ilDB->update(
                'il_qpl_qst_fq_unit',
                array('baseunit_fi' => array('integer', (int) $unit['new_unit_id'])),
                array(
                    'baseunit_fi' => array('integer', $unit['old_unit_id']),
                    'category_fi' => array('integer', $a_to_category_id)
                )
            );

            //update var : unit_fi
            $ilDB->update(
                'il_qpl_qst_fq_var',
                array('unit_fi' => array('integer', (int) $unit['new_unit_id'])),
                array(
                    'unit_fi' => array('integer', $unit['old_unit_id']),
                    'question_fi' => array('integer', $a_question_fi)
                )
            );

            //update res : unit_fi
            $ilDB->update(
                'il_qpl_qst_fq_res',
                array('unit_fi' => array('integer', (int) $unit['new_unit_id'])),
                array(
                    'unit_fi' => array('integer', $unit['old_unit_id']),
                    'question_fi' => array('integer', $a_question_fi)
                )
            );

            //update res_unit : unit_fi
            $ilDB->update(
                'il_qpl_qst_fq_res_unit',
                array('unit_fi' => array('integer', (int) $unit['new_unit_id'])),
                array(
                    'unit_fi' => array('integer', $unit['old_unit_id']),
                    'question_fi' => array('integer', $a_question_fi)
                )
            );
        }
    }

    public function getCategoryUnitCount($id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT * FROM il_qpl_qst_fq_unit WHERE category_fi = %s",
            array('integer'),
            array($id)
        );
        return $result->numRows();
    }

    public function isUnitInUse($id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result_1 = $ilDB->queryF(
            "SELECT unit_fi FROM il_qpl_qst_fq_res_unit WHERE unit_fi = %s",
            array('integer'),
            array($id)
        );

        $result_2 = $ilDB->queryF(
            "SELECT unit_fi FROM il_qpl_qst_fq_var WHERE unit_fi = %s",
            array('integer'),
            array($id)
        );
        $result_3 = $ilDB->queryF(
            "SELECT unit_fi FROM il_qpl_qst_fq_res WHERE unit_fi = %s",
            array('integer'),
            array($id)
        );

        $cnt_1 = $ilDB->numRows($result_1);
        $cnt_2 = $ilDB->numRows($result_2);
        $cnt_3 = $ilDB->numRows($result_3);

        if ($cnt_1 || $cnt_2 || $cnt_3) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $id
     * @return null|string
     */
    public function checkDeleteCategory($id)
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT unit_id FROM il_qpl_qst_fq_unit WHERE category_fi = %s',
            array('integer'),
            array($id)
        );
        if ($ilDB->numRows($res)) {
            while ($row = $ilDB->fetchAssoc($res)) {
                $unit_res = $this->checkDeleteUnit($row['unit_id'], $id);
                if (!is_null($unit_res)) {
                    return $unit_res;
                }
            }
        }
        return null;
    }

    public function deleteUnit($id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $this->checkDeleteUnit($id);
        if (!is_null($res)) {
            return $res;
        }
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM il_qpl_qst_fq_unit WHERE unit_id = %s",
            array('integer'),
            array($id)
        );
        if ($affectedRows > 0) {
            $this->clearUnits();
        }
        return null;
    }

    protected function loadUnits()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query(
            "
			SELECT units.*, il_qpl_qst_fq_ucat.category, baseunits.unit baseunit_title
			FROM il_qpl_qst_fq_unit units
			INNER JOIN il_qpl_qst_fq_ucat ON il_qpl_qst_fq_ucat.category_id = units.category_fi
			LEFT JOIN il_qpl_qst_fq_unit baseunits ON baseunits.unit_id = units.baseunit_fi
			ORDER BY il_qpl_qst_fq_ucat.category, units.sequence"
        );

        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $unit = new assFormulaQuestionUnit();
                $unit->initFormArray($row);
                $this->addUnit($unit);
            }
        }
    }

    public function getCategorizedUnits()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (count($this->categorizedUnits) == 0) {
            $result = $ilDB->queryF(
                "
				SELECT	units.*, il_qpl_qst_fq_ucat.category, il_qpl_qst_fq_ucat.question_fi, baseunits.unit baseunit_title
				FROM	il_qpl_qst_fq_unit units
				INNER JOIN il_qpl_qst_fq_ucat ON il_qpl_qst_fq_ucat.category_id = units.category_fi
				LEFT JOIN il_qpl_qst_fq_unit baseunits ON baseunits.unit_id = units.baseunit_fi
				WHERE	units.question_fi = %s
				ORDER BY il_qpl_qst_fq_ucat.category, units.sequence",
                array('integer'),
                array($this->getConsumerId())
            );

            if ($result->numRows()) {
                $category = '';
                while ($row = $ilDB->fetchAssoc($result)) {
                    $unit = new assFormulaQuestionUnit();
                    $unit->initFormArray($row);
                    if (strcmp($category, $unit->getCategory()) != 0) {
                        $cat = new assFormulaQuestionUnitCategory();
                        $cat->initFormArray(array(
                            'category_id' => $row['category_fi'],
                            'category' => $row['category'],
                            'question_fi' => $row['question_fi'],
                        ));
                        array_push($this->categorizedUnits, $cat);
                        $category = $unit->getCategory();
                    }
                    array_push($this->categorizedUnits, $unit);
                }
            }
        }

        return $this->categorizedUnits;
    }

    protected function clearUnits()
    {
        $this->units = array();
    }

    protected function addUnit($unit)
    {
        $this->units[$unit->getId()] = $unit;
    }

    public function getUnits()
    {
        if (count($this->units) == 0) {
            $this->loadUnits();
        }
        return $this->units;
    }

    public function loadUnitsForCategory($category)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $units = array();
        $result = $ilDB->queryF(
            "
			SELECT units.*, baseunits.unit baseunit_title
			FROM il_qpl_qst_fq_unit units
			INNER JOIN il_qpl_qst_fq_ucat ON il_qpl_qst_fq_ucat.category_id = units.category_fi  
			LEFT JOIN il_qpl_qst_fq_unit baseunits ON baseunits.unit_id = units.baseunit_fi
			WHERE il_qpl_qst_fq_ucat.category_id = %s 
			ORDER BY units.sequence",
            array('integer'),
            array($category)
        );
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $unit = new assFormulaQuestionUnit();
                $unit->initFormArray($row);
                array_push($units, $unit);
            }
        }
        return $units;
    }

    /**
     * @param int $id
     * @return assFormulaQuestionUnit
     */
    public function getUnit($id)
    {
        if (count($this->units) == 0) {
            $this->loadUnits();
        }
        if (array_key_exists($id, $this->units)) {
            return $this->units[$id];
        } else {
            //maybee this is a new unit ...
            // reload $this->units

            $this->loadUnits();
            if (array_key_exists($id, $this->units)) {
                return $this->units[$id];
            }
        }
        return null;
    }


    public function getUnitCategories()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $categories = array();
        $result = $ilDB->queryF(
            "SELECT * FROM il_qpl_qst_fq_ucat WHERE question_fi > %s ORDER BY category",
            array('integer'),
            array(0)
        );
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $value = (strcmp("-qpl_qst_formulaquestion_" . $row["category"] . "-", $this->lng->txt($row["category"])) == 0) ? $row["category"] : $this->lng->txt($row["category"]);

                if (strlen(trim($row["category"]))) {
                    $cat = array(
                        "value" => $row["category_id"],
                        "text" => $value,
                        "qst_id" => $row['question_fi']
                    );
                    $categories[$row["category_id"]] = $cat;
                }
            }
        }
        return $categories;
    }

    public function getAdminUnitCategories()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $categories = array();
        $result = $ilDB->queryF(
            "SELECT * FROM il_qpl_qst_fq_ucat WHERE question_fi = %s  ORDER BY category",
            array('integer'),
            array(0)
        );
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $value = (strcmp("-qpl_qst_formulaquestion_" . $row["category"] . "-", $this->lng->txt($row["category"])) == 0) ? $row["category"] : $this->lng->txt($row["category"]);

                if (strlen(trim($row["category"]))) {
                    $cat = array(
                        "value" => $row["category_id"],
                        "text" => $value,
                        "qst_id" => $row['question_fi']
                    );
                    $categories[$row["category_id"]] = $cat;
                }
            }
        }

        return $categories;
    }

    /**
     * @param integer $unit_id
     * @param integer $sequence
     */
    public function saveUnitOrder($unit_id, $sequence)
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
            '
			UPDATE il_qpl_qst_fq_unit
			SET sequence = %s
			WHERE unit_id = %s AND question_fi = %s
			',
            array('integer', 'integer', 'integer'),
            array((int) $sequence, $unit_id, $this->getConsumerId())
        );
    }

    public function checkDeleteUnit($id, $category_id = null)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT * FROM il_qpl_qst_fq_var WHERE unit_fi = %s",
            array('integer'),
            array($id)
        );
        if ($result->numRows() > 0) {
            return $this->lng->txt("err_unit_in_variables");
        }
        $result = $ilDB->queryF(
            "SELECT * FROM il_qpl_qst_fq_res WHERE unit_fi = %s",
            array('integer'),
            array($id)
        );
        if ($result->numRows() > 0) {
            return $this->lng->txt("err_unit_in_results");
        }
        if (!is_null($category_id)) {
            $result = $ilDB->queryF(
                "SELECT * FROM il_qpl_qst_fq_unit WHERE baseunit_fi = %s AND category_fi != %s",
                array('integer', 'integer', 'integer'),
                array($id, $id, $category_id)
            );
        } else {
            $result = $ilDB->queryF(
                "SELECT * FROM il_qpl_qst_fq_unit WHERE baseunit_fi = %s AND unit_id != %s",
                array('integer', 'integer'),
                array($id, $id)
            );
        }
        if ($result->numRows() > 0) {
            return $this->lng->txt("err_unit_is_baseunit");
        }
        return null;
    }

    /**
     * @param integer $id
     * @return assFormulaQuestionUnitCategory
     * @throws ilException
     */
    public function getUnitCategoryById($id)
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM il_qpl_qst_fq_ucat WHERE category_id = ' . $ilDB->quote($id, 'integer');
        $res = $ilDB->query($query);
        if (!$ilDB->numRows($res)) {
            throw new ilException('un_category_not_exist');
        }

        $row = $ilDB->fetchAssoc($res);
        $category = new assFormulaQuestionUnitCategory();
        $category->initFormArray($row);
        return $category;
    }

    /**
     * @param assFormulaQuestionUnitCategory $category
     * @throws ilException
     */
    public function saveCategory(assFormulaQuestionUnitCategory $category)
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT * FROM il_qpl_qst_fq_ucat WHERE category = %s AND question_fi = %s AND category_id != %s',
            array('text', 'integer', 'integer'),
            array($category->getCategory(), $this->getConsumerId(), $category->getId())
        );
        if ($ilDB->numRows($res)) {
            throw new ilException('err_wrong_categoryname');
        }

        $ilDB->manipulateF(
            'UPDATE il_qpl_qst_fq_ucat SET category = %s WHERE question_fi = %s AND category_id = %s',
            array('text', 'integer', 'integer'),
            array($category->getCategory(), $this->getConsumerId(), $category->getId())
        );
    }

    /**
     * @param assFormulaQuestionUnitCategory $category
     * @throws ilException
     */
    public function saveNewUnitCategory(assFormulaQuestionUnitCategory $category)
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT category FROM il_qpl_qst_fq_ucat WHERE category = %s AND question_fi = %s',
            array('text', 'integer'),
            array($category->getCategory(), $this->getConsumerId())
        );
        if ($ilDB->numRows($res)) {
            throw new ilException('err_wrong_categoryname');
        }

        $next_id = $ilDB->nextId('il_qpl_qst_fq_ucat');
        $ilDB->manipulateF(
            "INSERT INTO il_qpl_qst_fq_ucat (category_id, category, question_fi) VALUES (%s, %s, %s)",
            array('integer', 'text', 'integer'),
            array(
                $next_id,
                $category->getCategory(),
                (int) $this->getConsumerId()
            )
        );
        $category->setId($next_id);
    }

    /**
     * @return array
     */
    public function getAllUnitCategories()
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $categories = array();
        $result = $ilDB->queryF(
            "SELECT * FROM il_qpl_qst_fq_ucat WHERE question_fi = %s OR question_fi = %s ORDER BY category",
            array('integer', 'integer'),
            array($this->getConsumerId(), 0)
        );
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $category = new assFormulaQuestionUnitCategory();
                $category->initFormArray($row);
                $categories[] = $category;
            }
        }
        return $categories;
    }

    /**
     * @param $id
     * @return null|string
     */
    public function deleteCategory($id)
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $this->checkDeleteCategory($id);
        if (!is_null($res)) {
            return $this->lng->txt('err_category_in_use');
        }

        $res = $ilDB->queryF(
            'SELECT * FROM il_qpl_qst_fq_unit WHERE category_fi = %s',
            array('integer'),
            array($id)
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            $this->deleteUnit($row['unit_id']);
        }
        $ar = $ilDB->manipulateF(
            'DELETE FROM il_qpl_qst_fq_ucat WHERE category_id = %s',
            array('integer'),
            array($id)
        );
        if ($ar > 0) {
            $this->clearUnits();
        }
        return null;
    }

    /**
     * @param assFormulaQuestionUnit $unit
     */
    public function createNewUnit(assFormulaQuestionUnit $unit)
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $next_id = $ilDB->nextId('il_qpl_qst_fq_unit');
        $ilDB->manipulateF(
            'INSERT INTO il_qpl_qst_fq_unit (unit_id, unit, factor, baseunit_fi, category_fi, sequence, question_fi) VALUES (%s, %s, %s, %s, %s, %s, %s)',
            array('integer', 'text', 'float', 'integer', 'integer', 'integer', 'integer'),
            array(
                $next_id,
                $unit->getUnit(),
                1,
                0,
                (int) $unit->getCategory(),
                0,
                (int) $this->getConsumerId()
            )
        );
        $unit->setId($next_id);
        $unit->setFactor(1);
        $unit->setBaseUnit(0);
        $unit->setSequence(0);

        $this->clearUnits();
    }

    /**
     * @param assFormulaQuestionUnit $unit
     */
    public function saveUnit(assFormulaQuestionUnit $unit)
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT unit_id FROM il_qpl_qst_fq_unit WHERE unit_id = %s',
            array('integer'),
            array($unit->getId())
        );
        if ($ilDB->fetchAssoc($res)) {
            $row = $ilDB->fetchAssoc($res);
            $sequence = $row['sequence'];
            if (is_null($unit->getBaseUnit()) || !strlen($unit->getBaseUnit())) {
                $unit->setFactor(1);
            }
            $ar = $ilDB->manipulateF(
                'UPDATE il_qpl_qst_fq_unit SET unit = %s, factor = %s, baseunit_fi = %s, category_fi = %s, sequence = %s WHERE unit_id = %s AND question_fi = %s',
                array('text', 'float', 'integer', 'integer', 'integer', 'integer', 'integer'),
                array($unit->getUnit(), $unit->getFactor(), (int) $unit->getBaseUnit(), (int) $unit->getCategory(), (int) $unit->getSequence(), (int) $unit->getId(), (int) $this->getConsumerId())
            );
            if ($ar > 0) {
                $this->clearUnits();
            }
        }
    }

    /**
     * @param int $a_from_consumer_id
     * @param int $a_to_consumer_id
     */
    public function cloneUnits($a_from_consumer_id, $a_to_consumer_id)
    {
        /**
         * @var $ilDB ilDB
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $category_mapping = array();

        $res = $ilDB->queryF("SELECT * FROM il_qpl_qst_fq_ucat WHERE question_fi = %s", array('integer'), array($a_from_consumer_id));
        while ($row = $ilDB->fetchAssoc($res)) {
            $new_category_id = $this->copyCategory($row['category_id'], $a_to_consumer_id);
            $category_mapping[$row['category_id']] = $new_category_id;
        }

        foreach ($category_mapping as $old_category_id => $new_category_id) {
            $res = $ilDB->queryF(
                'SELECT * FROM il_qpl_qst_fq_unit WHERE category_fi = %s',
                array('integer'),
                array($old_category_id)
            );

            $i = 0;
            $units = array();
            while ($row = $ilDB->fetchAssoc($res)) {
                $next_id = $ilDB->nextId('il_qpl_qst_fq_unit');

                $units[$i]['old_unit_id'] = $row['unit_id'];
                $units[$i]['new_unit_id'] = $next_id;

                $ilDB->insert(
                    'il_qpl_qst_fq_unit',
                    array(
                        'unit_id' => array('integer', $next_id),
                        'unit' => array('text', $row['unit']),
                        'factor' => array('float', $row['factor']),
                        'baseunit_fi' => array('integer', (int) $row['baseunit_fi']),
                        'category_fi' => array('integer', (int) $new_category_id),
                        'sequence' => array('integer', (int) $row['sequence']),
                        'question_fi' => array('integer', (int) $a_to_consumer_id)
                    )
                );
                $i++;
            }

            foreach ($units as $unit) {
                //update unit : baseunit_fi
                $ilDB->update(
                    'il_qpl_qst_fq_unit',
                    array('baseunit_fi' => array('integer', (int) $unit['new_unit_id'])),
                    array(
                        'baseunit_fi' => array('integer', (int) $unit['old_unit_id']),
                        'question_fi' => array('integer', (int) $a_to_consumer_id)
                    )
                );

                //update var : unit_fi
                $ilDB->update(
                    'il_qpl_qst_fq_var',
                    array('unit_fi' => array('integer', (int) $unit['new_unit_id'])),
                    array(
                        'unit_fi' => array('integer', (int) $unit['old_unit_id']),
                        'question_fi' => array('integer', (int) $a_to_consumer_id)
                    )
                );

                //update res : unit_fi
                $ilDB->update(
                    'il_qpl_qst_fq_res',
                    array('unit_fi' => array('integer', (int) $unit['new_unit_id'])),
                    array(
                        'unit_fi' => array('integer', (int) $unit['old_unit_id']),
                        'question_fi' => array('integer', (int) $a_to_consumer_id)
                    )
                );

                //update res_unit : unit_fi
                $ilDB->update(
                    'il_qpl_qst_fq_res_unit',
                    array('unit_fi' => array('integer', (int) $unit['new_unit_id'])),
                    array(
                        'unit_fi' => array('integer', (int) $unit['old_unit_id']),
                        'question_fi' => array('integer', (int) $a_to_consumer_id)
                    )
                );
            }
        }
    }
}
