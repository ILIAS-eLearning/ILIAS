<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Modules/StudyProgramme/classes/model/class.ilStudyProgrammeType.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');


class ilStudyProgrammeTypeTableGUI extends ilTable2GUI
{

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var Filesystem
     */
    protected $webdir;

    /**
     * @var array
     */
    protected $columns = array(
        'title', 'description', 'default_language', 'icon',
    );


    /**
     * @param        $parent_obj
     * @param string $parent_cmd
     * @param int    $obj_ref_id
     */
    public function __construct($parent_obj, $parent_cmd, $obj_ref_id)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        $this->webdir = $DIC->filesystem()->web();
        $this->access = $DIC['ilAccess'];
        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->lng = $lng;
        $this->setPrefix('prg_types_table');
        $this->setId('prg_types_table');
        $this->obj_ref_id = $obj_ref_id;

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
     *
     * @param array $set
     */
    public function fillRow($set)
    {
        $icon = "";
        $type = new ilStudyProgrammeType($set['id']);

        if ($this->webdir->has($type->getIconPath(true))) {
            $icon = ilUtil::getWebspaceDir() . '/' . $type->getIconPath(true);
        }

        $this->tpl->setVariable('TITLE', $set['title']);
        $this->tpl->setVariable('DESCRIPTION', $set['description']);
        $this->tpl->setVariable('DEFAULT_LANG', $set['default_language']);

        if ($set["icon"]) {
            $this->tpl->setCurrentBlock("icon");
            $this->tpl->setVariable('ICON', $icon);
            $this->tpl->setVariable('ICON_ALT', $set["icon"]);
            $this->tpl->parseCurrentBlock();
        }

        if ($this->access->checkAccess("write", "", $this->obj_ref_id)) {
            $this->ctrl->setParameterByClass("ilstudyprogrammetypegui", "type_id", $set['id']);
            $selection = new ilAdvancedSelectionListGUI();
            $selection->setListTitle($this->lng->txt('actions'));
            $selection->setId('action_prg_type' . $set['id']);
            $selection->addItem($this->lng->txt('edit'), 'edit', $this->ctrl->getLinkTargetByClass('ilstudyprogrammetypegui', 'edit'));
            $selection->addItem($this->lng->txt('delete'), 'delete', $this->ctrl->getLinkTargetByClass('ilstudyprogrammetypegui', 'delete'));
            $this->tpl->setVariable('ACTIONS', $selection->getHTML());
        }
    }

    /**
     * Add columns
     */
    protected function initColumns()
    {
        foreach ($this->columns as $column) {
            $this->addColumn($this->lng->txt($column), $column);
        }
    }

    /**
     * Build and set data for table.
     */
    protected function buildData()
    {
        $types = ilStudyProgrammeType::getAllTypes();
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
