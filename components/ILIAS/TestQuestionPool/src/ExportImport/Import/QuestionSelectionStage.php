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

use ilComponentFactory;
use ILIAS\Language\Language;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\ImportStage;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\StageResult;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing\XMLFileDeserializer;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Factory as UIFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Second stage of the question pool import process. It displays the list of questions found in the imported file and
 * allows the user to select the questions to import.
 */
class QuestionSelectionStage implements ImportStage
{
    public const string SELECTED_QUESTIONS = 'selected_questions';
    private const string SELECTABLE_QUESTIONS = 'selectable_questions';

    private array $old_export_question_types = [
        'ORDERING QUESTION' => \ilQTIItem::QT_ORDERING,
        'KPRIM CHOICE QUESTION' => \ilQTIItem::QT_KPRIM_CHOICE,
        'LONG MENU QUESTION' => \ilQTIItem::QT_LONG_MENU,
        'SINGLE CHOICE QUESTION' => \ilQTIItem::QT_MULTIPLE_CHOICE_SR,
        'MULTIPLE CHOICE QUESTION' => \ilQTIItem::QT_MULTIPLE_CHOICE_MR,
        'MATCHING QUESTION' => \ilQTIItem::QT_MATCHING,
        'CLOZE QUESTION' => \ilQTIItem::QT_CLOZE,
        'IMAGE MAP QUESTION' => \ilQTIItem::QT_IMAGEMAP,
        'TEXT QUESTION' => \ilQTIItem::QT_TEXT,
        'NUMERIC QUESTION' => \ilQTIItem::QT_NUMERIC,
        'TEXTSUBSET QUESTION' => \ilQTIItem::QT_TEXTSUBSET
    ];

    public function __construct(
        private readonly Language $lng,
        private readonly LoggerInterface $log,
        private readonly ilComponentFactory $component_factory,
        private readonly UIFactory $ui_factory,
        private readonly ServerRequestInterface $request,
        private readonly string $form_action,
        private readonly string $title,
    ) {
    }

    public function getIdentifier(): string
    {
        return 'question_selection';
    }

    public function getLabel(): ?string
    {
        return $this->lng->txt('qpl_import_step_select');
    }

    public function getDescription(): ?string
    {
        return '';
    }

    public function process(ImportContext $context): StageResult
    {
        if ($context->has('selectable_questions')) {
            $options = [];
            foreach ($context->get('selectable_questions') as $question) {
                $options[$question] = $question;
            }

            $data = $this->buildSelectQuestionsForm($options)
                ->withRequest($this->request)
                ->getData();

            if (isset($data['selected_questions'])) {
                return StageResult::advance($context->with(self::SELECTED_QUESTIONS, $data['selected_questions']));
            }
        }

        if (!$context->has(UploadValidationStage::COMPONENT_IMPORT_FILE)) {
            $this->log->error("No component import file found in context");
            return StageResult::error($context, $this->lng->txt('qpl_import_file_not_found'));
        }

        $options = DetectLegacyImportStage::isLegacyImport($context)
            ? $this->readQuestionsFromQTI($context)
            : $this->readQuestions($context);

        if ($options === []) {
            $this->log->error("No questions found in import file");
            return StageResult::error($context, $this->lng->txt('qpl_import_no_items'));
        }

        $panel = $this->ui_factory->panel()->standard(
            $this->title,
            [
                $this->ui_factory->legacy()->content($this->lng->txt('qpl_import_verify_found_questions')),
                $this->buildSelectQuestionsForm($options)
            ]
        );

        return StageResult::interact(
            $context->with(self::SELECTABLE_QUESTIONS, array_keys($options)),
            [$panel]
        );
    }

    /**
     * @return list<int>
     */
    public static function getSelectedQuestions(ImportContext $context): array
    {
        return array_map('intval', $context->get(self::SELECTED_QUESTIONS, []));
    }

    private function readQuestions(ImportContext $context): array
    {
        $options = [];

        $deserializer = new XMLFileDeserializer()->open(
            $context->get(UploadValidationStage::COMPONENT_IMPORT_FILE)
        );

        $deserializer->addHandler('questions', function (array $questions) use (&$options): void {
            foreach ($questions as $question) {
                if (!isset($question['title']) || !isset($question['type'])) {
                    continue;
                }

                $raw_id = $question['id'];
                $id = is_array($raw_id) ? (string) ($raw_id['id'] ?? '') : (string) $raw_id;
                $options[$id] = "{$question['title']} ({$this->getLabelForQuestionType($question['type'])})";
            }
        });
        $deserializer->process();

        $count = count($options);
        $this->log->info("Found {$count} questions in import file");

        return $options;
    }

    /**
     * @deprecated This method is only used for legacy imports and will be removed with further ILIAS versions.
     */
    private function readQuestionsFromQTI(ImportContext $context): array
    {
        $parser = new \ilQTIParser(
            $context->get(UploadValidationStage::IMPORT_BASE_DIR),
            $context->get(DetectLegacyImportStage::LEGACY_QTI_FILE),
            \ilQTIParser::IL_MO_VERIFY_QTI,
            0
        );
        $parser->startParsing();

        $options = [];
        foreach ($parser->getFoundItems() as $item) {
            $options[$item['ident']] = "{$item['title']} ({$this->getLabelForQuestionType($item['type'])})";
        }

        $count = count($options);
        $this->log->info("Found {$count} questions in legacy import file");

        return $options;
    }

    private function buildSelectQuestionsForm(array $options): Form
    {
        $input = $this->ui_factory->input()->field()->multiSelect(
            $this->lng->txt('questions'),
            $options
        )->withValue(array_keys($options));

        $form = $this->ui_factory->input()->container()->form()->standard(
            $this->form_action,
            ['selected_questions' => $input]
        )->withSubmitLabel($this->lng->txt('import'));

        return $form;
    }

    private function getLabelForQuestionType(string $type): string
    {
        if ($this->lng->exists($type)) {
            return $this->lng->txt($type);
        }

        /**
         * @todo Remove with ILIAS 12: This is here for backward compatibility.
         * As we support the import of a previous version this should go with
         * ILIAS 11, but being generous: ILIAS 12 it is.
         */
        if (array_key_exists($type, $this->old_export_question_types)) {
            return $this->lng->txt($this->old_export_question_types[$type]);
        }
        return $this->getLabelForPluginQuestionTypes($type);
    }

    private function getLabelForPluginQuestionTypes(string $type): string
    {
        foreach ($this->component_factory->getActivePluginsInSlot('qst') as $pl) {
            if ($pl->getQuestionType() === $type) {
                return $pl->getQuestionTypeTranslation();
            }
        }
        return $type;
    }
}
