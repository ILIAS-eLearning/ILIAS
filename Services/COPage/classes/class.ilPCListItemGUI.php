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

/**
 * Class ilPCListItemGUI
 * Handles user commands on list items
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCListItemGUI extends ilPageContentGUI
{
    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand(): void
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }


    /**
     * insert new list item after current one
     */
    public function newItemAfter(): void
    {
        $this->content_obj->newItemAfter();
        $this->updateAndReturn();
    }

    /**
     * insert new list item before current one
     */
    public function newItemBefore(): void
    {
        $this->content_obj->newItemBefore();
        $this->updateAndReturn();
    }

    /**
     * delete a list item
     */
    public function deleteItem(): void
    {
        $this->content_obj->deleteItem();
        $this->updateAndReturn();
    }

    /**
     * move list item down
     */
    public function moveItemDown(): void
    {
        $this->content_obj->moveItemDown();
        $this->updateAndReturn();
    }

    /**
     * move list item up
     */
    public function moveItemUp(): void
    {
        $this->content_obj->moveItemUp();
        $this->updateAndReturn();
    }
}
