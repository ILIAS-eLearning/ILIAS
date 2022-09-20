<?php

declare(strict_types=0);
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
 * Class ilCourseMeritBadge
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @package ModulesCourse
 */
class ilCourseMeritBadge implements ilBadgeType
{
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    public function getId(): string
    {
        return "merit";
    }

    public function getCaption(): string
    {
        return $this->lng->txt("badge_crs_merit");
    }

    public function isSingleton(): bool
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function getValidObjectTypes(): array
    {
        return ["crs", "grp"];
    }

    public function getConfigGUIInstance(): ?ilBadgeTypeGUI
    {
        return null;
    }
}
