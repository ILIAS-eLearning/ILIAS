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

namespace ILIAS\MetaData\Copyright;

use ILIAS\Data\URI;
use ILIAS\MetaData\Copyright\Database\WrapperInterface;

class DatabaseRepository implements RepositoryInterface
{
    protected WrapperInterface $db_wrapper;

    public function __construct(WrapperInterface $db_wrapper)
    {
        $this->db_wrapper = $db_wrapper;
    }

    public function getEntry(int $id): EntryInterface
    {
        $rows = $this->db_wrapper->query(
            'SELECT * FROM il_md_cpr_selections WHERE entry_id = ' .
            $this->db_wrapper->quoteInteger($id)
        );

        foreach ($rows as $row) {
            return $this->entryFromRow($row);
        }
        return new NullEntry();
    }

    public function getAllEntries(): \Generator
    {
        $rows = $this->db_wrapper->query(
            'SELECT * FROM il_md_cpr_selections
            ORDER BY is_default DESC, position ASC'
        );

        foreach ($rows as $row) {
            yield $this->entryFromRow($row);
        }
    }

    public function getActiveEntries(): \Generator
    {
        $rows = $this->db_wrapper->query(
            'SELECT * FROM il_md_cpr_selections WHERE outdated = 0
            ORDER BY is_default DESC, position ASC'
        );

        foreach ($rows as $row) {
            yield $this->entryFromRow($row);
        }
    }

    public function getDefaultEntry(): EntryInterface
    {
        $rows = $this->db_wrapper->query(
            'SELECT * FROM il_md_cpr_selections WHERE is_default = 1'
        );

        foreach ($rows as $row) {
            return $this->entryFromRow($row);
        }
        return new NullEntry();
    }

    protected function entryFromRow(array $row): EntryInterface
    {
        $data = new CopyrightData(
            (string) ($row['full_name'] ?? ''),
            !empty($row['link'] ?? '') ? $this->getURI((string) $row['link']) : null,
            !empty($row['image_link']) ? $this->getURI((string) $row['image_link']) : null,
            (string) ($row['image_file'] ?? ''),
            (string) ($row['alt_text'] ?? ''),
            $row['is_default'] ? true : false
        );

        return new Entry(
            (int) $row['entry_id'],
            (string) ($row['title'] ?? ''),
            (string) ($row['description'] ?? ''),
            $row['is_default'] ? true : false,
            $row['outdated'] ? true : false,
            (int) ($row['position'] ?? 0),
            $data
        );
    }

    protected function getURI(string $uri): URI
    {
        return new URI($uri);
    }

    public function deleteEntry(int $id): void
    {
        $this->db_wrapper->manipulate(
            'DELETE FROM il_md_cpr_selections WHERE entry_id = ' .
            $this->db_wrapper->quoteInteger($id)
        );
    }

    public function createEntry(
        string $title,
        string $description = '',
        bool $is_outdated = false,
        string $full_name = '',
        ?URI $link = null,
        URI|string $image = '',
        string $alt_text = ''
    ): int {
        $this->checkTitle($title);

        $next_id = $this->db_wrapper->nextId('il_md_cpr_selections');
        if (is_string($image)) {
            $image_link = '';
            $image_file = $image;
        } else {
            $image_link = (string) $image;
            $image_file = '';
        }

        $this->db_wrapper->insert(
            'il_md_cpr_selections',
            [
                'entry_id' => [\ilDBConstants::T_INTEGER, $next_id],
                'title' => [\ilDBConstants::T_TEXT, $title],
                'description' => [\ilDBConstants::T_TEXT, $description],
                'is_default' => [\ilDBConstants::T_INTEGER, 0],
                'outdated' => [\ilDBConstants::T_INTEGER, (int) $is_outdated],
                'position' => [\ilDBConstants::T_INTEGER, $this->getNextPosition()],
                'full_name' => [\ilDBConstants::T_TEXT, $full_name],
                'link' => [\ilDBConstants::T_TEXT, (string) $link],
                'image_link' => [\ilDBConstants::T_TEXT, $image_link],
                'image_file' => [\ilDBConstants::T_TEXT, $image_file],
                'alt_text' => [\ilDBConstants::T_TEXT, $alt_text],
                'migrated' => [\ilDBConstants::T_INTEGER, 1]
            ]
        );

        return $next_id;
    }

    protected function getNextPosition(): int
    {
        $rows = $this->db_wrapper->query(
            'SELECT MAX(position) AS max FROM il_md_cpr_selections WHERE is_default = 0'
        );
        foreach ($rows as $row) {
            return isset($row['max']) ? ((int) $row['max']) + 1 : 0;
        }
        return 0;
    }

    public function updateEntry(
        int $id,
        string $title,
        string $description = '',
        bool $is_outdated = false,
        string $full_name = '',
        ?URI $link = null,
        URI|string $image = '',
        string $alt_text = ''
    ): void {
        $this->checkTitle($title);

        if (is_string($image)) {
            $image_link = '';
            $image_file = $image;
        } else {
            $image_link = (string) $image;
            $image_file = '';
        }

        $this->db_wrapper->update(
            'il_md_cpr_selections',
            [
                'title' => [\ilDBConstants::T_TEXT, $title],
                'description' => [\ilDBConstants::T_TEXT, $description],
                'outdated' => [\ilDBConstants::T_INTEGER, (int) $is_outdated],
                'full_name' => [\ilDBConstants::T_TEXT, $full_name],
                'link' => [\ilDBConstants::T_TEXT, (string) $link],
                'image_link' => [\ilDBConstants::T_TEXT, $image_link],
                'image_file' => [\ilDBConstants::T_TEXT, $image_file],
                'alt_text' => [\ilDBConstants::T_TEXT, $alt_text]
            ],
            [
                'entry_id' => [\ilDBConstants::T_INTEGER, $id]
            ]
        );
    }

    protected function checkTitle(string $title): void
    {
        if ($title === '') {
            throw new \ilMDCopyrightException(
                'Copyright entries can not have an empty title'
            );
        }
    }

    public function reorderEntries(int ...$ids): void
    {
        $pos = 0;
        $default_id = $this->getDefaultID();
        foreach ($ids as $id) {
            if ($id === $default_id) {
                continue;
            }
            $this->updatePosition($id, $pos);
            $pos++;
        }
    }

    protected function getDefaultID(): int
    {
        $rows = $this->db_wrapper->query(
            'SELECT entry_id FROM il_md_cpr_selections WHERE is_default = 1'
        );

        foreach ($rows as $row) {
            return (int) ($row['entry_id'] ?? 0);
        }
        return 0;
    }

    protected function updatePosition(int $id, int $position): void
    {
        $this->db_wrapper->update(
            'il_md_cpr_selections',
            ['position' => [\ilDBConstants::T_INTEGER, $position]],
            ['entry_id' => [\ilDBConstants::T_INTEGER, $id]]
        );
    }
}
