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

use ILIAS\Setup\Environment;

class ilDataCollectionSelectionFieldMigration implements \ILIAS\Setup\Migration
{
    public const DEFAULT_AMOUNT_OF_STEPS = 10000;

    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return "Migration of DataCollection selection field values";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return self::DEFAULT_AMOUNT_OF_STEPS;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective(),
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $stmt = $this->db->queryF(
            'SELECT il_dcl_stloc1_value.* FROM il_dcl_stloc1_value ' .
            'INNER JOIN il_dcl_record_field on il_dcl_record_field.id = il_dcl_stloc1_value.record_field_id ' .
            'INNER JOIN il_dcl_field ON il_dcl_field.id = il_dcl_record_field.field_id ' .
            'WHERE (datatype_id = %s OR datatype_id = %s) AND value LIKE %s AND value NOT LIKE %s AND value != "[]"',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [ilDclDatatype::INPUTFORMAT_TEXT_SELECTION, ilDclDatatype::INPUTFORMAT_DATE_SELECTION, '[%', '["%']
        );

        while ($row = $this->db->fetchAssoc($stmt)) {
            $old_values = json_decode($row['value']);
            $new_values = [];
            foreach ($old_values as $old_value) {
                $new_values[] = (string) $old_value;
            }
            $this->db->update(
                'il_dcl_stloc1_value',
                ['value' => [ilDBConstants::T_TEXT, json_encode($new_values)]],
                ['id' => [ilDBConstants::T_INTEGER, $row['id']]]
            );
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $legacy_file_field_amount = $this->db->fetchObject(
            $this->db->queryF(
                'SELECT COUNT(il_dcl_stloc1_value.id) AS amount FROM il_dcl_stloc1_value ' .
                'INNER JOIN il_dcl_record_field on il_dcl_record_field.id = il_dcl_stloc1_value.record_field_id ' .
                'INNER JOIN il_dcl_field ON il_dcl_field.id = il_dcl_record_field.field_id ' .
                'WHERE (datatype_id = %s OR datatype_id = %s) AND value LIKE %s AND value NOT LIKE %s AND value != "[]"',
                [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
                [ilDclDatatype::INPUTFORMAT_TEXT_SELECTION, ilDclDatatype::INPUTFORMAT_DATE_SELECTION, '[%', '["%']
            )
        );

        return (int) $legacy_file_field_amount?->amount;
    }
}
