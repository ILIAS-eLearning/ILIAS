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
 * Declaration of authorship (data gateway)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPortfolioDeclarationOfAuthorship
{
    protected ilSetting $prtf_settings;
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->prtf_settings = new ilSetting("prtf");
    }

    /**
     * Get for language
     */
    public function getForLanguage(
        string $l
    ): string {
        return $this->prtf_settings->get("decl_author_" . $l);
    }

    /**
     * Set for language
     */
    public function setForLanguage(
        string $l,
        string $value
    ): void {
        $this->prtf_settings->set("decl_author_" . $l, $value);
    }

    /**
     * Get for user
     */
    public function getForUser(
        ilObjUser $user
    ): string {
        $lng = $this->lng;

        $decl = $this->getForLanguage($user->getLanguage());
        if ($decl === "") {
            $decl = $this->getForLanguage($lng->getDefaultLanguage());
        }
        return $decl;
    }
}
