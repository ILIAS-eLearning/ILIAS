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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Test\Settings\SettingsFactory;

class PersonalSettingsDatabaseRepository implements PersonalSettingsRepository
{
    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly \ilObjUser $user,
        private readonly SettingsFactory $factory,
    ) {
    }

    /**
     * @return array<int, PersonalSettingsTemplate>
     */
    public function getForUser(?Range $range = null, ?Order $order = null): array
    {
        $query = "SELECT * FROM tst_test_defaults WHERE user_fi = %s ";

        if ($order === null) {
            $order = new Order('name', Order::ASC);
        }
        $query .= $order->join('ORDER BY', fn(...$o) => implode(' ', $o));

        if ($range !== null) {
            $query .= " LIMIT {$range->getLength()} OFFSET {$range->getStart()}";
        }

        $stmt = $this->db->queryF($query, [\ilDBConstants::T_INTEGER], [$this->user->getId()]);

        $templates = [];
        while ($row = $this->db->fetchAssoc($stmt)) {
            $templates[$row['test_defaults_id']] = $this->factory->createTemplateFromDBRow($row);
        }
        return $templates;
    }

    public function countForUser(): int
    {
        $stmt = $this->db->queryF(
            "SELECT COUNT(*) as cnt FROM tst_test_defaults WHERE user_fi = %s",
            [\ilDBConstants::T_INTEGER],
            [$this->user->getId()]
        );
        return (int) $this->db->fetchAssoc($stmt)['cnt'];
    }

    /**
     * @param list<int> $ids
     * @return array<int, PersonalSettingsTemplate>
     */
    public function getByIds(array $ids): array
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

    public function getById(int $id): ?PersonalSettingsTemplate
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

    public function create(
        string $name,
        string $description,
        string $author,
        ?\DateTimeImmutable $timestamp = null
    ): PersonalSettingsTemplate {
        // 1. Create new blank settings (required as a foreign key)
        $new_settings_id = $this->db->nextId('tst_test_settings');
        $this->db->insert(
            'tst_test_settings',
            [
                'id' => [\ilDBConstants::T_INTEGER, $new_settings_id],
            ]
        );

        // 2. Instantiate template
        $template = new PersonalSettingsTemplate(
            $this->db->nextId('tst_test_defaults'),
            $this->user->getId(),
            $name,
            $description,
            $author,
            $timestamp ?? \DateTimeImmutable::createFromFormat('U', (string) time()),  // this will be UTC
            $new_settings_id
        );

        // 3. Store template in the database
        $this->db->insert(
            'tst_test_defaults',
            [
                'test_defaults_id' => [\ilDBConstants::T_INTEGER, $template->getId()],
                'user_fi' => [\ilDBConstants::T_INTEGER, $template->getUserId()],
                'name' => [\ilDBConstants::T_TEXT, $template->getName()],
                'description' => [\ilDBConstants::T_TEXT, $template->getDescription()],
                'author' => [\ilDBConstants::T_TEXT, $template->getAuthor()],
                'tstamp' => [\ilDBConstants::T_INTEGER, $template->getCreatedAt()->getTimestamp()],
                'settings_id' => [\ilDBConstants::T_INTEGER, $template->getSettingsId()],
            ]
        );

        return $template;
    }

    public function delete(PersonalSettingsTemplate $template): void
    {
        $this->db->manipulateF(
            "DELETE FROM tst_test_defaults WHERE test_defaults_id = %s",
            [\ilDBConstants::T_INTEGER],
            [$template->getId()]
        );

        $this->db->manipulateF(
            "DELETE FROM tst_test_settings WHERE id = %s",
            [\ilDBConstants::T_INTEGER],
            [$template->getSettingsId()]
        );
    }

    /**
     * @return int[]
     */
    public function lookupMarkSteps(int $template_id): array
    {
        $stmt = $this->db->queryF(
            "SELECT mark_id FROM tst_defaults_marks WHERE tst_defaults_marks.defaults_id = %s",
            [\ilDBConstants::T_INTEGER],
            [$template_id]
        );
        return array_column($this->db->fetchAll($stmt), 'mark_id');
    }

    /**
     * @param int[] $mark_ids
     */
    public function associateMarkSteps(int $template_id, array $mark_ids): void
    {
        foreach ($mark_ids as $mark_id) {
            $this->db->insert(
                'tst_defaults_marks',
                [
                    'defaults_id' => [\ilDBConstants::T_INTEGER, $template_id],
                    'mark_id' => [\ilDBConstants::T_INTEGER, $mark_id],
                ]
            );
        }
    }

    /**
     * @param int[] $mark_ids
     */
    public function detachMarkSteps(int $template_id, array $mark_ids): void
    {
        $in_marks = $this->db->in('mark_id', $mark_ids, false, \ilDBConstants::T_INTEGER);
        $this->db->manipulate("DELETE FROM tst_defaults_marks WHERE {$in_marks} AND defaults_id = {$template_id}");
    }
}
