<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Stefan Meyer <meyer@leifos.com>
 *
 */
class ilFormatMail extends ilMail
{
    public function __construct(int $a_user_id)
    {
        parent::__construct($a_user_id);
    }

    public function formatReplyMessage() : string
    {
        if (empty($this->mail_data)) {
            return '';
        }

        $bodylines = preg_split("/\r\n|\n|\r/", $this->mail_data['m_message']);
        foreach ($bodylines as $i => $iValue) {
            $bodylines[$i] = '> ' . $iValue;
        }

        return $this->mail_data['m_message'] = implode(chr(10), $bodylines);
    }

    public function formatReplySubject() : string
    {
        if (empty($this->mail_data)) {
            return '';
        }

        return $this->mail_data['m_subject'] = 'RE: ' . $this->mail_data['m_subject'];
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
            $to = trim($to);
            if ($to !== '' && $currentUserLogin !== $to) {
                $newCC[] = $to;
            }
        }

        foreach (explode(',', $this->mail_data['rcp_cc']) as $cc) {
            $cc = trim($cc);
            if ($cc !== '' && $currentUserLogin !== $cc) {
                $newCC[] = $cc;
            }
        }

        return $this->mail_data['rcp_cc'] = implode(', ', $newCC);
    }

    public function formatReplyRecipient() : string
    {
        if (empty($this->mail_data)) {
            return '';
        }

        $user = new ilObjUser((int) $this->mail_data['sender_id']);
        return $this->mail_data['rcp_to'] = $user->getLogin();
    }

    public function formatForwardSubject() : string
    {
        if (empty($this->mail_data)) {
            return '';
        }

        return $this->mail_data['m_subject'] = '[FWD: ' . $this->mail_data['m_subject'] . ']';
    }

    /**
     * @param string[] $a_names
     * @param string $a_type
     * @return array
     */
    public function appendSearchResult(array $a_names, string $a_type) : array
    {
        $name_str = implode(',', $a_names);

        $key = 'rcp_to';
        if ('cc' === $a_type) {
            $key = 'rcp_cc';
        } elseif ('bcc' === $a_type) {
            $key = 'rcp_bcc';
        }

        if (!isset($this->mail_data[$key]) || !is_string($this->mail_data[$key])) {
            $this->mail_data[$key] = '';
        } else {
            $this->mail_data[$key] = trim($this->mail_data[$key]);
        }

        if ($this->mail_data[$key] !== '') {
            $this->mail_data[$key] .= ',';
        }
        $this->mail_data[$key] .= $name_str;

        return $this->mail_data;
    }

    public function formatLinebreakMessage(string $message) : string
    {
        $formatted = [];

        $linebreak = $this->mail_options->getLinebreak();

        $lines = explode(chr(10), $message);
        foreach ($lines as $iValue) {
            if (strpos($iValue, '>') !== 0) {
                $formatted[] = wordwrap($iValue, $linebreak, chr(10));
            } else {
                $formatted[] = $iValue;
            }
        }
        return implode(chr(10), $formatted);
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

        return $this->mail_options->getSignature() .
            chr(13) .
            chr(10) .
            chr(13) .
            chr(10) .
            $message;
    }
}
