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

namespace ILIAS\TestQuestionPool\ExportImport\Import;

use assFormulaQuestion;
use assFormulaQuestionUnit;
use assFormulaQuestionUnitCategory;
use assQuestion;
use ilAssQuestionPage;
use ilCtrl;
use ilDBInterface;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Filesystems;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\Convert\ImageOutputOptions;
use ILIAS\Filesystem\Util\Convert\Images;
use ILIAS\Language\Language;
use ILIAS\TestQuestionPool\ExportImport\Envelopes\QuestionImage;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Envelopes\Feedback;
use ILIAS\TestQuestionPool\ExportImport\Pipes\CollectQuestionImages;
use ilImportMapping;
use ilUnitConfigurationRepository;
use Psr\Log\LoggerInterface;

class QuestionsImporter
{
    private readonly Filesystem $filesystem;

    public function __construct(
        private readonly string $component,
        private readonly string $parent_type,
        private readonly ilCtrl $ctrl,
        private readonly ilDBInterface $database,
        private readonly Language $language,
        private readonly LoggerInterface $log,
        private readonly Images $image_converter,
        Filesystems $filesystems,
    ) {
        $this->filesystem = $filesystems->web();
    }

    public function importQuestion(
        array $normalized,
        Transformations $transformations,
        ilImportMapping $mapping,
        array $selected_questions
    ): ?assQuestion {
        $question_class = $normalized['type'];
        if (!class_exists($question_class)) {
            throw new \InvalidArgumentException("Question class {$question_class} does not exist");
        }

        /** @var assQuestion $question */
        $question = $transformations->denormalize($normalized, new $question_class());
        $old_question_id = $question->getId();
        if (!in_array($old_question_id, $selected_questions)) {
            $this->log->debug("Skipping question import for ID {$old_question_id} (not selected)");
            return null;
        }

        // Initialize feedback object to prevent error when saving the question
        $feedback_class = $question::getFeedbackClassNameByQuestionType($question->getQuestionType());
        $question->feedbackOBJ = new $feedback_class($question, $this->ctrl, $this->database, $this->language);

        // Create new question and store basic question properties
        $new_question_id = $question->createNewQuestion(false);
        $this->log->debug("Created new question: {$old_question_id} -> {$new_question_id}");
        $this->storeQuestionMappings($mapping, $old_question_id, $new_question_id, $question->getObjId());

        if ($question instanceof assFormulaQuestion) {
            $this->importFormulaQuestion($normalized, $question, $transformations, $mapping, );
        }

        // Save question-specific properties
        $question->saveToDb();
        $this->log->debug("Imported question {$new_question_id} (type: {$question->getQuestionType()})");

        $feedback = $transformations->denormalize($normalized['feedback'], Feedback::class);
        $this->importFeedback($feedback, $question);

        return $question;
    }


    public function importQuestionImages(
        int $parent_obj_id,
        ilImportMapping $mapping,
        ImportContext $context,
        CollectQuestionImages $pipe,
    ): void {
        $import_dir = dirname($context->get(UploadValidationStage::COMPONENT_IMPORT_FILE)) . '/expDir_1';

        foreach ($pipe->getEnvelopes() as $filename => $envelope) {
            $source_path = $import_dir . DIRECTORY_SEPARATOR . $filename;
            if (!file_exists($source_path)) {
                $this->log->error("Imported image path does not exist: {$source_path}");
                continue;
            }

            $image_base_path = $this->buildImageBasePath($parent_obj_id, $envelope, $mapping);
            $image_path = "{$image_base_path}/{$envelope->getFilename()}";
            if ($this->filesystem->has($image_path)) {
                $this->log->warning("Question image already exists: {$image_path}, skipping");
                continue;
            }

            $input_stream = Streams::ofReattachableResource(fopen($source_path, 'rb'));
            $this->filesystem->writeStream($image_path, $input_stream);
            $this->log->debug("Imported question image: {$source_path} -> {$image_path}");

            $thumbnail = $this->generateThumbnail($input_stream);
            if (!$thumbnail) {
                continue;
            }

            $thumbnail_path = "{$image_base_path}/thumb.{$envelope->getFilename()}";
            $this->filesystem->writeStream($thumbnail_path, $thumbnail);
            $this->log->debug("Generated question image thumbnail: {$thumbnail_path}");

            $thumbnail->close();
            $input_stream->close();
        }
    }

    private function buildImageBasePath(int $parent_obj_id, QuestionImage $envelope, ilImportMapping $mapping): ?string
    {
        $question_id = $mapping->getMapping($this->component, 'question', (string) $envelope->getQuestionId());
        if (!$question_id) {
            $this->log->error("Question ID mapping not found for {$envelope->getQuestionId()}");
            return null;
        }

        $subdir = $envelope->getType() === QuestionImage::TYPE_SOLUTION ? 'solution' : 'images';
        return "assessment/{$parent_obj_id}/{$question_id}/{$subdir}";
    }


    private function generateThumbnail(FileStream $image_stream): ?FileStream
    {
        $converter = $this->image_converter->thumbnail(
            $image_stream,
            100,
            new ImageOutputOptions()->withFormat(ImageOutputOptions::FORMAT_KEEP),
        );

        if (!$converter->isOK()) {
            $this->log->error("Could not generate thumbnail: {$converter->getThrowableIfAny()?->getMessage()}");
            return null;
        }

        return $converter->getStream();
    }

