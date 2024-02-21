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

namespace ILIAS\TestQuestionPool\Import;

use ILIAS\TestQuestionPool\Questions\QuestionIdentifiers;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;

trait TestQuestionsImportTrait
{
    private string $import_temp_directory = CLIENT_DATA_DIR . DIRECTORY_SEPARATOR . 'temp';

    private function buildImportDirectoriesFromImportFile(string $file_to_import): array
    {
        $subdir = basename($file_to_import, '.zip');
        return [
            $subdir,
            $this->import_temp_directory . DIRECTORY_SEPARATOR . $subdir,
            $this->import_temp_directory . DIRECTORY_SEPARATOR . $subdir . DIRECTORY_SEPARATOR . $subdir . '.xml',
            $this->import_temp_directory . DIRECTORY_SEPARATOR . $subdir . DIRECTORY_SEPARATOR . str_replace(
                ['qpl', 'tst'],
                'qti',
                $subdir
            ) . '.xml'
        ];
    }

    private function getImportTempDirectory(): string
    {
        return $this->import_temp_directory;
    }

    private function buildImportDirectoryFromImportFile(string $file_to_import): string
    {
        $subdir = basename($file_to_import, '.zip');
        return $this->import_temp_directory . DIRECTORY_SEPARATOR . $subdir;
    }

    private function retrieveSelectedQuestionsFromImportQuestionsSelectionForm(
        string $form_cmd,
        string $importdir,
        string $qtifile
    ): array {
        $data = $this->buildImportQuestionsSelectionForm(
            $form_cmd,
            $importdir,
            $qtifile
        )->withRequest($this->request)->getData();
        if (isset($data['selected_questions'])) {
            return $data['selected_questions'];
        }
        return [];
    }

    private function buildImportQuestionsSelectionForm(
        string $form_cmd,
        string $importdir,
        string $qtifile,
        string $path_to_uploaded_file_in_temp_dir = null
    ): ?StandardForm {
        $qtiParser = new \ilQTIParser(
            $importdir,
            $qtifile,
            \ilQTIParser::IL_MO_VERIFY_QTI,
            0,
            []
        );
        $qtiParser->startParsing();
        $founditems = &$qtiParser->getFoundItems();
        if ($path_to_uploaded_file_in_temp_dir !== null && $founditems === []) {
            \ilFileUtils::delDir($importdir);
            $this->deleteUploadedImportFile($path_to_uploaded_file_in_temp_dir);
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('qpl_import_no_items'), true);
            return null;
        }

        $complete = 0;
        $incomplete = 0;
        foreach ($founditems as $item) {
            if ($item['type'] !== '') {
                $complete++;
            } else {
                $incomplete++;
            }
        }

        if ($complete == 0) {
            \ilFileUtils::delDir($importdir);
            $this->deleteUploadedImportFile($path_to_uploaded_file_in_temp_dir);
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('qpl_import_non_ilias_files'), true);
            return null;
        }

        $options = [];
        $values = [];
        foreach ($founditems as $item) {
            switch ($item['type']) {
                case QuestionIdentifiers::CLOZE_TEST_IDENTIFIER:
                    $type = $this->lng->txt('assClozeTest');
                    break;
                case QuestionIdentifiers::IMAGEMAP_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt('assImagemapQuestion');
                    break;
                case QuestionIdentifiers::MATCHING_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt('assMatchingQuestion');
                    break;
                case QuestionIdentifiers::MULTIPLE_CHOICE_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt('assMultipleChoice');
                    break;
                case QuestionIdentifiers::KPRIM_CHOICE_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt('assKprimChoice');
                    break;
                case QuestionIdentifiers::LONG_MENU_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt('assLongMenu');
                    break;
                case QuestionIdentifiers::SINGLE_CHOICE_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt('assSingleChoice');
                    break;
                case QuestionIdentifiers::ORDERING_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt('assOrderingQuestion');
                    break;
                case QuestionIdentifiers::TEXT_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt('assTextQuestion');
                    break;
                case QuestionIdentifiers::NUMERIC_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt('assNumeric');
                    break;
                case QuestionIdentifiers::TEXTSUBSET_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt('assTextSubset');
                    break;
                default:
                    $type = $this->getLabelForPluginQuestionTypes($item['type']);
                    break;
            }

            $options[$item['ident']] = "{$item['title']} ({$type})";
            $values[] = $item['ident'];
        }
        $select_questions = $this->ui_factory->input()->field()->multiSelect(
            $this->lng->txt('questions'),
            $options
        )->withValue($values);

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(self::class, $form_cmd),
            ['selected_questions' => $select_questions]
        )->withSubmitLabel($this->lng->txt('import'));
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
