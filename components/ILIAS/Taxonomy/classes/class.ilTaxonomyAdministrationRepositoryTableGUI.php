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
 * TableGUI class for repository taxonomies
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilTaxonomyAdministrationRepositoryTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected int $obj_id;
    protected ilObject $obj;

    public function __construct($a_parent_obj, $a_parent_cmd, ilObject $a_obj)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();

        $this->obj = $a_obj;

        $this->setId("tax_adm_repo");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn($this->lng->txt("obj_tax"), "tax_title");
        $this->addColumn($this->lng->txt("status"), "status");
        $this->addColumn($this->lng->txt("object"), "obj_title");

        $this->setDefaultOrderField("tax_title");
        $this->setDefaultOrderDirection("asc");

        $this->setRowTemplate("tpl.tax_admin_repo_row.html", "Services/Taxonomy");

        $this->initItems();
    }

    protected function initItems(): void
    {
        $data = array();

        foreach ($this->obj->getRepositoryTaxonomies() as $tax_id => $objs) {
            foreach ($objs as $obj_id => $obj) {
                $idx = $tax_id . "_" . $obj_id;
                if (!isset($data[$idx])) {
                    $data[$idx] = array(
                        "tax_title" => $obj["tax_title"],
                        "obj_title" => $obj["obj_title"],
                        "tax_status" => $obj["tax_status"],
                        "references" => array()
                    );
                }

                $path = $obj["path"];
                array_pop($path);
                $path = implode(" &rsaquo; ", $path);

                $data[$idx]["references"][$obj["ref_id"]] =
                    array(
                        "path" => $path
                        ,
                        "url" => ilLink::_getLink($obj["ref_id"])
                    );
            }
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        foreach ($a_set["references"] as $ref) {
            $this->tpl->setCurrentBlock("obj_bl");
            $this->tpl->setVariable("OBJ_TITLE", $a_set["obj_title"]);
            $this->tpl->setVariable("OBJ_PATH", $ref["path"]);
            $this->tpl->setVariable("OBJ_URL", $ref["url"]);
            $this->tpl->parseCurrentBlock();
        }

        if ($a_set["tax_status"]) {
            $this->tpl->setVariable("TAX_STATUS", $this->lng->txt("active"));
            $this->tpl->setVariable("TAX_STATUS_COLOR", "smallgreen");
        } else {
            $this->tpl->setVariable("TAX_STATUS", $this->lng->txt("inactive"));
            $this->tpl->setVariable("TAX_STATUS_COLOR", "smallred");
        }

        $this->tpl->setVariable("TAX_TITLE", $a_set["tax_title"]);
    }
}
