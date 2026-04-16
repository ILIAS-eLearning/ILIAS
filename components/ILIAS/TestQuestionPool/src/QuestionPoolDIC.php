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

namespace ILIAS\TestQuestionPool;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Bridge\StateHolder;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Builder;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportSessionRepository;
use ILIAS\TestQuestionPool\ExportImport\QuestionPoolExporter;
use ILIAS\TestQuestionPool\ExportImport\QuestionPoolImporter;
use ILIAS\TestQuestionPool\ExportImport\SkillAssignmentsImporter;
use Pimple\Container as PimpleContainer;
use ILIAS\DI\Container as ILIASContainer;
use ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolutionsDatabaseRepository;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\TestQuestionPool\Questions\Files\QuestionFiles;
use ILIAS\Test\Participants\ParticipantRepository;
use ILIAS\Test\Settings\GlobalSettings\Repository as GlobalTestSettingsRepository;
use ILIAS\Test\Settings\GlobalSettings\GlobalTestSettings;

class QuestionPoolDIC extends PimpleContainer
{
    public static ?self $dic = null;

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
        $dic['request_data_collector'] = static fn($c): RequestDataCollector =>
            new RequestDataCollector(
                $DIC->http(),
                $DIC['refinery'],
                $DIC['upload']
            );
        $dic['question.repo.suggestedsolutions'] = static fn($c): SuggestedSolutionsDatabaseRepository =>
            new SuggestedSolutionsDatabaseRepository($DIC['ilDB']);
        $dic['question.general_properties.repository'] = static fn($c): GeneralQuestionPropertiesRepository =>
            new GeneralQuestionPropertiesRepository(
                $DIC['ilDB'],
                $DIC['component.factory'],
                $DIC['component.repository']
            );
        $dic['question_files'] = fn($c): QuestionFiles =>
            new QuestionFiles();

        $dic['participant_repository'] = static fn($c): ParticipantRepository =>
            new ParticipantRepository($DIC['ilDB']);
        $dic['global_test_settings'] = static fn($c): GlobalTestSettings =>
            (new GlobalTestSettingsRepository($DIC['ilSetting'], new \ilSetting('assessment')))->getGlobalSettings();

        $dic['exportimport.builder'] = static fn($c): Builder =>
            new Builder(
                $DIC,
                $c
            );
        $dic['exportimport.state_holder'] = static fn($c): StateHolder =>
            new StateHolder();
        $dic['exportimport.exporter'] = static fn($c): QuestionPoolExporter =>
            new QuestionPoolExporter(
                $c['exportimport.builder'],
                new DataFactory(),
                $c['question.general_properties.repository'],
                $DIC->database(),
                $DIC->taxonomy()->domain()
            );

        $dic['exportimport.session'] = static fn($c): ImportSessionRepository =>
            new ImportSessionRepository('qpl');
        $dic['exportimport.skill_assignments_importer'] = static fn($c): SkillAssignmentsImporter =>
            new SkillAssignmentsImporter(
                $DIC->skills()->internal()->repo()->getTreeRepo(),
                $DIC->skills()->usage(),
                (int) $DIC->settings()->get('inst_id', '0')
            );
        $dic['exportimport.importer'] = static fn($c): QuestionPoolImporter =>
            new QuestionPoolImporter(
                $c['exportimport.builder'],
                $DIC->ctrl(),
                $DIC->database(),
                $DIC->language(),
                $c['exportimport.skill_assignments_importer']
            );

        return $dic;
    }
}
