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

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;

trait TestQuestionsImportTrait
{
    private string $import_temp_directory = CLIENT_DATA_DIR . DIRECTORY_SEPARATOR . 'temp';

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

    private function buildImportDirectoriesFromContainerImport(string $importdir): array
    {
        return [
            $importdir,
            $importdir . DIRECTORY_SEPARATOR . basename($importdir) . '.xml',
            $importdir . DIRECTORY_SEPARATOR . preg_replace('/test|tst|qpl/', 'qti', basename($importdir)) . '.xml'
        ];
    }

    private function buildResultsFilePath(string $importdir, string $subdir): string
    {
        return $importdir . DIRECTORY_SEPARATOR
                . str_replace('tst', 'results', $subdir) . '.xml';
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
        string $qtifile,
        ServerRequestInterface $request
    ): array {
        $data = $this->buildImportQuestionsSelectionForm(
            $form_cmd,
            $importdir,
            $qtifile
        )->withRequest($request)->getData();
        if (isset($data['selected_questions'])) {
            return $data['selected_questions'];
        }
        return [];
    }

    private function buildImportQuestionsSelectionForm(
        string $form_cmd,
        string $importdir,
        string $qtifile,
        ?string $path_to_uploaded_file_in_temp_dir = null
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
            $options[$item['ident']] = "{$item['title']} ({$this->getLabelForQuestionType($item['type'])})";
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
