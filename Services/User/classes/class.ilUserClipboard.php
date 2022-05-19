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
 * User clipboard
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilUserClipboard
{
    public const SESSION_KEYWORD = 'usr_clipboard';
    private static ?ilUserClipboard $instance = null;
    private array $clipboard = array(); // Missing array type.
    
    protected function __construct(int $a_user_id)
    {
        $this->read();
    }
    
    public static function getInstance(int $a_usr_id) : self
    {
        if (!self::$instance) {
            self::$instance = new self($a_usr_id);
        }
        return self::$instance;
    }
    
    /**
     * Check if clipboard has content
     */
    public function hasContent() : bool
    {
        return (bool) count($this->clipboard);
    }
    
    /**
     * Get clipboard content
     */
    public function get() : array // Missing array type.
    {
        return $this->clipboard;
    }
    
    /**
     * Get validated content of clipboard
     */
    public function getValidatedContent() : array // Missing array type.
    {
        $valid = array();
        foreach ($this->clipboard as $usr_id) {
            if (strlen(ilObjUser::_lookupLogin($usr_id))) {
                $valid[] = $usr_id;
            }
        }
        return $valid;
    }
    
    /**
     * Add entries to clipboard
     */
    public function add(array $a_usr_ids) : void // Missing array type.
    {
        $this->clipboard = array_unique(array_merge($this->clipboard, $a_usr_ids));
    }
    
    /**
     * User ids to delete
     */
    public function delete(array $a_usr_ids) : void // Missing array type.
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
     */
    public function replace(array $a_usr_ids) : void // Missing array type.
    {
        $this->clipboard = $a_usr_ids;
    }
    
    public function clear() : void
    {
        $this->clipboard = array();
    }
    
    /**
     * Save clipboard content in session
     */
    public function save() : void
    {
        ilSession::set(self::SESSION_KEYWORD, $this->clipboard);
    }
    
    /**
     * Read from session
     */
    protected function read() : void
    {
        $this->clipboard = (array) ilSession::get(self::SESSION_KEYWORD);
    }
}
