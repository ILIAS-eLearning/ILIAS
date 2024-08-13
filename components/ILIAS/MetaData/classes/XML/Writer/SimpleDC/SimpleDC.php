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

namespace ILIAS\MetaData\XML\Writer\SimpleDC;

use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\XML\Copyright\CopyrightHandlerInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\Data\ReferenceId;
use ILIAS\MetaData\XML\Copyright\Links\LinkGeneratorInterface;
use ILIAS\Data\Factory as DataFactory;

class SimpleDC implements SimpleDCInterface
{
    protected PathFactory $path_factory;
    protected NavigatorFactoryInterface $navigator_factory;
    protected DataFactory $data_factory;
    protected CopyrightHandlerInterface $copyright_handler;
    protected LinkGeneratorInterface $link_generator;

    public function __construct(
        PathFactory $path_factory,
        NavigatorFactoryInterface $navigator_factory,
        DataFactory $data_factory,
        CopyrightHandlerInterface $copyright_handler,
        LinkGeneratorInterface $link_generator
    ) {
        $this->path_factory = $path_factory;
        $this->navigator_factory = $navigator_factory;
        $this->data_factory = $data_factory;
        $this->copyright_handler = $copyright_handler;
        $this->link_generator = $link_generator;
    }

    public function write(
        SetInterface $set,
        int $object_ref_id
    ): \SimpleXMLElement {
        $xml = new \SimpleXMLElement(<<<XML
            <oai_dc:dc 
                xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" 
                xmlns:dc="http://purl.org/dc/elements/1.1/" 
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
            </oai_dc:dc>
        XML);

        $this->addTitleToXML($xml, $set);
        $this->addCreatorsPublishersAndContributorsToXML($xml, $set);
        $this->addSubjectsToXML($xml, $set);
        $this->addDescriptionsToXML($xml, $set);
        $this->addDateToXML($xml, $set);
        $this->addTypesToXML($xml, $set);
        $this->addFormatsToXML($xml, $set);
        $this->addIdentifierToXML($xml, $set, $object_ref_id);
        $this->addSourcesAndRelationsToXML($xml, $set);
        $this->addLanguagesToXML($xml, $set);
        $this->addCoveragesToXML($xml, $set);
        $this->addRightsToXML($xml, $set);

        return $xml;
    }

    protected function addTitleToXML(\SimpleXMLElement $xml, SetInterface $set): void
    {
        $title_path = $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('title')
            ->get();

        $this->addLangStringsToXML($xml, $set, 'title', $title_path);
    }

    protected function addCreatorsPublishersAndContributorsToXML(\SimpleXMLElement $xml, SetInterface $set): void
    {
        $creator_path = $this->path_factory
            ->custom()
            ->withNextStep('lifeCycle')
            ->withNextStep('contribute')
            ->withNextStep('role')
            ->withNextStep('value')
            ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'author')
            ->withNextStepToSuperElement()
            ->withNextStepToSuperElement()
            ->withNextStep('entity')
            ->get();

