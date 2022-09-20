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
 ********************************************************************
 */

/**
 * Skill management main application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjSkillManagement extends ilObject
{
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->type = "skmg";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function update(): bool
    {
        $ilDB = $this->db;

        if (!parent::update()) {
            return false;
        }

        return true;
    }

    /**
    * read style folder data
    */
    public function read(): void
    {
        $ilDB = $this->db;

        parent::read();
    }

    /**
    * delete object and all related data
    *
    * @return	bool	true if all object data were removed; false if only a references were removed
    */
    public function delete(): bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        //put here your module specific stuff

        return true;
    }
}
