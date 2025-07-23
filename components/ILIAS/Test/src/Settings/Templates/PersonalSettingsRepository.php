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

namespace ILIAS\Test\Settings\Templates;

class PersonalSettingsRepository
{
    public function __construct(
        protected \ilDBInterface $db,
        protected \ilObjUser $user,
    ) {
    }

    /**
     * @return array<int, PersonalSettingsTemplate>
     */
    public function getTemplatesForUser(): array
    {
        $stmt = $this->db->queryF(
            "SELECT * FROM tst_test_defaults WHERE user_fi = %s ORDER BY name ASC",
            [\ilDBConstants::T_INTEGER],
            [$this->user->getId()]
        );

        $defaults = [];
        while ($row = $this->db->fetchAssoc($stmt)) {
            $defaults[$row['test_defaults_id']] = self::toTemplate($row);
        }
        return $defaults;
    }

    /**
     * @param list<int> $ids
     * @return array<int, PersonalSettingsTemplate>
     */
    public function getTemplatesByIds(array $ids): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM tst_test_defaults WHERE " . $this->db->in('test_defaults_id', $ids, false, \ilDBConstants::T_INTEGER),
        );

        $defaults = [];
        while ($row = $this->db->fetchAssoc($stmt)) {
            $defaults[$row['test_defaults_id']] = self::toTemplate($row);
        }
        return $defaults;
    }

    public function applyTemplate(int $test_id, int $template_id): void
    {
        // 1. Update entry in 'tst_test_settings'
        $stmt = $this->db->queryF(
            "SELECT tst_test_settings.* FROM tst_test_settings 
                INNER JOIN tst_test_defaults ON tst_test_settings.id = tst_test_defaults.settings_id
                WHERE tst_test_defaults.test_defaults_id = %s",
            [\ilDBConstants::T_INTEGER],
            [$template_id]
        );
        $template_settings_row = $this->db->fetchAssoc($stmt);
        unset($template_settings_row['id']);

        $stmt = $this->db->queryF("SELECT settings_id FROM tst_tests WHERE test_id = %s", [\ilDBConstants::T_INTEGER], [$test_id]);
        $test_settings_id = $this->db->fetchAssoc($stmt)['settings_id'];

        $this->db->update(
            'tst_test_settings',
            self::createDBParams($template_settings_row),
            ['id' => [\ilDBConstants::T_INTEGER, $test_settings_id]]
        );

        // 2. Delete old mark schema
        $this->db->manipulateF(
            "DELETE FROM tst_mark WHERE test_fi = %s",
            [\ilDBConstants::T_INTEGER],
            [$test_id]
        );

        // 3. Create new entries in 'tst_mark'
        $stmt = $this->db->queryF(
            "SELECT tst_mark.* FROM tst_mark INNER JOIN tst_defaults_marks ON tst_mark.mark_id = tst_defaults_marks.mark_id WHERE tst_defaults_marks.defaults_id = %s",
            [\ilDBConstants::T_INTEGER],
            [$template_id]
        );
        $mark_rows = $this->db->fetchAll($stmt);
        foreach ($mark_rows as $mark_row) {
            $new_mark_id = $this->db->nextId('tst_mark');

            $mark_row['mark_id'] = $new_mark_id;
            $mark_row['test_fi'] = $test_id;
            $this->db->insert('tst_mark', self::createDBParams($mark_row));
        }
    }

    public function createTemplate(int $test_id, string $name): void
    {
        // 1. Duplicate entry in 'tst_test_settings'
        $new_settings_id = $this->cloneSettings($test_id);

        // 2. Create entry in 'tst_test_defaults'
        $new_defaults_id = $this->db->nextId('tst_test_defaults');
        $this->db->insert(
            'tst_test_defaults',
            [
                'test_defaults_id' => [\ilDBConstants::T_INTEGER, $new_defaults_id],
                'user_fi' => [\ilDBConstants::T_INTEGER, $this->user->getId()],
                'name' => [\ilDBConstants::T_TEXT, $name],
                'tstamp' => [\ilDBConstants::T_INTEGER, time()],
                'settings_id' => [\ilDBConstants::T_INTEGER, $new_settings_id],
            ]
        );

        // 3. Duplicate entries in 'tst_mark'
        $this->cloneMarks($test_id, $new_defaults_id);
    }

    private function cloneSettings(int $test_id): int
    {
        $new_settings_id = $this->db->nextId('tst_test_settings');

        $stmt = $this->db->queryF(
            "SELECT tst_test_settings.* FROM tst_test_settings 
                INNER JOIN tst_tests ON tst_test_settings.id = tst_tests.settings_id
                WHERE tst_tests.test_id = %s",
            [\ilDBConstants::T_INTEGER],
            [$test_id]
        );
        $settings_row = $this->db->fetchAssoc($stmt);

        $settings_row['id'] = $new_settings_id;
        $this->db->insert('tst_test_settings', self::createDBParams($settings_row));

        return $new_settings_id;
    }

    private function cloneMarks(int $test_id, int $new_defaults_id): void
    {
        $stmt = $this->db->queryF(
            "SELECT * FROM tst_mark WHERE test_fi = %s",
            [\ilDBConstants::T_INTEGER],
            [$test_id]
        );
        $mark_rows = $this->db->fetchAll($stmt);

        foreach ($mark_rows as $mark_row) {
            $new_mark_id = $this->db->nextId('tst_mark');

            $mark_row['mark_id'] = $new_mark_id;
            $this->db->insert('tst_mark', self::createDBParams($mark_row));

            $this->db->insert(
                'tst_defaults_marks',
                [
                    'defaults_id' => [\ilDBConstants::T_INTEGER, $new_defaults_id],
                    'mark_id' => [\ilDBConstants::T_INTEGER, $new_mark_id],
                ]
            );
        }
    }

    public function deleteTemplate(int $template_id): void
    {
        // 1. Delete marks in tst_mark and references in tst_defaults_marks
        $stmt = $this->db->queryF(
            "SELECT mark_id FROM tst_defaults_marks WHERE defaults_id = %s",
            [\ilDBConstants::T_INTEGER],
            [$template_id]
        );
        $mark_ids = array_map(fn($row) => $row['mark_id'], $this->db->fetchAll($stmt));

        $in_marks = $this->db->in('mark_id', $mark_ids, false, \ilDBConstants::T_INTEGER);
        $this->db->manipulate("DELETE FROM tst_defaults_marks WHERE $in_marks");
        $this->db->manipulate("DELETE FROM tst_mark WHERE $in_marks");

        // 2. Delete entries in tst_test_defaults and tst_test_settings
        $stmt = $this->db->queryF(
            "SELECT settings_id FROM tst_test_defaults WHERE test_defaults_id = %s",
            [\ilDBConstants::T_INTEGER],
            [$template_id]
        );
        $settings_id = $this->db->fetchAssoc($stmt)['settings_id'];

        $this->db->manipulateF(
            "DELETE FROM tst_test_defaults WHERE test_defaults_id = %s",
            [\ilDBConstants::T_INTEGER],
            [$template_id]
        );

        $this->db->manipulateF(
            "DELETE FROM tst_test_settings WHERE id = %s",
            [\ilDBConstants::T_INTEGER],
            [$settings_id]
        );
    }

    private static function toTemplate(array $row): PersonalSettingsTemplate
    {
        return new PersonalSettingsTemplate(
            $row['test_defaults_id'],
            $row['user_fi'],
            $row['name'],
            $row['description'] ?? '',
            $row['author'] ?? '',
            \DateTimeImmutable::createFromFormat('U', (string) $row['tstamp'])
        );
    }

    /**
     * This method is used to create an array with column types and values from an array with values, which can be used
     * to execute ilDBInterface::insert or ilDBInterface::update. It should be used when the column type is not known
     * but must be guessed based on the PHP type. This is important so that strings are escaped correctly.
     *
     * @param array<mixed> $values
     * @return array<string, mixed>
     */
    private static function createDBParams(array $values): array
    {
        return array_map(function ($value) {
            $type = is_string($value) ? \ilDBConstants::T_TEXT : 'unknown';
            return [$type, $value];
        }, $values);
    }
}
