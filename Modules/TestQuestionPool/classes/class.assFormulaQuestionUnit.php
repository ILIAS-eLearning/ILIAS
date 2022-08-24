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
 * Formula Question Unit
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @ingroup ModulesTestQuestionPool
 */
class assFormulaQuestionUnit
{
    private int $id = 0;
    private string $unit = '';
    private float $factor = 0.0;
    private int $category = 0;
    private int $sequence = 0;
    private int $baseunit = 0;
    private ?string $baseunit_title = null;

    public function initFormArray(array $data): void
    {
        $this->id = (int) $data['unit_id'];
        $this->unit = $data['unit'];
        $this->factor = (float) $data['factor'];
        $this->baseunit = (int) $data['baseunit_fi'];
        $this->baseunit_title = $data['baseunit_title'] ?? null;
        $this->category = (int) $data['category'];
        $this->sequence = (int) $data['sequence'];
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setUnit(string $unit): void
    {
        $this->unit = $unit;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setSequence(int $sequence): void
    {
        $this->sequence = $sequence;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setFactor(float $factor): void
    {
        $this->factor = $factor;
    }

    public function getFactor(): float
    {
        return $this->factor;
    }

    public function setBaseUnit(int $baseunit): void
    {
        $this->baseunit = $baseunit;
    }

    public function getBaseUnit(): int
    {
        if ($this->baseunit > 0) {
            return $this->baseunit;
        }

        return $this->id;
    }

    public function setBaseunitTitle(?string $baseunit_title): void
    {
        $this->baseunit_title = $baseunit_title;
    }

    public function getBaseunitTitle(): ?string
    {
        return $this->baseunit_title;
    }

    public function setCategory(int $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): int
    {
        return $this->category;
    }

    public function getDisplayString(): string
    {
        global $DIC;

        $lng = $DIC->language();

        $unit = $this->getUnit();
        if (strcmp('-qpl_qst_formulaquestion_' . $unit . '-', $lng->txt('qpl_qst_formulaquestion_' . $unit)) !== 0) {
            $unit = $lng->txt('qpl_qst_formulaquestion_' . $unit);
        }

        return $unit;
    }

    public static function lookupUnitFactor(int $a_unit_id): float
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT factor FROM il_qpl_qst_fq_unit WHERE unit_id = %s',
            ['integer'],
            [$a_unit_id]
        );

        $row = $ilDB->fetchAssoc($res);

        return (float) $row['factor'];
    }
}
