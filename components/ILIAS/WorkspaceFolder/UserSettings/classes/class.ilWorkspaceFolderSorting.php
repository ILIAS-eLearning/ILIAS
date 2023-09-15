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

class ilWorkspaceFolderSorting
{
    public const SORT_DERIVED = 0;
    public const SORT_ALPHABETICAL_ASC = 1;
    public const SORT_ALPHABETICAL_DESC = 2;
    public const SORT_CREATION_ASC = 3;
    public const SORT_CREATION_DESC = 4;

    protected ilLanguage $lng;

    public function __construct(ilLanguage $lng = null)
    {
        global $DIC;

        $this->lng = ($lng != null)
            ? $lng
            : $DIC->language();

        $this->lng->loadLanguageModule("wfld");
    }

    public function getOptionsByType(
        string $wsp_type,
        int $selected,
        int $parent_effective
    ): array {
        $sort_options = ($wsp_type == "wfld")
            ? [self::SORT_DERIVED => $this->lng->txt("wfld_derive")]
            : [];
        if (in_array($wsp_type, ["wfld", "wsrt"])) {
            $sort_options[self::SORT_ALPHABETICAL_ASC] = $this->getLabel(self::SORT_ALPHABETICAL_ASC);
            $sort_options[self::SORT_ALPHABETICAL_DESC] = $this->getLabel(self::SORT_ALPHABETICAL_DESC);
            $sort_options[self::SORT_CREATION_ASC] = $this->getLabel(self::SORT_CREATION_ASC);
            $sort_options[self::SORT_CREATION_DESC] = $this->getLabel(self::SORT_CREATION_DESC);
        }

        if (isset($sort_options[self::SORT_DERIVED])) {
            $sort_options[self::SORT_DERIVED] .= " (" . $this->getLabel($parent_effective) . ")";
        }

        if (isset($sort_options[$selected])) {
            $sort_options[$selected] = "<strong>" . $sort_options[$selected] . "</strong>";
        }
        return $sort_options;
    }

    protected function getLabel(int $option): string
    {
        switch ($option) {
            case self::SORT_DERIVED: return $this->lng->txt("wfld_derive");
            case self::SORT_ALPHABETICAL_ASC: return $this->lng->txt("wfld_alphabetically_asc");
            case self::SORT_ALPHABETICAL_DESC: return $this->lng->txt("wfld_alphabetically_desc");
            case self::SORT_CREATION_ASC: return $this->lng->txt("wfld_creation_asc");
            case self::SORT_CREATION_DESC: return $this->lng->txt("wfld_creation_desc");
        }
        return "";
    }

    public function sortNodes(array $nodes, int $sorting): array
    {
        switch ($sorting) {
            case self::SORT_ALPHABETICAL_ASC:
                $nodes = ilArrayUtil::sortArray($nodes, "title", "asc");
                break;
            case self::SORT_ALPHABETICAL_DESC:
                $nodes = ilArrayUtil::sortArray($nodes, "title", "desc");
                break;
            case self::SORT_CREATION_ASC:
                $nodes = ilArrayUtil::sortArray($nodes, "create_date", "asc");
                break;
            case self::SORT_CREATION_DESC:
                $nodes = ilArrayUtil::sortArray($nodes, "create_date", "desc");
                break;
        }
        return $nodes;
    }
}
