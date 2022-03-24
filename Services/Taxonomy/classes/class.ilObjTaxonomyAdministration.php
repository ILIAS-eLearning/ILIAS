<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjTaxonomyAdministration
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjTaxonomyAdministration extends ilObject
{
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->db = $DIC->database();
        $this->type = "taxs";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function delete() : bool
    {
        // DISABLED
        return false;
    }

    protected function getPath(int $a_ref_id) : array
    {
        $tree = $this->tree;

        $res = array();

        foreach ($tree->getPathFull($a_ref_id) as $data) {
            $res[] = $data['title'];
        }

        return $res;
    }

    public function getRepositoryTaxonomies() : array
    {
        $ilDB = $this->db;
        $tree = $this->tree;

        $res = array();

        $sql = "SELECT oref.ref_id, od.obj_id, od.type obj_type, od.title obj_title," .
            " tu.tax_id, od2.title tax_title, cs.value tax_status" .
            " FROM object_data od" .
            " JOIN object_reference oref ON (od.obj_id = oref.obj_id)" .
            " JOIN tax_usage tu ON (tu.obj_id = od.obj_id)" .
            " JOIN object_data od2 ON (od2.obj_id = tu.tax_id)" .
            " LEFT JOIN container_settings cs ON (cs.id = od.obj_id AND keyword = " . $ilDB->quote(
                ilObjectServiceSettingsGUI::TAXONOMIES,
                "text"
            ) . ")" .
            " WHERE od.type = " . $ilDB->quote("cat", "text") . // categories only currently
            " AND tu.tax_id > " . $ilDB->quote(0, "integer");
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            if (!$tree->isDeleted((int) $row["ref_id"])) {
                $res[$row["tax_id"]][$row["obj_id"]] = array(
                    "tax_id" => $row["tax_id"],  // TODO PHP8-REVIEW: Please check. Cast to int could be relevant
                    "tax_title" => $row["tax_title"],
                    "tax_status" => (bool) $row["tax_status"],
                    "obj_title" => $row["obj_title"],
                    "obj_type" => $row["obj_type"],
                    "obj_id" => $row["obj_id"], // TODO PHP8-REVIEW: Please check. Cast to int could be relevant
                    "ref_id" => $row["ref_id"],// TODO PHP8-REVIEW: Please check. Cast to int could be relevant
                    "path" => $this->getPath((int) $row["ref_id"])
                );
            }
        }

        return $res;
    }
}
