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

use ILIAS\Wiki\Editing\EditingGUIRequest;

/**
 * Wiki page template gui class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiPageTemplateGUI
{
    protected EditingGUIRequest $request;
    protected ilObjWiki $wiki;
    protected ilToolbarGUI$toolbar;
    protected ilLanguage $lng;
    protected ilObjWikiGUI $wiki_gui;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct(
        ilObjWikiGUI $a_wiki_gui
    ) {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $tpl = $DIC["tpl"];
        $ilToolbar = $DIC->toolbar();
        $lng = $DIC->language();

        $this->wiki_gui = $a_wiki_gui;
        /** @var ilObjWiki $wiki */
        $wiki = $this->wiki_gui->getObject();
        $this->wiki = $wiki;
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->toolbar = $ilToolbar;

        $this->request = $DIC
            ->wiki()
            ->internal()
            ->gui()
            ->editing()
            ->request();
    }

    public function executeCommand(): void
    {
        $nc = $this->ctrl->getNextClass();

        switch ($nc) {
            default:
                $cmd = $this->ctrl->getCmd("listTemplates");
                if (in_array($cmd, array("listTemplates", "add", "remove", "saveTemplateSettings", "addPageTemplateFromPageAction", "removePageTemplateFromPageAction"))) {
                    $this->$cmd();
                }
                break;
        }
    }

    public function listTemplates(): void
    {
        // list pages
        $pages = ilWikiPage::getAllWikiPages($this->wiki->getId());
        $options = array("" => $this->lng->txt("please_select"));
        foreach ($pages as $p) {
            //if (!in_array($p["id"], $ipages_ids))
            //{
            $options[$p["id"]] = ilStr::shortenTextExtended($p["title"], 60, true);
            //}
        }

        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        $this->toolbar->setOpenFormTag(true);
        $this->toolbar->setCloseFormTag(false);

        if (count($options) > 0) {
            $si = new ilSelectInputGUI($this->lng->txt("wiki_pages"), "templ_page_id");
            $si->setOptions($options);
            $this->toolbar->addInputItem($si);
            $this->toolbar->addFormButton($this->lng->txt("wiki_add_template"), "add");
            $this->toolbar->addSeparator();
        }

        // empty page as template?
        $cb = new ilCheckboxInputGUI($this->lng->txt("wiki_empty_page_template"), "empty_page_templ");
        $cb->setChecked($this->wiki->getEmptyPageTemplate());
        $this->toolbar->addInputItem($cb, true);
        $this->toolbar->addFormButton($this->lng->txt("save"), "saveTemplateSettings");

        $tab = new ilWikiPageTemplatesTableGUI($this, "listTemplates", $this->wiki->getId());
        $tab->setOpenFormTag(false);
        $tab->setCloseFormTag(true);
        $this->tpl->setContent($tab->getHTML());
    }

    public function add(): void
    {
        $wpt = new ilWikiPageTemplate($this->wiki->getId());
        $wpt->save($this->request->getPageTemplateId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("wiki_template_added"), true);
        $this->ctrl->redirect($this, "listTemplates");
    }

    public function remove(): void
    {
        $wpt = new ilWikiPageTemplate($this->wiki->getId());

        $ids = $this->request->getIds();
        if (count($ids) > 0) {
            foreach ($ids as $id) {
                $wpt->remove((int) $id);
            }
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("wiki_template_status_removed"), true);
        }

        $this->ctrl->redirect($this, "listTemplates");
    }

    public function saveTemplateSettings(): void
    {
        $all_ids = $this->request->getAllIds();
        $new_pages = $this->request->getNewPages();
        $add_to_page = $this->request->getAddToPage();
        foreach ($all_ids as $id) {
            $wpt = new ilWikiPageTemplate($this->wiki->getId());
            $wpt->save($id, $new_pages[$id] ?? 0, $add_to_page[$id] ?? 0);
        }

        $this->wiki->setEmptyPageTemplate($this->request->getEmptyPageTemplate());
        $this->wiki->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "listTemplates");
    }


    //
    // PAGE ACTIONS
    //

    public function removePageTemplateFromPageAction(): void
    {
        $page_id = $this->request->getWikiPageId();
        if ($page_id) {
            $wpt = new ilWikiPageTemplate($this->wiki->getId());
            $wpt->remove($page_id);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("wiki_template_status_removed"), true);
        }

        $this->ctrl->redirect($this, "listTemplates");
    }

    public function addPageTemplateFromPageAction(): void
    {
        $page_id = $this->request->getWikiPageId();
        if ($page_id) {
            $wpt = new ilWikiPageTemplate($this->wiki->getId());
            $wpt->save($page_id);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("wiki_template_added"), true);
        }

        $this->ctrl->redirect($this, "listTemplates");
    }
}
