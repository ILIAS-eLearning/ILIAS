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

class ilLTIDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableColumnExists('lti2_consumer', 'consumer_key')) {
            $this->db->dropTableColumn('lti2_consumer', 'consumer_key');
        }
    }

    public function step_2(): void
    {
        if ($this->db->tableColumnExists('lti2_consumer', 'consumer_key256')) {
            $this->db->renameTableColumn('lti2_consumer', 'consumer_key256', 'consumer_key');
        }
    }

    public function step_3(): void
    {
        if ($this->db->tableColumnExists('lti2_consumer', 'consumer_key')) {
            $this->db->modifyTableColumn('lti2_consumer', 'consumer_key', array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        ));
        }
    }

    public function step_4(): void
    {
        if (!$this->db->tableColumnExists('lti2_consumer', 'platform_id')) {
            $this->db->addTableColumn('lti2_consumer', 'platform_id', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_5(): void
    {
        if (!$this->db->tableColumnExists('lti2_consumer', 'client_id')) {
            $this->db->addTableColumn('lti2_consumer', 'client_id', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_6(): void
    {
        if (!$this->db->tableColumnExists('lti2_consumer', 'deployment_id')) {
            $this->db->addTableColumn('lti2_consumer', 'deployment_id', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_7(): void
    {
        if (!$this->db->tableColumnExists('lti2_consumer', 'public_key')) {
            $this->db->addTableColumn('lti2_consumer', 'public_key', [
                'type' => 'clob',
                'notnull' => false
            ]);
        }
    }

    public function step_8(): void
    {
        if (!$this->db->tableExists('lti2_access_token')) {
            $values = array(
                'consumer_pk' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'scopes' => array(
                    'type' => 'clob',
                    'default' => '',
                    'notnull' => true
                ),
                'token' => array(
                    'type' => 'text',
                    'length' => 2000,
                    'default' => '',
                    'notnull' => true
                ),
                'expires' => array(
                    'type' => 'timestamp',
                    'notnull' => true
                ),
                'created' => array(
                    'type' => 'timestamp',
                    'notnull' => true
                ),
                'updated' => array(
                    'type' => 'timestamp',
                    'notnull' => true
                )
           );
            $this->db->createTable("lti2_access_token", $values);
            $this->db->addPrimaryKey("lti2_access_token", array("consumer_pk"));
        }
    }

    public function step_9(): void
    {
        $this->db->modifyTableColumn("lti2_consumer", "settings", array("type" => "clob", "notnull" => false));
    }

    /**
     * The LTI User role could be created more than once. Its title starts with "il_", so the copies
     * cannot be deleted from the user interface. Keep one of them and move everything to it.
     */
    public function step_10(): void
    {
        $role_ids = [];
        $res = $this->db->query(
            "SELECT obj_id FROM object_data WHERE type = " . $this->db->quote('role', 'text') .
            " AND title = " . $this->db->quote('il_lti_global_role', 'text') . " ORDER BY obj_id"
        );
        while ($row = $this->db->fetchAssoc($res)) {
            $role_ids[] = (int) $row['obj_id'];
        }

        if (count($role_ids) < 2) {
            return;
        }

        $keep_role_id = $this->getLtiRoleToKeep($role_ids);

        foreach ($role_ids as $role_id) {
            if ($role_id === $keep_role_id) {
                continue;
            }

            $this->db->manipulateF(
                "UPDATE lti_ext_consumer SET role = %s WHERE role = %s",
                ['integer', 'integer'],
                [$keep_role_id, $role_id]
            );
            foreach (['admin', 'tutor', 'member'] as $column) {
                $this->db->manipulateF(
                    "UPDATE lti_int_provider_obj SET " . $column . " = %s WHERE " . $column . " = %s",
                    ['integer', 'integer'],
                    [$keep_role_id, $role_id]
                );
            }

            $res = $this->db->queryF(
                "SELECT usr_id FROM rbac_ua WHERE rol_id = %s AND usr_id NOT IN " .
                "(SELECT usr_id FROM rbac_ua WHERE rol_id = %s)",
                ['integer', 'integer'],
                [$role_id, $keep_role_id]
            );
            $user_ids = [];
            while ($row = $this->db->fetchAssoc($res)) {
                $user_ids[] = (int) $row['usr_id'];
            }
            foreach ($user_ids as $user_id) {
                $this->db->insert('rbac_ua', [
                    'usr_id' => ['integer', $user_id],
                    'rol_id' => ['integer', $keep_role_id]
                ]);
            }

            foreach (['rbac_ua', 'rbac_pa', 'rbac_templates', 'rbac_fa'] as $table) {
                $this->db->manipulateF("DELETE FROM " . $table . " WHERE rol_id = %s", ['integer'], [$role_id]);
            }
            $this->db->manipulateF("DELETE FROM role_data WHERE role_id = %s", ['integer'], [$role_id]);
            $this->db->manipulateF("DELETE FROM ldap_rg_mapping WHERE role = %s", ['integer'], [$role_id]);
            foreach (['object_translation', 'object_description', 'object_data'] as $table) {
                $this->db->manipulateF("DELETE FROM " . $table . " WHERE obj_id = %s", ['integer'], [$role_id]);
            }
        }
    }

    /**
     * Prefer the role used by the most consumers, then the one with the most users, then the oldest one.
     *
     * @param int[] $role_ids
     */
    protected function getLtiRoleToKeep(array $role_ids): int
    {
        $keep_role_id = $role_ids[0];
        $best = [-1, -1];
        foreach ($role_ids as $role_id) {
            $consumers = $this->db->fetchAssoc($this->db->queryF(
                "SELECT COUNT(*) cnt FROM lti_ext_consumer WHERE role = %s",
                ['integer'],
                [$role_id]
            ));
            $users = $this->db->fetchAssoc($this->db->queryF(
                "SELECT COUNT(*) cnt FROM rbac_ua WHERE rol_id = %s",
                ['integer'],
                [$role_id]
            ));
            $score = [(int) $consumers['cnt'], (int) $users['cnt']];
            if ($score > $best) {
                $best = $score;
                $keep_role_id = $role_id;
            }
        }

        return $keep_role_id;
    }
}
