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

namespace ILIAS\User\Setup;

use ILIAS\User\Settings\NewAccountMail\Stakeholder;
use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;

class MigrateNewAccountAttachments implements Migration
{
    private ?\ilResourceStorageMigrationHelper $helper = null;
    private int $root_user_id;

    public function getLabel(): string
    {
        return 'Migrate New Account Mail Attachments to IRSS';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 15;
    }

    public function getPreconditions(Environment $environment): array
    {
        return \ilResourceStorageMigrationHelper::getPreconditions();
    }

    public function prepare(Environment $environment): void
    {
        $this->helper = new \ilResourceStorageMigrationHelper(
            new Stakeholder(),
            $environment
        );
        $this->root_user_id = $this->helper->getDatabase()->fetchObject(
            $this->helper->getDatabase()->query(
                'SELECT usr_id FROM usr_data WHERE login="root"'
            )
        )?->usr_id ?? 6;
    }

    public function step(Environment $environment): void
    {
        $db = $this->helper->getDatabase();
        $res = $db->fetchObject(
            $db->query(
                'SELECT
                    att_file, lang
                FROM mail_template
                WHERE att_file IS NOT NULL AND type = "nacc" LIMIT 1;'
            )
        );

        $path = '/' . implode(
            '/',
            array_map(
                static fn(string $path_part): string => trim($path_part, '/'),
                [
                    $this->helper->getClientDataDir() . '/ilReg/reg_7',
                    $res->lang,
                ]
            )
        );

        $rid = null;

        if (file_exists($path)) {
            $rid = $this->helper->movePathToStorage(
                $path,
                $this->root_user_id,
                static fn() => $res->att_file,
                static fn() => $res->att_file
            );
        }
        if ($rid !== null) {
            $rid = $rid->serialize();
        }

        $db->update(
            'mail_template',
            [
                'att_file' => [\ilDBConstants::T_TEXT, null],
                'att_rid' => [\ilDBConstants::T_TEXT, $rid]
            ],
            [
                'lang' => [\ilDBConstants::T_TEXT, $res->lang],
                'type' => [\ilDBConstants::T_TEXT, 'nacc'],
            ]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        return $this->helper->getDatabase()->fetchObject(
            $this->helper->getDatabase()->query(
                'SELECT COUNT(*) cnt FROM mail_template WHERE att_file IS NOT NULL AND type = "nacc"'
            )
        )->cnt;
    }
}
