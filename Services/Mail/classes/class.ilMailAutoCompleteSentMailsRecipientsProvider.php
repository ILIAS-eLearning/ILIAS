<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/class.ilMailAutoCompleteRecipientProvider.php';
require_once 'Services/Utilities/classes/class.ilStr.php';

/**
 * Class ilMailAutoCompleteSentMailsRecipientsProvider
 */
class ilMailAutoCompleteSentMailsRecipientsProvider extends ilMailAutoCompleteRecipientProvider
{
    /**
     * @var array
     */
    protected $users_stack = array();
    
    /**
     * "Current" implementation of iterator interface
     * @return  array
     */
    public function current()
    {
        if (is_array($this->data)) {
            return array(
                'login' => $this->data['login'],
                'firstname' => '',
                'lastname' => ''
            );
        } elseif (count($this->users_stack) > 0) {
            return array(
                'login' => array_shift($this->users_stack),
                'firstname' => '',
                'lastname' => ''
            );
        }
    }

    /**
     * "Key" implementation of iterator interface
     * @return  boolean true/false
     */
    public function key()
    {
        if (is_array($this->data)) {
            return $this->data['login'];
        } elseif (count($this->users_stack) > 0) {
            return $this->users_stack[0];
        }
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $this->data = $this->db->fetchAssoc($this->res);
        if (
            is_array($this->data) &&
            (
                strpos($this->data['login'], ',') !== false ||
                strpos($this->data['login'], ';') !== false
            )
        ) {
            $parts = array_filter(array_map('trim', preg_split("/[ ]*[;,][ ]*/", trim($this->data['login']))));
            foreach ($parts as $part) {
                if (ilStr::strPos(ilStr::strToLower($part), ilStr::strToLower($this->term)) !== false) {
                    $this->users_stack[] = $part;
                }
            }
            if ($this->users_stack) {
                $this->data = null;
            }
        }
        return is_array($this->data) || count($this->users_stack) > 0;
    }
    
    

    /**
     * "Rewind "implementation of iterator interface
     */
    public function rewind()
    {
        if ($this->res) {
            $this->db->free($this->res);
            $this->res = null;
        }

        $query = "
			SELECT DISTINCT
				mail.rcp_to login
			FROM mail
			WHERE " . $this->db->like('mail.rcp_to', 'text', $this->quoted_term) . "
			AND sender_id = " . $this->db->quote($this->user_id, 'integer') . "
			AND mail.sender_id = mail.user_id";

        $this->res = $this->db->query($query);
    }
}
