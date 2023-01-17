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
 * Class ilResourceStorageDB90
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilResourceStorageDB90 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }


    /**
     * creates a new database table "il_resource_flavour" that is used to reference
     * resource flavours to it's original resource.
     */
    public function step_1(): void
    {
        $flavour_table = "il_resource_flavour";
        $this->db->dropTable($flavour_table, false);
        if ($this->db->tableExists($flavour_table)) {
            return;
        }

        $this->db->createTable($flavour_table, [
            'rid' => [
                'notnull' => true,
                'length' => '64',
                'type' => 'text',
            ],
            'revision' => [
                'notnull' => true,
                'length' => '8',
                'type' => 'integer',
            ],
            'definition_id' => [
                'notnull' => true,
                'length' => '64',
                'type' => 'text',
            ],
            'variant' => [
                'notnull' => false,
                'length' => '768',
                'type' => 'text',
            ]
        ]);

        $this->db->addIndex($flavour_table, ['rid'], 'i1');
        $this->db->addIndex($flavour_table, ['definition_id'], 'i3');
        $this->db->addIndex($flavour_table, ['variant'], 'i4');
        $this->db->addPrimaryKey($flavour_table, ['rid', 'revision', 'definition_id', 'variant']);
    }

    public function step_2(): void
    {
        // Remove some unused indexes, since they are in primaries now
        try {
            $this->db->dropIndexByFields('il_resource_info', ['rid']);
        } catch (Exception $e) {
        }
        try {
            $this->db->dropIndexByFields('il_resource_revision', ['rid']);
        } catch (Exception $e) {
        }
        try {
            $this->db->dropIndexByFields('il_resource_stkh_u', ['rid']);
        } catch (Exception $e) {
        }
    }
}
