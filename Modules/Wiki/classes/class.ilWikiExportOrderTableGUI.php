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
 * TableGUI class for ordering pages to be printed/exported
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilWikiExportOrderTableGUI extends ilTable2GUI
{
    protected int $order;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        array $a_all_pages,
        array $a_page_ids
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $title = "wiki_show_print_view";
        $cmd = "printView";

        $this->setTitle($lng->txt($title));

        $this->addColumn($lng->txt("wiki_ordering"), "", "1");
        $this->addColumn($lng->txt("wiki_page"));

        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), $this->getParentCmd()));
        $this->addCommandButton($this->getParentCmd(), $lng->txt("refresh"));

        $button = ilSubmitButton::getInstance();
        $button->setCaption("continue");
        $button->setCommand($cmd);
        $this->addCommandButtonInstance($button);

        $this->setRowTemplate("tpl.table_row_export_order.html", "Modules/Wiki");
        $this->setLimit(9999);

        $this->getItems($a_all_pages, $a_page_ids);
    }

    protected function getItems(
        array $a_all_pages,
        array $a_page_ids
    ): void {
        $data = array();

        foreach ($a_page_ids as $page_id) {
            $data[] = array(
                "id" => $page_id,
                "title" => $a_all_pages[$page_id]["title"]
            );
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        $this->order += 10;

        $this->tpl->setVariable("PAGE_ID", $a_set["id"]);
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("ORDER", $this->order);
    }
}
