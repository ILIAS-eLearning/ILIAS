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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilWOPIDB120 implements \ilDatabaseUpdateSteps
{
    private ?ilDBInterface $db = null;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableExists('wopi_lock')) {
            return;
        }
        $this->db->createTable('wopi_lock', [
            'resource_id' => [
                'type' => 'text',
                'length' => 64,
                'notnull' => true,
            ],
            'lock_value' => [
                'type' => 'text',
                'length' => 1024,
                'notnull' => true,
            ],
            'expires' => [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
            ],
            'user_id' => [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
            ],
        ]);
        $this->db->addPrimaryKey('wopi_lock', ['resource_id']);
    }

    /**
     * Saving via WOPI used to add the stakeholder "wopi_unknown" to the resource, which granted
     * access to everybody. Saving no longer adds a stakeholder, see RequestHandler.
     */
    public function step_2(): void
    {
        $this->db->manipulateF(
            'DELETE FROM il_resource_stkh_u WHERE stakeholder_id = %s',
            ['text'],
            ['wopi_unknown']
        );
        $this->db->manipulateF(
            'DELETE FROM il_resource_stkh WHERE id = %s',
            ['text'],
            ['wopi_unknown']
        );
    }
}
