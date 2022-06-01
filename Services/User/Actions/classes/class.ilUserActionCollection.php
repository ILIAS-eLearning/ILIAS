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
 * Represents a set of collected user actions
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserActionCollection
{
    /**
     * @var ilUserAction[]
     */
    protected array $actions = array();

    public static function getInstance() : ilUserActionCollection
    {
        return new self();
    }

    /**
     * Add action
     *
     * @param ilUserAction $a_action action object
     */
    public function addAction(ilUserAction $a_action) : void
    {
        $this->actions[] = $a_action;
    }

    /**
     * @return ilUserAction[]
     */
    public function getActions() : array
    {
        return $this->actions;
    }
}
