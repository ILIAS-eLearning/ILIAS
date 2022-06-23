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

namespace ILIAS\Wiki;

use ILIAS\COPage;
use ILIAS\Export;
use ilPropertyFormGUI;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class WikiPrintViewProviderGUI extends Export\AbstractPrintViewProvider
{
    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var array|null
     */
    protected $selected_pages = null;

    /**
     * @var \ilObjWiki
     */
    protected $wiki;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * PrintView constructor.
     * @param \ilLanguage $lng
     * @param \ilCtrl     $ctrl
     * @param int         $wiki_ref_id
     * @param array       $selected_pages
     */
    public function __construct(
        \ilLanguage $lng,
        \ilCtrl $ctrl,
        int $wiki_ref_id,
        ?array $selected_pages
    ) {
        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->wiki = new \ilObjWiki($wiki_ref_id);
        $this->selected_pages = (!is_null($selected_pages))
            ? $selected_pages
            : array_map(
                static function ($p) {
                    return $p["id"];
                },
                \ilWikiPage::getAllWikiPages($this->wiki->getId())
            );
    }

    public function getTemplateInjectors() : array
    {
        $resource_collector = new COPage\ResourcesCollector(
            \ilPageObjectGUI::OFFLINE,
            new \ilWikiPage()
        );
        $resource_injector = new COPage\ResourcesInjector($resource_collector);

        return [
            function ($tpl) use ($resource_injector) {
                $resource_injector->inject($tpl);
            }
        ];
    }

    public function getPages() : array
    {
        $print_pages = [];

        foreach ($this->selected_pages as $p_id) {
            $page_gui = new \ilWikiPageGUI($p_id);
            $page_gui->setWiki($this->wiki);
            $page_gui->setOutputMode($this->getOutputMode());
            $print_pages[] = $page_gui->showPage();
        }

        return $print_pages;
    }

    public function getSelectionForm() : ?ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $pages = \ilWikiPage::getAllWikiPages(
            \ilObject::_lookupObjId($this->wiki->getRefId())
        );

        $form = new \ilPropertyFormGUI();

        //var_dump($pages);
        // selection type
        $radg = new \ilRadioGroupInputGUI($lng->txt("cont_selection"), "sel_type");
        $radg->setValue("page");
        $op1 = new \ilRadioOption($lng->txt("cont_current_page"), "page");
        $radg->addOption($op1);
        $op2 = new \ilRadioOption($lng->txt("wiki_whole_wiki")
            . " (" . $lng->txt("wiki_pages") . ": " . count($pages) . ")", "wiki");
        $radg->addOption($op2);
        $op3 = new \ilRadioOption($lng->txt("wiki_selected_pages"), "selection");
        $radg->addOption($op3);

        $nl = new \ilNestedListInputGUI("", "obj_id");
        $op3->addSubItem($nl);

        foreach ($pages as $p) {
            $nl->addListNode(
                $p["id"],
                $p["title"],
                0,
                false,
                false,
                \ilUtil::getImagePath("icon_pg.svg"),
                $lng->txt("wiki_page")
            );
        }

        $form->addItem($radg);

        $form->addCommandButton("printViewOrder", $lng->txt("wiki_show_print_view"));

        $form->setTitle($lng->txt("cont_print_selection"));
        $form->setFormAction(
            $ilCtrl->getFormActionByClass(
                "ilWikiPageGUI",
                "printViewOrder"
            )
        );

        return $form;
    }
}
