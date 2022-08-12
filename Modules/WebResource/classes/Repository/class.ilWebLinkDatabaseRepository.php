<?php declare(strict_types=1);

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

/**
 * @author Tim Schmitz <schmitz@leifos.de>
 */
class ilWebLinkDatabaseRepository implements ilWebLinkRepository
{
    public const ITEMS_TABLE = 'webr_items';
    public const LISTS_TABLE = 'webr_lists';
    public const PARAMS_TABLE = 'webr_params';

    protected int $webr_id;
    protected ilDBInterface $db;
    protected ilObjUser $user;
    protected bool $update_history;

    public function __construct(int $webr_id, bool $update_history = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->webr_id = $webr_id;
        $this->update_history = $update_history;
    }

    public function createItem(ilWebLinkDraftItem $item) : ilWebLinkItem
    {
        $next_link_id = $this->db->nextId(self::ITEMS_TABLE);

        $this->validateInternalItemTarget($item);

        //create the parameters
        $new_parameters = [];
        foreach ($item->getParameters() as $parameter) {
            $next_param_id = $this->db->nextId(self::PARAMS_TABLE);

            try {
                $this->validateParameter($parameter);
            } catch (Exception $e) {
                continue;
            }


            $new_parameter = new ilWebLinkParameter(
                $this->user,
                $this->getWebrId(),
                $next_link_id,
                $next_param_id,
                $parameter->getValue(),
                $parameter->getName()
            );

            $this->db->insert(
                self::PARAMS_TABLE,
                [
                    'webr_id' => ['integer', $new_parameter->getWebrId()],
                    'link_id' => ['integer', $new_parameter->getLinkId()],
                    'param_id' => ['integer', $new_parameter->getParamId()],
                    'name' => ['text', $new_parameter->getName()],
                    'value' => ['integer', $new_parameter->getValue()]
                ]
            );

            $new_parameters[] = $new_parameter;
        }

        //create the item with the new parameters
        if ($item->isInternal()) {
            $class = ilWebLinkItemInternal::class;
        } else {
            $class = ilWebLinkItemExternal::class;
        }

        $new_item = new $class(
            $this->getWebrId(),
            $next_link_id,
            $item->getTitle(),
            $item->getDescription(),
            $item->getTarget(),
            $item->isActive(),
            $this->getNewDateTimeImmutable(),
            $this->getNewDateTimeImmutable(),
            $new_parameters
        );

        $this->db->insert(
            self::ITEMS_TABLE,
            [
                'internal' => ['integer', (int) $new_item->isInternal()],
                'webr_id' => ['integer', $new_item->getWebrId()],
                'link_id' => ['integer', $new_item->getLinkId()],
                'title' => ['text', $new_item->getTitle()],
                'description' => ['text', $new_item->getDescription() ?? ''],
                'target' => ['text', $new_item->getTarget()],
                'active' => ['integer', (int) $new_item->isActive()],
                'create_date' => ['integer', $new_item->getCreateDate()
                                                      ->getTimestamp()],
                'last_update' => ['integer', $new_item->getLastUpdate()
                                                      ->getTimestamp()]
            ]
        );

        if ($this->isUpdateHistory()) {
            ilHistory::_createEntry(
                $this->getWebrId(),
                "add",
                [$new_item->getTitle()]
            );
        }

        return $new_item;
    }

    public function createList(ilWebLinkDraftList $list) : ilWebLinkList
    {
        $new_list = new ilWebLinkList(
            $this->getWebrId(),
            $list->getTitle(),
            $list->getDescription(),
            $this->getNewDateTimeImmutable(),
            $this->getNewDateTimeImmutable(),
        );

        $this->db->insert(
            self::LISTS_TABLE,
            [
                'webr_id' => ['integer', $new_list->getWebrId()],
                'title' => ['text', $new_list->getTitle()],
                'description' => ['text', $new_list->getDescription() ?? ''],
                'create_date' => ['integer', $new_list->getCreateDate()
                                                      ->getTimestamp()],
                'last_update' => ['integer', $new_list->getLastUpdate()
                                                      ->getTimestamp()],
            ]
        );

        if ($this->isUpdateHistory()) {
            ilHistory::_createEntry(
                $this->getWebrId(),
                "add",
                [$new_list->getTitle()]
            );
        }

        return $new_list;
    }

