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

namespace ILIAS\GlobalScreen\UI\Footer\Entries;

use ILIAS\GlobalScreen\Scope\Footer\Collector\FooterMainCollector;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\Footer\Factory\canHaveParent;
use ILIAS\GlobalScreen\Scope\Footer\Factory\Link;
use ILIAS\GlobalScreen\Scope\Footer\Factory\Modal;
use ILIAS\GlobalScreen\Scope\Footer\Factory\hasAction;

class EntriesRepositoryDB implements EntriesRepository
{
    use Hasher;

    public const TABLE_NAME = 'gs_footer_items';

    /**
     * @var Entry[]
     */
    protected array $cache = [];

    public function __construct(
        private \ilDBInterface $db,
        private ?\ilFooterCustomGroupsProvider $provider = null
    ) {
    }

    public function syncWithGlobalScreen(
        FooterMainCollector $collector
    ): void {
        $collector->collectOnce();
        $this->preload();

        foreach ($collector->getRawUnfilteredItems() as $item) {
            if (!$item instanceof canHaveParent) {
                continue;
            }
            if ($this->has($item->getProviderIdentification()->serialize())) {
                continue;
            }
            /** @var Link|Modal $item */

            $new = new EntryDTO(
                $item->getProviderIdentification()->serialize(),
                $item->getTitle(),
                true,
                $item->getPosition(),
                $item->getParentIdentification()->serialize(),
                $item instanceof hasAction ? (string) $item->getAction() : '',
                $item instanceof hasAction ? $item->mustOpenInNewViewport() : false,
                true
            );
            $this->store($new);
        }
    }

    public function preload(): void
    {
        foreach (
            $this->db->fetchAll(
                $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE type != 1 ORDER BY position ASC')
            ) as $row
        ) {
            $entry = $this->fromDB($row);
            $this->cache[$entry->getId()] = $entry;
        }
    }

    private function fromDB(array $row): Entry
    {
        return new EntryDTO(
            $row['id'],
            $row['title'],
            $row['is_active'] === 1,
            (int) $row['position'],
            (string) ($row['parent'] ?? ''),
            (string) ($row['action'] ?? ''),
            (bool) $row['external'],
            (bool) $row['core']
        );
    }

    public function get(string $identifier): ?Entry
    {
        if (isset($this->cache[$identifier]) && $this->has($identifier)) {
            return $this->cache[$identifier];
        }

        $row = $this->db->queryF(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = %s AND type != 1',
            ['text'],
            [$identifier]
        )->fetchAssoc();
        if ($row === null) {
            return null;
        }
        return $this->fromDB($row);
    }

    public function has(string $identifier): bool
    {
        return $this->db->queryF(
            'SELECT id FROM ' . self::TABLE_NAME . ' WHERE id = %s AND type != 1',
            ['text'],
            [$identifier]
        )->numRows() > 0;
    }

    public function blank(): Entry
    {
        return new EntryDTO('', '', true, 0, '', false);
    }

    public function store(Entry $entry): Entry
    {
        if ($entry->getId() === '' || !$this->has($entry->getId())) {
            return $this->create($entry);
        }

        return $this->update($entry);
    }

    private function create(Entry $entry): Entry
    {
        if ($this->provider === null) {
            throw new \LogicException('No provider set');
        }

        if ($entry->getId() === '') {
            $entry = $entry->withId($this->provider->getNewIdentification()->serialize());
        }
        $this->db->insert(
            self::TABLE_NAME,
            [
                'id' => ['text', $entry->getId()],
                'type' => ['integer', 2],
                'title' => ['text', $entry->getTitle()],
                'position' => ['integer', $entry->getPosition()],
                'is_active' => ['integer', $entry->isActive() ? 1 : 0],
                'parent' => ['text', $entry->getParent()],
                'action' => ['text', $entry->getAction()],
                'external' => ['integer', $entry->isExternal() ? 1 : 0],
                'core' => ['integer', $entry->isCore() ? 1 : 0],
            ]
        );
        return $entry;
    }

    private function update(Entry $entry): Entry
    {
        $this->db->update(
            self::TABLE_NAME,
            [
                'title' => ['text', $entry->getTitle()],
                'position' => ['integer', $entry->getPosition()],
                'is_active' => ['integer', $entry->isActive() ? 1 : 0],
                'parent' => ['text', $entry->getParent()],
                'action' => ['text', $entry->getAction()],
                'external' => ['integer', $entry->isExternal() ? 1 : 0],
                'core' => ['integer', $entry->isCore() ? 1 : 0],
            ],
            ['id' => ['text', $entry->getId()]]
        );
        return $entry;
    }

    public function delete(Entry $entry): void
    {
        if ($entry->isCore()) {
            return;
        }

        $this->db->manipulateF(
            'DELETE FROM ' . self::TABLE_NAME . ' WHERE id = %s',
            ['text'],
            [$entry->getId()]
        );
    }

    /**
     * @return \Generator|Entry[]
     */
    public function all(): \Generator
    {
        $this->preload();
        yield from $this->cache;
    }

    public function allForParent(string $parent_identifier): \Generator
    {
        $this->preload();
        foreach ($this->cache as $entry) {
            if ($entry->getParent() === $parent_identifier) {
                yield $entry;
            }
        }
    }

    public function updatePositionById(string $id, int $position): void
    {
        $this->db->update(
            self::TABLE_NAME,
            ['position' => ['integer', $position]],
            ['id' => ['text', $id]]
        );
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return null;
    }

    public function reset(FooterMainCollector $collector): void
    {
        $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE type != 1');
        $this->syncWithGlobalScreen($collector);
    }

}
