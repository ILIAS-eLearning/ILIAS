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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Test\Scoring\Marks\MarkSchema;
use ILIAS\Test\Settings\MainSettings\MainSettings;
use ILIAS\Test\Settings\ScoreReporting\ScoreSettings;

class PersonalSettingsImporter
{
    private const SCHEMA_FILE = __DIR__ . '/../../../xml/personal-settings-template.xsd';

    public function __construct(
        private readonly DataFactory $data_factory,
        private readonly Filesystem $filesystem,
        private readonly PersonalSettingsRepository $repository,
    ) {
    }

    public function run(string $file): void
    {
        $xml_content = $this->filesystem->read($file);

        $dom = new \DOMDocument();
        $dom->loadXML($xml_content);


        if (!$dom->schemaValidate(self::SCHEMA_FILE)) {
            throw new \ilImportException('XML validation failed against XSD schema');
        }
        $doc = $dom->documentElement;

        $imported_ilias_version = $this->data_factory->version($doc->getAttribute('ilias-version'));
        $current_ilias_version = $this->data_factory->version(ILIAS_VERSION_NUMERIC);

        if ($imported_ilias_version->getMajor() > $current_ilias_version->getMajor()) {
            throw new \ilImportException('Unsupported Import between ILIAS major versions');
        }

        $template_data = $this->getAttributes($doc);

        $main_settings_data = $this->parseElementsRecursive(
            $this->firstChildElement($doc, 'main-settings')
        );

        $score_settings_data = $this->parseElementsRecursive(
            $this->firstChildElement($doc, 'score-settings')
        );

        $mark_schema_data = $this->parseElementsRecursive(
            $this->firstChildElement($doc, 'mark-schema')
        );

        $this->repository->createTemplate(
            PersonalSettingsTemplate::denormalize($template_data),
            MainSettings::denormalize($main_settings_data),
            ScoreSettings::denormalize($score_settings_data),
            MarkSchema::denormalize($mark_schema_data)
        );
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
            $attributes[$property_name] = $element->getAttribute($name);
        }
        return $attributes;
    }

    /**
     * Returns the first child element of the given element with the given name. It returns null if no child element
     * with the given name exists.
     */
    private function firstChildElement(\DOMElement $element, string $element_name): ?\DOMElement
    {
        $elements = $element->getElementsByTagName($element_name);
        foreach ($elements as $element) {
            if ($element instanceof \DOMElement) {
                return $element;
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
            fn($child) => $child instanceof \DOMElement
        );

        if (count($children) > 0) {
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
        $value = $parent->textContent;
        return match($type) {
            'string' => $value,
            'integer' => (int) $value,
            'boolean' => $value == 'true',
            'double' => (float) $value,
            'NULL' => null,
            default => throw new \InvalidArgumentException('Invalid type: ' . $type),
        };
    }
}
