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

        $dic['repo.UserAssignments'] = fn ($dic) => new ilOrgUnitUserAssignmentDBRepository($DIC['ilDB']);
        $dic['repo.Authorities'] = fn ($dic) => new ilOrgUnitAuthorityDBRepository($DIC['ilDB']);
        $dic['repo.Positions'] = fn ($dic) => new ilOrgUnitPositionDBRepository(
            $DIC['ilDB'],
            $dic['repo.Authorities'],
            $dic['repo.UserAssignments']
        );
        $dic['repo.OperationContexts'] = fn ($dic) => new ilOrgUnitOperationContextDBRepository($DIC['ilDB']);
        $dic['repo.Operations'] = fn ($dic) => new ilOrgUnitOperationDBRepository(
            $DIC['ilDB'],
            $dic["repo.OperationContexts"]
        );
        $dic['repo.Permissions'] = fn ($dic) => new ilOrgUnitPermissionDBRepository(
            $DIC['ilDB'],
            $dic["repo.Operations"],
            $dic["repo.OperationContexts"]
        );
        $dic['ui.factory'] = fn (): \ILIAS\UI\Factory => $DIC['ui.factory'];
        $dic['ui.renderer'] = fn (): \ILIAS\UI\Renderer => $DIC['ui.renderer'];
        $dic['query'] = fn (): \ILIAS\HTTP\Wrapper\RequestWrapper => $DIC['http']->wrapper()->query();
        $dic['refinery'] = fn (): \ILIAS\Refinery\Factory => $DIC['refinery'];
        $dic['access'] = fn (): \ilAccessHandler => $DIC['ilAccess'];
        $dic['lng'] = fn (): \ilLanguage => $DIC['lng'];
        $dic['rowactions'] = fn ($d): \ILIAS\Modules\OrgUnit\ARHelper\RowActions =>
            new  \ILIAS\Modules\OrgUnit\ARHelper\RowActions(
                $d['ui.factory'],
                $d['ui.renderer'],
                $d['lng']
            );

        $dic['ctrl'] = fn (): \ilCtrl => $DIC['ilCtrl'];
        $dic['tabs'] = fn (): \ilTabsGUI => $DIC['ilTabs'];
        return $dic;
    }
}
