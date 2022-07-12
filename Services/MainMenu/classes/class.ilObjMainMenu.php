<?php declare(strict_types=1);

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
    public function getPresentationTitle() : string
    {
        return $this->lng->txt("main_menu");
    }


    /**
     * @inheritDoc
     */
    public function getLongDescription() : string
    {
        return $this->lng->txt("add_remove_edit_entries_of_main_menu");
    }
}
