<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilImportConfig.php");
/**
 * Import configuration for learning modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilLearningModuleImportConfig extends ilImportConfig
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
