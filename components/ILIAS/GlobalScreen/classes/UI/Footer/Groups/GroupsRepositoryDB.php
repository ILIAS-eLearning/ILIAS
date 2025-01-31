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

namespace ILIAS\GlobalScreen\UI\Footer\Groups;

use ILIAS\GlobalScreen\Scope\Footer\Collector\FooterMainCollector;
use ILIAS\GlobalScreen\Scope\Footer\Factory\isGroup;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;

class GroupsRepositoryDB implements GroupsRepository
{
    use Hasher;

    public const TABLE_NAME = 'gs_footer_items';
    private bool $loaded = false;

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
            if (!$item instanceof isGroup) {
                continue;
            }
            if ($this->has($item->getProviderIdentification()->serialize())) {
                continue;
            }

            $new = new GroupDTO(
                $item->getProviderIdentification()->serialize(),
                $item->getTitle(),
                true,
                $item->getPosition(),
                count($item->getEntries()),
                true
            );
            $this->store($new);
            $this->cache[$new->getId()] = $new;
        }
    }

    public function preload(): void
    {
        if ($this->loaded) {
            return;
        }

        foreach (
            $this->db->fetchAll(
                $this->db->query(
                    'SELECT g.*, (SELECT COUNT(id) FROM gs_footer_items WHERE parent = g.id) AS items
                FROM gs_footer_items AS g
                WHERE g.type = 1
                ORDER BY g.position ASC'
                )
            ) as $row
        ) {
            $group = $this->fromDB($row);
            $this->cache[$group->getId()] = $group;
        }
        $this->loaded = true;
    }

    private function fromDB(array $row): Group
    {
        return new GroupDTO(
            $row['id'],
            $row['title'],
            $row['is_active'] === 1,
            (int) $row['position'],
            (int) ($row['items'] ?? 0),
            (bool) $row['core']
        );
    }

    public function get(string $identifier): ?Group
    {
        if (isset($this->cache[$identifier]) && $this->has($identifier)) {
            return $this->cache[$identifier];
        }

        $row = $this->db->queryF(
            'SELECT g.*, (SELECT COUNT(id) FROM gs_footer_items WHERE parent = g.id) AS items
                FROM gs_footer_items AS g
                WHERE g.type = 1 AND g.id = %s
                ORDER BY g.position ASC',
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
            'SELECT id FROM ' . self::TABLE_NAME . ' WHERE id = %s AND type = 1',
            ['text'],
            [$identifier]
        )->numRows() > 0;
    }

    public function blank(): Group
    {
        return new GroupDTO('', '', true, 0, 0, false);
    }

    public function store(Group $group): Group
    {
        if ($group->getId() === '' || !$this->has($group->getId())) {
            return $this->create($group);
        }

        return $this->update($group);
    }

    private function create(Group $group): Group
    {
        if ($this->provider === null) {
            throw new \LogicException('No provider set');
        }
        if ($group->getId() === '') {
            $group = $group->withId($this->provider->getNewIdentification()->serialize());
        }
        $this->db->insert(
            self::TABLE_NAME,
            [
                'id' => ['text', $group->getId()],
                'type' => ['inetger', 1],
                'title' => ['text', $group->getTitle()],
                'position' => ['integer', $group->getPosition()],
                'is_active' => ['integer', $group->isActive() ? 1 : 0],
                'parent' => ['text', null],
                'core' => ['integer', $group->isCore() ? 1 : 0],
            ]
        );
        return $group;
    }

    private function update(Group $group): Group
    {
        $this->db->update(
            self::TABLE_NAME,
            [
                'title' => ['text', $group->getTitle()],
                'position' => ['integer', $group->getPosition()],
                'is_active' => ['integer', $group->isActive() ? 1 : 0],
                'parent' => ['text', null],
                'core' => ['integer', $group->isCore() ? 1 : 0],
            ],
            ['id' => ['text', $group->getId()]]
        );
        return $group;
    }

    public function delete(Group $group): void
    {
        if ($group->isCore()) {
            return;
        }

        if ($group->getItems() > 0) {
            return;
        }

        $this->db->manipulateF(
            'DELETE FROM ' . self::TABLE_NAME . ' WHERE id = %s',
            ['text'],
            [$group->getId()]
        );
    }

    /**
     * @return \Generator|Group[]
     */
    public function all(): \Generator
    {
        if (!$this->loaded) {
            $this->preload();
        }
        yield from $this->cache;
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
        $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME);//. ' WHERE type = 1');
        $this->syncWithGlobalScreen($collector);
    }

}
