<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Portfolio\Administration;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class PortfolioRoleAssignmentDBRepository
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;
        $this->db = $DIC->database();
    }

    public function add(
        int $template_ref_id,
        int $role_id
    ) : void {
        $db = $this->db;
        $db->replace(
            "prtf_role_assignment",
            [
                    "role_id" => ["integer", $role_id],
                    "template_ref_id" => ["integer", $template_ref_id]
        ],
            []
        );
    }

    public function delete(
        int $template_ref_id,
        int $role_id
    ) : void {
        $db = $this->db;
        $db->manipulateF(
            "DELETE FROM prtf_role_assignment WHERE " .
            " role_id = %s AND template_ref_id = %s",
            ["integer", "integer"],
            [$role_id, $template_ref_id]
        );
    }

    public function getTemplatesForRoles(
        array $role_ids
    ) : array {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT * FROM prtf_role_assignment " .
            " WHERE " . $db->in("role_id", $role_ids, false, "integer"),
            [],
            []
        );
        $template_ref_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $template_ref_ids[$rec["template_ref_id"]] = $rec["template_ref_id"];
        }
        return $template_ref_ids;
    }

    public function getAllAssignmentData(
    ) : array {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT * FROM prtf_role_assignment ",
            [],
            []
        );
        $data = [];
        while ($rec = $db->fetchAssoc($set)) {
            $role_title = \ilObject::_lookupTitle($rec["role_id"]);
            $template_title = \ilObject::_lookupTitle(
                \ilObject::_lookupObjId($rec["template_ref_id"])
            );
            $data[] = [
                "role_id" => $rec["role_id"],
                "template_ref_id" => $rec["template_ref_id"],
                "role_title" => $role_title,
                "template_title" => $template_title
            ];
        }
        return $data;
    }
}
