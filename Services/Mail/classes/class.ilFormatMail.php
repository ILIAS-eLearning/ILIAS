<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @author	Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/

class ilFormatMail extends ilMail
{
    public function __construct(int $a_user_id)
    {
        parent::__construct($a_user_id);
    }

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

    public function formatReplySubject()
    {
        if (empty($this->mail_data)) {
            return false;
        }
        return $this->mail_data["m_subject"] = "RE: " . $this->mail_data["m_subject"];
    }

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

    public function formatReplyRecipient()
    {
        if (empty($this->mail_data)) {
            return false;
        }

        $user = new ilObjUser($this->mail_data["sender_id"]);
        return $this->mail_data["rcp_to"] = $user->getLogin();
    }

    public function formatForwardSubject()
    {
        if (empty($this->mail_data)) {
            return false;
        }
        return $this->mail_data["m_subject"] = "[FWD: " . $this->mail_data["m_subject"] . "]";
    }

    /**
    * @param string[] $a_names names to append
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
            if (strpos($iValue, '>') !== 0) {
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
        $message = $this->mail_options->getSignature() .
            chr(13) .
            chr(10) .
            chr(13) .
            chr(10) .
            $message;

        return $message;
    }
}
