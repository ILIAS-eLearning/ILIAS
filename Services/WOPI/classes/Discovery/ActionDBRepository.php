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

namespace ILIAS\Services\WOPI\Discovery;

use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ActionDBRepository implements ActionRepository
{
    private const TABLE_NAME = 'wopi_action';

    public function __construct(
        private \ilDBInterface $db
    ) {
    }

    public function hasActionForSuffix(
        string $suffix,
        ActionTarget $action_target
    ): bool {
        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE ext = %s AND name = %s';
        $result = $this->db->queryF($query, ['text', 'text'], [strtolower($suffix), $action_target->value]);
        return $result->numRows() > 0;
    }

    public function getActionForSuffix(
        string $suffix,
        ActionTarget $action_target
    ): ?Action {
        if (!$this->hasActionForSuffix($suffix, $action_target)) {
            return null;
        }

        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE ext = %s AND name = %s';
        $result = $this->db->queryF($query, ['text', 'text'], [strtolower($suffix), $action_target->value]);
        $row = $this->db->fetchAssoc($result);
        return $this->fromDBRow($row);
    }

    public function getActions(): array
    {
        $actions = [];
        $query = 'SELECT * FROM ' . self::TABLE_NAME;
        $result = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($result)) {
            $actions[] = $this->fromDBRow($row);
        }
        return $actions;
    }

    public function getActionsForApp(App $app): array
    {
        $actions = [];
        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE app_id = %s';
        $result = $this->db->queryF($query, ['integer'], [$app->getId()]);
        while ($row = $this->db->fetchAssoc($result)) {
            $actions[] = $this->fromDBRow($row);
        }
        return $actions;
    }

    private function fromDBRow(array $row): Action
    {
        return new Action(
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['ext'],
            new URI((string) $row['urlsrc'])
        );
    }

    public function clear(): void
    {
        $query = 'DELETE FROM ' . self::TABLE_NAME;
        $this->db->manipulate($query);
    }

    public function store(Action $action, App $for_app): void
    {
        // store only actions with extensions
        if (empty($action->getExtension())) {
            return;
        }

        if ($action->getId() === 0 || $this->db->queryF(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = %s',
            ['integer'],
            [$action->getId()]
        )->numRows() === 0) {
            $this->db->insert(self::TABLE_NAME, [
                'id' => ['integer', $this->db->nextId(self::TABLE_NAME)],
                'name' => ['text', $action->getName()],
                'ext' => ['text', strtolower($action->getExtension())],
                'urlsrc' => ['text', $action->getLauncherUrl()],
                'app_id' => ['integer', $for_app->getId()],
            ]);
        } else {
            $this->db->update(self::TABLE_NAME, [
                'name' => ['text', $action->getName()],
                'ext' => ['text', strtolower($action->getExtension())],
                'urlsrc' => ['text', $action->getLauncherUrl()],
                'app_id' => ['integer', $for_app->getId()],
            ], [
                'id' => ['integer', $action->getId()],
            ]);
        }
    }
}
