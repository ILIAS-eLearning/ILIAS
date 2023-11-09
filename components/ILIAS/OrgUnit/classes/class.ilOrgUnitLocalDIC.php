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
 ********************************************************************
 */

declare(strict_types=1);

use Pimple\Container;

class ilOrgUnitLocalDIC
{
    public static ?Container $dic = null;

    public static function dic(): Container
    {
        if (!self::$dic) {
            self::$dic = self::buildDIC();
        }
        return self::$dic;
    }

    protected static function buildDIC(): Container
    {
        global $DIC;
        $dic = new Container();

        $dic['repo.UserAssignments'] = static fn ($dic) => new ilOrgUnitUserAssignmentDBRepository($DIC['ilDB']);
        $dic['repo.Authorities'] = static fn ($dic) => new ilOrgUnitAuthorityDBRepository($DIC['ilDB']);
        $dic['repo.Positions'] = static fn ($dic) => new ilOrgUnitPositionDBRepository(
            $DIC['ilDB'],
            $dic['repo.Authorities'],
            $dic['repo.UserAssignments']
        );
        $dic['repo.OperationContexts'] = static fn ($dic) => new ilOrgUnitOperationContextDBRepository($DIC['ilDB']);
        $dic['repo.Operations'] = static fn ($dic) => new ilOrgUnitOperationDBRepository(
            $DIC['ilDB'],
            $dic["repo.OperationContexts"]
        );
        $dic['repo.Permissions'] = static fn ($dic) => new ilOrgUnitPermissionDBRepository(
            $DIC['ilDB'],
            $dic["repo.Operations"],
            $dic["repo.OperationContexts"]
        );
        $dic['ui.factory'] = static fn (): \ILIAS\UI\Factory => $DIC['ui.factory'];
        $dic['ui.renderer'] = static fn (): \ILIAS\UI\Renderer => $DIC['ui.renderer'];
        $dic['query'] = static fn (): \ILIAS\HTTP\Wrapper\RequestWrapper => $DIC['http']->wrapper()->query();
        $dic['refinery'] = static fn (): \ILIAS\Refinery\Factory => $DIC['refinery'];
        $dic['access'] = static fn (): \ilAccessHandler => $DIC['ilAccess'];
        $dic['lng'] = static fn (): \ilLanguage => $DIC['lng'];
        $dic['dropdownbuilder'] = static fn ($d): \ILIAS\components\OrgUnit\ARHelper\DropdownBuilder =>
            new  \ILIAS\components\OrgUnit\ARHelper\DropdownBuilder(
                $d['ui.factory'],
                $d['ui.renderer'],
                $d['lng']
            );

        $dic['ctrl'] = static fn (): \ilCtrl => $DIC['ilCtrl'];
        $dic['tabs'] = static fn (): \ilTabsGUI => $DIC['ilTabs'];
        return $dic;
    }
}
