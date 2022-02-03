<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Session based immediate storage.
 *
 * This class stores session based user data in the database. The difference
 * to ilSession is that data is written immediately when the set() function
 * is called and that this data is written "per key".
 *
 * Please note that the values are limited to TEXT(1000)!
 *
 * This class is needed for cases, where ajax calls should write session
 * based data.
 *
 * Since more concurrent ajax calls can be initiated by a page request, these
 * calls may run into race conditions, if ilSession is used, since it always
 * reads all key/value pairs at the beginning of a request and writes all of
 * them at the end. Similar issues can appear if a page initiates additional
 * requests by (i)frames.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilSessionIStorage
{
    private string $session_id = "";
    private string $component_id = "";
    private static array $values = [];
    
    /**
     * Constructor
     *
     * @param string $a_component_id component id (e.g. "crs", "lm", ...)
     * @param string $a_sess_id session id
     */
    public function __construct(string $a_component_id, string $a_sess_id = "")
    {
        $this->component_id = $a_component_id;
        if ($a_sess_id != "") {
            $this->session_id = $a_sess_id;
        } else {
            $this->session_id = session_id();
        }
    }

    private function initComponentCacheIfNotExists() : void
    {
        if (!isset(self::$values[$this->component_id]) || !is_array(self::$values[$this->component_id])) {
            self::$values[$this->component_id] = [];
        }
    }
    
    /**
     * Set a value
     *
     * @param string $a_val value
     */
    public function set(string $a_key, string $a_val)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->initComponentCacheIfNotExists();

        self::$values[$this->component_id][$a_key] = $a_val;
        $ilDB->replace(
            "usr_sess_istorage",
            array(
                "session_id" => array("text", $this->session_id),
                "component_id" => array("text", $this->component_id),
                "vkey" => array("text", $a_key)
                ),
            array("value" => array("text", $a_val))
        );
    }

    /**
     * @param string $a_key
     * @return string
     */
    public function get(string $a_key) : string
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (
            isset(self::$values[$this->component_id]) && is_array(self::$values[$this->component_id]) &&
            isset(self::$values[$this->component_id][$a_key])
        ) {
            return self::$values[$this->component_id][$a_key];
        }
        
        $set = $ilDB->query(
            "SELECT value FROM usr_sess_istorage " .
            " WHERE session_id = " . $ilDB->quote($this->session_id, "text") .
            " AND component_id = " . $ilDB->quote($this->component_id, "text") .
            " AND vkey = " . $ilDB->quote($a_key, "text")
        );
        $rec = $ilDB->fetchAssoc($set);
        $value = (string) ($rec['value'] ?? '');

        $this->initComponentCacheIfNotExists();

        self::$values[$this->component_id][$a_key] = $value;

        return $value;
    }
    
    /**
     * Destroy session(s). This is called by ilSession->destroy
     */
    public static function destroySession(string $a_session_id) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!is_array($a_session_id)) {
            $q = "DELETE FROM usr_sess_istorage WHERE session_id = " .
                $ilDB->quote($a_session_id, "text");
        } else {
            $q = "DELETE FROM usr_sess_istorage WHERE " .
                $ilDB->in("session_id", $a_session_id, "", "text");
        }

        $ilDB->manipulate($q);
    }
}
