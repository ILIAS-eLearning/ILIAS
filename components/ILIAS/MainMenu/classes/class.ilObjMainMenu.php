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

/**
 * Class ilObjMainMenuGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjMainMenu extends ilObject
{
    /**
     * ilObjMainMenu constructor.
     *
     * @param int  $id
     * @param bool $call_by_reference
     */
    public function __construct(int $id = 0, bool $call_by_reference = true)
    {
        $this->type = "mme";
        parent::__construct($id, $call_by_reference);
    }


    /**
     * @inheritDoc
     */
    #[\Override]
    public function getPresentationTitle(): string
    {
        return $this->lng->txt("main_menu");
    }


    /**
     * @inheritDoc
     */
    #[\Override]
    public function getLongDescription(): string
    {
        return $this->lng->txt("add_remove_edit_entries_of_main_menu");
    }
}
