<?php

declare(strict_types=1);

use Pimple\Container;

class ilStudyProgrammeDIC
{
    public static $dic;

    public static function dic() : Container
    {
        if (!self::$dic) {
            self::$dic = self::buildDIC();
        }
        return self::$dic;
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
            return new ilStudyProgrammeAssignmentDBRepository($DIC['ilDB']);
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
        $dic['ilObjStudyProgrammeMembersGUI'] = function ($dic) use ($DIC) {
            return new ilObjStudyProgrammeMembersGUI(
                $DIC['tpl'],
                $DIC['ilCtrl'],
                $DIC['ilToolbar'],
                $DIC['ilAccess'],
                $DIC['lng'],
                $DIC['ilUser'],
                $DIC['ilTabs'],
                $dic['ilStudyProgrammeUserProgressDB'],
                $dic['ilStudyProgrammeUserAssignmentDB'],
                $dic['ilStudyProgrammeRepositorySearchGUI'],
                $dic['ilObjStudyProgrammeIndividualPlanGUI'],
                $dic['ilStudyProgrammePositionBasedAccess']
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
                $DIC['tree']
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
                $DIC['ilSetting']
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
                $dic['model.Type.ilStudyProgrammeTypeRepository'],
                $DIC->ui()->factory()->input(),
                $DIC->ui()->renderer(),
                $DIC->http()->request(),
                $DIC->refinery()
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
                $DIC['ilAccess'],
                $dic['ilStudyProgrammeUserProgressDB'],
                $dic['ilStudyProgrammeUserAssignmentDB']
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
            $lng = $DIC['lng'];
            if (strpos(get_class($lng), 'class@anonymous') === 0) {
                $lng = new \ilSetupLanguage("en");
            }

            return new ilStudyProgrammeUserProgressDB(
                $dic['model.Progress.ilStudyProgrammeProgressRepository'],
                $dic['model.Assignment.ilStudyProgrammeAssignmentRepository'],
                $lng,
                $dic['ilStudyProgrammeEvents']
            );
        };
        $dic['ilStudyProgrammeUserAssignmentDB'] = function ($dic) use ($DIC) {
            $tree = $DIC->offsetExists('tree') ?
                $DIC['tree'] : new ilTree(ROOT_FOLDER_ID);

            $logger = $DIC['ilLog'];
            if (strpos(get_class($logger), 'class@anonymous') === 0) {
                $logger = ilLoggerFactory::getLogger('setup');
            }

            return new ilStudyProgrammeUserAssignmentDB(
                $dic['ilStudyProgrammeUserProgressDB'],
                $dic['model.Assignment.ilStudyProgrammeAssignmentRepository'],
                $tree,
                $dic['ilStudyProgrammeEvents']
            );
        };
        $dic['ilOrgUnitObjectTypePositionSetting'] = function ($dic) {
            return new ilOrgUnitObjectTypePositionSetting('prg');
        };
        $dic['ilStudyProgrammePositionBasedAccess'] = function ($dic) {
            return new ilStudyProgrammePositionBasedAccess(new ilOrgUnitPositionAccess());
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
                $dic['ilStudyProgrammeUserProgressDB']
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
                $dic['ilStudyProgrammeUserProgressDB']
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

        return $dic;
    }
}
