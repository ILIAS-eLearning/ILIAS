<?php declare(strict_types=1);

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

    public static function dic() : Container
    {
        if (!self::$dic) {
            self::$dic = self::buildDIC();
        }
        return self::$dic;
    }

    public static function specificDicFor(ilObjStudyProgramme $prg) : Container
    {
        global $DIC;
        $dic = new Container();

        $dic['permissionhelper'] = static function ($dic) use ($prg, $DIC) {
            return new ilPRGPermissionsHelper(
                $DIC['ilAccess'],
                new ilOrgUnitPositionAccess(),
                $prg
            );
        };

        return $dic;
    }


    protected static function buildDIC() : Container
    {
        global $DIC;
        $dic = new Container();

        $dic['ilAppEventHandler'] = static function ($dic) use ($DIC) {
            return $DIC->offsetExists('ilAppEventHandler') ?
                $DIC['ilAppEventHandler'] : new ilAppEventHandler();
        };
        $dic['ilStudyProgrammeEvents'] = static function ($dic) {
            return new ilStudyProgrammeEvents(
                $dic['ilAppEventHandler'],
                $dic['model.Assignment.ilStudyProgrammeAssignmentRepository']
            );
        };
        $dic['model.Settings.ilStudyProgrammeSettingsRepository'] = static function ($dic) use ($DIC) {
            return new ilStudyProgrammeSettingsDBRepository(
                $DIC['ilDB']
            );
        };
        $dic['model.Progress.ilStudyProgrammeProgressRepository'] = static function ($dic) use ($DIC) {
            return new ilStudyProgrammeProgressDBRepository($DIC['ilDB']);
        };
        $dic['model.Assignment.ilStudyProgrammeAssignmentRepository'] = static function ($dic) use ($DIC) {
            return new ilStudyProgrammeAssignmentDBRepository($DIC['ilDB'], $DIC['tree']);
        };
        $dic['model.AutoMemberships.ilStudyProgrammeAutoMembershipsRepository'] = static function ($dic) use ($DIC) {
            return new ilStudyProgrammeAutoMembershipsDBRepository(
                $DIC['ilDB'],
                (int) $DIC['ilUser']->getId()
            );
        };
        $dic['model.AutoMemberships.ilStudyProgrammeMembershipSourceReaderFactory'] = static function ($dic) use ($DIC) {
            return new ilStudyProgrammeMembershipSourceReaderFactory($DIC);
        };

        $dic['model.Type.ilStudyProgrammeTypeRepository'] = static function ($dic) use ($DIC) {
            return new ilStudyProgrammeTypeDBRepository(
                $DIC['ilDB'],
                $dic['model.Settings.ilStudyProgrammeSettingsRepository'],
                $DIC->filesystem()->web(),
                $DIC['ilUser'],
                $DIC['lng'],
                $DIC['component.factory']
            );
        };
        $dic['model.AutoCategories.ilStudyProgrammeAutoCategoriesRepository'] = static function ($dic) use ($DIC) {
            return new ilStudyProgrammeAutoCategoryDBRepository(
                $DIC['ilDB'],
                (int) $DIC['ilUser']->getId()
            );
        };
        $dic['ilObjStudyProgrammeSettingsGUI'] = static function ($dic) use ($DIC) {
            return new ilObjStudyProgrammeSettingsGUI(
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
        };

        $dic['PRGMessages'] = static function ($dic) use ($DIC) {
            $messages = new ilPRGMessageCollection();
            return new ilPRGMessagePrinter(
                $messages,
                $DIC['lng'],
                $DIC['tpl']
            );
        };

        $dic['ilObjStudyProgrammeMembersGUI'] = static function ($dic) use ($DIC) {
            return new ilObjStudyProgrammeMembersGUI(
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
        };
        $dic['ilObjStudyProgrammeAutoMembershipsGUI'] = static function ($dic) use ($DIC) {
            return new ilObjStudyProgrammeAutoMembershipsGUI(
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
        };
        $dic['ilObjStudyProgrammeTreeGUI'] = static function ($dic) use ($DIC) {
            return new ilObjStudyProgrammeTreeGUI(
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
        };
        $dic['ilStudyProgrammeTypeGUI'] = static function ($dic) use ($DIC) {
            return new ilStudyProgrammeTypeGUI(
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
        };
        $dic['ilStudyProgrammeRepositorySearchGUI'] = static function ($dic) {
            return new ilStudyProgrammeRepositorySearchGUI();
        };
        $dic['ilObjStudyProgrammeIndividualPlanGUI'] = static function ($dic) use ($DIC) {
            return new ilObjStudyProgrammeIndividualPlanGUI(
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
        };
        $dic['ilObjStudyProgrammeAutoCategoriesGUI'] = static function ($dic) use ($DIC) {
            return new ilObjStudyProgrammeAutoCategoriesGUI(
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
        };
        $dic['DataFactory'] = static function ($dic) {
            return new \ILIAS\Data\Factory();
        };
        $dic['ilStudyProgrammeUserProgressDB'] = static function ($dic) {
            return $dic['model.Progress.ilStudyProgrammeProgressRepository'];
        };

        $dic['ilStudyProgrammeUserAssignmentDB'] = static function ($dic) {
            return $dic['model.Assignment.ilStudyProgrammeAssignmentRepository'];
        };

        $dic['ilOrgUnitObjectTypePositionSetting'] = static function ($dic) {
            return new ilOrgUnitObjectTypePositionSetting('prg');
        };

        $dic['ilStudyProgrammeMailMemberSearchGUI'] = static function ($dic) use ($DIC) {
            return new ilStudyProgrammeMailMemberSearchGUI(
                $DIC['ilCtrl'],
                $DIC['tpl'],
                $DIC['lng'],
                $DIC['ilAccess'],
                $DIC->http()->wrapper(),
                $DIC->refinery()
            );
        };
        $dic['ilStudyProgrammeChangeExpireDateGUI'] = static function ($dic) use ($DIC) {
            return new ilStudyProgrammeChangeExpireDateGUI(
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
        };
        $dic['ilStudyProgrammeChangeDeadlineGUI'] = static function ($dic) use ($DIC) {
            return new ilStudyProgrammeChangeDeadlineGUI(
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
        };
        $dic['ilStudyProgrammeDashboardViewGUI'] = static function ($dic) use ($DIC) {
            return new ilStudyProgrammeDashboardViewGUI(
                $DIC['lng'],
                $DIC['ilUser'],
                $DIC['ilAccess'],
                $DIC['ilSetting'],
                $DIC['ui.factory'],
                $DIC['ui.renderer'],
                $DIC['ilCtrl'],
                $dic['Log']
            );
        };

        $dic['ilStudyProgrammeCommonSettingsGUI'] = static function ($dic) use ($DIC) {
            return new ilStudyProgrammeCommonSettingsGUI(
                $DIC['ilCtrl'],
                $DIC['tpl'],
                $DIC['lng'],
                $DIC->object()
            );
        };

        $dic['Log'] = static function ($dic) {
            return ilLoggerFactory::getLogger('prg');
        };

        $dic['current_user'] = static function ($dic) use ($DIC) {
            return $DIC['ilUser'];
        };
        
        return $dic;
    }
}
