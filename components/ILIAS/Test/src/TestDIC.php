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

use ILIAS\Test\Participants\ParticipantRepository;
use ILIAS\Test\Results\TestPassResultRepository;
use ILIAS\Test\Utilities\TitleColumnsBuilder;
use ILIAS\Test\TestManScoringDoneHelper;
use ILIAS\Test\Scoring\Marks\MarksRepository;
use ILIAS\Test\Scoring\Marks\MarksDatabaseRepository;
use ILIAS\Test\Settings\MainSettings\MainSettingsRepository;
use ILIAS\Test\Settings\MainSettings\MainSettingsDatabaseRepository;
use ILIAS\Test\Settings\GlobalSettings\Repository as GlobalSettingsRepository;
use ILIAS\Test\Settings\GlobalSettings\TestLoggingSettings;
use ILIAS\Test\Logging\TestLoggingRepository;
use ILIAS\Test\Logging\TestLoggingDatabaseRepository;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Logging\TestLogViewer;
use ILIAS\Test\Logging\Factory as InteractionFactory;
use ILIAS\Test\ExportImport\Factory as ExportImportFactory;
use ILIAS\Test\Questions\Properties\Repository as TestQuestionsRepository;
use ILIAS\Test\Questions\Properties\DatabaseRepository as TestQuestionsDatabaseRepository;
use ILIAS\Test\Results\Data\Factory as ResultsDataFactory;
use ILIAS\Test\Results\Presentation\Factory as ResultsPresentationFactory;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\TestQuestionPool\RequestDataCollector as QPLRequestDataCollector;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\DI\Container as ILIASContainer;
use Pimple\Container as PimpleContainer;

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

        $dic['title_columns_builder'] = static fn($c): TitleColumnsBuilder =>
            new TitleColumnsBuilder(
                $c['question.general_properties.repository'],
                $DIC['ilCtrl'],
                $DIC['ilAccess'],
                $DIC['lng'],
                $DIC['static_url'],
                $DIC['ui.factory']
            );

        $dic['results.data.factory'] = static fn($c): ResultsDataFactory =>
            new ResultsDataFactory(
                $c['shuffler'],
                $DIC['ui.factory'],
                $DIC['ui.renderer']
            );

        $dic['results.presentation.factory'] = static fn($c): ResultsPresentationFactory =>
           new ResultsPresentationFactory(
               $DIC['ui.factory'],
               $DIC['ui.renderer'],
               $DIC['refinery'],
               new DataFactory(),
               $DIC['http'],
               $DIC['lng']
           );

        $dic['results.data.test_pass_result_repository'] = static fn($c): TestPassResultRepository =>
            new TestPassResultRepository($DIC['ilDB']);

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

        $dic['response_handler'] = static fn($c): ResponseHandler =>
            new ResponseHandler($DIC['http'], );

        $dic['settings.global.repository'] = static fn($c): GlobalSettingsRepository =>
                new GlobalSettingsRepository(new \ilSetting('assessment'));

        $dic['logging.settings'] = static fn($c): TestLoggingSettings =>
            $c['settings.global.repository']->getLoggingSettings();

        $dic['logging.factory'] = static fn($c): InteractionFactory =>
            new InteractionFactory();

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
                new Logging\AdditionalInformationGenerator(
                    (new \ilMustacheFactory())->getBasicEngine(),
                    $DIC['lng'],
                    $DIC['ui.factory'],
                    $DIC['refinery'],
                    $c['question.general_properties.repository']
                ),
                \ilLoggerFactory::getLogger('tst')
            );

        $dic['logging.viewer'] = static fn($c): TestLogViewer =>
            new TestLogViewer(
                $c['logging.repository'],
                $c['logging.logger'],
                $c['title_columns_builder'],
                $c['question.general_properties.repository'],
                $DIC['http']->request(),
                $DIC['http']->wrapper()->query(),
                $DIC->uiService(),
                $DIC['ui.factory'],
                $DIC['ui.renderer'],
                $DIC['refinery'],
                $DIC['lng'],
                $DIC['tpl'],
                $DIC['file_delivery']->delivery(),
                $DIC['ilUser']
            );

        $dic['exportimport.factory'] = static fn($c): ExportImportFactory =>
            new ExportImportFactory(
                $DIC['lng'],
                $DIC['ilDB'],
                $DIC['ilBench'],
                $DIC['tpl'],
                $c['logging.logger'],
                $DIC['tree'],
                $DIC['component.repository'],
                $DIC['component.factory'],
                $DIC['file_delivery'],
                $DIC['ilUser'],
                $c['question.general_properties.repository']
            );

        $dic['questions.properties.repository'] = static fn($c): TestQuestionsRepository =>
            new TestQuestionsDatabaseRepository(
                $DIC['ilDB'],
                $c['question.general_properties.repository']
            );

        $dic['question.general_properties.repository'] = static fn($c): GeneralQuestionPropertiesRepository =>
            new GeneralQuestionPropertiesRepository(
                $DIC['ilDB'],
                $DIC['component.factory'],
                $DIC['component.repository']
            );

        $dic['question.request_data_wrapper'] = static fn($c): QPLRequestDataCollector =>
            new QPLRequestDataCollector(
                $DIC->http(),
                $DIC['refinery'],
                $DIC['upload']
            );

        $dic['participant.repository'] = static fn($c): ParticipantRepository =>
            new ParticipantRepository($DIC['ilDB']);

        return $dic;
    }
}
