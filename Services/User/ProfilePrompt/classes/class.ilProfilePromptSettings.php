<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Profile prompt settings
 *
 * @author killing@leifos.de
 * @ingroup ServicesUser
 */
class ilProfilePromptSettings
{
    const MODE_INCOMPLETE_ONLY = 0;
    const MODE_ONCE_AFTER_LOGIN = 1;
    const MODE_REPEAT = 2;

    /**
     * Constructor
     * @param int $mode
     * @param int $days
     */
    public function __construct(int $mode, int $days, array $info_texts, array $promp_texts)
    {
        $this->mode = $mode;
        $this->days = $days;
        $this->info_texts = $info_texts;
        $this->prompt_texts = $promp_texts;
    }

    /**
     * @return int
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return array
     */
    public function getInfoTexts()
    {
        return $this->info_texts;
    }

    /**
     * @return array
     */
    public function getPromptTexts()
    {
        return $this->prompt_texts;
    }

    /**
     * @return string
     */
    public function getInfoText($lang)
    {
        if (isset($this->info_texts[$lang])) {
            return $this->info_texts[$lang];
        }
        return "";
    }

    /**
     * @return string
     */
    public function getPromptText($lang)
    {
        if (isset($this->prompt_texts[$lang])) {
            return $this->prompt_texts[$lang];
        }
        return "";
    }
}
