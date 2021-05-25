<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Import configuration for media pools
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaPoolImportConfig extends ilImportConfig
{
    protected $transl_into = false;
    protected $transl_into_lm = null;
    protected $transl_lang = "";

    /**
     * Set translation import mode
     *
     * @param ilObjLearningModule $a_lm learning module
     * @param string $a_lang language
     */
    public function setTranslationImportMode($a_lm, $a_lang = "")
    {
        if ($a_lm != null) {
            $this->transl_into = true;
            $this->transl_into_lm = $a_lm;
            $this->transl_lang = $a_lang;
        } else {
            $this->transl_into = false;
        }
    }

    /**
     * Get translation import mode
     *
     * @return bool check if translation import is activated
     */
    public function getTranslationImportMode()
    {
        return $this->transl_into;
    }

    /**
     * Get translation lm
     *
     * @return ilObjLearningModule learning module
     */
    public function getTranslationLM()
    {
        return $this->transl_into_lm;
    }

    /**
     * Get translation language
     *
     * @return string language
     */
    public function getTranslationLang()
    {
        return $this->transl_lang;
    }
}
