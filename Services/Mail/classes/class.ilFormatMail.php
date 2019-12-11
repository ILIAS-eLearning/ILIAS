<?php
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
    public function __construct($a_user_id)
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
        for ($i = 0; $i < count($bodylines); $i++) {
            $bodylines[$i] = "> " . $bodylines[$i];
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
    * @access	public
    * @return string
    */
    public function formatReplyRecipientsForCC()
    {
        global $DIC;

        if (empty($this->mail_data)) {
            return '';
        }

        $newCC = array();

        $currentUserLogin = $DIC->user()->getLogin();

        foreach (explode(',', $this->mail_data['rcp_to']) as $to) {
            if (trim($to) != '' && $currentUserLogin != trim($to)) {
                $newCC[] = trim($to);
            }
        }

        foreach (explode(',', $this->mail_data['rcp_cc']) as $cc) {
            if (trim($cc) != '' && $currentUserLogin != trim($cc)) {
                $newCC[] = trim($cc);
            }
        }

        return ($this->mail_data['rcp_cc'] = implode(', ', $newCC));
    }
    
    /**
    * get reply recipient
    * @access	public
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
    * @access	public
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
    * @access	public
    * @param array names to append
    * @param string rcp type ('to','cc','bc')
    * @return string
    */
    public function appendSearchResult($a_names, $a_type)
    {
        if (empty($this->mail_data)) {
            return false;
        }
        $name_str = implode(',', $a_names);
        switch ($a_type) {
            case 'to':
                $this->mail_data["rcp_to"] = trim($this->mail_data["rcp_to"]);
                if ($this->mail_data["rcp_to"]) {
                    $this->mail_data["rcp_to"] = $this->mail_data["rcp_to"] . ",";
                }
                $this->mail_data["rcp_to"] = $this->mail_data["rcp_to"] . $name_str;
                break;

            case 'cc':
                $this->mail_data["rcp_cc"] = trim($this->mail_data["rcp_cc"]);
                if ($this->mail_data["rcp_cc"]) {
                    $this->mail_data["rcp_cc"] = $this->mail_data["rcp_cc"] . ",";
                }
                $this->mail_data["rcp_cc"] = $this->mail_data["rcp_cc"] . $name_str;
                break;

            case 'bc':
                $this->mail_data["rcp_bcc"] = trim($this->mail_data["rcp_bcc"]);
                if ($this->mail_data["rcp_bcc"]) {
                    $this->mail_data["rcp_bcc"] = $this->mail_data["rcp_bcc"] . ",";
                }
                $this->mail_data["rcp_bcc"] = $this->mail_data["rcp_bcc"] . $name_str;
                break;

        }
        return $this->mail_data;
    }
    /**
    * format message according to linebreak option
    * @param string message
    * @access	public
    * @return string formatted message
    */
    public function formatLinebreakMessage($a_message)
    {
        $formatted = array();

        $linebreak = $this->mail_options->getLinebreak();

        $lines = explode(chr(10), $a_message);
        for ($i=0;$i<count($lines);$i++) {
            if (substr($lines[$i], 0, 1) != '>') {
                $formatted[] = wordwrap($lines[$i], $linebreak, chr(10));
            } else {
                $formatted[] = $lines[$i];
            }
        }
        $formatted = implode(chr(10), $formatted);

        return $formatted;
    }
                    
                

    /**
    * append signature to mail body
    * @access	public
    * @return string
    */
    public function appendSignature()
    {
        return $this->mail_data["m_message"] .= chr(13) . chr(10) . $this->mail_options->getSignature();
    }

    /**
     * @return string
     */
    public function prependSignature()
    {
        return $this->mail_options->getSignature() . chr(13) . chr(10) . chr(13) . chr(10) . $this->mail_data["m_message"];
    }
} // END class.ilFormatMail
