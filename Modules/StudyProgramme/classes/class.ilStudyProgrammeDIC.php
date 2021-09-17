<?php declare(strict_types=1);

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

        $dic['permissionhelper'] = function ($dic) use ($prg, $DIC) {
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

        $dic['ilAppEventHandler'] = function ($dic) use ($DIC) {
            return $DIC->offsetExists('ilAppEventHandler') ?
                $DIC['ilAppEventHandler'] : new \ilAppEventHandler();
        };
        $dic['ilStudyProgrammeEvents'] = function ($dic) {
            return new ilStudyProgrammeEvents(
                $dic['ilAppEventHandler'],
                $dic['model.Assignment.ilStudyProgrammeAssignmentRepository']
            );
        };
        $dic['model.Settings.ilStudyProgrammeSettingsRepository'] = function ($dic) use ($DIC) {
            return new ilStudyProgrammeSettingsDBRepository(
                $DIC['ilDB']
            );
        };
        $dic['model.Progress.ilStudyProgrammeProgressRepository'] = function ($dic) use ($DIC) {
            return new ilStudyProgrammeProgressDBRepository($DIC['ilDB']);
        };
        $dic['model.Assignment.ilStudyProgrammeAssignmentRepository'] = function ($dic) use ($DIC) {
            return new ilStudyProgrammeAssignmentDBRepository($DIC['ilDB'], $DIC['tree']);
        };
        $dic['model.AutoMemberships.ilStudyProgrammeAutoMembershipsRepository'] = function ($dic) use ($DIC) {
            return new ilStudyProgrammeAutoMembershipsDBRepository(
                $DIC['ilDB'],
                (int) $DIC['ilUser']->getId()
            );
        };
        $dic['model.AutoMemberships.ilStudyProgrammeMembershipSourceReaderFactory'] = function ($dic) use ($DIC) {
            return new ilStudyProgrammeMembershipSourceReaderFactory($DIC);
        };

        $dic['model.Type.ilStudyProgrammeTypeRepository'] = function ($dic) use ($DIC) {
            return new ilStudyProgrammeTypeDBRepository(
                $DIC['ilDB'],
                $dic['model.Settings.ilStudyProgrammeSettingsRepository'],
                $DIC->filesystem()->web(),
                $DIC['ilUser'],
                $DIC['ilPluginAdmin'],
                $DIC['lng']
            );
        };
        $dic['model.AutoCategories.ilStudyProgrammeAutoCategoriesRepository'] = function ($dic) use ($DIC) {
            return new ilStudyProgrammeAutoCategoryDBRepository(
                $DIC['ilDB'],
                (int) $DIC['ilUser']->getId()
            );
        };
        $dic['ilObjStudyProgrammeSettingsGUI'] = function ($dic) use ($DIC) {
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
                $DIC['ilTabs']
            );
        };

        $dic['PRGMessages'] = function ($dic) use ($DIC) {
            $messages = new ilPRGMessageCollection();
            return new ilPRGMessagePrinter(
                $messages,
                $DIC['lng'],
                $DIC['tpl']
            );
        };

        $dic['ilObjStudyProgrammeMembersGUI'] = function ($dic) use ($DIC) {
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
                new ilConfirmationGUI()
            );
        };
        $dic['ilObjStudyProgrammeAutoMembershipsGUI'] = function ($dic) use ($DIC) {
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
                $DIC->refinery(),
            );
        };
        $dic['ilObjStudyProgrammeTreeGUI'] = function ($dic) use ($DIC) {
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
                $DIC['rbacadmin']
            );
        };
        $dic['ilStudyProgrammeTypeGUI'] = function ($dic) use ($DIC) {
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
                $DIC->filesystem()->web()
            );
        };
        $dic['ilStudyProgrammeRepositorySearchGUI'] = function ($dic) {
            return new ilStudyProgrammeRepositorySearchGUI();
        };
        $dic['ilObjStudyProgrammeIndividualPlanGUI'] = function ($dic) use ($DIC) {
            return new ilObjStudyProgrammeIndividualPlanGUI(
                $DIC['tpl'],
                $DIC['ilCtrl'],
                $DIC['lng'],
                $DIC['ilUser'],
                $dic['ilStudyProgrammeUserProgressDB'],
                $dic['ilStudyProgrammeUserAssignmentDB'],
                $dic['PRGMessages']
            );
        };
        $dic['ilObjStudyProgrammeAutoCategoriesGUI'] = function ($dic) use ($DIC) {
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
                $DIC['tree']
            );
        };
        $dic['DataFactory'] = function ($dic) use ($DIC) {
            return new \ILIAS\Data\Factory();
        };
        $dic['ilStudyProgrammeUserProgressDB'] = function ($dic) use ($DIC) {
            return $dic['model.Progress.ilStudyProgrammeProgressRepository'];
        };

        $dic['ilStudyProgrammeUserAssignmentDB'] = function ($dic) use ($DIC) {
            return $dic['model.Assignment.ilStudyProgrammeAssignmentRepository'];
        };

        $dic['ilOrgUnitObjectTypePositionSetting'] = function ($dic) {
            return new ilOrgUnitObjectTypePositionSetting('prg');
        };

        $dic['ilStudyProgrammeMailMemberSearchGUI'] = function ($dic) use ($DIC) {
            return new ilStudyProgrammeMailMemberSearchGUI(
                $DIC['ilCtrl'],
                $DIC['tpl'],
                $DIC['lng'],
                $DIC['ilAccess']
            );
        };
        $dic['ilStudyProgrammeChangeExpireDateGUI'] = function ($dic) use ($DIC) {
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
        $dic['ilStudyProgrammeChangeDeadlineGUI'] = function ($dic) use ($DIC) {
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
        $dic['ilStudyProgrammeDashboardViewGUI'] = function ($dic) use ($DIC) {
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

        $dic['ilStudyProgrammeCommonSettingsGUI'] = function ($dic) use ($DIC) {
            return new ilStudyProgrammeCommonSettingsGUI(
                $DIC['ilCtrl'],
                $DIC['tpl'],
                $DIC['lng'],
                $DIC->object()
            );
        };

        $dic['Log'] = function ($dic) {
            return ilLoggerFactory::getLogger('prg');
        };

        $dic['current_user'] = function ($dic) use ($DIC) {
            return $DIC['ilUser'];
        };
        
        return $dic;
    }
}
