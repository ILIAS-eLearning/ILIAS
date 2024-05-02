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
    private array $edit_actions = [ActionTarget::EDIT, ActionTarget::EMBED_EDIT, ActionTarget::CONVERT];
    private array $view_actions = [ActionTarget::VIEW, ActionTarget::EMBED_VIEW];

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

    public function hasEditActionForSuffix(string $suffix): bool
    {
        foreach ($this->edit_actions as $action_target) {
            if ($this->hasActionForSuffix($suffix, $action_target)) {
                return true;
            }
        }
        return false;
    }

    public function hasViewActionForSuffix(string $suffix): bool
    {
        foreach ($this->view_actions as $action_target) {
            if ($this->hasActionForSuffix($suffix, $action_target)) {
                return true;
            }
        }
        return false;
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

    public function getEditActionForSuffix(string $suffix): ?Action
    {
        foreach ($this->edit_actions as $action_target) {
            $action = $this->getActionForSuffix($suffix, $action_target);
            if ($action !== null) {
                return $action;
            }
        }
        return null;
    }

    public function getViewActionForSuffix(string $suffix): ?Action
    {
        foreach ($this->view_actions as $action_target) {
            $action = $this->getActionForSuffix($suffix, $action_target);
            if ($action !== null) {
                return $action;
            }
        }
        return null;
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

    public function getActionsForTarget(ActionTarget $action_target): array
    {
        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE name = %s';
        $result = $this->db->queryF($query, ['text'], [$action_target->value]);
        $actions = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $actions[] = $this->fromDBRow($row);
        }
        return $actions;
    }

    public function getActionsForTargets(ActionTarget ...$action_target): array
    {
        $actions = [];
        foreach ($action_target as $target) {
            $actions += $this->getActionsForTarget($target);
        }
        return $actions;
    }

    public function getSupportedSuffixes(ActionTarget $action_target): array
    {
        $query = 'SELECT ext FROM ' . self::TABLE_NAME . ' WHERE name = %s';
        $result = $this->db->queryF($query, ['text'], [$action_target->value]);
        $suffixes = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $suffixes[] = $row['ext'];
        }
        return $suffixes;
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
            new URI((string) $row['urlsrc']),
            empty($row['url_appendix']) ? null : (string) $row['url_appendix'],
            empty($row['target_text']) ? null : (string) $row['target_text']
        );
    }

    public function clear(): void
    {
        $query = 'DELETE FROM ' . self::TABLE_NAME;
        $this->db->manipulate($query);
    }

    public function clearSuperfluous(Action ...$actions): void
    {
        $collected_ids = array_map(
            static function (Action $act) {
                return $act->getId();
            },
            $actions
        );
        $query = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE ' . $this->db->in('id', $collected_ids, true, 'integer');
        $this->db->manipulate($query);
    }

    public function store(Action $action, App $for_app): void
    {
        // store only actions with extensions
        if (empty($action->getExtension())) {
            return;
        }

        // check for existing action to update them
        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE name = %s AND ext = %s AND target_ext = %s';
        $result = $this->db->queryF(
            $query,
            ['text', 'text', 'text'],
            [$action->getName(), $action->getExtension(), $action->getTargetExtension()]
        );

        if ($this->db->numRows($result) > 0) {
            $row = $this->db->fetchAssoc($result);
            $action = $action->withId((int) $row['id']);
        }

        if ($this->db->queryF(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = %s',
            ['integer'],
            [$action->getId()]
        )->numRows() === 0) {
            $next_id = (int) $this->db->nextId(self::TABLE_NAME);
            $this->db->insert(self::TABLE_NAME, [
                'id' => ['integer', $next_id],
                'name' => ['text', $action->getName()],
                'ext' => ['text', strtolower($action->getExtension())],
                'urlsrc' => ['text', $action->getLauncherUrl()],
                'app_id' => ['integer', $for_app->getId()],
                'url_appendix' => ['text', $action->getUrlAppendix()],
                'target_ext' => ['text', $action->getTargetExtension()],
            ]);
            $action = $action->withId($next_id);
        } else {
            $this->db->update(self::TABLE_NAME, [
                'name' => ['text', $action->getName()],
                'ext' => ['text', strtolower($action->getExtension())],
                'urlsrc' => ['text', $action->getLauncherUrl()],
                'app_id' => ['integer', $for_app->getId()],
                'url_appendix' => ['text', $action->getUrlAppendix()],
                'target_ext' => ['text', $action->getTargetExtension()],
            ], [
                'id' => ['integer', $action->getId()],
            ]);
        }
    }
}
