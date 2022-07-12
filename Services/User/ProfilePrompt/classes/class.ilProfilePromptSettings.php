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
 * Profile prompt settings
 * @author Alexander Killing <killing@leifos.de>
 */
class ilProfilePromptSettings
{
    public const MODE_INCOMPLETE_ONLY = 0;
    public const MODE_ONCE_AFTER_LOGIN = 1;
    public const MODE_REPEAT = 2;
    protected array $prompt_texts; // Missing array type.
    protected array $info_texts; // Missing array type.
    protected int $days;
    protected int $mode;

    public function __construct(int $mode, int $days, array $info_texts, array $promp_texts) // Missing array type.
    {
        $this->mode = $mode;
        $this->days = $days;
        $this->info_texts = $info_texts;
        $this->prompt_texts = $promp_texts;
    }

    public function getDays() : int
    {
        return $this->days;
    }

    public function getMode() : int
    {
        return $this->mode;
    }

    public function getInfoTexts() : array // Missing array type.
    {
        return $this->info_texts;
    }

    public function getPromptTexts() : array // Missing array type.
    {
        return $this->prompt_texts;
    }

    public function getInfoText(string $lang) : string
    {
        return $this->info_texts[$lang] ?? "";
    }

    public function getPromptText(string $lang) : string
    {
        return $this->prompt_texts[$lang] ?? "";
    }
}
