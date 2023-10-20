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

class DatabaseRepository implements RepositoryInterface
{
    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function getEntry(int $id): EntryInterface
    {
        $res = $this->db->query(
            'SELECT * FROM il_md_cpr_selections WHERE entry_id = ' .
            $this->db->quote($id, \ilDBConstants::T_INTEGER)
        );

        if ($row = $this->db->fetchAssoc($res)) {
            return $this->entryFromRow($row);
        }
        return new NullEntry();
    }

    public function getAllEntries(): \Generator
    {
        $res = $this->db->query(
            'SELECT * FROM il_md_cpr_selections
            ORDER BY is_default DESC, position ASC'
        );

        while ($row = $this->db->fetchAssoc($res)) {
            yield $this->entryFromRow($row);
        }
    }

    public function getActiveEntries(): \Generator
    {
        $res = $this->db->query(
            'SELECT * FROM il_md_cpr_selections WHERE outdated = 0
            ORDER BY is_default DESC, position ASC'
        );

        while ($row = $this->db->fetchAssoc($res)) {
            yield $this->entryFromRow($row);
        }
    }

    public function getDefaultEntry(): EntryInterface
    {
        $res = $this->db->query(
            'SELECT * FROM il_md_cpr_selections WHERE is_default = 1'
        );

        if ($row = $this->db->fetchAssoc($res)) {
            return $this->entryFromRow($row);
        }
        return new NullEntry();
    }

    protected function entryFromRow(array $row): EntryInterface
    {
        $data = new CopyrightData(
            $row['full_name'] ?? '',
            !empty($row['link'] ?? '') ? $this->getURI($row['link']) : null,
            !empty($row['image_link']) ? $this->getURI($row['image_link']) : null,
            $row['image_file'] ?? '',
            $row['alt_text'] ?? '',
            $row['is_default'] ? true : false
        );

        return new Entry(
            $row['entry_id'],
            $row['title'] ?? '',
            $row['description'] ?? '',
            $row['is_default'] ? true : false,
            $row['outdated'] ? true : false,
            $row['position'] ?? 0,
            $data
        );
    }

    protected function getURI(string $uri): URI
    {
        return new URI($uri);
    }

    public function deleteEntry(int $id): void
    {
        $this->db->manipulate(
            'DELETE FROM il_md_cpr_selections WHERE entry_id = ' .
            $this->db->quote($id, \ilDBConstants::T_INTEGER)
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

        $next_id = $this->db->nextId('il_md_cpr_selections');
        if (is_string($image)) {
            $image_link = '';
            $image_file = $image;
        } else {
            $image_link = (string) $image;
            $image_file = '';
        }

        $this->db->insert(
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
        $res = $this->db->query(
            'SELECT MAX(position) AS max FROM il_md_cpr_selections WHERE is_default = 0'
        );
        $row = $this->db->fetchAssoc($res);

        return isset($row['max']) ? $row['max'] + 1 : 0;
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

        $this->db->update(
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
        $res = $this->db->query(
            'SELECT entry_id FROM il_md_cpr_selections WHERE is_default = 1'
        );

        if ($row = $this->db->fetchAssoc($res)) {
            return $row['entry_id'] ?? 0;
        }
        return 0;
    }

    protected function updatePosition(int $id, int $position): void
    {
        $this->db->update(
            'il_md_cpr_selections',
            ['position' => [\ilDBConstants::T_INTEGER, $position]],
            ['entry_id' => [\ilDBConstants::T_INTEGER, $id]]
        );
    }
}
