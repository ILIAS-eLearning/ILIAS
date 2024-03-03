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

namespace ILIAS\Test;

use Pimple\Container as PimpleContainer;
use ILIAS\DI\Container as ILIASContainer;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Test\TestManScoringDoneHelper;
use ILIAS\Test\Scoring\Marks\MarksRepository;
use ILIAS\Test\Scoring\Marks\MarksDatabaseRepository;
use ILIAS\Test\Settings\MainSettings\MainSettingsRepository;
use ILIAS\Test\Settings\MainSettings\MainSettingsDatabaseRepository;
use ILIAS\Test\Administration\TestGlobalSettingsRepository;
use ILIAS\Test\Administration\TestLoggingSettings;
use ILIAS\Test\Logging\TestLoggingRepository;
use ILIAS\Test\Logging\TestLoggingDatabaseRepository;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Logging\TestLogViewer;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

class TestDIC extends PimpleContainer
{
    protected static ?self $dic = null;

    public static function dic(): self
    {
        if (!self::$dic) {
            global $DIC;
            self::$dic = self::buildDIC($DIC);
        }
        return self::$dic;
    }

    protected static function buildDIC(ILIASContainer $DIC): self
    {
        $dic = new self();
        $dic['shuffler'] = static fn($c): \ilTestShuffler =>
            new \ilTestShuffler($DIC['refinery']);

        $dic['results.factory'] = static fn($c): \ilTestResultsFactory =>
            new \ilTestResultsFactory(
                $c['shuffler'],
                $DIC['ui.factory'],
                $DIC['ui.renderer']
            );

        $dic['results.presentation.factory'] = static fn($c): \ilTestResultsPresentationFactory =>
           new \ilTestResultsPresentationFactory(
               $DIC['ui.factory'],
               $DIC['ui.renderer'],
               $DIC['refinery'],
               new DataFactory(),
               $DIC['http'],
               $DIC['lng']
           );

        $dic['settings.main.repository'] = static fn($c): MainSettingsRepository =>
            new MainSettingsDatabaseRepository($DIC['ilDB']);

        $dic['participant.access_filter.factory'] = static fn($c): \ilTestParticipantAccessFilterFactory =>
            new \ilTestParticipantAccessFilterFactory($DIC['ilAccess']);

        $dic['scoring.manual.done_helper'] = static fn($c): TestManScoringDoneHelper =>
            new TestManScoringDoneHelper();

        $dic['marks.repository'] = static fn($c): MarksRepository =>
            new MarksDatabaseRepository($DIC['ilDB']);

        $dic['request_data_collector'] = static fn($c): RequestDataCollector =>
            new RequestDataCollector(
                $DIC['http'],
                $DIC['refinery']
            );

        $dic['settings.global.repository'] = static fn($c): TestGlobalSettingsRepository =>
                new TestGlobalSettingsRepository(new \ilSetting('assessment'));

        $dic['logging.settings'] = static fn($c): TestLoggingSettings =>
            $c['settings.global.repository']->getLoggingSettings();

        $dic['logging.factory'] = static fn($c): InteractionFactory =>
            new InteractionFactory(
                $DIC['lng']
            );

        $dic['logging.repository'] = static fn($c): TestLoggingRepository =>
            new TestLoggingDatabaseRepository(
                $c['logging.factory'],
                $DIC['ilDB']
            );

        $dic['logging.logger'] = static fn($c): TestLogger =>
            new TestLogger(
                $c['logging.settings'],
                $c['logging.repository'],
                $c['logging.factory'],
                \ilLoggerFactory::getLogger('tst'),
                $DIC['lng']
            );

        $dic['logging.viewer'] = static fn($c): TestLogViewer =>
            new TestLogViewer(
                $c['logging.repository'],
                $c['logging.logger'],
                $DIC->http()->request(),
                $DIC->uiService(),
                $DIC['ui.factory'],
                $DIC['lng']
            );

        $dic['question.general_properties.repository'] = static fn($c): GeneralQuestionPropertiesRepository =>
            new GeneralQuestionPropertiesRepository(
                $DIC['ilDB'],
                $DIC['component.factory'],
                $DIC['lng']
            );

        return $dic;
    }
}
