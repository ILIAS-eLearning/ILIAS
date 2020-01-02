<?php

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitPositionTableGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionTableGUI extends ilTable2GUI
{

    /**
     * @var \ILIAS\DI\Container
     */
    protected $DIC;
    /**
     * @var array
     */
    protected $columns = array(
        'title',
        'description',
        'authorities',
    );


    /**
     * ilOrgUnitPositionTableGUI constructor.
     *
     * @param \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands $parent_obj
     * @param string                                       $parent_cmd
     */
    public function __construct(BaseCommands $parent_obj, $parent_cmd)
    {
        $this->DIC = $GLOBALS["DIC"];
        $this->setPrefix('orgu_types_table');
        $this->setId('orgu_types_table');
        parent::__construct($parent_obj, $parent_cmd);
        $this->setRowTemplate('tpl.position_row.html', 'Modules/OrgUnit');
        $this->initColumns();
        $this->addColumn($this->DIC->language()->txt('action'), '', '100px', false, 'text-right');
        $this->buildData();
        $this->setFormAction($this->DIC->ctrl()->getFormAction($this->parent_obj));
    }


    /**
     * Pass data to row template
     *
     * @param array $set
     */
    public function fillRow($set)
    {
        /**
         * @var $obj ilOrgUnitPosition
         */
        $obj = ilOrgUnitPosition::find($set["id"]);

        $this->tpl->setVariable('TITLE', $obj->getTitle());
        $this->tpl->setVariable('DESCRIPTION', $obj->getDescription());
        $this->tpl->setVariable('AUTHORITIES', implode("<br>", $obj->getAuthorities()));

        $this->DIC->ctrl()
                  ->setParameterByClass(ilOrgUnitPositionGUI::class, BaseCommands::AR_ID, $set['id']);
        $selection = new ilAdvancedSelectionListGUI();
        $selection->setListTitle($this->DIC->language()->txt('actions'));
        $selection->setId(BaseCommands::AR_ID . $set['id']);
        $selection->addItem($this->DIC->language()->txt('edit'), 'edit', $this->DIC->ctrl()
                                                                                   ->getLinkTargetByClass(ilOrgUnitPositionGUI::class, ilOrgUnitPositionGUI::CMD_EDIT));
        if (!$obj->isCorePosition()) {
            $selection->addItem($this->DIC->language()->txt('delete'), 'delete', $this->DIC->ctrl()
                                                                                           ->getLinkTargetByClass(ilOrgUnitPositionGUI::class, ilOrgUnitPositionGUI::CMD_CONFIRM_DELETION));
        }

        $this->tpl->setVariable('ACTIONS', $selection->getHTML());
    }


    /**
     * Add columns
     */
    protected function initColumns()
    {
        foreach ($this->columns as $column) {
            $this->addColumn($this->DIC->language()->txt($column), $column);
        }
    }


    /**
     * Build and set data for table.
     */
    protected function buildData()
    {
        $this->setData(ilOrgUnitPosition::getArray());
    }
}
