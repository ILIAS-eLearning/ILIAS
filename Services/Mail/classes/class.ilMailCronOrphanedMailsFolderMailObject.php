<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailsFolderMailObject
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsFolderMailObject
{
    protected int $mail_id = 0;
    protected string $mail_subject = '';

    /**
     * @param $mail_id
     * @param $mail_subject
     */
    public function __construct(int $mail_id, string $mail_subject)
    {
        $this->setMailId($mail_id);
        $this->setMailSubject($mail_subject);
    }

    /**
     * @return int
     */
    public function getMailId() : int
    {
        return $this->mail_id;
    }

    /**
     * @param int $mail_id
     */
    public function setMailId(int $mail_id) : void
    {
        $this->mail_id = $mail_id;
    }

    /**
     * @return string
     */
    public function getMailSubject() : string
    {
        return $this->mail_subject;
    }

    /**
     * @param string $mail_subject
     */
    public function setMailSubject(string $mail_subject) : void
    {
        $this->mail_subject = $mail_subject;
    }
}
