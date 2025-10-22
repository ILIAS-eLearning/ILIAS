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

use ILIAS\Test\Scoring\Marks\MarkSchema;
use ILIAS\Test\Scoring\Marks\MarkSchemaFactory;
use ILIAS\Test\Scoring\Marks\MarksRepository;
use ILIAS\Test\Settings\MainSettings\MainSettings;
use ILIAS\Test\Settings\MainSettings\MainSettingsRepository;
use ILIAS\Test\Settings\ScoreReporting\ScoreSettings;
use ILIAS\Test\Settings\ScoreReporting\ScoreSettingsRepository;
use ILIAS\Test\Settings\SettingsFactory;

class PersonalSettingsRepository
{
    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly \ilObjUser $user,
        private readonly SettingsFactory $factory,
        private readonly MarkSchemaFactory $marks_factory,
        private readonly MainSettingsRepository $main_settings_repository,
        private readonly ScoreSettingsRepository $score_settings_repository,
        private readonly MarksRepository $marks_repository,
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

        $templates = [];
        while ($row = $this->db->fetchAssoc($stmt)) {
            $templates[$row['test_defaults_id']] = $this->factory->createTemplateFromDBRow($row);
        }
        return $templates;
    }

    /**
     * @param list<int> $ids
     * @return array<int, PersonalSettingsTemplate>
     */
    public function getTemplatesByIds(array $ids): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM tst_test_defaults WHERE {$this->db->in('test_defaults_id', $ids, false, \ilDBConstants::T_INTEGER)}",
        );

        $templates = [];
        while ($row = $this->db->fetchAssoc($stmt)) {
            $templates[$row['test_defaults_id']] = $this->factory->createTemplateFromDBRow($row);
        }
        return $templates;
    }

    public function getTemplateById(int $id): ?PersonalSettingsTemplate
    {
        $stmt = $this->db->queryF(
            "SELECT * FROM tst_test_defaults WHERE test_defaults_id = %s",
            [\ilDBConstants::T_INTEGER],
            [$id]
        );

        if ($row = $this->db->fetchAssoc($stmt)) {
            return $this->factory->createTemplateFromDBRow($row);
        }
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(int $template_id): array
    {
        $stmt = $this->db->queryF(
            "SELECT tst_test_settings.* FROM tst_test_settings 
                INNER JOIN tst_test_defaults ON tst_test_settings.id = tst_test_defaults.settings_id
                WHERE tst_test_defaults.test_defaults_id = %s",
            [\ilDBConstants::T_INTEGER],
            [$template_id]
        );
        return $this->db->fetchAssoc($stmt);
    }

    public function getMarkSchema(int $template_id): MarkSchema
    {
        $stmt = $this->db->queryF(
            "SELECT tst_mark.* FROM tst_mark INNER JOIN tst_defaults_marks ON tst_mark.mark_id = tst_defaults_marks.mark_id WHERE tst_defaults_marks.defaults_id = %s",
            [\ilDBConstants::T_INTEGER],
            [$template_id]
        );
        return $this->marks_factory->createMarkSchemaFromDBRow($this->db->fetchAll($stmt), -1);
    }

    public function applyTemplate(int $test_id, int $template_id): void
    {
        $stmt = $this->db->queryF(
            "SELECT settings_id FROM tst_tests WHERE test_id = %s",
            [\ilDBConstants::T_INTEGER],
            [$test_id]
        );
        $test_settings_id = $this->db->fetchAssoc($stmt)['settings_id'];

        $settings_data = $this->getSettings($template_id);
        $main_settings = $this->factory->createMainSettingsFromDBRow($settings_data)->withId($test_settings_id);
        $score_settings = $this->factory->createScoreSettingsFromDBRow($settings_data)->withId($test_settings_id);
        $mark_schema = $this->getMarkSchema($template_id)->withTestId($test_id);

        $this->main_settings_repository->store($main_settings);
        $this->score_settings_repository->store($score_settings);
        $this->marks_repository->storeMarkSchema($mark_schema);
    }

    public function createTemplateFor(int $test_id, string $name, string $description, string $author): PersonalSettingsTemplate
    {
        $template = new PersonalSettingsTemplate(
            $this->db->nextId('tst_test_defaults'),
            $this->user->getId(),
            $name,
            $description,
            $author,
            \DateTimeImmutable::createFromFormat('U', (string) time())
        );

        $this->createTemplate(
            $template,
            $this->main_settings_repository->getFor($test_id),
            $this->score_settings_repository->getFor($test_id),
            $this->marks_repository->getMarkSchemaFor($test_id),
        );

        return $template;
    }

    public function createTemplate(
        PersonalSettingsTemplate $template,
        MainSettings $main_settings,
        ScoreSettings $score_settings,
        MarkSchema $mark_schema
    ): void {
        // 1. Create new blank settings
        $new_settings_id = $this->db->nextId('tst_test_settings');
        $this->db->insert(
            'tst_test_settings',
            [
                'id' => [\ilDBConstants::T_INTEGER, $new_settings_id],
            ]
        );

        // 2. Store settings using their repositories
        $this->main_settings_repository->store($main_settings->withId($new_settings_id));
        $this->score_settings_repository->store($score_settings->withId($new_settings_id));

        // 3. Store template in database
        $template = $template->withId($this->db->nextId('tst_test_defaults'));
        $this->storeTemplate($template, $new_settings_id);

        // 4. Store marks in database and create references in tst_defaults_marks
        foreach ($mark_schema->getMarkSteps() as $mark_step) {
            $new_mark_id = $this->db->nextId('tst_mark');

            $mark_row = $mark_step->toStorage();
            $mark_row['mark_id'] = [\ilDBConstants::T_INTEGER, $new_mark_id];
            $mark_row['test_fi'] = [\ilDBConstants::T_INTEGER, 0];

            $this->db->insert('tst_mark', $mark_row);
            $this->db->insert(
                'tst_defaults_marks',
                [
                    'defaults_id' => [\ilDBConstants::T_INTEGER, $template->getId()],
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
        $mark_ids = array_map(static fn(array $row): int => $row['mark_id'], $this->db->fetchAll($stmt));

        $in_marks = $this->db->in('mark_id', $mark_ids, false, \ilDBConstants::T_INTEGER);
        $this->db->manipulate("DELETE FROM tst_defaults_marks WHERE {$in_marks}");
        $this->db->manipulate("DELETE FROM tst_mark WHERE {$in_marks}");

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

    private function storeTemplate(PersonalSettingsTemplate $template, int $settings_id): void
    {
        $this->db->insert(
            'tst_test_defaults',
            [
                'test_defaults_id' => [\ilDBConstants::T_INTEGER, $template->getId()],
                'user_fi' => [\ilDBConstants::T_INTEGER, $template->getUserId()],
                'name' => [\ilDBConstants::T_TEXT, $template->getName()],
                'description' => [\ilDBConstants::T_TEXT, $template->getDescription()],
                'author' => [\ilDBConstants::T_TEXT, $template->getAuthor()],
                'tstamp' => [\ilDBConstants::T_INTEGER, $template->getCreatedAt()->getTimestamp()],
                'settings_id' => [\ilDBConstants::T_INTEGER, $settings_id],
            ]
        );
    }
}
