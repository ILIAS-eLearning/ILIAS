<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');
/**
 * Session data set class
 *
 * @author Fabian Wolf <wolf@leifos.com>
 * @version $Id$
 * @ingroup ingroup ModulesSession
 */
class ilSessionMaterialsTableGUI extends ilTable2GUI
{
    protected $container_ref_id;
    protected $material_items;
    protected $filter; // [array]

    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui;

    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $renderer;

    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tree = $DIC['tree'];

        $this->ui = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();

        $this->setId("sess_materials_" . $a_parent_obj->object->getId());

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->parent_ref_id = $tree->getParentId($a_parent_obj->object->getRefId());
        $this->parent_object_id = $a_parent_obj->object->getId();

        //$this->setEnableNumInfo(false);
        //$this->setLimit(100);
        $this->setRowTemplate("tpl.session_materials_row.html", "Modules/Session");

        $this->setFormName('materials');
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->addColumn("", "f", 1);
        $this->addColumn($lng->txt("crs_materials"), "object", "90%");
        $this->addColumn($lng->txt("sess_is_assigned"), "active", 5);
        //todo can I remove this?
        $this->setSelectAllCheckbox('items');

        $this->setFilterCommand("applyFilter");
        $this->setResetCommand("resetFilter");

        $this->initFilter();
        $this->lng->loadLanguageModule('sess');
    }

    /**
     * Get data and put it into an array
     */
    public function getDataFromDb()
    {
        global $DIC;

        $tree = $DIC['tree'];
        $objDefinition = $DIC['objDefinition'];


        $nodes = $tree->getSubTree($tree->getNodeData($this->parent_ref_id));
        $materials = array();

        foreach ($nodes as $node) {
            // No side blocks here
            if ($node['child'] == $this->parent_ref_id ||
                $objDefinition->isSideBlock($node['type']) ||
                in_array($node['type'], array('sess', 'itgr', 'rolf'))) {
                continue;
            }

            if ($node['type'] == 'rolf') {
                continue;
            }

            if (!empty($this->getMaterialItems())) {
                $node["sorthash"] = (int) (!in_array($node['ref_id'], $this->getMaterialItems())) . $node["title"];
            }
            $materials[] = $node;
        }

        $materials = ilUtil::sortArray($materials, "sorthash", "ASC");

        if (!empty($this->filter)) {
            $materials = $this->filterData($materials);
        }
        return $materials;
    }

    /**
     * Apply the filters to the data
     * @param $a_data
     * @return mixed
     */
    public function filterData($a_data)
    {
        $data_filtered = $a_data;

        //Filter by title
        if ($this->filter["title"]) {
            foreach ($data_filtered as $key => $material) {
                $title = $material["title"];
                if (stripos($title, $this->filter["title"]) === false) {
                    unset($data_filtered[$key]);
                }
            }
        }

        //Filter by obj type
        if ($this->filter['type']) {
            foreach ($data_filtered as $key => $material) {
                $type = $material["type"];
                //types can be: file, exc
                if ($type != $this->filter["type"]) {
                    unset($data_filtered[$key]);
                }
            }
        }

        //Filter by status
        if ($this->filter["status"]) {
            //items_ref = materials already assigned.
            $assigned_items = new ilEventItems($this->parent_object_id);
            $assigned_items = $assigned_items->getItems();

            if ($this->filter["status"]== "assigned") {
                foreach ($data_filtered as $key => $material) {
                    if (!in_array($material["ref_id"], $assigned_items)) {
                        unset($data_filtered[$key]);
                    }
                }
            } elseif ($this->filter["status"] == "notassigned") {
                foreach ($data_filtered as $key => $material) {
                    if (in_array($material["ref_id"], $assigned_items)) {
                        unset($data_filtered[$key]);
                    }
                }
            }
        }

        return $data_filtered;
    }

    public function setMaterials($a_materials)
    {
        $this->setData($a_materials);
    }

    /**
     * Fill a single data row.
     */
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable('TYPE_IMG', ilObject::_getIcon('', 'tiny', $a_set['type']));
        $this->tpl->setVariable('IMG_ALT', $this->lng->txt('obj_' . $a_set['type']));

        $this->tpl->setVariable("VAL_POSTNAME", "items");
        $this->tpl->setVariable("VAL_ID", $a_set['ref_id']);

        $this->tpl->setVariable("COLL_TITLE", $a_set['title']);

        if (strlen($a_set['description'])) {
            $this->tpl->setVariable("COLL_DESC", $a_set['description']);
        }
        if (in_array($a_set['ref_id'], $this->getMaterialItems())) {
            $ass_glyph = $this->ui->glyph()->apply();
            $this->tpl->setVariable("ASSIGNED_IMG_OK", $this->renderer->render($ass_glyph));
        }


        include_once('./Services/Tree/classes/class.ilPathGUI.php');
        $path = new ilPathGUI();
        $path->enableDisplayCut(true);
        $path->enableTextOnly(false);
        $this->tpl->setVariable("COLL_PATH", $path->getPath($this->getContainerRefId(), $a_set['ref_id']));
    }

    /**
     * Set Material Items
     * @param $a_set
     */
    public function setMaterialItems($a_set)
    {
        $this->material_items = $a_set;
    }

    /**
     * Get Material Items
     * @return mixed
     */
    public function getMaterialItems()
    {
        return $this->material_items;
    }

    /**
     * Set Mcontainer ref id
     * @param $a_set
     */
    public function setContainerRefId($a_set)
    {
        $this->container_ref_id = $a_set;
    }

    /**
     * Get container ref id
     * @return mixed
     */
    public function getContainerRefId()
    {
        return $this->container_ref_id;
    }

    /**
     * Get object types available in this specific session.
     * @return array
     */
    public function typesAvailable()
    {
        $items = $this->getDataFromDb();

        $all_types = array();
        foreach ($items as $item) {
            array_push($all_types, $item["type"]);
        }
        return array_values(array_unique($all_types));
    }

    /**
     * Filters initialization.
     */
    public function initFilter()
    {
        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();	// get currenty value from session (always after addFilterItem())
        $this->filter["title"] = $ti->getValue();

        // types
        //todo remove banned types if necessary.
        $filter_types = $this->typesAvailable();
        $types = array();
        $types[0] = $this->lng->txt('sess_filter_all_types');
        foreach ($filter_types as $type) {
            $types["$type"] = $this->lng->txt("obj_" . $type);
        }

        $select = new ilSelectInputGUI($this->lng->txt("type"), "type");
        $select->setOptions($types);
        $this->addFilterItem($select);
        $select->readFromSession();
        $this->filter["type"] = $select->getValue();

        // status
        $status = array();
        $status[0] = "-";
        $status["notassigned"] = $this->lng->txt("sess_filter_not_assigned");
        $status["assigned"] = $this->lng->txt("assigned");

        $select_status = new ilSelectInputGUI($this->lng->txt("assigned"), "status");
        $select_status->setOptions($status);
        $this->addFilterItem($select_status);
        $select_status->readFromSession();
        $this->filter['status'] = $select_status->getValue();
    }
}
