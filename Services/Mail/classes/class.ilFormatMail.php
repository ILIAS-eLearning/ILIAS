<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class UserMail
* this class handles user mails
*
*
* @author	Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/
include_once "Services/Mail/classes/class.ilMail.php";

class ilFormatMail extends ilMail
{

    /**
    * Constructor
    * setup an mail object
    * @param int user_id
    * @access	public
    */
    public function __construct(int $a_user_id)
    {
        parent::__construct($a_user_id);
    }

    /**
    * format a reply message
    * @access	public
    * @return string
    */
    public function formatReplyMessage()
    {
        if (empty($this->mail_data)) {
            return false;
        }

        $bodylines = preg_split("/\r\n|\n|\r/", $this->mail_data["m_message"]);
        foreach ($bodylines as $i => $iValue) {
            $bodylines[$i] = "> " . $iValue;
        }

        return $this->mail_data["m_message"] = implode(chr(10), $bodylines);
    }

    /**
    * format a reply subject
    * @access	public
    * @return string
    */
    public function formatReplySubject()
    {
        if (empty($this->mail_data)) {
            return false;
        }
        return $this->mail_data["m_subject"] = "RE: " . $this->mail_data["m_subject"];
    }
    
    /**
    * get reply recipients for cc
    */
    public function formatReplyRecipientsForCC() : string
    {
        global $DIC;

        if (empty($this->mail_data)) {
            return '';
        }

        $newCC = [];

        $currentUserLogin = $DIC->user()->getLogin();

        foreach (explode(',', $this->mail_data['rcp_to']) as $to) {
            if (trim($to) !== '' && $currentUserLogin !== trim($to)) {
                $newCC[] = trim($to);
            }
        }

        foreach (explode(',', $this->mail_data['rcp_cc']) as $cc) {
            if (trim($cc) !== '' && $currentUserLogin !== trim($cc)) {
                $newCC[] = trim($cc);
            }
        }

        return ($this->mail_data['rcp_cc'] = implode(', ', $newCC));
    }
    
    /**
    * get reply recipient
    * @return string
    */
    public function formatReplyRecipient()
    {
        if (empty($this->mail_data)) {
            return false;
        }

        require_once './Services/User/classes/class.ilObjUser.php';

        $user = new ilObjUser($this->mail_data["sender_id"]);
        return $this->mail_data["rcp_to"] = $user->getLogin();
    }
    /**
    * format a forward subject
    * @return string
    */
    public function formatForwardSubject()
    {
        if (empty($this->mail_data)) {
            return false;
        }
        return $this->mail_data["m_subject"] = "[FWD: " . $this->mail_data["m_subject"] . "]";
    }

    /**
    * append search result to recipient
    * @param string[] names to append
    * @param string rcp type ('to','cc','bc')
    */
    public function appendSearchResult(array $a_names, string $a_type) : array
    {
        $name_str = implode(',', $a_names);
        switch ($a_type) {
            case 'to':
                if ($this->mail_data["rcp_to"]) {
                    $this->mail_data["rcp_to"] = trim($this->mail_data["rcp_to"]);
                    $this->mail_data["rcp_to"] .= ",";
                }
                $this->mail_data["rcp_to"] .= $name_str;
                break;

            case 'cc':
                if ($this->mail_data["rcp_cc"]) {
                    $this->mail_data["rcp_cc"] = trim($this->mail_data["rcp_cc"]);
                    $this->mail_data["rcp_cc"] .= ",";
                }
                $this->mail_data["rcp_cc"] .= $name_str;
                break;

            case 'bc':
                if ($this->mail_data["rcp_bcc"]) {
                    $this->mail_data["rcp_bcc"] = trim($this->mail_data["rcp_bcc"]);
                    $this->mail_data["rcp_bcc"] .= ",";
                }
                $this->mail_data["rcp_bcc"] .= $name_str;
                break;

        }

        return $this->mail_data;
    }

    
    public function formatLinebreakMessage(string $message) : string
    {
        $formatted = [];

        $linebreak = $this->mail_options->getLinebreak();

        $lines = explode(chr(10), $message);
        foreach ($lines as $i => $iValue) {
            if (!str_starts_with($iValue, '>')) {
                $formatted[] = wordwrap($iValue, $linebreak, chr(10));
            } else {
                $formatted[] = $iValue;
            }
        }
        $formatted = implode(chr(10), $formatted);

        return $formatted;
    }

    
    public function appendSignature() : string
    {
        $message = (string) ($this->mail_data['m_message'] ?? '');
        $message .= chr(13) . chr(10) . $this->mail_options->getSignature();

        return $message;
    }

    
    public function prependSignature() : string
    {
        $message = (string) ($this->mail_data['m_message'] ?? '');
        $message = $this->mail_options->getSignature() . chr(13) . chr(10) . chr(13) . chr(10) . $message;

        return $message;
    }
} // END class.ilFormatMail
