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

class ilWorkflowEngine9DBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->manipulate("DELETE FROM settings WHERE keyword = " .
            $this->db->quote('wfe_activation', "text") . " AND module = " .
            $this->db->quote('common', "text"));

        $this->db->manipulate("DELETE FROM settings WHERE module = " .
            $this->db->quote('wfe', "text"));
    }

    public function step_2(): void
    {
        $this->db->dropTable('wfe_det_listening');
        $this->db->dropTable('wfe_startup_events');
        $this->db->dropTable('wfe_static_inputs');
        $this->db->dropTable('wfe_workflows');
    }

    public function step_3(): void
    {
        $this->delete_directory( '../default/wfe');
    }

    public function step_4(): void
    {
        $res = $this->db->queryF(
            'SELECT obj_id FROM object_data WHERE type = %s',
            array('text'),
            array('wfe')
        );
        $row = $this->db->fetchAssoc($res);
        if (is_array($row) && isset($row['obj_id'])) {
            $obj_id = $row['obj_id'];

            $ref_res = $this->db->queryF(
                'SELECT ref_id FROM object_reference WHERE obj_id = %s',
                array('integer'),
                array($obj_id)
            );

            while ($ref_row = $this->db->fetchAssoc($ref_res)) {
                if (is_array($ref_row) && isset($ref_row['ref_id'])) {
                    $ref_id = $ref_row['ref_id'];

                    $this->db->manipulateF(
                        'DELETE FROM tree WHERE child = %s',
                        array('integer'),
                        array($ref_id)
                    );

                    $this->db->manipulateF(
                        'DELETE FROM rbac_pa WHERE ref_id = %s',
                        array('integer'),
                        array($ref_id)
                    );

                    $this->db->manipulateF(
                        'DELETE FROM rbac_templates WHERE parent = %s',
                        array('integer'),
                        array($ref_id)
                    );

                    $this->db->manipulateF(
                        'DELETE FROM rbac_fa WHERE parent = %s',
                        array('integer'),
                        array($ref_id)
                    );

                }
            }

            $this->db->manipulateF(
                'DELETE FROM object_reference WHERE obj_id = %s',
                array('integer'),
                array($obj_id)
            );

            $this->db->manipulateF(
                'DELETE FROM object_data WHERE obj_id = %s',
                array('integer'),
                array($obj_id)
            );
        }

        $res = $this->db->queryF(
            'SELECT obj_id FROM object_data WHERE type = %s AND title = %s',
            array('text', 'text'),
            array('typ', 'wfe')
        );
        $row = $this->db->fetchAssoc($res);

        if (is_array($row) && isset($row['obj_id'])) {
            $obj_id = $row['obj_id'];

            $this->db->manipulateF(
                'DELETE FROM rbac_ta WHERE typ_id = %s',
                array('integer'),
                array($obj_id)
            );

            $this->db->manipulateF(
                'DELETE FROM object_data WHERE obj_id = %s',
                array('integer'),
                array($obj_id)
            );
        }
    }

    private function delete_directory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}
