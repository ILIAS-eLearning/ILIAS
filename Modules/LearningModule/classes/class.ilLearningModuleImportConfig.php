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
 * Import configuration for learning modules
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningModuleImportConfig extends ilImportConfig
{
    protected bool $transl_into = false;
    protected ?ilObjLearningModule $transl_into_lm = null;
    protected string $transl_lang = "";

    public function setTranslationImportMode(
        ilObjLearningModule $a_lm,
        string $a_lang = ""
    ) : void {
        if ($a_lm != null) {
            $this->transl_into = true;
            $this->transl_into_lm = $a_lm;
            $this->transl_lang = $a_lang;
        } else {
            $this->transl_into = false;
        }
    }

    public function getTranslationImportMode() : bool
    {
        return $this->transl_into;
    }

    public function getTranslationLM() : ilObjLearningModule
    {
        return $this->transl_into_lm;
    }

    public function getTranslationLang() : string
    {
        return $this->transl_lang;
    }
}
