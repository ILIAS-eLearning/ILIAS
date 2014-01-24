<?php

require_once 'Services/Notifications/classes/class.ilNotificationSetupHelper.php';

/**
 * wrapper for iterating a list of user settings by providing the user ids
 */
class ilNotificationUserIterator implements Iterator {

    private $userids;
    private $rset;
    private $data;
    private $module;

    public function __construct($module, array $userids = array()) {
        global $ilDB;

        $this->db = $ilDB;
        $this->userids = $userids;

        $this->module = $module;

        $this->rewind();
    }

    public function  __destruct() {
        $this->db->free($this->rset);
    }

    public function current() {
        return $this->data;
    }

    public function key() {
        return (int)$this->data['usr_id'];
    }

    public function next() {
        //$this->data = $this->db->fetchAssoc($this->rset);
    }

    public function rewind() {
        $query = 'SELECT usr_id, module, channel FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE module=%s AND ' . $this->db->in('usr_id', $this->userids, false, 'integer');
        $types = array('text');
        $values = array($this->module);
        $this->rset = $this->db->queryF($query, $types, $values);
    }

    public function valid() {
        $this->data = $this->db->fetchAssoc($this->rset);
        return is_array($this->data);
    }
}
?>
