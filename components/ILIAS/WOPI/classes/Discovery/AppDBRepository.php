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
class AppDBRepository implements AppRepository
{
    protected const TABLE_NAME = 'wopi_app';

    public function __construct(
        private \ilDBInterface $db
    ) {
    }

    public function getApps(ActionRepository $action_repository): array
    {
        $apps = [];
        $query = 'SELECT * FROM ' . self::TABLE_NAME;
        $result = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($result)) {
            $apps[] = $this->fromDBRow($row, $action_repository);
        }

        return $apps;
    }

    private function fromDBRow(array $row, ActionRepository $action_repository): App
    {
        return new App(
            (int) $row['id'],
            (string) $row['name'],
            $action_repository->getActionsForApp($row['id']),
            $row['favicon'] ? new URI($row['favicon']) : null
        );
    }

    public function storeCollection(Apps $apps, ActionRepository $action_repository): void
    {
        foreach ($apps->getApps() as $app) {
            $this->store($app, $action_repository);
        }
    }

    public function clear(ActionRepository $action_repository): void
    {
        $query = 'DELETE FROM ' . self::TABLE_NAME;
        $this->db->manipulate($query);
        $action_repository->clear();
    }

    public function store(App $app, ActionRepository $action_repository): void
    {
        if ($app->getId() === 0) {
            // check if there is an app with same name
            $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE name = %s';
            $result = $this->db->queryF($query, ['text'], [$app->getName()]);
            if ($this->db->numRows($result) > 0) {
                $row = $this->db->fetchAssoc($result);
                $app = new App(
                    (int) $row['id'],
                    $app->getName(),
                    $app->getActions(),
                    $app->getFavicon()
                );
            }
        }

        if ($app->getId() === 0 || $this->db->queryF(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = %s',
            ['integer'],
            [$app->getId()]
        )->numRows() === 0) {
            $app = new App(
                $this->db->nextId(self::TABLE_NAME),
                $app->getName(),
                $app->getActions(),
                $app->getFavicon()
            );

            $query = 'INSERT INTO ' . self::TABLE_NAME . ' (id, name, favicon) VALUES (%s, %s, %s)';
            $this->db->manipulateF(
                $query,
                ['integer', 'text', 'text'],
                [$app->getId(), $app->getName(), $app->getFavicon()]
            );
        } else {
            $query = 'UPDATE ' . self::TABLE_NAME . ' SET name = %s, favicon = %s WHERE id = %s';
            $this->db->manipulateF(
                $query,
                ['text', 'text', 'integer'],
                [$app->getName(), $app->getFavicon(), $app->getId()]
            );
        }

        foreach ($app->getActions() as $action) {
            $action_repository->store($action, $app);
        }
    }

}
