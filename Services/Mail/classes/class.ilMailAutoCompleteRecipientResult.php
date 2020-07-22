<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Search/classes/class.ilSearchSettings.php';

/**
 * Class ilMailAutoCompleteRecipientResult
 */
class ilMailAutoCompleteRecipientResult
{
    const MODE_STOP_ON_MAX_ENTRIES = 1;
    const MODE_FETCH_ALL = 2;

    const MAX_RESULT_ENTRIES = 1000;

    protected $allow_smtp = null;
    protected $user_id = null;
    protected $handled_recipients = array();
    protected $mode = self::MODE_STOP_ON_MAX_ENTRIES;
    protected $max_entries = null;
    public $result = array();

    /**
     * @param int $mode
     */
    public function __construct($mode)
    {
        global $DIC;

        $this->allow_smtp = $DIC->rbac()->system()->checkAccess('smtp_mail', MAIL_SETTINGS_ID);
        $this->user_id = $DIC->user()->getId();
        $this->max_entries = ilSearchSettings::getInstance()->getAutoCompleteLength();
        
        $this->result['items'] = array();
        $this->result['hasMoreResults'] = false;

        $this->initMode($mode);
    }

    /**
     * @param int $mode
     * @throws InvalidArgumentException
     */
    protected function initMode($mode)
    {
        if (!in_array($mode, array(self::MODE_FETCH_ALL, self::MODE_STOP_ON_MAX_ENTRIES))) {
            throw new InvalidArgumentException("Wrong mode passed!");
        }
        $this->mode = $mode;
    }

    /**
     * @return bool
     */
    public function isResultAddable()
    {
        if (
            $this->mode == self::MODE_STOP_ON_MAX_ENTRIES &&
            $this->max_entries >= 0 && count($this->result['items']) >= $this->max_entries
        ) {
            return false;
        } elseif (
            $this->mode == self::MODE_FETCH_ALL &&
            count($this->result['items']) >= self::MAX_RESULT_ENTRIES
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param string $login
     * @param string $firstname
     * @param string $lastname
     */
    public function addResult($login, $firstname, $lastname)
    {
        if (!isset($this->handled_recipients[$login])) {
            $recipient = array();
            $recipient['value'] = $login;

            $label = $login;
            if ($firstname && $lastname) {
                $label .= " [" . $firstname . ", " . $lastname . "]";
            }
            $recipient['label'] = $label;

            $this->result['items'][] = $recipient;
            $this->handled_recipients[$login] = 1;
        }
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->result;
    }

    /**
     * @return int
     */
    public function numItems()
    {
        return (int) count($this->result['items']);
    }
}
