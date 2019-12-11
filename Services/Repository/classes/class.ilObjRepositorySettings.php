<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject.php");

/**
 * Class ilObjRepositorySettings
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.ilObjSystemFolder.php 33501 2012-03-03 11:11:05Z akill $
 *
 * @ingroup ServicesRepository
 */
class ilObjRepositorySettings extends ilObject
{
    const NEW_ITEM_GROUP_TYPE_GROUP = 1;
    const NEW_ITEM_GROUP_TYPE_SEPARATOR = 2;
    
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id, $a_call_by_reference = true)
    {
        $this->type = "reps";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function delete()
    {
        // DISABLED
        return false;
    }
    
    public static function addNewItemGroupSeparator()
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        // append
        $pos = $ilDB->query("SELECT max(pos) mpos FROM il_new_item_grp");
        $pos = $ilDB->fetchAssoc($pos);
        $pos = (int) $pos["mpos"];
        $pos += 10;
        
        $seq = $ilDB->nextID("il_new_item_grp");
        
        $ilDB->manipulate("INSERT INTO il_new_item_grp" .
            " (id, pos, type) VALUES (" .
            $ilDB->quote($seq, "integer") .
            ", " . $ilDB->quote($pos, "integer") .
            ", " . $ilDB->quote(self::NEW_ITEM_GROUP_TYPE_SEPARATOR, "integer") .
            ")");
        return true;
    }
    
    public static function addNewItemGroup(array $a_titles)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        // append
        $pos = $ilDB->query("SELECT max(pos) mpos FROM il_new_item_grp");
        $pos = $ilDB->fetchAssoc($pos);
        $pos = (int) $pos["mpos"];
        $pos += 10;
        
        $seq = $ilDB->nextID("il_new_item_grp");
        
        $ilDB->manipulate("INSERT INTO il_new_item_grp" .
            " (id, titles, pos, type) VALUES (" .
            $ilDB->quote($seq, "integer") .
            ", " . $ilDB->quote(serialize($a_titles), "text") .
            ", " . $ilDB->quote($pos, "integer") .
            ", " . $ilDB->quote(self::NEW_ITEM_GROUP_TYPE_GROUP, "integer") .
            ")");
        return true;
    }
    
    public static function updateNewItemGroup($a_id, array $a_titles)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate("UPDATE il_new_item_grp" .
            " SET titles = " . $ilDB->quote(serialize($a_titles), "text") .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        return true;
    }
    
    public static function deleteNewItemGroup($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();
        
        // move subitems to unassigned
        $sub_items = self::getNewItemGroupSubItems();
        $sub_items = $sub_items[$a_id];
        if ($sub_items) {
            foreach ($sub_items as $obj_type) {
                $old_pos = $ilSetting->get("obj_add_new_pos_" . $obj_type);
                if (strlen($old_pos) == 8) {
                    $new_pos = "9999" . substr($old_pos, 4);
                    $ilSetting->set("obj_add_new_pos_" . $obj_type, $new_pos);
                    $ilSetting->set("obj_add_new_pos_grp_" . $obj_type, 0);
                }
            }
        }
        
        $ilDB->manipulate("DELETE FROM il_new_item_grp" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        return true;
    }
    
    public static function getNewItemGroups()
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();
        $ilUser = $DIC->user();
        
        $def_lng = $lng->getDefaultLanguage();
        $usr_lng = $ilUser->getLanguage();
        
        $res = array();
        
        $set = $ilDB->query("SELECT * FROM il_new_item_grp ORDER BY pos");
        while ($row = $ilDB->fetchAssoc($set)) {
            if ($row["type"] == self::NEW_ITEM_GROUP_TYPE_GROUP) {
                $row["titles"] = unserialize($row["titles"]);

                $title = $row["titles"][$usr_lng];
                if (!$title) {
                    $title = $row["titles"][$def_lng];
                }
                if (!$title) {
                    $title = array_shift($row["titles"]);
                }
                $row["title"] = $title;
            } else {
                $row["title"] = $lng->txt("rep_new_item_group_separator");
            }
            
            $res[$row["id"]] = $row;
        }
        
        return $res;
    }

    public static function updateNewItemGroupOrder(array $a_order)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        asort($a_order);
        $pos = 0;
        foreach (array_keys($a_order) as $id) {
            $pos += 10;
            
            $ilDB->manipulate("UPDATE il_new_item_grp" .
                " SET pos = " . $ilDB->quote($pos, "integer") .
                " WHERE id = " . $ilDB->quote($id, "integer"));
        }
    }
    
    protected static function getAllObjTypes()
    {
        global $DIC;

        $ilPluginAdmin = $DIC["ilPluginAdmin"];
        $objDefinition = $DIC["objDefinition"];
        
        $res = array();
        
        // parse modules
        include_once("./Services/Component/classes/class.ilModule.php");
        foreach (ilModule::getAvailableCoreModules() as $mod) {
            $has_repo = false;
            $rep_types = $objDefinition->getRepositoryObjectTypesForComponent(IL_COMP_MODULE, $mod["subdir"]);
            if (sizeof($rep_types) > 0) {
                foreach ($rep_types as $ridx => $rt) {
                    // we only want to display repository modules
                    if ($rt["repository"]) {
                        $has_repo = true;
                    } else {
                        unset($rep_types[$ridx]);
                    }
                }
            }
            if ($has_repo) {
                foreach ($rep_types as $rt) {
                    $res[] = $rt["id"];
                }
            }
        }
        
        // parse plugins
        include_once("./Services/Component/classes/class.ilPlugin.php");
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "Repository", "robj");
        foreach ($pl_names as $pl_name) {
            $pl_id = ilPlugin::lookupIdForName(IL_COMP_SERVICE, "Repository", "robj", $pl_name);
            if ($pl_id) {
                $res[] = $pl_id;
            }
        }
        
        return $res;
    }
    
    public static function getNewItemGroupSubItems()
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        
        $res = array();
        
        foreach (self::getAllObjTypes() as $type) {
            $pos_grp = $ilSetting->get("obj_add_new_pos_grp_" . $type, 0);
            $res[$pos_grp][] = $type;
        }
        
        return $res;
    }
    
    public static function getDefaultNewItemGrouping()
    {
        global $DIC;

        $lng = $DIC->language();
        
        $res = array();
                                
        $groups = array(
            "organisation" => array("fold", "sess", "cat", "catr", "crs", "crsr", "grp", "grpr", "itgr", "book", "prg"),
            "communication" => array("frm", "chtr"),
            "breaker1" => null,
            "content" => array("file", "webr", "feed", "copa", "wiki", "blog", "lm", "htlm", "sahs", "lso", "glo", "dcl", "bibl", "mcst", "mep"),
            "breaker2" => null,
            "assessment" => array("exc", "tst", "qpl", "iass"),
            "feedback" => array("poll", "svy", "spl"),
            "templates" => array("prtt")
        );
        
        $pos = 0;
        foreach ($groups as $group => $items) {
            $pos += 10;
            $grp_id = $pos/10;

            if (is_array($items)) {
                $title = $lng->txt("rep_add_new_def_grp_" . $group);
                
                $res["groups"][$grp_id] = array("id" => $grp_id,
                    "titles" => array($lng->getUserLanguage() => $title),
                    "pos" => $pos,
                    "type" => self::NEW_ITEM_GROUP_TYPE_GROUP,
                    "title" => $title);

                foreach ($items as $idx => $item) {
                    $res["items"][$item] = $grp_id;
                    $res["sort"][$item] = str_pad($pos, 4, "0", STR_PAD_LEFT) .
                        str_pad($idx+1, 4, "0", STR_PAD_LEFT);
                }
            } else {
                $title = "COL_SEP";
                
                $res["groups"][$grp_id] = array("id" => $grp_id,
                    "titles" => array($lng->getUserLanguage() => $title),
                    "pos" => $pos,
                    "type" => self::NEW_ITEM_GROUP_TYPE_SEPARATOR,
                    "title" => $title);
            }
        }
        
        return $res;
    }
    
    public static function deleteObjectType($a_type)
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        
        // see ilObjRepositorySettingsGUI::saveModules()
        $ilSetting->delete("obj_dis_creation_" . $a_type);
        $ilSetting->delete("obj_add_new_pos_" . $a_type);
        $ilSetting->delete("obj_add_new_pos_grp_" . $a_type);
    }
}
