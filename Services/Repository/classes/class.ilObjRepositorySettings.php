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
 * @package WebDAV
 */
class ilObjRepositorySettings extends ilObject
{
    const NEW_ITEM_GROUP_TYPE_GROUP = 1;
    const NEW_ITEM_GROUP_TYPE_SEPARATOR = 2;


    /**
     * Boolean property. Set this to true, to enable WebDAV access to files.
     */
    private $webdavEnabled;
    /**
     * Boolean property. Set this to true, to enable versioning for existing files uploaded with WebDAV.
     * Set this to false, to overwrite existing file version on file upload
     */
    private $webdavVersioningEnabled;
    /**
     * Boolean property. Set this to true, to make WebDAV item actions visible for repository items.
     */
    private $webdavActionsVisible;
    /**
     * Boolean property. Set this to true, to use customized mount instructions.
     * If the value is false, the default mount instructions are used.
     */
    private $customWebfolderInstructionsEnabled;
    /**
     * String property. Customized mount instructions for WebDAV access to files.
     */
    private $customWebfolderInstructions;


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


    /**
     * Sets the webdavEnabled property.
     *
     * @param boolean    new value
     *
     * @return    void
     */
    public function setWebdavEnabled($newValue)
    {
        $this->webdavEnabled = $newValue;
    }

    /**
     * Gets the webdavEnabled property.
     *
     * @return    boolean    value
     */
    public function isWebdavEnabled()
    {
        return $this->webdavEnabled;
    }

    public function setWebdavVersioningEnabled($newValue)
    {
        $this->webdavVersioningEnabled = $newValue;
    }

    public function isWebdavVersioningEnabled()
    {
        return $this->webdavVersioningEnabled;
    }

    /**
     * Sets the webdavActionsVisible property.
     *
     * @param boolean    new value
     *
     * @return    void
     */
    public function setWebdavActionsVisible($newValue)
    {
        $this->webdavActionsVisible = $newValue;
    }

    /**
     * Gets the webdavActionsVisible property.
     *
     * @return    boolean    value
     */
    public function isWebdavActionsVisible()
    {
        return $this->webdavActionsVisible;
    }

    /**
     * Gets the defaultWebfolderInstructions property.
     * This is a read only property. The text is retrieved from $lng.
     *
     * @return    String    value
     */
    public function getDefaultWebfolderInstructions()
    {
        return self::_getDefaultWebfolderInstructions();
    }

    /**
     * Gets the customWebfolderInstructionsEnabled property.
     *
     * @return    boolean    value
     */
    public function isCustomWebfolderInstructionsEnabled()
    {
        return $this->customWebfolderInstructionsEnabled;
    }

    /**
     * Sets the customWebfolderInstructionsEnabled property.
     *
     * @param boolean new value.
     *
     * @return    void
     */
    public function setCustomWebfolderInstructionsEnabled($newValue)
    {
        $this->customWebfolderInstructionsEnabled = $newValue;
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
            "organisation" => array("fold", "sess", "cat", "catr", "crs", "crsr", "grp", "grpr", "itgr", "book", "prg", "prgr"),
            "communication" => array("frm", "chtr"),
            "breaker1" => null,
            "content" => array("file", "webr", "feed", "copa", "wiki", "blog", "lm", "htlm", "sahs", 'cmix', 'lti', "lso", "glo", "dcl", "bibl", "mcst", "mep"),
            "breaker2" => null,
            "assessment" => array("exc", "tst", "qpl", "iass"),
            "feedback" => array("poll", "svy", "spl"),
            "templates" => array("prtt")
        );
        
        $pos = 0;
        foreach ($groups as $group => $items) {
            $pos += 10;
            $grp_id = $pos / 10;

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
                        str_pad($idx + 1, 4, "0", STR_PAD_LEFT);
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

    /**
     * create
     *
     * note: title, description and type should be set when this function is called
     *
     * @return    integer        object id
     */
    public function create()
    {
        parent::create();
        $this->write();
    }


    /**
     * update object in db
     *
     * @return    boolean    true on success
     */
    public function update()
    {
        parent::update();
        $this->write();
    }

    /**
     * write object data into db
     *
     * @param boolean
     */
    private function write()
    {
        global $DIC;
        $ilClientIniFile = $DIC['ilClientIniFile'];
        $settings = new ilSetting('file_access');

        // Clear any old error messages
        $ilClientIniFile->error(null);

        if (!$ilClientIniFile->groupExists('file_access')) {
            $ilClientIniFile->addGroup('file_access');
        }
        $ilClientIniFile->setVariable('file_access', 'webdav_enabled', $this->webdavEnabled ? '1' : '0');
        $settings->set('webdav_versioning_enabled', $this->webdavVersioningEnabled ? '1' : '0');
        $ilClientIniFile->setVariable('file_access', 'webdav_actions_visible', $this->webdavActionsVisible ? '1' : '0');
        $ilClientIniFile->write();

        if ($ilClientIniFile->getError()) {
            global $DIC;
            $ilErr = $DIC['ilErr'];
            $ilErr->raiseError($ilClientIniFile->getError(), $ilErr->WARNING);
        }
    }

    /**
     * read object data from db into object
     */
    public function read()
    {
        parent::read();

        global $DIC;
        $settings = new ilSetting('file_access');
        $ilClientIniFile = $DIC['ilClientIniFile'];
        $this->webdavEnabled = $ilClientIniFile->readVariable('file_access', 'webdav_enabled') == '1';
        // default_value = 1 for versionigEnabled because it was already standard before ilias5.4
        $this->webdavVersioningEnabled = $settings->get('webdav_versioning_enabled', '1') == '1';
        $this->webdavActionsVisible = $ilClientIniFile->readVariable('file_access', 'webdav_actions_visible') == '1';
        $ilClientIniFile->ERROR = false;
    }

    /**
     * TODO: Check if needed and refactor
     *
     * Gets instructions for the usage of webfolders.
     *
     * The instructions consist of HTML text with placeholders.
     * See _getWebfolderInstructionsFor for a description of the supported
     * placeholders.
     *
     * @return String HTML text with placeholders.
     */
    public static function _getDefaultWebfolderInstructions()
    {
        global $lng;

        return $lng->txt('webfolder_instructions_text');
    }
}
