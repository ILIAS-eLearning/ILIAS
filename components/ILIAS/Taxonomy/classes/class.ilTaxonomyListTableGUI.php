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
 * TableGUI class for taxonomy list
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaxonomyListTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected int $requested_tax_id;
    protected int $assigned_object_id;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, int $a_assigned_object_id, $a_info = null)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->assigned_object_id = $a_assigned_object_id;

        $this->setData(ilObjTaxonomy::getUsageOfObject($this->assigned_object_id, true));
        $this->setTitle($lng->txt("obj_taxf"));
        $this->setDescription($a_info);

        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("description"));
        $this->addColumn($this->lng->txt("actions"));

        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.taxonomy_list_row.html", "Services/Taxonomy");

        // @todo introduce request wrapper
        $params = $DIC->http()->request()->getQueryParams();
        $this->requested_tax_id = (int) ($params["tax_id"] ?? null);
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this->parent_obj, "tax_id", $a_set["tax_id"]);

        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget($this->parent_obj, "listNodes"));
        $this->tpl->setVariable("CMD", $lng->txt("edit"));
        $this->tpl->parseCurrentBlock();
        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget($this->parent_obj, "confirmDeleteTaxonomy"));
        $this->tpl->setVariable("CMD", $lng->txt("delete"));

        $this->tpl->parseCurrentBlock();
        $ilCtrl->setParameter($this->parent_obj, "tax_id", $this->requested_tax_id);

        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("DESCRIPTION", ilObject::_lookupDescription($a_set["tax_id"]));
    }
}
