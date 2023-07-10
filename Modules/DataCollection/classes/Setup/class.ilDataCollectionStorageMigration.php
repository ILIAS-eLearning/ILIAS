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

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilDataCollectionStorageMigration implements \ILIAS\Setup\Migration
{
    public const DEFAULT_AMOUNT_OF_STEPS = 10000;

    protected ilResourceStorageMigrationHelper $helper;

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return "Migration of DataCollection files to the Resource Storage Service.";
    }

    /**
     * @inheritDoc
     */
    public function getDefaultAmountOfStepsPerRun(): int
    {
        return self::DEFAULT_AMOUNT_OF_STEPS;
    }

    /**
     * @inheritDoc
     */
    public function getPreconditions(\ILIAS\Setup\Environment $environment): array
    {
        return ilResourceStorageMigrationHelper::getPreconditions();
    }

    /**
     * @inheritDoc
     */
    public function prepare(\ILIAS\Setup\Environment $environment): void
    {
        $this->helper = new ilResourceStorageMigrationHelper(
            new ilDataCollectionStakeholder(),
            $environment
        );
    }

    /**
     * @inheritDoc
     */
    public function step(\ILIAS\Setup\Environment $environment): void
    {
        $integer_storage = "il_dcl_stloc2_value";
        $string_storage = "il_dcl_stloc1_value";
        $db = $this->helper->getDatabase();

        // Find next field with a fileupload datatype.

        $legacy_file_field = $db->fetchObject(
            $db->query(
                "SELECT * FROM il_dcl_field AS field WHERE datatype_id = 6 LIMIT 1;"
            )
        );

        // Loop through all records of the field.
        $legacy_file_records = $db->queryF(
            "SELECT * FROM il_dcl_record_field AS record_field WHERE record_field.field_id = %s;",
            ['integer'],
            [$legacy_file_field->id]
        );
        while ($record = $db->fetchObject($legacy_file_records)) {
            // Get the file id from the storage.
            $legacy_file_record = $db->fetchObject(
                $db->queryF(
                    "SELECT id, rid, file_id, record_field_id 
                    FROM $integer_storage AS storage 
                    JOIN file_data AS file ON file.file_id = storage.value
                    WHERE storage.record_field_id = %s;",
                    ['integer'],
                    [$record->id]
                )
            );

            // Store RID as new value in string storage.
            $rid = $legacy_file_record->rid;
            $db->insert($string_storage, [
                'id' => ['integer', $db->nextId($string_storage)],
                'record_field_id' => ['integer', (int) $legacy_file_record->record_field_id],
                'value' => ['text', $rid],
            ]);

            // Remove file_id from integer storage.
            $db->manipulateF(
                "DELETE FROM $integer_storage WHERE id = %s;",
                ['integer'],
                [(int) $legacy_file_record->id]
            );

            // Switch Stakeholder
            $this->helper->moveResourceToNewStakeholderAndOwner(
                new ResourceIdentification($rid),
                new ilObjFileStakeholder(),
                $this->helper->getStakeholder()
            );

            // Delete File-object
            try {
                $file_id = (int) $legacy_file_record->file_id;
                $db->manipulateF(
                    "DELETE FROM file_data WHERE file_id = %s",
                    ['integer'],
                    [$file_id]
                );
                $db->manipulateF(
                    "DELETE FROM history WHERE obj_id = %s",
                    ['integer'],
                    [(int) $legacy_file_record->id]
                );
                $db->manipulateF(
                    "DELETE FROM object_data WHERE obj_id = %s AND type = %s",
                    ['integer', 'text'],
                    [(int) $legacy_file_record->id, 'file']
                );
            } catch (Exception $e) {
                continue;
            }
        }

        // update datatype of legacy entry, so it now reads from string-storage.
        $db->update("il_dcl_field", [
            'datatype_id' => ['integer', ilDclDatatype::INPUTFORMAT_FILE],
        ], [
            'id' => ['integer', (int) $legacy_file_field->id],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getRemainingAmountOfSteps(): int
    {
        $legacy_file_field_amount = $this->helper->getDatabase()->fetchObject(
            $this->helper->getDatabase()->query(
                "SELECT COUNT(field.id) AS amount FROM il_dcl_field AS field WHERE field.datatype_id = 6;"
            )
        );

        return (int) $legacy_file_field_amount?->amount;
    }
}
