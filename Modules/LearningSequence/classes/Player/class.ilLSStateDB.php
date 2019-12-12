<?php

declare(strict_types=1);

/**
 * Persistence for View-States
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSStateDB
{
    const TABLE_NAME = 'lso_states';

    const CURRENT_ITEM_ID = "current_item";
    const STATES = "states";
    const FIRST_ACCESS = "first_access";
    const LAST_ACCESS = "last_access";

    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return  array <int,ILIAS\KioskMode\State>	usr_id => [item_ref_id=>State]
     */
    public function getStatesFor(int $lso_ref_id, array $usr_ids = []) : array
    {
        $data = $this->select($lso_ref_id, $usr_ids);
        $ret = [];
        foreach ($usr_ids as $usr_id) {
            $ret[$usr_id] = [];
            if (array_key_exists($usr_id, $data)) {
                $ret[$usr_id] = $data[$usr_id][self::STATES];
            }
        }

        return $ret;
    }

    /**
     * @return  array <int, int>	usr_id => item_ref_id
     */
    public function getCurrentItemsFor(int $lso_ref_id, array $usr_ids = []) : array
    {
        $data = $this->select($lso_ref_id, $usr_ids);
        $ret = [];
        foreach ($usr_ids as $usr_id) {
            $ret[$usr_id] = -1;
            if (array_key_exists($usr_id, $data)) {
                $ret[$usr_id] = $data[$usr_id][self::CURRENT_ITEM_ID];
            }
        }

        return $ret;
    }

    public function getFirstAccessFor(int $lso_ref_id, array $usr_ids = []) : array
    {
        $data = $this->select($lso_ref_id, $usr_ids);
        $ret = [];
        foreach ($usr_ids as $usr_id) {
            $ret[$usr_id] = -1;
            if (array_key_exists($usr_id, $data)) {
                $ret[$usr_id] = $data[$usr_id][self::FIRST_ACCESS];
            }
        }

        return $ret;
    }

    public function getLastAccessFor(int $lso_ref_id, array $usr_ids = []) : array
    {
        $data = $this->select($lso_ref_id, $usr_ids);
        $ret = [];
        foreach ($usr_ids as $usr_id) {
            $ret[$usr_id] = -1;
            if (array_key_exists($usr_id, $data)) {
                $ret[$usr_id] = $data[$usr_id][self::LAST_ACCESS];
            }
        }

        return $ret;
    }

    /**
     * update a single State (for the item with ref_id);
     * if $current_item is not set, assume that $ref_id is the current one.
     */
    public function updateState(
        int $lso_ref_id,
        int $usr_id,
        int $ref_id,
        ILIAS\KioskMode\State $state,
        int $current_item = null
    ) {
        $insert_first = $this->entryExistsFor($lso_ref_id, $usr_id) === false;
        $states = $this->getStatesFor($lso_ref_id, [$usr_id]);
        $states = $states[$usr_id];
        $states[$ref_id] = $state;
        $serialized = $this->serializeStates($states);
        if (is_null($current_item)) {
            $current_item = $ref_id;
        }

        $ilAtomQuery = $this->db->buildAtomQuery();
        $ilAtomQuery->addTableLock(static::TABLE_NAME);
        $ilAtomQuery->addQueryCallable(
            function (ilDBInterface $db) use ($insert_first, $lso_ref_id, $usr_id, $current_item, $serialized) {
                if ($insert_first) {
                    $this->insert($lso_ref_id, $usr_id);
                }
                $this->update($lso_ref_id, $usr_id, $current_item, $serialized, $first_access);
            }
        );

        $ilAtomQuery->run();
    }

    protected function entryExistsFor(int $lso_ref_id, int $usr_id) : bool
    {
        return count($this->select($lso_ref_id, [$usr_id])) > 0;
    }

    protected function insert(int $lso_ref_id, int $usr_id)
    {
        $first_access = date("d.m.Y H:i:s");
        $values = array(
            "lso_ref_id" => array("integer", $lso_ref_id),
            "usr_id" => array("integer", $usr_id),
            "first_access" => array("text", $first_access)
        );

        $this->db->insert(static::TABLE_NAME, $values);
    }

    protected function update(
        int $lso_ref_id,
        int $usr_id,
        int $current_item,
        string $serialized
    ) {
        $last_access = date("d.m.Y H:i:s");
        $where = array(
            "lso_ref_id" => array("integer", $lso_ref_id),
            "usr_id" => array("integer", $usr_id)
        );
        $values = array(
            "current_item" => array("integer", $current_item),
            "states" => array("text", $serialized),
            "last_access" => array("text", $last_access)
        );

        $this->db->update(static::TABLE_NAME, $values, $where);
    }

    /**
     * @param int $lso_ref_id
     * @param int[] $usr_ids
     */
    public function deleteFor(int $lso_ref_id, array $usr_ids = [])
    {
        $query =
             "DELETE FROM " . static::TABLE_NAME . PHP_EOL
            . "WHERE lso_ref_id = " . $this->db->quote($lso_ref_id, "integer") . PHP_EOL
        ;

        if (count($usr_ids) > 0) {
            $query .= "AND usr_id IN (" . implode(',', $usr_ids) . ")";
        }

        $this->db->manipulate($query);
    }

    public function deleteForItem(int $lso_ref_id, int $item_ref_id)
    {
        $all_states = $this->select($lso_ref_id);
        if (count($all_states) === 0) {
            return;
        }

        $ilAtomQuery = $this->db->buildAtomQuery();
        $ilAtomQuery->addTableLock(static::TABLE_NAME);

        foreach ($all_states as $usr_id=>$state_entry) {
            $current_item = $state_entry['current_item'];
            $states = $state_entry['states'];

            if ($current_item === $item_ref_id) {
                $current_item = -1;
            }

            if (array_key_exists($item_ref_id, $states)) {
                unset($states[$item_ref_id]);
            }
            $serialized = $this->serializeStates($states);

            $ilAtomQuery->addQueryCallable(
                function (ilDBInterface $db) use ($lso_ref_id, $usr_id, $current_item, $serialized) {
                    $this->update($lso_ref_id, $usr_id, $current_item, $serialized);
                }
            );
        }
        $ilAtomQuery->run();
    }


    /**
     * @return array <int,ILIAS\KioskMode\State> ref_id=>State
     */
    protected function buildStates(string $serialized) : array
    {
        $states = [];
        $data = json_decode($serialized, true);
        foreach ($data as $ref_id => $kvpair) {
            $states[$ref_id] = new ILIAS\KioskMode\State();
            if (is_array($kvpair)) {
                foreach ($kvpair as $key => $value) {
                    $states[$ref_id] = $states[$ref_id]->withValueFor($key, $value);
                }
            }
        }

        return $states;
    }

    /**
     * @param array <int,ILIAS\KioskMode\State> ref_id=>State
     */
    protected function serializeStates(array $states)
    {
        $data = [];
        foreach ($states as $ref_id => $state) {
            $data[$ref_id] = json_decode($state->serialize());
        }

        return json_encode($data);
    }

    protected function select(int $lso_ref_id, array $usr_ids = [])
    {
        $query =
             "SELECT usr_id, current_item, states, first_access, last_access" . PHP_EOL
            . "FROM " . static::TABLE_NAME . PHP_EOL
            . "WHERE lso_ref_id = " . $this->db->quote($lso_ref_id, "integer") . PHP_EOL
        ;

        if (count($usr_ids) > 0) {
            $query .= "AND usr_id IN (" . implode(',', $usr_ids) . ")";
        }

        $result = $this->db->query($query);

        $ret = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $ret[$row['usr_id']] = [
                self::CURRENT_ITEM_ID => (int) $row[self::CURRENT_ITEM_ID],
                self::STATES => $this->buildStates($row[self::STATES]),
                self::FIRST_ACCESS => $row[self::FIRST_ACCESS],
                self::LAST_ACCESS => $row[self::LAST_ACCESS]
            ];
        }

        return $ret;
    }
}
