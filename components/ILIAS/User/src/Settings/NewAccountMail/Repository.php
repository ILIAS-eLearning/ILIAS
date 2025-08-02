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

declare(strict_types=1);

namespace ILIAS\User\Settings\NewAccountMail;

class Repository
{
    private const TABLE_NAME = 'mail_template';
    private const TYPE = 'nacc';

    public function __construct(
        private readonly \ilDBInterface $db
    ) {
    }

    public function getFor(string $lang_code): Mail
    {
        $result_object = $this->db->fetchObject(
            $this->db->query(
                'SELECT * FROM ' . self::TABLE_NAME . ' WHERE type = "' . self::TYPE
                    . '" AND lang = "' . $lang_code . '"'
            )
        );

        if ($result_object === null) {
            return new MailImplementation($lang_code);
        }

        return new MailImplementation(
            $result_object->lang,
            trim($result_object->subject),
            trim($result_object->body),
            trim($result_object->sal_g),
            trim($result_object->sal_m),
            trim($result_object->sal_f),
            $result_object->att_rid,
            $result_object->att_file ?? null
        );
    }

    public function store(Mail $account_mail): void
    {
        $this->db->replace(
            self::TABLE_NAME,
            [
                'lang' => [\ilDBConstants::T_TEXT, $account_mail->getLangCode()],
                'type' => [\ilDBConstants::T_TEXT, self::TYPE]
            ],
            $account_mail->toStorage()
        );
    }
}
