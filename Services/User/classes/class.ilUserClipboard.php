<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User clipboard
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilUserClipboard
{
    const SESSION_KEYWORD = 'usr_clipboard';
    
    private static $instance = null;
    
    private $user_id = 0;
    private $clipboard = array();
    
    
    /**
     * singleton constructor
     */
    protected function __construct($a_user_id)
    {
        $this->user_id = $a_user_id;
        $this->read();
    }
    
    /**
     * Get singelton instance
     * @param int $a_usr_id
     * @return ilUserClipboard
     */
    public static function getInstance($a_usr_id)
    {
        if (!self::$instance) {
            self::$instance = new self($a_usr_id);
        }
        return self::$instance;
    }
    
    /**
     * Check if clipboard has content
     * @return bool
     */
    public function hasContent()
    {
        return (bool) count($this->clipboard);
    }
    
    /**
     * Get clipboard content
     * @return array
     */
    public function get()
    {
        return (array) $this->clipboard;
    }
    
    /**
     * Get validated content of clipboard
     * @return type
     */
    public function getValidatedContent()
    {
        $valid = array();
        foreach ($this->clipboard as $usr_id) {
            include_once './Services/User/classes/class.ilObjUser.php';
            if (strlen(ilObjUser::_lookupLogin($usr_id))) {
                $valid[] = $usr_id;
            }
        }
        return $valid;
    }
    
    /**
     * Add entries to clipboard
     */
    public function add($a_usr_ids)
    {
        $this->clipboard = array_unique(array_merge($this->clipboard, (array) $a_usr_ids));
    }
    
    /**
     * User ids to delete
     * @param array $a_usr_ids
     */
    public function delete(array $a_usr_ids)
    {
        $remaining = array();
        foreach ($this->get() as $usr_id) {
            if (!in_array($usr_id, $a_usr_ids)) {
                $remaining[] = $usr_id;
            }
        }
        $this->replace($remaining);
    }
    
    /**
     * Replace clipboard content
     * @param array $a_usr_ids
     */
    public function replace(array $a_usr_ids)
    {
        $this->clipboard = $a_usr_ids;
    }
    
    public function clear()
    {
        $this->clipboard = array();
    }
    
    /**
     * Save clipboard content in session
     */
    public function save()
    {
        ilSession::set(self::SESSION_KEYWORD, (array) $this->clipboard);
    }
    
    /**
     * Read from session
     */
    protected function read()
    {
        $this->clipboard = (array) ilSession::get(self::SESSION_KEYWORD);
    }
}
