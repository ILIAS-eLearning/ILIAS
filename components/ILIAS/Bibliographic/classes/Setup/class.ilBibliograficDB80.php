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

/**
 * Class ilBibliograficDB80
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilBibliograficDB80 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $database;

    /**
     * @inheritDoc
     */
    public function prepare(ilDBInterface $db): void
    {
        $this->database = $db;
    }

    public function step_1(): void
    {
        if ($this->database->tableColumnExists('il_bibl_field', 'object_id')) {
            $this->database->dropTableColumn('il_bibl_field', 'object_id');
        }
    }

    /**
     * This step serves the transfer from the old object specific online status implementation to the new centralized one.
     *
     * It will update the object_data offline column to the inverted value of the il_bibl_data is_online column
     * for all bibl entries where the offline status is not set yet and an old is_online status exists.
     */
    public function step_2(): void
    {
        if ($this->database->tableColumnExists('il_bibl_data', 'is_online')
            && $this->database->tableColumnExists('object_data', 'offline')
        ) {
            $bibl_data = $this->database->fetchAll(
                $this->database->query('SELECT * FROM il_bibl_data')
            );
            foreach ($bibl_data as $bibl_data_entry) {
                if (isset($bibl_data_entry['is_online'])) {
                    $query = "UPDATE `object_data` SET `offline` = %s WHERE `obj_id` = %s AND `type` = %s AND `offline` IS NULL";
                    $this->database->manipulateF(
                        $query,
                        ['integer', 'integer', 'text'],
                        [
                            !$bibl_data_entry['is_online'],
                            $bibl_data_entry['id'],
                            'bibl'
                        ]
                    );
                }
            }
            $this->database->dropTableColumn('il_bibl_data', 'is_online');
        }
    }
}