    /**
     * Finalize the imported question pages by replacing the old question ids with the new question ids.
     */
    public function finalizeQuestionPages(ilImportMapping $mapping): void
    {
        $page_mappings = $mapping->getMappingsOfEntity('components/ILIAS/COPage', 'pg');

        foreach ($page_mappings as $old => $new) {
            if (!preg_match('/^qpl:(\d+)$/', $old, $old_matches)) {
                continue;
            }
            $old_question_id = $old_matches[1];

            if (!preg_match('/^qpl:(\d+)$/', $new, $new_matches)) {
                continue;
            }
            $new_question_id = $new_matches[1];
            $this->log->debug("Finalizing question page: {$old_question_id} -> {$new_question_id}");

            $page = new ilAssQuestionPage((int) $new_question_id);
            $xml = preg_replace(
                '/il_\d+_qst_' . preg_quote($old_question_id, '/') . '\b/',
                "il__qst_{$new_question_id}",
                $page->getXMLContent()
            );
            if ($xml === null) {
                continue;
            }
            $page->setXMLContent($xml);

            $parent_obj_id = $mapping->getMapping(
                $this->component,
                'question_assignment',
                $new_question_id
            );
            if ($parent_obj_id !== null) {
                $page->setParentId((int) $parent_obj_id);
            }

            $page->updateFromXML();
            $this->log->debug("Updated question page: {$page->getId()}");
            unset($page);
        }
    }

    private function storeQuestionMappings(
        ilImportMapping $mapping,
        int $old_question_id,
        int $new_question_id,
        int $parent_obj_id,
    ): void {
        $mapping->addMapping(
            $this->component,
            'question',
            (string) $old_question_id,
            (string) $new_question_id
        );
        $mapping->addMapping(
            $this->component,
            'question_assignment',
            (string) $new_question_id,
            (string) $parent_obj_id
        );
        $mapping->addMapping(
            'components/ILIAS/Taxonomy',
            'tax_item',
            "{$this->parent_type}:quest:{$old_question_id}",
            (string) $new_question_id
        );
        $mapping->addMapping(
            'components/ILIAS/Taxonomy',
            'tax_item_obj_id',
            "{$this->parent_type}:quest:{$old_question_id}",
            (string) $parent_obj_id
        );
        $mapping->addMapping(
            'components/ILIAS/COPage',
            'pg',
            "qpl:{$old_question_id}",
            "qpl:{$new_question_id}"
        );
    }

    private function importFeedback(Feedback $feedback, assQuestion $question): void
    {
        $question_id = $question->getId();
        $question->feedbackOBJ->importGenericFeedback($question_id, false, $feedback->getGenericUncompleted());
        $question->feedbackOBJ->importGenericFeedback($question_id, true, $feedback->getGenericCompleted());

        foreach ($feedback->getSpecificFeedback() as $specific_feedback) {
            $question->feedbackOBJ->importSpecificAnswerFeedback(
                $question_id,
                (int) $specific_feedback['question_index'],
                (int) $specific_feedback['answer_index'],
                $specific_feedback['feedback']
            );
        }

        $this->log->debug("Imported feedback for question: {$question_id}");
    }

    private function importFormulaQuestion(
        array $normalized,
        assFormulaQuestion $question,
        Transformations $transformations,
        ilImportMapping $mapping,
    ): void {
        $formula = $normalized['formula_data'];
        $repository = new ilUnitConfigurationRepository($question->getId());

        // First, import the unit categories which are referenced by the units
        foreach ($formula['categories'] as $normalized_category) {
            $category = $transformations->denormalize($normalized_category, new assFormulaQuestionUnitCategory());
            $old_category_id = $category->getId();

            $repository->saveNewUnitCategory($category);
            $this->log->debug("Imported formula question unit category: {$old_category_id} -> {$category->getId()}");
            $mapping->addMapping($this->component, 'unit_category', (string) $old_category_id, (string) $category->getId());
        }

        // Ensure base units are imported first so they can be referenced by the units. The mapping pipe will ensure
        // that the category id, question id and base unit id are mapped to the new ids.
        $normalized_units = array_merge($formula['base_units'], $formula['units']);
        foreach ($normalized_units as $normalized_unit) {
            $old_unit_id = $transformations->denormalize($normalized_unit['id'], Id::class)->getId();

            $unit = new assFormulaQuestionUnit();
            $repository->createNewUnit($unit);
            $mapping->addMapping($this->component, 'unit', (string) $old_unit_id, (string) $unit->getId());

            $unit = $transformations->denormalize($normalized_unit, $unit);
            $repository->saveUnit($unit);
            $this->log->debug("Imported formula question unit: {$old_unit_id} -> {$unit->getId()}");
        }

        // The question object is denormalized again to ensure the new unit ids are set in the variables and results.
        $new_question = $transformations->denormalize($normalized, $question);

        $question->clearVariables();
        foreach ($new_question->getVariables() as $variable) {
            $question->addVariable($variable);
        }

        $question->clearResults();
        foreach ($new_question->getResults() as $result) {
            $question->addResult($result);
        }
    }
}