    public function createAllItemsInDraftContainer(ilWebLinkDraftItemsContainer $container) : ilWebLinkItemsContainer
    {
        $new_items = [];

        foreach ($container->getItems() as $item) {
            $new_items[] = $this->createItem($item);
        }

        return new ilWebLinkItemsContainer(
            $this->getWebrId(),
            $new_items
        );
    }

    public function getAllItemsAsContainer(bool $only_active = false) : ilWebLinkItemsContainer
    {
        $query = "SELECT * FROM " . $this->db->quoteIdentifier(self::ITEMS_TABLE) . " " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), 'integer');

        if ($only_active) {
            $query .= " AND active = " . $this->db->quote(1, 'integer');
        }

        $res = $this->db->query($query);
        $items = [];

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $parameters = $this->getParametersByLinkId((int) $row->link_id);

            if ($row->internal) {
                $class = ilWebLinkItemInternal::class;
            } else {
                $class = ilWebLinkItemExternal::class;
            }

            $items[] = new $class(
                (int) $row->webr_id,
                (int) $row->link_id,
                (string) $row->title,
                ((string) $row->description) !== '' ? (string) $row->description : null,
                (string) $row->target,
                (bool) $row->active,
                $this->getNewDateTimeImmutable()->setTimestamp((int) $row->create_date),
                $this->getNewDateTimeImmutable()->setTimestamp((int) $row->last_update),
                $parameters
            );
        }

        return new ilWebLinkItemsContainer(
            $this->getWebrId(),
            $items
        );
    }

    public function getItemByLinkId(int $link_id) : ilWebLinkItem
    {
        $query = "SELECT * FROM " . $this->db->quoteIdentifier(self::ITEMS_TABLE) . " " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), 'integer') . " " .
            "AND link_id = " . $this->db->quote($link_id, 'integer');

        $res = $this->db->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $parameters = $this->getParametersByLinkId((int) $row->link_id);

            if ($row->internal) {
                $class = ilWebLinkItemInternal::class;
            } else {
                $class = ilWebLinkItemExternal::class;
            }

            return new $class(
                (int) $row->webr_id,
                (int) $row->link_id,
                (string) $row->title,
                ((string) $row->description) !== '' ? (string) $row->description : null,
                (string) $row->target,
                (bool) $row->active,
                $this->getNewDateTimeImmutable()->setTimestamp((int) $row->create_date),
                $this->getNewDateTimeImmutable()->setTimestamp((int) $row->last_update),
                $parameters
            );
        }

        throw new ilWebLinkDatabaseRepositoryException(
            'No item with the given link_id was found in this web link object.'
        );
    }

    public function doesOnlyOneItemExist(bool $only_active = false) : bool
    {
        $query = "SELECT COUNT(*) AS num FROM " . $this->db->quoteIdentifier(self::ITEMS_TABLE) . " " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), 'integer');

        if ($only_active) {
            $query .= " AND active = " . $this->db->quote(1, 'integer');
        }

        $row = $this->db->query($query)
                        ->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        return $row->num == 1;
    }

    public function getParameterinItemByParamId(
        ilWebLinkItem $item,
        int $param_id
    ) : ilWebLinkParameter {
        $res = $this->db->query(
            "SELECT * FROM " . $this->db->quoteIdentifier(self::PARAMS_TABLE) . " " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), 'integer') . " " .
            "AND link_id = " . $this->db->quote($item->getLinkId(), 'integer') . " " .
            "AND param_id = " . $this->db->quote($param_id, 'integer')
        );

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return new ilWebLinkParameter(
                $this->user,
                (int) $row->webr_id,
                (int) $row->link_id,
                (int) $row->param_id,
                (int) $row->value,
                (string) $row->name
            );
        }

        throw new ilWebLinkDatabaseRepositoryException(
            'In the given item of this web link object, no parameter with the given param_id was found.'
        );
    }

    public function getList() : ilWebLinkList
    {
        $res = $this->db->query(
            "SELECT * FROM " . $this->db->quoteIdentifier(self::LISTS_TABLE) . " " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), 'integer')
        );

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return new ilWebLinkList(
                (int) $row->webr_id,
                (string) $row->title,
                ((string) $row->description) !== '' ? (string) $row->description : null,
                $this->getNewDateTimeImmutable()->setTimestamp((int) $row->create_date),
                $this->getNewDateTimeImmutable()->setTimestamp((int) $row->last_update),
            );
        }

        throw new ilWebLinkDatabaseRepositoryException(
            'No list exists in this web link object.'
        );
    }

    public function doesListExist() : bool
    {
        $res = $this->db->query(
            "SELECT COUNT(*) AS num FROM " . $this->db->quoteIdentifier(self::LISTS_TABLE) . " " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), 'integer')
        );

        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        return ((int) $row->num) > 0;
    }

    public function updateItem(
        ilWebLinkItem $item,
        ilWebLinkDraftItem $drafted_item
    ) : void {
        if ($item->getWebrId() !== $this->getWebrId()) {
            throw new ilWebLinkDatabaseRepositoryException(
                'Cannot update an item from a different web link object.'
            );
        }

        $this->validateInternalItemTarget($drafted_item);

        $this->db->update(
            self::ITEMS_TABLE,
            [
                'title' => ['text', $drafted_item->getTitle()],
                'description' => ['text', $drafted_item->getDescription() ?? ''],
                'target' => ['text', $drafted_item->getTarget()],
                'active' => ['integer', (int) $drafted_item->isActive()],
                'internal' => ['integer', (int) $drafted_item->isInternal()],
                'last_update' => ['integer', $this->getCurrentTime()]
            ],
            [
                'webr_id' => ['integer', $item->getWebrId()],
                'link_id' => ['integer', $item->getLinkId()]
            ]
        );

        /*
         * drafted parameters of the drafted item are either created or
         * update an existing parameter
         */
        $param_ids = [];
        foreach ($drafted_item->getParameters() as $parameter) {
            if ($parameter instanceof ilWebLinkParameter) {
                $param_ids[] = $parameter->getParamId();
                continue;
            }

            try {
                $this->validateParameter($parameter);
            } catch (Exception $e) {
                continue;
            }

            if ($old_parameter = $parameter->getOldParameter()) {
                if (
                    $old_parameter->getLinkId() !== $item->getLinkId() ||
                    $old_parameter->getWebrId() !== $item->getWebrId()
                ) {
                    throw new ilWebLinkDatabaseRepositoryException(
                        'Cannot update a parameter from a different item.'
                    );
                }

                $this->db->update(
                    self::PARAMS_TABLE,
                    [
                        'name' => ['text', $drafted_item->getTitle()],
                        'value' => ['integer', $drafted_item->getTitle()]
                    ],
                    [
                        'webr_id' => ['integer', $item->getWebrId()],
                        'link_id' => ['integer', $item->getLinkId()],
                        'param_id' => ['integer', $parameter->getOldParameter()
                                                            ->getParamId()],
                    ]
                );
                continue;
            }

            $next_param_id = $this->db->nextId(self::PARAMS_TABLE);
            $this->db->insert(
                self::PARAMS_TABLE,
                [
                    'webr_id' => ['integer', $this->getWebrId()],
                    'link_id' => ['integer', $item->getLinkId()],
                    'param_id' => ['integer', $next_param_id],
                    'name' => ['text', $parameter->getName()],
                    'value' => ['integer', $parameter->getValue()]
                ]
            );
        }

        /*
         * parameters attached to the original item but not the drafted item
         * are deleted
         */
        foreach ($item->getParameters() as $parameter) {
            if (!in_array($parameter->getParamId(), $param_ids)) {
                $this->deleteParameterByLinkIdAndParamId(
                    $item->getLinkId(),
                    $parameter->getParamId()
                );
            }
        }

        if ($this->isUpdateHistory()) {
            ilHistory::_createEntry(
                $this->getWebrId(),
                "update",
                [$item->getTitle()]
            );
        }
    }

    public function updateList(
        ilWebLinkList $list,
        ilWebLinkDraftList $drafted_list
    ) : void {
        if ($list->getWebrId() !== $this->getWebrId()) {
            throw new ilWebLinkDatabaseRepositoryException(
                'Cannot update a list from a different web link object.'
            );
        }

        $this->db->update(
            self::LISTS_TABLE,
            [
                'title' => ['text', $drafted_list->getTitle()],
                'description' => ['text', $drafted_list->getDescription() ?? ''],
                'last_update' => ['integer', $this->getCurrentTime()]
            ],
            [
                'webr_id' => ['integer', $list->getWebrId()]
            ]
        );

        if ($this->isUpdateHistory()) {
            ilHistory::_createEntry(
                $this->getWebrId(),
                "update",
                [$list->getTitle()]
            );
        }
    }

    public function deleteAllItems() : void
    {
        $this->db->manipulate(
            "DELETE FROM " . $this->db->quoteIdentifier(self::ITEMS_TABLE) . " " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), 'integer')
        );

        $this->db->manipulate(
            "DELETE FROM " . $this->db->quoteIdentifier(self::PARAMS_TABLE) . " " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), 'integer')
        );
    }

    public function deleteItemByLinkID(int $link_id) : void
    {
        if ($this->isUpdateHistory()) {
            $title = $this->getItemByLinkId($link_id)->getTitle();
        }

        $this->db->manipulate(
            "DELETE FROM " . $this->db->quoteIdentifier(self::ITEMS_TABLE) . " " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), 'integer') . " " .
            "AND link_id = " . $this->db->quote($link_id, 'integer')
        );

        $this->db->manipulate(
            "DELETE FROM " . $this->db->quoteIdentifier(self::PARAMS_TABLE) . " " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), 'integer') . " " .
            "AND link_id = " . $this->db->quote($link_id, 'integer')
        );

        if (isset($title)) {
            ilHistory::_createEntry(
                $this->getWebrId(),
                "delete",
                [$title]
            );
        }
    }

    public function deleteParameterByLinkIdAndParamId(
        int $link_id,
        int $param_id
    ) : void {
        $this->db->manipulate(
            "DELETE FROM " . $this->db->quoteIdentifier(self::PARAMS_TABLE) . " " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), 'integer') . " " .
            "AND link_id = " . $this->db->quote($link_id, 'integer') . " " .
            "AND param_id = " . $this->db->quote($param_id, 'integer')
        );
    }

    public function deleteList() : void
    {
        if ($this->isUpdateHistory()) {
            $title = $this->getList()->getTitle();
        }

        $res = $this->db->manipulate(
            "DELETE FROM " . $this->db->quoteIdentifier(self::LISTS_TABLE) . " " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), 'integer')
        );

        if (isset($title)) {
            ilHistory::_createEntry(
                $this->getWebrId(),
                "delete",
                [$title]
            );
        }
    }

    /**
     * @return ilWebLinkParameter[]
     */
    protected function getParametersByLinkId(int $link_id) : array
    {
        $res = $this->db->query(
            "SELECT * FROM " . $this->db->quoteIdentifier(self::PARAMS_TABLE) . " " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), 'integer') . " " .
            "AND link_id = " . $this->db->quote($link_id, 'integer')
        );
        $parameters = [];

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $parameters[] = new ilWebLinkParameter(
                $this->user,
                (int) $row->webr_id,
                (int) $row->link_id,
                (int) $row->param_id,
                (int) $row->value,
                (string) $row->name
            );
        }

        return $parameters;
    }

    /**
     * @throws ilWebLinkDatabaseRepositoryException
     */
    protected function validateParameter(ilWebLinkBaseParameter $parameter) : void
    {
        if (!in_array(
            $parameter->getValue(),
            ilWebLinkBaseParameter::VALUES
        )) {
            throw new ilWebLinkDatabaseRepositoryException(
                'The value of the parameter you are trying to create is invalid.'
            );
        }
    }

    /**
     * @throws ilWebLinkDatabaseRepositoryException
     */
    protected function validateInternalItemTarget(ilWebLinkDraftItem $item) : void
    {
        if (
            $item->isInternal() &&
            !ilLinkInputGUI::isInternalLink($item->getTarget())
        ) {
            throw new ilWebLinkDatabaseRepositoryException(
                'The target of this internal link item is not internal.'
            );
        }
    }

    public function getWebrId() : int
    {
        return $this->webr_id;
    }

    public function isUpdateHistory() : bool
    {
        return $this->update_history;
    }

    public function setUpdateHistory(bool $update_history) : void
    {
        $this->update_history = $update_history;
    }

    protected function getCurrentTime() : int
    {
        return time();
    }

    protected function getNewDateTimeImmutable() : DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
