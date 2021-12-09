<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Portfolio;

use \ILIAS\COPage;
use \ILIAS\Export;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class PortfolioPrintViewProviderGUI extends Export\AbstractPrintViewProvider
{
    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilObjPortfolio
     */
    protected $portfolio;

    /**
     * @var array|null
     */
    protected $selected_pages = null;

    /**
     * @var bool
     */
    protected $include_signature;

    /**
     * @var \ilPortfolioDeclarationOfAuthorship
     */
    protected $declaration_of_authorship = null;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * PrintView constructor.
     * @param \ilLanguage     $lng
     * @param \ilObjPortfolio $portfolio
     * @param bool            $include_signature
     * @param array|null      $selected_pages
     */
    public function __construct(
        \ilLanguage $lng,
        \ilCtrl $ctrl,
        \ilObjPortfolio $portfolio,
        bool $include_signature = false,
        array $selected_pages = null
    ) {
        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->portfolio = $portfolio;
        $this->selected_pages = $selected_pages;
        $this->include_signature = $include_signature;
    }

    protected function withDeclarationOfAuthorship(
        \ilPortfolioDeclarationOfAuthorship $decl,
        \ilObjUser $user
    ) : self {
        $c = clone $this;
        $c->declaration_of_authorship = $decl;
        $c->user = $user;
        return $c;
    }

    public function getSelectionForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $pages = \ilPortfolioPage::getAllPortfolioPages($this->portfolio->getId());


        $form = new \ilPropertyFormGUI();

        // declaration of authorship
        if ($this->declaration_of_authorship &&
            $this->declaration_of_authorship->getForUser($this->user) != "") {
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
            if ($p["type"] != \ilPortfolioPage::TYPE_BLOG) {
                $nl->addListNode(
                    $p["id"],
                    $p["title"],
                    0,
                    false,
                    false,
                    \ilUtil::getImagePath("icon_pg.svg"),
                    $lng->txt("page")
                );
            } else {
                $nl->addListNode(
                    $p["id"],
                    $lng->txt("obj_blog") . ": " . \ilObject::_lookupTitle($p["title"]),
                    0,
                    false,
                    false,
                    \ilUtil::getImagePath("icon_blog.svg"),
                    $lng->txt("obj_blog")
                );
                $pages2 = \ilBlogPosting::getAllPostings($p["title"]);
                foreach ($pages2 as $p2) {
                    $nl->addListNode(
                        "b" . $p2["id"],
                        $p2["title"],
                        $p["id"],
                        false,
                        false,
                        \ilUtil::getImagePath("icon_pg.svg"),
                        $lng->txt("page")
                    );
                }
            }
        }

        $form->addItem($radg);

        $form->addCommandButton("exportPDF", $lng->txt("exp_show_print_view"));

        $form->setTitle($lng->txt("prtf_print_options"));
        $form->setFormAction(
            $ilCtrl->getFormActionByClass(
                "ilObjPortfolioGUI",
                "exportPDF"
            )
        );

        return $form;
    }

    public function getTemplateInjectors() : array
    {
        $resource_collector = new COPage\ResourcesCollector(
            \ilPageObjectGUI::OFFLINE,
            new \ilPortfolioPage()
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
            "Modules/Portfolio"
        );
        foreach ($pages as $page) {
            if ($page["type"] != \ilPortfolioPage::TYPE_BLOG) {
                if (is_array($this->selected_pages) &&
                    !in_array($page["id"], $this->selected_pages)) {
                    continue;
                }
                $cover_tpl->setCurrentBlock("content_item");
                $cover_tpl->setVariable("ITEM_TITLE", $page["title"]);
                $cover_tpl->parseCurrentBlock();
            } else {
                $cover_tpl->setCurrentBlock("content_item");
                $cover_tpl->setVariable("ITEM_TITLE", $lng->txt("obj_blog") . ": " . \ilObject::_lookupTitle($page["title"]));
                $cover_tpl->parseCurrentBlock();
            }
        }

        if ($this->include_signature) {
            $cover_tpl->setCurrentBlock("signature");
            $cover_tpl->setVariable("TXT_SIGNATURE", $lng->txt("prtf_signature_date"));
            $cover_tpl->parseCurrentBlock();
        }

        if (!is_null($this->declaration_of_authorship)) {
            $cover_tpl->setCurrentBlock("decl_author");
            $cover_tpl->setVariable(
                "TXT_DECL_AUTHOR",
                nl2br($this->declaration_of_authorship->getForUser($this->user))
            );
            $cover_tpl->parseCurrentBlock();
        }

        $cover_tpl->setVariable("PORTFOLIO_TITLE", $this->portfolio->getTitle());
        $cover_tpl->setVariable("PORTFOLIO_ICON", \ilUtil::getImagePath("icon_prtf.svg"));

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

        $page_head_tpl = new \ilTemplate("tpl.prtf_page_head.html", true, true, "Modules/Portfolio");
        $page_head_tpl->setVariable("AUTHOR", $author_str);
        $page_head_tpl->setVariable("DATE", $date_str);
        $page_head_str = $page_head_tpl->get();

        foreach ($pages as $page) {
            if ($page["type"] != \ilPortfolioPage::TYPE_BLOG) {
                if (is_array($this->selected_pages) &&
                    !in_array($page["id"], $this->selected_pages)) {
                    continue;
                }

                $page_gui = new \ilPortfolioPageGUI($this->portfolio->getId(), $page["id"]);
                $page_gui->setOutputMode($this->getOutputMode());
                $page_gui->setPresentationTitle($page["title"]);
                $html = $page_gui->showPage();
                $print_pages[] = $page_head_str . $html;
            } else {
                $pages2 = \ilBlogPosting::getAllPostings($page["title"]);
                foreach ($pages2 as $p2) {
                    if ($_POST["sel_type"] == "selection" && (!is_array($_POST["obj_id"]) || !in_array("b" . $p2["id"], $_POST["obj_id"]))) {
                        continue;
                    }
                    $page_gui = new \ilBlogPostingGUI(0, null, $p2["id"]);
                    $page_gui->setFileDownloadLink("#");
                    $page_gui->setFullscreenLink("#");
                    $page_gui->setSourcecodeDownloadScript("#");
                    $page_gui->setOutputMode($this->getOutputMode());
                    $print_pages[] = $page_head_str . $page_gui->showPage(\ilObject::_lookupTitle($page["title"]) . ": " . $page_gui->getBlogPosting()->getTitle());
                }
            }
        }

        return $print_pages;
    }
}