        $publisher_path = $this->path_factory
            ->custom()
            ->withNextStep('lifeCycle')
            ->withNextStep('contribute')
            ->withNextStep('role')
            ->withNextStep('value')
            ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'publisher')
            ->withNextStepToSuperElement()
            ->withNextStepToSuperElement()
            ->withNextStep('entity')
            ->get();

        $any_contributor_path = $this->path_factory
            ->custom()
            ->withNextStep('lifeCycle')
            ->withNextStep('contribute')
            ->withNextStep('entity')
            ->get();

        $creators = [];
        $creator_navigator = $this->navigator_factory->navigator($creator_path, $set->getRoot());
        foreach ($creator_navigator->elementsAtFinalStep() as $creator) {
            $creators[] = $creator;
            $this->addNamespacedChildToXML(
                $xml,
                'creator',
                $creator->getData()->value()
            );
        }

        $publishers = [];
        $publisher_navigator = $this->navigator_factory->navigator($publisher_path, $set->getRoot());
        foreach ($publisher_navigator->elementsAtFinalStep() as $publisher) {
            $publishers[] = $publisher;
            $this->addNamespacedChildToXML(
                $xml,
                'publisher',
                $publisher->getData()->value()
            );
        }

        $any_contributor_navigator = $this->navigator_factory->navigator($any_contributor_path, $set->getRoot());
        foreach ($any_contributor_navigator->elementsAtFinalStep() as $any_contributor) {
            if (
                in_array($any_contributor, $creators, true) ||
                in_array($any_contributor, $publishers, true)
            ) {
                continue;
            }

            $this->addNamespacedChildToXML(
                $xml,
                'contributor',
                $any_contributor->getData()->value()
            );
        }
    }

    protected function addSubjectsToXML(\SimpleXMLElement $xml, SetInterface $set): void
    {
        $keyword_path = $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('keyword')
            ->get();

        $this->addLangStringsToXML($xml, $set, 'subject', $keyword_path);

        $taxon_entry_string_path = $this->path_factory
            ->custom()
            ->withNextStep('classification')
            ->withNextStep('purpose')
            ->withNextStep('value')
            ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'discipline')
            ->withNextStepToSuperElement()
            ->withNextStepToSuperElement()
            ->withNextStep('taxonPath')
            ->withNextStep('taxon')
            ->withNextStep('entry')
            ->withNextStep('string')
            ->get();

        $navigator = $this->navigator_factory->navigator($taxon_entry_string_path, $set->getRoot());
        $strings = [];
        $current_taxon_path = null;

        foreach ($navigator->elementsAtFinalStep() as $entry_string) {
            $taxon_path = $entry_string->getSuperElement()->getSuperElement()->getSuperElement();
            if ($current_taxon_path !== $taxon_path) {
                if (!empty($strings)) {
                    $this->addNamespacedChildToXML($xml, 'subject', implode(': ', $strings));
                }

                $current_taxon_path = $taxon_path;
                $strings = [];
            }

            if ($entry_string->getData()->value() !== '') {
                $strings[] = $entry_string->getData()->value();
            }
        }

        if (!empty($strings)) {
            $this->addNamespacedChildToXML($xml, 'subject', implode(': ', $strings));
        }
    }

    protected function addDescriptionsToXML(\SimpleXMLElement $xml, SetInterface $set): void
    {
        $description_path = $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('description')
            ->get();

        $this->addLangStringsToXML($xml, $set, 'description', $description_path);
    }

    protected function addDateToXML(\SimpleXMLElement $xml, SetInterface $set): void
    {
        $date_path = $this->path_factory
            ->custom()
            ->withNextStep('lifeCycle')
            ->withNextStep('contribute')
            ->withNextStep('date')
            ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, '0')
            ->get();

        foreach ($this->getDataValuesFromPath($set, $date_path) as $value) {
            $this->addNamespacedChildToXML($xml, 'date', $value);
        }
    }

    protected function addTypesToXML(\SimpleXMLElement $xml, SetInterface $set): void
    {
        $type_path = $this->path_factory
            ->custom()
            ->withNextStep('educational')
            ->withNextStep('learningResourceType')
            ->withNextStep('value')
            ->get();

        foreach ($this->getDataValuesFromPath($set, $type_path) as $value) {
            $this->addNamespacedChildToXML($xml, 'type', $value);
        }
    }

    protected function addFormatsToXML(\SimpleXMLElement $xml, SetInterface $set): void
    {
        $format_path = $this->path_factory
            ->custom()
            ->withNextStep('technical')
            ->withNextStep('format')
            ->get();

        foreach ($this->getDataValuesFromPath($set, $format_path) as $value) {
            $this->addNamespacedChildToXML($xml, 'format', $value);
        }
    }

    protected function addIdentifierToXML(
        \SimpleXMLElement $xml,
        SetInterface $set,
        int $object_ref_id
    ): void {
        $type = $set->getRessourceID()->type();
        if ($type === '') {
            return;
        }

        $link = $this->link_generator->generateLinkForReference($object_ref_id, $type);
        $this->addNamespacedChildToXML($xml, 'identifier', (string) $link);

        if (!$this->link_generator->doesReferenceHavePublicAccessExport($object_ref_id)) {
            return;
        }
        $download_link = $this->link_generator->generateLinkForPublicAccessExportOfReference($object_ref_id);
        $this->addNamespacedChildToXML($xml, 'identifier', (string) $download_link);
    }

    protected function addLanguagesToXML(\SimpleXMLElement $xml, SetInterface $set): void
    {
        $language_path = $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('language')
            ->get();

        foreach ($this->getDataValuesFromPath($set, $language_path) as $value) {
            $this->addNamespacedChildToXML(
                $xml,
                'language',
                $value === 'xx' ? 'none' : $value
            );
        }
    }

    protected function addSourcesAndRelationsToXML(\SimpleXMLElement $xml, SetInterface $set): void
    {
        $source_path = $this->path_factory
            ->custom()
            ->withNextStep('relation')
            ->withNextStep('kind')
            ->withNextStep('value')
            ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'isbasedon')
            ->withNextStepToSuperElement()
            ->withNextStepToSuperElement()
            ->withNextStep('resource')
            ->withNextStep('identifier')
            ->withNextStep('entry')
            ->get();

        $any_entry_path = $this->path_factory
            ->custom()
            ->withNextStep('relation')
            ->withNextStep('resource')
            ->withNextStep('identifier')
            ->withNextStep('entry')
            ->get();

        $sources = [];
        $source_navigator = $this->navigator_factory->navigator($source_path, $set->getRoot());
        foreach ($source_navigator->elementsAtFinalStep() as $source) {
            $sources[] = $source;
            $this->addNamespacedChildToXML(
                $xml,
                'source',
                $source->getData()->value()
            );
        }

        $any_entry_navigator = $this->navigator_factory->navigator($any_entry_path, $set->getRoot());
        foreach ($any_entry_navigator->elementsAtFinalStep() as $any_entry) {
            if (in_array($any_entry, $sources, true)) {
                continue;
            }

            $this->addNamespacedChildToXML(
                $xml,
                'relation',
                $any_entry->getData()->value()
            );
        }
    }

    protected function addCoveragesToXML(\SimpleXMLElement $xml, SetInterface $set): void
    {
        $coverage_path = $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('coverage')
            ->get();

        $this->addLangStringsToXML($xml, $set, 'coverage', $coverage_path);
    }

    protected function addRightsToXML(\SimpleXMLElement $xml, SetInterface $set): void
    {
        $description_path = $this->path_factory
            ->custom()
            ->withNextStep('rights')
            ->withNextStep('description')
            ->withNextStep('string')
            ->get();

        foreach ($this->getDataValuesFromPath($set, $description_path) as $value) {
            $this->addNamespacedChildToXML(
                $xml,
                'rights',
                $this->copyright_handler->copyrightAsString($value)
            );
        }
    }

    protected function addLangStringsToXML(
        \SimpleXMLElement $xml,
        SetInterface $set,
        string $name,
        PathInterface $path
    ): void {
        $navigator = $this->navigator_factory->navigator($path, $set->getRoot());
        foreach ($navigator->elementsAtFinalStep() as $element) {
            $string_element = null;
            $lang_element = null;
            foreach ($element->getSubElements() as $sub_element) {
                if ($sub_element->getDefinition()->name() === 'string') {
                    $string_element = $sub_element;
                }
                if ($sub_element->getDefinition()->name() === 'language') {
                    $lang_element = $sub_element;
                }
            }

            if (is_null($string_element)) {
                continue;
            }
            $string_xml = $this->addNamespacedChildToXML(
                $xml,
                $name,
                $string_element->getData()->value()
            );
            if (!is_null($lang_element) && !is_null($string_xml)) {
                $string_xml->addAttribute(
                    'xml:lang',
                    $lang_element->getData()->value(),
                    'xml'
                );
            }
        }
    }

    /**
     * @return string[]
     */
    protected function getDataValuesFromPath(SetInterface $set, PathInterface $path): \Generator
    {
        $navigator = $this->navigator_factory->navigator($path, $set->getRoot());
        foreach ($navigator->elementsAtFinalStep() as $element) {
            yield $element->getData()->value();
        }
    }

    protected function addNamespacedChildToXML(
        \SimpleXMLElement $xml,
        string $name,
        string $value
    ): ?\SimpleXMLElement {
        if ($value === '') {
            return null;
        }
        return $xml->addChild($name, $value, "http://purl.org/dc/elements/1.1/");
    }
}
