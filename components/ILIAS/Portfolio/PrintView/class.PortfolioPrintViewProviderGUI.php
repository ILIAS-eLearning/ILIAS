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

namespace ILIAS\Portfolio;

use ILIAS\COPage;
use ILIAS\Export;
use ilPropertyFormGUI;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PortfolioPrintViewProviderGUI extends Export\AbstractPrintViewProvider
{
    protected bool $include_declaration = false;
    protected StandardGUIRequest $port_request;
    protected \ilLanguage $lng;
    protected \ilObjPortfolio $portfolio;
    protected ?array $selected_pages = null;
    protected bool $include_signature;
    protected ?\ilPortfolioDeclarationOfAuthorship $declaration_of_authorship = null;
    protected \ilObjUser $user;
    protected \ilCtrl $ctrl;

    public function __construct(
        \ilLanguage $lng,
        \ilCtrl $ctrl,
        \ilObjPortfolio $portfolio,
        bool $include_signature = false,
        array $selected_pages = null,
        bool $include_declaration = false
    ) {
        global $DIC;

        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->portfolio = $portfolio;
        $this->selected_pages = $selected_pages;
        $this->include_signature = $include_signature;
        $this->include_declaration = $include_declaration;
        $this->port_request = $DIC->portfolio()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    public function withDeclarationOfAuthorship(
        \ilPortfolioDeclarationOfAuthorship $decl,
        \ilObjUser $user
    ): self {
        $c = clone $this;
        $c->declaration_of_authorship = $decl;
        $c->user = $user;
        return $c;
    }

    public function getSelectionForm(): ?ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $pages = \ilPortfolioPage::getAllPortfolioPages($this->portfolio->getId());


        $form = new \ilPropertyFormGUI();

        // declaration of authorship
        if ($this->declaration_of_authorship &&
            $this->declaration_of_authorship->getForUser($this->user) !== "") {
            $cb = new \ilCheckboxInputGUI($this->lng->txt("prtf_decl_authorship"), "decl_author");
            $cb->setInfo($this->declaration_of_authorship->getForUser($this->user));
            $form->addItem($cb);
        }

        // signature
        $cb = new \ilCheckboxInputGUI($this->lng->txt("prtf_signature"), "signature");
        $cb->setInfo($this->lng->txt("prtf_signature_info"));
        $form->addItem($cb);


        // selection type
        $radg = new \ilRadioGroupInputGUI($lng->txt("prtf_print_selection"), "sel_type");
        $radg->setValue("all_pages");
        $op2 = new \ilRadioOption($lng->txt("prtf_all_pages"), "all_pages");
        $radg->addOption($op2);
        $op3 = new \ilRadioOption($lng->txt("prtf_selected_pages"), "selection");
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
                \ilUtil::getImagePath("standard/icon_pg.svg"),
                $lng->txt("page")
            );
        }

        $form->addItem($radg);

        $form->addCommandButton("showPrintView", $lng->txt("exp_show_print_view"));

        $form->setTitle($lng->txt("prtf_print_options"));
        $form->setFormAction(
            $ilCtrl->getFormActionByClass(
                "ilObjPortfolioGUI",
                "showPrintView"
            )
        );

        return $form;
    }

    public function getTemplateInjectors(): array
    {
        $page = new \ilPortfolioPage();
        $page->setEmptyPageXml();
        $resource_collector = new COPage\ResourcesCollector(
            \ilPageObjectGUI::OFFLINE,
            $page
        );
        $resource_injector = new COPage\ResourcesInjector($resource_collector);

        return [
            function ($tpl) use ($resource_injector) {
                $resource_injector->inject($tpl);
            }
        ];
    }

    public function getPages(): array
    {
        $lng = $this->lng;
        $portfolio = $this->portfolio;

        $pages = \ilPortfolioPage::getAllPortfolioPages(
            $portfolio->getId()
        );

        $print_pages = [];

        // cover page
        $cover_tpl = new \ilTemplate(
            "tpl.prtf_cover.html",
            true,
            true,
            "components/ILIAS/Portfolio"
        );
        foreach ($pages as $page) {
            if (is_array($this->selected_pages) &&
                !in_array($page["id"], $this->selected_pages)) {
                continue;
            }
            $cover_tpl->setCurrentBlock("content_item");
            $cover_tpl->setVariable("ITEM_TITLE", $page["title"]);
            $cover_tpl->parseCurrentBlock();
        }

        if ($this->include_signature) {
            $cover_tpl->setCurrentBlock("signature");
            $cover_tpl->setVariable("TXT_SIGNATURE", $lng->txt("prtf_signature_date"));
            $cover_tpl->parseCurrentBlock();
        }

        if (!is_null($this->declaration_of_authorship) && $this->include_declaration) {
            $cover_tpl->setCurrentBlock("decl_author");
            $cover_tpl->setVariable(
                "TXT_DECL_AUTHOR",
                nl2br($this->declaration_of_authorship->getForUser($this->user))
            );
            $cover_tpl->parseCurrentBlock();
        }

        $cover_tpl->setVariable("PORTFOLIO_TITLE", $this->portfolio->getTitle());
        $cover_tpl->setVariable("PORTFOLIO_ICON", \ilUtil::getImagePath("standard/icon_prtf.svg"));

        $cover_tpl->setVariable("TXT_AUTHOR", $lng->txt("prtf_author"));
        $cover_tpl->setVariable("TXT_LINK", $lng->txt("prtf_link"));
        $cover_tpl->setVariable("TXT_DATE", $lng->txt("prtf_date_of_print"));

        $author = \ilObjUser::_lookupName($this->portfolio->getOwner());
        $author_str = $author["firstname"] . " " . $author["lastname"];
        $cover_tpl->setVariable("AUTHOR", $author_str);

        $href = \ilLink::_getStaticLink($this->portfolio->getId(), "prtf");
        $cover_tpl->setVariable("LINK", $href);

        \ilDatePresentation::setUseRelativeDates(false);
        $date_str = \ilDatePresentation::formatDate(new \ilDate(date("Y-m-d"), IL_CAL_DATE));
        $cover_tpl->setVariable("DATE", $date_str);

        $print_pages[] = $cover_tpl->get();

        $page_head_tpl = new \ilTemplate("tpl.prtf_page_head.html", true, true, "components/ILIAS/Portfolio");
        $page_head_tpl->setVariable("AUTHOR", $author_str);
        $page_head_tpl->setVariable("DATE", $date_str);
        $page_head_str = $page_head_tpl->get();

        foreach ($pages as $page) {
            if (is_array($this->selected_pages) &&
                !in_array($page["id"], $this->selected_pages)) {
                continue;
            }
            $page_gui = new \ilPortfolioPageGUI($this->portfolio->getId(), $page["id"]);
            $page_gui->setOutputMode($this->getOutputMode());
            $page_gui->setPresentationTitle($page["title"]);
            $html = $this->ctrl->getHTML($page_gui);
            $print_pages[] = $page_head_str . $html;
        }

        return $print_pages;
    }
}
