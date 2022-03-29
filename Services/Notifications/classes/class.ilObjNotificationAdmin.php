<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjNotificationAdmin
 *
 * @author Ingmar Szmais <iszmais@databay.de>
 */
class ilObjNotificationAdmin extends ilObject
{
    protected int $root_ref_id = 0;
    protected int $root_id = 0;

    /**
     * @inheritDoc
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = 'nota';
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
     * @access	public
     */
    public function delete() : bool
    {
        return false;
    }

    public function getRootRefId() : int
    {
        $this->loadRootRefIdAndId();

        return $this->root_ref_id;
    }

    public function getRootId() : int
    {
        $this->loadRootRefIdAndId();

        return $this->root_id;
    }

    /**
     * @throws Exception
     */
    private function loadRootRefIdAndId() : void
    {
        if ($this->root_ref_id === 0 || $this->root_id === 0) {
            $q = "SELECT object_reference.obj_id, object_reference.ref_id FROM object_data
			INNER JOIN object_reference ON object_reference.obj_id = object_data.obj_id
			WHERE type = %s";
            $set = $this->db->queryF($q, ['text'], ['nota']);
            if ($res = $this->db->fetchAssoc($set)) {
                $this->root_id = (int) $res["obj_id"];
                $this->root_ref_id = (int) $res["ref_id"];
            } else {
                throw new Exception('Node "nota" not found.');
            }
        }
    }
}
