<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailsFolderMailObject
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsFolderMailObject
{
    /**
     * @var int
     */
    protected $mail_id = 0;

    /**
     * @var string
     */
    protected $mail_subject = '';

    /**
     * @param $mail_id
     * @param $mail_subject
     */
    public function __construct($mail_id, $mail_subject)
    {
        $this->setMailId($mail_id);
        $this->setMailSubject($mail_subject);
    }

    /**
     * @return int
     */
    public function getMailId()
    {
        return $this->mail_id;
    }

    /**
     * @param int $mail_id
     */
    public function setMailId($mail_id)
    {
        $this->mail_id = $mail_id;
    }

    /**
     * @return string
     */
    public function getMailSubject()
    {
        return $this->mail_subject;
    }

    /**
     * @param string $mail_subject
     */
    public function setMailSubject($mail_subject)
    {
        $this->mail_subject = $mail_subject;
    }
}
