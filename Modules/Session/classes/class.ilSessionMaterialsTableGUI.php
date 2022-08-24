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
 ********************************************************************
 */

/**
 * Session data set class
 *
 * @author Fabian Wolf <wolf@leifos.com>
 * @version $Id$
 * @ingroup ingroup ModulesSession
 */
class ilSessionMaterialsTableGUI extends ilTable2GUI
{
    protected ILIAS\UI\Factory $ui;
    protected ILIAS\UI\Renderer $renderer;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilTree $tree;
    protected ilObjectDefinition $objDefinition;
    protected int $container_ref_id = 0;
    protected array $material_items = [];
    protected array $filter = [];
    protected int $parent_ref_id = 0;
    protected int $parent_object_id = 0;

    public function __construct(object $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $this->objDefinition = $DIC['objDefinition'];
        $this->ui = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();

        $this->setId("sess_materials_" . $a_parent_obj->getCurrentObject()->getId());

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->parent_ref_id = $this->tree->getParentId($a_parent_obj->getCurrentObject()->getRefId());
        $this->parent_object_id = $a_parent_obj->getCurrentObject()->getId();

        //$this->setEnableNumInfo(false);
        //$this->setLimit(100);
        $this->setRowTemplate("tpl.session_materials_row.html", "Modules/Session");

        $this->setFormName('materials');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->addColumn("", "f", "1");
        $this->addColumn($this->lng->txt("crs_materials"), "object", "90%");
        $this->addColumn($this->lng->txt("sess_is_assigned"), "active", "5");
        //todo can I remove this?
        $this->setSelectAllCheckbox('items');

        $this->setFilterCommand("applyFilter");
        $this->setResetCommand("resetFilter");

        $this->initFilter();
        $this->lng->loadLanguageModule('sess');
    }

    public function setMaterialItems(array $a_set): void
    {
        $this->material_items = $a_set;
    }

    public function getMaterialItems(): array
    {
        return $this->material_items;
    }

    public function setContainerRefId(int $a_set): void
    {
        $this->container_ref_id = $a_set;
    }

    public function getContainerRefId(): int
    {
        return $this->container_ref_id;
    }

    public function getDataFromDb(): array
    {
        $tree = $this->tree;
        $objDefinition = $this->objDefinition;

        $nodes = $tree->getSubTree($tree->getNodeData($this->parent_ref_id));
        $materials = [];

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

        $materials = ilArrayUtil::sortArray($materials, "sorthash", "ASC");

        if (!empty($this->filter)) {
            $materials = $this->filterData($materials);
        }
        return $materials;
    }

    public function filterData(array $a_data): array
    {
        $data_filtered = $a_data;

        //Filter by title
        if (isset($this->filter["title"]) && $this->filter['title'] !== '') {
            foreach ($data_filtered as $key => $material) {
                $title = $material["title"];
                if (stripos($title, $this->filter["title"]) === false) {
                    unset($data_filtered[$key]);
                }
            }
        }

        //Filter by obj type
        if (isset($this->filter['type']) && $this->filter['type'] !== '') {
            foreach ($data_filtered as $key => $material) {
                $type = $material["type"];
                //types can be: file, exc
                if ($type != $this->filter["type"]) {
                    unset($data_filtered[$key]);
                }
            }
        }

        //Filter by status
        if (isset($this->filter["status"]) && $this->filter['status'] !== '') {
            //items_ref = materials already assigned.
            $assigned_items = new ilEventItems($this->parent_object_id);
            $assigned_items = $assigned_items->getItems();

            if ($this->filter["status"] == "assigned") {
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

    public function setMaterials(array $a_materials): void
    {
        $this->setData($a_materials);
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('TYPE_IMG', ilObject::_getIcon(0, 'tiny', $a_set['type']));
        $this->tpl->setVariable('IMG_ALT', $this->lng->txt('obj_' . $a_set['type']));

        $this->tpl->setVariable("VAL_POSTNAME", "items");
        $this->tpl->setVariable("VAL_ID", $a_set['ref_id']);

        $this->tpl->setVariable("COLL_TITLE", $a_set['title']);

        if (strlen($a_set['description'])) {
            $this->tpl->setVariable("COLL_DESC", $a_set['description']);
        }
        if (in_array($a_set['ref_id'], $this->getMaterialItems())) {
            $ass_glyph = $this->ui->symbol()->glyph()->apply();
            $this->tpl->setVariable("ASSIGNED_IMG_OK", $this->renderer->render($ass_glyph));
        }

        $path = new ilPathGUI();
        $path->enableDisplayCut(true);
        $path->enableTextOnly(false);
        $this->tpl->setVariable("COLL_PATH", $path->getPath($this->getContainerRefId(), (int) $a_set['ref_id']));
    }

    /**
     * Get object types available in this specific session.
     */
    public function typesAvailable(): array
    {
        $items = $this->getDataFromDb();

        $all_types = [];
        foreach ($items as $item) {
            $all_types[] = $item["type"];
        }
        return array_values(array_unique($all_types));
    }

    public function initFilter(): void
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
        $types = [];
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
        $status = [];
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
