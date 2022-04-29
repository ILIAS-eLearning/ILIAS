<?php declare(strict_types=1);

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
 * ilMailCronOrphanedMailsFolderMailObject
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsFolderMailObject
{
    protected int $mail_id = 0;
    protected string $mail_subject = '';

    public function __construct(int $mail_id, string $mail_subject)
    {
        $this->setMailId($mail_id);
        $this->setMailSubject($mail_subject);
    }

    public function getMailId() : int
    {
        return $this->mail_id;
    }

    public function setMailId(int $mail_id) : void
    {
        $this->mail_id = $mail_id;
    }

    public function getMailSubject() : string
    {
        return $this->mail_subject;
    }

    public function setMailSubject(string $mail_subject) : void
    {
        $this->mail_subject = $mail_subject;
    }
}
