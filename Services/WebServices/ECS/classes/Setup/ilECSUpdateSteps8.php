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

    public function step_2() : void
    {
        if (!$this->db->tableColumnExists('ecs_part_settings', 'username_placeholders')) {
            $this->db->addTableColumn(
                'ecs_part_settings',
                'username_placeholders',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'notnull' => false,
                    'length' => 500,
                    'default' => null
                ]
            );
        }
    }

    public function step_3() : void
    {
        if (!$this->db->tableColumnExists('ecs_part_settings', 'incoming_auth_type')) {
            $this->db->addTableColumn(
                'ecs_part_settings',
                'incoming_auth_type',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'notnull' => true,
                    'length' => 1,
                    'default' => 0
                ]
            );
        }
    }

    public function step_4() : void
    {
        if (!$this->db->tableColumnExists('ecs_part_settings', 'incoming_local_accounts')) {
            $this->db->addTableColumn(
                'ecs_part_settings',
                'incoming_local_accounts',
                [
                    'type' => ilDBConstants::T_INTEGER,
                    'notnull' => true,
                    'length' => 1,
                    'default' => 1
                ]
            );
        }
    }

    public function step_5() : void
    {
        if (!$this->db->tableColumnExists('ecs_part_settings', 'outgoing_auth_mode')) {
            $this->db->addTableColumn(
                'ecs_part_settings',
                'outgoing_auth_mode',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'notnull' => false,
                    'length' => 64,
                    'default' => ''
                ]
            );
        }
    }

    public function step_6() : void
    {
        if ($this->db->tableColumnExists('ecs_part_settings', 'outgoing_auth_mode')) {
            $this->db->renameTableColumn(
                'ecs_part_settings',
                'outgoing_auth_mode',
                'outgoing_auth_modes'
            );
        }
    }
}
