<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Contains info on offline mode, focus, translation, etc.
 *
 * @author @leifos.de
 * @ingroup
 */
class ilLMPresentationStatus
{
    /**
     * @var string
     */
    protected $lang;

    /**
     * @var int?
     */
    protected $focus_id = null;

    /**
     * Constructor
     */
    public function __construct(
        ilObjUser $user,
        ilObjLearningModule $lm,
        ilLMTree $lm_tree,
        string $requested_transl = "",
        string $requested_focus_id = "",
        string $requested_focus_return = "",
        string $requested_search_string = "",
        string $offline,
        bool $export_all_languages,
        string $export_format
    ) {
        $this->lm = $lm;
        $this->ot = ilObjectTranslation::getInstance($lm->getId());
        $this->requested_transl = (string) $requested_transl;
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

    /**
     * Init
     */
    protected function init()
    {
        // determine language
        $this->lang = "-";
        if ($this->ot->getContentActivated()) {
            $langs = $this->ot->getLanguages();
            if (isset($langs[$this->requested_transl]) || $this->requested_transl == $this->ot->getMasterLanguage()) {
                $this->lang = $this->requested_transl;
            } elseif (isset($langs[$this->user->getCurrentLanguage()])) {
                $this->lang = $this->user->getCurrentLanguage();
            }
            if ($this->lang == $this->ot->getMasterLanguage()) {
                $this->lang = "-";
            }
        }

        // determin focus id
        if ($this->requested_focus_id > 0 && $this->lm_tree->isInTree($this->requested_focus_id)) {
            $this->focus_id = $this->requested_focus_id;
        }
    }
    
    /**
     * Get language key
     *
     * @return string
     */
    public function getLang() : string
    {
        return $this->lang;
    }

    /**
     * @return int
     */
    public function getFocusId()
    {
        return $this->focus_id;
    }

    /**
     * @return int
     */
    public function getFocusReturn()
    {
        return $this->requested_focus_return;
    }

    /**
     * @return int
     */
    public function getSearchString()
    {
        return $this->requested_search_string;
    }

    /**
     * @return bool
     */
    public function offline() : bool
    {
        return $this->offline;
    }

    /**
     * @return bool
     */
    public function exportAllLanguages() : bool
    {
        return $this->export_all_languages;
    }

    /**
     * @return bool
     */
    public function getExportFormat() : bool
    {
        return $this->export_format;
    }

    /**
     * Get lm presentationtitle
     *
     * @return string
     */
    public function getLMPresentationTitle()
    {
        if ($this->offline() && $this->lang != "" && $this->lang != "-") {
            $ot = $this->ot;
            $data = $ot->getLanguages();
            $ltitle = $data[$this->lang]["title"];
            if ($ltitle != "") {
                return $ltitle;
            }
        }
        return $this->lm->getTitle();
    }

    /**
     * Is TOC necessary, see #30027
     * Check if at least two entries will be shown
     * @return bool
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
