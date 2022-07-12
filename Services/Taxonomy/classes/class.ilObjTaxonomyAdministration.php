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
                    "tax_id" => (int) $row["tax_id"],
                    "tax_title" => $row["tax_title"],
                    "tax_status" => (bool) $row["tax_status"],
                    "obj_title" => $row["obj_title"],
                    "obj_type" => $row["obj_type"],
                    "obj_id" => (int) $row["obj_id"],
                    "ref_id" => (int) $row["ref_id"],
                    "path" => $this->getPath((int) $row["ref_id"])
                );
            }
        }

        return $res;
    }
}
