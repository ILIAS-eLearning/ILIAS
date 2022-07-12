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
 * TableGUI class for wiki page templates
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiPageTemplatesTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_wiki_id
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $templates = new ilWikiPageTemplate($a_wiki_id);
        $this->setData($templates->getAllInfo());
        $this->setTitle($lng->txt(""));

        $this->addColumn($this->lng->txt(""), "", "1");
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("wiki_templ_new_pages"), "");
        $this->addColumn($this->lng->txt("wiki_templ_add_to_page"), "");

        $this->setDefaultOrderDirection("asc");
        $this->setDefaultOrderField("title");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.wiki_page_templates_row.html", "Modules/Wiki");

        $this->addMultiCommand("remove", $lng->txt("wiki_remove_template_status"));
        $this->addCommandButton("saveTemplateSettings", $lng->txt("save"));
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("ID", $a_set["wpage_id"]);
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        if ($a_set["new_pages"]) {
            $this->tpl->setVariable("NEW_PAGES_CHECKED", 'checked="checked"');
        }
        if ($a_set["add_to_page"]) {
            $this->tpl->setVariable("ADD_TO_PAGE_CHECKED", 'checked="checked"');
        }
    }
}
