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
 * Contains info on offline mode, focus, translation, etc.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMPresentationStatus
{
    protected string $export_format;
    protected bool $export_all_languages;
    public bool $offline;
    protected ilObjUser $user;
    protected string $requested_search_string;
    protected ilLMTree $lm_tree;
    protected string $requested_focus_return;
    protected string $requested_focus_id;
    protected string $requested_transl;
    protected ilObjectTranslation $ot;
    protected ilObjLearningModule $lm;
    protected string $lang;
    protected int $focus_id = 0;
    protected $concrete_lang = "";

    public function __construct(
        ilObjUser $user,
        ilObjLearningModule $lm,
        ilLMTree $lm_tree,
        string $requested_transl = "",
        string $requested_focus_id = "",
        string $requested_focus_return = "",
        string $requested_search_string = "",
        bool $offline = false,
        bool $export_all_languages = false,
        string $export_format = ""
    ) {
        $this->lm = $lm;
        $this->ot = ilObjectTranslation::getInstance($lm->getId());
        $this->requested_transl = $requested_transl;
        $this->requested_focus_id = $requested_focus_id;
        $this->requested_focus_return = $requested_focus_return;
        $this->requested_search_string = $requested_search_string;
        $this->user = $user;
        $this->lm_tree = $lm_tree;
        $this->offline = $offline;
        $this->export_all_languages = $export_all_languages;
        $this->export_format = $export_format;
        $this->init();
    }

    protected function init() : void
    {
        // determine language
        $this->lang = "-";
        $this->concrete_lang = "-";
        if ($this->ot->getContentActivated()) {
            $langs = $this->ot->getLanguages();
            if (isset($langs[$this->requested_transl]) || $this->requested_transl == $this->ot->getMasterLanguage()) {
                $this->lang = $this->requested_transl;
            } else {
                $this->lang = $this->user->getCurrentLanguage();
            }
            $this->concrete_lang = $this->lang;
            if ($this->lang == $this->ot->getMasterLanguage()) {
                $this->lang = "-";
            }
        }

        // determin focus id
        if ($this->requested_focus_id > 0 && $this->lm_tree->isInTree($this->requested_focus_id)) {
            $this->focus_id = $this->requested_focus_id;
        }
    }
    
    public function getLang() : string
    {
        return $this->lang;
    }

    /**
     * Only difference to getLang():
     * if current language is the master lang the language key will be returned, not "-"
     */
    public function getConcreteLang() : string
    {
        return $this->concrete_lang;
    }

    public function getFocusId() : int
    {
        return $this->focus_id;
    }

    public function getFocusReturn() : string
    {
        return $this->requested_focus_return;
    }

    public function getSearchString() : string
    {
        return $this->requested_search_string;
    }

    public function offline() : bool
    {
        return $this->offline;
    }

    public function exportAllLanguages() : bool
    {
        return $this->export_all_languages;
    }

    public function getExportFormat() : string
    {
        return $this->export_format;
    }

    public function getLMPresentationTitle() : string
    {
        if ($this->offline() && $this->lang != "" && $this->lang != "-") {
            $ot = $this->ot;
            $data = $ot->getLanguages();
            $ltitle = $data[$this->lang]["title"];
            if ($ltitle != "") {
                return $ltitle;
            }
            $ltitle = $data[$ot->getFallbackLanguage()]["title"];
            if ($ltitle != "") {
                return $ltitle;
            }
        }
        return $this->lm->getTitle();
    }

    /**
     * Is TOC necessary, see #30027
     * Check if at least two entries will be shown
     */
    public function isTocNecessary() : bool
    {
        $childs = $this->lm_tree->getChilds($this->lm_tree->getRootId());
        if (count($childs) == 0) {      // no chapter -> false
            return false;
        }
        if (count($childs) > 1) {       // more than one chapter -> true
            return true;
        }
        if ($this->lm->getTOCMode() != "pages") {   // one chapter TOC does not show pages -> false
            return false;
        }
        $current_chapter = current($childs);
        $childs = $this->lm_tree->getChilds($current_chapter["child"]);
        if (count($childs) > 1) {
            return true;            // more than one page -> true
        }
        return false;               // zero or one page -> false
    }
}
