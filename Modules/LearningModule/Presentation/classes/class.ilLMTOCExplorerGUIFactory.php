<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author killing@leifos.de
 */
class ilLMTOCExplorerGUIFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
    }

    /**
     * Get explorer
     *
     * @return ilLMTOCExplorerGUI
     */
    public function getExplorer(
        ilLMPresentationService $service,
        string $parent_cmd = "")
    {
        // this needs a proper interface
        $tracker = $service->getTracker();
        $chapter_has_no_active_page =  $service->getNavigationStatus()->isChapterWithoutActivePage();
        $lang = $service->getPresentationStatus()->getLang();
        $focus_id = $service->getPresentationStatus()->getFocusId();
        $export_all_languages = $service->getPresentationStatus()->exportAllLanguages();
        $current_page = $service->getNavigationStatus()->getCurrentPage();
        $deactivated_page = $service->getNavigationStatus()->isDeactivatedPage();
        $requested_obj_id = $service->getRequest()->getRequestedObjId();
        $lm = $service->getLearningModule();
        $lm_tree = $service->getLMTree();
        $offline = $service->getPresentationStatus()->offline();

        $exp = new ilLMTOCExplorerGUI(
            "illmpresentationgui",
            $parent_cmd,
            $service,
            $lang,
            $focus_id,
            $export_all_languages);
        $exp->setMainTemplate($this->tpl);
        $exp->setTracker($tracker);

        // determine highlighted and force open nodes
        $page_id = $current_page;
        if ($deactivated_page)
        {
            $page_id = $requested_obj_id;
        }
        if ($page_id > 0)
        {
            $exp->setPathOpen((int) $page_id);
        }
        // empty chapter
        if ($chapter_has_no_active_page &&
            ilLMObject::_lookupType($requested_obj_id) == "st")
        {
            $exp->setHighlightNode($requested_obj_id);
        }
        else
        {
            if ($lm->getTOCMode() == "pages")
            {
                if ($deactivated_page)
                {
                    $exp->setHighlightNode($requested_obj_id);
                }
                else
                {
                    $exp->setHighlightNode($page_id);
                }
            }
            else
            {
                $exp->setHighlightNode($lm_tree->getParentId($page_id));
            }
        }
        if ($offline)
        {
            $exp->setOfflineMode(true);
        }

        return $exp;
    }

}