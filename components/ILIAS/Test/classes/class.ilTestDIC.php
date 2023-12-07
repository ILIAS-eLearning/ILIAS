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

declare(strict_types=1);

use Pimple\Container;
use ILIAS\Test\TestManScoringDoneHelper;
use ILIAS\Test\InternalRequestService;
use ILIAS\Test\Administration\TestGlobalSettingsRepository;
use ILIAS\Test\Administration\TestLoggingSettings;

class ilTestDIC extends Container
{
    protected static ?Container $dic = null;

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
        $dic = $DIC;

        $dic['shuffler'] = static fn($c): ilTestShuffler =>
            new ilTestShuffler($DIC['refinery']);

        $dic['factory.results'] = static fn($c): ilTestResultsFactory =>
            new ilTestResultsFactory(
                $c['shuffler'],
                $dic['ui.factory'],
                $dic['ui.renderer']
            );

        $dic['factory.results_presentation'] = static fn($c): ilTestResultsPresentationFactory =>
           new ilTestResultsPresentationFactory(
               $DIC['ui.factory'],
               $DIC['ui.renderer'],
               $DIC['refinery'],
               new ILIAS\Data\Factory(),
               $DIC['http'],
               $DIC['lng']
           );

        $dic['main_settings_repository'] = static fn($c): ilObjTestMainSettingsDatabaseRepository =>
            new ilObjTestMainSettingsDatabaseRepository($DIC['ilDB']);

        $dic['participantAccessFilterFactory'] = static fn($c): ilTestParticipantAccessFilterFactory =>
            new ilTestParticipantAccessFilterFactory($DIC['ilAccess']);

        $dic['manScoringDoneHelper'] = static fn($c): TestManScoringDoneHelper =>
            new TestManScoringDoneHelper();

        $dic['request.internal'] = static fn($c): InternalRequestService =>
            new InternalRequestService($DIC['http'], $DIC['refinery']);

        $dic['global_settings_repository'] = static fn($c): TestGlobalSettingsRepository =>
                new TestGlobalSettingsRepository(new ilSetting('assessment'));

        return $dic;
    }
}
