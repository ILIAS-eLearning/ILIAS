<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Declaration of authorship (data gateway)
 *
 * @author killing@leifos.de
 * @ingroup ModulesPortfolio
 */
class ilPortfolioDeclarationOfAuthorship
{
    /**
     * @var ilSetting
     */
    protected $prtf_settings;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->prtf_settings = new ilSetting("prtf");
    }

    /**
     * Get for language
     *
     * @param string $l
     * @return string
     */
    public function getForLanguage(string $l) : string
    {
        return $this->prtf_settings->get("decl_author_" . $l);
    }

    /**
     * Set for language
     *
     * @param string $l
     * @param string $value
     * @return string
     */
    public function setForLanguage(string $l, string $value) : string
    {
        return $this->prtf_settings->set("decl_author_" . $l, $value);
    }

    /**
     * Get for user
     *
     * @param ilObjUser $user
     * @return string
     */
    public function getForUser(ilObjUser $user) : string
    {
        $lng = $this->lng;

        $decl = $this->getForLanguage($user->getLanguage());
        if ($decl == "") {
            $decl = $this->getForLanguage($lng->getDefaultLanguage());
        }
        return $decl;
    }
}
