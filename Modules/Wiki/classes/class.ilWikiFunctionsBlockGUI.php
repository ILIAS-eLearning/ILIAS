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
 * BlockGUI class for wiki functions block
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilWikiFunctionsBlockGUI extends ilBlockGUI
{
    public static $block_type = "wikiside";
    public static $st_data;
    protected int $ref_id;
    protected ilWikiPage $pageob;
    protected ilObjWiki $wiki;
    
    public function __construct()
    {
        global $DIC;

        $request = $DIC
            ->wiki()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $lng = $DIC->language();
        
        parent::__construct();
        
        $lng->loadLanguageModule("wiki");
        $this->setEnableNumInfo(false);
        
        $this->setTitle($lng->txt("wiki_functions"));
        $this->allow_moving = false;

        $this->ref_id = $request->getRefId();

        $this->wiki = new ilObjWiki($this->ref_id);

        $this->setPresentation(self::PRES_SEC_LEG);
    }

    public function getBlockType() : string
    {
        return self::$block_type;
    }

    protected function isRepositoryObject() : bool
    {
        return false;
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");

        switch ($next_class) {
            default:
                return $this->$cmd();
        }
    }

    public function setPageObject(ilWikiPage $a_pageob) : void
    {
        $this->pageob = $a_pageob;
    }

    public function getPageObject() : ilWikiPage
    {
        return $this->pageob;
    }

    public function fillDataSection() : void
    {
        $this->setDataSection($this->getLegacyContent());
    }

    //
    // New rendering
    //

    protected bool $new_rendering = true;


    protected function getLegacyContent() : string
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;
        
        $tpl = new ilTemplate("tpl.wiki_side_block_content.html", true, true, "Modules/Wiki");
        
        $wp = $this->getPageObject();

        // info
        $actions[] = array(
            "txt" => $lng->txt("info_short"),
            "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "infoScreen")
            );

        // recent changes
        $actions[] = array(
            "txt" => $lng->txt("wiki_recent_changes"),
            "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "recentChanges")
            );

        foreach ($actions as $a) {
            $tpl->setCurrentBlock("action");
            $tpl->setVariable("HREF", $a["href"]);
            $tpl->setVariable("TXT", $a["txt"]);
            $tpl->parseCurrentBlock();

            $tpl->touchBlock("item");
        }


        $actions = array();
        
        // all pages
        $actions[] = array(
            "txt" => $lng->txt("wiki_all_pages"),
            "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "allPages")
            );

        // new pages
        $actions[] = array(
            "txt" => $lng->txt("wiki_new_pages"),
            "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "newPages")
            );

        // popular pages
        $actions[] = array(
            "txt" => $lng->txt("wiki_popular_pages"),
            "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "popularPages")
            );

        // orphaned pages
        $actions[] = array(
            "txt" => $lng->txt("wiki_orphaned_pages"),
            "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "orphanedPages")
            );


        // page lists
        $list = new ilAdvancedSelectionListGUI();
        $list->setListTitle($lng->txt("wiki_page_lists"));
        $list->setStyle(ilAdvancedSelectionListGUI::STYLE_LINK);
        $list->setId("wiki_pglists");

        foreach ($actions as $a) {
            $list->addItem(
                $a["txt"],
                "",
                $a["href"]
            );
        }
        $tpl->setCurrentBlock("plain");
        $tpl->setVariable("PLAIN", $list->getHTML());
        $tpl->parseCurrentBlock();
        $tpl->touchBlock("item");

        
        // page actions
        $list = new ilAdvancedSelectionListGUI();
        $list->setStyle(ilAdvancedSelectionListGUI::STYLE_LINK);
        $list->setListTitle($lng->txt("wiki_page_actions"));
        $list->setId("wiki_pgactions");

        if ($ilAccess->checkAccess("write", "", $this->ref_id)) {
            // rating
            if (ilObjWiki::_lookupRating($this->getPageObject()->getWikiId())) {
                if (!$this->getPageObject()->getRating()) {
                    $list->addItem(
                        $lng->txt("wiki_activate_page_rating"),
                        "",
                        $ilCtrl->getLinkTargetByClass("ilwikipagegui", "activateWikiPageRating")
                    );
                } else {
                    $list->addItem(
                        $lng->txt("wiki_deactivate_page_rating"),
                        "",
                        $ilCtrl->getLinkTargetByClass("ilwikipagegui", "deactivateWikiPageRating")
                    );
                }
            }
        }

        if ($ilAccess->checkAccess("write", "", $this->ref_id) ||
            $ilAccess->checkAccess("edit_page_meta", "", $this->ref_id)) {
            // unhide advmd?
            if (count(ilAdvancedMDRecord::_getSelectedRecordsByObject("wiki", $this->ref_id, "wpg")) &&
                ilWikiPage::lookupAdvancedMetadataHidden($this->getPageObject()->getId())) {
                $list->addItem(
                    $lng->txt("wiki_unhide_meta_adv_records"),
                    "",
                    $ilCtrl->getLinkTargetByClass("ilwikipagegui", "unhideAdvancedMetaData")
                );
            }
        }

        if (($ilAccess->checkAccess("edit_content", "", $this->ref_id) && !$this->getPageObject()->getBlocked())
            || $ilAccess->checkAccess("write", "", $this->ref_id)) {
            // rename
            $list->addItem(
                $lng->txt("wiki_rename_page"),
                "",
                $ilCtrl->getLinkTargetByClass("ilwikipagegui", "renameWikiPage")
            );
        }

        if (ilWikiPerm::check("activate_wiki_protection", $this->ref_id)) {
            // block/unblock
            if ($this->getPageObject()->getBlocked()) {
                $list->addItem(
                    $lng->txt("wiki_unblock_page"),
                    "",
                    $ilCtrl->getLinkTargetByClass("ilwikipagegui", "unblockWikiPage")
                );
            } else {
                $list->addItem(
                    $lng->txt("wiki_block_page"),
                    "",
                    $ilCtrl->getLinkTargetByClass("ilwikipagegui", "blockWikiPage")
                );
            }
        }

        if (ilWikiPerm::check("delete_wiki_pages", $this->ref_id)) {
            // delete page
            $st_page = ilObjWiki::_lookupStartPage($this->getPageObject()->getParentId());
            if ($st_page !== $this->getPageObject()->getTitle()) {
                $list->addItem(
                    $lng->txt("wiki_delete_page"),
                    "",
                    $ilCtrl->getLinkTargetByClass("ilwikipagegui", "deleteWikiPageConfirmationScreen")
                );
            }
        }
        
        if ($ilAccess->checkAccess("write", "", $this->ref_id)) {
            $wpt = new ilWikiPageTemplate($this->getPageObject()->getParentId());
            if (!$wpt->isPageTemplate($this->getPageObject()->getId())) {
                $list->addItem(
                    $lng->txt("wiki_add_template"),
                    "",
                    $ilCtrl->getLinkTargetByClass("ilwikipagetemplategui", "addPageTemplateFromPageAction")
                );
            } else {
                $list->addItem(
                    $lng->txt("wiki_remove_template_status"),
                    "",
                    $ilCtrl->getLinkTargetByClass("ilwikipagetemplategui", "removePageTemplateFromPageAction")
                );
            }
        }

        if ($ilAccess->checkAccess("write", "", $this->ref_id) ||
            $ilAccess->checkAccess("read", "", $this->ref_id)) {
            $tpl->setCurrentBlock("plain");
            $tpl->setVariable("PLAIN", $list->getHTML());
            $tpl->parseCurrentBlock();
            $tpl->touchBlock("item");
        }

        // permissions
        //		if ($ilAccess->checkAccess('edit_permission', "", $this->ref_id))
        //		{
        //			$actions[] = array(
        //				"txt" => $lng->txt("perm_settings"),
        //				"href" => $ilCtrl->getLinkTargetByClass(array("ilobjwikigui", "ilpermissiongui"), "perm")
        //				);
        //		}

        $actions = array();
        
        // settings
        if ($ilAccess->checkAccess('write', "", $this->ref_id)) {
            $actions[] = array(
                "txt" => $lng->txt("wiki_contributors"),
                "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "listContributors")
                );
        }

        // manage
        if (ilWikiPerm::check("wiki_html_export", $this->ref_id)) {
            if (!$this->wiki->isCommentsExportPossible()) {
                $actions[] = array(
                    "txt" => $lng->txt("wiki_html_export"),
                    "id" => "il_wiki_user_export",
                    "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "initUserHTMLExport")
                );
            } else {
                $this->lng->loadLanguageModule("note");
                $comments_helper = new \ILIAS\Notes\Export\ExportHelperGUI();
                $comments_modal = $comments_helper->getCommentIncludeModalDialog(
                    $this->lng->txt("wiki_html_export"),
                    $this->lng->txt("note_html_export_include_comments"),
                    "il.Wiki.Pres.performHTMLExport();",
                    "il.Wiki.Pres.performHTMLExportWithComments();",
                    true
                );
                $actions[] = array(
                    "txt" => $lng->txt("wiki_html_export"),
                    "modal" => $comments_modal
                );
            }
        }

        // manage
        if ($ilAccess->checkAccess('write', "", $this->ref_id)) {
            $actions[] = array(
                "txt" => $lng->txt("settings"),
                "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "editSettings")
                );
        } elseif ($ilAccess->checkAccess('statistics_read', "", $this->ref_id)) {
            $actions[] = array(
                "txt" => $lng->txt("statistics"),
                "href" => $ilCtrl->getLinkTargetByClass(array("ilobjwikigui", "ilwikistatgui"), "initial")
                );
        }

        $modal_html = "";
        foreach ($actions as $a) {
            $tpl->setCurrentBlock("action");
            if (($a["modal"] ?? "") != "") {
                $signal = $a["modal"]->getShowSignal();
                $onclick = "$(document).trigger('" . $signal . "', {'id': '" . $signal . "','triggerer':$(this), 'options': JSON.parse('[]')}); return false;";
                $tpl->setVariable("ONCLICK", ' onclick="' . $onclick . '" ');
                $tpl->setVariable("HREF", "#");
                $modal_html .= $this->ui->renderer()->render($a["modal"]);
            } else {
                $tpl->setVariable("HREF", $a["href"]);
            }
            $tpl->setVariable("TXT", $a["txt"]);
            if (($a["id"] ?? "") != "") {
                $tpl->setVariable("ACT_ID", "id='" . $a["id"] . "'");
            }
            $tpl->parseCurrentBlock();

            $tpl->touchBlock("item");
        }

        return $tpl->get() . $modal_html;
    }
}
