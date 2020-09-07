<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesMetaData
 */
class ilMDCopyrightUsageTableGUI extends ilTable2GUI
{
    /**
     * @var integer
     */
    protected $copyright_id;

    /**
     * @var ilDBInterface
     */
    protected $db;

    protected $lng;

    protected $filter;
    protected $objects;

    /**
     * ilCopyrightUsageGUI constructor.
     * @param $a_parent_obj ilMDCopyrightUsageGUI
     * @param $a_parent_cmd string
     */
    public function __construct($a_parent_obj, $a_parent_cmd = '')
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->copyright_id = $a_parent_obj->getEntryId();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('meta');

        $this->setId("mdcopusage" . $this->copyright_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }


    /**
     * init table columns, ...
     */
    public function init()
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

    /**
     * Parse table content
     */
    public function parse()
    {
        $data = $this->collectData($this->getCurrentFilter());
        $this->setData($data);
    }

    /**
     * Init Filter
     */
    public function initFilter()
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
     * Get current filter settings
     * @return	array
     */
    protected function getCurrentFilter()
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

    public function fillRow($a_set)
    {
        global $DIC;

        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();

        $icon = $f->icon()->standard($a_set['type'], $this->lng->txt($a_set['type']), "medium");
        $this->tpl->setVariable('OBJ_TYPE_ICON', $r->render($icon));
        $this->tpl->setVariable('TITLE', $a_set['title']);
        $this->tpl->setVariable("DESCRIPTION", $a_set['desc']);
        if ($a_set['references']) {
            $path = new ilPathGUI();
            $path->enableHideLeaf(false);
            $path->enableDisplayCut(true);
            $path->enableTextOnly(false);

            foreach ($a_set['references'] as $reference) {
                $this->tpl->setCurrentBlock("references");
                $this->tpl->setVariable("REFERENCE", $path->getPath(ROOT_FOLDER_ID, $reference));
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
     * @param array $filters
     * @return array
     */
    public function collectData(array $filters)
    {
        $db_data = $this->getDataFromDB();

        $data = array();
        foreach ($db_data as $item) {
            $obj_id = $item['obj_id'];
            if ($filters['title']) {
                if (stripos(ilObject::_lookupTitle($obj_id), $filters['title']) === false) {
                    continue;
                }
            }
            if ($filters['object']) {
                if (ilObject::_lookupType($obj_id) != $filters['object']) {
                    continue;
                }
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

    public function getObjTypesAvailable()
    {
        $query = "SELECT DISTINCT obj_type FROM il_meta_rights " .
            "WHERE description = " . $this->db->quote('il_copyright_entry__' . IL_INST_ID . '__' . $this->copyright_id, 'text') .
            " AND rbac_id = obj_id";
        $result = $this->db->query($query);
        $data = array();
        while ($row = $this->db->fetchAssoc($result)) {
            array_push($data, $row['obj_type']);
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getDataFromDB()
    {
        $query = "SELECT rbac_id, obj_id, obj_type FROM il_meta_rights " .
            "WHERE description = " . $this->db->quote('il_copyright_entry__' . IL_INST_ID . '__' . $this->copyright_id, 'text') .
            ' AND rbac_id != ' . $this->db->quote(0, 'integer') .
            " GROUP BY rbac_id";

        $result = $this->db->query($query);
        $data = array();
        while ($row = $this->db->fetchAssoc($result)) {
            $data[] = array(
                "obj_id" => $row['rbac_id'],
                "obj_type" => $row['obj_type']
            );
        }
        return $data;
    }

    public function getCountSubItemsFromDB($a_rbac_id)
    {
        $query = "SELECT count(rbac_id) total FROM il_meta_rights " .
            "WHERE rbac_id = " . $this->db->quote($a_rbac_id) .
            " AND rbac_id <> obj_id";

        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);

        return $row['total'];
    }
}
