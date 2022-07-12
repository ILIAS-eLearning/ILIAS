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

use ILIAS\Filesystem\Filesystem;

class ilStudyProgrammeTypeTableGUI extends ilTable2GUI
{
    protected int $obj_ref_id;
    protected ilStudyProgrammeTypeRepository $type_repo;
    protected ilTabsGUI $tabs;
    protected ilAccessHandler $access;
    protected Filesystem $web_dir;

    protected array $columns = ['title', 'description', 'default_language', 'icon'];

    public function __construct(
        ilStudyProgrammeTypeGUI $parent_obj,
        string $parent_cmd,
        int $obj_ref_id,
        ilStudyProgrammeTypeRepository $type_repo,
        ilCtrl $ctrl,
        ilTabsGUI $tabs,
        ilAccess $access,
        ilLanguage $lng,
        Filesystem $web_dir
    ) {
        $this->obj_ref_id = $obj_ref_id;
        $this->type_repo = $type_repo;
        $this->ctrl = $ctrl;
        $this->tabs = $tabs;
        $this->access = $access;
        $this->lng = $lng;
        $this->web_dir = $web_dir;

        $this->setPrefix('prg_types_table');
        $this->setId('prg_types_table');

        parent::__construct($parent_obj, $parent_cmd);

        $this->setRowTemplate('tpl.types_row.html', 'Modules/StudyProgramme');
        $this->initColumns();
        $action_column = "";
        if ($this->access->checkAccess("write", "", $this->obj_ref_id)) {
            $action_column = $this->lng->txt('action');
        }
        $this->addColumn($action_column);
        $this->buildData();
        $this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
    }

    /**
     * Pass data to row template
     * @param array $a_set
     */
    protected function fillRow(array $a_set) : void
    {
        $icon = "";
        $type = $this->type_repo->getType((int) $a_set['id']);

        if ($this->web_dir->has($type->getIconPath(true))) {
            $icon = ilFileUtils::getWebspaceDir() . '/' . $type->getIconPath(true);
        }

        $this->tpl->setVariable('TITLE', $a_set['title']);
        $this->tpl->setVariable('DESCRIPTION', $a_set['description']);
        $this->tpl->setVariable('DEFAULT_LANG', $a_set['default_language']);

        if ($a_set["icon"]) {
            $this->tpl->setCurrentBlock("icon");
            $this->tpl->setVariable('ICON', $icon);
            $this->tpl->setVariable('ICON_ALT', $a_set["icon"]);
            $this->tpl->parseCurrentBlock();
        }

        if ($this->access->checkAccess("write", "", $this->obj_ref_id)) {
            $this->ctrl->setParameterByClass("ilstudyprogrammetypegui", "type_id", $a_set['id']);
            $selection = new ilAdvancedSelectionListGUI();
            $selection->setListTitle($this->lng->txt('actions'));
            $selection->setId('action_prg_type' . $a_set['id']);
            $selection->addItem(
                $this->lng->txt('edit'),
                'edit',
                $this->ctrl->getLinkTargetByClass('ilstudyprogrammetypegui', 'edit')
            );
            $selection->addItem(
                $this->lng->txt('delete'),
                'delete',
                $this->ctrl->getLinkTargetByClass('ilstudyprogrammetypegui', 'delete')
            );
            $this->tpl->setVariable('ACTIONS', $selection->getHTML());
        }
    }

    /**
     * Add columns
     */
    protected function initColumns() : void
    {
        foreach ($this->columns as $column) {
            $this->addColumn($this->lng->txt($column), $column);
        }
    }

    /**
     * Build and set data for table.
     */
    protected function buildData() : void
    {
        $types = $this->type_repo->getAllTypes();
        $data = array();
        /** @var $type ilStudyProgrammeType */
        foreach ($types as $type) {
            $row = array();
            $row['id'] = $type->getId();
            $row['title'] = $type->getTitle($type->getDefaultLang());
            $row['default_language'] = $type->getDefaultLang();
            $row['description'] = $type->getDescription($type->getDefaultLang());
            $row['icon'] = $type->getIcon();
            $data[] = $row;
        }
        $this->setData($data);
    }
}
