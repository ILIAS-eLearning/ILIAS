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
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilUserFormSettings
{
    protected ilDBInterface $db;
    protected int $user_id;
    protected string $id;
    protected array $settings = array(); // Missing array type.
    private bool $has_stored_entry = false;
    
    public function __construct(
        string $a_id,
        ?int $a_user_id = null
    ) {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        
        $this->user_id = (int) $a_user_id;
        $this->id = $a_id;
        $this->db = $ilDB;
        
        if (!$this->user_id) {
            $this->user_id = $ilUser->getId();
        }
        
        $this->read();
    }
    
    /**
     * Check if entry exist
     */
    public function hasStoredEntry() : bool
    {
        return $this->has_stored_entry;
    }
    
    /**
     * @param array Array of Settings
     */
    public function set(array $a_data) : void // Missing array type.
    {
        $this->settings = $a_data;
    }
    
    public function reset() : void
    {
        $this->settings = array();
    }
    
    /**
     * Check if a specific option is enabled
     */
    public function enabled(string $a_option) : bool
    {
        return (bool) $this->getValue($a_option);
    }
    
    /**
     * Get value
     * @return mixed
     */
    public function getValue(string $a_option)
    {
        if ($this->valueExists($a_option)) {
            return $this->settings[$a_option];
        }
        return null;
    }
    
    /**
     * @param mixed $a_value
     */
    public function setValue(string $a_option, $a_value) : void
    {
        $this->settings[$a_option] = $a_value;
    }
    
    /**
     * Delete value
     */
    public function deleteValue(string $a_option) : void
    {
        if ($this->valueExists($a_option)) {
            unset($this->settings[$a_option]);
        }
    }
    
    /**
     * Does value exist in settings?
     */
    public function valueExists(string $a_option) : bool
    {
        return array_key_exists($a_option, $this->settings);
    }

    public function store() : void
    {
        $this->delete(false);
            
        $query = "INSERT INTO usr_form_settings (user_id,id,settings) " .
            "VALUES( " .
                $this->db->quote($this->user_id, 'integer') . ", " .
                $this->db->quote($this->id, 'text') . ", " .
                $this->db->quote(serialize($this->settings), 'text') . " " .
            ")";
        $this->db->manipulate($query);
    }
    
    protected function read() : void
    {
        $query = "SELECT * FROM usr_form_settings" .
            " WHERE user_id = " . $this->db->quote($this->user_id, 'integer') .
            " AND id = " . $this->db->quote($this->id, 'text');
        $res = $this->db->query($query);
        
        if ($res->numRows()) {
            $this->has_stored_entry = true;
        }
        
        $this->reset();
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->settings = unserialize($row->settings, ['allowed_classes' => false]);
        }
    }
        
    /**
     * Delete user related data
     */
    public function delete(bool $a_reset = true) : void
    {
        $query = "DELETE FROM usr_form_settings" .
            " WHERE user_id = " . $this->db->quote($this->user_id, 'integer') .
            " AND id = " . $this->db->quote($this->id, 'text');
        $this->db->manipulate($query);
        
        if ($a_reset) {
            $this->reset();
        }
    }
    
    /**
     * Delete all settings for user id
     */
    public static function deleteAllForUser(int $a_user_id) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "DELETE FROM usr_form_settings" .
            " WHERE user_id = " . $ilDB->quote($a_user_id, 'integer');
        $ilDB->manipulate($query);
    }
    
    /**
     * Delete for id
     */
    public static function deleteAllForId(string $a_id) : void
    {
        $query = "DELETE FROM usr_form_settings" .
            " WHERE id = " . $GLOBALS['DIC']['ilDB']->quote($a_id, 'text');
        $GLOBALS['DIC']['ilDB']->manipulate($query);
    }
    
    /**
     * Delete all entries for prefix
     */
    public static function deleteAllForPrefix(string $a_prefix) : void
    {
        $query = "DELETE FROM usr_form_settings " .
            'WHERE ' . $GLOBALS['DIC']['ilDB']->like('id', 'text', $a_prefix . '%');
        
        $GLOBALS['DIC']['ilDB']->manipulate($query);
    }
    
    /**
     * Import settings from form
     */
    public function importFromForm(ilPropertyFormGUI $a_form) : void
    {
        $this->reset();
        $value = null;
        
        foreach ($a_form->getItems() as $item) {
            if (method_exists($item, "getPostVar")) {
                $field = $item->getPostVar();
                
                if (method_exists($item, "getDate")) {
                    $value = $item->getDate();
                    if ($value && !$value->isNull()) {
                        $value = $value->get(IL_CAL_DATETIME);
                    }
                } elseif (method_exists($item, "getChecked")) {
                    $value = $item->getChecked();
                } elseif (method_exists($item, "getMulti") && $item->getMulti()) {
                    $value = $item->getMultiValues();
                } elseif (method_exists($item, "getValue")) {
                    $value = $item->getValue();
                }
                
                $this->setValue($field, $value);
            }
        }
    }
    
    /**
     * Export settings to form
     */
    public function exportToForm(
        ilPropertyFormGUI $a_form,
        bool $a_set_post = false
    ) : void {
        foreach ($a_form->getItems() as $item) {
            if (method_exists($item, "getPostVar")) {
                $field = $item->getPostVar();
                
                if ($this->valueExists($field)) {
                    $value = $this->getValue($field);

                    if (method_exists($item, "setDate")) {
                        $date = new ilDateTime($value, IL_CAL_DATETIME);
                        $item->setDate($date);
                    } elseif (method_exists($item, "setChecked")) {
                        $item->setChecked((bool) $value);
                    } elseif (method_exists($item, "setValue")) {
                        $item->setValue($value);
                    }
                }
            }
        }
    }
}
