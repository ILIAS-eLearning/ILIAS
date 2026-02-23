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

namespace ILIAS\Test\Settings\Templates;

use ILIAS\Test\Scoring\Marks\MarksRepository;
use ILIAS\Test\Settings\MainSettings\MainSettingsRepository;
use ILIAS\Test\Settings\ScoreReporting\ScoreSettingsRepository;
use PersonalSettingsImportHandlerGUI;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Language\Language;
use ILIAS\Test\Scoring\Marks\MarkSchema;
use ILIAS\Test\Settings\MainSettings\MainSettings;
use ILIAS\Test\Settings\ScoreReporting\ScoreSettings;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Factory as UIFactory;
use Psr\Http\Message\ServerRequestInterface;

class PersonalSettingsImportAction
{
    private const SCHEMA_FILE = __DIR__ . '/../../../xml/personal-settings-template.xsd';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly Language $lng,
        private readonly DataFactory $data_factory,
        private readonly Filesystem $filesystem,
        private readonly PersonalSettingsRepository $repository,
        private readonly MainSettingsRepository $main_settings_repository,
        private readonly ScoreSettingsRepository $score_settings_repository,
        private readonly MarksRepository $marks_repository,
    ) {
    }

    public function buildModal(string $url): RoundTrip
    {
        $input_handler = new PersonalSettingsImportHandlerGUI();

        $file_upload_input = $this->ui_factory->input()->field()->file($input_handler, $this->lng->txt('import_file'))
            ->withAcceptedMimeTypes(PersonalSettingsImportHandlerGUI::SUPPORTED_IMPORT_MIME_TYPES)
            ->withRequired(true)
            ->withMaxFiles(1);

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('personal_settings_import'),
            [],
            ['upload' => $file_upload_input],
            $url
        )->withSubmitLabel($this->lng->txt('import'));
    }

    public function perform(ServerRequestInterface $request): void
    {
        $data = $this->buildModal('')->withRequest($request)->getData();

        if (!isset($data['upload']) || $data['upload'] === []) {
            throw new \InvalidArgumentException('import_file_not_valid_here');
        }

        $upload_dir = array_pop($data['upload']);
        $files = $this->filesystem->listContents($upload_dir);

        if (count($files) !== 1) {
            throw new \InvalidArgumentException('import_file_not_valid_here');
        }

        $this->importFile($files[0]->getPath());

        $this->filesystem->deleteDir($upload_dir);
    }

    public function importFile(string $file): void
    {
        $dom = new \DOMDocument();
        $dom->resolveExternals = false;
        $dom->substituteEntities = false;
        $dom->validateOnParse = false;
        $dom->loadXML($this->filesystem->read($file), LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

        if (!$dom->schemaValidate(self::SCHEMA_FILE)) {
            throw new \ilImportException('XML validation failed against XSD schema');
        }
        $doc = $dom->documentElement;

        $imported_ilias_version = $this->data_factory->version($doc->getAttribute('ilias-version'));
        $current_ilias_version = $this->data_factory->version(ILIAS_VERSION_NUMERIC);

        if ($imported_ilias_version->getMajor() > $current_ilias_version->getMajor()) {
            throw new \ilImportException('Unsupported Import between ILIAS major versions');
        }

        $main_settings_data = $this->parseElementsRecursive(
            $this->firstChildElement($doc, 'main-settings')
        );

        $score_settings_data = $this->parseElementsRecursive(
            $this->firstChildElement($doc, 'score-settings')
        );

        $mark_schema_data = $this->parseElementsRecursive(
            $this->firstChildElement($doc, 'mark-schema')
        );

        $imported_template = PersonalSettingsTemplate::fromExport($this->getAttributes($doc));
        $template = $this->repository->create(
            $imported_template->getName(),
            $imported_template->getDescription(),
            $imported_template->getAuthor(),
            $imported_template->getCreatedAt()
        );

        $this->main_settings_repository->store(
            MainSettings::fromExport($main_settings_data)->withId($template->getSettingsId())
        );
        $this->score_settings_repository->store(
            ScoreSettings::fromExport($score_settings_data)->withId($template->getSettingsId())
        );

        $mark_ids = $this->marks_repository->storeMarkSchema(MarkSchema::fromExport($mark_schema_data));
        $this->repository->associateMarkSteps($template->getId(), $mark_ids);
    }

    /**
     * Returns the attributes of the given element as an associative array. It will replace hyphens with underscores in
     * the attribute names.
     *
     * @return array<string, string>
     */
    private function getAttributes(\DOMElement $element): array
    {
        $attributes = [];
        foreach ($element->getAttributeNames() as $name) {
            $property_name = str_replace('-', '_', $name);
            $attributes[$property_name] = $this->sanitizeContent($element->getAttribute($name));
        }
        return $attributes;
    }

    /**
     * Returns the first child element of the given element with the given name. It returns null if no child element
     * with the given name exists.
     */
    private function firstChildElement(\DOMElement $element, string $element_name): ?\DOMElement
    {
        foreach ($element->getElementsByTagName($element_name) as $item) {
            if ($item instanceof \DOMElement) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Parses the given element recursively into an associative array structure. It will use the 'name' attribute as key
     * for the array. If the element has no 'name' attribute, it will use the array as a list.
     */
    private function parseElementsRecursive(\DOMElement $parent): mixed
    {
        $children = array_filter(
            iterator_to_array($parent->childNodes),
            static fn(mixed $child): bool => $child instanceof \DOMElement,
        );

        if ($children !== []) {
            $settings = [];
            foreach ($children as $child) {
                if ($name = $child->getAttribute('name')) {
                    $settings[$name] = $this->parseElementsRecursive($child);
                } else {
                    $settings[] = $this->parseElementsRecursive($child);
                }
            }
            return $settings;
        }

        $type = $parent->getAttribute('type') ?? 'string';
        $value = $this->sanitizeContent($parent->textContent);
        return match($type) {
            'string' => htmlspecialchars_decode($value),
            'integer' => (int) $value,
            'boolean' => $value == 'true',
            'double' => (float) $value,
            'NULL' => null,
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };
    }

    /**
     * Sanitize string values parsed from XML to avoid displaying malicious content.
     *
     * - Decodes HTML entities to catch encoded tags
     * - Strips all HTML/PHP tags
     * - Removes control characters (except tab, newline, carriage return)
     * - Trims surrounding whitespace
     */
    private function sanitizeContent(string $value): string
    {
        // Decode entities first so that encoded tags like &lt;script&gt; are handled
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Remove all tags
        $stripped = strip_tags($decoded);
        // Remove non-printable control characters except for common whitespace (tab, LF, CR)
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $stripped);
        return trim($clean ?? '');
    }
}
