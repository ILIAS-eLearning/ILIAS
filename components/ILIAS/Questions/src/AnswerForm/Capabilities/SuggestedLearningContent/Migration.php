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

namespace ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent;

use ILIAS\Questions\AnswerForm\Capabilities\Migration as MigrationInterface;
use ILIAS\Questions\AnswerForm\Migration\Migration as AnswerFormMigration;
use ILIAS\Questions\AnswerForm\Migration\MigrationInsert;
use ILIAS\Questions\AnswerForm\Migration\SanitizeLegacyText;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\TableNameSpace;
use ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolution;
use ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolutionFile;
use ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolutionsDatabaseRepository;
use ILIAS\Database\FieldDefinition;
use ILIAS\Setup\Environment;

class Migration implements MigrationInterface
{
    use SanitizeLegacyText;

    private ?SuggestedSolutionsDatabaseRepository $old_repository = null;
    private ?\ilResourceStorageMigrationHelper $irss_migration_helper = null;

    public function __construct(
        private readonly TableDefinitions $table_definitions,
    ) {
    }

    #[\Override]
    public function getTableNameSpace(): TableNameSpace
    {
        return $this->table_definitions->getTableNameSpace();
    }

    #[\Override]
    public function completeMigrationInsert(
        Environment $environment,
        AnswerFormMigration $answer_form_migration,
        MigrationInsert $migration_insert
    ): ?MigrationInsert {
        if ($this->old_repository === null) {
            $this->old_repository = new SuggestedSolutionsDatabaseRepository(
                $migration_insert->getDb()
            );
            $this->irss_migration_helper = new \ilResourceStorageMigrationHelper(
                new Stakeholder(),
                $environment
            );
        }

        $old_suggested_learning_content = $this->old_repository->selectFor(
            $migration_insert->getOldQuestionId()
        )[0] ?? null;

        if ($old_suggested_learning_content === null) {
            return null;
        }

        $insert = $this->buildInsertFromOldSuggestedSolution(
            $migration_insert->getAnswerFormId(),
            $old_suggested_learning_content
        );

        if ($insert === null) {
            return null;
        }

        return $migration_insert->withAdditionalInsert($insert);
    }

    private function buildInsertFromOldSuggestedSolution(
        MigrationInsert $migration_insert,
        SuggestedSolution $old_values
    ): ?Insert {
        $type = $this->buildNewTypeFromOld($old_values->getType())->value;
        $content = $this->buildContentStringFromOldSuggesteSolution(
            $migration_insert->getDb(),
            $type,
            $old_values
        );

        if ($content === null) {
            return null;
        }

        $pf = $migration_insert->getPersistenceFactory();
        return $pf->insert(
            $this->table_definitions->getColumns(
                $migration_insert->getTableNameBuilder(),
                TableTypes::SuggestedLearningContent
            ),
            [
                $pf->value(
                    FieldDefinition::T_TEXT,
                    $migration_insert->getAnswerFormId()->toString()
                ),
                $pf->value(
                    FieldDefinition::T_TEXT,
                    $type
                ),
                $pf->value(
                    FieldDefinition::T_TEXT,
                    json_encode($content)
                ),
            ]
        );

    }

    private function buildNewTypeFromOld(
        string $old_type
    ): Types {
        return match($old_type) {
            SuggestedSolution::TYPE_FILE => Types::File,
            SuggestedSolution::TYPE_LM => Types::LearningModule,
            SuggestedSolution::TYPE_LM_PAGE => Types::LearningModulePage,
            SuggestedSolution::TYPE_LM_CHAPTER => Types::LearningModuleChapter,
            SuggestedSolution::TYPE_GLOSARY_TERM => Types::GlossaryTerm
        };
    }

    private function buildContentStringFromOldSuggesteSolution(
        \ilDBInterface $db,
        Types $type,
        SuggestedSolution $old_values
    ): ?array {
        $old_parent_and_owner = $this->fetchQuestionParentAndOwnerFromDB(
            $db,
            $old_values->getQuestionId()
        );
        if ($old_values instanceof SuggestedSolutionFile) {
            $rid = $this->irss_migration_helper->movePathToStorage(
                $this->buildFilePath(
                    $old_parent_and_owner->obj_fi,
                    $old_values
                ),
                $this->determineNewOwner(
                    $old_parent_and_owner->owner
                )
            );

            if ($rid === null) {
                return null;
            }

            return [
                Content::KEY_FILE_TITLE => $old_values->getTitle() === $old_values->getFilename()
                     ? ''
                     : $old_values->getTitle(),
                Content::KEY_RID => $rid->serialize()
            ];
        }

        $id = $this->parseInternalLink(
            $old_values->getInternalLink()
        );

        if ($id === null) {
            return null;
        }

        if ($type === Types::LearningModule) {
            return [
                Content::KEY_TARGET_REF_ID => $id
            ];
        }

        $target_ref_id = $this->fetchTargetRefIdFromDB(
            $db,
            $type,
            $id
        );

        if ($target_ref_id === null) {
            return null;
        }

        return [
            Content::KEY_TARGET_REF_ID => $target_ref_id,
            Content::KEY_SUB_OBJECT_ID => $id
        ];
    }

    private function fetchQuestionParentAndOwnerFromDB(
        \ilDBInterface $db,
        int $old_question_id
    ): \stdClass {
        return $db->fetchObject(
            $db->queryF(
                'SELECT obj_fi, owner FROM qpl_questions WHERE id = %s',
                [FieldDefinition::T_INTEGER],
                [$old_question_id]
            )
        );
    }

    private function fetchTargetRefIdFromDB(
        \ilDBInterface $db,
        Types $type,
        int $sub_object_id
    ): ?int {
        if ($type === Types::LearningModulePage
            || $type === Types::LearningModuleChapter) {
            return $db->fetchObject(
                $db->queryF(
                    'SELECT object_reference.ref_id FROM lm_data' . PHP_EOL
                        . 'INNER JOIN object_reference' . PHP_EOL
                        . 'ON lm_data.lm_id = object_reference.obj_id' . PHP_EOL
                        . 'WHERE lm_data.obj_id = %s',
                    [FieldDefinition::T_INTEGER],
                    [$sub_object_id]
                )
            )?->lm_id;
        }

        return $db->fetchObject(
            $db->queryF(
                'SELECT object_reference.ref_id FROM glossary_term' . PHP_EOL
                    . 'INNER JOIN object_reference' . PHP_EOL
                    . 'ON glossary_term.glo_id = object_reference.obj_id' . PHP_EOL
                    . 'WHERE glossary_term.id = %s',
                [FieldDefinition::T_INTEGER],
                [$sub_object_id]
            )
        )?->glo_id;
    }

    private function determineNewOwner(
        int $owner
    ): int {
        if ($owner > 0) {
            return $owner;
        }

        return $this->irss_migration_helper
                ->getStakeholder()
                ->getOwnerOfNewResources();
    }

    private function buildFilePath(
        int $parent_id,
        SuggestedSolutionFile $old_values
    ): string {
        return \ilFileUtils::removeTrailingPathSeparators(CLIENT_WEB_DIR)
            . "/assessment/{$parent_id}/{$old_values->getQuestionId()}"
            . "/solution/{$old_values->getFilename()}";
    }

    private function parseInternalLink(
        string $link
    ): ?int {
        if (preg_match("/il__\w+_(\d+)/", $link, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }
}
