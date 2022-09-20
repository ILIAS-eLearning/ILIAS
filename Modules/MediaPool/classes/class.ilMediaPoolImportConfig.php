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
 * Import configuration for media pools
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaPoolImportConfig extends ilImportConfig
{
    protected bool $transl_into = false;
    protected ?ilObjMediaPool $transl_into_mep = null;
    protected string $transl_lang = "";

    public function setTranslationImportMode(
        ?ilObjMediaPool $a_mep,
        string $a_lang = ""
    ): void {
        if ($a_mep !== null) {
            $this->transl_into = true;
            $this->transl_into_mep = $a_mep;
            $this->transl_lang = $a_lang;
        } else {
            $this->transl_into = false;
        }
    }

    public function getTranslationImportMode(): bool
    {
        return $this->transl_into;
    }

    public function getTranslationMep(): ?ilObjMediaPool
    {
        return $this->transl_into_mep;
    }

    public function getTranslationLang(): string
    {
        return $this->transl_lang;
    }
}
