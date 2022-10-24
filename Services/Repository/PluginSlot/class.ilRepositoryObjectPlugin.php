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
 * Abstract parent class for all repository object plugin classes.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilRepositoryObjectPlugin extends ilPlugin
{
    protected ilLanguage $lng;
    protected ilDBInterface $db;

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
        parent::__construct($this->db, $DIC["component.repository"], "xtst");
    }

    /**
     * Only very little classes seem to care about this:
     *    - Services/Repository/classes/class.ilRepositoryObjectPlugin.php
     *    - Modules/OrgUnit/classes/Extension/class.ilOrgUnitExtensionPlugin.php
     *
     * @param string $a_ctype
     * @param string $a_cname
     * @param string $a_slot_id
     * @param string $a_pname
     * @param string $a_img
     *
     * @return string
     */
    public static function _getImagePath(string $a_ctype, string $a_cname, string $a_slot_id, string $a_pname, string $a_img): string
    {
        global $DIC;

        $img = ilUtil::getImagePath($a_img);
        if (is_int(strpos($img, "Customizing"))) {
            return $img;
        }

        $component_repository = $DIC["component.repository"];

        $plugin = $component_repository->getPluginByName($a_pname);
        $component = $component_repository->getComponentByTypeAndName($a_ctype, $a_cname);

        $d2 = $component->getId() . "_" . $a_slot_id . "_" . $plugin->getId();

        $img = ilUtil::getImagePath($d2 . "/" . $a_img);
        if (is_int(strpos($img, "Customizing"))) {
            return $img;
        }

        $d = $plugin->getPath();

        return $d . "/templates/images/" . $a_img;
    }



    public static function _getIcon(string $a_type): string
    {
        global $DIC;
        $component_repository = $DIC["component.repository"];
        return self::_getImagePath(
            ilComponentInfo::TYPE_SERVICES,
            "Repository",
            "robj",
            $component_repository->getPluginById($a_type)->getName(),
            "icon_" . $a_type . ".svg"
        );
    }

    public static function _getName(string $a_id): string
    {
        global $DIC;
        $component_repository = $DIC["component.repository"];
        if (!$component_repository->hasPluginId($a_id)) {
            return "";
        }
        return $component_repository->getPluginById($a_id)->getName();
    }

    protected function beforeActivation(): bool
    {
        $ilDB = $this->db;

        // before activating, we ensure, that the type exists in the ILIAS
        // object database and that all permissions exist
        $type = $this->getId();

        if (strpos($type, "x") !== 0) {
            throw new ilPluginException("Object plugin type must start with an x. Current type is " . $type . ".");
        }

        // check whether type exists in object data, if not, create the type
        $set = $ilDB->query(
            "SELECT * FROM object_data " .
            " WHERE type = " . $ilDB->quote("typ", "text") .
            " AND title = " . $ilDB->quote($type, "text")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            $t_id = $rec["obj_id"];
        } else {
            $t_id = $ilDB->nextId("object_data");
            $ilDB->manipulate("INSERT INTO object_data " .
                "(obj_id, type, title, description, owner, create_date, last_update) VALUES (" .
                $ilDB->quote($t_id, "integer") . "," .
                $ilDB->quote("typ", "text") . "," .
                $ilDB->quote($type, "text") . "," .
                $ilDB->quote("Plugin " . $this->getPluginName(), "text") . "," .
                $ilDB->quote(-1, "integer") . "," .
                $ilDB->quote(ilUtil::now(), "timestamp") . "," .
                $ilDB->quote(ilUtil::now(), "timestamp") .
                ")");
        }

        // add rbac operations
        // 1: edit_permissions, 2: visible, 3: read, 4:write, 6:delete
        $ops = [1, 2, 3, 4, 6];
        if ($this->allowCopy()) {
            $ops[] = ilRbacReview::_getOperationIdByName("copy");
        }
        foreach ($ops as $op) {
            // check whether type exists in object data, if not, create the type
            $set = $ilDB->query(
                "SELECT * FROM rbac_ta " .
                " WHERE typ_id = " . $ilDB->quote($t_id, "integer") .
                " AND ops_id = " . $ilDB->quote($op, "integer")
            );
            if (!$ilDB->fetchAssoc($set)) {
                $ilDB->manipulate("INSERT INTO rbac_ta " .
                    "(typ_id, ops_id) VALUES (" .
                    $ilDB->quote($t_id, "integer") . "," .
                    $ilDB->quote($op, "integer") .
                    ")");
            }
        }

        // now add creation permission, if not existing
        $set = $ilDB->query(
            "SELECT * FROM rbac_operations " .
            " WHERE class = " . $ilDB->quote("create", "text") .
            " AND operation = " . $ilDB->quote("create_" . $type, "text")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            $create_ops_id = $rec["ops_id"];
        } else {
            $create_ops_id = $ilDB->nextId("rbac_operations");
            $ilDB->manipulate("INSERT INTO rbac_operations " .
                "(ops_id, operation, description, class) VALUES (" .
                $ilDB->quote($create_ops_id, "integer") . "," .
                $ilDB->quote("create_" . $type, "text") . "," .
                $ilDB->quote("create " . $type, "text") . "," .
                $ilDB->quote("create", "text") .
                ")");
        }

        // assign creation operation to root, cat, crs, grp and fold
        $par_types = $this->getParentTypes();
        foreach ($par_types as $par_type) {
            $set = $ilDB->query(
                "SELECT obj_id FROM object_data " .
                " WHERE type = " . $ilDB->quote("typ", "text") .
                " AND title = " . $ilDB->quote($par_type, "text")
            );
            if (($rec = $ilDB->fetchAssoc($set)) && $rec["obj_id"] > 0) {
                $set = $ilDB->query(
                    "SELECT * FROM rbac_ta " .
                    " WHERE typ_id = " . $ilDB->quote($rec["obj_id"], "integer") .
                    " AND ops_id = " . $ilDB->quote($create_ops_id, "integer")
                );
                if (!$ilDB->fetchAssoc($set)) {
                    $ilDB->manipulate("INSERT INTO rbac_ta " .
                        "(typ_id, ops_id) VALUES (" .
                        $ilDB->quote($rec["obj_id"], "integer") . "," .
                        $ilDB->quote($create_ops_id, "integer") .
                        ")");
                }
            }
        }

        return true;
    }

    protected function beforeUninstallCustom(): bool
    {
        // plugin-specific
        // false would indicate that anything went wrong
        return true;
    }

    abstract protected function uninstallCustom(): void;

    final protected function beforeUninstall(): bool
    {
        if ($this->beforeUninstallCustom()) {
            $rep_util = new ilRepUtil();
            $rep_util->deleteObjectType($this->getId());

            // custom database tables may be needed by plugin repository object
            $this->uninstallCustom();

            return true;
        }
        return false;
    }

    /**
     * @return string[]
     */
    public function getParentTypes(): array
    {
        $par_types = ["root", "cat", "crs", "grp", "fold"];
        return $par_types;
    }

    /**
     * decides if this repository plugin can be copied
     */
    public function allowCopy(): bool
    {
        return false;
    }

    /**
     * Decide if this repository plugin uses OrgUnit Permissions
     */
    public function useOrguPermissions(): bool
    {
        return false;
    }

    public function getPrefix(): string
    {
        $lh = $this->getLanguageHandler();
        return $lh->getPrefix();
    }
}
