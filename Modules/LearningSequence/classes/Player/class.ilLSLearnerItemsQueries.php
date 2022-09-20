<?php

declare(strict_types=1);

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

use ILIAS\KioskMode\View;

/**
 * This combines calls to ProgressDB and StateDB to handle learner-items
 * in the context of a specific LSObject and a specific user.
 */
class ilLSLearnerItemsQueries
{
    protected ilLearnerProgressDB $progress_db;
    protected ilLSStateDB $states_db;
    protected int $ls_ref_id;
    protected int $usr_id;

    public function __construct(
        ilLearnerProgressDB $progress_db,
        ilLSStateDB $states_db,
        int $ls_ref_id,
        int $usr_id
    ) {
        $this->progress_db = $progress_db;
        $this->states_db = $states_db;
        $this->ls_ref_id = $ls_ref_id;
        $this->usr_id = $usr_id;
    }

    /**
     * @return LSLearnerItem[]
     */
    public function getItems(): array
    {
        return $this->progress_db->getLearnerItems($this->usr_id, $this->ls_ref_id);
    }

    public function getCurrentItemRefId(): int
    {
        $current = $this->states_db->getCurrentItemsFor($this->ls_ref_id, [$this->usr_id]); //0 or greater
        return max(0, $current[$this->usr_id]);
    }

    public function getCurrentItemPosition(): int
    {
        $current_position = 0;
        $items = $this->getItems();
        foreach ($items as $index => $item) {
            if ($item->getRefId() === $this->getCurrentItemRefId()) {
                $current_position = $index;
            }
        }
        return $current_position;
    }

    public function getStateFor(LSItem $ls_item, View $view): ILIAS\KioskMode\State
    {
        $states = $this->states_db->getStatesFor($this->ls_ref_id, [$this->usr_id]);
        $states = $states[$this->usr_id];

        if (array_key_exists($ls_item->getRefId(), $states)) {
            return $states[$ls_item->getRefId()];
        }
        return $view->buildInitialState(
            new ILIAS\KioskMode\State()
        );
    }

    public function storeState(
        ILIAS\KioskMode\State $state,
        int $state_item_ref_id,
        int $current_item_ref_id
    ): void {
        $this->states_db->updateState(
            $this->ls_ref_id,
            $this->usr_id,
            $state_item_ref_id,
            $state,
            $current_item_ref_id
        );
    }

    public function getFirstAccess(): string
    {
        $first_access = $this->states_db->getFirstAccessFor($this->ls_ref_id, [$this->usr_id]);
        return $first_access[$this->usr_id];
    }
}
