<?php

declare(strict_types=1);

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

use Pimple\Container;

class ilStudyProgrammeDIC
{
    public static ?Container $dic = null;

    public static function dic(): Container
    {
        if (!self::$dic) {
            self::$dic = self::buildDIC();
        }
        return self::$dic;
    }

    public static function specificDicFor(ilObjStudyProgramme $prg): Container
    {
        global $DIC;
        $dic = new Container();

        $dic['permissionhelper'] = static function ($dic) use ($prg, $DIC) {
            return new ilPRGPermissionsHelper(
                $DIC['ilAccess'],
                new ilOrgUnitPositionAccess($DIC['ilAccess']),
                $prg
            );
        };

        return $dic;
    }


    protected static function buildDIC(): Container
    {
        global $DIC;
        $dic = new Container();

        $dic['ilAppEventHandler'] = static fn ($dic) =>
            $DIC->offsetExists('ilAppEventHandler') ? $DIC['ilAppEventHandler'] : new ilAppEventHandler();
        $dic['ilStudyProgrammeEvents'] = static fn ($dic) =>
            new ilStudyProgrammeEvents(
                $dic['ilAppEventHandler'],
                $dic['model.Assignment.ilStudyProgrammeAssignmentRepository']
            );
        $dic['model.Settings.ilStudyProgrammeSettingsRepository'] = static fn ($dic) =>
            new ilStudyProgrammeSettingsDBRepository(
                $DIC['ilDB']
            );
        $dic['model.Progress.ilStudyProgrammeProgressRepository'] = static fn ($dic) =>
            new ilStudyProgrammeProgressDBRepository($DIC['ilDB']);
        $dic['model.Assignment.ilStudyProgrammeAssignmentRepository'] = static fn ($dic) =>
            new ilStudyProgrammeAssignmentDBRepository($DIC['ilDB'], $DIC['tree']);
        $dic['model.AutoMemberships.ilStudyProgrammeAutoMembershipsRepository'] = static fn ($dic) =>
            new ilStudyProgrammeAutoMembershipsDBRepository(
                $DIC['ilDB'],
                (int) $DIC['ilUser']->getId()
            );
        $dic['model.AutoMemberships.ilStudyProgrammeMembershipSourceReaderFactory'] = static fn ($dic) =>
            new ilStudyProgrammeMembershipSourceReaderFactory($DIC);
        $dic['model.Type.ilStudyProgrammeTypeRepository'] = static fn ($dic) =>
            new ilStudyProgrammeTypeDBRepository(
                $DIC['ilDB'],
                $dic['model.Settings.ilStudyProgrammeSettingsRepository'],
                $DIC->filesystem()->web(),
                $DIC['ilUser'],
                $DIC['lng'],
                $DIC['component.factory']
            );
        $dic['model.AutoCategories.ilStudyProgrammeAutoCategoriesRepository'] = static fn ($dic) =>
            new ilStudyProgrammeAutoCategoryDBRepository(
                $DIC['ilDB'],
                (int) $DIC['ilUser']->getId()
            );
        $dic['ilObjStudyProgrammeSettingsGUI'] = static fn ($dic) =>
            new ilObjStudyProgrammeSettingsGUI(
                $DIC['tpl'],
                $DIC['ilCtrl'],
                $DIC['lng'],
                $DIC->ui()->factory()->input(),
                $DIC->ui()->renderer(),
                $DIC->http()->request(),
                $DIC->refinery(),
                $dic['DataFactory'],
                $dic['model.Type.ilStudyProgrammeTypeRepository'],
                $dic['ilStudyProgrammeCommonSettingsGUI'],
                $DIC['ilTabs'],
                $DIC->http()->wrapper()->query()
            );
        $dic['PRGMessages'] = static fn ($dic) =>
            new ilPRGMessagePrinter(
                new ilPRGMessageCollection(),
                $DIC['lng'],
                $DIC['tpl']
            );
        $dic['ilObjStudyProgrammeMembersGUI'] = static fn ($dic) =>
            new ilObjStudyProgrammeMembersGUI(
                $DIC['tpl'],
                $DIC['ilCtrl'],
                $DIC['ilToolbar'],
                $DIC['lng'],
                $DIC['ilUser'],
                $DIC['ilTabs'],
                $dic['ilStudyProgrammeUserProgressDB'],
                $dic['ilStudyProgrammeUserAssignmentDB'],
                $dic['ilStudyProgrammeRepositorySearchGUI'],
                $dic['ilObjStudyProgrammeIndividualPlanGUI'],
                $dic['PRGMessages'],
                $dic['DataFactory'],
                new ilConfirmationGUI(),
                $DIC->http()->wrapper(),
                $DIC->refinery()
            );
        $dic['ilObjStudyProgrammeAutoMembershipsGUI'] = static fn ($dic) =>
            new ilObjStudyProgrammeAutoMembershipsGUI(
                $DIC['tpl'],
                $DIC['ilCtrl'],
                $DIC['ilToolbar'],
                $DIC['lng'],
                $DIC->ui()->factory(),
                $DIC['ui.factory']->messageBox(),
                $DIC['ui.factory']->button(),
                $DIC->ui()->renderer(),
                $DIC->http()->request(),
                $DIC['tree'],
                $DIC->http()->wrapper()->query(),
                $DIC->refinery()
            );
        $dic['ilObjStudyProgrammeTreeGUI'] = static fn ($dic) =>
            new ilObjStudyProgrammeTreeGUI(
                $DIC['tpl'],
                $DIC['ilCtrl'],
                $DIC['ilAccess'],
                $DIC['ilToolbar'],
                $DIC['lng'],
                $DIC['ilLog'],
                $DIC['ilias'],
                $DIC['ilSetting'],
                $DIC['tree'],
                $DIC['rbacadmin'],
                $DIC->http()->wrapper(),
                $DIC->refinery()
            );
        $dic['ilStudyProgrammeTypeGUI'] = static fn ($dic) =>
            new ilStudyProgrammeTypeGUI(
                $DIC['tpl'],
                $DIC['ilCtrl'],
                $DIC['ilAccess'],
                $DIC['ilToolbar'],
                $DIC['lng'],
                $DIC['ilias'],
                $DIC['ilTabs'],
                $DIC['ilUser'],
                $dic['model.Type.ilStudyProgrammeTypeRepository'],
                $DIC->ui()->factory()->input(),
                $DIC->ui()->renderer(),
                $DIC->http()->request(),
                $DIC->refinery(),
                $DIC->filesystem()->web(),
                $DIC->http()->wrapper()->query()
            );
        $dic['ilStudyProgrammeRepositorySearchGUI'] = static fn ($dic) =>
            new ilStudyProgrammeRepositorySearchGUI();
        $dic['ilObjStudyProgrammeIndividualPlanGUI'] = static fn ($dic) =>
            new ilObjStudyProgrammeIndividualPlanGUI(
                $DIC['tpl'],
                $DIC['ilCtrl'],
                $DIC['lng'],
                $DIC['ilUser'],
                $dic['ilStudyProgrammeUserProgressDB'],
                $dic['ilStudyProgrammeUserAssignmentDB'],
                $dic['PRGMessages'],
                $DIC->http()->wrapper(),
                $DIC->refinery()
            );
        $dic['ilObjStudyProgrammeAutoCategoriesGUI'] = static fn ($dic) =>
            new ilObjStudyProgrammeAutoCategoriesGUI(
                $DIC['tpl'],
                $DIC['ilCtrl'],
                $DIC['ilToolbar'],
                $DIC['lng'],
                $DIC->ui()->factory(),
                $DIC['ui.factory']->messageBox(),
                $DIC['ui.factory']->button(),
                $DIC->ui()->renderer(),
                $DIC->http()->request(),
                $DIC['tree'],
                $DIC->http()->wrapper()->query(),
                $DIC->refinery()
            );
        $dic['DataFactory'] = static fn ($dic) =>
            new \ILIAS\Data\Factory();
        $dic['ilStudyProgrammeUserProgressDB'] = static fn ($dic) =>
            $dic['model.Progress.ilStudyProgrammeProgressRepository'];
        $dic['ilStudyProgrammeUserAssignmentDB'] = static fn ($dic) =>
            $dic['model.Assignment.ilStudyProgrammeAssignmentRepository'];
        $dic['ilOrgUnitObjectTypePositionSetting'] = static fn ($dic) =>
            new ilOrgUnitObjectTypePositionSetting('prg');
        $dic['ilStudyProgrammeMailMemberSearchGUI'] = static fn ($dic) =>
            new ilStudyProgrammeMailMemberSearchGUI(
                $DIC['ilCtrl'],
                $DIC['tpl'],
                $DIC['lng'],
                $DIC['ilAccess'],
                $DIC->http()->wrapper(),
                $DIC->refinery()
            );
        $dic['ilStudyProgrammeChangeExpireDateGUI'] = static fn ($dic) =>
            new ilStudyProgrammeChangeExpireDateGUI(
                $DIC['ilCtrl'],
                $DIC['tpl'],
                $DIC['lng'],
                $DIC['ilAccess'],
                $DIC['ilUser'],
                $DIC->ui()->factory()->input(),
                $DIC->ui()->renderer(),
                $DIC->http()->request(),
                $DIC->refinery(),
                $dic['DataFactory'],
                $dic['PRGMessages']
            );
        $dic['ilStudyProgrammeChangeDeadlineGUI'] = static fn ($dic) =>
            new ilStudyProgrammeChangeDeadlineGUI(
                $DIC['ilCtrl'],
                $DIC['tpl'],
                $DIC['lng'],
                $DIC['ilAccess'],
                $DIC['ilUser'],
                $DIC->ui()->factory()->input(),
                $DIC->ui()->renderer(),
                $DIC->http()->request(),
                $DIC->refinery(),
                $dic['DataFactory'],
                $dic['PRGMessages']
            );
        $dic['ilStudyProgrammeDashboardViewGUI'] = static fn ($dic) =>
            new ilStudyProgrammeDashboardViewGUI(
                $DIC['lng'],
                $DIC['ilUser'],
                $DIC['ilAccess'],
                $DIC['ilSetting'],
                $DIC['ui.factory'],
                $DIC['ui.renderer'],
                $DIC['ilCtrl'],
                $dic['Log']
            );
        $dic['ilStudyProgrammeCommonSettingsGUI'] = static fn ($dic) =>
            new ilStudyProgrammeCommonSettingsGUI(
                $DIC['ilCtrl'],
                $DIC['tpl'],
                $DIC['lng'],
                $DIC->object()
            );
        $dic['Log'] = static fn ($dic) =>
            ilLoggerFactory::getLogger('prg');
        $dic['current_user'] = static fn ($dic) =>
            $DIC['ilUser'];

        return $dic;
    }
}
