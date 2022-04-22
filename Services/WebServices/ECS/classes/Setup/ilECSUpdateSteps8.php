<?php declare(strict_types=1);
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
 */

/**
 * Class ilECSUpdateSteps8
 * contains update steps for release 8
 * @author Stefan Meyer <meyer@leifos.de>
 */
class ilECSUpdateSteps8 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    /**
     * Add consent table
     */
    public function step_1() : void
    {
        if (!$this->db->tableExists('ecs_user_consent')) {
            $this->db->createTable('ecs_user_consent', [
                'usr_id' => [
                    'type' => ilDBConstants::T_INTEGER,
                    'length' => 4,
                    'notnull' => true
                ],
                'mid' => [
                    'type' => ilDBConstants::T_INTEGER,
                    'length' => 4,
                    'notnull' => true,
                ]
            ]);
            $this->db->addPrimaryKey('ecs_user_consent', ['usr_id', 'mid']);
        }
    }
}
