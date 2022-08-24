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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * @author  Jesús López <lopez@leifos.com>
 * @version $Id$
 * @ingroup ServicesMetaData
 */
class ilMDCopyrightUsageTableGUI extends ilTable2GUI
{
    protected int $copyright_id;

    protected ilDBInterface $db;
    protected Factory $ui_factory;
    protected Renderer $ui_renderer;

    protected array $filter = [];
    protected array $objects = [];

    public function __construct(ilMDCopyrightUsageGUI $a_parent_obj, string $a_parent_cmd = '')
    {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->db = $DIC->database();
        $this->copyright_id = $a_parent_obj->getEntryId();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('meta');

        $this->setId("mdcopusage" . $this->copyright_id);
    }

    public function init(): void
    {
        $md_entry = new ilMDCopyrightSelectionEntry($this->copyright_id);
        $this->setTitle($md_entry->getTitle());

        $this->addColumn($this->lng->txt('object'), 'object');
        $this->addColumn($this->lng->txt('meta_references'), 'references');
        $this->addColumn($this->lng->txt('meta_copyright_sub_items'), 'subitems');
        $this->addColumn($this->lng->txt('owner'), 'owner');

        $this->setRowTemplate("tpl.show_copyright_usages_row.html", "Services/MetaData");
        $this->setFormAction($this->ctrl->getFormAction(
            $this->getParentObject(),
            $this->getParentCmd()
        ));
        $this->setDisableFilterHiding(true);

        $this->initFilter();
    }

    public function parse(): void
    {
        $data = $this->collectData($this->getCurrentFilter());
        $this->setData($data);
    }

    public function initFilter(): void
    {
        $title = $this->addFilterItemByMetaType(
            "title",
            ilTable2GUI::FILTER_TEXT,
            false,
            $this->lng->txt("object") . " " . $this->lng->txt("title")
        );
        $this->filter["title"] = $title->getValue();

        //object
        $this->objects = array();
        foreach ($this->getObjTypesAvailable() as $item) {
            $this->objects[$item] = $this->lng->txt("obj_" . $item);
        }
        $item = $this->addFilterItemByMetaType("object", ilTable2GUI::FILTER_SELECT);
        $item->setOptions(array("" => "-") + $this->objects);
        $this->filter["object"] = $item->getValue();
    }

    /**
     * @return string[]
     */
    protected function getCurrentFilter(): array
    {
        $filter = array();
        if ($this->filter["title"]) {
            $filter["title"] = $this->filter["title"];
        }

        if ($this->filter['object']) {
            $filter['object'] = $this->filter['object'];
        }
        return $filter;
    }

    protected function fillRow(array $a_set): void
    {
        $icon = $this->ui_factory->symbol()->icon()->standard(
            $a_set['type'],
            $this->lng->txt($a_set['type']),
            "medium"
        );
        $this->tpl->setVariable('OBJ_TYPE_ICON', $this->ui_renderer->render($icon));
        $this->tpl->setVariable('TITLE', $a_set['title']);
        $this->tpl->setVariable("DESCRIPTION", $a_set['desc']);
        if ($a_set['references']) {
            $path = new ilPathGUI();
            $path->enableHideLeaf(false);
            $path->enableDisplayCut(true);
            $path->enableTextOnly(false);

            foreach ($a_set['references'] as $reference) {
                $this->tpl->setCurrentBlock("references");
                $this->tpl->setVariable("REFERENCE", $path->getPath(ROOT_FOLDER_ID, (int) $reference));
                $this->tpl->parseCurrentBlock();
            }
        }

        $this->tpl->setVariable('SUB_ITEMS', $a_set['sub_items']);

        //TODO FIX WHITE PAGE OWNER LINK
        if ($a_set['owner_link']) {
            $this->tpl->setCurrentBlock("link_owner");
            $this->tpl->setVariable("OWNER_LINK", $a_set['owner_link']);
            $this->tpl->setVariable('OWNER', $a_set['owner_name']);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock("owner");
            $this->tpl->setVariable('OWNER', $a_set['owner_name']);
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * @param string[] $filters
     * @return array<string, mixed>
     * @noinspection PhpParamsInspection
     */
    public function collectData(array $filters): array
    {
        $db_data = $this->getDataFromDB();

        $data = array();
        foreach ($db_data as $item) {
            $obj_id = $item['obj_id'];
            if ($filters['title'] && stripos(ilObject::_lookupTitle($obj_id), $filters['title']) === false) {
                continue;
            }
            if ($filters['object'] && ilObject::_lookupType($obj_id) !== $filters['object']) {
                continue;
            }
            $data[] = array(
                "obj_id" => $obj_id,
                "type" => ilObject::_lookupType($obj_id),
                "title" => ilObject::_lookupTitle($obj_id),
                "desc" => ilObject::_lookupDescription($obj_id),
                "references" => ilObject::_getAllReferences($obj_id),
                "owner_name" => ilUserUtil::getNamePresentation(ilObject::_lookupOwner($obj_id)),
                "owner_link" => ilUserUtil::getProfileLink(ilObject::_lookupOwner($obj_id)),
                "sub_items" => $this->getCountSubItemsFromDB($obj_id)
            );
        }

        return $data;
    }

    /**
     * @return string[]
     */
    public function getObjTypesAvailable(): array
    {
        $query = "SELECT DISTINCT obj_type FROM il_meta_rights " .
            "WHERE description = " . $this->db->quote(
                'il_copyright_entry__' . IL_INST_ID . '__' . $this->copyright_id,
                'text'
            ) .
            " AND rbac_id = obj_id";
        $result = $this->db->query($query);
        $data = array();
        while ($row = $this->db->fetchAssoc($result)) {
            $data[] = $row['obj_type'];
        }
        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataFromDB(): array
    {
        $query = "SELECT rbac_id, obj_id, obj_type FROM il_meta_rights " .
            "WHERE description = " . $this->db->quote(
                'il_copyright_entry__' . IL_INST_ID . '__' . $this->copyright_id,
                'text'
            ) .
            ' AND rbac_id != ' . $this->db->quote(0, 'integer') .
            " GROUP BY rbac_id";

        $result = $this->db->query($query);
        $data = array();
        while ($row = $this->db->fetchAssoc($result)) {
            $data[] = array(
                "obj_id" => (int) $row['rbac_id'],
                "obj_type" => (string) $row['obj_type']
            );
        }
        return $data;
    }

    public function getCountSubItemsFromDB(int $a_rbac_id): int
    {
        $query = "SELECT count(rbac_id) total FROM il_meta_rights " .
            "WHERE rbac_id = " . $this->db->quote($a_rbac_id, ilDBConstants::T_INTEGER) .
            " AND rbac_id != obj_id";

        $result = $this->db->query($query);
        while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->total;
        }
        return 0;
    }
}
