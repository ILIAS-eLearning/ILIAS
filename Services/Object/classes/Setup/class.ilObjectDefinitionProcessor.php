<?php declare(strict_types=1);

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
 
class ilObjectDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected ilDBInterface $db;
    protected ?string $component;
    protected ?string $current_object;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function purge() : void
    {
        $this->db->manipulate("DELETE FROM il_object_def");
        $this->db->manipulate("DELETE FROM il_object_subobj");
        $this->db->manipulate("DELETE FROM il_object_group");
        $this->db->manipulate("DELETE FROM il_object_sub_type");
    }

    public function beginComponent(string $component, string $type) : void
    {
        $this->component = $type . "/" . $component;
        $this->current_object = null;
    }

    public function endComponent(string $component, string $type) : void
    {
        $this->component = null;
        $this->current_object = null;
    }

    public function beginTag(string $name, array $attributes) : void
    {
        switch ($name) {
            case 'object':

                // if attributes are not given, set default (repository only)
                if (($attributes["repository"] ?? null) === null) {
                    $attributes["repository"] = true;
                }
                if (($attributes["workspace"] ?? null) === null) {
                    $attributes["workspace"] = false;
                }

                $this->current_object = $attributes["id"];
                $this->db->manipulateF(
                    "INSERT INTO il_object_def (id, class_name, component,location," .
                    "checkbox,inherit,translate,devmode,allow_link,allow_copy,rbac,default_pos," .
                    "default_pres_pos,sideblock,grp," . $this->db->quoteIdentifier("system") . ",export,repository,workspace,administration," .
                    "amet,orgunit_permissions,lti_provider,offline_handling) VALUES " .
                    "(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
                    array("text", "text", "text", "text", "integer", "integer", "text", "integer","integer","integer",
                        "integer","integer","integer","integer", "text", "integer", "integer", "integer", "integer",
                        'integer','integer','integer','integer','integer'),
                    array(
                        $attributes["id"],
                        $attributes["class_name"],
                        $this->component,
                        $this->component . "/" . $attributes["dir"],
                        (int) ($attributes["checkbox"] ?? null),
                        (int) ($attributes["inherit"] ?? null),
                        $attributes["translate"] ?? null,
                        (int) ($attributes["devmode"] ?? null),
                        (int) ($attributes["allow_link"] ?? null),
                        (int) ($attributes["allow_copy"] ?? null),
                        (int) ($attributes["rbac"] ?? null),
                        (int) ($attributes["default_pos"] ?? null),
                        (int) ($attributes["default_pres_pos"] ?? null),
                        (int) ($attributes["sideblock"] ?? null),
                        $attributes["group"] ?? null,
                        (int) ($attributes["system"] ?? null),
                        (int) ($attributes["export"] ?? null),
                        (int) ($attributes["repository"] ?? null),
                        (int) ($attributes["workspace"] ?? null),
                        (int) ($attributes['administration'] ?? null),
                        (int) ($attributes['amet'] ?? null),
                        (int) ($attributes['orgunit_permissions'] ?? null),
                        (int) ($attributes['lti_provider'] ?? null),
                        (int) ($attributes['offline_handling'] ?? null)
                    )
                );
                break;
            
            case "subobj":
                $this->db->manipulateF(
                    "INSERT INTO il_object_subobj (parent, subobj, mmax) VALUES (%s,%s,%s)",
                    array("text", "text", "integer"),
                    array($this->current_object, $attributes["id"], (int) ($attributes["max"] ?? null))
                );
                break;

            case "parent":
                $this->db->manipulateF(
                    "INSERT INTO il_object_subobj (parent, subobj, mmax) VALUES (%s,%s,%s)",
                    array("text", "text", "integer"),
                    array($attributes["id"], $this->current_object, (int) ($attributes["max"] ?? null))
                );
                break;

            case "objectgroup":
                $this->db->manipulateF(
                    "INSERT INTO il_object_group (id, name, default_pres_pos) VALUES (%s,%s,%s)",
                    array("text", "text", "integer"),
                    array($attributes["id"], $attributes["name"], $attributes["default_pres_pos"])
                );
                break;
            case "sub_type":
                $this->db->manipulate("INSERT INTO il_object_sub_type " .
                    "(obj_type, sub_type, amet) VALUES (" .
                    $this->db->quote($this->current_object, "text") . "," .
                    $this->db->quote($attributes["id"], "text") . "," .
                    $this->db->quote($attributes["amet"], "integer") .
                    ")");
                break;
        }
    }

    public function endTag(string $name) : void
    {
    }
}
