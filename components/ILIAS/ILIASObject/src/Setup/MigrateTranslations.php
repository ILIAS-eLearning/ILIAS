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

namespace ILIAS\ILIASObject\Setup;

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class MigrateTranslations implements Migration
{
    private const TESTS_PER_STEP = 100;

    private \ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Remove Table for Content Translation Information & Move Information to Translations';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        if (!$this->db->tableExists('obj_content_master_lng')) {
            return;
        }
        $query_result = $this->db->query(
            'SELECT obj_id, master_lang, fallback_lang FROM obj_content_master_lng LIMIT ' . self::TESTS_PER_STEP
        );

        while (($row = $this->db->fetchObject($query_result)) !== null) {
            $this->db->update(
                'object_translation',
                [
                    'lang_base' => [\ilDBConstants::T_INTEGER, 1]
                ],
                [
                    'obj_id' => [\ilDBConstants::T_INTEGER, $row->obj_id],
                    'lang_code' => [\ilDBConstants::T_TEXT, $row->master_lang]
                ]
            );
            if ($row->fallback_lang !== null && $row->fallback_lang !== '') {
                $this->db->update(
                    'object_translation',
                    [
                        'lang_default' => [\ilDBConstants::T_INTEGER, 0]
                    ],
                    [
                        'obj_id' => [\ilDBConstants::T_INTEGER, $row->obj_id]
                    ]
                );
                $this->db->update(
                    'object_translation',
                    [
                        'lang_default' => [\ilDBConstants::T_INTEGER, 1]
                    ],
                    [
                        'obj_id' => [\ilDBConstants::T_INTEGER, $row->obj_id],
                        'lang_code' => [\ilDBConstants::T_TEXT, $row->fallback_lang]
                    ]
                );
            }
            $this->db->manipulate(
                "DELETE FROM obj_content_master_lng WHERE obj_id = {$row->obj_id}"
            );
        }

        if ($this->getRemainingAmountOfSteps() === 0) {
            $this->db->dropTable('obj_content_master_lng');
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        if (!$this->db->tableExists('obj_content_master_lng')) {
            return 0;
        }

        return ((int) ceil(
            $this->db->fetchObject(
                $this->db->query('
                SELECT DISTINCT COUNT(obj_id) as cnt
                FROM obj_content_master_lng
            ')
            )->cnt / self::TESTS_PER_STEP
        )) + 1;
    }
}
