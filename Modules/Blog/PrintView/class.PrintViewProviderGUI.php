<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Blog;

use \ILIAS\COPage;
use \ILIAS\Export;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class PrintViewProviderGUI extends Export\AbstractPrintViewProvider
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
     * @var \ilObjBlog
     */
    protected $blog;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    protected $access_handler;

    /**
     * @var int
     */
    protected $style_sheet_id = 0;

    /**
     * @var int
     */
    protected $node_id = 0;

    /**
     * PrintView constructor.
     * @param \ilLanguage $lng
     * @param \ilCtrl     $ctrl
     * @param \ilObjBlog  $blog
     * @param array       $selected_pages
     */
    public function __construct(
        \ilLanguage $lng,
        \ilCtrl $ctrl,
        \ilObjBlog $blog,
        int $node_id,
        $access_handler,
        $style_id,
        ?array $selected_pages = null
    ) {
        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->blog = $blog;
        $this->node_id = $node_id;
        $this->access_handler = $access_handler;
        $this->style_sheet_id = $style_id;

        $this->selected_pages = $selected_pages;
    }

    public function getTemplateInjectors() : array
    {
        $resource_collector = new COPage\ResourcesCollector(
            \ilPageObjectGUI::OFFLINE,
            new \ilBlogPosting()
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

        $selected_pages = (is_array($this->selected_pages))
            ? $this->selected_pages
            : array_map(function ($i) {
                return $i["id"];
            }, \ilBlogPosting::getAllPostings($this->blog->getId()));

        foreach ($selected_pages as $p_id) {
            $page_gui = new \ilBlogPostingGUI(
                $this->node_id,
                $this->access_handler,
                $p_id,
                0,
                false,
                false,
                $this->style_sheet_id
            );
            $page_gui->setOutputMode($this->getOutputMode());
            $print_pages[] = $page_gui->showPage();
        }

        return $print_pages;
    }

    public function getSelectionForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $postings = \ilBlogPosting::getAllPostings($this->blog->getId());

        $form = new \ilPropertyFormGUI();

        //var_dump($pages);
        // selection type
        $radg = new \ilRadioGroupInputGUI($lng->txt("cont_selection"), "sel_type");
        $radg->setValue("page");
        $op1 = new \ilRadioOption($lng->txt("blog_whole_blog")
            . " (" . $lng->txt("blog_postings") . ": " . count($postings) . ")", "wiki");
        $radg->addOption($op1);
        $op2 = new \ilRadioOption($lng->txt("blog_selected_pages"), "selection");
        $radg->addOption($op2);

        $nl = new \ilNestedListInputGUI("", "obj_id");
        $op2->addSubItem($nl);

        foreach ($postings as $p) {
            $nl->addListNode(
                $p["id"],
                $p["title"],
                0,
                false,
                false,
                \ilUtil::getImagePath("icon_pg.svg"),
                $lng->txt("blog_posting")
            );
        }

        $form->addItem($radg);

        $form->addCommandButton("printPostings", $lng->txt("blog_show_print_view"));

        $form->setTitle($lng->txt("cont_print_selection"));
        $form->setFormAction(
            $ilCtrl->getFormActionByClass(
                "ilObjBlogGUI",
                "printPostings"
            )
        );

        return $form;
    }
}
