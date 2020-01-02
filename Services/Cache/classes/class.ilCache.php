<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cache class. The cache class stores key/value pairs. Since the primary
 * key is only one text field. It's sometimes necessary to combine parts
 * like "100:200" for user id 100 and ref_id 200.
 *
 * However sometimes records should be deleted by pars of the main key. For
 * this reason up to two optional additional optional integer and two additional
 * optional text fields can be stored. A derived class may delete records
 * based on the content of this additional keys.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesCache
 */
class ilCache
{
    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct($a_component, $a_cache_name, $a_use_long_content = false)
    {
        $this->setComponent($a_component);
        $this->setName($a_cache_name);
        $this->setUseLongContent($a_use_long_content);
    }
    
    /**
     * Check if cache is disabled
     * Forced if member view is active
     * @return bool
     */
    public function isDisabled()
    {
        include_once './Services/Container/classes/class.ilMemberViewSettings.php';
        return ilMemberViewSettings::getInstance()->isActive();
    }
    
    /**
     * Set component
     *
     * @param	string	component
     */
    public function setComponent($a_val)
    {
        $this->component = $a_val;
    }
    
    /**
     * Get component
     *
     * @return	string	component
     */
    protected function getComponent()
    {
        return $this->component;
    }
    
    /**
     * Set name
     *
     * @param	string	name
     */
    protected function setName($a_val)
    {
        $this->name = $a_val;
    }
    
    /**
     * Get name
     *
     * @return	string	name
     */
    protected function getName()
    {
        return $this->name;
    }
    
    /**
     * Set use long content
     *
     * @param	boolean	use long content
     */
    protected function setUseLongContent($a_val)
    {
        $this->use_long_content = $a_val;
    }
    
    /**
     * Get use long content
     *
     * @return	boolean	use long content
     */
    protected function getUseLongContent()
    {
        return $this->use_long_content;
    }
    
    /**
     * Set expires after x seconds
     *
     * @param	int	expires after x seconds
     */
    public function setExpiresAfter($a_val)
    {
        $this->expires_after = $a_val;
    }
    
    /**
     * Get expires after x seconds
     *
     * @return	int	expires after x seconds
     */
    public function getExpiresAfter()
    {
        return $this->expires_after;
    }
    
    /**
     * Get entry
     *
     * @param	string	entry id
     * @return	string	entry value
     */
    final public function getEntry($a_id)
    {
        if ($this->readEntry($a_id)) {	// cache hit
            $this->last_access = "hit";
            return $this->entry;
        }
        $this->last_access = "miss";
    }
    
    /**
     * Read entry
     *
     * @param
     * @return
     */
    protected function readEntry($a_id)
    {
        global $ilDB;
        
        $table = $this->getUseLongContent()
            ? "cache_clob"
            : "cache_text";
    
        $query = "SELECT value FROM $table WHERE " .
            "component = " . $ilDB->quote($this->getComponent(), "text") . " AND " .
            "name = " . $ilDB->quote($this->getName(), "text") . " AND " .
            "expire_time > " . $ilDB->quote(time(), "integer") . " AND " .
            "ilias_version = " . $ilDB->quote(ILIAS_VERSION_NUMERIC, "text") . " AND " .
            "entry_id = " . $ilDB->quote($a_id, "text");
        
        $set = $ilDB->query($query);

        if ($rec  = $ilDB->fetchAssoc($set)) {
            $this->entry = $rec["value"];
            return true;
        }

        return false;
    }
    
    /**
     * Last access
     */
    public function getLastAccessStatus()
    {
        return $this->last_access;
    }
    
    
    /**
     * Store entry
     *
     * @param	string		key
     * @param	string		value
     * @param	int			additional optional integer key
     * @param	int			additional optional integer key
     * @param	string		additional optional text key
     * @param	string		additional optional text key
     * @return
     */
    public function storeEntry(
        $a_id,
        $a_value,
        $a_int_key1 = null,
        $a_int_key2 = null,
        $a_text_key1 = null,
        $a_text_key2 = null
    ) {
        global $ilDB;

        $table = $this->getUseLongContent()
            ? "cache_clob"
            : "cache_text";
        $type =  $this->getUseLongContent()
            ? "clob"
            : "text";
            
        // do not store values, that do not fit into the text field
        if (strlen($a_value) > 4000 && $type == "text") {
            return;
        }

        $set = $ilDB->replace($table, array(
            "component" => array("text", $this->getComponent()),
            "name" => array("text", $this->getName()),
            "entry_id" => array("text", $a_id)
            ), array(
            "value" => array($type, $a_value),
            "int_key_1" => array("integer", $a_int_key1),
            "int_key_2" => array("integer", $a_int_key2),
            "text_key_1" => array("text", $a_text_key1),
            "text_key_2" => array("text", $a_text_key2),
            "expire_time" => array("integer", (int) (time() + $this->getExpiresAfter())),
            "ilias_version" => array("text", ILIAS_VERSION_NUMERIC)
            ));
            
        // In 1/2000 times, delete old entries
        $num = rand(1, 2000);
        if ($num == 500) {
            $ilDB->manipulate(
                "DELETE FROM $table WHERE " .
                " ilias_version <> " . $ilDB->quote(ILIAS_VERSION_NUMERIC, "text") .
                " OR expire_time < " . $ilDB->quote(time(), "integer")
            );
        }
    }
    
    /**
     * Delete by additional keys
     */
    public function deleteByAdditionalKeys(
        $a_int_key1 = null,
        $a_int_key2 = null,
        $a_text_key1 = null,
        $a_text_key2 = null
    ) {
        global $ilDB;

        $table = $this->getUseLongContent()
            ? "cache_clob"
            : "cache_text";
            
        $q = "DELETE FROM $table WHERE " .
            "component = " . $ilDB->quote($this->getComponent(), "text") .
            " AND name = " . $ilDB->quote($this->getName(), "text");

        $fds = array("int_key_1" => array("v" => $a_int_key1, "t" => "integer"),
            "int_key_2" => array("v" => $a_int_key2, "t" => "integer"),
            "text_key_1" => array("v" => $a_text_key1, "t" => "text"),
            "text_key_2" => array("v" => $a_text_key2, "t" => "text"));
        $sep = " AND";
        foreach ($fds as $k => $fd) {
            if (!is_null($fd["v"])) {
                $q .= $sep . " " . $k . " = " . $ilDB->quote($fd["v"], $fd["t"]);
                $set = " AND";
            }
        }
        $ilDB->manipulate($q);
    }
    
    /**
    * Delete all entries of cache
    */
    public function deleteAllEntries()
    {
        global $ilDB;

        $table = $this->getUseLongContent()
            ? "cache_clob"
            : "cache_text";
            
        $q = "DELETE FROM $table WHERE " .
            "component = " . $ilDB->quote($this->getComponent(), "text") .
            " AND name = " . $ilDB->quote($this->getName(), "text");
        $ilDB->manipulate($q);
    }

    /**
     * Delete entry
     *
     * @param	string		key
     */
    public function deleteEntry($a_id)
    {
        global $ilDB;

        $table = $this->getUseLongContent()
            ? "cache_clob"
            : "cache_text";
        
        $ilDB->manipulate("DELETE FROM " . $table . " WHERE "
            . " entry_id = " . $ilDB->quote($a_id, "text")
            . " AND component = " . $ilDB->quote($this->getComponent(), "text") .
            " AND name = " . $ilDB->quote($this->getName(), "text"));
    }
}
