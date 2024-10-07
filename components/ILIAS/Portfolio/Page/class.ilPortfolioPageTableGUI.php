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
 * Portfolio page table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioPageTableGUI extends ilTable2GUI
{
    protected \ILIAS\Portfolio\InternalGUIService $gui;
    protected ilObjUser $user;
    protected ilObjPortfolioBase $portfolio;
    protected bool $is_template;
    protected string $page_gui;

    public function __construct(
        ilObjPortfolioBaseGUI $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->gui = $DIC->portfolio()->internal()->gui();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->disable("numinfo");
        $this->setLimit(9999);

        /** @var ilObjPortfolio $portfolio */
        $portfolio = $a_parent_obj->getObject();
        $this->portfolio = $portfolio;
        $this->page_gui = $this->parent_obj->getPageGUIClassName();
        $this->is_template = ($this->portfolio->getType() === "prtt");

        $this->setTitle($lng->txt("content"));

        //$this->addColumn($this->lng->txt(""), "", "1");
        $this->addColumn($this->lng->txt("user_order"));
        $this->addColumn($this->lng->txt("title"));
        $this->addColumn($this->lng->txt("type"));
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.portfolio_page_row.html", "components/ILIAS/Portfolio");

        //$this->addMultiCommand("confirmPortfolioPageDeletion", $lng->txt("delete"));
        //$this->addMultiCommand("copyPageForm", $lng->txt("prtf_copy_page"));

        $this->addCommandButton(
            "savePortfolioPagesOrdering",
            $lng->txt("user_save_ordering_and_titles")
        );

        $this->getItems();
    }

    public function getItems(): void
    {
        $ilUser = $this->user;

        $data = ilPortfolioPage::getAllPortfolioPages($this->portfolio->getId());
        $this->setData($data);

    }

    protected function fillRow(array $a_set): void
    {
        $f = $this->gui->ui()->factory();
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $action_items = [];
        switch ($a_set["type"]) {
            case ilPortfolioPage::TYPE_PAGE:
                $this->tpl->setCurrentBlock("title_field");
                $this->tpl->setVariable("ID", $a_set["id"]);
                $this->tpl->setVariable("VAL_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($a_set["title"]));
                $this->tpl->parseCurrentBlock();

                $ilCtrl->setParameterByClass(
                    $this->page_gui,
                    "ppage",
                    $a_set["id"]
                );
                //$action_item = ilLinkButton::getInstance();
                //$action_item->setCaption('edit_page');
                //$action_item->setUrl($ilCtrl->getLinkTargetByClass($this->page_gui, "edit"));
                $action_items[] = $f->button()->shy(
                    $lng->txt('edit_page'),
                    $ilCtrl->getLinkTargetByClass($this->page_gui, "edit")
                );


                $this->tpl->setVariable("TYPE", $lng->txt("page"));
                break;

        }

        $ilCtrl->setParameter($this->parent_obj, "prtf_page", $a_set["id"]);

        // copy
        //$action_item = ilLinkButton::getInstance();
        if ((int) $a_set["type"] === ilPortfolioPage::TYPE_PAGE) {
            $txt = $lng->txt('prtf_copy_pg');
        }
        $action_items[] = $f->button()->shy(
            $txt,
            $ilCtrl->getLinkTarget($this->parent_obj, "copyPageForm")
        );

        $action_items[] = $f->button()->shy(
            $lng->txt("delete"),
            $ilCtrl->getLinkTarget($this->parent_obj, "confirmPortfolioPageDeletion")
        );


        $ilCtrl->setParameter($this->parent_obj, "prtf_page", "");

        $ks = [];
        if (count($action_items) > 0) {
            $first = array_shift($action_items);
            $ks[] = $f->button()->standard(
                $first->getLabel(),
                $first->getAction()
            );
            /*$split_button = ilSplitButtonGUI::getInstance();
            $i = 0;
            foreach ($action_items as $item) {
                if ($i++ === 0) {
                    $split_button->setDefaultButton($item);
                } else {
                    $split_button->addMenuItem(new ilButtonToSplitButtonMenuItemAdapter($item));
                }
            }*/
            if (count($action_items) > 0) {
                $ks[] = $f->dropdown()->standard($action_items);
            }
            $this->tpl->setVariable(
                "SPLIT_BUTTON",
                $this->gui->ui()->renderer()->render([$ks])
            );
        }


        $this->tpl->setVariable("ID", $a_set["id"]);
        $this->tpl->setVariable("VAL_ORDER_NR", $a_set["order_nr"]);
    }
}
